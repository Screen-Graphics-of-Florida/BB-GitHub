<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromScript         = $_GET['fromScript'];
$fromUser           = $_GET['fromUser'];
$fromPmtType        = $_GET['fromPmtType'];

if ($_GET['user']) {$user = $_GET['user'];}
else               {$user = $fromUser;}
if ($_GET['pmtType']) {$pmtType = $_GET['pmtType'];}
else                  {$pmtType = $fromPmtType;}

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "Payment Column By User Maintenance";
$scriptName     = "ARPmtColumnUserMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromUser=" . urlencode(trim($fromUser)) . "&amp;fromPmtType=" . urlencode(trim($fromPmtType)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HARPCU_E";

$backURL="{$homeURL}{$phpPath}ARPmtColumnUser.php{$scriptVarBase}&amp;tag=REPORT";

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

// Program Option Security
$harpcu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01=$harpcu_OPT['sec_01'];
$sec_02=$harpcu_OPT['sec_02'];
if ($user=="HDS") {$sec_03="N";}
else              {$sec_03=$harpcu_OPT['sec_03'];}
$sec_04=$harpcu_OPT['sec_04'];

if ($tag == "ADD") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CheckEnterChg.php';

	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.user.value ==\"\" || ";
	print "\n     document.Chg.pmtType.value ==\"\") ";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n return true; ";
	print "\n } ";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARPMTCOLUMNUSERMAINT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";

	print "\n <table $contentTable> ";
	print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
	print "\n <tr><td><h1>$page_title</h1></td> ";
	print "\n     <td class=\"toolbar\"> ";
	require 'MaintainTopNoTable.php';
	print "\n     </td> ";
	print "\n </tr> ";
	print "\n </table> ";

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';
	if ($errFound !="") {
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$Err_PUUSER=DecatErr_Field("@@user", "user");
		$Err_PUTYPE=DecatErr_Field("@@type", "pmtType");
		$row['PUUSER']=Decat_Field("@@user", $edtVar);
		$row['PUTYPE']=Decat_Field("@@type", $edtVar);
		$errFound= "";
	} else {
		$focusField= "user";
		$row['PUUSER']=$fromUser;
		$row['PUTYPE']=$fromPmtType;
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Add&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$fieldDesc=RetValue("USUSER='$row[PUUSER]'", "SYUSER", "USDESC");
	$textOvr=SetTextOvr($Err_PUUSER);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>User</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"user\" value=\"" . rtrim($row['PUUSER']) . "\" size=\"10\" maxlength=\"10\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=user&amp;descFld=userName\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"userName\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_PUUSER);

	$fieldDesc=RetValue("CPTYPE='$row[PUTYPE]'", "ARPAYT", "CPDESC");
	$textOvr=SetTextOvr($Err_PUTYPE);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Payment Type</span></td> ";
	if ($maintenanceCode=="A") {
		print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"pmtType\" value=\"" . rtrim($row['PUTYPE']) . "\" size=\"10\" maxlength=\"1\"> ";
		print "\n                             <a href=\"{$homeURL}{$phpPath}ARPmtTypeSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=pmtType&amp;fldDesc=pmtTypeDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a>";
		print "\n     <span class=\"dspdesc\" id=\"pmtTypeDesc\">$fieldDesc</span></td>";
	} else {
		$F_pmtType=Format_Code($row[PUTYPE]);
		print "\n     <td class=\"dspalph\"><input type=\"hidden\" name=\"pmtType\" value=\"" . rtrim($row['PUTYPE']) . "\">$fieldDesc $F_pmtType</td> ";
	}
	print "\n </tr> ";
	DspErrMsg($Err_PUTYPE);

	print "\n </table> ";

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

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'AJAXRequest.js';
	require_once 'Menu.js';

	require_once 'CheckEnterChg.php';
	require_once 'NoFormValidate.php';
	require_once 'ShowHideSelCriteria.php';

	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "ARPMTCOLUMNUSERMAINT";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";

	require 'stmtSQLClear.php';
	$stmtSQL .= " Select PLCOLM,PLDESC,Case When Coalesce(PUDSPL,' ')='Y' Then 'CHECKED' ELSE ' ' END as ROWCHECKED";
	$fileSQL .= " ARPYCL ";
	$fileSQL .= " left join ARPYCU on (PUUSER,PUTYPE,PUCOLM)=('$fromUser',PLTYPE,PLCOLM) ";
	$selectSQL .= "PLTYPE='$fromPmtType' ";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By PLSEQ ";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";

	if ($errFound != "") {
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$Err_PUUSER=DecatErr_Field("@@user", "user");
		$Err_PUTYPE=DecatErr_Field("@@type", "pmtType");
		$errFound= "";
	}

	print "\n <table $contentTable>";
	$fieldDesc=RetValue("USUSER='$user'", "SYUSER", "USDESC");
	$F_user=Format_Code($user);
	$textOvr=SetTextOvr($Err_PUUSER);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>User</span></td> ";
	print "\n     <td class=\"dspalph\"><input type=\"hidden\" name=\"user\" value=\"" . rtrim($user) . "\">$fieldDesc $F_user</td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PUUSER);

	$fieldDesc=RetValue("CPTYPE='$pmtType'", "ARPAYT", "CPDESC");
	$F_pmtType=Format_Code($pmtType);
	$textOvr=SetTextOvr($Err_PUTYPE);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Payment Type</span></td> ";
	print "\n     <td class=\"dspalph\"><input type=\"hidden\" name=\"pmtType\" value=\"" . rtrim($pmtType) . "\">$fieldDesc $F_pmtType</td> ";
	print "\n </tr> ";
	DspErrMsg($Err_PUTYPE);
	print "\n </table> ";

	print "\n <table $contentTable>";
	print "\n   <tr>";
	print "\n     <th class=\"colhdr\">Select</th>";
	print "\n     <th class=\"colhdr\">Column</th>";
	print "\n   </tr>";

	$rowCount=1;
	while ($row = db2_fetch_assoc($sqlResult)){
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\"> ";
		print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"selc{$rowCount}\" value='Y' $row[ROWCHECKED] title=\"Select Column\"></td> ";
		print "\n     <td class=\"colalph\">$row[PLDESC] ";
		print "\n                          <input type=\"hidden\" name=\"colm{$rowCount}\" value=\"" . rtrim($row['PLCOLM']) . "\"></td> ";
		print "\n </tr>";
		$rowCount ++;
	}
	print "\n </table> ";
	print "\n </form>";
	require_once 'MaintainBottom.php';
	print $hrTagAttr;
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit;
}

