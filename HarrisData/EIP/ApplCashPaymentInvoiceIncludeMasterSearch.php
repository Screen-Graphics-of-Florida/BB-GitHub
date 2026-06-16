<?php

Build_AdvSrch_Entry("Freight","frFreight","toFreight","operFreight","opersel_num2_short","N","15","15");
Build_AdvSrch_Entry("Tax","frTax","toTax","operTax","opersel_num2_short","N","15","15");
Build_AdvSrch_Entry("Special Charge","frSpecCharge","toSpecCharge","operSpecCharge","opersel_num2_short","N","15","15");

if ($fromType=="P") {
	$operNbr = "operBillTo";
	print "\n <tr><td class=\"dsphdr\">Bill-To</td>";
	print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frBillTo\" size=\"7\" maxlength=\"7\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frBillTo&amp;fldDesc=frBillToName\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toBillTo\" size=\"7\" maxlength=\"7\">";
	print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toBillTo&amp;fldDesc=toBillToName\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n </tr>";

	Build_AdvSrch_Entry("Bill-To Name","srchBillToName","","operBillToName","opersel_alph_short","A","20","30");
}

$operNbr = "operTerms";
print "\n <tr><td class=\"dsphdr\">Terms</td>";
print "\n     <td>"; require "opersel_alph_short.php"; print "</td> ";
print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchTerms\" size=\"2\" maxlength=\"2\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}TermsCodeSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=srchTerms&amp;fldDesc=termsCodeDesc\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
print "\n </tr>";

Build_AdvSrch_Entry("Terms Description","srchTermsDesc","","operTermsDesc","opersel_alph_short","A","20","30");
Build_AdvSrch_Entry("Due Date","frDueDate","toDueDate","operDueDate","opersel_num2_short","D","6","6");
Build_AdvSrch_Entry("Invoice Date","frInvoiceDate","toInvoiceDate","operInvoiceDate","opersel_num2_short","D","6","6");
Build_AdvSrch_Entry("Reference Number","srchPONumber","","operPONumber","opersel_alph_short","A","10","22");
Build_AdvSrch_Entry("Order Number","frOEOrder","toOEOrder","operOEOrder","opersel_num2_short","N","8","8");
Build_AdvSrch_Entry("Order Date","frOEDate","toOEDate","operOEDate","opersel_num2_short","D","6","6");
Build_AdvSrch_Entry("Line Number","frOELine","toOELine","operOELine","opersel_num2_short","N","3","3");

$operNbr = "operPlant";
print "\n <tr><td class=\"dsphdr\">Plant</td>";
print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frPlant\" size=\"3\" maxlength=\"3\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frPlant&amp;fldDesc=frPlantName\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toPlant\" size=\"3\" maxlength=\"3\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toPlant&amp;fldDesc=toPlantName\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
print "\n </tr>";

Build_AdvSrch_Entry("Plant Name","srchPlantName","","operPlantName","opersel_alph_short","A","20","30");
Build_AdvSrch_Entry("Mfg Order","srchMfgOrder","","operMfgOrder","opersel_alph_short","N","9","9");
Build_AdvSrch_Entry("Invoice Amount","frInvoiceAmount","toInvoiceAmount","operInvoiceAmount","opersel_num2_short","N","15","15");

$operNbr = "operLocation";
print "\n <tr><td class=\"dsphdr\">Location</td>";
print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frLocation\" size=\"3\" maxlength=\"3\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frLocation&amp;fldDesc=frLocationDesc\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toLocation\" size=\"3\" maxlength=\"3\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}LocationSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toLocation&amp;fldDesc=toLocationDesc\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
print "\n </tr>";

Build_AdvSrch_Entry("Location Name","srchLocationName","","operLocationName","opersel_alph_short","A","20","30");

$operNbr = "operSalesman";
print "\n <tr><td class=\"dsphdr\">Salesman</td>";
print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frSalesman\" size=\"3\" maxlength=\"3\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frSalesman&amp;fldDesc=frSalesmanName\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toSalesman\" size=\"3\" maxlength=\"3\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}SalesmanSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toSalesman&amp;fldDesc=toSalesmanName\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
print "\n </tr>";

Build_AdvSrch_Entry("Salesman Name","srchSalesmanName","","operSalesmanName","opersel_alph_short","A","20","30");

$operNbr = "operShipTo";
print "\n <tr><td class=\"dsphdr\">Ship-To</td>";
print "\n     <td>"; require "opersel_num2_short.php"; print "</td>";
print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"frShipTo\" size=\"7\" maxlength=\"7\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=frShipTo&amp;fldDesc=frShipToName\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"toShipTo\" size=\"7\" maxlength=\"7\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}CustomerSearch.php{$genericVarBase}&amp;docName=Search&amp;fldName=toShipTo&amp;fldDesc=toShipToName\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
print "\n </tr>";

Build_AdvSrch_Entry("Ship-To Name","srchShipToName","","operShipToName","opersel_alph_short","A","20","30");
Build_AdvSrch_Entry("Last Posted Date","frLastPostedDate","toLastPostedDate","operLastPostedDate","opersel_num2_short","D","6","6");

$operNbr = "operSubCode";
print "\n <tr><td class=\"dsphdr\">Created By Payment Code</td>";
print "\n     <td>"; require "opersel_alph_short.php"; print "</td>";
print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srchSubCode\" size=\"4\" maxlength=\"4\">";
print "\n                             <a href=\"{$homeURL}{$phpPath}ARPmtSubCodeSearch.php{$scriptVarBase}&amp;docName=Search&amp;fldName=srchSubCode&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage</a></td> ";
print "\n </tr>";

Build_AdvSrch_Entry("Created By Payment Code Description","srchSubCodeDesc","","operSubCodeDesc","opersel_alph_short","A","20","50");
Build_AdvSrch_Entry("Invoice Code","srchInvoiceCode","","operInvoiceCode","opersel_alph_short","A","1","1");
?>
