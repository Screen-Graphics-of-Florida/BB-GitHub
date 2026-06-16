<?php
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/sg_install_error.log');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $e = error_get_last();
    if ($e && $e['type'] === 1) {
        file_put_contents('/tmp/sg_install_error.log',
            "FATAL [{$e['type']}] {$e['message']} in {$e['file']}:{$e['line']}\n",
            FILE_APPEND);
    }
});

file_put_contents('/tmp/sg_install_error.log', date('H:i:s') . " install start\n");

require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

file_put_contents('/tmp/sg_install_error.log', date('H:i:s') . " includes ok\n", FILE_APPEND);

function sgQ($v) { return str_replace("'", "''", $v); }
function sgCap($v, $n) { return substr($v, 0, $n); }
function sgRowExists($conn, $sql) {
    $r = db2_exec($conn, $sql);
    if (!$r) return false;
    $row = db2_fetch_assoc($r);
    return $row && (int)$row['CNT'] > 0;
}
function sgRunSQL($conn, $sql, &$errList) {
    $r = db2_exec($conn, $sql);
    if (!$r) { $errList[] = db2_stmt_errormsg(); return false; }
    return true;
}

file_put_contents('/tmp/sg_install_error.log', date('H:i:s') . " functions ok\n", FILE_APPEND);

$action  = isset($_GET['action']) ? $_GET['action'] : 'preview';
$conn    = $i5Connect->getConnection();
$dbUser  = strtoupper(trim($_SERVER['PHP_AUTH_USER']));

$portals = array(
    array('code' => 'SGINQ',  'title' => 'SG Inquiries',     'seq' => 100),
    array('code' => 'SGDASH', 'title' => 'SG Dashboards',    'seq' => 101),
    array('code' => 'SGDINT', 'title' => 'SG Data Integrity', 'seq' => 102),
    array('code' => 'SGRPT',  'title' => 'SG Reports',        'seq' => 103),
    array('code' => 'SGSOP',  'title' => "SG SOP's",          'seq' => 104),
);
$cats = array(
    array('code' => 'ACCT',    'label' => 'Accounting'),
    array('code' => 'INVMGMT', 'label' => 'Inventory Management'),
    array('code' => 'MFG',     'label' => 'Manufacturing'),
    array('code' => 'OE',      'label' => 'Order Entry'),
    array('code' => 'PLN',     'label' => 'Planning'),
    array('code' => 'PUR',     'label' => 'Purchasing'),
);
$roles = array(
    'ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR',
    'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY',
    'LARIANN','MCRESPO','MFGVP','PLANNING','PLANPRDPUR',
    'PRODMANAGR','PRODUCTION','PURCHASING','SALES','SALESADMIN',
    'TIFFANY','WIDGETS','HD_SLSM',
);

file_put_contents('/tmp/sg_install_error.log', date('H:i:s') . " arrays ok\n", FILE_APPEND);

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG Install</title></head><body>';
echo '<h2>SG Menu Install - SG5</h2>';

file_put_contents('/tmp/sg_install_error.log', date('H:i:s') . " html started\n", FILE_APPEND);

