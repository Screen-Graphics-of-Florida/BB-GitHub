<?php
// DiagCostHist.php
// READ-ONLY discovery for the item cost history project.
// Dumps layouts, row counts, sample rows, date ranges and journal status for:
//   SGHDSDATA/HDMCMM     -- live cost master (cost set 1 = Standard, 2 = Current)
//   SEQUELDBF/HDMCMMDLY  -- SEQUEL daily snapshot, set 1, 2025-01-01 .. 2025-08-17
//   SEQUELDBF/HDMCMMDLY1 -- SEQUEL daily snapshot, set 1, 2025-01-01 .. current
//   SEQUELDBF/HDMCMMDLY2 -- SEQUEL daily snapshot, set 2, 2025-08-18 .. current
//
// Writes nothing. Catalog reads, COUNT/MIN/MAX, and a handful of sample rows only.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/DiagCostHist.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) {
    die('DB connect failed: ' . htmlspecialchars(db2_conn_errormsg()));
}

// ---------- helpers -------------------------------------------------------

function qrows($conn, $sql, &$err = null) {
    $err = null;
    $s = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$s) { $err = db2_stmt_errormsg(); return array(); }
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    db2_free_stmt($s);
    return $out;
}

function qone($conn, $sql, &$err = null) {
    $rows = qrows($conn, $sql, $err);
    return count($rows) ? $rows[0] : null;
}

function h($v) { return htmlspecialchars(trim((string)$v)); }

// ---------- files under examination ---------------------------------------

$files = array(
    array('lib' => 'SGHDSDATA', 'file' => 'HDMCMM',
          'note' => 'Live cost master'),
    array('lib' => 'SEQUELDBF', 'file' => 'HDMCMMDLY',
          'note' => 'Snapshot, set 1 Standard, 2025-01-01 .. 2025-08-17'),
    array('lib' => 'SEQUELDBF', 'file' => 'HDMCMMDLY1',
          'note' => 'Snapshot, set 1 Standard, 2025-01-01 .. current'),
    array('lib' => 'SEQUELDBF', 'file' => 'HDMCMMDLY2',
          'note' => 'Snapshot, set 2 Current, 2025-08-18 .. current'),
);

$report = array();

