<?php

print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";

if ($sec_03 != "N" && $maintenanceCode == "C") {print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";}

$medIcon= "Y";
require_once 'HelpPage.php';
