<?php
/* ============================================================================
 * DieMasterInquiry.php  --  EIP replacement for the DIELIB green-screen die
 * inquiries and printed reports.  READ-ONLY.
 *
 * Replaces, from DIELIB/DIESRC:
 *   DIINQ1  Die inquiry by minimum die cut dimension  -> "Min Cut Dim" filter,
 *           which reproduces the RPG SETLL lower-bound (PCWDTH >= entered value)
 *   DIINQ2  Die inquiry by die number                 -> "Die #" filter + detail
 *   DIPRT1  Die master report by die number           -> sort by Die #  + Excel
 *   DIPRT2  Die master report by piece size           -> sort by Piece Size + Excel
 *
 * NOT replaced: DIEREORG (physical purge of DELCD='D' rows and rebuild of the
 * DIESORTX logical) stays on the green screen, and DIMANT (maintenance) is a
 * separate screen -- writes need SIZDIE2 kept in lockstep.
 *
 * Data source is DIELIB.DIEMAST2 itself, so the green-screen DIE menu and this
 * page are always looking at the same records.  The die master does not
 * reference the customer or item masters, so there are no joins and no
 * drill-through links here by design.
 * ========================================================================== */

require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

/* ---------------------------------------------------------------------------
 * PCWDTH scale.
 *
 * The 1999 DDS declares PCWDTH as 4S 0, but every RPG program in DIELIB reads
 * positions 6-9 with TWO decimals, and DIMANT enforces PCWDTH <= PCLNTH where
 * PCLNTH is a true 4S 2.  So SQL is expected to return 1050 where the
 * application means 10.50.
 *
 * Set to 100 to divide raw PCWDTH by 100 for display.  Set to 1 if
 * DieMasterDiag.php panel 4 shows VIOL_RAW near zero / NUMERIC_SCALE of 2,
 * meaning the file was redefined and needs no conversion.
 * ------------------------------------------------------------------------- */
$PCWDTH_SCALE = 100;

$ROW_CAP = 10000;

/* ---- Parameters -------------------------------------------------------- */

$incDeleted = (isset($_GET['deleted']) && $_GET['deleted'] === '1');
$sortMode   = (isset($_GET['sort']) && $_GET['sort'] === 'size') ? 'size' : 'dino';

/* ---- Helpers ----------------------------------------------------------- */

function die_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* Piece width, corrected for the DDS/RPG decimal discrepancy. */
function die_pcwdth($raw) {
    global $PCWDTH_SCALE;
    if ($raw === null || $raw === '') return null;
    return (float)$raw / $PCWDTH_SCALE;
}

/* Dimension in inches, 2 decimals; blank for null/zero-ish. */
function die_dim($v) {
    if ($v === null || $v === '') return '';
    return number_format((float)$v, 2);
}

/* Rule height, 3 decimals (.918 is standard). */
function die_rule($v) {
    if ($v === null || $v === '') return '';
    return number_format((float)$v, 3);
}

function die_int($v) {
    if ($v === null || $v === '') return '';
    return number_format((int)$v);
}

/* DIMANT stores RC / BLEED as Y or N; render them the way DINQ5 does. */
function die_yn($v) {
    $v = strtoupper(trim((string)$v));
    if ($v === 'Y') return 'YES';
    if ($v === 'N') return 'NO';
    return $v;
}

/* ---- Query ------------------------------------------------------------- */
/*
 * DINO is 5A: 4 digits plus an optional letter suffix (DIPRT1 splits positions
 * 1-4 as numeric and position 5 as DILETR).  Sorting by die number is
 * therefore a character sort, matching the green screen's keyed sequence.
 *
 * The piece-size sort mirrors the DIESORTX logical (PCWDTH, PCLNTH, NOUP),
 * which is what DIPRT2 reads.
 */

$orderBy = ($sortMode === 'size')
    ? 'PCWDTH, PCLNTH, NOUP, DINO'
    : 'DINO';

$where = $incDeleted ? '' : "WHERE DELCD <> 'D'";