foreach ($files as $f) {
    $lib  = $f['lib'];
    $file = $f['file'];
    $r = array('lib' => $lib, 'file' => $file, 'note' => $f['note']);

    // --- columns ---------------------------------------------------------
    $r['cols'] = qrows($conn,
        "SELECT ORDINAL_POSITION AS POS,
                COLUMN_NAME       AS COLNAME,
                SYSTEM_COLUMN_NAME AS SYSNAME,
                DATA_TYPE         AS DTYPE,
                LENGTH            AS LEN,
                COALESCE(NUMERIC_SCALE,0) AS SCALE,
                COALESCE(COLUMN_HEADING,'') AS HEADING,
                COALESCE(COLUMN_TEXT,'')    AS COLTEXT
           FROM QSYS2.SYSCOLUMNS
          WHERE (TABLE_SCHEMA = '$lib' OR SYSTEM_TABLE_SCHEMA = '$lib')
            AND (TABLE_NAME   = '$file' OR SYSTEM_TABLE_NAME  = '$file')
          ORDER BY ORDINAL_POSITION", $r['colerr']);

    // --- size ------------------------------------------------------------
    $st = qone($conn,
        "SELECT SUM(NUMBER_ROWS) AS NROWS,
                SUM(DATA_SIZE)   AS DSIZE,
                MAX(ROW_LENGTH)  AS RLEN
           FROM QSYS2.SYSPARTITIONSTAT
          WHERE (TABLE_SCHEMA = '$lib' OR SYSTEM_TABLE_SCHEMA = '$lib')
            AND (TABLE_NAME   = '$file' OR SYSTEM_TABLE_NAME  = '$file')",
        $r['staterr']);
    $r['stat'] = $st;

    // --- live row count --------------------------------------------------
    $c = qone($conn, "SELECT COUNT(*) AS CNT FROM $lib.$file", $r['cnterr']);
    $r['count'] = $c ? $c['CNT'] : null;

    // --- date-ish columns: min/max ---------------------------------------
    // Any DATE/TIMESTAMP column, or any column whose name smells like a date.
    $r['ranges'] = array();
    foreach ($r['cols'] as $c2) {
        $nm = trim($c2['COLNAME']);
        $dt = strtoupper(trim($c2['DTYPE']));
        $isDate = ($dt === 'DATE' || $dt === 'TIMESTAMP')
               || preg_match('/(DATE|DT|DAY|YMD|PERIOD)$/i', $nm)
               || preg_match('/^(DATE|DT)/i', $nm);
        if (!$isDate) continue;
        $mm = qone($conn,
            "SELECT MIN($nm) AS MINV, MAX($nm) AS MAXV,
                    COUNT(DISTINCT $nm) AS NDIST
               FROM $lib.$file", $e2);
        $r['ranges'][] = array(
            'col'   => $nm,
            'type'  => $dt,
            'min'   => $mm ? $mm['MINV']  : null,
            'max'   => $mm ? $mm['MAXV']  : null,
            'ndist' => $mm ? $mm['NDIST'] : null,
            'err'   => $e2,
        );
    }

    // --- low-cardinality columns: distinct counts -------------------------
    // Short character / small numeric columns are where the cost-set and
    // company/plant keys live. Cheap enough at this row count.
    $r['cards'] = array();
    $probed = 0;
    foreach ($r['cols'] as $c3) {
        if ($probed >= 12) break;
        $nm  = trim($c3['COLNAME']);
        $dt  = strtoupper(trim($c3['DTYPE']));
        $len = (int)$c3['LEN'];
        $scl = (int)$c3['SCALE'];
        $short = in_array($dt, array('CHAR','VARCHAR')) ? ($len <= 6)
               : (in_array($dt, array('DECIMAL','NUMERIC','SMALLINT','INTEGER')) && $scl == 0 && $len <= 5);
        if (!$short) continue;
        $probed++;
        $d = qone($conn, "SELECT COUNT(DISTINCT $nm) AS NDIST FROM $lib.$file", $e3);
        $vals = array();
        if (!$e3) {
            $vr = qrows($conn,
                "SELECT $nm AS V, COUNT(*) AS N FROM $lib.$file
                  GROUP BY $nm ORDER BY 2 DESC FETCH FIRST 8 ROWS ONLY", $e4);
            foreach ($vr as $v) $vals[] = trim($v['V']) . ' (' . $v['N'] . ')';
        }
        $r['cards'][] = array(
            'col'   => $nm,
            'ndist' => $d ? $d['NDIST'] : null,
            'vals'  => implode(', ', $vals),
            'err'   => $e3,
        );
    }

    // --- sample rows ------------------------------------------------------
    $n = ($file === 'HDMCMM') ? 10 : 5;
    $r['sample'] = qrows($conn,
        "SELECT * FROM $lib.$file FETCH FIRST $n ROWS ONLY", $r['samperr']);

    $report[] = $r;
}

// ---------- journal status on HDMCMM --------------------------------------
// If HDMCMM is journaled and receivers are retained, real change history may
// already exist and can be mined instead of reconstructed from snapshots.

$journal = qone($conn,
    "SELECT OBJNAME, OBJTEXT, JOURNALED, JOURNAL_NAME, JOURNAL_LIBRARY,
            JOURNAL_IMAGES, LAST_JOURNAL_START_TIMESTAMP
       FROM TABLE(QSYS2.OBJECT_STATISTICS('SGHDSDATA','FILE','HDMCMM')) x",
    $journalErr);

$receivers = array();
if ($journal && trim((string)$journal['JOURNALED']) === 'YES') {
    $jl = trim($journal['JOURNAL_LIBRARY']);
    $jn = trim($journal['JOURNAL_NAME']);
    $receivers = qrows($conn,
        "SELECT OBJNAME AS RCV, OBJSIZE AS SZ, OBJTEXT AS TXT,
                OBJCREATED AS CREATED
           FROM TABLE(QSYS2.OBJECT_STATISTICS('$jl','JRNRCV')) y
          ORDER BY OBJCREATED DESC
          FETCH FIRST 15 ROWS ONLY", $rcvErr);
}

// ---------- library sanity ------------------------------------------------
$libs = qrows($conn,
    "SELECT SCHEMA_NAME FROM QSYS2.SYSSCHEMAS
      WHERE SCHEMA_NAME IN ('SGHDSDATA','SEQUELDBF','S5HDSDATA')
      ORDER BY SCHEMA_NAME", $libErr);

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cost History Discovery - HDMCMM</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:13px Arial,sans-serif; background:#f0f2f5; padding:20px; }
.hdr { background:linear-gradient(135deg,#2a5a8c,#1a3d5c); color:#fff;
       padding:12px 20px; border-radius:5px; border-bottom:3px solid #f90;
       margin-bottom:16px; font-size:17px; font-weight:bold; }
.sect { font-weight:bold; font-size:14px; margin:22px 0 8px;
        color:#1a3d5c; border-bottom:2px solid #90caf9; padding-bottom:3px; }
.sub  { font-weight:bold; font-size:12px; margin:12px 0 4px; color:#37474f; }
.info { background:#e3f2fd; border:1px solid #90caf9; border-radius:5px;
        padding:10px 14px; margin-bottom:12px; font-size:12px; }
.ok   { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px; }
.bad  { background:#ffebee; border:1px solid #ef9a9a; border-radius:5px;
        padding:8px 14px; margin-bottom:10px; font-size:12px;
        font-family:monospace; }
table.full { border-collapse:collapse; width:100%; background:#fff;
             border-radius:4px; overflow:hidden;
             box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:14px; }
table.full th { background:#2a5a8c; color:#fff !important; font-weight:bold !important;
                padding:5px 8px; text-align:left; font-size:11px;
                white-space:nowrap; }
table.full td { padding:3px 8px; font-size:11px; font-family:monospace;
                border-bottom:1px solid #f0f0f0; white-space:nowrap; }
table.full tr:nth-child(even) td { background:#fafafa; }
.scroll { overflow-x:auto; margin-bottom:14px; }
.pill { display:inline-block; background:#1565c0; color:#fff; border-radius:3px;
        padding:1px 7px; font-size:11px; margin-right:6px; }
</style>
</head>
<body>

<div class="hdr">Cost History Discovery &mdash; HDMCMM + SEQUEL Daily Snapshots</div>

<div class="info">
  Read-only. Run: <?php echo date('Y-m-d H:i:s'); ?><br>
  Purpose: capture exact layouts so a change-only (delta) history table can be
  designed without guessing at field names or sizes.
</div>

<div class="sect">Libraries Present</div>
<?php if ($libErr): ?>
  <div class="bad"><?php echo h($libErr); ?></div>
<?php else: ?>
  <div class="ok">
    <?php foreach ($libs as $l) echo '<span class="pill">' . h($l['SCHEMA_NAME']) . '</span>'; ?>
  </div>
<?php endif; ?>

<div class="sect">Journal Status &mdash; SGHDSDATA/HDMCMM</div>
<?php if ($journalErr): ?>
  <div class="bad">OBJECT_STATISTICS failed: <?php echo h($journalErr); ?></div>
<?php elseif (!$journal): ?>
  <div class="bad">No object statistics returned for SGHDSDATA/HDMCMM.</div>
<?php else: ?>
  <table class="full">
    <tr><th>Journaled</th><th>Journal</th><th>Library</th><th>Images</th>
        <th>Journaling Started</th><th>Text</th></tr>
    <tr>
      <td><?php echo h($journal['JOURNALED']); ?></td>
      <td><?php echo h($journal['JOURNAL_NAME']); ?></td>
      <td><?php echo h($journal['JOURNAL_LIBRARY']); ?></td>
      <td><?php echo h($journal['JOURNAL_IMAGES']); ?></td>
      <td><?php echo h($journal['LAST_JOURNAL_START_TIMESTAMP']); ?></td>
      <td><?php echo h($journal['OBJTEXT']); ?></td>
    </tr>
  </table>
  <?php if (count($receivers)): ?>
    <div class="sub">Journal receivers on hand (newest first) &mdash;
      these bound how far back real change history can be mined</div>
    <table class="full">
      <tr><th>Receiver</th><th>Created</th><th>Size</th><th>Text</th></tr>
      <?php foreach ($receivers as $rc): ?>
      <tr><td><?php echo h($rc['RCV']); ?></td>
          <td><?php echo h($rc['CREATED']); ?></td>
          <td><?php echo h($rc['SZ']); ?></td>
          <td><?php echo h($rc['TXT']); ?></td></tr>
      <?php endforeach; ?>
    </table>
  <?php elseif (isset($rcvErr) && $rcvErr): ?>
    <div class="bad">Receiver list failed: <?php echo h($rcvErr); ?></div>
  <?php endif; ?>
<?php endif; ?>

<?php foreach ($report as $r): ?>

<div class="sect"><?php echo h($r['lib']); ?>/<?php echo h($r['file']); ?>
  &mdash; <?php echo h($r['note']); ?></div>

<?php if ($r['cnterr']): ?>
  <div class="bad">Row count failed: <?php echo h($r['cnterr']); ?></div>
<?php else: ?>
  <div class="ok">
    <span class="pill">Rows: <?php echo number_format((float)$r['count']); ?></span>
    <?php if ($r['stat']): ?>
      <span class="pill">Data size: <?php echo number_format((float)$r['stat']['DSIZE']); ?> bytes</span>
      <span class="pill">Row length: <?php echo h($r['stat']['RLEN']); ?></span>
    <?php endif; ?>
    <span class="pill">Columns: <?php echo count($r['cols']); ?></span>
  </div>
<?php endif; ?>

<div class="sub">Column layout</div>
<?php if ($r['colerr']): ?>
  <div class="bad"><?php echo h($r['colerr']); ?></div>
<?php elseif (!count($r['cols'])): ?>
  <div class="bad">No columns returned &mdash; file not found in catalog.</div>
<?php else: ?>
<div class="scroll">
<table class="full">
  <tr><th>#</th><th>Column</th><th>Sys Name</th><th>Type</th><th>Len</th>
      <th>Scale</th><th>Heading</th><th>Text</th></tr>
  <?php foreach ($r['cols'] as $c): ?>
  <tr>
    <td><?php echo h($c['POS']); ?></td>
    <td><strong><?php echo h($c['COLNAME']); ?></strong></td>
    <td><?php echo h($c['SYSNAME']); ?></td>
    <td><?php echo h($c['DTYPE']); ?></td>
    <td><?php echo h($c['LEN']); ?></td>
    <td><?php echo h($c['SCALE']); ?></td>
    <td><?php echo h(preg_replace('/\s+/', ' ', $c['HEADING'])); ?></td>
    <td><?php echo h($c['COLTEXT']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if (count($r['ranges'])): ?>
<div class="sub">Date-like columns &mdash; range and distinct day count</div>
<table class="full">
  <tr><th>Column</th><th>Type</th><th>Min</th><th>Max</th><th>Distinct</th><th>Error</th></tr>
  <?php foreach ($r['ranges'] as $rg): ?>
  <tr>
    <td><strong><?php echo h($rg['col']); ?></strong></td>
    <td><?php echo h($rg['type']); ?></td>
    <td><?php echo h($rg['min']); ?></td>
    <td><?php echo h($rg['max']); ?></td>
    <td><?php echo $rg['ndist'] === null ? '' : number_format((float)$rg['ndist']); ?></td>
    <td><?php echo h($rg['err']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if (count($r['cards'])): ?>
<div class="sub">Low-cardinality columns &mdash; likely keys (cost set, company, plant, type)</div>
<div class="scroll">
<table class="full">
  <tr><th>Column</th><th>Distinct</th><th>Top values (count)</th><th>Error</th></tr>
  <?php foreach ($r['cards'] as $cd): ?>
  <tr>
    <td><strong><?php echo h($cd['col']); ?></strong></td>
    <td><?php echo $cd['ndist'] === null ? '' : number_format((float)$cd['ndist']); ?></td>
    <td><?php echo h($cd['vals']); ?></td>
    <td><?php echo h($cd['err']); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<div class="sub">Sample rows</div>
<?php if ($r['samperr']): ?>
  <div class="bad"><?php echo h($r['samperr']); ?></div>
<?php elseif (!count($r['sample'])): ?>
  <div class="bad">No rows returned.</div>
<?php else: ?>
<div class="scroll">
<table class="full">
  <tr><?php foreach (array_keys($r['sample'][0]) as $k)
          echo '<th>' . h($k) . '</th>'; ?></tr>
  <?php foreach ($r['sample'] as $row): ?>
  <tr><?php foreach ($row as $v) echo '<td>' . h($v) . '</td>'; ?></tr>
  <?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php endforeach; ?>

<div class="sect">Next step</div>
<div class="info">
  Once the layouts above are known, a second pass measures <strong>churn</strong> &mdash;
  how many item/cost-set combinations actually change value per day in the
  snapshot files. That number decides the size of the delta history table and
  confirms the change-only design before anything is built.
</div>

</body>
</html>