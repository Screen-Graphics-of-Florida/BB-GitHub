<?php
print "\n <table $contentTable>";
print "\n     <tr>";
print "\n         <td class=\"toolbar\">";
if (($sec_01 != "N" && ($maintenanceCode == "A" || $maintenanceCode == "Z")) || ($sec_02 != "N" && $maintenanceCode == "C") || ($maintenanceCode != "A" && $maintenanceCode != "C" && $maintenanceCode != "D" && $maintenanceCode != "Z")) {
	print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
}
if ($wfInstance > "0")  {print "\n <a onClick=\"return confirmCancelWF()\" href=\"$cancelWFURL\">$cancelImageMed</a>";}
elseif ($backURL != "") {print "\n <a href=\"$backURL\">$cancelImageMed</a>";}
else                    {print "\n <a href=\"javascript:history.back()\">$cancelImageMed</a>";}
if ($sec_03 != "N" && $maintenanceCode == "C") {
	print "\n <a onClick=\"return confirmDelete()\" href=\"$deleteURL\">$deleteImageMed</a>";
}
$medIcon= "Y";
require "HelpPage.php";
print "\n         </td>";
print "\n     </tr>";
print "\n </table>";