$sql = "
    SELECT
        TRIM(DINO)                  AS DINO,
        PCWDTH                      AS PCWDTH,
        PCLNTH                      AS PCLNTH,
        DIWIDTH                     AS DIWIDTH,
        DILNTH                      AS DILNTH,
        NOUP                        AS NOUP,
        T.\"RULE\"                  AS RULEHT,
        TRIM(SHAPE)                 AS SHAPE,
        RC                          AS RC,
        BLEED                       AS BLEED,
        TRIM(BINNO)                 AS BINNO,
        TRIM(CUSACR)                AS ARTRACK,
        TRIM(ENDUSR)                AS ENDUSR,
        CUSTNO                      AS CUSTNO,
        TRIM(PARTNO)                AS PARTNO,
        TRIM(COMENT)                AS COMENT,
        DELCD                       AS DELCD
    FROM DIELIB.DIEMAST2 T
    $where
    ORDER BY $orderBy
    FETCH FIRST $ROW_CAP ROWS ONLY
";

$conn   = $i5Connect->getConnection();
$rows   = array();
$sqlErr = '';

$stmt = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $rows[] = $r;
    }
    db2_free_stmt($stmt);
} else {
    $sqlErr = db2_stmt_errormsg();
}

$rowCount   = count($rows);
$deletedCnt = 0;
foreach ($rows as $r) {
    if (strtoupper(trim((string)$r['DELCD'])) === 'D') $deletedCnt++;
}

/* ---- CSV export (replaces DIPRT1 / DIPRT2 printed reports) ------------- */

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $tag = ($sortMode === 'size') ? 'ByPieceSize' : 'ByDieNumber';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="DieMaster_' . $tag . '_'
         . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array(
        'Die #', 'Piece Width', 'Piece Length', 'Cut Shape', 'Round Cornered',
        'Allows Bleed', 'Number Up', 'Width Across Rule', 'Length Across Rule',
        'Rule Height', 'Bin Location', 'Art Rack', 'Customer Name', 'Cust #',
        'Part Number', 'Comments', 'Status'
    ));
    foreach ($rows as $r) {
        fputcsv($out, array(
            trim((string)$r['DINO']),
            die_dim(die_pcwdth($r['PCWDTH'])),
            die_dim($r['PCLNTH']),
            trim((string)$r['SHAPE']),
            die_yn($r['RC']),
            die_yn($r['BLEED']),
            (int)$r['NOUP'],
            die_dim($r['DIWIDTH']),
            die_dim($r['DILNTH']),
            die_rule($r['RULEHT']),
            trim((string)$r['BINNO']),
            trim((string)$r['ARTRACK']),
            trim((string)$r['ENDUSR']),
            (int)$r['CUSTNO'],
            trim((string)$r['PARTNO']),
            trim((string)$r['COMENT']),
            (strtoupper(trim((string)$r['DELCD'])) === 'D') ? 'DELETED' : 'Active'
        ));
    }
    fclose($out);
    exit;
}

/* ---- Filter option lists ---------------------------------------------- */

$shapeOptions = array();
foreach ($rows as $r) {
    $s = trim((string)$r['SHAPE']);
    if ($s !== '') $shapeOptions[$s] = true;
}
ksort($shapeOptions, SORT_NATURAL);

/* ---- Detail records for the modal (mirrors green-screen DINQ5) --------- */

$jsRows = array();
foreach ($rows as $r) {
    $jsRows[] = array(
        'dino'    => trim((string)$r['DINO']),
        'pcwdth'  => die_dim(die_pcwdth($r['PCWDTH'])),
        'pclnth'  => die_dim($r['PCLNTH']),
        'diwidth' => die_dim($r['DIWIDTH']),
        'dilnth'  => die_dim($r['DILNTH']),
        'shape'   => trim((string)$r['SHAPE']),
        'noup'    => die_int($r['NOUP']),
        'rc'      => die_yn($r['RC']),
        'bleed'   => die_yn($r['BLEED']),
        'rule'    => die_rule($r['RULEHT']),
        'binno'   => trim((string)$r['BINNO']),
        'artrack' => trim((string)$r['ARTRACK']),
        'endusr'  => trim((string)$r['ENDUSR']),
        'custno'  => ((int)$r['CUSTNO'] === 0) ? '' : die_int($r['CUSTNO']),
        'partno'  => trim((string)$r['PARTNO']),
        'coment'  => trim((string)$r['COMENT']),
        'deleted' => (strtoupper(trim((string)$r['DELCD'])) === 'D') ? 1 : 0,
    );
}

