<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$wrnVar             = $_GET['wrnVar'];

$fromScript         = $_GET['fromScript'];
$fromRwSelection    = $_GET['fromRwSelection'];
$GIGRSN             = $_GET['GIGRSN'];
$GIGRRN             = $_GET['GIGRRN'];

require_once 'SetLibraryList.php';

require_once "GLControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title     = "G/L Report Writer Selection Maintenance";
$scriptName     = "GLReportWriterSelectionMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromRwSelection=" . urlencode(trim($fromRwSelection)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HGLRWS_E";
$vldPgmName     = "HGLRWS_E";

$backURL=$_SESSION[$fromURL];
if ($backURL == "") {$backURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=178";}

require_once 'ProgSecurityTestInclude.php';
if ($pgmOptAuth=="F") {
	require_once 'ProgSecurityError.php';
	exit;
}

// Program Option Security
$HGLRWS_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01=$HGLRWS_OPT['sec_01'];
$sec_02=$HGLRWS_OPT['sec_02'];
$sec_03=$HGLRWS_OPT['sec_03'];
$sec_04=$HGLRWS_OPT['sec_04'];

if ($tag == "ADD") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CheckEnterChg.php';

	print "\n function validate(chgForm) {";
	print "\n   if (document.Chg.rwSelection.value ==\"\" ";
	print "\n    || document.Chg.rwReport.value ==\"\" ";
	print "\n   ) {alert(\"$reqFieldError\"); return false;} ";
	print "\n   return true; ";
	print "\n } ";

	print "\n </script> \n";

	require_once ($genericHead);
	print "\n </head>";

	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "GLRWSELECTIONMAINTAIN";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	$stmtSQL= "";
	if ($maintenanceCode == "A") {
		require_once 'AddRecordSQL.php';
	} else {
		$stmtSQL .= " Select * ";
		$stmtSQL .= " From GLWRSM ";
		$stmtSQL .= " Where GIGRSN='$fromRwSelection' ";
	}
	require 'stmtSQLEnd.php';

	require_once 'MaintainTop.php';

	print $hrTagAttr;
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';

	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	$row = db2_fetch_assoc($sqlResult);

	$focusField="rwSelection";
	if ($errFound != "" || $maintenanceCode=="A") {
		if ($errFound == "" && $maintenanceCode=="A") {
			$edtVar= "";
		} elseif ($errFound != "") {
			$focusField= "";
			$edtVar=EdtVarErr($profileHandle, $edtVar);
			$errVar=ErrVarErr($profileHandle, $errVar);
			$Err_GIGRSN=DecatErr_Field("@@grsn", "rwSelection");
			$Err_GIGRRN=DecatErr_Field("@@grrn", "rwReport");
			$errFound= "";
		}
		$row['GIGRSN']=Decat_Field("@@grsn", $edtVar);
		$row['GIGRRN']=Decat_Field("@@grrn", $edtVar);
	}

	print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Add&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
	print "\n <table $contentTable>";

	$textOvr=SetTextOvr($Err_GIGRSN);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Selection Name</span></td> ";
	print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"rwSelection\" value=\"" . trim($row['GIGRSN']) . "\" size=\"10\" maxlength=\"10\"> $reqFieldChar</td> ";
	print "\n  </tr> ";
	DspErrMsg($Err_GIGRSN);

	$fieldDesc=RetValue("GAGRRN='$row[GIGRRN]'", "GLWRDM", "GAGRRD");
	$textOvr=SetTextOvr($Err_GIGRRN);
	print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Report</span></td> ";
	print "\n      <td class=\"inputalph\"><input type=\"text\" name=\"rwReport\" value=\"" . trim($row['GIGRRN']) . "\" size=\"10\" maxlength=\"10\"> ";
	print "\n                              <a href=\"{$homeURL}{$phpPath}GLReportWriterReportSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=rwReport&amp;fldDesc=rwReportDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n                              <span class=\"dspdesc\" id=\"rwReportDesc\">$fieldDesc</span></td>";
	print "\n  </tr> ";
	DspErrMsg($Err_GIGRRN);

	print "\n </table>";
	print "\n <script TYPE=\"text/javascript\">";
	print "\n   document.Chg.$focusField.focus();";
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

if ($tag == "Edit_Add") {
	$edtVar="";
	Concat_Field("@@frm1", $fromRwSelection);
	$_POST['rwSelection']=strtoupper($_POST['rwSelection']); Concat_Field("@@grsn", $_POST['rwSelection']);
	$_POST['rwReport']   =strtoupper($_POST['rwReport']);    Concat_Field("@@grrn", $_POST['rwReport']);
	$edtVar .= "}{";

	$returnValue=Maintain_Edit_Handle("HGLRWS_W1", $profileHandle, "A", $errFound, $edtVar, $errVar, $wrnVar);
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;GIGRSN=" . urlencode(trim($_POST['rwSelection'])) . "&amp;GIGRRN=" . urlencode(trim($_POST['rwReport'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=ADD&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;rwSelection=" . urlencode(trim($_POST['rwSelection'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
	exit;
}

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'Menu.js';

	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'CheckReqFieldJava.php';
	require_once 'DateEdit.php';
	require_once 'DisplayHideSelCriteria.php';
	require_once 'NumEdit.php';
	require_once 'SaveCurrentURL.php';
	require_once 'UpperCase.php';

	print "\n function validate(chgForm) {";
	print "\n   if (document.Chg.rwSelectionDesc.value ==\"\" ";
	print "\n    || document.Chg.outputOption.value ==\"\" ";
	print "\n   ) {alert(\"$reqFieldError\"); return false;} ";

	print "\n   if (editdate(document.Chg.consolDate) ";
	print "\n    && editNum(document.Chg.specificPeriod, 4, 0) ";
	print "\n    && editNum(document.Chg.frCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.toCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.frFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.toFacility, 4, 0) ";
	print "\n   ) return true; ";
	print "\n } ";

	print "\n  function confirmDelete() {return confirm(\"$delRecordConf\")} ";
?>

  var coFacCount = "1";
  var coFacNxtSq = "10";
   
  function addRowCoFac(tblName,rwSelCo,rwSelFac,coFacName,coFacErrorDesc) {
    coFacNxtSq++; // add 1
    var tbody = document.getElementById(tblName).getElementsByTagName("TBODY")[0];
    // create row
    var row = document.createElement("TR");
    // ??????  row.setAttribute('id',colname);  removed by lwm - halted otherwise
             
    var td0 = document.createElement("TD")
    td0.setAttribute('class','inputnmbr');
    td0.setAttribute('className','inputnmbr');  // For IE
    var strHtml = "<INPUT TYPE=\"text\" onkeyup=\"chkNumber(this)\" NAME=\"co" + coFacNxtSq + "\" ID=\"co" + coFacNxtSq + "\" VALUE=\"" + rwSelCo + "\" SIZE=\"2\" MAXLENGTH=\"2\"> ";	
    td0.innerHTML = strHtml.replace(/!coFacCount!/g,coFacCount);

    var td1 = document.createElement("TD")
    td1.setAttribute('class','inputnmbr');
    td1.setAttribute('className','inputnmbr');  // For IE
    var strHtml = "<INPUT TYPE=\"text\" onkeyup=\"chkNumber(this)\" NAME=\"fc" + coFacNxtSq + "\" ID=\"fc" + coFacNxtSq + "\" VALUE=\"" + rwSelFac + "\" SIZE=\"4\" MAXLENGTH=\"4\"> ";	
    td1.innerHTML = strHtml.replace(/!coFacCount!/g,coFacCount);

    var td2 = document.createElement("TD")
    td2.setAttribute('class','inputnmbr');
    td2.setAttribute('className','inputnmbr');  // For IE
    var strHtml = "<a ID=\"cofacSearch" + coFacNxtSq + "\" href=\"<?php print trim(str_replace('"', '\"', $homeURL))?><?php print trim(str_replace('"', '\"', $phpPath))?>CoFacSearch.php<?php print trim(str_replace('"', '\"', $genericVarBase))?>&amp;docName=Chg&amp;fldCo=co" + coFacNxtSq + "&amp;fldFac=fc" + coFacNxtSq + "&amp;fldDesc=nm" + coFacNxtSq + "\" onclick=\"<?php print trim(str_replace('"', '\"', $searchWinVar))?>\"><?php print trim(str_replace('"', '\"', $searchImage))?><\/a>";
    td2.innerHTML = strHtml.replace(/!coFacCount!/g,coFacCount);

    var td3 = document.createElement("TD")
    if (coFacErrorDesc) {
      td3.setAttribute('class','error');
      td3.setAttribute('className','error');  // For IE
      var strHtml = "<span ID=\"nm" + coFacNxtSq + "\">" + coFacErrorDesc + "</span>";
    } else {
      td3.setAttribute('class','colalph');
      td3.setAttribute('className','colalph');  // For IE
      var strHtml = "<span ID=\"nm" + coFacNxtSq + "\">" + coFacName + "</span>"
    }
    td3.innerHTML = strHtml.replace(/!coFacCount!/g,coFacCount);
   
    // append data to row
    row.appendChild(td0);
    row.appendChild(td1);
    row.appendChild(td2);
    row.appendChild(td3);   
    // add to count variable
    coFacCount = parseInt(coFacCount) + 1;
    // append row to table
    tbody.appendChild(row);
    
    var GIGRPS=document.getElementById('coFacReportSelection').value;
    var coFld="co"+coFacNxtSq;
    var facFld="fc"+coFacNxtSq;
    var srchFld="cofacSearch"+coFacNxtSq;
    if (GIGRPS=='1') {
      document.getElementById(coFld).style.display='inline';    
      document.getElementById(facFld).style.display='none';    
      document.getElementById(srchFld).style.display='none';    
    } else if (GIGRPS=='2') {
      document.getElementById(coFld).style.display='none';    
      document.getElementById(facFld).style.display='inline';    
      document.getElementById(srchFld).style.display='none';    
    } else if (GIGRPS=='3') {
      document.getElementById(coFld).style.display='inline';    
      document.getElementById(facFld).style.display='inline';    
      document.getElementById(srchFld).style.display='inline';    
    } 
  }
  
  // Open Co/Fac Selection
  function OpenCoFacSelection(GIGRPS) {
    if (GIGRPS=='1') {
      document.getElementById('frCoFacHeading').innerHTML='From Company';    
      document.getElementById('toCoFacHeading').innerHTML='To Company';    
      document.getElementById('colCo').style.display='inline';    
      document.getElementById('frCompany').style.display='inline';    
      document.getElementById('toCompany').style.display='inline';    
      document.getElementById('colFac').style.display='none';    
      document.getElementById('frFacility').style.display='none';    
      document.getElementById('toFacility').style.display='none';    
      document.getElementById('frcofacSearch').style.display='none';    
      document.getElementById('tocofacSearch').style.display='none';    
    } else if (GIGRPS=='2') {
      document.getElementById('frCoFacHeading').innerHTML='From Facility';    
      document.getElementById('toCoFacHeading').innerHTML='To Facility';    
      document.getElementById('colCo').style.display='none';    
      document.getElementById('frCompany').style.display='none';    
      document.getElementById('toCompany').style.display='none';    
      document.getElementById('colFac').style.display='inline';    
      document.getElementById('frFacility').style.display='inline';    
      document.getElementById('toFacility').style.display='inline';    
      document.getElementById('frcofacSearch').style.display='none';    
      document.getElementById('tocofacSearch').style.display='none';    
    } else if (GIGRPS=='3') {
      document.getElementById('frCoFacHeading').innerHTML='From Company/Facility';    
      document.getElementById('toCoFacHeading').innerHTML='To Company/Facility';    
      document.getElementById('colCo').style.display='inline';    
      document.getElementById('frCompany').style.display='inline';    
      document.getElementById('toCompany').style.display='inline';    
      document.getElementById('colFac').style.display='inline';    
      document.getElementById('frFacility').style.display='inline';    
      document.getElementById('toFacility').style.display='inline';    
      document.getElementById('frcofacSearch').style.display='inline';    
      document.getElementById('tocofacSearch').style.display='inline';    
    } 
    for (i=11; i<=coFacNxtSq; i++) {
      var coFld="co"+i;
      var facFld="fc"+i;
      var srchFld="cofacSearch"+i;
      if (GIGRPS=='1') {
        document.getElementById(coFld).style.display='inline';    
        document.getElementById(facFld).style.display='none';    
        document.getElementById(srchFld).style.display='none';    
      } else if (GIGRPS=='2') {
        document.getElementById(coFld).style.display='none';    
        document.getElementById(facFld).style.display='inline';    
        document.getElementById(srchFld).style.display='none';    
      } else if (GIGRPS=='3') {
        document.getElementById(coFld).style.display='inline';    
        document.getElementById(facFld).style.display='inline';    
        document.getElementById(srchFld).style.display='inline';    
      } 
    }
    showSel('ShowCoFacSelection');
  }  
  
  // Close Co/Fac Selection
  function CloseCoFacSelection() {hideSel('ShowCoFacSelection');} 

  // Changed Co/Fac Selection
  function editGIGRPS() { 
    if (document.getElementById('coFacReportSelection').value=='1') {OpenCoFacSelection(document.getElementById('coFacReportSelection').value);}
    else if (document.getElementById('coFacReportSelection').value=='2') {OpenCoFacSelection(document.getElementById('coFacReportSelection').value);}
    else if (document.getElementById('coFacReportSelection').value=='3') {OpenCoFacSelection(document.getElementById('coFacReportSelection').value);}
    else {CloseCoFacSelection();}
  } 
  
  // Budget 
  var bdgCount = "1";
  var bdgNxtSq = "10";
   
  function addRowBdg(tblName,rwBdgCol,rwColDesc,rwBdgNbr,bdgDesc,bdgErrorDesc) {
    bdgNxtSq++; // add 1
    var tbody = document.getElementById(tblName).getElementsByTagName("TBODY")[0];
    // create row
    var row = document.createElement("TR");
    // ??????  row.setAttribute('id',colname);  removed by lwm - halted otherwise
             
    var td0 = document.createElement("TD")
    td0.setAttribute('class','inputnmbr');
    td0.setAttribute('className','inputnmbr');  // For IE
    var strHtml = "<INPUT TYPE=\"hidden\" NAME=\"cl" + bdgNxtSq + "\" ID=\"cl" + bdgNxtSq + "\" VALUE=\"" + rwBdgCol + "\"> "+ rwBdgCol ;	
    td0.innerHTML = strHtml.replace(/!bdgCount!/g,bdgCount);
             
    var td1 = document.createElement("TD")
    td1.setAttribute('class','inputnmbr');
    td1.setAttribute('className','inputnmbr');  // For IE
    var strHtml = rwColDesc ;	
    td1.innerHTML = strHtml.replace(/!bdgCount!/g,bdgCount);

    var td2 = document.createElement("TD")
    td2.setAttribute('class','inputnmbr');
    td2.setAttribute('className','inputnmbr');  // For IE
    var strHtml = "<INPUT TYPE=\"text\" onkeyup=\"chkNumber(this)\" NAME=\"bd" + bdgNxtSq + "\" ID=\"bd" + bdgNxtSq + "\" VALUE=\"" + rwBdgNbr + "\" SIZE=\"3\" MAXLENGTH=\"3\"> ";	
    td2.innerHTML = strHtml.replace(/!bdgCount!/g,bdgCount);

    var td3 = document.createElement("TD")
    td3.setAttribute('class','inputnmbr');
    td3.setAttribute('className','inputnmbr');  // For IE
    var strHtml = "<a ID=\"bdgSearch" + bdgNxtSq + "\" href=\"<?php print trim(str_replace('"', '\"', $homeURL))?><?php print trim(str_replace('"', '\"', $phpPath))?>BudgetPlanSearch.php<?php print trim(str_replace('"', '\"', $genericVarBase))?>&amp;docName=Chg&amp;fldName=bd" + bdgNxtSq + "&amp;fldDesc=ds" + bdgNxtSq + "\" onclick=\"<?php print trim(str_replace('"', '\"', $searchWinVar))?>\"><?php print trim(str_replace('"', '\"', $searchImage))?><\/a>";
    td3.innerHTML = strHtml.replace(/!bdgCount!/g,bdgCount);

    var td4 = document.createElement("TD")
    if (bdgErrorDesc) {
      td4.setAttribute('class','error');
      td4.setAttribute('className','error');  // For IE
      var strHtml = "<span ID=\"ds" + coFacNxtSq + "\">" + bdgErrorDesc + "</span>";
    } else {
      td4.setAttribute('class','colalph');
      td4.setAttribute('className','colalph');  // For IE
      var strHtml = "<span ID=\"ds" + coFacNxtSq + "\">" + bdgDesc + "</span>"
    }
    td4.innerHTML = strHtml.replace(/!bdgCount!/g,bdgCount);
   
    // append data to row
    row.appendChild(td0);
    row.appendChild(td1);
    row.appendChild(td2);
    row.appendChild(td3);   
    row.appendChild(td4);   
    // add to count variable
    bdgCount = parseInt(bdgCount) + 1;
    // append row to table
    tbody.appendChild(row);
  }
  
  // Open Budget Selection
  function OpenBdgSelection() {showSel('ShowBdgSelection');}  
  
  // Close Budget Selection
  function CloseBdgSelection() {hideSel('ShowBdgSelection');} 

  // Changed Budget Selection
  function editGIMBDG() { 
    if (document.getElementById('multBudget').checked) {OpenBdgSelection();}
    else {CloseBdgSelection();}
  } 
  
<?php

print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "GLRWSELECTIONMAINTAIN";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
$stmtSQL= "";
if ($maintenanceCode == "A") {
	require_once 'AddRecordSQL.php';
} else {
	$stmtSQL .= " Select * ";
	$stmtSQL .= " From GLWRSM ";
	$stmtSQL .= " Where GIGRSN='$fromRwSelection' ";
}
require 'stmtSQLEnd.php';

$HGLRWS_OUT=pgmOptSecurity($profileHandle, $dataBaseID, 'HGLRWSOUT');
$PrtSec=$HGLRWS_OUT['sec_01'];
$DrlSec=$HGLRWS_OUT['sec_02'];
$ViwSec=$HGLRWS_OUT['sec_03'];
$DspSec=$HGLRWS_OUT['sec_04'];
$FilSec=$HGLRWS_OUT['sec_05'];
$AudSec=$HGLRWS_OUT['sec_06'];
$SprSec=$HGLRWS_OUT['sec_07'];

require_once 'MaintainTop.php';

print $hrTagAttr;
require_once 'RequiredField.php';
require_once 'ErrorDisplay.php';

$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

$focusField="rwSelection";

if ($GIGRSN=="") {$GIGRSN=$row['GIGRSN'];} else {$row['GIGRSN']=$GIGRSN;}
if ($GIGRRN=="") {$GIGRRN=$row['GIGRRN'];} else {$row['GIGRRN']=$GIGRRN;}

if ($errFound == "") {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Delete From GLWRSC_W Where GIXHND='$profileHandle' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	require 'stmtSQLClear.php';
	$stmtSQL .= " Insert Into GLWRSC_W (GIXHND,GIGRSN,GICO,GIFAC,GIERR) ";
	$stmtSQL .= " Select '". trim($profileHandle) . "','" . trim($GIGRSN) . "',GICO,GIFAC,' ' From GLWRSC Where GIGRSN='$fromRwSelection' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	require 'stmtSQLClear.php';
	$stmtSQL .= " Delete From GLWRBD_W Where BDXHND='$profileHandle' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

	require 'stmtSQLClear.php';
	$stmtSQL .= " Insert Into GLWRBD_W (BDXHND,BDGRSN,BDGRCL,BDNMBR,BDERR) ";
	$stmtSQL .= " Select '". trim($profileHandle) . "','" . trim($GIGRSN) . "',GBGRC#,coalesce(BDNMBR,0),' ' From GLWRCM Inner Join GLWRCD on (GJGRCG,GJGRFT)=(GBGRCG,'B') left Join GLWRBD on (BDGRSN,BDGRC#)=('$fromRwSelection',GBGRC#) Where GBGRRN='$GIGRRN' ";
	$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
}

if ($errFound != "" || $maintenanceCode=="A") {
	if ($errFound == "" && $maintenanceCode=="A") {
		$edtVar= "";
	} elseif ($errFound != "") {
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$Err_GIGRSN=DecatErr_Field("@@grsn", "rwSelection");
		$Err_GIGRSD=DecatErr_Field("@@grsd", "rwSelectionDesc");
		$Err_GIGRRN=DecatErr_Field("@@grrn", "rwReport");
		$Err_GIGRPS=DecatErr_Field("@@grps", "coFacReportSelection");
		$Err_GICFCN=DecatErr_Field("@@cfcn", "coFacConsolidation");
		$Err_GIACCN=DecatErr_Field("@@accn", "accountConsolidation");
		$Err_GICNSD=DecatErr_Field("@@cnsd", "consolDate");
		$Err_GIUNPS=DecatErr_Field("@@unps", "inclUnposted");
		$Err_GIEOYA=DecatErr_Field("@@eoya", "inclEOY");
		$Err_GIPERD=DecatErr_Field("@@perd", "specificPeriod");
		$Err_GITHOU=DecatErr_Field("@@thou", "roundTo");
		$Err_GIPRTH=DecatErr_Field("@@prth", "printHeading");
		$Err_GINAB =DecatErr_Field("@@nab@", "normalAcctBal");
		$Err_GIPNOV=DecatErr_Field("@@pnov", "printNoValue");
		$Err_GIMBDG=DecatErr_Field("@@mbdg", "multBudget");
		$Err_GIOOPT=DecatErr_Field("@@oopt", "outputOption");
		$Err_GIGHCT=DecatErr_Field("@@ghct", "category");
		$Err_GIGHUS=DecatErr_Field("@@ghus", "userID");
		$Err_GICOFC=DecatErr_Field("@@cofc", "frCompany");
		$Err_GICOTC=DecatErr_Field("@@cotc", "toCompany");
		$Err_GIFCFF=DecatErr_Field("@@fcff", "frFacility");
		$Err_GIFCTF=DecatErr_Field("@@fctf", "toFacility");
	}
	if ($maintenanceCode=="A") {$row['GIGRSN']=$GIGRSN;}
	else                       {$row['GIGRSN']=Decat_Field("@@grsn", $edtVar);}
	if ($maintenanceCode=="A") {$row['GIGRRN']=$GIGRRN;}
	else                       {$row['GIGRRN']=Decat_Field("@@grrn", $edtVar);}
	$row['GIGRSD']=Decat_Field("@@grsd", $edtVar);
	$row['GIGRPS']=Decat_Field("@@grps", $edtVar);
	$row['GICFCN']=Decat_Field("@@cfcn", $edtVar);
	$row['GIACCN']=Decat_Field("@@accn", $edtVar);
	$row['GICNSD']=Decat_Field("@@cnsd", $edtVar);
	$row['GIUNPS']=Decat_Field("@@unps", $edtVar);
	$row['GIEOYA']=Decat_Field("@@eoya", $edtVar);
	$row['GIPERD']=Decat_Field("@@perd", $edtVar);
	$row['GITHOU']=Decat_Field("@@thou", $edtVar);
	$row['GIPRTH']=Decat_Field("@@prth", $edtVar);
	$row['GINAB'] =Decat_Field("@@nab@", $edtVar);
	$row['GIPNOV']=Decat_Field("@@pnov", $edtVar);
	$row['GIMBDG']=Decat_Field("@@mbdg", $edtVar);
	$row['GIOOPT']=Decat_Field("@@oopt", $edtVar);
	$row['GIGHCT']=Decat_Field("@@ghct", $edtVar);
	$row['GIGHUS']=Decat_Field("@@ghus", $edtVar);
	$row['GICOFC']=Decat_Field("@@cofc", $edtVar);
	$row['GICOTC']=Decat_Field("@@cotc", $edtVar);
	$row['GIFCFF']=Decat_Field("@@fcff", $edtVar);
	$row['GIFCTF']=Decat_Field("@@fctf", $edtVar);
	$row['GITSTP']=Decat_Field("@@tstp", $edtVar);

	if ($errFound == "" && $maintenanceCode=="A") {
		$row['GICFCN']='N';
		$row['GIACCN']='N';
		$row['GIUNPS']='N';
		$row['GIEOYA']='Y';
		$row['GIPRTH']='Y';
		$row['GINAB'] =$CTNAB;
		$row['GIPNOV']='N';
		$row['GIMBDG']='N';
		if     ($PrtSec='Y') {$row['GIOOPT']='P';}
		elseif ($DrlSec='Y') {$row['GIOOPT']='L';}
		elseif ($ViwSec='Y') {$row['GIOOPT']='V';}
		elseif ($DspSec='Y') {$row['GIOOPT']='D';}
		elseif ($FilSec='Y') {$row['GIOOPT']='F';}
		elseif ($SprSec='Y') {$row['GIOOPT']='S';}
		elseif ($AudSec='Y') {$row['GIOOPT']='A';}
		$row['GIGHUS']=$userProfile;
	}
	$errFound= "";
} else {
	$focusField="rwSelectionDesc";
	$row['GICNSD']=DateInputFromCYMD($row['GICNSD']);
	$row['GIPERD']=PeriodInputFromCYP($row['GIPERD']);
}

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
print "\n <table $contentTable>";
print "\n     <tr><td><input type=\"hidden\" name=\"originalTimeStamp\" value=\"" . rtrim($row['GITSTP']) . "\"></td></tr> ";

$textOvr=SetTextOvr($Err_GIGRSN);
print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Selection Name</span></td> ";
print "\n      <td class=\"inputalph\"><input type=\"hidden\" name=\"rwSelection\" value=\"" . trim($row['GIGRSN']) . "\">" . trim($row['GIGRSN']) . "</td> ";
print "\n  </tr> ";
DspErrMsg($Err_GIGRSN);

$fieldDesc=RetValue("GAGRRN='$row[GIGRRN]'", "GLWRDM", "GAGRRD");
$textOvr=SetTextOvr($Err_GIGRRN);
print "\n  <tr><td class=\"dsphdr\"><span $textOvr>Report</span></td> ";
print "\n      <td class=\"inputalph\"><input type=\"hidden\" name=\"rwReport\" value=\"" . trim($row['GIGRRN']) . "\">" . trim($row['GIGRRN']);
print "\n                              <span class=\"dspdesc\" id=\"rwReportDesc\">$fieldDesc</span></td>";
print "\n  </tr> ";
DspErrMsg($Err_GIGRRN);

Build_Fld_Entry("Selection Description","rwSelectionDesc","inputalph","","GIGRSD",$row[GIGRSD],$Err_GIGRSD,"30","30","Y","","");

$fieldDesc=RetValue("(FLTYPE,FLVALU)=('GLRWCFSEL','$row[GIGRPS]')", "SYFLAG", "FLDESC");
$textOvr=SetTextOvr($Err_GIGRPS);
print "\n <tr><td class=\"dsphdr\"><span $textOvr>Co/Fac Report Selection</span></td> ";
print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"coFacReportSelection\" id=\"coFacReportSelection\" value=\"" . rtrim($row['GIGRPS']) . "\" size=\"1\" maxlength=\"1\" onBlur=\"editGIGRPS()\"> ";
print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;flagType=GLRWCFSEL&amp;flagSrchHdr=". urlencode("Co/Fac Report Selection") . "&amp;fldName=coFacReportSelection&amp;fldDesc=coFacReportSelectionDesc\" onclick=\"$searchWinVar\"> $searchImage</a>";
print "\n                             <span class=\"dspdesc\" id=\"coFacReportSelectionDesc\">$fieldDesc</span></td>";
print "\n </tr> ";
DspErrMsg($Err_GIGRPS);

Build_Fld_Entry("Co/Fac Consolidation","coFacConsolidation","inputalph","YORN","GICFCN",$row[GICFCN],$Err_GICFCN,"1","1","","","");
Build_Fld_Entry("Account Consolidation","accountConsolidation","inputalph","YORN","GIACCN",$row[GIACCN],$Err_GIACCN,"1","1","","","");
Build_Fld_Entry("Consolidation Date","consolDate","inputdate","Date","GICNSD",$row[GICNSD],$Err_GICNSD,"6","6","","","");
Build_Fld_Entry("Include Unposted Journals","inclUnposted","inputalph","YORN","GIUNPS",$row[GIUNPS],$Err_GIUNPS,"1","1","","","");
Build_Fld_Entry("Include EOY Adjustments","inclEOY","inputalph","YORN","GIEOYA",$row[GIEOYA],$Err_GIEOYA,"1","1","","","");
Build_Fld_Entry("Specific Period","specificPeriod","inputnmbr","","GIPERD",$row[GIPERD],$Err_GIPERD,"4","4","","","");
Build_Fld_Entry("Round To","roundTo","inputnmbr","GLRWROUND","GITHOU",$row[GITHOU],$Err_GITHOU,"1","1","","","");
Build_Fld_Entry("Print Standard Heading","printHeading","inputalph","YORN","GIPRTH",$row[GIPRTH],$Err_GIPRTH,"1","1","","","");
Build_Fld_Entry("Normal Account Balances","normalAcctBal","inputalph","YORN","GINAB",$row[GINAB],$Err_GINAB,"1","1","","","");
Build_Fld_Entry("Print Row With No Value","printNoValue","inputalph","YORN","GIPNOV",$row[GIPNOV],$Err_GIPNOV,"1","1","","","");

$BdgColCount=RetValue("GBGRRN='$GIGRRN'", "GLWRCM Inner Join GLWRCD on (GJGRCG,GJGRFT)=(GBGRCG,'B')", "Char(Count(*))");
if ($BdgColCount==0) {$fldDisabled = "DISABLED";}
else                 {$fldDisabled = "";}
$fldChecked=Field_Checked($row['GIMBDG'],"Y");
$textOvr=SetTextOvr($Err_GIMBDG);
print "\n <tr><td class=\"dsphdr\"><span $textOvr>Override Budget Plans</span></td> ";
print "\n     <td class=\"checkbox\"><input type=\"checkbox\" name=\"multBudget\" id=\"multBudget\" value=\"Y\" $fldDisabled $fldChecked onClick=\"editGIMBDG()\"></td>";
print "\n </tr> ";
DspErrMsg($Err_GIMBDG);

Build_Fld_Entry("Output Option","outputOption","inputalph","RWOUTPUT","GIOOPT",$row['GIOOPT'],$Err_GIOOPT,"1","1","Y","","");
Build_Fld_Entry("Selection Category","category","inputalph","","GIGHCT",$row[GIGHCT],$Err_GIGHCT,"4","4","","","");

$fieldDesc=RetValue("USUSER='$row[GIGHUS]'", "SYUSER", "USDESC");
$textOvr=SetTextOvr($Err_GIGHUS);
print "\n <tr><td class=\"dsphdr\"><span $textOvr>User ID</span></td> ";
print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"userID\" value=\"" . rtrim($row['GIGHUS']) . "\" size=\"10\" maxlength=\"10\"> ";
print "\n                             <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=userID&amp;descFld=userIDName\" onclick=\"$searchWinVar\"> $searchImage</a>";
print "\n     <span class=\"dspdesc\" id=\"userIDName\">$fieldDesc</span></td>";
print "\n </tr> ";
DspErrMsg($Err_GIGHUS);

print "\n </table> ";

// Company/Facility Report Selection (hidden DIV)
print "\n <div id=\"ShowCoFacSelection\">";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Company/Facility Report Selection ";
Print "\n </legend> ";

print "\n <table $contentTable>";
$textOvrFr=SetTextOvr($Err_GICOFC); if ($textOvrFr=="") {$textOvrFr=SetTextOvr($Err_GIFCFF);}
$textOvrTo=SetTextOvr($Err_GICOTC); if ($textOvrFr=="") {$textOvrFr=SetTextOvr($Err_GIFCTF);}
print "\n  <tr><td class=\"dsphdr\"><span id=\"frCoFacHeading\" $textOvrFr>From Company/Facility</span></td> ";
print "\n      <td class=\"inputnmbr\"><input type=\"text\" name=\"frCompany\" id=\"frCompany\" value=\"$row[GICOFC]\" size=\"2\" maxlength=\"2\">";
print "\n                              <input type=\"text\" name=\"frFacility\" id=\"frFacility\" value=\"$row[GIFCFF]\" size=\"4\" maxlength=\"4\">";
print "\n                              <a id=\"frcofacSearch\" href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frCompany&amp;fldFac=frFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
print "\n      <td class=\"dsphdr\"><span id=\"toCoFacHeading\" $textOvrTo>To Company/Facility</span></td> ";
print "\n      <td class=\"inputnmbr\"><input type=\"text\" name=\"toCompany\" id=\"toCompany\" value=\"$row[GICOTC]\" size=\"2\" maxlength=\"2\">";
print "\n                              <input type=\"text\" name=\"toFacility\" id=\"toFacility\" value=\"$row[GIFCTF]\" size=\"4\" maxlength=\"4\">";
print "\n                              <a id=\"tocofacSearch\" href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFac=toFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
print "\n  </tr> ";
DspErrMsg($Err_GICOFC); DspErrMsg($Err_GIFCFF);
DspErrMsg($Err_GICOTC); DspErrMsg($Err_GIFCTF);
print "\n </table> ";

print "\n <table id=\"coFacTable\" $contentTable>";
print "\n     <tr><th class=\"colhdr\"><span id=\"colCo\">Co</span></th>";
print "\n         <th class=\"colhdr\"><span id=\"colFac\">Fac</span></th>";
print "\n         <th class=\"icon\" align=\"left\"><a href=\"javascript:addRowCoFac('coFacTable','','','','')\">$addMoreImage</a></th> ";
print "\n     </tr>";

require 'stmtSQLClear.php';
$stmtSQL .= " Select GICO,GIFAC,GIERR ";
$stmtSQL .= "       ,Coalesce(CFCFNM,' ') as CFCFNM ";
$stmtSQL .= "       ,Coalesce(ERERDS,' ') as ERERDS ";
$stmtSQL .= " From GLWRSC_W";
$stmtSQL .= " Left Join HDCFAC on (CFCO#,CFFAC#)=(GICO,GIFAC)";
$stmtSQL .= " Left Join HDERROR on ERER#=GIERR";
$stmtSQL .= " Where (GIXHND,GIGRSN)=('$profileHandle','$GIGRSN')";
require 'stmtSQLEnd.php';

$startRow = 1;

$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

while ($rowCoFac = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCoFac) {
		print "\n <script TYPE=\"text/javascript\">";
		print "\n addRowCoFac('coFacTable','". trim($rowCoFac[GICO]) . "','" . trim($rowCoFac['GIFAC']) . "','" . trim($rowCoFac['CFCFNM']) . "','" . trim($rowCoFac['ERERDS']) . "')";
		print "\n </script>";
	}
	$startRow ++;
}

print "\n <script TYPE=\"text/javascript\">";
if (trim($row['GIGRPS'])<"1" || trim($row['GIGRPS'])>"3") {print "\n CloseCoFacSelection()";}
else                                                      {print "\n OpenCoFacSelection($row[GIGRPS])";}
print "\n </script>";

print "\n </table> ";
print "\n </fieldset> ";
print "\n </div>";

// Budget Plan Selection (hidden DIV)
print "\n <div id=\"ShowBdgSelection\">";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Budget Plan Selection ";
Print "\n </legend> ";

print "\n <table id=\"bdgTable\" $contentTable>";
print "\n     <tr><th class=\"colhdr\">Column</th>";
print "\n         <th class=\"colhdr\">Column Description</th>";
print "\n         <th class=\"colhdr\">Plan</th>";
print "\n     </tr>";

require 'stmtSQLClear.php';
$stmtSQL .= " Select BDGRCL,BDNMBR,BDERR ";
$stmtSQL .= "       ,GBGRCD ";
$stmtSQL .= "       ,Coalesce(BGDESC,' ') as BGDESC ";
$stmtSQL .= "       ,Coalesce(ERERDS,' ') as ERERDS ";
$stmtSQL .= " From GLWRBD_W";
$stmtSQL .= " Inner Join GLWRCM on (GBGRRN,GBGRC#)=('$row[GIGRRN]',BDGRCL)";
$stmtSQL .= " Left Join GLBGHD on BGNMBR=BDNMBR";
$stmtSQL .= " Left Join HDERROR on ERER#=BDERR";
$stmtSQL .= " Where (BDXHND,BDGRSN)=('$profileHandle','$GIGRSN') ";
$stmtSQL .= " Order by BDGRCL";
require 'stmtSQLEnd.php';

$startRow = 1;

$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
$rowBdg = db2_fetch_assoc($sqlResult);

while ($rowBdg = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowBdg) {
		print "\n <script TYPE=\"text/javascript\">";
		print "\n addRowBdg('bdgTable','". trim($rowBdg[BDGRCL]) . "','" . trim($rowBdg['GBGRCD']) . "','" . trim($rowBdg['BDNMBR']) . "','" . trim($rowBdg['BGDESC']) . "','" . trim($rowBdg['ERERDS']) . "')";
		print "\n </script>";
	}
	$startRow ++;
}

print "\n <script TYPE=\"text/javascript\">";
if (trim($row['GIMBDG'])=="Y") {print "\n OpenBdgSelection()";}
else                           {print "\n CloseBdgSelection()";}
print "\n </script>";

print "\n </table> ";
print "\n </fieldset> ";
print "\n </div>";

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
	if ($maintenanceCode=="D" && is_null($_POST['rwSelection'])) {
		$_POST['rwSelection']    =$fromRwSelection;
		$_POST['rwSelectionDesc']=RetValue("GIGRSN='$_POST[rwSelection]'", "GLWRSM", "GIGRSD");
	}

	if ($maintenanceCode == "Z") {$maintenanceCode="A";}

	$edtVar="";
	Concat_Field("@@frm1", $fromRwSelection);
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	Concat_Field("@@grsn", strtoupper($_POST['rwSelection']));
	Concat_Field("@@grsd", $_POST['rwSelectionDesc']);
	Concat_Field("@@grrn", strtoupper($_POST['rwReport']));
	Concat_Field("@@grps", $_POST['coFacReportSelection']);
	if (!isset($_POST['coFacConsolidation'])) {$_POST['coFacConsolidation']="N";} Concat_Field("@@cfcn", $_POST['coFacConsolidation']);
	if (!isset($_POST['accountConsolidation'])) {$_POST['accountConsolidation']="N";} Concat_Field("@@accn", $_POST['accountConsolidation']);
	Concat_Field("@@cnsd", strtoupper($_POST['consolDate']));
	if (!isset($_POST['inclUnposted'])) {$_POST['inclUnposted']="N";} Concat_Field("@@unps", $_POST['inclUnposted']);
	if (!isset($_POST['inclEOY'])) {$_POST['inclEOY']="N";} Concat_Field("@@eoya", $_POST['inclEOY']);
	Concat_Field("@@perd", $_POST['specificPeriod']);
	Concat_Field("@@thou", strtoupper($_POST['roundTo']));
	if (!isset($_POST['printHeading'])) {$_POST['printHeading']="N";} Concat_Field("@@prth", $_POST['printHeading']);
	if (!isset($_POST['normalAcctBal'])) {$_POST['normalAcctBal']="N";} Concat_Field("@@nab@", $_POST['normalAcctBal']);
	if (!isset($_POST['printNoValue'])) {$_POST['printNoValue']="N";} Concat_Field("@@pnov", $_POST['printNoValue']);
	if (!isset($_POST['multBudget'])) {$_POST['multBudget']="N";} Concat_Field("@@mbdg", $_POST['multBudget']);
	Concat_Field("@@oopt", strtoupper($_POST['outputOption']));
	Concat_Field("@@ghct", strtoupper($_POST['category']));
	Concat_Field("@@ghus", strtoupper($_POST['userID']));
	Concat_Field("@@cofc", $_POST['frCompany']);
	Concat_Field("@@cotc", $_POST['toCompany']);
	Concat_Field("@@fcff", $_POST['frFacility']);
	Concat_Field("@@fctf", $_POST['toFacility']);
	for ($i=11; $i<=70; $i++) {
		if (isset($_POST['co'.$i])) {Concat_Field("@@co$i", $_POST['co'.$i]);}
		if (isset($_POST['fc'.$i])) {Concat_Field("@@fc$i", $_POST['fc'.$i]);}
	}
	for ($i=11; $i<=70; $i++) {
		if (isset($_POST['cl'.$i])) {Concat_Field("@@cl$i", $_POST['cl'.$i]);}
		if (isset($_POST['bd'.$i])) {Concat_Field("@@bd$i", $_POST['bd'.$i]);}
	}
	$edtVar .= "}{";

	$returnValue=Maintain_Edit_Handle("HGLRWS_W", $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
	$maintenanceCode=$returnValue['maintenanceCode'];
	$errFound       =$returnValue['errFound'];
	$edtVar         =$returnValue['edtVar'];
	$errVar         =$returnValue['errVar'];
	$wrnVar         =$returnValue['wrnVar'];

	if ($errFound == "") {
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, $_POST['rwSelectionDesc'], $_POST['rwSelection'], "", "", "", "");
		if ($maintenanceCode == "D" || $fromScript == "") {
			print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		} else {
			print "<meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}{$fromScript}{$genericVarBase}&amp;tag=REPORT&amp;fromRwSelection=" . urlencode(trim($_POST['rwSelection'])) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		}
	} elseif ($maintenanceCode == "D") {
		$Err_GIGRSN=DecatErr_Field("@@grsn", "rwSelection");
		$confMessage=Format_ConfMsg_Desc("", $_POST['rwSelectionDesc'], $_POST['rwSelection'], "", "", "<br>$Err_GIGRSN", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=178&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;rwSelection=" . urlencode(trim($_POST['rwSelection'])) . "&amp;GIGRSN=" . urlencode(trim($_POST['rwSelection'])) . "&amp;GIGRRN=" . urlencode(trim($_POST['rwReport'])) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

?>
