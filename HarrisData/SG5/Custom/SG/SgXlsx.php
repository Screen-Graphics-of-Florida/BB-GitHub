<?php
// -----------------------------------------------------------------------------
//  SgXlsx - a minimal, dependency-free .xlsx writer for SG reports
//
//  Why this exists: SG reports export CSV, which cannot carry more than one
//  sheet, cannot hold a Read Me, and loses every number format. The tier call
//  workbooks Bill built by hand in openpyxl are multi-sheet, so the EIP pages
//  that replace them need the same shape.
//
//  Why not a library: the IBM i has no Composer tree and PhpSpreadsheet would
//  be a large dependency to maintain on a box with no package manager for PHP
//  libraries. The zip extension IS present (checked 2026-08-28), and an xlsx is
//  just a zip of XML, so the whole writer is a few hundred lines.
//
//  Memory: every sheet is streamed to its own temp file with fwrite and only
//  then added to the zip, so a 45,000-row sheet costs a few KB of memory
//  instead of tens of MB of accumulated string. Nothing is ever held whole.
//
//  PHP VERSION - read this before editing. The web SAPI on this box is
//  PHP 5.6.23 under ZendServer 8.5.5 (/usr/local/zendsvr6/bin/php), NOT the
//  PHP 7.4 at /QOpenSys/pkgs/bin/php that the command line gives you. Lint with
//  the ZendServer one or you will ship a page that parses cleanly and then dies
//  with a 500. No PHP 7 syntax or functions: no intdiv, no ??, no <=>, no
//  scalar type hints, no return types, no Throwable, array() not [].
//
//  Usage:
//      require_once dirname(__FILE__) . '/SgXlsx.php';
//      $x = new SgXlsx();
//      $x->addSheet('Summary', array('widths' => array(30, 12, 14),
//                                    'freeze' => 1, 'filter' => true));
//      $x->headerRow(array('Customer', 'Orders', 'Revenue'));
//      $x->row(array('Acme', 12, $x->money(41234.5)));
//      $x->send('MyReport_20260828.xlsx');     // streams and exits
//
//  Cell values: pass a scalar for automatic handling (int/float become numbers,
//  strings become text), or one of the wrappers for formatting:
//      $x->money($v)   $#,##0            $x->money2($v)  $#,##0.00
//      $x->int($v)     #,##0             $x->pct($v)     0.0%  (pass 0.25)
//      $x->date('2026-08-28')            $x->bold($v)
//      $x->wrap($text)                   $x->title($text)
//      $x->amber($v)  highlighted money, for the figures that need the eye
// -----------------------------------------------------------------------------

class SgXlsx
{
    // Style indexes into the cellXfs list written by styles(). Keep in step.
    const S_PLAIN  = 0;
    const S_HEAD   = 1;
    const S_MONEY  = 2;
    const S_MONEY2 = 3;
    const S_INT    = 4;
    const S_DATE   = 5;
    const S_PCT    = 6;
    const S_BOLD   = 7;
    const S_WRAP   = 8;
    const S_TITLE  = 9;
    const S_AMBER  = 10;

    private $sheets  = array();   // name => array(file, rows, cols, opts)
    private $cur     = null;      // name of the open sheet
    private $fh      = null;      // handle on the open sheet's temp file
    private $rowNo   = 0;
    private $maxCol  = 0;
    private $tmp     = array();   // every temp file, for cleanup
    private $used    = array();   // lower-cased sheet names already taken

    public function __destruct()
    {
        $this->cleanup();
    }

    // -- value wrappers -------------------------------------------------------

    public function money($v)  { return array('n' => (float)$v, 's' => self::S_MONEY);  }
    public function money2($v) { return array('n' => (float)$v, 's' => self::S_MONEY2); }
    public function int($v)    { return array('n' => (float)$v, 's' => self::S_INT);    }
    public function pct($v)    { return array('n' => (float)$v, 's' => self::S_PCT);    }
    public function bold($v)   { return array('v' => $v,        's' => self::S_BOLD);   }
    // ONLY for a sheet whose first column is wide, such as a Read Me. Wrapped
    // text in a narrow column makes Excel grow the row to fit a vertical ribbon
    // of one-word lines, which wrecks the sheet. On a data sheet use plain short
    // strings instead and let them spill across the empty cells to the right.
    public function wrap($v)   { return array('v' => $v,        's' => self::S_WRAP);   }
    public function title($v)  { return array('v' => $v,        's' => self::S_TITLE);  }
    public function amber($v)  { return array('n' => (float)$v, 's' => self::S_AMBER);  }