/* ---- Link builders ----------------------------------------------------- */

$selfBase = $_SERVER['PHP_SELF'];

function die_url($base, $overrides) {
    $p = $_GET;
    unset($p['export']);
    foreach ($overrides as $k => $v) {
        if ($v === null) unset($p[$k]);
        else             $p[$k] = $v;
    }
    return $base . (empty($p) ? '' : '?' . http_build_query($p));
}

$exportDinoURL = die_url($selfBase, array('export' => 'csv', 'sort' => 'dino'));
$exportSizeURL = die_url($selfBase, array('export' => 'csv', 'sort' => 'size'));
$toggleDelURL  = die_url($selfBase, array('deleted' => $incDeleted ? null : '1'));
$sortDinoURL   = die_url($selfBase, array('sort' => 'dino', 'export' => null));
$sortSizeURL   = die_url($selfBase, array('sort' => 'size', 'export' => null));

/* ---- Page ------------------------------------------------------------- */

print "\n<html><head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n</head>";
require_once 'Banner.php';
require_once dirname(__FILE__) . '/../SgReportNav.php';

?>
<table <?php echo $baseTable; ?>>
<tr valign="top">
<td class="content">

<style>
table[summary="banner"] { display:none !important; }
body { box-sizing:border-box !important; }
body > table { width:100% !important; max-width:none !important; table-layout:auto !important; }
td.content { width:calc(100vw - 155px) !important; max-width:none !important;
             box-sizing:border-box !important; }
#die-grid { width:100% !important; min-width:100% !important; }
#die-grid thead th { background-color:#374151 !important; color:#fff !important;
                     font-weight:bold !important; cursor:pointer; user-select:none;
                     white-space:nowrap; font-size:11px; }
#die-grid thead th:hover { opacity:0.85; }
#die-grid thead th.die-asc::after  { content:' \25B2'; font-size:9px; }
#die-grid thead th.die-desc::after { content:' \25BC'; font-size:9px; }
#die-grid tbody .die-row:nth-child(odd)  { background:#F7F7F7; }
#die-grid tbody .die-row:nth-child(even) { background:#FFFFFF; }
#die-grid tbody .die-row:hover           { background:#EFF6FF !important; }
#die-grid tbody td { color:#111827 !important; font-size:12px; }
#die-grid tbody td.die-num a { color:#2563EB !important; text-decoration:none !important;
                               font-weight:bold !important; }
#die-grid tbody td.die-num a:hover { text-decoration:underline !important; }
#die-grid tbody tr.die-deleted td { color:#9CA3AF !important; font-style:italic; }
.die-badge { display:inline-block; font-size:9px; font-weight:700; padding:1px 5px;
             border-radius:8px; background:#DC2626; color:#fff; }

/* Detail modal -- the green-screen DINQ5 layout */
#die-ovl { display:none; position:fixed; top:0; left:0; width:100%; height:100%;
           background:rgba(17,24,39,0.55); z-index:100000; }
#die-mod { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
           background:#fff; border-radius:6px; width:640px; max-width:94vw;
           max-height:92vh; overflow-y:auto; z-index:100001; display:none;
           box-shadow:0 10px 40px rgba(0,0,0,0.4); }
#die-mod .mhdr { background:#1F2937; color:#fff; padding:9px 14px; font-size:14px;
                 font-weight:bold; display:flex; align-items:center; gap:10px; }
#die-mod .mhdr .x { margin-left:auto; cursor:pointer; font-size:20px; line-height:1;
                    color:#D1D5DB; }
#die-mod .mhdr .x:hover { color:#fff; }
#die-mod .mbody { padding:14px 16px; }
#die-mod dl { display:grid; grid-template-columns:auto 1fr auto 1fr; gap:6px 12px;
              margin:0; font-size:13px; align-items:baseline; }
