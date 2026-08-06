<?php
// PhpVer.php — one-line interpreter probe. Safe, read-only, no DB.
header("Content-Type: text/plain");
echo "PHP_VERSION   : " . PHP_VERSION . "\n";
echo "SERVER_PORT   : " . (isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : "?") . "\n";
echo "SAPI          : " . php_sapi_name() . "\n";
echo "php.ini       : " . php_ini_loaded_file() . "\n";
echo "DOCUMENT_ROOT : " . (isset($_SERVER["DOCUMENT_ROOT"]) ? $_SERVER["DOCUMENT_ROOT"] : "?") . "\n";
echo "db2 ext       : " . (extension_loaded("ibm_db2") ? "yes" : "NO") . "\n";
echo "short arrays  : ok (this file uses none)\n";
