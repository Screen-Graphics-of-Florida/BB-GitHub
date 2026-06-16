<?php
$pos = strpos($page_title, "Search");
if ($pos !== false){$searchTitle = $page_title;}
else               {$searchTitle = "$page_title Search";}

print "\n <table $contentTable>  <colgroup>  <col width=\"89%\">  <col width=\"6%\">";
print "\n <tr><td><h1>$searchTitle</h1></td>";
print "\n <td class=\"toolbar\">";
print "<a href=\"javascript:document.Search.updateSearch.value='N'; check(document.Search)\">$goSearchImageLrg</a>";
require_once 'HelpPage.php';
print "\n </td> </tr> </table>";
?>