#die-mod dt { color:#6B7280; white-space:nowrap; }
#die-mod dd { margin:0; font-weight:bold; color:#111827; }
#die-mod .wide { grid-column:2 / span 3; }
#die-mod .sect { grid-column:1 / span 4; border-top:1px solid #E5E7EB;
                 margin-top:8px; padding-top:8px; font-size:10px; font-weight:700;
                 color:#6B7280; text-transform:uppercase; letter-spacing:.8px; }
#die-mod .delnote { background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5;
                    padding:6px 10px; border-radius:3px; font-size:12px;
                    font-weight:bold; margin-bottom:10px; }
</style>

<div style="position:relative; left:-155px; width:calc(100% + 155px); box-sizing:border-box;
            display:flex; align-items:center;
            padding:10px 14px 10px calc(155px + 14px);
            background:linear-gradient(to right,
                #111827 0%, #1F2937 25%, #374151 55%, #4B5563 78%, #6B7280 100%);
            border-bottom:3px solid rgba(0,0,0,0.15);
            gap:10px; margin-bottom:6px;">
  <h1 style="font-size:22px;color:#fff !important;margin:0;flex:1;font-weight:bold !important;
             text-shadow:0 1px 3px rgba(0,0,0,0.4);">
    Die Master Inquiry
  </h1>
  <a href="<?php echo die_h($_sgnHome . '/Welcome.php?baseVar=' . rawurlencode($_sgnBv)
        . '&eID=' . rawurlencode($_sgnEid) . '&portal=9999999999'); ?>"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#06B6D4;
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid #0891B2;white-space:nowrap;display:inline-block;">&#8592; Back to EIP</a>
  <a href="https://screen-graphics.com/"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#CC1F20;
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid #8b1010;white-space:nowrap;display:inline-block;">Logout</a>
</div>

<?php if ($sqlErr): ?>
<p style="color:red;font-weight:bold;padding:8px;">
  SQL Error: <?php echo die_h($sqlErr); ?><br>
  <span style="font-weight:normal;font-size:12px;">If this says DIEMAST2 was not found,
  DIELIB is not reachable from the web job &mdash; run DieMasterDiag.php.</span>
</p>
<?php endif; ?>

