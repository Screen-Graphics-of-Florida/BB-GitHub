<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$groupNumber = (isset($_GET['groupNumber'])) ? $_GET['groupNumber'] : "";

require_once 'SetLibraryList.php';
require_once "SystemControl$dataBaseID.php";
require_once "ETControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'ETRetInfo.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Group Indirect Labor Adj Entry";
$scriptName = "GroupIndirectLaborAdjEntry.php";
$scriptVarBase = "{$genericVarBase}&amp;groupNumber=" . urlencode(trim($groupNumber));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$programName = "HETBLM";

if ($TADSSF == 'Y') {
    $timeMax = '6';
    $timeFormat = "(HHMMSS)";
} else {
    $timeMax = '4';
    $timeFormat = "(HHMM)";
}

$stmtSQL = " Select * From HDGRPM Where BBGRP#=$groupNumber Fetch First 1 Row Only";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$groupRow = db2_fetch_assoc($sqlResult);

$backURL = $_SESSION[$fromURL];
if ($backURL == "") {
    $backURL = "{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=261";
}
$_SESSION[$fromURL] = $backURL;

if ($tag == 'ENTRY') {
    $stmtSQL = " Delete From ETGLAW Where WAXHND='$profileHandle'";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $loadGLAW = 'Y';
}

if ($tag == "Edit_Data") {
    
    $edtVar = "";
    
    Concat_Field("@@grpn", $groupNumber);
    Concat_Field("@@rdsp", $_POST['redisplay']);
    
    Concat_Field("@@date", $_POST['transDate']);
    Concat_Field("@@strt", $_POST['strt']);
    Concat_Field("@@stop", $_POST['stop']);
    Concat_Field("@@dtrc", strtoupper($_POST['dtrc']));
    if (isset($_POST['plt'])) {
        Concat_Field("@@plt@", $_POST['plt']);
    }
    if (isset($_POST['dept'])) {
        Concat_Field("@@dept", strtoupper($_POST['dept']));
    }
    if (isset($_POST['workCenter'])) {
        Concat_Field("@@wc@@", strtoupper($_POST['workCenter']));
    }
    $edtVar .= "}{";
    
    $returnValue = Maintain_Edit_Handle("HETGLA_W", $profileHandle, $maintenanceCode, $errFound, $edtVar, $errVar, $wrnVar);
    $maintenanceCode = $returnValue['maintenanceCode'];
    $errFound = $returnValue['errFound'];
    $edtVar = $returnValue['edtVar'];
    $errVar = $returnValue['errVar'];
    $wrnVar = $returnValue['wrnVar'];
    
    if ($errFound == "" && $_POST['displayedRows'] > '0' && $_POST['redisplay'] == "") {
        $confMessage = "Confirm Add Of Indirect Labor for " . Format_Confirm_Desc("{$groupRow['BBDESC']}", "{$groupNumber}", "", "", "", "");
        print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$backURL}&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        exit();
    }
    
    $loadGLAW = $_POST['loadGLAW'];
}

