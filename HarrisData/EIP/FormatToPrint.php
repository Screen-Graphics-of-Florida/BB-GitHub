<?php
if ($medIcon == "Y") {$printIcon = $formatPrintMed;} else {$printIcon = $formatPrintDesc;}
print "\n <a href=\"{$baseURL}&amp;tag=REPORT&amp;formatToPrint=Y\" target=\"_blank\">$printIcon</a>";
?>