<div style="display:flex;align-items:stretch;border-bottom:2px solid #D1D5DB;">
  <div style="flex:1;display:flex;flex-direction:column;">

    <div style="background:#2563EB;border-bottom:1px solid #1d4ed8;padding:4px 14px;
                display:flex;align-items:center;gap:14px;font-size:11px;color:#fff;flex:1;
                flex-wrap:wrap;">
      <span>Live from <b>DIELIB.DIEMAST2</b> &mdash; the same file the green-screen DIE menu uses</span>
      <span style="background:#fff;border-radius:12px;padding:2px 10px;font-weight:700;
                   color:#2563EB;">Report order:
        <?php echo ($sortMode === 'size') ? 'Piece Size (DIPRT2)' : 'Die Number (DIPRT1)'; ?></span>
      <span style="background:#fff3cd;border:1px solid #f0c060;border-radius:12px;
                   padding:2px 10px;font-weight:700;color:#856404;">As of:
        <?php echo date('D, M j, Y g:i a'); ?></span>
    </div>

    <div style="display:flex;align-items:center;gap:10px;padding:6px 10px;
                background:#F7F7F7;font-size:12px;flex:1;flex-wrap:wrap;">

      <label style="white-space:nowrap;font-weight:600;">Die #:
        <input type="text" id="die-fdino" size="8" placeholder="1234 or 1234A"
               style="padding:2px 4px;border:1px solid #b0bac8;border-radius:3px;
                      font-size:12px;margin-left:4px;">
      </label>

      <label style="white-space:nowrap;font-weight:600;"
             title="Reproduces green-screen DIINQ1: piece width is the minimum cut dimension, so this shows dies whose piece width is at least this value.">
        Min Cut Dim &ge;
        <input type="text" id="die-fmin" size="6" placeholder="10.50"
               style="padding:2px 4px;border:1px solid #b0bac8;border-radius:3px;
                      font-size:12px;margin-left:4px;">
      </label>

      <label style="white-space:nowrap;font-weight:600;">Cut Shape:
        <select id="die-fshape"
                style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                       font-size:12px;margin-left:4px;">
          <option value="">All</option>
          <?php foreach (array_keys($shapeOptions) as $v): ?>
          <option value="<?php echo die_h($v); ?>"><?php echo die_h($v); ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label style="white-space:nowrap;font-weight:600;">R/C:
        <select id="die-frc" style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                                    font-size:12px;margin-left:4px;">
          <option value="">All</option><option value="YES">Yes</option><option value="NO">No</option>
        </select>
      </label>

      <label style="white-space:nowrap;font-weight:600;">Bleed:
        <select id="die-fbleed" style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;
                                       font-size:12px;margin-left:4px;">
          <option value="">All</option><option value="YES">Yes</option><option value="NO">No</option>
        </select>
      </label>

      <label style="white-space:nowrap;font-weight:600;">Customer / Part:
        <input type="text" id="die-ftext" size="14" placeholder="name or part #"
               style="padding:2px 4px;border:1px solid #b0bac8;border-radius:3px;
                      font-size:12px;margin-left:4px;">
      </label>

      <button id="die-clear-btn"
              style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #bbb;
                     border-radius:3px;background:#fff;">Clear</button>

      <b id="die-count-text" style="margin-left:auto;white-space:nowrap;font-size:12px;">
        <?php echo number_format($rowCount); ?>&nbsp;die<?php echo $rowCount === 1 ? '' : 's'; ?>
      </b>
    </div>

  </div>

  <div style="display:flex;flex-direction:column;align-items:stretch;justify-content:center;
              gap:4px;padding:6px 10px;background:#F7F7F7;border-left:2px solid #D1D5DB;">
    <a href="<?php echo die_h($exportDinoURL); ?>"
       style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
              font-weight:bold;text-decoration:none;white-space:nowrap;text-align:center;
              display:block;">&#8595; Excel &mdash; by Die #</a>
    <a href="<?php echo die_h($exportSizeURL); ?>"
       style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
              font-weight:bold;text-decoration:none;white-space:nowrap;text-align:center;
              display:block;">&#8595; Excel &mdash; by Piece Size</a>
    <a href="<?php echo die_h($toggleDelURL); ?>"
       style="background:<?php echo $incDeleted ? '#7B1FA2' : '#6B7280'; ?>;color:#fff;
              padding:3px 14px;border-radius:3px;font-size:11px;font-weight:bold;
              text-decoration:none;white-space:nowrap;text-align:center;display:block;">
      <?php echo $incDeleted
            ? 'Hide deleted (' . number_format($deletedCnt) . ')'
            : 'Show deleted dies'; ?></a>
  </div>
</div>

<div style="padding:4px 10px;background:#EEF2FF;font-size:11px;color:#3730A3;
            border-bottom:1px solid #C7D2FE;">
  Sort order for export:
  <a href="<?php echo die_h($sortDinoURL); ?>"
     style="font-weight:<?php echo $sortMode === 'dino' ? '700' : '400'; ?>;">Die Number</a>
  &nbsp;|&nbsp;
  <a href="<?php echo die_h($sortSizeURL); ?>"
     style="font-weight:<?php echo $sortMode === 'size' ? '700' : '400'; ?>;">Piece Size</a>
  &nbsp;&mdash;&nbsp; click any column heading to re-sort on screen, or a die number for full detail.
</div>

<div style="overflow-x:auto;">
<table id="die-grid" <?php echo isset($contentTable) ? $contentTable : ''; ?>
       style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th class="colhdr">Die #</th>
      <th class="colhdr">Piece<br>Width</th>
      <th class="colhdr">Piece<br>Length</th>
      <th class="colhdr">Cut Shape</th>
      <th class="colhdr">R/C</th>
      <th class="colhdr">Bleed</th>
      <th class="colhdr">#&nbsp;Up</th>
      <th class="colhdr">Width<br>Across Rule</th>
      <th class="colhdr">Length<br>Across Rule</th>
      <th class="colhdr">Rule<br>Height</th>
      <th class="colhdr">Bin</th>
      <th class="colhdr">Art<br>Rack</th>
      <th class="colhdr">Customer Name</th>
      <th class="colhdr">Cust&nbsp;#</th>
      <th class="colhdr">Part Number</th>
      <th class="colhdr">Comments</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($rows) && !$sqlErr): ?>
    <tr><td colspan="16" class="colcode" align="center" style="padding:20px;">
      No die records found.
    </td></tr>
