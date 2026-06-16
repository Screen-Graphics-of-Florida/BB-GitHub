<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode = $_GET['maintenanceCode'];
$errFound     = (isset($_GET['errFound']))     ? $_GET['errFound']               : "";
$fromTblID    = (isset($_GET['fromTblID']))    ? $_GET['fromTblID']              : 0;
$fromPagID    = (isset($_GET['fromPagID']))    ? $_GET['fromPagID']              : 0;
$fromScript   = (isset($_GET['fromScript']))   ? strtoupper($_GET['fromScript']) : "";
$pageHeading1 = (isset($_GET['pageHeading1'])) ? $_GET['pageHeading1']           : "";
$role         = (isset($_GET['role']))         ? $_GET['role']                   : "";
$user         = (isset($_GET['user']))         ? $_GET['user']                   : "";
$filterID     = (isset($_GET['filterID']))     ? $_GET['filterID']               : 0;
$sylMaxSeq    = (isset($_GET['sylMaxSeq']))    ? $_GET['sylMaxSeq']              : 0;

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Filter Selection Maintain";
$scriptName     = "FilterSelectionMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromCSV=Y&amp;fromType=L&amp;fromTblID=" . urlencode($fromTblID) . "&amp;fromPagID=" . urlencode($fromPagID) . "&amp;fromScript=" . urlencode($fromScript) . "&amp;filterID=" . urlencode($filterID) . "&amp;pageHeading1=". urlencode($pageHeading1);
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;role=" . urlencode($role) . "&amp;user=" . urlencode($user) . "&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HSYLFL";
$popUpWin       = "Y";
$backURL="{$homeURL}{$phpPath}FilterSelection.php{$scriptVarBase}&amp;tag=REPORT";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

