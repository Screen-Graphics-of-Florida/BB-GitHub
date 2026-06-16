<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$backHome = $_GET ['backHome'];
$errFound = $_GET ['errFound'];
$wrnVar = $_GET ['wrnVar'];
$reportSelType = $_GET ['reportSelType'];
$jobSbmSched = $_GET ['jobSbmSched'];
$resetSelectionFlag = $_GET ['resetSelectionFlag'];
$rtvSelection = $_GET ['rtvSelection'];
$saveSelection = $_GET ['saveSelection'];
$scheduleJobSwitch = $_GET ['scheduleJobSwitch'];
$selScheduleJob = $_GET ['selScheduleJob'];
$submitSchedule = $_GET ['submitSchedule'];

require_once 'SetLibraryList.php';

require_once "GLControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "G/L Budget Worksheet";
$scriptName = "GLBudgetWorksheet.php";
$scriptVarBase = "{$genericVarBase}&amp;backHome=" . urlencode ( trim ( $backHome ) ) . "&amp;reportSelType=" . urlencode ( trim ( $reportSelType ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$submitNoSelection = "";
$submitCallProgram = "CGLBSH";
$submitEnvProgram = "HGLBSH";
$submitEnvPrinter = "HGLBSHPF";
$submitScheduleScript = "";
$applicationID = "GL";

if (is_null ( $tag )) {
	$tag = "REPORT";
}

if ($tag == "REPORT") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'EditFromToAllJava.js';
	require_once 'Menu.js';
	
	require_once 'CalendarInclude.php';
	require_once 'CheckEnterChg.php';
	require_once 'DateEdit.php';
	require_once 'DisplayHideSelCriteria.php';
	require_once 'NumEdit.php';
	
	print "\n function validate(chgForm) { ";
	print "\n   if (document.Chg.fiscalYear.value ==\"\"  ";
	print "\n   ) {alert(\"$reqFieldError\"); return false;} ";
	
	print "\n   if (editNum(document.Chg.fiscalYear, 4, 0) ";
	print "\n    && editdate(document.Chg.consolDate) ";
	print "\n    && editNum(document.Chg.budgetPlan, 3, 0) ";
	print "\n    && editNum(document.Chg.frCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.toCompany, 2, 0) ";
	print "\n    && editNum(document.Chg.frFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.toFacility, 4, 0) ";
	print "\n    && editNum(document.Chg.frAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.toAccount, 4, 0) ";
	print "\n    && editNum(document.Chg.frSubAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.toSubAcct, 4, 0) ";
	print "\n    && editNum(document.Chg.frPeriod, 2, 0) ";
	print "\n    && editNum(document.Chg.toPeriod, 2, 0) ";
	print "\n    && editFromToAll(document.Chg.frCompany, document.Chg.toCompany, document.Chg.allCompany, 2) ";
	print "\n    && editFromToAll(document.Chg.frFacility, document.Chg.toFacility, document.Chg.allFacility, 4) ";
	print "\n    && editFromToAll(document.Chg.frAccount, document.Chg.toAccount, document.Chg.allAccount, 4) ";
	print "\n    && editFromToAll(document.Chg.frSubAcct, document.Chg.toSubAcct, document.Chg.allSubAcct, 4) ";
	print "\n    && editFromTo(document.Chg.frPeriod, document.Chg.toPeriod, 2) ";
	print "\n    ) {return true;} ";
	print "\n } ";
	
	?>

  
  // Consoldation
  function OpenConsolReport() {showSel('showConsolReport');}  
  function CloseConsolReport() {hideSel('showConsolReport');} 
  function editCONS() { 
    if (document.getElementById('consolReport').checked) {OpenConsolReport();}
    else {CloseConsolReport();}
  } 
  
  // New Budget
  function OpenNewBudget() {showSel('showNewBudget');}  
  function CloseNewBudget() {hideSel('showNewBudget');} 
  function editNEWB() { 
    if (document.getElementById('newBudget').checked) {OpenNewBudget();}
    else {CloseNewBudget();}
  } 

  // Budget Plans
  var count = "1";
  function chgCursor() {document.body.style.cursor=(document.body.style.cursor=="move") ? "default" : "move";}  

  function addRow(tblName,dspseq,plannmbr,plandesc,delimg,movimg) {
  
    if (document.getElementById(plannmbr)) {alert(plandesc + ' already selected'); return;}

    var tbody = document.getElementById(tblName).getElementsByTagName("TBODY")[0];
    // create row
    var row = document.createElement("TR");
    row.setAttribute('id',plannmbr);
    
    var td0 = document.createElement("TD")
    td0.setAttribute('class','opticon');
    td0.setAttribute('className','opticon');  // For IE
    var img = document.createElement('IMG');
    img.setAttribute('src', movimg);
    img.setAttribute('title', 'Click to move');
    img.onmousedown = function(){zxcDragRow(this);}
    td0.appendChild(img);
    
    var td1 = document.createElement("TD")
    td1.setAttribute('class','colopt');
    td0.setAttribute('className','colopt');  // For IE
    var img = document.createElement('IMG');
    img.setAttribute('src', delimg);
    img.setAttribute('title', 'Remove row');
    img.onclick = function(){delRow(row);}
    td1.appendChild(img);
    
    var td2 = document.createElement("TD")
    td2.setAttribute('class','colnmbr');
    td2.setAttribute('className','colnmbr');  // For IE
    var strHtml = plannmbr;
    td2.innerHTML = strHtml.replace(/!count!/g,count);
    
    var td3 = document.createElement("TD")
    td3.setAttribute('class','colalph');
    td3.setAttribute('className','colalph');  // For IE
    var strHtml = plandesc;
    td3.innerHTML = strHtml.replace(/!count!/g,count);
    
    // append data to row
    row.appendChild(td0);
    row.appendChild(td1);
    row.appendChild(td2);
    row.appendChild(td3);
    // add to count variable
    count = parseInt(count) + 1;
    // append row to table
    tbody.appendChild(row);
    saveOrderList(tblName);
  }
  
function delRow(row){row.parentNode.removeChild(row);}

function saveOrderList(listId) {
  var rows = document.getElementById(listId).getElementsByTagName('TR');
  colSeq = "";
  for(var i=0; i
<rows.length ; i++){
    trId=rows[i].getAttribute(
	'id');
    colSeq +="|"
	+ trId;
  }
  document.getElementById('srtCol').value=colSeq; return
	true;
} 
  
var zxcRRow,zxcRows;
var zxcMseX,zxcMseY;

function
	zxcDragRow(zxcobj){
  if (zxcRRow){ return; }
  chgCursor();
  var
	zxcrow;
  var zxctbdy=zxcobj; while (zxctbdy.tagName!='TBODY'
	){
    if (zxctbdy.tagName== 'TR'){ zxcrow=zxctbdy;
	}
    zxctbdy=zxctbdy.parentNode; }
  zxcRRow=zxcrow;
	zxctbdy.removeChild(zxcrow);
  zxcRows=zxctbdy.getElementsByTagName(
	'TR');
}

function
	zxcDrop(event) {
  if (!zxcRRow){ return; }
  chgCursor();
  zxcMse(event);
  for (var
	zxc0=zxcRows.length-1;zxc0>=0;zxc0--){ if (zxcPos(zxcRows[zxc0])[1]<zxcMseY
	){
      zxcRows[zxc0].parentNode.insertBefore(zxcRRow,zxcRows[zxc0]);
      zxcRRow=null;
	return;
    }
  }
  zxcRows[0].parentNode.insertBefore(zxcRRow,zxcRows[0]);
}

function
	zxcPos(zxc){
  zxcObjLeft=zxc.offsetLeft; zxcObjTop=zxc.offsetTop;
	while(zxc.offsetParent!=null){ zxcObjParent=zxc.offsetParent;
	zxcObjLeft+=zxcObjParent.offsetLeft; zxcObjTop+=zxcObjParent.offsetTop;
	zxc=zxcObjParent; }
  return [zxcObjLeft,zxcObjTop];
}

function
	zxcMse(event){
  if(!event) var event=window.event;
	if (document.all){ zxcMseX=event.clientX+zxcDocS()[0];
	zxcMseY=event.clientY+zxcDocS()[1]; }
  else {zxcMseX=event.pageX;
	zxcMseY=event.pageY; }
}

function zxcDocS(){
  var
	zxcsx,zxcsy;
  if (!document.body.scrollTop){ zxcsx=document.documentElement.scrollLeft;
	zxcsy=document.documentElement.scrollTop;
	}
  else { zxcsx=document.body.scrollLeft;
	zxcsy=document.body.scrollTop;
	}
  return [zxcsx,zxcsy]
}

document.onmouseup=function(event){zxcDrop(event);saveOrderList(
	'selTable');}

<?php
	print "\n </script> \n";
	
	require_once ($genericHead);
	print "\n </head>";
	print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
	require_once 'Banner.php';
	print "\n <table $baseTable>";
	print "\n <tr valign=\"top\">";
	$pageID = "GLBUDGETWORKSHEET";
	require_once 'MenuDisplay.php';
	print "\n <td class=\"content\">";
	require 'SubmitScheduleTop.php';
	require 'ConfMessageDisplayNoTable.php';
	print $hrTagAttr;
	
	$focusField = "frCompany";
	
	// Fill Array with Budget Information
	$budgets = array ();
	
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select BGNMBR,BGDESC ";
	$stmtSQL .= " From GLBGHD ";
	$stmtSQL .= " Order By BGNMBR ";
	require 'stmtSQLEnd.php';
	
	$startRow = 1;
	$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$rowBdg = db2_fetch_assoc ( $sqlResult );
	
	while ( $rowBdg = db2_fetch_assoc ( $sqlResult, $startRow ) ) {
		if ($rowBdg) {
			$budgets [] = array ("sequence" => 0, "plan" => $rowBdg ['BGNMBR'], "desc" => $rowBdg ['BGDESC'] );
		}
		$startRow ++;
	}
	
	// Get Errors
	if ($errFound != "" || $scheduleJobSwitch == "Y") {
		$scheduleJobSwitch = "";
		$focusField = "";
		$edtVar = EdtVarErr ( $profileHandle, $edtVar );
		if ($errFound != "") {
			$errVar = ErrVarErr ( $profileHandle, $errVar );
			
			$Err_YEAR = DecatErr_Field ( "@@year", "fiscalYear" );
			$Err_CONS = DecatErr_Field ( "@@cons", "consolReport" );
			$Err_BALS = DecatErr_Field ( "@@bals", "incBalSheet" );
			$Err_NOAM = DecatErr_Field ( "@@noam", "noAmount" );
			$Err_NEWB = DecatErr_Field ( "@@newb", "newBudget" );
			$Err_NOTP = DecatErr_Field ( "@@notp", "incNotPlan" );
			$Err_CNSD = DecatErr_Field ( "@@cnsd", "consolDate" );
			$Err_PLAN = DecatErr_Field ( "@@plan", "budgetPlan" );
			$Err_PDSC = DecatErr_Field ( "@@pdsc", "planDesc" );
			
			$Err_FCO = DecatErr_Field ( "@@fco@", "frCompany" );
			$Err_TCO = DecatErr_Field ( "@@tco@", "toCompany" );
			$Err_ACO = DecatErr_Field ( "@@aco@", "allCompany" );
			
			$Err_FFAC = DecatErr_Field ( "@@ffac", "frFacility" );
			$Err_TFAC = DecatErr_Field ( "@@tfac", "toFacility" );
			$Err_AFAC = DecatErr_Field ( "@@afac", "allFacility" );
			
			$Err_FACC = DecatErr_Field ( "@@facc", "frAccount" );
			$Err_TACC = DecatErr_Field ( "@@tacc", "toAccount" );
			$Err_AACC = DecatErr_Field ( "@@aacc", "allAccount" );
			
			$Err_FSUB = DecatErr_Field ( "@@fsub", "frSubAcct" );
			$Err_TSUB = DecatErr_Field ( "@@tsub", "toSubAcct" );
			$Err_ASUB = DecatErr_Field ( "@@asub", "allSubAcct" );
			
			$Err_FPER = DecatErr_Field ( "@@fper", "frPeriod" );
			$Err_TPER = DecatErr_Field ( "@@tper", "toPeriod" );
			
			$Err_CNTR = DecatErr_Field ( "@@cntr", "budgetGounter" );
			
			require 'ScheduleJobErr.php'; // Schedule Entries Errors
		}
		$submitSchedule = Decat_Field ( "@@sbjb", $edtVar );
		
		$YEAR = Decat_Field ( "@@year", $edtVar );
		$CONS = Decat_Field ( "@@cons", $edtVar );
		$BALS = Decat_Field ( "@@bals", $edtVar );
		$NOAM = Decat_Field ( "@@noam", $edtVar );
		$NEWB = Decat_Field ( "@@newb", $edtVar );
		$NOTP = Decat_Field ( "@@notp", $edtVar );
		$CNSD = Decat_Field ( "@@cnsd", $edtVar );
		$PLAN = Decat_Field ( "@@plan", $edtVar );
		$PDSC = Decat_Field ( "@@pdsc", $edtVar );
		
		$FCO = Decat_Field ( "@@fco@", $edtVar );
		$TCO = Decat_Field ( "@@tco@", $edtVar );
		$ACO = Decat_Field ( "@@aco@", $edtVar );
		
		$FFAC = Decat_Field ( "@@ffac", $edtVar );
		$TFAC = Decat_Field ( "@@tfac", $edtVar );
		$AFAC = Decat_Field ( "@@afac", $edtVar );
		
		$FACC = Decat_Field ( "@@facc", $edtVar );
		$TACC = Decat_Field ( "@@tacc", $edtVar );
		$AACC = Decat_Field ( "@@aacc", $edtVar );
		
		$FSUB = Decat_Field ( "@@fsub", $edtVar );
		$TSUB = Decat_Field ( "@@tsub", $edtVar );
		$ASUB = Decat_Field ( "@@asub", $edtVar );
		
		$FPER = Decat_Field ( "@@fper", $edtVar );
		$TPER = Decat_Field ( "@@tper", $edtVar );
		$APER = Decat_Field ( "@@aper", $edtVar );
		
		$SORT = Decat_Field ( "@@sort", $edtVar );
		$col_sort = explode('|',$SORT);
		
		$xx = 10;
		foreach ( $col_sort as $colID ) {
			if (trim ( $colID ) == "" || trim ( $colID ) == "null") {
				continue;
			}
			$xx ++;
			$SEQ = Decat_Field ( "@@sq$xx", $edtVar );
			$BDG = Decat_Field ( "@@bd$xx", $edtVar );
			for($i = 0; $i < count ( $budgets ); $i ++) {
				if ($budgets [$i] ["plan"] == $BDG) {
					$budgets [$i] ["sequence"] = $SEQ;
				}
			}
		}
		
		require 'ScheduleJobValue.php'; // Schedule Entries Values
	} else {
		$CONS = "N";
		$BALS = "N";
		$NOAM = "N";
		$NEWB = "N";
		$NOTP = "N";
		$CNSD = DateInputFromCYMD ( DateTodayCYMD () );
		
		$ACO = "ALL";
		$AFAC = "ALL";
		$AACC = "ALL";
		$ASUB = "ALL";
		$APER = "ALL";
	}
	if ($ACO == "ALL") {
		$checked_ACO = "CHECKED";
	} else {
		$checked_ACO = "";
	}
	if ($AFAC == "ALL") {
		$checked_AFAC = "CHECKED";
	} else {
		$checked_AFAC = "";
	}
	if ($AACC == "ALL") {
		$checked_AACC = "CHECKED";
	} else {
		$checked_AACC = "";
	}
	if ($ASUB == "ALL") {
		$checked_ASUB = "CHECKED";
	} else {
		$checked_ASUB = "";
	}
	if ($APER == "ALL") {
		$checked_APER = "CHECKED";
	} else {
		$checked_APER = "";
	}
	
	print "\n <table $quickLinkTable> ";
	print "\n   <tr> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#printOption\">Print Option</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#fromToAll\">From/To/All</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#selected\">Selected Budget Plans</a></td> ";
	print "\n     <td class=\"quickLinkTabs\"><a href=\"#plans\">Budget Plans</a></td> ";
	print "\n   </tr> ";
	print "\n </table> ";
	
	print $hrTagAttr;
	
	require_once 'RequiredField.php';
	require_once 'ErrorDisplay.php';
	
	print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" onSubmit=\"return validate(document.Chg)\" ACTION=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=Edit_Data&amp;wrnVar=" . urlencode ( trim ( $wrnVar ) ) . "\">";
	
	print "\n <a name=\"printOption\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">Print Option</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	
	Build_Fld_Entry ( "Fiscal Year", "fiscalYear", "inputnmbr", "", "YEAR", $YEAR, $Err_YEAR, "4", "4", "Y", "", "" );
	
	// Consolidated Report
	$fldChecked = Field_Checked ( $CONS, "Y" );
	$textOvr = SetTextOvr ( $Err_CONS );
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Consolidated Report</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"checkbox\" name=\"consolReport\" id=\"consolReport\" value=\"Y\" $fldChecked onClick=\"editCONS()\"></td>";
	print "\n </tr> ";
	DspErrMsg ( $Err_CONS );
	
	Build_Fld_Entry ( "Include Balance Sheet Accounts", "incBalSheet", "inputalph", "YORN", "BALS", $BALS, $Err_BALS, "1", "1", "", "", "" );
	Build_Fld_Entry ( "Calculate Budget If Account Has No Budget", "noAmount", "inputalph", "YORN", "NOAM", $NOAM, $Err_NOAM, "1", "1", "", "", "" );
	
	// Create New Budget Plan
	$fldChecked = Field_Checked ( $NEWB, "Y" );
	$textOvr = SetTextOvr ( $Err_NEWB );
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Create New Budget Plan</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"checkbox\" name=\"newBudget\" id=\"newBudget\" value=\"Y\" $fldChecked onClick=\"editNEWB()\"></td>";
	print "\n </tr> ";
	DspErrMsg ( $Err_NEWB );
	
	Build_Fld_Entry ( "Include Accounts Not In Plan", "incNotPlan", "inputalph", "YORN", "NOTP", $NOTP, $Err_NOTP, "1", "1", "", "", "" );
	print "\n </table> ";
	
	// Consolidation (hidden DIV)
	print "\n <div id=\"showConsolReport\">";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Consolidated Report ";
	Print "\n </legend> ";
	print "\n <table $contentTable> ";
	Build_Fld_Entry ( "Consolidation Date", "consolDate", "inputdate", "Date", "CNSD", $CNSD, $Err_CNSD, "6", "6", "", "", "" );
	print "\n </table> ";
	print "\n <script TYPE=\"text/javascript\">";
	if (trim ( $CONS ) == "Y") {
		print "\n OpenConsolReport()";
	} else {
		print "\n CloseConsolReport()";
	}
	print "\n </script>";
	print "\n </fieldset> ";
	print "\n </div>";
	
	// New Budget (hidden DIV)
	print "\n <div id=\"showNewBudget\">";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Create New Budget Plan ";
	Print "\n </legend> ";
	print "\n <table $contentTable> ";
	$fieldDesc = RetValue ( "BGNMBR=$PLAN", "GLBGHD", "BGDESC" );
	$textOvr = SetTextOvr ( $Err_PLAN );
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>New Budget Plan</span></td> ";
	print "\n     <td class=\"inputnmbr\"><input type=\"text\" name=\"budgetPlan\" value=\"" . rtrim ( $PLAN ) . "\" size=\"3\" maxlength=\"3\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}BudgetPlanSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;fldName=budgetPlan&amp;fldDesc=none\" onclick=\"$searchWinVar\"> $searchImage</a></td>";
	print "\n </tr> ";
	DspErrMsg ( $Err_PLAN );
	Build_Fld_Entry ( "New Budget Plan Description", "planDesc", "inputalph", "", "PDSC", $PDSC, $Err_PDSC, "30", "30", "", "", "" );
	print "\n </table> ";
	print "\n <script TYPE=\"text/javascript\">";
	if (trim ( $NEWB ) == "Y") {
		print "\n OpenNewBudget()";
	} else {
		print "\n CloseNewBudget()";
	}
	print "\n </script>";
	print "\n </fieldset> ";
	print "\n </div>";
	
	print "\n </fieldset> ";
	
	// From/To-All
	print "\n <a name=\"fromToAll\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n     <legend class=\"legendTitle\">From/To/All</legend> ";
	require 'TopOfForm.php';
	print "\n     <table $contentTable> ";
	print "\n         <tr><td>&nbsp;</td> ";
	print "\n             <td class=\"colhdr\">From</td> ";
	print "\n             <td class=\"colhdr\">To</td> ";
	print "\n             <td class=\"colhdr\">All</td> ";
	print "\n         </tr> ";
	
	$textOvr = SetTextOvr ( $Err_FCO );
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_TCO );
	}
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_ACO );
	}
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Company</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frCompany\" value=\"$FCO\" size=\"2\" maxlength=\"2\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=frCompany&amp;fldFac=frFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toCompany\" value=\"$TCO\" size=\"2\" maxlength=\"2\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}CoFacSearch.php{$genericVarBase}&amp;docName=Chg&amp;fldCo=toCompany&amp;fldFac=toFacility&amp;fldDesc=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allCompany\" value=\"ALL\" $checked_ACO onClick=\"if (this.checked) this.form.frCompany.value='', this.form.toCompany.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg ( $Err_FCO );
	DspErrMsg ( $Err_TCO );
	DspErrMsg ( $Err_ACO );
	
	$textOvr = SetTextOvr ( $Err_FFAC );
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_TFAC );
	}
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_AFAC );
	}
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Facility</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frFacility\" value=\"$FFAC\" size=\"4\" maxlength=\"4\"></td>";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toFacility\" value=\"$TFAC\" size=\"4\" maxlength=\"4\"></td>";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allFacility\" value=\"ALL\" $checked_AFAC onClick=\"if (this.checked) this.form.frFacility.value='', this.form.toFacility.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg ( $Err_FFAC );
	DspErrMsg ( $Err_TFAC );
	DspErrMsg ( $Err_AFAC );
	
	$textOvr = SetTextOvr ( $Err_FACC );
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_TACC );
	}
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_AACC );
	}
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Account Number</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frAccount\" value=\"$FACC\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=frAccount&amp;subFld=frSubAcct&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toAccount\" value=\"$TACC\" size=\"4\" maxlength=\"4\">";
	print "\n                                     <a href=\"{$homeURL}{$phpPath}AccountSearch.php{$genericVarBase}&amp;docName=Chg&amp;acctFld=toAccount&amp;subFld=toSubAcct&amp;descFld=none\" onclick=\"$searchWinVar\">$searchImage </a></td> ";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allAccount\" value=\"ALL\" $checked_AACC onClick=\"if (this.checked) this.form.frAccount.value='', this.form.toAccount.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg ( $Err_FACC );
	DspErrMsg ( $Err_TACC );
	DspErrMsg ( $Err_AACC );
	
	$textOvr = SetTextOvr ( $Err_FSUB );
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_TSUB );
	}
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_ASUB );
	}
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Subaccount Number</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frSubAcct\" value=\"$FSUB\" size=\"4\" maxlength=\"4\"></td>";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toSubAcct\" value=\"$TSUB\" size=\"4\" maxlength=\"4\"></td>";
	print "\n             <td class=\"inputalph\"><input type=\"checkbox\" name=\"allSubAcct\" value=\"ALL\" $checked_ASUB onClick=\"if (this.checked) this.form.frSubAcct.value='', this.form.toSubAcct.value='';\"></td> ";
	print "\n         </tr> ";
	DspErrMsg ( $Err_FSUB );
	DspErrMsg ( $Err_TSUB );
	DspErrMsg ( $Err_ASUB );
	
	$textOvr = SetTextOvr ( $Err_FPER );
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_TPER );
	}
	if ($textOvr == "") {
		$textOvr = SetTextOvr ( $Err_APER );
	}
	print "\n         <tr><td class=\"dsphdr\"><span $textOvr>Substitute Budget With Last<br>Year Actual For Periods</span></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"frPeriod\" value=\"$FPER\" size=\"2\" maxlength=\"2\"></td> ";
	print "\n             <td class=\"inputnmbr\"><input type=\"text\" name=\"toPeriod\" value=\"$TPER\" size=\"2\" maxlength=\"2\"></td> ";
	print "\n         </tr> ";
	DspErrMsg ( $Err_FPER );
	DspErrMsg ( $Err_TPER );
	DspErrMsg ( $Err_APER );
	
	print "\n </table> ";
	print "\n </fieldset> ";
	
	// Selected Plans
	print "\n <a name=\"selected\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Selected Budget Plans</legend> ";
	require 'TopOfForm.php';
	print "\n <div class=\"page\">To move a plan, select the Click to move icon $wildSortImage and while holding down <br>the button move the mouse to the column you want to move it above and then release the button.<br>&nbsp;</div> ";
	
	print "\n <table id=\"selTable\" $contentTable>";
	print "\n     <tr><th class=\"colhdr\" colspan=\"2\">Opt</th>";
	print "\n         <th class=\"colhdr\">Budget Plan</th>";
	print "\n         <th class=\"colhdr\">Description</th>";
	print "\n     </tr>";
	print "\n     <tr><td><INPUT TYPE=\"hidden\" ID=\"srtCol\" NAME=\"srtCol\"></td></tr>";
	
	print "\n <script TYPE=\"text/javascript\">";
	$budgets = subval_sort ( $budgets, 'sequence' );
	foreach ( $budgets as $bdg ) {
		$sequence = $bdg ['sequence'];
		$plan = $bdg ['plan'];
		$desc = $bdg ['desc'];
		if ($sequence > 0) {
			print "\n addRow('selTable','" . trim ( $sequence ) . "','" . trim ( $plan ) . "','" . trim ( $desc ) . "','{$homeURL}{$imagePath}smDelete.gif','{$homeURL}{$imagePath}lgSqSort.gif')";
		}
	}
	print "\n </script>";
	print "\n </table> ";
	print "\n </fieldset> ";
	
	// Budget Plans
	print "\n <a name=\"plans\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Budget Plans</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	print "\n     <tr><th class=\"colhdr\">Budget Plan</th>";
	print "\n         <th class=\"colhdr\">Description</th>";
	print "\n     </tr>";
	DspErrMsg ( $Err_CNTR );
	
	$budgets = subval_sort ( $budgets, 'plan' );
	foreach ( $budgets as $bdg ) {
		$sequence = $bdg ['sequence'];
		$plan = $bdg ['plan'];
		$desc = $bdg ['desc'];
		require 'SetRowClass.php';
		print "\n <tr class=\"$rowClass\"> ";
		print "\n     <td class=\"colnmbr\">$plan</td>";
		print "\n     <td class=\"colalph\"><a href=\"javascript:addRow('selTable','" . trim ( $sequence ) . "','" . trim ( $plan ) . "','" . trim ( $desc ) . "','{$homeURL}{$imagePath}smDelete.gif','{$homeURL}{$imagePath}lgSqSort.gif')\">$desc</a></td>";
		print "\n </tr> ";
	}
	print "\n </table> ";
	print "\n </fieldset> ";
	
	$envProgram = $submitEnvProgram;
	$envPrinter = $submitEnvPrinter;
	require 'ScheduleJob.php';
	require 'SubmitScheduleBottom.php';
	print "\n $hrTagAttr ";
	
	if ($focusField != "") {
		print "\n <script TYPE=\"text/javascript\"> ";
		print "\n document.Chg.$focusField.focus(); ";
		print "\n </script> ";
	}
	print "\n </form>";
	require_once 'Copyright.php';
	print "\n </td> </tr> </table>";
	require_once 'Trailer.php';
	print "</body> </html>";
	exit ();
}

