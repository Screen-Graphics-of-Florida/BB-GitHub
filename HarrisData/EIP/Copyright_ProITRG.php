<?php
print "<div class=\"copr\">";
$roleDesc = RetValue("RMROLE='{$activeRole}'", "SYROLM", "RMDESC");
print " &copy; Copyright 2014 Pro I.T. Resource Group, Inc. &nbsp; &nbsp;" .  date("l F dS Y");
require_once  'CurrentTime.php';
print " &nbsp; &nbsp; User: <span title=\"$userProfile\">$profileName</span> &nbsp; Role: <span title=\"$activeRole\">$roleDesc</span>";
print  " </div> \n";

if ($formatToPrint == "" || $formatToPrint == "N"){
	print "<div class=\"copr\">";
	// require_once 'HelpBook.php';
	require_once 'ProgSecurityUsageInquiry.php';
	print  " </div> \n";
}

if ($connect) {db2_close($connect);}

?>