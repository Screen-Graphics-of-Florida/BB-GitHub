<?php
// Temporary SeidenPHP migration diagnostic -- delete when the cutover is done.
header('Content-Type: text/plain');

echo "php version        : " . PHP_VERSION . "\n";
echo "sapi               : " . php_sapi_name() . "\n";
echo "ini loaded file    : " . var_export(php_ini_loaded_file(), true) . "\n";
echo "ini scanned files  : " . var_export(php_ini_scanned_files(), true) . "\n";
echo "PHPRC env          : " . var_export(getenv('PHPRC'), true) . "\n";
echo "\n";
echo "auto_prepend_file  : " . var_export(ini_get('auto_prepend_file'), true) . "\n";
echo "HDToolkitPath set  : " . var_export(isset($HDToolkitPath) ? $HDToolkitPath : false, true) . "\n";
echo "\n";
echo "memory_limit       : " . ini_get('memory_limit') . "   (want 256M)\n";
echo "max_execution_time : " . ini_get('max_execution_time') . "   (want 300)\n";
echo "post_max_size      : " . ini_get('post_max_size') . "   (want 512M)\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "   (want 512M)\n";
echo "include_path       : " . ini_get('include_path') . "\n";
echo "\n";
echo "ibm_db2 loaded     : " . var_export(extension_loaded('ibm_db2'), true) . "\n";
echo "Seiden CW readable : " . var_export(is_readable('/QOpenSys/pkgs/lib/php/ToolkitApi/CW/cw.php'), true) . "\n";
echo "instance php.ini   : " . var_export(is_readable('/www/sg5eip/conf/php.ini'), true) . "\n";
echo "prepend file       : " . var_export(is_readable('/www/sg5eip/conf/hd_toolkit_path.php'), true) . "\n";
echo "toolkit.ini        : " . var_export(is_readable('/QOpenSys/pkgs/lib/php/ToolkitApi/toolkit.ini'), true) . "\n";
echo "\n";
echo "i5_connect exists  : " . var_export(function_exists('i5_connect'), true) . "\n";
echo "auth user          : " . var_export(isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null, true) . "\n";