<?php endif; ?>
<?php foreach ($rows as $i => $r):
    $pw      = die_pcwdth($r['PCWDTH']);
    $isDel   = (strtoupper(trim((string)$r['DELCD'])) === 'D');
    $custno  = ((int)$r['CUSTNO'] === 0) ? '' : die_int($r['CUSTNO']);
?>
    <tr class="die-row<?php echo $isDel ? ' die-deleted' : ''; ?>">
      <td class="colcode die-num">
        <a href="javascript:dieDetail(<?php echo (int)$i; ?>)"
           ><?php echo die_h(trim((string)$r['DINO'])); ?></a>
        <?php if ($isDel): ?><span class="die-badge">DEL</span><?php endif; ?>
      </td>
      <td class="colcode" align="right" data-val="<?php echo $pw === null ? '' : $pw; ?>">
        <?php echo die_h(die_dim($pw)); ?></td>
      <td class="colcode" align="right" data-val="<?php echo (float)$r['PCLNTH']; ?>">
        <?php echo die_h(die_dim($r['PCLNTH'])); ?></td>
      <td class="colcode"><?php echo die_h(trim((string)$r['SHAPE'])); ?></td>
      <td class="colcode"><?php echo die_h(die_yn($r['RC'])); ?></td>
      <td class="colcode"><?php echo die_h(die_yn($r['BLEED'])); ?></td>
      <td class="colcode" align="right" data-val="<?php echo (int)$r['NOUP']; ?>">
        <?php echo die_h(die_int($r['NOUP'])); ?></td>
      <td class="colcode" align="right" data-val="<?php echo (float)$r['DIWIDTH']; ?>">
        <?php echo die_h(die_dim($r['DIWIDTH'])); ?></td>
      <td class="colcode" align="right" data-val="<?php echo (float)$r['DILNTH']; ?>">
        <?php echo die_h(die_dim($r['DILNTH'])); ?></td>
      <td class="colcode" align="right" data-val="<?php echo (float)$r['RULEHT']; ?>">
        <?php echo die_h(die_rule($r['RULEHT'])); ?></td>
      <td class="colcode"><?php echo die_h(trim((string)$r['BINNO'])); ?></td>
      <td class="colcode"><?php echo die_h(trim((string)$r['ARTRACK'])); ?></td>
      <td class="colcode"><?php echo die_h(trim((string)$r['ENDUSR'])); ?></td>
      <td class="colcode" align="right"><?php echo die_h($custno); ?></td>
      <td class="colcode"><?php echo die_h(trim((string)$r['PARTNO'])); ?></td>
      <td class="colcode"><?php echo die_h(trim((string)$r['COMENT'])); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

<?php if ($rowCount >= $ROW_CAP): ?>
<p style="font-size:11px;color:#b06000;background:#fff8e1;border:1px solid #f0c040;
          padding:5px 10px;margin:6px 0;">
  &#9888; Result capped at <?php echo number_format($ROW_CAP); ?> rows &mdash; the die master
  is larger than expected and the cap in this page should be raised.
</p>
<?php endif; ?>

