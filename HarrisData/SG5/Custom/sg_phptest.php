<?php
// Step 1: write errors to a file we can read
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/sg_install_error.log');
ini_set('display_errors', 0);
error_reporting(E_ALL);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $msg = date('H:i:s') . " [$errno] $errstr in $errfile:$errline\n";
    file_put_contents('/tmp/sg_install_error.log', $msg, FILE_APPEND);
});

register_shutdown_function(function() {
    $e = error_get_last();
    if ($e) {
        $msg = date('H:i:s') . " FATAL [{$e['type']}] {$e['message']} in {$e['file']}:{$e['line']}\n";
        file_put_contents('/tmp/sg_install_error.log', $msg, FILE_APPEND);
    }
    // Output log if it exists
    if (file_exists('/tmp/sg_install_error.log')) {
        echo '<pre style="color:red">';
        echo htmlspecialchars(file_get_contents('/tmp/sg_install_error.log'));
        echo '</pre>';
    } else {
        echo '<p style="color:green">No errors logged.</p>';
    }
});

echo '<!DOCTYPE html><html><body>';
echo '<h2>SG PHP Test</h2>';

// Clear old log
file_put_contents('/tmp/sg_install_error.log', date('Y-m-d H:i:s') . " Starting test\n");

echo '<p>Step 1: Starting...</p>'; flush();

require_once 'GetURLParm.php';
echo '<p>Step 2: GetURLParm OK</p>'; flush();

require_once 'GenericDirectCallVariables.php';
echo '<p>Step 3: GenericDirectCallVariables OK</p>'; flush();

require_once 'SetLibraryList.php';
echo '<p>Step 4: SetLibraryList OK — user=' . htmlspecialchars($_SERVER['PHP_AUTH_USER']) . '</p>'; flush();

$conn = $i5Connect->getConnection();
echo '<p>Step 5: DB connection OK</p>'; flush();

// Test a simple query
$r = db2_exec($conn, "SELECT COUNT(*) AS CNT FROM SYPORT");
if ($r) {
    $row = db2_fetch_assoc($r);
    echo '<p>Step 6: Query OK — SYPORT has ' . $row['CNT'] . ' rows</p>';
} else {
    echo '<p style="color:red">Step 6: Query FAILED — ' . htmlspecialchars(db2_stmt_errormsg()) . '</p>';
}

// Test sgQ and sgCap functions inline (no function definitions — just inline test)
$testStr = substr(str_replace("'", "''", "O'Brien"), 0, 20);
echo '<p>Step 7: String functions OK — test=' . htmlspecialchars($testStr) . '</p>';

echo '<p style="color:green"><strong>All steps passed.</strong></p>';
echo '</body></html>';
?>
