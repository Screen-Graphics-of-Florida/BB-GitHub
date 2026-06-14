<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

// BACKUP before any output
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $lines = array('-- SG5STDPGM.SYDSGN backup -- ' . date('Y-m-d H:i:s'));
    $rb = db2_exec($conn, "SELECT * FROM SG5STDPGM.SYDSGN ORDER BY PDTBID,PDPGID");
    if (!$rb) { $lines[] = '-- query failed: ' . db2_stmt_errormsg(); }
    else {
        $nbc = db2_num_fields($rb); $bc = array();
        for ($i=0; $i<$nbc; $i++) $bc[] = db2_field_name($rb, $i);
        while ($row = db2_fetch_assoc($rb)) {
            $vals = array();
            foreach ($bc as $c) $vals[] = "'" . str_replace("'","''",(string)rtrim($row[$c])) . "'";
            $lines[] = "INSERT INTO SG5STDPGM.SYDSGN (" . implode(',',$bc) . ") VALUES (" . implode(',',$vals) . ");";
        }
    }
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="sydsgn_backup_' . date('Ymd_His') . '.sql"');
    header('Cache-Control: no-cache, no-store');
    echo implode("\r\n", $lines);
    exit;
}

// UPDATE
$updateDone = false; $updateMsg = '';
if (isset($_POST['fix'])) {
    $r = db2_exec($conn, "UPDATE SG5STDPGM.SYDSGN SET PDUSER=' ' WHERE PDTBID=199 AND PDPGID=0");
    if ($r) {
        $updateDone = true;
        $updateMsg = 'OK — ' . db2_num_rows($r) . ' row(s) updated. PDUSER is now blank (all users).';
    } else {
        $updateDone = true;
        $updateMsg = 'ERROR: ' . db2_stmt_errormsg();
    }
}

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SYDSGN Fix</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 7px;white-space:nowrap;}
th{background:#ddd;}.hi{background:#dfd;font-weight:bold;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
.step{border:1px solid #999;padding:10px;background:#f9f9f9;margin:8px 0;}
</style></head><body><h1>SYDSGN Fix: PDUSER blank for PDTBID=199</h1>';

echo '<p><b>Root cause:</b> SG5STDPGM.SYDSGN row for PDTBID=199 has <code>PDUSER=\'BILL\'</code>.
Rtv_Default_Page() returns NULL for any user other than BILL, making the SQL
<code>PDPGID=</code> (syntax error). Fix: set PDUSER to blank so it applies to all users.</p>';

if ($updateDone) {
    if (strpos($updateMsg,'ERROR')===false) {
        echo '<p><span class="ok">'. htmlspecialchars($updateMsg) .'</span></p>';
        // Verify
        $rv = db2_exec($conn, "SELECT PDTBID,PDPGID,PDTYPE,PDUSER,PDROLE FROM SG5STDPGM.SYDSGN WHERE PDTBID=199");
        if ($rv) {
            $row = db2_fetch_assoc($rv);
            echo '<p>Verified: PDUSER=\''.htmlspecialchars(rtrim($row['PDUSER'])).'\' PDROLE=\''.htmlspecialchars(rtrim($row['PDROLE'])).'\'</p>';
        }
        echo '<p><strong>Now try Configuration &gt; Portal on SG5.</strong></p>';
    } else {
        echo '<p><span class="err">'.htmlspecialchars($updateMsg).'</span></p>';
    }
    echo '</body></html>'; exit;
}

// Show current state
echo '<h2>Current SYDSGN row for PDTBID=199</h2>';
$r = db2_exec($conn, "SELECT * FROM SG5STDPGM.SYDSGN WHERE PDTBID=199");
if ($r) {
    $nf = db2_num_fields($r); $cols = array();
    for ($i=0;$i<$nf;$i++) $cols[] = db2_field_name($r,$i);
    echo '<table><tr>'; foreach ($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>'; echo '</tr>';
    while ($row = db2_fetch_assoc($r)) {
        echo '<tr>';
        foreach ($cols as $c) {
            $val = rtrim((string)$row[$c]);
            $cls = ($c==='PDUSER' && $val!=='') ? ' class="err"' : '';
            echo '<td'.$cls.'>'.htmlspecialchars($val).'</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p class="err">Query failed: '.htmlspecialchars(db2_stmt_errormsg()).'</p>';
}

echo '<div class="step"><b>Step 1:</b> <a href="?action=backup" style="background:#060;color:#fff;padding:6px 16px;text-decoration:none;">Download SG5STDPGM.SYDSGN backup</a></div>';
echo '<div class="step"><b>Step 2:</b> Set PDUSER=blank for PDTBID=199 so all users can access Configuration/Portal<br><br>
<form method="post"><button name="fix" value="1" style="background:#c00;color:#fff;padding:7px 20px;font-size:13px;">UPDATE PDUSER to blank</button></form></div>';

echo '</body></html>';
?>
