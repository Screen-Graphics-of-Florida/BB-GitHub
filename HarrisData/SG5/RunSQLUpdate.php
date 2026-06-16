<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$updateStmt    = $_GET['amp;updateStmt'];

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';

$status = db2_exec($i5Connect->getConnection (), $updateStmt);
?>