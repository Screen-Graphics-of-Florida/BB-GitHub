<?php
// DiagKitsStructure.php
// READ-ONLY diagnostic for KitsStructureReport.php.
//
// Dumps the real column layout + a couple of sample rows for every file the
// Kits Structure Report touches, so the report's field names can be locked to
// what actually exists instead of being guessed:
//
//   SGHDSDATA.HDIMST   parent/child item master
//   SGHDSDATA.HDMPSM   BOM / product structure
//   SGHDSDATA.HDIWHS   item x warehouse (on hand, sold YTD, issued YTD)
//   SGHDSDATA.HDIPLT   item x plant     (qty mfg YTD, committed to MO)
//   SGHDSDATA.HDPCLS   product class list
//
// Run this BEFORE trusting the report's numbers.  It writes nothing.
//
// URL: https://portal.screen-graphics.com:5610/Custom/SG/Order%20Entry/DiagKitsStructure.php

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

function dks_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function dks_rows($conn, $sql) {
    $s = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$s) return array('__error' => db2_stmt_errormsg());
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    db2_free_stmt($s);
    return $out;
}

$files = array('HDIMST', 'HDMPSM', 'HDIWHS', 'HDIPLT', 'HDPCLS');

// Item pattern used for the sample-row probes (same default as the report).
$probeItem = isset($_GET['item']) ? trim((string)$_GET['item']) : '94-*';
$probeLike = str_replace(array('*', '?'), array('%', '_'), $probeItem);
$probeLike = str_replace("'", "''", $probeLike);