<!-- Detail view -- mirrors green-screen DINQ5 -->
<div id="die-ovl" onclick="dieClose()"></div>
<div id="die-mod" role="dialog" aria-modal="true">
  <div class="mhdr">
    <span>Die Detail &mdash; <span id="dm-dino"></span></span>
    <span class="x" onclick="dieClose()" title="Close">&times;</span>
  </div>
  <div class="mbody">
    <div class="delnote" id="dm-delnote" style="display:none;">
      DIE IS IN DELETE STATUS &mdash; flagged for removal at the next DIEREORG.
    </div>
    <dl>
      <dt>Piece Width</dt>        <dd id="dm-pcwdth"></dd>
      <dt>Width Across Rule</dt>  <dd id="dm-diwidth"></dd>
      <dt>Piece Length</dt>       <dd id="dm-pclnth"></dd>
      <dt>Length Across Rule</dt> <dd id="dm-dilnth"></dd>
      <dt>Cut Shape</dt>          <dd id="dm-shape"></dd>
      <dt>Number Up</dt>          <dd id="dm-noup"></dd>
      <dt>Round Cornered</dt>     <dd id="dm-rc"></dd>
      <dt>Allows Bleed</dt>       <dd id="dm-bleed"></dd>
      <dt>Rule Height</dt>        <dd id="dm-rule"></dd>
      <dt>&nbsp;</dt>             <dd>&nbsp;</dd>

      <div class="sect">Location</div>
      <dt>Bin Location</dt>       <dd id="dm-binno"></dd>
      <dt>Art Rack</dt>           <dd id="dm-artrack"></dd>

      <div class="sect">Reference</div>
      <dt>Customer Name</dt>      <dd id="dm-endusr"></dd>
      <dt>Cust Number</dt>        <dd id="dm-custno"></dd>
      <dt>Part Number</dt>        <dd id="dm-partno" class="wide"></dd>
      <dt>Comments</dt>           <dd id="dm-coment" class="wide"></dd>
    </dl>
  </div>
</div>

</td>
</tr>
</table>

<script type="text/javascript">
var DIE_ROWS = <?php echo json_encode(array_values($jsRows)); ?>;

function dieText(id, v) {
    var el = document.getElementById(id);
    if (el) el.textContent = (v === '' || v === null || v === undefined) ? '—' : v;
}

function dieDetail(i) {
    var d = DIE_ROWS[i];
    if (!d) return;
    dieText('dm-dino',    d.dino);
    dieText('dm-pcwdth',  d.pcwdth  ? d.pcwdth  + '"' : '');
    dieText('dm-pclnth',  d.pclnth  ? d.pclnth  + '"' : '');
    dieText('dm-diwidth', d.diwidth ? d.diwidth + '"' : '');
    dieText('dm-dilnth',  d.dilnth  ? d.dilnth  + '"' : '');
    dieText('dm-shape',   d.shape);
    dieText('dm-noup',    d.noup);
    dieText('dm-rc',      d.rc);
    dieText('dm-bleed',   d.bleed);
    dieText('dm-rule',    d.rule);
    dieText('dm-binno',   d.binno);
    dieText('dm-artrack', d.artrack);
    dieText('dm-endusr',  d.endusr);
    dieText('dm-custno',  d.custno);
    dieText('dm-partno',  d.partno);
    dieText('dm-coment',  d.coment);
    var dn = document.getElementById('dm-delnote');
    if (dn) dn.style.display = d.deleted ? 'block' : 'none';
    document.getElementById('die-ovl').style.display = 'block';
    document.getElementById('die-mod').style.display = 'block';
}

function dieClose() {
    document.getElementById('die-ovl').style.display = 'none';
    document.getElementById('die-mod').style.display = 'none';
}

document.addEventListener('keydown', function (e) {
    if (e.keyCode === 27) dieClose();
});

