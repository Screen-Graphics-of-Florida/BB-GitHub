<?php

print "\n <a name=\"CurrentFilterCriteria\"></a> ";
if ($wildCardDisplay != "") {
	print "\n <fieldset class=\"legendBody\">";
	print "\n     <legend class=\"legendTitle\">Current Filter Criteria</legend>";
	require 'TopOfForm.php';
	print "\n     <table $contentTable>";
	print "\n         <colgroup><col width=\"99%\"><col width=\"1%\">";
	print "\n         <tr><td class=\"toolbar\"><td>&nbsp;</td><td><a href=\"javascript:document.Chg.updateSearch.value='C'; check(document.Chg){$wildCardResetURL}\">$wildClearLrg</a></td></tr>";
	print "\n         <tr><td class=\"searchcriteria\">$wildCardDisplay</td></tr>";
	print "\n     </table>";
	print "\n </fieldset>";
}

print "\n <a name=\"RefineFilterCriteria\"></a> ";
print "\n <fieldset class=\"legendBody\">";
print "\n     <legend  class=\"legendTitle\">Refine Filter Criteria</legend>";
require 'TopOfForm.php';
print "\n     <table $contentTable>";
print "\n         <colgroup> <col width=\"89%\"> <col width=\"1%\">";
print "\n        <tr><td class=\"searchCriteria\">";
print "\n            <input type=\"hidden\" name=\"updateSearch\" value=\"\">";
if ($wildCardDisplay != ""){
	print "\n        Add To Filter:";
	print "\n        <input type=\"radio\" name=\"andOr\" value=\"and\" CHECKED> And";
	print "          <input type=\"radio\" name=\"andOr\" value=\"or\">Or &nbsp;";
}
print "\n            </td>";
print "\n            <td class=\"toolbar\">";
print "\n                <a href=\"javascript:document.Chg.updateSearch.value='Y'; check(document.Chg)\">$addToImage</a>";
print "\n            </td> </tr> </table>";
print "\n     <table $contentTable>";
print "\n         <tr>";
print "\n             <th class=\"dsphdr\">&nbsp;</th>";
print "\n             <th class=\"dsphdr\">Operand</th>";
if ($fromToSearch == "Y"){
	print "\n         <th class=\"dsphdr\">From</th>";
	print "\n         <th class=\"dsphdr\">To</th>";
}else{
	print "\n         <th class=\"dsphdr\">Filter Data</th>";
}
print "\n         </tr>";

?>