<?php

require_once ($genericHead);
print "\n </head> <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\">";
if     ($scriptType == "S"){require ($searchBanner);}
elseif ($scriptType == "I"){require ($inquiryBanner);}
else                       {require_once 'Banner.php';}
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
if ($pageID != "") {print "<td class=\"menu\">"; Menu_Query($profileHandle, $dataBaseID, $portal, $pageID, $userProfile); print "</td>";}
print "\n <td class=\"content\">";
require 'SearchPageTitle.php';
if     ($scriptType == "S"){print $searchhrTagAttr;}
elseif ($scriptType == "I"){print $inquiryhrTagAttr;}
else                       {print $hrTagAttr;}
$wildCardResetURL = "{$baseURL}&amp;tag=WILDCARD&amp;chgSrch=C";
if ($wildCardDisplay != "") {
	print "\n <fieldset class=\"legendBody\">";
	print "\n <legend class=\"legendTitle\">Current Search Criteria</legend>";
	print "\n <table $contentTable>";
	print "\n <colgroup>";
	print "\n <col width=\"99%\">";
	print "\n <col width=\"1%\">";
	print "\n <tr><td class=\"toolbar\"><td>&nbsp;</td><td><a href=\"{$wildCardResetURL}\">$wildClearLrg</a></td></tr>";
	print "\n <tr><td class=\"searchcriteria\">$wildCardDisplay</td></tr>";
	print "\n </table>";
	print "\n </fieldset>";
}

print "\n <form class=\"formClass\" METHOD=POST NAME=\"Search\" onSubmit=\"return validate(document.Search)\" action=\"{$baseURL}&amp;tag=WILDCARD\">";

print "\n <fieldset class=\"legendBody\">";
print "<legend  class=\"legendTitle\">Refine Search Criteria</legend>";
print "\n <table $contentTable>";
print "\n <colgroup> <col width=\"89%\"> <col width=\"6%\">";
print "\n <tr><td class=\"searchCriteria\">";
print "\n <input type=\"hidden\" name=\"updateSearch\" value=\"Y\">";
if ($wildCardDisplay != ""){
	print "\n Add To Search:";
	print "\n <input type=\"radio\" name=\"andOr\" value=\"and\" CHECKED> And";
	print "<input type=\"radio\" name=\"andOr\" value=\"or\">Or &nbsp;";
}
print "\n </td>";
print "\n <td class=\"toolbar\">";
print "\n <a href={$baseURL}&amp;tag=MASTERSEARCH&amp;chgSrch=D>$wildDftLrg</a>";
print "\n <a href=\"javascript:document.Search.updateSearch.value='Y'; check(document.Search)\">$addToImage</a>";
print "\n </td> </tr> </table>";
print "\n <table $contentTable>";
print "<tr>";
print "\n <th class=\"dsphdr\">&nbsp;</th>";
print "\n <th class=\"dsphdr\">Operand</th>";
if ($fromToSearch == "Y"){
	print "\n <th class=\"dsphdr\">From</th>";
	print "\n <th class=\"dsphdr\">To</th>";
}else{
	print "\n <th class=\"dsphdr\">Search Data</th>";
}
print "</tr>";
?>