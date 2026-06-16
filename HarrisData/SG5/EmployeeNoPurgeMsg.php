<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$docName        = $_GET['docName'];
$fldEmpl        = (isset($_GET['fldEmpl']))         ? $_GET['fldEmpl']        : null;
$fldEmplName    = (isset($_GET['fldEmplName']))     ? $_GET['fldEmplName']    : null;
$fldCo          = (isset($_GET['fldCo']))           ? $_GET['fldCo']          : null;
$fldFacl        = (isset($_GET['fldFacl']))         ? $_GET['fldFacl']        : null;
$fldHRCo        = (isset($_GET['fldHRCo']))         ? $_GET['fldHRCo']        : null;
$fldHREmpl      = (isset($_GET['fldHREmpl']))       ? $_GET['fldHREmpl']      : null;

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';


$page_title     = "Purge Option Not Available";
$scriptName     = "EmployeeNoPurgeMsg.php";
$scriptVarBase  = "{$genericVarBase}&amp;docName=" . urlencode(trim($docName)) . "&amp;fldEmpl=" . urlencode(trim($fldEmpl)). "&amp;fldEmplName=" . urlencode(trim($fldEmplName)) . "&amp;fldCo=" . urlencode(trim($fldCo)). "&amp;fldFacl=" . urlencode(trim($fldFacl)) . "&amp;fldHRCo=" . urlencode(trim($fldHRCo)). "&amp;fldHREmpl=" . urlencode(trim($fldHREmpl));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL      = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;


$maxRows = $dspMaxRows;
if (is_null($tag)) {$tag="REPORT";}

if ($tag == "REPORT") {

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
$formName = "Inquiry";

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function selectEmpl(emplNum,emplDesc,coNum,faclNum,coHRNum,emplHRNum){ ";
if ($fldEmpl!=null) {
	print "\n if (window.opener.document.$docName.$fldEmpl) ";
	print "\n    {window.opener.document.$docName.$fldEmpl.value = emplNum;} ";
}
if ($fldEmplName!=null) {
	print "\n if (window.opener.document.$docName.$fldEmplName) { ";
	print "\n    window.opener.document.$docName.$fldEmplName.value = emplDesc; ";
	print "\n } else if (window.opener.document.getElementById('$fldEmplName')) { ";
	print "\n    window.opener.document.getElementById('$fldEmplName').innerHTML = emplDesc; ";
	print "\n } ";
}
if ($fldCo!=null) {
	print "\n if (window.opener.document.$docName.$fldCo) ";
	print "\n    {window.opener.document.$docName.$fldCo.value = coNum;} ";
}
if ($fldFacl!=null) {
	print "\n if (window.opener.document.$docName.$fldFacl) ";
	print "\n    {window.opener.document.$docName.$fldFacl.value = faclNum;} ";
}

if ($fldHRCo!=null) {
	print "\n if (window.opener.document.$docName.$fldHRCo) ";
	print "\n    {window.opener.document.$docName.$fldHRCo.value = coHRNum;} ";
}
if ($fldHREmpl!=null) {
	print "\n if (window.opener.document.$docName.$fldHREmpl) ";
	print "\n    {window.opener.document.$docName.$fldHREmpl.value = emplHRNum;} ";
}
	print "\n window.opener.document.$docName.focus(); ";

	print "\n window.opener.document.$docName.$fldEmpl.focus(); ";


print "\n window.close(); ";
print "\n } ";

require_once 'AJAXRequest.js';
require_once 'CheckEnterSearch.php';
require_once 'CheckSel.js';
require_once 'NoFormValidate.php';
require_once 'SaveCurrentURL.php';
require_once 'ShowHideSelCriteria.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkEnterSearch()\" onBlur=\"window.close()\">";
require_once ($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";
require_once 'PageTitleInclude.php';
print $searchhrTagAttr;

$uv_CompanyName ="EMCOMP";
require 'UserViewEmpl.php';
require 'UserView.php';

	$F_CoFac=Format_CoFac($fldCo,$fldFacl,"N");
	
print "\n <a name=\"ShowEmpl\"></a> ";
	
	
	print "\n     <table $contentTable> ";
	print "\n     <td class=\"colalph\"><a href=\"javascript:selectEmpl('" . trim($fldEmpl) . "','" . trim($fldEmplName) . "','" . trim($fldCo) . "','" . trim($fldFacl) . "','" . trim($fldHRCo) . "','" . trim($fldHREmpl) . "')\" title=\"Select Employee\">$row[EMLNAM]</a></td> ";
	print "\n <tr><td class=\"dsphdr\">Company/Facility</td>";
	print "\n     <td class=\"colalph\">$F_CoFac</td></tr>";
	print "\n <tr><td class=\"dsphdr\">Employee</td>";
	print "\n     <td class=\"colalph\">$fldEmpl</td></tr>";
	print "\n <tr><td class=\"dsphdr\">HR Company</td>";
	print "\n     <td class=\"colalph\">$fldHRCo</td></tr>";
	print "\n <tr><td class=\"dsphdr\">HR Employee</td>";
	print "\n     <td class=\"colalph\">$fldHREmpl</td></tr>";
	print "\n <tr><td class=\"dsphdr\">Employee Name</td>";
	print "\n     <td class=\"colalph\">$fldEmplName</td></tr>";
	print "\n <tr><td class=\"dsphdr\">. </td></tr>";
	print "\n <tr><td class=\"dsphdr\">. </td></tr>";
	
	print "\n     </table> ";
	


print "\n <a name=\"ShowText\"></a> ";
	
	print "\n     <table $contentTable> ";
	print "\n      <tr><td class=\"warningtext\">The purge option is not available for this employee.</td></tr>";
	print "\n      <tr><td class=\"warningtext\">.</td></tr>";
	print "\n      <tr><td class=\"warningtext\">If Manufacturing, Employee Time, or both applications are installed</td></tr>";
	print "\n      <tr><td class=\"warningtext\">--OR--</tr>";
	print "\n      <tr><td class=\"warningtext\">if Employee Audit is not turned on,</td></tr>";
	print "\n      <tr><td class=\"warningtext\">the employee must be purged using the Purge Utility found in the Utilities/HRIS portal. </td></tr>";
	print "\n      <tr><td class=\"warningtext\">.</td></tr>";
	print "\n      <tr><td class=\"warningtext\">Refer to the Housekeeping section of the HRIS Manual for more information regarding </td></tr>";
	print "\n      <tr><td class=\"warningtext\">Employee Purge.</td></tr>";
	print "\n      <tr><td class=\"warningtext\">.</td></tr>";
	print "\n      <tr><td class=\"warningtext\">For more information on Employee Audit, refer to the Audit Control section of the </td></tr>";
	print "\n      <tr><td class=\"warningtext\">Control Panel Plus manual.</td></tr>";
	print "\n     </table> ";
	

	



require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($searchTrailer);
print "\n </body> \n </html>";
}
?>	