/* ---- Filters ---------------------------------------------------------- */
(function () {
    var dinoIn  = document.getElementById('die-fdino');
    var minIn   = document.getElementById('die-fmin');
    var shapeIn = document.getElementById('die-fshape');
    var rcIn    = document.getElementById('die-frc');
    var bleedIn = document.getElementById('die-fbleed');
    var textIn  = document.getElementById('die-ftext');
    var clrBtn  = document.getElementById('die-clear-btn');
    var cntEl   = document.getElementById('die-count-text');
    var tbl     = document.getElementById('die-grid');
    if (!tbl) return;
    var tbody   = tbl.querySelector('tbody');

    function applyFilters() {
        var dino  = (dinoIn  ? dinoIn.value  : '').trim().toUpperCase();
        var min   = parseFloat(minIn ? minIn.value : '');
        var shape = shapeIn ? shapeIn.value : '';
        var rc    = rcIn    ? rcIn.value    : '';
        var bleed = bleedIn ? bleedIn.value : '';
        var txt   = (textIn ? textIn.value : '').trim().toUpperCase();
        var rows  = tbody.querySelectorAll('tr');
        var shown = 0;

        for (var i = 0; i < rows.length; i++) {
            var c = rows[i].cells;
            if (!c || c.length < 16) { rows[i].style.display = ''; continue; }

            var dinoVal  = c[0].textContent.replace('DEL', '').trim().toUpperCase();
            var pwVal    = parseFloat(c[1].getAttribute('data-val'));
            var shapeVal = c[3].textContent.trim();
            var rcVal    = c[4].textContent.trim();
            var blVal    = c[5].textContent.trim();
            var custVal  = c[12].textContent.trim().toUpperCase();
            var partVal  = c[14].textContent.trim().toUpperCase();

            var ok = (!dino  || dinoVal.indexOf(dino) === 0)
                  && (isNaN(min) || (!isNaN(pwVal) && pwVal >= min))
                  && (!shape || shapeVal === shape)
                  && (!rc    || rcVal    === rc)
                  && (!bleed || blVal    === bleed)
                  && (!txt   || custVal.indexOf(txt) !== -1
                             || partVal.indexOf(txt) !== -1);

            rows[i].style.display = ok ? '' : 'none';
            if (ok) shown++;
        }
        if (cntEl) cntEl.textContent = shown.toLocaleString()
                 + (shown === 1 ? ' die' : ' dies');
    }

    if (dinoIn)  dinoIn.addEventListener('input',  applyFilters);
    if (minIn)   minIn.addEventListener('input',   applyFilters);
    if (shapeIn) shapeIn.addEventListener('change', applyFilters);
    if (rcIn)    rcIn.addEventListener('change',   applyFilters);
    if (bleedIn) bleedIn.addEventListener('change', applyFilters);
    if (textIn)  textIn.addEventListener('input',  applyFilters);
    if (clrBtn)  clrBtn.addEventListener('click', function () {
        if (dinoIn)  dinoIn.value  = '';
        if (minIn)   minIn.value   = '';
        if (shapeIn) shapeIn.value = '';
        if (rcIn)    rcIn.value    = '';
        if (bleedIn) bleedIn.value = '';
        if (textIn)  textIn.value  = '';
        applyFilters();
    });
}());

/* ---- Click-to-sort --------------------------------------------------- */
(function () {
    var tbl = document.getElementById('die-grid');
    if (!tbl) return;
    var tbody = tbl.querySelector('tbody');
    var ths   = tbl.querySelectorAll('thead th');
    var state = { col: 0, dir: 1 };

    function cellVal(td) {
        if (td.hasAttribute('data-val')) {
            var raw = td.getAttribute('data-val');
            if (raw === '') return null;
            var f = parseFloat(raw);
            return isNaN(f) ? null : f;
        }
        var t = td.textContent.replace(/,/g, '').trim();
        if (t === '' || t === '—') return null;
        var n = parseFloat(t);
        return isNaN(n) ? t.toLowerCase() : n;
    }

    function sortBy(col) {
        state.dir = (state.col === col) ? -state.dir : 1;
        state.col = col;
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        rows.sort(function (a, b) {
            if (!a.cells[col] || !b.cells[col]) return 0;
            var va = cellVal(a.cells[col]);
            var vb = cellVal(b.cells[col]);
            if (va === null && vb === null) return 0;
            if (va === null) return 1;
            if (vb === null) return -1;
            if (va < vb) return -state.dir;
            if (va > vb) return  state.dir;
            return 0;
        });
        rows.forEach(function (r) { tbody.appendChild(r); });
        for (var i = 0; i < ths.length; i++) {
            ths[i].className = ths[i].className.replace(/\s*die-(asc|desc)/g, '');
        }
        ths[col].className += (state.dir === 1 ? ' die-asc' : ' die-desc');
    }

    for (var i = 0; i < ths.length; i++) {
        (function (col) {
            ths[col].addEventListener('click', function () { sortBy(col); });
        }(i));
    }
}());
</script>

</body>
