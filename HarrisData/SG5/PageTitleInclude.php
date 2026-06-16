<?php
print "\n <table $contentTable> ";
print "\n     <colgroup> <col width=\"80%\"><col width=\"15%\"> ";
print "\n     <tr><td><h1>$page_title</h1></td> ";
print "\n         <td class=\"toolbar\"> ";
require_once 'HelpPage.php';
if ($displayCloseIcon == "Y") {print "\n &nbsp;<a href=\"javascript:window.close()\">$closeImageMed</a> ";}
print "\n </td></tr></table> ";