// Parent whose full BOM is dumped under HDMPSM.
$probeParent = isset($_GET['parent']) ? trim((string)$_GET['parent']) : '';
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Diag - Kits Structure files</title>
<style>
 body { font-family:Arial,sans-serif; font-size:13px; margin:18px; }
 h1 { font-size:18px; } h2 { font-size:15px; margin-top:26px; border-bottom:2px solid #374151; padding-bottom:3px; }
 table { border-collapse:collapse; margin:8px 0 4px; }
 th,td { border:1px solid #ccc; padding:3px 7px; font-size:12px; text-align:left; white-space:nowrap; }
 th { background:#374151; color:#fff; }
 .err { color:#CC1F20; font-weight:bold; }
 .note { color:#555; font-style:italic; margin:4px 0 10px; }
 code { background:#f2f2f2; padding:1px 4px; }
</style></head><body>

<h1>Kits Structure Report &ndash; file layout diagnostic</h1>
<p class="note">Read-only. HDIMST sample rows are filtered on item pattern
<code><?php echo dks_h($probeItem); ?></code> (override with <code>?item=...</code>).
The HDMPSM sample is the <b>complete</b> component list for a single parent
(override with <code>?parent=...</code>) &mdash; never a truncated slice, which would
read as a whole structure when it is not.</p>

<?php foreach ($files as $f):

    $cols = dks_rows($conn,
        "SELECT COLUMN_NAME, DATA_TYPE, LENGTH, NUMERIC_SCALE, COLUMN_TEXT
           FROM QSYS2.SYSCOLUMNS
          WHERE TABLE_SCHEMA = 'SGHDSDATA' AND TABLE_NAME = '$f'
          ORDER BY ORDINAL_POSITION");

    $keys = dks_rows($conn,
        "SELECT k.COLUMN_NAME, k.ORDINAL_POSITION
           FROM QSYS2.SYSKEYCST k
           JOIN QSYS2.SYSCST c
             ON c.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
            AND c.CONSTRAINT_NAME   = k.CONSTRAINT_NAME
          WHERE c.TABLE_SCHEMA = 'SGHDSDATA' AND c.TABLE_NAME = '$f'
            AND c.CONSTRAINT_TYPE = 'PRIMARY KEY'
          ORDER BY k.ORDINAL_POSITION");

    $cnt = dks_rows($conn, "SELECT COUNT(*) AS N FROM SGHDSDATA.$f");
?>
<h2><?php echo dks_h($f); ?>
    <?php if (isset($cnt[0]['N'])): ?>
        &mdash; <?php echo dks_h(number_format((float)$cnt[0]['N'])); ?> rows
    <?php endif; ?>
</h2>

<?php if (isset($cols['__error'])): ?>
    <p class="err">SYSCOLUMNS error: <?php echo dks_h($cols['__error']); ?></p>
<?php else: ?>
    <?php if (!isset($keys['__error']) && !empty($keys)):
        $kn = array();
        foreach ($keys as $k) $kn[] = rtrim((string)$k['COLUMN_NAME']);
    ?>
    <p class="note">Primary key: <code><?php echo dks_h(implode(', ', $kn)); ?></code></p>
    <?php endif; ?>

    <table>
      <tr><th>#</th><th>Column</th><th>Type</th><th>Len</th><th>Scale</th><th>Text</th></tr>
      <?php $i = 0; foreach ($cols as $c): $i++; ?>
      <tr>
        <td><?php echo $i; ?></td>
        <td><b><?php echo dks_h(rtrim((string)$c['COLUMN_NAME'])); ?></b></td>
        <td><?php echo dks_h(rtrim((string)$c['DATA_TYPE'])); ?></td>
        <td><?php echo dks_h($c['LENGTH']); ?></td>
        <td><?php echo dks_h($c['NUMERIC_SCALE']); ?></td>
        <td><?php echo dks_h(rtrim((string)$c['COLUMN_TEXT'])); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>

    <?php
    // Sample rows.
    //   HDIMST  - probed on the item pattern.
    //   HDMPSM  - EVERY component of ONE parent, not an arbitrary row slice. A
    //             "FETCH FIRST 3" here is actively misleading: it returns a
    //             partial structure that reads like a whole one. (That is how
    //             00200-111000-MS looked like a 3-component kit when it has 4:
    //             OLUV-GLS61 at seq 80 was simply past the cut.)
    //             ?parent=<item> picks the parent; otherwise the first one in
    //             the file is used.
    //   others  - first few rows is fine, they are per-item files.
    $sampleSql = "SELECT * FROM SGHDSDATA.$f FETCH FIRST 3 ROWS ONLY";

    if ($f === 'HDIMST') {
        $sampleSql = "SELECT * FROM SGHDSDATA.HDIMST
                       WHERE RTRIM(IMITEM) LIKE '$probeLike'
                       FETCH FIRST 3 ROWS ONLY";
    } elseif ($f === 'HDMPSM') {
        if ($probeParent !== '') {
            $sampleSql = "SELECT * FROM SGHDSDATA.HDMPSM
                           WHERE RTRIM(PSPPN) = '" . str_replace("'", "''", $probeParent) . "'
                           ORDER BY PSSEQN, PSCPN";
        } else {
            $sampleSql = "SELECT * FROM SGHDSDATA.HDMPSM
                           WHERE RTRIM(PSPPN) = (SELECT MIN(RTRIM(PSPPN)) FROM SGHDSDATA.HDMPSM)
                           ORDER BY PSSEQN, PSCPN";
        }
    }
    $sample = dks_rows($conn, $sampleSql);
    ?>
    <?php if ($f === 'HDMPSM'): ?>
    <p class="note">Complete structure for one parent<?php
        echo $probeParent !== '' ? ' (' . dks_h($probeParent) . ')' : ' (lowest PSPPN in the file)';
    ?> &mdash; <?php echo is_array($sample) && !isset($sample['__error']) ? count($sample) : 0; ?>
    component line(s). Override with <code>?parent=&lt;item&gt;</code>.</p>
    <?php endif; ?>
    <?php if (isset($sample['__error'])): ?>
        <p class="err">Sample error: <?php echo dks_h($sample['__error']); ?></p>
    <?php elseif (empty($sample)): ?>
        <p class="note">No sample rows returned.</p>
    <?php else: ?>
    <table>
      <tr><?php foreach (array_keys($sample[0]) as $h): ?><th><?php echo dks_h($h); ?></th><?php endforeach; ?></tr>
      <?php foreach ($sample as $r): ?>
      <tr><?php foreach ($r as $v): ?><td><?php echo dks_h(rtrim((string)$v)); ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
<?php endif; ?>

<?php endforeach; ?>

<h2>Confirmed field map used by KitsStructureReport.php</h2>
<p class="note">Resolved from this diagnostic on 2026-08-10 and hard-coded in the report.
Re-run this page after any Harris upgrade and re-check the rows below.</p>
<table>
  <tr><th>Needed value</th><th>File</th><th>Column</th><th>Note</th></tr>
  <tr><td>BOM parent item</td><td>HDMPSM</td><td>PSPPN</td><td>CHAR(15) Parent Item Number</td></tr>
  <tr><td>BOM child item</td><td>HDMPSM</td><td>PSCPN</td><td>CHAR(15) Component Item Number</td></tr>
  <tr><td>BOM qty per</td><td>HDMPSM</td><td>PSQPER</td><td>DECIMAL(9,5) Quantity Per</td></tr>
  <tr><td>Routing sequence</td><td>HDMPSM</td><td>PSSEQN</td><td>part of the key - a component can repeat per step</td></tr>
  <tr><td>Structure status</td><td>HDMPSM</td><td>PSSTC</td><td>shown, not filtered</td></tr>
  <tr><td>Product class code</td><td>HDPCLS</td><td>PCPCLS</td><td>CHAR(4), 93 rows; description PCPCDS</td></tr>
  <tr><td>Qty on hand</td><td>HDIWHS</td><td>IWOHQT</td><td>summed over all warehouses</td></tr>
  <tr><td>Qty sold YTD</td><td>HDIWHS</td><td>IWQSYT</td><td>summed over all warehouses</td></tr>
  <tr><td>Qty issued YTD</td><td>HDIWHS</td><td>IWQIYT</td><td><b>not</b> IWQITY - that column does not exist</td></tr>
  <tr><td>Qty kit issued YTD</td><td>HDIWHS</td><td>IWQKYT</td><td>added - kit-specific issues</td></tr>
  <tr><td>Qty mfg YTD</td><td>HDIPLT</td><td>IPQMFG</td><td>YTD Quantity Manufactured</td></tr>
  <tr><td>Qty committed to MO</td><td>HDIPLT</td><td>IPCMTO</td><td>Quantity Committed To Manufacturing</td></tr>
  <tr><td>Kit flag</td><td>HDIMST</td><td>IMKIT</td><td>added - K/F/C, an alternative to filtering on IMPCLS</td></tr>
</table>

</body></html>
