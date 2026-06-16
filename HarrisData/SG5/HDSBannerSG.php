
<!-- from https://stackoverflow.com/questions/449788/http-authentication-logout-via-php -->
<script type="text/javascript">
    function logout() {
        var xmlhttp;
        if (window.XMLHttpRequest) {
            xmlhttp = new XMLHttpRequest();
        }
        // code for IE
        else if (window.ActiveXObject) {
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        if (window.ActiveXObject) {
            // IE clear HTTP Authentication
            document.execCommand("ClearAuthenticationCache");
            window.location.href='https://www.screen-graphics.com/';
        } else {
            xmlhttp.open("GET", '/path/that/will/return/200/OK', true, "logout", "logout");
            xmlhttp.send("");
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4) {window.location.href='https://www.screen-graphics.com/';}
            }
        }
        return false;
    }
</script>

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
print  "|";
print     "\n <a href = \"#\" onclick=\"logout();\" title=\"Logout\" class=\"banner\">Logout</a>";
print "\n</td>";
print "</tr>";

$logo1 =  "$homePath" ."images/SGFL.gif";
if (file_exists($logo1)){$logo1Exists = "Y";}

/*$logo2 = "$homePath" ."images/hdlogo2-data.gif";
if (file_exists($logo2)){$logo2Exists = "Y";}*/

print "\n <tr>";
if ($logo1Exists=="Y"){
print "<td><img src=\"images/SGFL.gif\" width=\"89\" height=\"32\" alt=\"Screen Graphics\" title=\"Screen Graphics \" border=\"0\"></td>";
}
if ($logo2Exists=="Y"){
print "<td><img src=\"images/hdlogo2-data.gif\" width=\"78\" height=\"32\" alt=\"HarrisData\" title=\"HarrisData\" border=\"0\"></td>";
}
print "</tr>";

print "\n </table>";

?>
