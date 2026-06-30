<?php
ini_set("display_errors", "1");
ini_set("log_errors", "1");
error_reporting(E_ALL);
echo "PHP_VERSION=" . PHP_VERSION . "
";
echo "display_errors=" . ini_get("display_errors") . "
";
echo "error_reporting=" . ini_get("error_reporting") . "
";
echo "OK";
?>