    // A real date cell, so Excel can filter and sort it as a date rather than
    // as text. Accepts YYYY-MM-DD; anything else comes back blank rather than
    // silently landing in 1900.
    public function date($ymd)
    {
        $ymd = trim((string)$ymd);
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) { return ''; }
        $serial = $this->serial((int)$m[1], (int)$m[2], (int)$m[3]);
        if ($serial === null) { return ''; }
        return array('n' => $serial, 's' => self::S_DATE);
    }

    // Excel counts days from 1900-01-01 = 1 but also believes 1900 was a leap
    // year, so everything from 1900-03-01 on is one higher than the true day
    // count. An epoch of 1899-12-30 absorbs both, and 1970-01-01 is serial
    // 25569 on that scale.
    //
    // Pure integer arithmetic, deliberately. The first version anchored on
    // gmmktime(0,0,0,12,30,1899), which returns FALSE on PHP 5.6 in PASE
    // because it predates the Unix epoch - so serial() returned null and EVERY
    // date cell in the workbook came out empty while the day-count columns
    // beside them were fine. No date function can be trusted for the anchor.
    private function serial($y, $m, $d)
    {
        $y = (int)$y; $m = (int)$m; $d = (int)$d;
        if ($y < 1900 || $y > 9999 || $m < 1 || $m > 12 || $d < 1 || $d > 31) { return null; }

        // Days from 1970-01-01, by the standard civil-date algorithm
        $yy = ($m <= 2) ? $y - 1 : $y;
        $era = (int)floor($yy / 400);
        $yoe = $yy - $era * 400;                                  // 0..399
        $mp  = ($m > 2) ? $m - 3 : $m + 9;                         // Mar=0..Feb=11
        $doy = (int)((153 * $mp + 2) / 5) + $d - 1;                // 0..365
        $doe = $yoe * 365 + (int)($yoe / 4) - (int)($yoe / 100) + $doy;
        $days = $era * 146097 + $doe - 719468;                     // since 1970-01-01

        return $days + 25569;
    }

    // -- sheets ---------------------------------------------------------------

    // opts: widths => array of column widths in characters
    //       freeze => number of top rows to freeze (0 = none)
    //       filter => true to put an autofilter across the header row
    public function addSheet($name, $opts = array())
    {
        $this->closeSheet();

        $name = $this->sheetName($name);
        $file = $this->tempFile();
        $fh   = fopen($file, 'wb');
        if ($fh === false) {
            throw new Exception('SgXlsx: could not open a temp file for sheet ' . $name);
        }

        $freeze = isset($opts['freeze']) ? (int)$opts['freeze'] : 0;
        $widths = isset($opts['widths']) && is_array($opts['widths']) ? $opts['widths'] : array();

        fwrite($fh, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');

        fwrite($fh, '<sheetViews><sheetView workbookViewId="0">');
        if ($freeze > 0) {
            fwrite($fh, '<pane ySplit="' . $freeze . '" topLeftCell="A' . ($freeze + 1)
                      . '" activePane="bottomLeft" state="frozen"/>');
        }
        fwrite($fh, '</sheetView></sheetViews>');
        fwrite($fh, '<sheetFormatPr defaultRowHeight="13.5"/>');

        if (!empty($widths)) {
            fwrite($fh, '<cols>');
            $i = 1;
            foreach ($widths as $w) {
                $w = max(3, min(120, (float)$w));
                fwrite($fh, '<col min="' . $i . '" max="' . $i . '" width="'
                          . number_format($w, 2, '.', '') . '" customWidth="1"/>');
                $i++;
            }
            fwrite($fh, '</cols>');
        }
        fwrite($fh, '<sheetData>');

        $this->cur    = $name;
        $this->fh     = $fh;
        $this->rowNo  = 0;
        $this->maxCol = 0;
        $this->sheets[$name] = array('file' => $file, 'rows' => 0, 'cols' => 0,
                                     'filter' => !empty($opts['filter']));
        return $this;
    }

    // Excel: 31 characters, none of []:*?/\, must be unique and non-blank.
    private function sheetName($name)
    {
        $name = str_replace(array('[', ']', ':', '*', '?', '/', '\\'), ' ', (string)$name);
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($name === '') { $name = 'Sheet'; }
        if (function_exists('mb_substr')) { $name = mb_substr($name, 0, 31); }
        else                              { $name = substr($name, 0, 31); }

        // Uniqueness, keeping the suffix inside the 31-character ceiling
        $base = $name; $n = 2;
        while (isset($this->used[strtolower($name)])) {
            $sfx  = ' (' . $n . ')';
            $keep = 31 - strlen($sfx);
            $name = rtrim(substr($base, 0, $keep)) . $sfx;
            $n++;
        }
        $this->used[strtolower($name)] = true;
        return $name;
    }

    public function headerRow($labels)
    {
        $cells = array();
        foreach ($labels as $l) { $cells[] = array('v' => $l, 's' => self::S_HEAD); }
        return $this->row($cells);
    }

    public function row($cells)
    {
        if ($this->fh === null) {
            throw new Exception('SgXlsx: row() called before addSheet()');
        }
        $this->rowNo++;
        $r = $this->rowNo;
        $out = '<row r="' . $r . '">';
        $col = 0;
        foreach ($cells as $cell) {
            $col++;
            $ref = $this->colName($col) . $r;

            $style = self::S_PLAIN;
            $num   = null;
            $txt   = null;

            if (is_array($cell)) {
                $style = isset($cell['s']) ? (int)$cell['s'] : self::S_PLAIN;
                if (isset($cell['n'])) { $num = $cell['n']; }
                elseif (isset($cell['v'])) { $txt = $cell['v']; }
            } elseif (is_int($cell) || is_float($cell)) {
                $num   = $cell;
                $style = is_int($cell) ? self::S_INT : self::S_MONEY2;
            } else {
                $txt = $cell;
            }

            if ($num !== null && is_numeric($num)) {
                $out .= '<c r="' . $ref . '" s="' . $style . '"><v>'
                      . $this->numStr($num) . '</v></c>';
            } else {
                $txt = ($txt === null) ? '' : (string)$txt;
                if ($txt === '') {
                    // An empty styled cell still carries its fill and border,
                    // which matters for the header row
                    $out .= ($style === self::S_PLAIN)
                          ? ''
                          : '<c r="' . $ref . '" s="' . $style . '"/>';
                } else {
                    $out .= '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t'
                          . (($txt !== trim($txt)) ? ' xml:space="preserve"' : '')
                          . '>' . $this->esc($txt) . '</t></is></c>';
                }
            }
        }
        $out .= '</row>';
        fwrite($this->fh, $out);
        if ($col > $this->maxCol) { $this->maxCol = $col; }
        return $this;
    }

    // A blank spacer row, for the Read Me sheet
    public function blank($n = 1)
    {
        for ($i = 0; $i < $n; $i++) { $this->row(array('')); }
        return $this;
    }

    private function numStr($v)
    {
        $v = (float)$v;
        if (!is_finite($v)) { return '0'; }
        // Enough precision for money and percentages, without exponent notation
        $s = number_format($v, 6, '.', '');
        $s = rtrim(rtrim($s, '0'), '.');
        return ($s === '' || $s === '-') ? '0' : $s;
    }

    private function closeSheet()
    {
        if ($this->fh === null) { return; }
        fwrite($this->fh, '</sheetData>');
        $s = $this->sheets[$this->cur];
        // autoFilter belongs after sheetData in the schema, which is exactly
        // why the sheet is streamed - the final row number is known by now.
        if (!empty($s['filter']) && $this->rowNo > 1 && $this->maxCol > 0) {
            fwrite($this->fh, '<autoFilter ref="A1:' . $this->colName($this->maxCol)
                            . $this->rowNo . '"/>');
        }
        fwrite($this->fh, '</worksheet>');
        fclose($this->fh);
        $this->sheets[$this->cur]['rows'] = $this->rowNo;
        $this->sheets[$this->cur]['cols'] = $this->maxCol;
        $this->fh  = null;
        $this->cur = null;
    }

    public function rowCount()
    {
        return $this->rowNo;
    }

    // -- assembly -------------------------------------------------------------

    // Builds the xlsx, streams it to the browser and exits. Returns only on a
    // failure the caller should report.
    // $obKeep is how many output-buffer levels already existed before the
    // caller started buffering. Those belong to the SAPI - php.ini here sets
    // output_buffering = 4096, and tearing that buffer down under the Zend
    // Enabler leaves the FastCGI response malformed, which Apache reports as a
    // bare 500 with nothing in the PHP log. Discard only our own levels, then
    // empty what is left rather than destroying it.
    public function send($filename, $obKeep = 0)
    {
        $zipPath = $this->build();
        while (ob_get_level() > $obKeep) { @ob_end_clean(); }
        if (ob_get_level() > 0) { @ob_clean(); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $this->safeFile($filename) . '"');
        header('Cache-Control: max-age=0, must-revalidate');
        header('Pragma: public');
        // No Content-Length on purpose: with a SAPI buffer still in play a
        // stale or mismatched length is another way to earn a 500.
        readfile($zipPath);
        @unlink($zipPath);
        $this->cleanup();
        exit;
    }

    // Builds the xlsx and leaves it at $path. This is the route a scheduled job
    // takes - nothing is streamed and nothing exits, so the caller can go on to
    // attach the file to an email. Returns the path.
    public function save($path)
    {
        $zipPath = $this->build();
        if (!@copy($zipPath, $path)) {
            @unlink($zipPath);
            $this->cleanup();
            throw new Exception('SgXlsx: could not write the workbook to ' . $path);
        }
        @unlink($zipPath);
        $this->cleanup();
        return $path;
    }

    // Assembles the parts into a zip and returns its temp path.
    private function build()
    {
        $this->closeSheet();
        if (empty($this->sheets)) { throw new Exception('SgXlsx: nothing to write'); }
        if (!class_exists('ZipArchive')) {
            throw new Exception('SgXlsx: the zip extension is not loaded on this server');
        }

        $zipPath = $this->tempFile();
        @unlink($zipPath);                       // ZipArchive wants to create it
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('SgXlsx: could not create the workbook zip');
        }

        $names = array_keys($this->sheets);
        $n     = count($names);

        $zip->addFromString('[Content_Types].xml', $this->contentTypes($n));
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook($names));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels($n));
        $zip->addFromString('xl/styles.xml', $this->styles());

        $i = 1;
        foreach ($names as $nm) {
            $zip->addFile($this->sheets[$nm]['file'], 'xl/worksheets/sheet' . $i . '.xml');
            $i++;
        }
        if (!$zip->close()) {
            throw new Exception('SgXlsx: the workbook zip failed to close');
        }
        // The sheet temp files are now inside the zip and can go
        foreach ($this->sheets as $nm => $s) { @unlink($s['file']); }
        return $zipPath;
    }

    private function safeFile($f)
    {
        $f = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$f);
        return ($f === '') ? 'export.xlsx' : $f;
    }

    private function contentTypes($n)
    {
        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
           . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
           . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
           . '<Default Extension="xml" ContentType="application/xml"/>'
           . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
           . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        for ($i = 1; $i <= $n; $i++) {
            $x .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" '
                . 'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        return $x . '</Types>';
    }

    private function rootRels()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
             . '<Relationship Id="rId1" '
             . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" '
             . 'Target="xl/workbook.xml"/></Relationships>';
    }

    private function workbook($names)
    {
        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
           . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
           . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
           . '<sheets>';
        $i = 1;
        foreach ($names as $nm) {
            $x .= '<sheet name="' . $this->esc($nm) . '" sheetId="' . $i
                . '" r:id="rId' . $i . '"/>';
            $i++;
        }
        return $x . '</sheets></workbook>';
    }

    private function workbookRels($n)
    {
        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
           . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        for ($i = 1; $i <= $n; $i++) {
            $x .= '<Relationship Id="rId' . $i . '" '
                . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" '
                . 'Target="worksheets/sheet' . $i . '.xml"/>';
        }
        // Styles takes the id after the last sheet
        $x .= '<Relationship Id="rId' . ($n + 1) . '" '
            . 'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" '
            . 'Target="styles.xml"/>';
        return $x . '</Relationships>';
    }

    // Colours follow the SG report standard: header #374151 with white bold
    // text, amber #FEF3C7 for the figures that need the eye.
    private function styles()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
          . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
          . '<numFmts count="5">'
          .   '<numFmt numFmtId="164" formatCode="&quot;$&quot;#,##0"/>'
          .   '<numFmt numFmtId="165" formatCode="&quot;$&quot;#,##0.00"/>'
          .   '<numFmt numFmtId="166" formatCode="#,##0"/>'
          .   '<numFmt numFmtId="167" formatCode="yyyy\\-mm\\-dd"/>'
          .   '<numFmt numFmtId="168" formatCode="0.0%"/>'
          . '</numFmts>'
          . '<fonts count="4">'
          .   '<font><sz val="10"/><name val="Calibri"/></font>'
          .   '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
          .   '<font><b/><sz val="10"/><name val="Calibri"/></font>'
          .   '<font><b/><sz val="14"/><color rgb="FF111827"/><name val="Calibri"/></font>'
          . '</fonts>'
          . '<fills count="4">'
          .   '<fill><patternFill patternType="none"/></fill>'
          .   '<fill><patternFill patternType="gray125"/></fill>'
          .   '<fill><patternFill patternType="solid"><fgColor rgb="FF374151"/>'
          .     '<bgColor indexed="64"/></patternFill></fill>'
          .   '<fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/>'
          .     '<bgColor indexed="64"/></patternFill></fill>'
          . '</fills>'
          . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
          . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
          . '<cellXfs count="11">'
          .   '<xf numFmtId="0"   fontId="0" fillId="0" borderId="0" xfId="0"/>'
          .   '<xf numFmtId="0"   fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">'
          .     '<alignment vertical="center" wrapText="1"/></xf>'
          .   '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
          .   '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
          .   '<xf numFmtId="166" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
          .   '<xf numFmtId="167" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
          .   '<xf numFmtId="168" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
          .   '<xf numFmtId="0"   fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
          .   '<xf numFmtId="0"   fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1">'
          .     '<alignment vertical="top" wrapText="1"/></xf>'
          .   '<xf numFmtId="0"   fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
          .   '<xf numFmtId="164" fontId="2" fillId="3" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1"/>'
          . '</cellXfs>'
          . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
          . '</styleSheet>';
    }

    // -- plumbing -------------------------------------------------------------

    private function colName($n)
    {
        $s = '';
        while ($n > 0) {
            $r = ($n - 1) % 26;
            $s = chr(65 + $r) . $s;
            // Not intdiv(): the web SAPI on this box is PHP 5.6.23 under
            // ZendServer, and intdiv() arrived in PHP 7.0.
            $n = (int)floor(($n - 1) / 26);
        }
        return $s;
    }

    // XML 1.0 forbids most control characters outright - they cannot be
    // escaped, only removed, and one of them in a customer name would make the
    // whole workbook unopenable.
    private function esc($s)
    {
        $s = (string)$s;
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    private function tempFile()
    {
        $dir = sys_get_temp_dir();
        if (!is_dir($dir) || !is_writable($dir)) { $dir = '/tmp'; }
        $f = tempnam($dir, 'sgx');
        if ($f === false) {
            throw new Exception('SgXlsx: no writable temp directory (' . $dir . ')');
        }
        $this->tmp[] = $f;
        return $f;
    }

    private function cleanup()
    {
        if ($this->fh !== null) { @fclose($this->fh); $this->fh = null; }
        foreach ($this->tmp as $f) { @unlink($f); }
        $this->tmp = array();
    }
}