if ($action === 'install') {

    $ins = 0; $skip = 0; $err = 0; $errors = array();

    foreach ($portals as $portal) {
        $code  = $portal['code'];
        $title = $portal['title'];
        $rdseq = $portal['seq'];
        $short = sgCap($title, 20);

        // SYURLM portal header
        $sql = "SELECT COUNT(*) AS CNT FROM SYURLM WHERE FUID = '" . sgQ($code) . "'";
        if (sgRowExists($conn, $sql)) {
            echo 'SKIP SYURLM header ' . $code . '<br>';
            $skip++;
        } else {
            $sql = "INSERT INTO SYURLM (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU) VALUES ('" . sgQ(sgCap($code,55)) . "','" . sgQ($short) . "','" . sgQ(sgCap($title,50)) . "','','','','Y','')";
            if (sgRunSQL($conn, $sql, $errors)) { echo 'OK   SYURLM header ' . $code . '<br>'; $ins++; }
            else { echo 'ERR  SYURLM header ' . $code . ' - ' . end($errors) . '<br>'; $err++; }
        }

        // SYPORT portal header
        $sql = "SELECT COUNT(*) AS CNT FROM SYPORT WHERE FPPORT = '" . sgQ($code) . "' AND TRIM(FPPAGE) = ''";
        if (sgRowExists($conn, $sql)) {
            echo 'SKIP SYPORT header ' . $code . '<br>';
            $skip++;
        } else {
            $sql = "INSERT INTO SYPORT (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,FPTSTP,FPTSUS) VALUES ('" . sgQ(sgCap($code,20)) . "','',1.00,'" . sgQ(sgCap($code,55)) . "','','" . sgQ(sgCap($title,50)) . "','Y','',CURRENT_TIMESTAMP,'" . sgQ(sgCap($dbUser,10)) . "')";
            if (sgRunSQL($conn, $sql, $errors)) { echo 'OK   SYPORT header ' . $code . '<br>'; $ins++; }
            else { echo 'ERR  SYPORT header ' . $code . ' - ' . end($errors) . '<br>'; $err++; }
        }

        // SYPGMO
        $sql = "SELECT COUNT(*) AS CNT FROM SYPGMO WHERE SOPGID = '" . sgQ(sgCap($code,10)) . "' AND SOMOPT = 1";
        if (sgRowExists($conn, $sql)) {
            echo 'SKIP SYPGMO ' . $code . '<br>';
            $skip++;
        } else {
            $sql = "INSERT INTO SYPGMO (SOPGID,SOMOPT,SOMDES,SORESV) VALUES ('" . sgQ(sgCap($code,10)) . "',1,'View','Y')";
            if (sgRunSQL($conn, $sql, $errors)) { echo 'OK   SYPGMO ' . $code . '<br>'; $ins++; }
            else { echo 'ERR  SYPGMO ' . $code . ' - ' . end($errors) . '<br>'; $err++; }
        }

        // Sub-categories
        foreach ($cats as $cidx => $cat) {
            $fuid   = $code . '_' . $cat['code'];
            $lbl    = $cat['label'];
            $ftitl  = sgCap($title . ' - ' . $lbl, 50);
            $fpdesc = sgCap($lbl, 20);
            $url    = '@@homeURL@@phpPathCustom/SG/sg_portal_landing.php?portal=' . $code . '&cat=' . $cat['code'];
            $fpseq  = number_format($cidx + 1, 2, '.', '');

            $sql = "SELECT COUNT(*) AS CNT FROM SYURLM WHERE FUID = '" . sgQ($fuid) . "'";
            if (sgRowExists($conn, $sql)) {
                echo 'SKIP SYURLM sub ' . $fuid . '<br>'; $skip++;
            } else {
                $sql = "INSERT INTO SYURLM (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU) VALUES ('" . sgQ(sgCap($fuid,55)) . "','" . sgQ(sgCap($lbl,20)) . "','" . sgQ(sgCap($ftitl,50)) . "','_blank','" . sgQ(sgCap($url,512)) . "','','Y','')";
                if (sgRunSQL($conn, $sql, $errors)) { echo 'OK   SYURLM sub ' . $fuid . '<br>'; $ins++; }
                else { echo 'ERR  SYURLM sub ' . $fuid . ' - ' . end($errors) . '<br>'; $err++; }
            }

            $sql = "SELECT COUNT(*) AS CNT FROM SYPORT WHERE FPPORT = '" . sgQ($code) . "' AND FPPAGE = '" . sgQ($code) . "' AND FPID = '" . sgQ($fuid) . "'";
            if (sgRowExists($conn, $sql)) {
                echo 'SKIP SYPORT sub ' . $fuid . '<br>'; $skip++;
            } else {
                $sql = "INSERT INTO SYPORT (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,FPTSTP,FPTSUS) VALUES ('" . sgQ(sgCap($code,20)) . "','" . sgQ(sgCap($code,20)) . "'," . $fpseq . ",'" . sgQ(sgCap($fuid,55)) . "','" . sgQ($fpdesc) . "','" . sgQ(sgCap($ftitl,50)) . "','','',CURRENT_TIMESTAMP,'" . sgQ(sgCap($dbUser,10)) . "')";
                if (sgRunSQL($conn, $sql, $errors)) { echo 'OK   SYPORT sub ' . $fuid . '<br>'; $ins++; }
                else { echo 'ERR  SYPORT sub ' . $fuid . ' - ' . end($errors) . '<br>'; $err++; }
            }
        }

        // Roles
        foreach ($roles as $role) {
            $sql = "SELECT COUNT(*) AS CNT FROM SYROLD WHERE RDROLE = '" . sgQ($role) . "' AND RDPORT = '" . sgQ($code) . "'";
            if (sgRowExists($conn, $sql)) {
                $skip++;
            } else {
                $sql = "INSERT INTO SYROLD (RDROLE,RDPORT,RDSEQN,RDRESV,RDTSTP,RDTSUS) VALUES ('" . sgQ(sgCap($role,10)) . "','" . sgQ(sgCap($code,20)) . "'," . $rdseq . ",'',CURRENT_TIMESTAMP,'" . sgQ(sgCap($dbUser,10)) . "')";
                if (sgRunSQL($conn, $sql, $errors)) { $ins++; }
                else { echo 'ERR  SYROLD ' . $role . ' -> ' . $code . ' - ' . end($errors) . '<br>'; $err++; }
            }
        }
        echo 'Roles for ' . $code . ': inserted=' . $ins . ' skip=' . $skip . '<br>';
    }

    echo '<hr><strong>Done. Inserted=' . $ins . ' Skipped=' . $skip . ' Errors=' . $err . '</strong><br>';
    if (!empty($errors)) {
        echo '<br>Errors:<br>';
        foreach ($errors as $e) { echo htmlspecialchars($e) . '<br>'; }
    }
    echo '<br><a href="?">Back to menu</a>';

} else {

    echo '<p>Ready to install 5 SG portals with 6 sub-menus each for ' . count($roles) . ' roles.</p>';
    echo '<p><a href="?action=install">Run Install Now</a></p>';
    echo '<p>All inserts are idempotent - safe to re-run.</p>';

}

file_put_contents('/tmp/sg_install_error.log', date('H:i:s') . " done\n", FILE_APPEND);

echo '</body></html>';
?>