require_once ($docType);
print "\n <html> <head>";
require_once ($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'AJAXRequest.js';
require_once 'CheckEnterChg.php';
require_once 'Menu.js';
require_once 'CalendarInclude.php';
require_once 'DateEdit.php';
require_once 'NumEdit.php';
require_once 'UpperCase.php';
require_once 'CheckEnterChg.php';
?>
function empDelete(delDate,delEmid,delShft,delRecs) {
	<?php print "\n var url =\"{$homeURL}{$phpPath}GroupLaborAdjEmpUpdate.php?baseVar=" . urlencode($baseVar) . "&eID=" . urlencode($eID) . "\"; \n"; ?>
    	url += "&maintCd=D";
    	url += "&transDate=" + escape(delDate);
    	url += "&emid=" + escape(delEmid);
    	url += "&shft=" + escape(delShft);
    	url += "&recs=" + escape(delRecs);
    	url += "&dummy=" + new Date().getTime();
	var ajaxRequest = new ajaxObject(url,empDeleteResponse);
		ajaxRequest.update();
}

function empDeleteResponse(responseText, responseStatus) {
	if (responseStatus==200) {
	  document.Chg.redisplay.value='Y';
	  document.Chg.submit();
    } else  { 
      alert(responseStatus + " -- Error Deleting Employee");
    }
}

function validate(chgForm) {
  if (document.Chg.transDate.value ==""
      || document.Chg.strt.value ==	""
      || document.Chg.stop.value ==""
      || document.Chg.dtrc.value ==""
      <?php if ($HDMERL > 0) : ?>
      || document.Chg.plt.value ==""
      || document.Chg.dept.value ==""
      || document.Chg.workCenter.value ==""
      <?php elseif ($HDPERL > 0 || $HDPRRL > 0) : ?>
      || document.Chg.dept.value ==""
      <?php endif; ?>
     ) {alert("<?php echo $reqFieldError; ?>"); return false;}
  if (editdate(document.Chg.transDate) &&
      <?php if ($HDMERL > 0) : ?>
      editNum(document.Chg.plt, 3, 0) &&	
      <?php endif; ?>
      <?php if ($TADSSF == 'Y') : ?>
      editNum(document.Chg.strt, 6, 0) &&
      editNum(document.Chg.stop, 6, 0)
      <?php else : ?>
      editNum(document.Chg.strt, 4, 0) &&
      editNum(document.Chg.stop, 4, 0)
      <?php endif; ?>
     ) return true;
 }
 
function confirmDelete(text) {return confirm("<?php echo $delRecordConf; ?>" + "\n\n" + text);}
</script>
<?php
require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "GROUPINDIRECTLABORADJENTRY";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

require_once 'MaintainTop.php';

print "\n <table $contentTable> ";
Format_Header("Group", $groupRow['BBDESC'], $groupNumber);
$scheduleDesc = RetValue("SMSCHD={$groupRow['BBSCHD']} and SMEFFS is null", "HDSCHM", "SMDESC");
Format_Header("Schedule", $scheduleDesc, $groupRow['BBSCHD']);
print "\n </table> ";

print $hrTagAttr;
require_once 'RequiredField.php';
require_once 'ErrorDisplay.php';

if ($errFound != "") {
    $focusField = "";
    
    $Err_date = DecatErr_Field("@@date", "transDate");
    $Err_start = DecatErr_Field("@@strt", "strt");
    $Err_stop = DecatErr_Field("@@stop", "stop");
    $Err_dtrc = DecatErr_Field("@@dtrc", "dtrc");
    if ($HDMERL > 0) {
        $Err_plant = DecatErr_Field("@@plt@", "plt");
    }
    $Err_dept = DecatErr_Field("@@dept", "dept");
    
    $transDate = Decat_Field("@@date", $edtVar);
    $startTime = Decat_Field("@@strt", $edtVar);
    $stopTime = Decat_Field("@@stop", $edtVar);
    $dtrc = Decat_Field("@@dtrc", $edtVar);
    if ($HDMERL > 0) {
        $plant = Decat_Field("@@plt@", $edtVar);
        $wc = Decat_Field("@@wc@@", $edtVar);
    }
    $dept = Decat_Field("@@dept", $edtVar);
    
    $errFound = "";
} else {
    $transDate = (isset($_POST['transDate'])) ? $_POST['transDate'] : '';
    $startTime = (isset($_POST['strt'])) ? $_POST['strt'] : '';
    $stopTime = (isset($_POST['stop'])) ? $_POST['stop'] : '';
    $dtrc = (isset($_POST['dtrc'])) ? $_POST['dtrc'] : '';
    if ($HDMERL > 0) {
        $plant = (isset($_POST['plt'])) ? $_POST['plt'] : $groupRow['BBPLNT'];
        $wc = (isset($_POST['workCenter'])) ? $_POST['workCenter'] : $groupRow['BBWC'];
    }
    $dept = (isset($_POST['dept'])) ? $_POST['dept'] : $groupRow['BBDEPT'];
    
    if (isset($_POST['transDate'])) {
        $focusField = 'strt';
    } else {
        $focusField = "transDate";
    }
}

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";
print "\n <table $contentTable>";

$transDateISO = Date_To_ISO($transDate);
if ($tag !== 'ENTRY' && empty($Err_date)) {
    // Load Default list of Employees
    if ($loadGLAW == 'Y') {
        $stmtSQL = " Insert Into ETGLAW
        (WAXHND,WADATE,WAEMID,WASHFT,WARECS,WASTRT,WASTOP,
        WALNAM,WAFNAM,WAMIDI,WACO,WAFAC,WAEMP,WASCTL,WASCHD)
        Select '$profileHandle',LBDATE,LBEMID,LBSHFT,LBRECS,LBSTRT,LBSTOP,
        EMLNAM,EMFNAM,EMMIDI,LBCO,LBFAC,LBEMP,LBSCTL,LBSCHD
        From HREMPL inner join SIMLBP on LBCO=EMCOMP and LBFAC=EMFACL and LBEMP=EMEMPL
        Where EMHGRP=$groupNumber and LBDATE='$transDateISO' and LBDTCL='15'
          and not exists (Select EHEMID from HDMECH Where EHEMID=EMEMID and EHDATE='$transDateISO' and EHTRAN='10') ";
        
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
        
        $loadGLAW = 'N';
    }
    print "\n <tr><td class=\"dsphdr\">Transaction Date</td>";
    print "\n     <td class=\"dspdesc\"><input type=\"hidden\" name=\"transDate\" value=\"{$transDate}\">$transDate";
    print "\n         <span class=\"dspdesc\" id=\"dateDesc\">Monday</span>";
    print "\n     </td>";
    print "\n </tr> ";
} else {
    Build_Fld_Entry("Transaction Date", "transDate", "inputnmbr", "Date", "", $transDate, $Err_date, "", "", "Y", "", "");
}

Build_Fld_Entry("Start Time $timeFormat", "strt", "inputnmbr", "", "", $startTime, $Err_start, "6", $timeMax, "Y", "", "");

Build_Fld_Entry("Stop Time $timeFormat", "stop", "inputnmbr", "", "", $stopTime, $Err_stop, "6", $timeMax, "Y", "", "");

$fieldDesc = RetValue("EVTYPE='I' and EVCODE='{$dtrc}'", "HDEVNT ", "EVDESC");
$textOvr = SetTextOvr($Err_dtrc);
print "\n <tr><td class=\"dsphdr\"><span $textOvr>Indirect/Downtime Code</span></td>";
print "\n     <td class=\"inputalph\"><input name=\"dtrc\" type=\"text\" value=\"{$dtrc}\" size=\"5\" maxlength=\"2\"> $reqFieldChar";
print "\n                             <a href=\"{$homeURL}{$phpPath}ETCodeSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=dtrc&amp;fldDesc=dtrcDesc&amp;fldType=I\" onclick=\"$searchWinVar\"> $searchImage</a> ";
print "\n                             <span class=\"dspdesc\" id=\"dtrcDesc\">$fieldDesc</span>";
print "\n     </td>";
print "\n </tr> ";
DspErrMsg($Err_dtrc);

if ($HDMERL > 0) {
    $fieldDesc = RetValue("PLPLNT={$plant}", "HDPLNT ", "PLNAME");
    $textOvr = SetTextOvr($Err_plant);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Plant Number</span></td>";
    print "\n     <td class=\"inputnmbr\"><input name=\"plt\" type=\"text\" value=\"{$plant}\" size=\"5\" maxlength=\"3\"> $reqFieldChar";
    print "\n                             <a href=\"{$homeURL}{$phpPath}PlantSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=plt&amp;fldDesc=pltDesc\" onclick=\"$searchWinVar\"> $searchImage </a> ";
    print "\n         <span class=\"dspdesc\" id=\"pltDesc\">$fieldDesc</span>";
    print "\n     </td>";
    print "\n </tr> ";
    DspErrMsg($Err_plant);
    
    $fieldDesc = RetValue("WCPLT={$plant} and WCDEPT='{$dept}' and WCWC='{$wc}", "HDMWCM ", "WCDESC");
    $textOvr = SetTextOvr($Err_dept);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Department/Work Center</span></td>";
    print "\n     <td class=\"inputalph\"><input name=\"dept\" type=\"text\" value=\"{$dept}\" size=\"5\" maxlength=\"5\"> / <input name=\"workCenter\" type=\"text\" value=\"{$wc}\" size=\"5\" maxlength=\"5\"> $reqFieldChar";
    print "\n                             <a href=\"{$homeURL}{$phpPath}DeptWCSearch.php{$genericVarBase}&amp;tag=REPORT&amp;forPlant={$plant}&amp;docName=Chg&amp;fldPlant=&amp;fldPltName=&amp;flddept=dept&amp;fldWC=workCenter&amp;fldDesc=deptDesc\" onclick=\"$searchWinVar\"> $searchImage </a> ";
    print "\n                             <span class=\"dspdesc\" id=\"deptDesc\">$fieldDesc</span>";
    print "\n     </td>";
    print "\n </tr> ";
    DspErrMsg($Err_dept);
} elseif (($HDPERL > 0 || $HDPRRL > 0)) {
    $fieldDesc = RetValue("EADEPT='{$dept}'", "PREXAC ", "EANAME");
    $textOvr = SetTextOvr($Err_dept);
    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Department</span></td>";
    print "\n     <td class=\"inputalph\"><input name=\"dept\" type=\"text\" value=\"{$dept}\" size=\"5\" maxlength=\"5\"> $reqFieldChar";
    print "\n                             <a href=\"{$homeURL}{$phpPath}DepartmentSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldName=dept&amp;fldDesc=deptDesc\" onclick=\"$searchWinVar\"> $searchImage </a> ";
    print "\n                             <span class=\"dspdesc\" id=\"deptDesc\">$fieldDesc</span>";
    print "\n     </td>";
    print "\n </tr> ";
    DspErrMsg($Err_dept);
}
print "\n <tr><td><input type=\"hidden\" name=\"loadGLAW\" value=\"$loadGLAW\"></td></tr>";
print "\n <tr><td><input type=\"hidden\" name=\"redisplay\" value=\"\"></td></tr>";

// Employees
require 'stmtSQLClear.php';
$stmtSQL .= " Select WALNAM, WAFNAM, WAMIDI, WASTRT, WASTOP, WADATE, WAEMID, WASHFT, WARECS";
$fileSQL .= " ETGLAW ";
$selectSQL .= " WAXHND='$profileHandle' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By WALNAM, WAFNAM, WARECS";
require 'stmtSQLEnd.php';

$pageSelectList = "Y";
require 'stmtSQLTotalRows.php';
print "\n <tr><td><input type=\"hidden\" name=\"displayedRows\" value=\"$sql_Record_Count\"></td></tr>";
print "\n </table>";

if ($sql_Record_Count > '0' || $loadGLAW == 'N') {
    
    print "\n <fieldset class=\"legendBody\"> ";
    print "\n <legend class=\"legendTitle\">Employees</legend>";
    
    print "\n <table $contentTable>";
    print "\n <tr><td colspan=2>&nbsp;</td>";
    print "\n     <td class=\"dsphdr\" colspan=2>Add Employee";
    print "\n                             <a href=\"{$homeURL}{$phpPath}GroupEmployeeShiftSearch.php{$genericVarBase}&amp;docName=Chg&amp;forDate={$transDateISO}\" onclick=\"$searchWinVar\"> $searchImage </a> ";
    print "\n     </td>";
    print "\n </tr> ";
    
    print "\n <tr><th class=\"colhdr\">Opt</th>";
    print "\n     <th class=\"colhdr\">Employee</th>";
    print "\n     <th class=\"colhdr\">Shift Start</th>";
    print "\n     <th class=\"colhdr\">Shift Stop</th>";
    print "\n </tr>";
    
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
    
    $rowCount = 0;
    while ($row = db2_fetch_assoc($sqlResult)) {
        $F_name = Format_EmplName(trim($row['WAFNAM']), trim($row['WALNAM']), trim($row['WAMIDI']), "", "", "");
        
        require 'SetRowClass.php';
        print "\n <tr class=\"$rowClass\">";
        
        // Maintenance Options
        $confirmDesc = Format_Confirm_Desc($F_name, "", "", "", "", "");
        print "\n <td class=\"opticon\">";
        print "\n     <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"javascript:empDelete('{$row['WADATE']}',{$row['WAEMID']},{$row['WASHFT']},{$row['WARECS']});\">$deleteImageSml</a>";
        print "\n </td>";
        
        print "\n <td class=\"colalph\">$F_name</td>";
        
        if ($TADSSF == "Y") {
            $F_BWSTRT = EditHrsMinSec($row['WASTRT']);
            $F_BWSTOP = EditHrsMinSec($row['WASTOP']);
        } else {
            $F_BWSTRT = EditHrsMin(substr($row['WASTRT'], 0, (strlen($row['WASTRT']) - 2)));
            $F_BWSTOP = EditHrsMin(substr($row['WASTOP'], 0, (strlen($row['WASTOP']) - 2)));
        }
        print "\n <td class=\"colnmbr\">$F_BWSTRT</td>";
        print "\n <td class=\"colnmbr\">$F_BWSTOP</td>";
        print "\n</tr>";
    }
    print "\n </table></fieldset>";
}

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

?>