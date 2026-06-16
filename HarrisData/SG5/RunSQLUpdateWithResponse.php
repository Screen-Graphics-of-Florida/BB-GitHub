<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$updateStmt    = $_GET['amp;updateStmt'];
$responseFlds    = $_GET['amp;responseFlds'];

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';

$status = db2_exec($i5Connect->getConnection (), $updateStmt);
print "|$responseFlds|";

?>