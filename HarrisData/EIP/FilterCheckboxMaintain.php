<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound        = (isset($_GET['errFound'])) ? $_GET['errFound']    : "";

$fromTblID    = (isset($_GET['fromTblID']))     ? $_GET['fromTblID']     : 0;
$fromPagID    = (isset($_GET['fromPagID']))     ? $_GET['fromPagID']     : 0;
$tableName    = (isset($_GET['tableName']))     ? $_GET['tableName']     : "";
$tableDesc    = (isset($_GET['tableDesc']))     ? $_GET['tableDesc']     : "";
$pageDesc     = (isset($_GET['pageDesc']))      ? $_GET['pageDesc']      : "";
$checkboxID   = (isset($_GET['checkboxID']))    ? $_GET['checkboxID']    : 0;

require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Filter Checkbox Maintenance";
$scriptName     = "FilterCheckboxMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromTblID=" . urlencode($fromTblID) . "&amp;fromPagID=" . urlencode($fromPagID) . "&amp;tableName=" . urlencode($tableName) . "&amp;tableDesc=" . urlencode($tableDesc) . "&amp;pageDesc=" . urlencode($pageDesc) . "&amp;checkboxID=" . urlencode($checkboxID);
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "hsyxxx";
$backURL="{$homeURL}{$phpPath}FilterCheckbox.php{$scriptVarBase}&amp;tag=REPORT";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

$stmtSQL = "Select DSXML From SYDCST Where DSTBID=$fromTblID";
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
if (is_resource ( $sqlResult )) {
	$row = db2_fetch_array ( $sqlResult );
	$xmlDocset = simplexml_load_string ( $row[0] );
}

$docsetRow = $xmlDocset->row;
foreach ($docsetRow->col as $col) {
	$colRelated = (string) $col->related_column_1;
	if ($colRelated == "") {
		$colHeading = (string) $col[0]->label;
		$col_sort[$colHeading]= trim(strtoupper($col['id']));
	}
}
ksort($col_sort);

