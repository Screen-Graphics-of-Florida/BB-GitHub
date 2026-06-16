<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn   = $i5Connect->getConnection();
$action = isset($_POST['action']) ? $_POST['action'] : 'preview';

$PORT = 'SGCUSTRPT';

// Collect all SYURLM FUIDs linked through SYPORT sub-items
function getSubFuids($conn, $port) {
    $fuids = array();
    $r = db2_exec($conn, "SELECT DISTINCT FPID FROM SYPORT WHERE FPPORT = '" . $port . "' AND TRIM(FPPAGE) != ''");
    if ($r) {
        while ($row = db2_fetch_assoc($r)) {
            $v = trim($row['FPID']);
            if ($v !== '') $fuids[] = $v;
        }
    }
    return $fuids;
}

$subFuids = getSubFuids($conn, $PORT);

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Delete SGCUSTRPT</title>
<style>body{font-family:Arial,sans-serif;font-size:13px;padding:20px;}
h2{color:#8b0000;}h3{color:#444;margin:16px 0 6px;}
table{border-collapse:collapse;margin-bottom:10px;}td,th{border:1px solid #ccc;padding:3px 8px;}th{background:#eee;}
.warn{background:#fff3cd;border:1px solid #f0ad4e;border-radius:4px;padding:12px;margin:12px 0;}
.ok{background:#d4edda;border:1px solid #28a745;border-radius:4px;padding:12px;margin:12px 0;}
.btn-del{background:#c00;color:#fff;border:none;padding:10px 24px;font-size:14px;font-weight:bold;cursor:pointer;border-radius:4px;}
.btn-del:hover{background:#900;}
pre{background:#f8f8f8;border:1px solid #ddd;padding:8px;font-size:11px;}
</style></head><body>';

echo '<h2>Delete Portal: SGCUSTRPT (SG Custom Rpt)</h2>';

if ($action === 'delete') {
    // EXECUTE DELETION
    echo '<h3>Deleting...</h3>';
    $errors = array();
    $total  = 0;

    // 1. SYROLD
    $r = db2_exec($conn, "DELETE FROM SYROLD WHERE RDPORT = '" . $PORT . "'");
    if ($r) {
        $n = db2_num_rows($r);
        echo 'OK  SYROLD: ' . $n . ' rows deleted<br>';
        $total += $n;
    } else {
        $errors[] = 'SYROLD: ' . db2_stmt_errormsg();
        echo 'ERR SYROLD: ' . end($errors) . '<br>';
    }

    // 2. SYPORT all rows for this portal
    $r = db2_exec($conn, "DELETE FROM SYPORT WHERE FPPORT = '" . $PORT . "'");
    if ($r) {
        $n = db2_num_rows($r);
        echo 'OK  SYPORT: ' . $n . ' rows deleted<br>';
        $total += $n;
    } else {
        $errors[] = 'SYPORT: ' . db2_stmt_errormsg();
        echo 'ERR SYPORT: ' . end($errors) . '<br>';
    }

    // 3. SYURLM sub-items
    foreach ($subFuids as $fuid) {
        $r = db2_exec($conn, "DELETE FROM SYURLM WHERE FUID = '" . str_replace("'","''",$fuid) . "'");
        if ($r) {
            $n = db2_num_rows($r);
            echo 'OK  SYURLM sub ' . htmlspecialchars($fuid) . ': ' . $n . ' rows<br>';
            $total += $n;
        } else {
            $errors[] = 'SYURLM sub ' . $fuid . ': ' . db2_stmt_errormsg();
            echo 'ERR SYURLM sub ' . htmlspecialchars($fuid) . ': ' . end($errors) . '<br>';
        }
    }

    // 4. SYURLM header
    $r = db2_exec($conn, "DELETE FROM SYURLM WHERE FUID = '" . $PORT . "'");
    if ($r) {
        $n = db2_num_rows($r);
        echo 'OK  SYURLM header ' . $PORT . ': ' . $n . ' rows<br>';
        $total += $n;
    } else {
        $errors[] = 'SYURLM header: ' . db2_stmt_errormsg();
        echo 'ERR SYURLM header: ' . end($errors) . '<br>';
    }

    // 5. SYPGMO
    $r = db2_exec($conn, "DELETE FROM SYPGMO WHERE SOPGID = '" . $PORT . "'");
    if ($r) {
        $n = db2_num_rows($r);
        echo 'OK  SYPGMO: ' . $n . ' rows deleted<br>';
        $total += $n;
    } else {
        $errors[] = 'SYPGMO: ' . db2_stmt_errormsg();
        echo 'ERR SYPGMO: ' . end($errors) . '<br>';
    }

    echo '<hr>';
    if (empty($errors)) {
        echo '<div class="ok"><strong>Done. ' . $total . ' total rows deleted. SGCUSTRPT has been removed from the system.</strong></div>';
    } else {
        echo '<div class="warn"><strong>' . count($errors) . ' error(s) occurred. Check above.</strong></div>';
    }
    echo '</body></html>';
    exit;
}

// PREVIEW MODE
echo '<div class="warn"><strong>WARNING:</strong> This will permanently delete the SGCUSTRPT portal and all its sub-items from SYURLM, SYPORT, SYROLD, and SYPGMO. Make sure you have a backup before proceeding.</div>';

// Show what will be deleted
$sections = array(
    array('SYROLD', "SELECT * FROM SYROLD WHERE RDPORT = '" . $PORT . "' ORDER BY RDROLE"),
    array('SYPORT', "SELECT * FROM SYPORT WHERE FPPORT = '" . $PORT . "' ORDER BY FPPAGE, FPSEQ"),
    array('SYURLM header', "SELECT * FROM SYURLM WHERE FUID = '" . $PORT . "'"),
    array('SYPGMO', "SELECT * FROM SYPGMO WHERE SOPGID = '" . $PORT . "'"),
);
if (!empty($subFuids)) {
    $inList = implode("','", array_map(function($f){ return str_replace("'","''",$f); }, $subFuids));
    $sections[] = array('SYURLM sub-items', "SELECT * FROM SYURLM WHERE FUID IN ('" . $inList . "') ORDER BY FUID");
}

$grandTotal = 0;
foreach ($sections as $sec) {
    $label = $sec[0]; $sql = $sec[1];
    $r = db2_exec($conn, $sql);
    echo '<h3>' . htmlspecialchars($label) . '</h3>';
    if (!$r) {
        echo '<p style="color:red">Query error: ' . htmlspecialchars(db2_stmt_errormsg()) . '</p>';
        continue;
    }
    $rows = array(); $cols = array();
    $nc = db2_num_fields($r);
    for ($i = 0; $i < $nc; $i++) $cols[] = db2_field_name($r, $i);
    while ($row = db2_fetch_assoc($r)) $rows[] = $row;
    echo '<p>' . count($rows) . ' row(s) will be deleted</p>';
    $grandTotal += count($rows);
    if (!empty($rows)) {
        echo '<table><tr>';
        foreach ($cols as $c) echo '<th>' . htmlspecialchars($c) . '</th>';
        echo '</tr>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $v) echo '<td>' . htmlspecialchars(trim((string)$v)) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

echo '<hr><p><strong>Total rows to delete: ' . $grandTotal . '</strong></p>';
echo '<div class="warn"><strong>Have you backed up first?</strong> <a href="sg_menu_backup.php?dl=1">Download backup now</a> before continuing.</div>';
echo '<form method="post">
<input type="hidden" name="action" value="delete">
<button type="submit" class="btn-del" onclick="return confirm(\'Are you sure? This cannot be undone without restoring from backup.\')">DELETE SGCUSTRPT FROM SYSTEM</button>
</form>';

echo '</body></html>';
?>