if ($fromTblID > 0) {
    $stmtSQL = "Select DSXML From SYDCST Where DSTBID=$fromTblID";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    if (is_resource($sqlResult)) {
        $row = db2_fetch_array($sqlResult);
        $xmlDocset = simplexml_load_string($row[0]);
    }

    $docsetTable = $xmlDocset->table;
    $docsetRow = $xmlDocset->row;
    $docsetLink = $xmlDocset->link;
    $docsetFilter = $xmlDocset->filter;
    $link_count = count($docsetLink->xpath("linkid"));
    $filter_count = count($docsetFilter->xpath("checkbox"));
    foreach ($docsetRow->col as $col) {
        $colLabel = (string)trim(strtoupper($col[0]->label));
        if (strpos($colLabel, "@@parm[") !== false) {
            $parmName = Decat_Parm($colLabel);
            if ($GLOBALS["$parmName"] != "") {
                $colLabel = $GLOBALS["$parmName"];
            } else {
                continue;
            }
        }
        $colName = trim(strtoupper($col['id']));
        $colSort = $colLabel . $colName;
        $col_sort[$colLabel] = $colLabel . ' [' . $colName . ']';
    }
    ksort($col_sort);
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";

	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.filterName.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n return true;";
	print "\n }";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr >";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	print "\n <td class=\"content\">";
	$stmtSQL= "";
    if ($maintenanceCode == "A" || $maintenanceCode=="S") {
        $stmtSQL .= " Select  * From SYLFLW Where LWXHND='$profileHandle' and LWSCRNU='$fromScript' and LWTBID=$fromTblID and LWPGID=$fromPagID and LWSEQ=0";
	} else {
		$stmtSQL .= " Select  * From SYLFLT Where ";
		$stmtSQL .=  " LFSCRNU='$fromScript' and LFTBID=$fromTblID and LFPGID=$fromPagID and LFFLID=$filterID and LFROLE='$role' and LFUSER='$user' and LFSEQ=0";
	}
	require 'stmtSQLEnd.php';

	// Program Option Security
	$hsylfl_OPT=pgmOptSecurity($profileHandle, $dataBaseID, "HSYLFL");
	$sec_01=$hsylfl_OPT['sec_01'];
	$sec_02=$hsylfl_OPT['sec_02'];
	$sec_03=$hsylfl_OPT['sec_03'];
	$sec_04=$hsylfl_OPT['sec_04'];
    $sec_05=$hsylfl_OPT['sec_05'];  // Edit Selection Criteria
	require_once 'MaintainTop.php';
	print "<table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\"> \n <tr><td><h2>$pageHeading1</h2></td></tr></table>";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode($maintenanceCode) . "\">";
	print "\n <table $contentTable>";
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	if ($errFound != "") {
        $focusField= "";
        $edtVar=EdtVarErr($profileHandle, $edtVar);
        $errVar=ErrVarErr($profileHandle, $errVar);
        $Err_LFROLE=DecatErr_Field("@@role", "role");
        $Err_LFUSER=DecatErr_Field("@@user", "user");
        $Err_LFNAME=DecatErr_Field("@@name", "filterName");
        $Err_LFDFLT=DecatErr_Field("@@tdft", "toDft");
        $Err_LFFILV=DecatErr_Field("@@filv", "filterSelection");
        $Err_LFFILD=DecatErr_Field("@@fild", "filterDisplay");

		$row['LFROLE']=Decat_Field("@@role", $edtVar);
		$row['LFUSER']=Decat_Field("@@user", $edtVar);
		$row['LFNAME']=Decat_Field("@@name", $edtVar);
		$row['LFDFLT']=Decat_Field("@@tdft", $edtVar);
        $row['LFFILV']=Decat_Field("@@filv", $edtVar);
        $row['LFFILD']=Decat_Field("@@fild", $edtVar);

		$errFound= "";

    } elseif ($maintenanceCode == "A" || $maintenanceCode == 'S') {
        $focusField="filterName";
        $row['LFSCRNU'] = $row['LWSCRNU'];
        $row['LFTBID'] = $row['LWTBID'];
        $row['LFPGID'] = $row['LWPGID'];
        $row['LFROLE'] = ($maintenanceCode == 'A') ? $activeRole : $role;
        $row['LFUSER'] = ($maintenanceCode == 'A') ? $userProfile : $user;;
        $row['LFFLID'] = $row['LWFLID'];
        $row['LFSEQ'] = $row['LWSEQ'];
        $row['LFNAME'] = ($maintenanceCode == 'S') ? $row['LWNAME'] : '';
        $row['LFOVAR'] = $row['LWOVAR'];
        $row['LFCVAR'] = $row['LWCVAR'];
        $wrkVar=$row['LWFVAR'];
        $row['LFFILV']=Decat_Field("@@filv", $wrkVar);
        $findStr = "and \(";
        $row['LFFILV'] = preg_replace('/' . $findStr . '/', '', $row['LFFILV'], 1);
        $row['LFFILV'] = substr_replace($row['LFFILV'] ,"", -1);
        $row['LFFILD']=Decat_Field("@@fild", $wrkVar);
        $row['LFFILD'] = str_replace("&nbsp;", "", $row['LFFILD']);

	} else {
		$focusField="filterName";
        $wrkVar=$row['LFFVAR'];
        $row['LFFILV']=Decat_Field("@@filv", $wrkVar);
        $findStr = "and \(";
        $row['LFFILV'] = preg_replace('/' . $findStr . '/', '', $row['LFFILV'], 1);
        $row['LFFILV'] = substr_replace($row['LFFILV'] ,"", -1);
        $row['LFFILD']=Decat_Field("@@fild", $wrkVar);
        $row['LFFILD'] = str_replace("&nbsp;", "", $row['LFFILD']);
    }

	$dftChecked=Field_Checked($row['LFDFLT'], "Y");

	$fieldDesc=RetValue("RMROLE='{$row['LFROLE']}'", "SYROLM", "RMDESC");
	$textOvr=SetTextOvr($Err_LFROLE);
	print "\n <tr> ";
	if ($admin == "Y") {
		print "<td class=\"dsphdr\"><span $textOvr>Role</span></td>";
		if ($maintenanceCode == "A") {
			print "\n <td class=\"inputnmbr\"><input type=\"text\" name=\"role\" value=\"{$row['LFROLE']}\" size=\"10\" maxlength=\"10\">";
			print "\n     <a href=\"{$homeURL}{$phpPath}RoleSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;roleFld=role&amp;descFld=roleDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"roleDesc\">$fieldDesc</span>";
			print "\n </td>";
		} else {
			if (trim($row['LFROLE']) != "") {$F_LFROLE=Format_Code($row['LFROLE']);}
			print "\n <td class=\"dspalph\"><input type=\"hidden\" name=\"role\" value=\"{$row['LFROLE']}\">$fieldDesc &nbsp; $F_LFROLE</td>";
		}
	} else {
		print "\n <td><input type=\"hidden\" name=\"role\" value=\"{$row['LFROLE']}\"></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_LFROLE);

	$userName=RetValue("USUSER='{$row['LFUSER']}'", "SYUSER", "USDESC");
	$textOvr=SetTextOvr($Err_LFUSER);
	print "\n <tr> ";
	if ($admin == "Y") {
		print "<td class=\"dsphdr\"><span $textOvr>User Profile</span></td>";
		if ($maintenanceCode == "A") {
			print "\n <td class=\"inputalph\"><input type=\"text\" name=\"user\" value=\"{$row['LFUSER']}\" size=\"10\" maxlength=\"10\">";
			print "\n     <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=user&amp;descFld=userName\" onclick=\"$searchWinVar\">$searchImage</a>";
			print "\n     <span class=\"dspdesc\" id=\"userName\">$userName</span>";
			print "\n </td>";
		} else {
			if (trim($row['LFUSER']) != "") {$F_LFUSER=Format_Code($row['LFUSER']);}
			print "\n <td class=\"dspalph\"><input type=\"hidden\" name=\"user\" value=\"{$row['LFUSER']}\">$userName &nbsp; $F_LFUSER</td>";
		}
	} else {
		print "\n <td><input type=\"hidden\" name=\"user\" value=\"{$row['LFUSER']}\"></td>";
	}
	print "\n </tr> ";
	DspErrMsg($Err_LFUSER);

	$textOvr=SetTextOvr($Err_LFNAME);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Name</span></td>";
	print "\n     <td class=\"inputalph\"><input name=\"filterName\" value=\"" . trim($row['LFNAME']) . "\" type=\"text\" size=\"50\" maxlength=\"100\"> $reqFieldChar </td>";
	print "\n </tr>";
	DspErrMsg($Err_LFNAME);

	print "\n <tr><td class=\"dsphdr\">Default</td>";
	print "\n     <td class=\"inputalph\"><input type=\"checkbox\" name=\"toDft\" value=\"Y\" $dftChecked></td>";
	print "\n </tr>";

    if ($sec_05 == 'Y') {
        $textOvr=SetTextOvr($Err_LFFILV);
        print "\n <tr><td class=\"dsphdr\"><span $textOvr>Selection Criteria</span></td>";
        print "\n     <td class=\"inputalph\"><textarea name=\"filterSelection\" value=\"" . trim($row['LFFILV']) . "\" ROWS=10 COLS=60 WRAP=\"hard\">" . trim($row['LFFILV']) . "</textarea></td>";
        print "\n </tr>";
        DspErrMsg($Err_LFFILV);

        $tblName = $docsetTable->name;
        print "\n <tr><td class=\"dsphdr\">{$tblName} Columns</td>";
        print "\n     <td class=\"inputalph\"><select>";
        foreach ($col_sort as $id => $name) {
            print "<option value=\"$id\">$name</option>";
        }
        print "\n     </select></td>";
        print "\n </tr>";

        $textOvr=SetTextOvr($Err_LFFILD);
        print "\n <tr><td class=\"dsphdr\"><span $textOvr>Description</span></td>";
        print "\n     <td class=\"inputalph\"><textarea name=\"filterDisplay\" value=\"" . trim($row['LFFILD']) . "\" ROWS=10 COLS=60 WRAP=\"hard\">" . trim($row['LFFILD']) . "</textarea></td>";
        print "\n </tr>";
        DspErrMsg($Err_LFFILD);
    }

	print "\n </table> ";

	print "\n <script TYPE=\"text/javascript\">";
	print "\n document.Chg.$focusField.focus();";
	print "\n </script>";
	print "\n </form>";
	require_once 'MaintainBottom.php';
	print $hrTagAttr;

	if ($maintenanceCode=="A" || $maintenanceCode=="S") {
		$workSQL    = "";
		$workSQL = "LWXHND='$profileHandle' and LWSCRNU='$fromScript' and LWTBID=$fromTblID and LWPGID=$fromPagID and LWSEQ=0";
		$filterVar       = RetValue("$workSQL", "SYLFLW", "LWFVAR");
		$ordByVar        = RetValue("$workSQL", "SYLFLW", "LWOVAR");
		$currentCriteria = Decat_Field("@@fild", $filterVar);
		$returnArray     = Get_OrderBy($ordByVar);
		$currentSequence = str_replace( ",", "<br>", $returnArray['orderByDisplay']);
	} else {
		$currentCriteria = Decat_Field("@@fild", $row['LFFVAR']);
		$returnArray     = Get_OrderBy($row['LFOVAR']);
		$currentSequence = str_replace( ",", "<br>", $returnArray['orderByDisplay']);
	}
    $currentCriteria = html_entity_decode($currentCriteria);
	require 'SearchCriteria.php';
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require ($searchTrailer);
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Data") {

	$edtVar= "";
	Concat_Field("@@tbid", $fromTblID);
	Concat_Field("@@pgid", $fromPagID);
	Concat_Field("@@scrn", $fromScript);
	Concat_Field("@@flid", $filterID);
	Concat_Field("@@mseq", $sylMaxSeq);
	if ($maintenanceCode=="D" && !(isset($_POST['role']))) {
		$_POST['role']=$role;
		$_POST['user']=$user;
	}
	Concat_Field("@@role", strtoupper($_POST['role']));
	Concat_Field("@@user", strtoupper($_POST['user']));
	Concat_Field("@@name", $_POST['filterName']);
	Concat_Field("@@tdft", $_POST['toDft']);
    if ($sec_05 == 'Y') {
        Concat_Field("@@filv", $_POST['filterSelection']);
        $fild = htmlspecialchars(trim($_POST['filterDisplay']), ENT_QUOTES);
        Concat_Field("@@fild", $fild);
        Concat_Field("@@tbnm", $docsetTable->name);
    }
    $edtVar .= "}{";

	$returnValue=Validate_Data($profileHandle, $dataBaseID, $maintenanceCode, $errFound, $edtVar, $errVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];

	if ($errFound == "") {
		print "\n <script TYPE=\"text/javascript\">";
        print "\n opener.location.reload();";
        print "\n opener.focus();";
		print "\n window.close();";
		print "\n </script>";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=" . urlencode($maintenanceCode) . "&amp;errFound=" . urlencode($errFound) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . " \"> ";
	}
}

function Validate_Data($profileHandle,$dataBaseID,$maintenanceCode,$errFound,$edtVar,$errVar) {
	global $pgmLibrary, $i5Connect;
	if (is_null($errFound )) $errFound="";
	if (is_null($edtVar ))   $edtVar="";
	if (is_null($errVar ))   $errVar="";

	$pgmCall = array(
	array("Name"=>"profileHandle",   "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
	array("Name"=>"dataBaseID",      "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
	array("Name"=>"maintenanceCode", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"errFound",        "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HSYLFL_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HSYLFL_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"profileHandle"  =>$profileHandle,
	"dataBaseID"     =>$dataBaseID,
	"maintenanceCode"=>$maintenanceCode,
	"errFound"       =>$errFound,
	"edtVar"         =>$edtVar,
	"errVar"         =>$errVar);

	$parmOut = array(
	"profileHandle"  =>"profileHandle",
	"dataBaseID"     =>"dataBaseID",
	"maintenanceCode"=>"maintenanceCode",
	"errFound"       =>"errFound",
	"edtVar"         =>"edtVar",
	"errVar"         =>"errVar");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data (HSYLFL_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['profileHandle']  =$profileHandle;
	$returnValue['dataBaseID']     =$dataBaseID;
	$returnValue['maintenanceCode']=$maintenanceCode;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	return $returnValue;
}


?>