if ($tag == "MAINTAIN") {

	$editVariables = " editNum(document.Chg.dspSequence, 2, 1) ";
	foreach ($col_sort as $colName) {
		$col  = $docsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
		$coldType   = (string) $col[0]->data_type;
		$colfType   = (string) $col[0]->flag_type;
		$colHeading = (string) $col[0]->label;
		$colSize    = (string) $col[0]->length;
		$colDecm    = (string) $col[0]->decimal;
		$colName = str_replace("#", "_", $colName);
		if ($coldType == "NUMERIC" || $coldType == "DECIMAL" || $coldType == "DATE") {
			if ($editVariables) {$editVariables .= " &&";}
			if ($colfType == "CYMD" || $colfType == "ISO") {
				$editVariables .= " editdate(document.Chg.srch$colName) ";
			} else {
				$editVariables .= " editNum(document.Chg.srch$colName,$colSize,$colDecm) ";
			}
		}
	}

	require_once ($docType);
	print "\n <html> <head>";
	$formName = "Chg";
	require_once ($headInclude);

	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'DateEdit.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.labelText.value ==\"\" ||";
	print "\n     document.Chg.dspSequence.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if ($editVariables) ";
	print "\n return true;";
	print "\n }";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr>";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "FILTERCHECKBOXMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select  * From SYTBFC ";
		$stmtSQL .= " Where TVTBID=$fromTblID and TVPGID=$fromPagID and TVCBID=$checkboxID ";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hsyxxx_OPT=pgmOptSecurity($profileHandle, $dataBaseID, "hsyxxx");
	$sec_01=$hsyxxx_OPT['sec_01'];
	$sec_02=$hsyxxx_OPT['sec_02'];
	$sec_03=$hsyxxx_OPT['sec_03'];
	$sec_04=$hsyxxx_OPT['sec_04'];
	require_once 'MaintainTop.php';
	print "<table $contentTable>";
	Format_Header_URL("Table", $tableDesc, $tableName, "{$homeURL}{$cGIPath}Table.d2w/REPORT{$altVarBase}&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']));
	if ($fromPagID) {
		Format_Header_URL("Page", $pageDesc, "", "{$homeURL}{$cGIPath}Page.d2w/REPORT{$altVarBase}&amp;tableName=" . urlencode($tableName). "&amp;tableDesc=" . urlencode($tableDesc) . "&amp;tblID=" . urlencode($fromTblID) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']));
	}
	print "\n </table>";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "" || $maintenanceCode == "A" || $chgSrch) {
		if ($errFound == "" && $maintenanceCode == "A" && !$chgSrch) {
			$edtVar= "";
			$focusField="labelText";
		} else {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_TVLABL=DecatErr_Field("@@labl", "labelText");
			$Err_TVDSEQ=DecatErr_Field("@@dseq", "dspSequence");
			$Err_TVDFTV=DecatErr_Field("@@dftv", "dftValue");
			$Err_TVDFTV=DecatErr_Field("@@grpn", "groupName");
		}

		$row['TVLABL']=Decat_Field("@@labl", $edtVar);
		$row['TVDSEQ']=Decat_Field("@@dseq", $edtVar);
		$row['TVDFTV']=Decat_Field("@@dftv", $edtVar);
		$row['TVGRPN']=Decat_Field("@@grpn", $edtVar);
		$row['TVFILD']=Decat_Field("@@fild", $edtVar);
		$row['TVFILV']=Decat_Field("@@filv", $edtVar);
		$errFound= "";

	} else {
		$focusField="";
	}

	$dftChecked=Field_Checked($row['TVDFTV'], "1");

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\"  onSubmit=\"return validate(document.Chg)\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode($maintenanceCode) . "\">";
	print "\n <table $contentTable>";

	Build_Fld_Entry("Label","labelText","inputalph","","TVLABL",$row[TVLABL],$Err_TVLABL,"50","100","Y","","");
	Build_Fld_Entry("Display Sequence","dspSequence","inputnmbr","","TVDSEQ",$row[TVDSEQ],$Err_TVDSEQ,"10","4","Y","","");
	Build_Fld_Entry("Group","groupName","inputalph","","TVGRPN",$row[TVGRPN],$Err_TVGRPN,"10","10","","","");

	print "\n <tr><td class=\"dsphdr\">Default</td>";
	print "\n     <td class=\"inputalph\">";
	print "\n       <input type=\"checkbox\" name=\"dftValue\" value=\"1\" $dftChecked>";
	print "\n       <input type=\"hidden\" name=\"srchText\" value=\"" . trim($row['TVFILD']) . "\">";
	print "\n       <input type=\"hidden\" name=\"srchSQL\" value=\"" . trim($row['TVFILV']) . "\">";
	print "\n     </td>";
	print "\n </tr>";

	print "\n </table> ";

	if (trim($row['TVFILD']) != "") {
		print "\n <fieldset class=\"legendBody\">";
		print "\n <legend class=\"legendTitle\">Current Search Criteria</legend>";
		print "\n <table $contentTable>";
		print "\n <colgroup>";
		print "\n <col width=\"99%\">";
		print "\n <col width=\"1%\">";
		print "\n <tr><td class=\"toolbar\"><td>&nbsp;</td><td><a href=\"javascript:document.Chg.updateSearch.value='C';check(document.Chg)\">$wildClearLrg</a></td></tr>";
		print "\n <tr><td class=\"searchcriteria\">" . trim($row['TVFILD']) . "</td></tr>";
		print "\n </table>";
		print "\n </fieldset>";
	}

	print "\n <fieldset class=\"legendBody\">";
	print "<legend  class=\"legendTitle\">Refine Search Criteria</legend>";
	print "\n <table $contentTable>";
	print "\n <colgroup> <col width=\"99%\"> <col width=\"1%\">";
	print "\n <tr><td class=\"searchCriteria\">";
	print "\n <input type=\"hidden\" name=\"updateSearch\" value=\"\">";
	if ($row['TVFILD'] != ""){
		print "\n Add To Search:";
		print "\n <input type=\"radio\" name=\"andOr\" value=\"and\" CHECKED> And";
		print "<input type=\"radio\" name=\"andOr\" value=\"or\">Or &nbsp;";
	}
	print "\n </td>";
	print "\n <td class=\"toolbar\">";
	print "\n <a href=\"javascript:document.Chg.updateSearch.value='Y';check(document.Chg)\">$addToImage</a>";
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

	foreach ($col_sort as $colName) {
		$col  = $docsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
		$coldType   = (string) $col[0]->data_type;
		$colfType   = (string) $col[0]->flag_type;
		$colHeading = (string) $col[0]->label;
		$colSize    = (string) $col[0]->length;
		$maxWidth   = ($coldType == "DATE") ? "6" : $colSize;
		$colName = str_replace("#", "_", $colName);
		if (!$focusField) {
			$focusField = "srch$colName";
		}
		$colSize = ($maxWidth > "12") ? "12" : $maxWidth;
		print "\n <tr><td class=\"dsphdr\">$colHeading</td>";
		if ($colfType == "PHONE") {
			print "\n <td>&nbsp;</td>";
			print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"srch$colName\" size=\"$colSize\" maxlength=\"$maxWidth\"></td>";
		} elseif ($colfType != "") {
			$operNbr = "oper$colName";
			if ($coldType == "NUMERIC") {print "\n <td>";  require "opersel_num_short.php"; print "</td>";}
			else                        {print "\n <td>";  require "opersel_alph_short.php"; print "</td>";}
			print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"srch$colName\" id=\"srch$colName\" value=\"" . rtrim($fldValue) . "\" size=\"$colSize\" maxlength=\"$maxWidth\"> ";
			print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=$colfType&amp;flagSrchHdr=". urlencode($fldDesc) . "&amp;fldName=srch{$colName}&amp;fldDesc=srch{$colName}Desc\" onclick=\"$searchWinVar\"> $searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"srch{$colName}Desc\">" .trim($fieldDesc) . "</span></td>";
		} elseif ($coldType == "NUMERIC" || $coldType == "DECIMAL" || $coldType == "DATE") {
			$operNbr = "oper$colName";
			print "\n <td>";  require "opersel_num_short.php"; print "</td>";
			print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"srch$colName\" size=\"$colSize\" maxlength=\"$maxWidth\">";
			if ($colfType == "CYMD" || $colfType == "ISO") {
				print "\n <a href=\"javascript:calWindow('srch$colName');\">$calendarImage</a>";
			}
			print "\n </td>";
		} else {
			$operNbr = "oper$colName";
			print "\n <td>"; require "opersel_alph_short.php"; print "</td>";
			print "\n <td class=\"inputalph\"><input type=\"text\" name=\"srch$colName\" size=\"$colSize\" maxlength=\"$maxWidth\"></td>";
		}
		print "\n </tr>";
	}
	print "\n </table>";
	print "\n <a href=\"javascript:document.Chg.updateSearch.value='Y';check(document.Chg)\">$addToImage</a>";
	print "\n </fieldset>";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.$focusField.focus();";
	print "\n </script>";
	print "\n </form>";
	require_once 'MaintainBottom.php';
	print $hrTagAttr;
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {
	if ($maintenanceCode=="D" && !isset($_POST['labelText'])) {
		$_POST['labelText']    = RetValue("TVTBID=$fromTblID and TVPGID=$fromPagID and TVCBID=$checkboxID", "SYTBFC", "TVLABL");
		$_POST['dspSequence']  = RetValue("TVTBID=$fromTblID and TVPGID=$fromPagID and TVCBID=$checkboxID", "SYTBFC", "TVDSEQ");
	}

	$edtVar= "";
	Concat_Field("@@tbid", $fromTblID);
	Concat_Field("@@pgid", $fromPagID);
	Concat_Field("@@cbid", $checkboxID);

	Concat_Field("@@labl", $_POST['labelText']);
	Concat_Field("@@dseq", $_POST['dspSequence']);
	Concat_Field("@@grpn", $_POST['groupName']);
	if (!(isset($_POST['dftValue']))) {$_POST['dftValue']='0';}
	Concat_Field("@@dftv", $_POST['dftValue']);

	$updateSearch = (isset($_POST['updateSearch'])) ? $_POST['updateSearch'] : null;
	if ($updateSearch) {
		$andOr           = (isset($_POST['andOr'])) ? $_POST['andOr'] : null;
		$wildCardTemp    = "";
		$wildDisplayTemp = "";
		$wildCardSearch  = $_POST['srchSQL'];
		$wildCardDisplay = $_POST['srchText'];
		foreach ($col_sort as $colName) {
			if (strlen($_POST["srch$colName"]) == 0) {continue;}
			$col  = $docsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
			$coldType   = (string) strtoupper($col[0]->data_type);
			$colfType   = (string) $col[0]->flag_type;
			$colHeading = (string) $col[0]->label;
			$coldftVal  = (string) $col[0]->default;
			$colAltSort = (string) $col[0]->alt_sort;
			$selName    = ($curAltSort)? $curAltSort : $colName;
			$colName    = str_replace("#", "_", $colName);

			if ($colfType == "PHONE" && $coldType == "CHAR") {
				$returnValue = Build_Filter($selName, $colHeading, $_POST["srch$colName"], "", "", "PA");
			} elseif ($colfType == "PHONE") {
				$returnValue = Build_Filter($selName, $colHeading, $_POST["srch$colName"], "", "", "P");
			} elseif ($colfType == "ISO" && $coldftVal == "NULL") {
				$returnValue = Build_Filter("coalesce($selName,'0001-01-01')", $colHeading, $_POST["srch$colName"], "", $_POST["oper$colName"], "I");
			} elseif ($colfType == "ISO") {
				$returnValue = Build_Filter($selName, $colHeading, $_POST["srch$colName"], "", $_POST["oper$colName"], "I");
			} elseif ($colfType == "CYMD") {
				$returnValue = Build_Filter($selName, $colHeading, $_POST["srch$colName"], "", $_POST["oper$colName"], "D");
			} elseif ($colfType == "PERIOD") {
				$returnValue = Build_Filter($selName, $colHeading, $_POST["srch$colName"], "", $_POST["oper$colName"], "DP");
			} elseif ($coldType == "NUMERIC" || $coldType == "DECIMAL") {
				$returnValue = Build_Filter($selName, $colHeading, $_POST["srch$colName"], "", $_POST["oper$colName"], "N");
			} else {
				$returnValue = Build_Filter($selName, $colHeading, $_POST["srch$colName"], "U", $_POST["oper$colName"], "A");
			}

		}
		$wildCardTemp    = $returnValue['wildCardTemp'];
		$wildDisplayTemp = $returnValue['wildDisplayTemp'];
		$wildCardSearch  = $returnValue['wildCardSearch'];
		if ($wildCardTemp != ""){
			$wildCardSearch  .= $wildCardTemp;
			$wildCardSearch  .= "))";
			$wildCardDisplay .= $wildDisplayTemp;
		}
		if ($updateSearch == "C") {
			$wildCardSearch = "";
			$wildCardDisplay = "";
		}
		$_POST['srchSQL'] = $wildCardSearch;
		$_POST['srchText'] = $wildCardDisplay;
		$chgSrch = $updateSearch;
	}
	Concat_Field("@@filv", $_POST['srchSQL']);
	Concat_Field("@@fild", $_POST['srchText']);
	$edtVar .= "}{";
	if ($maintenanceCode == "Z") {$maintenanceCode = "A";}

	if ($errFound != "" || $chgSrch) {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=" . urlencode($maintenanceCode) . "&amp;errFound=" . urlencode($errFound) . "&amp;chgSrch=" . urlencode($chgSrch) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . " \"> ";
	} else {
		$_POST['srchSQL'] = str_replace("'", "''", $_POST['srchSQL']);
		if ($maintenanceCode == "A") {
			require 'stmtSQLClear.php';
			$stmtSQL .= " Insert Into SYTBFC (TVTBID,TVPGID,TVCBID,TVDSEQ,TVGRPN,TVLABL,TVDFTV,TVFILV,TVFILD,TVLABLU,TVTSUS,TVTSPT) ";
			$stmtSQL .= " Select $fromTblID,$fromPagID,(coalesce(max(TVCBID),0)+1),{$_POST['dspSequence']},'{$_POST['groupName']}','{$_POST['labelText']}','{$_POST['dftValue']}','{$_POST['srchSQL']}','{$_POST['srchText']}','" . strtoupper($_POST['labelText']) . "','$userProfile','Y' ";
			$stmtSQL .= " From SYTBFC Where TVTBID=$fromTblID and TVPGID=$fromPagID ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		} elseif ($maintenanceCode == "C") {
			require 'stmtSQLClear.php';
			$stmtSQL .= " Update SYTBFC ";
			$stmtSQL .= " Set TVDSEQ={$_POST['dspSequence']},TVGRPN='{$_POST['groupName']}',TVLABL='{$_POST['labelText']}',TVDFTV='{$_POST['dftValue']}',TVFILV='{$_POST['srchSQL']}',TVFILD='{$_POST['srchText']}',TVLABLU='" . strtoupper($_POST['labelText']) . "',TVTSTP=CURRENT_TIMESTAMP,TVTSUS='$userProfile',TVTSPT='Y' ";
			$stmtSQL .= " Where TVTBID=$fromTblID and TVPGID=$fromPagID and TVCBID=$checkboxID ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		} elseif ($maintenanceCode == "D") {
			require 'stmtSQLClear.php';
			$stmtSQL .= " Delete From SYTBFC Where TVTBID=$fromTblID and TVPGID=$fromPagID and TVCBID=$checkboxID ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		}

		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "{$_POST['labelText']}", "{$_POST['dspSequence']}" , "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}FilterCheckbox.php{$scriptVarBase}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	}
}

function Build_Filter ($fldName, $fldDesc, $selData, $upperCase, $operand, $fldType){
	global $wildSearchDft, $wildCardTemp, $wildCardSearch, $wildDisplayTemp, $wildCardDisplay, $andOr;

	$selData = trim($selData);
	if ($fldType!="N") {$selData = str_replace("'", "''", $selData);}

	if ($operand == "LIKE" && ($wildSearchDft == "1" || $wildSearchDft == "2" || $wildSearchDft == "3")){
		$wildPos = strpos($selData,"*");
		if ($wildPos === false){$wildPos =  strpos($selData,"?");}
		if ($wildPos === false){
			if       ($wildSearchDft == "1"){$selData = "*{$selData}";
			} elseif ($wildSearchDft == "2"){$selData = "{$selData}*";
			} elseif ($wildSearchDft == "3"){$selData = "*{$selData}*";}
		}
	}

	if ($upperCase == "U"){$selData = strtoupper($selData);}

	if ($operand == "<>"){$displayOper = "Not=";
	} else               {$displayOper = $operand;}

	if ($andOr == ""){$andOr = "or";}

	if ($wildCardTemp == ""){
		if ($wildCardSearch == ""){
			$wildCardSearch  = " ( (";
			$wildDisplayTemp = "&nbsp;";
		} else {
			$wildPos = strrpos($wildCardSearch, ")");
			if ($wildPos !== false) {$wildCardSearch = substr($wildCardSearch, 0, $wildPos);}
			$wildCardSearch .= " {$andOr} (";
		}
	}

	if ($wildCardTemp != ""){
		$wildCardTemp    .= " and ";
		$wildDisplayTemp .= " and ";
	} elseif ($wildCardDisplay != ""){
		$wildDisplayTemp .= " <br> $andOr ";
	}

	if ($fldType == "A"){
		if ($selData == "") {$wildDisplayTemp .= "$fldDesc $displayOper '' ''";}
		else {$wildDisplayTemp .= "$fldDesc $displayOper $selData";}
		$selData=str_replace("?", "_", $selData);
		$selData=str_replace("*", "%", $selData);
		$wildCardTemp    .= "$fldName $operand '$selData'";

	} elseif ($fldType == "D"){
		$selDate = substr($selData, 0, 2) . $dateEdit . substr($selData, 2, 2) . $dateEdit . substr($selData, 4, 2);
		$selData = DateToCYMD($selData);
		$wildDisplayTemp .= "$fldDesc $displayOper $selDate";
		$wildCardTemp    .= "$fldName $operand $selData";

	} elseif ($fldType == "DP"){
		$selDate = $selData;
		$selDate = substr($selDate, 0, 2) . $dateEdit . substr($selData, 2, 2);
		$selData = PeriodToCYP($selData);
		$wildDisplayTemp .= "$fldDesc $displayOper $selDate";
		$wildCardTemp    .= "$fldName $operand $selData";

	} elseif ($fldType == "DG"){
		$wildDisplayTemp .= " $fldDesc $displayOper $selData ";
		$selData=str_replace("?", "_", $selData);
		$selData=str_replace("*", "%", $selData);
		$wildCardTemp    .= " digits($fldName) $operand '$selData'";

	} elseif ($fldType == "I"){
		if ($selData == "0"){$selData =  "000000";}
		$selDate = substr($selData, 0, 2) . $dateEdit . substr($selData, 2, 2) . $dateEdit . substr($selData, 4, 2);
		$dateOut = Date_To_ISO($selData);
		$wildDisplayTemp .= " $fldDesc $displayOper $selDate";
		$wildCardTemp    .= " $fldName $operand '$dateOut'";

	} elseif ($fldType == "N"){
		$wildDisplayTemp .= " $fldDesc $displayOper $selData";
		$wildCardTemp    .= "$fldName $operand $selData";

	} elseif ($fldType == "P" || $fldType == "PA"){
		$qte = ($fldType == "PA") ? "'" : "";
		$fromPhoneNumber = $selData;
		$toPhoneNumber   = $selData;
		$fromPhoneNumber = str_pad($fromPhoneNumber, 10, "0");
		$toPhoneNumber   = str_pad($toPhoneNumber, 10, "9");
		$wildCardTemp    .= " $fldName between $qte$fromPhoneNumber$qte and $qte$toPhoneNumber$qte";
		$wildDisplayTemp .= " $fldDesc between $fromPhoneNumber and $toPhoneNumber";

	} elseif ($fldType == "V"){
		$wildDisplayTemp .= " $fldDesc $displayOper $selData";
	}
	$returnValue['wildCardTemp']    = $wildCardTemp;
	$returnValue['wildDisplayTemp'] = $wildDisplayTemp;
	$returnValue['wildCardSearch']  = $wildCardSearch;
	return $returnValue;
}

?>