if ($tag == "Edit_Data") {
	$edtVar = "";
	if (! isset ( $_POST ['consolReport'] )) {
		$_POST ['consolReport'] = "N";
	}
	Concat_Field ( "@@cons", $_POST ['consolReport'] );
	if (! isset ( $_POST ['incBalSheet'] )) {
		$_POST ['incBalSheet'] = "N";
	}
	Concat_Field ( "@@bals", $_POST ['incBalSheet'] );
	if (! isset ( $_POST ['noAmount'] )) {
		$_POST ['noAmount'] = "N";
	}
	Concat_Field ( "@@noam", $_POST ['noAmount'] );
	if (! isset ( $_POST ['newBudget'] )) {
		$_POST ['newBudget'] = "N";
	}
	Concat_Field ( "@@newb", $_POST ['newBudget'] );
	if (! isset ( $_POST ['incNotPlan'] )) {
		$_POST ['incNotPlan'] = "N";
	}
	Concat_Field ( "@@notp", $_POST ['incNotPlan'] );
	Concat_Field ( "@@year", $_POST ['fiscalYear'] );
	Concat_Field ( "@@cnsd", $_POST ['consolDate'] );
	Concat_Field ( "@@plan", $_POST ['budgetPlan'] );
	Concat_Field ( "@@pdsc", $_POST ['planDesc'] );
	
	Concat_Field ( "@@fco@", $_POST ['frCompany'] );
	Concat_Field ( "@@tco@", $_POST ['toCompany'] );
	if (! isset ( $_POST ['allCompany'] )) {
		$_POST ['allCompany'] = "";
	}
	Concat_Field ( "@@aco@", $_POST ['allCompany'] );
	
	Concat_Field ( "@@ffac", $_POST ['frFacility'] );
	Concat_Field ( "@@tfac", $_POST ['toFacility'] );
	if (! isset ( $_POST ['allFacility'] )) {
		$_POST ['allFacility'] = "";
	}
	Concat_Field ( "@@afac", $_POST ['allFacility'] );
	
	Concat_Field ( "@@facc", $_POST ['frAccount'] );
	Concat_Field ( "@@tacc", $_POST ['toAccount'] );
	if (! isset ( $_POST ['allAccount'] )) {
		$_POST ['allAccount'] = "";
	}
	Concat_Field ( "@@aacc", $_POST ['allAccount'] );
	
	Concat_Field ( "@@fsub", $_POST ['frSubAcct'] );
	Concat_Field ( "@@tsub", $_POST ['toSubAcct'] );
	if (! isset ( $_POST ['allSubAcct'] )) {
		$_POST ['allSubAcct'] = "";
	}
	Concat_Field ( "@@asub", $_POST ['allSubAcct'] );
	
	Concat_Field ( "@@fper", $_POST ['frPeriod'] );
	Concat_Field ( "@@tper", $_POST ['toPeriod'] );
	
	Concat_Field ( "@@sort", $_POST ['srtCol'] );
	$col_sort = explode('|',$_POST['srtCol'] );
	
	$xx = 10;
	foreach ( $col_sort as $colID ) {
		if (trim ( $colID ) == "" || trim ( $colID ) == "null") {
			continue;
		}
		$xx ++;
		Concat_Field ( "@@sq$xx", $xx );
		Concat_Field ( "@@bd$xx", $colID );
	}
	Concat_Field ( "@@cntr", $xx );
	
	require 'ScheduleJobConcat.php'; // Schedule Entries Values
	$edtVar .= "}{";
	
	$returnValue = Selection_Edit_Handle ( "HGLBWS_W", $profileHandle, $dataBaseID, $submitSchedule, $errFound, $edtVar, $errVar, $wrnVar );
	$submitSchedule = $returnValue ['submitSchedule'];
	$errFound = $returnValue ['errFound'];
	$edtVar = $returnValue ['edtVar'];
	$errVar = $returnValue ['errVar'];
	$wrnVar = $returnValue ['wrnVar'];
	
	require 'SubmitScheduleUpdate.php';
	exit ();
}

?>