if ($tag == "Edit_Add") {
	$edtVar= "";
	Concat_Field("@@subr", "EDIT_ADD");
	Concat_Field("@@frm1", $fromUser);
	Concat_Field("@@frm2", $fromPmtType);
	$_POST['user']=strtoupper($_POST['user']);      Concat_Field("@@user", $_POST['user']);
	$_POST['pmtType']=strtoupper($_POST['pmtType']);Concat_Field("@@type", $_POST['pmtType']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARPCU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "" && $maintenanceCode=="A") {
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;fromUser=" . urlencode(trim($_POST['user'])) . "&amp;fromPmtType=" . urlencode(trim($_POST['pmtType'])) . "&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;user=" . urlencode(trim($_POST['user'])) . "&amp;pmtType=" . urlencode(trim($_POST['pmtType'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	} elseif ($errFound == "") {
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;user=" . urlencode(trim($_POST['user'])) . "&amp;pmtType=" . urlencode(trim($_POST['pmtType'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=ADD&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;user=" . urlencode(trim($_POST['user'])) . "&amp;pmtType=" . urlencode(trim($_POST['pmtType'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

if ($tag == "Edit_Data") {
	if ($maintenanceCode=="D" && is_null($_POST['user'])) {
		$_POST['user']    =$fromUser;
		$_POST['pmtType'] =$fromPmtType;
	}

	$edtVar= "";
	Concat_Field("@@subr", "EDIT_DATA");
	Concat_Field("@@frm1", $fromUser);
	Concat_Field("@@frm2", $fromPmtType);
	Concat_Field("@@user", $_POST['user']);
	Concat_Field("@@type", $_POST['pmtType']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit("HARPCU_W", $userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];


	if ($errFound == "") {
		$recCount=RetValue("PLTYPE='$_POST[pmtType]'", "ARPYCL", "Count(*)");
		$rowCount=1;
		while ($rowCount<=$recCount) {
			$selcFld="selc{$rowCount}";
			$colmFld="colm{$rowCount}";
			require 'stmtSQLClear.php';
			$stmtSQL .= " Update ARPYCU ";
			if ($_POST[$selcFld]=="Y") {$stmtSQL .= " Set PUDSPL='Y', ";}
			else                       {$stmtSQL .= " Set PUDSPL='N', ";}
			$stmtSQL .= " PUTSTP=Current_timestamp,PUTSUS='$userProfile',PUTSPT='Y' ";
			$stmtSQL .= " Where (PUUSER,PUTYPE,PUCOLM)=('$_POST[user]','$_POST[pmtType]','$_POST[$colmFld]') ";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
			$rowCount ++;
		}
		$USDESC=RetValue("USUSER='$_POST[user]'", "SYUSER", "USDESC");
		$CPDESC=RetValue("CPTYPE='$_POST[pmtType]'", "ARPAYT", "CPDESC");
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "$USDESC", "$_POST[user]" , "$CPDESC", "$_POST[pmtType]", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} elseif ($maintenanceCode == "D") {
		$USDESC=RetValue("USUSER='$_POST[user]'", "SYUSER", "USDESC");
		$CPDESC=RetValue("CPTYPE='$_POST[pmtType]'", "ARPAYT", "CPDESC");
		$confMessage=Format_ConfMsg_Desc("", "$USDESC", "$_POST[user]" , "$CPDESC", "$_POST[pmtType]", "<br>$fieldValue", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;user=" . urlencode(trim($_POST['user'])) . "&amp;pmtType=" . urlencode(trim($_POST['pmtType'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>
