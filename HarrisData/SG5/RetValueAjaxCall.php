<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$selWhere    = $_GET['amp;selWhere'];
$selTable    = $_GET['amp;selTable'];
$selColumn   = $_GET['amp;selColumn'];
$returnValue = "";

require_once 'SetLibraryList.php';
require_once 'GenericDirectCallVariables.php';

if (is_null($selWhere)) $selWhere="";
if (is_null($selTable))   $selTable="";
if (is_null($selColumn))  $selColumn="";

$returnValue=RetValue($selWhere,$selTable,$selColumn);
print "|$returnValue|";

?>