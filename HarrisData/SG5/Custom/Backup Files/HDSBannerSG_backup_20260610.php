<?php
print "<!-- Top of page rapid navigation bar -->";
print  "\n<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" summary=\"banner\">";

/*print   "\n<colgroup>";
print          "<col class=\"menu\">";
print         "<col>";
print    "</colgroup>";*/

print "\n <tr class=\"bannerdiv\">";
print "<td colspan=\"2\">";
print     " \n <a href=\"http://www.screen-graphics.com\" title=\"Screen Graphics home page\" class=\"banner\">Home</a>";
print      "|";
print     "\n <a href=\"http://www.screen-graphics.com/contact\" title=\"Contact Screen Graphics\" class=\"banner\">Contact Us</a>";
print  "\n |";
print "\n <a href=\"https://www.harrisdata.com/\"><img width=\"34\" height=\"11\" src=\"icon/bannerZone.gif\" border=\"0\" alt=\"Zone - Login for HarrisData Business Application Software Customers\"></a>";
print "\n</td>";
print "</tr>";

$logo1 =  "$homePath" ."images/SGFL.gif";
if (file_exists($logo1)){$logo1Exists = "Y";}

/*$logo2 = "$homePath" ."images/hdlogo2-data.gif";
if (file_exists($logo2)){$logo2Exists = "Y";}*/

print "\n <tr>";
if ($logo1Exists=="Y"){
print "<td><img src=\"/images/SGFL.gif\" width=\"89\" height=\"32\" alt=\"Screen Graphics\" title=\"Screen Graphics \" border=\"0\"></td>";
}
if ($logo2Exists=="Y"){
print "<td><img src=\"images/hdlogo2-data.gif\" width=\"78\" height=\"32\" alt=\"HarrisData\" title=\"HarrisData\" border=\"0\"></td>";
}
print "</tr>";

print "\n </table>";

?>
