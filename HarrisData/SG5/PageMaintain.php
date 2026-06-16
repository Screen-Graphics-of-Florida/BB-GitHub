<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$maintenanceCode    = $_GET['maintenanceCode'];
$errFound           = $_GET['errFound'];
$pagID        = (isset($_GET['pagID']))     ? $_GET['pagID']     : 0;
$role         = (isset($_GET['role']))      ? $_GET['role']      : "";
$user         = (isset($_GET['user']))      ? $_GET['user']      : "";
$tableName    = (isset($_GET['tableName'])) ? $_GET['tableName'] : "";
$tableDesc    = (isset($_GET['tableDesc'])) ? $_GET['tableDesc'] : "";
$intHD        = (isset($_GET['intHD']))     ? $_GET['intHD']     : "";
$selectAll    = (isset($_GET['selectAll'])) ? $_GET['selectAll'] : "";

$includeName= "{$homePath}APControl{$dataBaseID}.php";        if (file_exists($includeName)) {require_once "APControl$dataBaseID.php";}
$includeName= "{$homePath}ARControl{$dataBaseID}.php";        if (file_exists($includeName)) {require_once "ARControl$dataBaseID.php";}
$includeName= "{$homePath}ETControl{$dataBaseID}.php";        if (file_exists($includeName)) {require_once "ETControl$dataBaseID.php";}
$includeName= "{$homePath}GLControl{$dataBaseID}.php";        if (file_exists($includeName)) {require_once "GLControl$dataBaseID.php";}
$includeName= "{$homePath}InventoryControl{$dataBaseID}.php"; if (file_exists($includeName)) {require_once "InventoryControl$dataBaseID.php";}
$includeName= "{$homePath}OEControl{$dataBaseID}.php";        if (file_exists($includeName)) {require_once "OEControl$dataBaseID.php";}
$includeName= "{$homePath}PEControl{$dataBaseID}.php";        if (file_exists($includeName)) {require_once "PEControl$dataBaseID.php";}
$includeName= "{$homePath}POControl{$dataBaseID}.php";        if (file_exists($includeName)) {require_once "POControl$dataBaseID.php";}
$includeName= "{$homePath}PRControl{$dataBaseID}.php";        if (file_exists($includeName)) {require_once "PRControl$dataBaseID.php";}
$includeName= "{$homePath}SystemControl{$dataBaseID}.php";    if (file_exists($includeName)) {require_once "SystemControl$dataBaseID.php";}

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once "SystemControl$dataBaseID.php";
require_once 'VarBase.php';

$page_title     = "Page Maintenance";
$scriptName     = "PageMaintain.php";
$scriptVarBase  = "{$genericVarBase}&amp;tblID=" . urlencode($tblID) . "&amp;tableName=" . urlencode(trim($tableName)) . "&amp;tableDesc=" . urlencode(trim($tableDesc)) . "&amp;pagID=" . urlencode($pagID) . "&amp;intHD=" . urlencode(trim($intHD));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$programName    = "HSYXXX";
$backURL="{$homeURL}{$phpPath}Page.php{$scriptVarBase}";

if ($admin!="Y") {
	require_once 'ProgSecurityError.php';
	exit;
}

$stmtSQL = "Select DSXML From SYDCST Where DSTBID=$tblID";
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
if (is_resource ( $sqlResult )) {
	$row = db2_fetch_array ( $sqlResult );
	$xmlDocset = simplexml_load_string ( $row[0] );
}

$docsetTable  = $xmlDocset->table;
$docsetRow    = $xmlDocset->row;
$docsetLink   = $xmlDocset->link;
$docsetFilter = $xmlDocset->filter;
$link_count   = count($docsetLink->xpath("linkid"));
$filter_count = count($docsetFilter->xpath("checkbox"));
foreach ($docsetRow->col as $col) {
	$colLabel = (string) trim(strtoupper($col[0]->label));
	if (strpos($colLabel, "@@parm[") !== false) {
		$parmName = Decat_Parm($colLabel);
		if ($GLOBALS["$parmName"] != "") {$colLabel = $GLOBALS["$parmName"];}
		else {continue;}
	}
	$colName  = trim(strtoupper($col['id']));
	$colSort = $colLabel . $colName;
	$col_sort[$colSort]= $colName;
}
ksort($col_sort);

if ($tag == "MAINTAIN") {
	require_once ($docType);
	print "\n <html> <head>";
	require_once ($headInclude);
	$formName = "Chg";
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterChg.php';
	require_once 'Menu.js';
	require_once 'NumEdit.php';
	print "\n function validate(chgForm) {";
	print "\n if (document.Chg.pageDesc.value ==\"\")";
	print "\n {alert(\"$reqFieldError\"); return false;} ";
	print "\n if (editNumPos(document.Chg.overrideRows, 5, 0)) ";
	print "\n return true;";
	print "\n }";
	print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\")}";
?>
  var count = "1";
  function chgCursor() { 
    document.body.style.cursor=(document.body.style.cursor=="move") ? "default" : "move"; 
  } 
  function addRow(tblName,collabel,colhdg,colname,dspseq,colsize,coldec,coltot,delimg,movimg,userdef,dftsrch,colopr,colcmp,colclr)
  {
	var wrkhdg = colhdg.replace("<br>", " ");
    if (document.getElementById(colname)) {alert(wrkhdg + ' already selected'); return;}

    var tbody = document.getElementById(tblName).getElementsByTagName("TBODY")[0];
    // create row
    var row = document.createElement("TR");
    row.setAttribute('id',colname);
    
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
    td1.setAttribute('className','colopt');  // For IE
    var img = document.createElement('IMG');
    img.setAttribute('src', delimg);
    img.setAttribute('title', 'Remove row');
    img.onclick = function(){delRow(row);}
    td1.appendChild(img);
    
    var td2 = document.createElement("TD")
    td2.setAttribute('class','colalph');
    td2.setAttribute('className','colalph');  // For IE
    var strHtml2 = colname;
    td2.innerHTML = strHtml2.replace(/!count!/g,count);
    
    var td3 = document.createElement("TD")
    td3.setAttribute('class','inputalph');
    td3.setAttribute('className','inputalph');  // For IE
    if (userdef!='Y') {td3.innerHTML = "<input type=text name=lb"+colname+" value='"+collabel+"' size='35' maxlength='100'>";}
    else              {td3.innerHTML = "<input type=text name=lb"+colname+" value='"+collabel+"' size='35' maxlength='100' disabled>";}
    
    var td4 = document.createElement("TD")
    td4.setAttribute('class','inputalph');
    td4.setAttribute('className','inputalph');  // For IE
    if (userdef!='Y') {td4.innerHTML = "<input type=text name=ch"+colname+" value='"+colhdg+"' size='35' maxlength='100'>";}
    else              {td4.innerHTML = "<input type=text name=ch"+colname+" value='"+colhdg+"' size='35' maxlength='100' disabled>";}

    var td8 = document.createElement("TD")
    td8.setAttribute('class','colcode');
    td8.setAttribute('className','colcode');  // For IE
    if (dftsrch == 'Y') {td8.innerHTML = "<input type=radio name=dftSrch value='"+colname+"' CHECKED>";}
    else                {td8.innerHTML = "<input type=radio name=dftSrch value='"+colname+"'>";}
    
    var td5 = document.createElement("TD")
    td5.setAttribute('class','inputnmbr');
    td5.setAttribute('className','inputnmbr');  // For IE
    if (colsize) {td5.innerHTML = "<input type=text name=ln"+colname+" value='"+colsize+"' size='3' maxlength='5'>";}
    else         {td5.innerHTML = "<input type=hidden name=ln"+colname+" value='"+colsize+"'>";}
    
    var td6 = document.createElement("TD")
    td6.setAttribute('class','inputnmbr');
    td6.setAttribute('className','inputnmbr');  // For IE
    if (coldec) {td6.innerHTML = "<input type=text name=dc"+colname+" value='"+coldec+"' size='3' maxlength='5'>";}
    else        {td6.innerHTML = "<input type=hidden name=dc"+colname+" value='"+coldec+"'>";}

    var td7 = document.createElement("TD")
    td7.setAttribute('class','colcode');
    td7.setAttribute('className','colcode');  // For IE
    if (coldec) {
      if (coltot == 'Y') {td7.innerHTML = "<input type=checkbox name=ct"+colname+" value='Y' CHECKED>";}
      else               {td7.innerHTML = "<input type=checkbox name=ct"+colname+" value='Y'>";}
    } else {td7.innerHTML = "<input type=hidden name=ct"+colname+" value=''>";}

    var td9 = document.createElement("TD")
    var s0 = (colopr=='') ? "selected" : "";
    var s1 = (colopr=='LK') ? "selected" : "";
    var s2 = (colopr=='NL') ? "selected" : "";
    var s3 = (colopr=='EQ') ? "selected" : "";
    var s4 = (colopr=='NE') ? "selected" : "";
    var s5 = (colopr=='LT') ? "selected" : "";
    var s6 = (colopr=='LE') ? "selected" : "";
    var s7 = (colopr=='GT') ? "selected" : "";
    var s8 = (colopr=='GE') ? "selected" : "";
    td9.setAttribute('class','colcode');
    td9.setAttribute('className','colcode');  // For IE
    td9.innerHTML = '<select name=op'+colname+' SIZE=1 style="width: 5em"><option '+s0+' value=""><option '+s1+' VALUE="LK" title="Like">Like<option '+s2+' VALUE="NL" title="Not Like">Not Like<option '+s3+' VALUE="EQ" title="Equal">=<option '+s4+' VALUE="NE" title="Not Equal">Not=<option '+s5+' VALUE="LT" title="Less Than"><<option '+s6+' VALUE="LE" title="Less Than or Equal"><=<option '+s7+' VALUE="GT" title="Greater Than">><option '+s8+' VALUE="GE" title="Greater Than or Equal">>=</select>';
	  
    var td10 = document.createElement("TD")
    td10.setAttribute('class','colcode');
    td10.setAttribute('className','colcode');  // For IE
    td10.innerHTML = "<input type=text name=cm"+colname+" value='"+colcmp+"' size='20' maxlength='30'>";

    var td11 = document.createElement("TD")
    td11.setAttribute('class','colcode');
    td11.setAttribute('className','colcode');  // For IE
    td11.innerHTML = "<input type=color name=co"+colname+" value='"+colclr+"' size='7' maxlength='7'>";

    // append data to row
    row.appendChild(td0);
    row.appendChild(td1);
    row.appendChild(td2);
    row.appendChild(td3);
    row.appendChild(td4);
    row.appendChild(td8);
    row.appendChild(td5);
    row.appendChild(td6);
    row.appendChild(td7);
    row.appendChild(td9);
    row.appendChild(td10);
    row.appendChild(td11);
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
  for(var i=0; i<rows.length; i++){
    trId=rows[i].getAttribute('id');
    colSeq += "|" + trId;
  }

  document.getElementById('srtCol').value = colSeq;
  return true;
} 
  
var zxcRRow,zxcRows;
var zxcMseX,zxcMseY;

function zxcDragRow(zxcobj){
 if (zxcRRow){ return; }
 chgCursor();
 var zxcrow;
 var zxctbdy=zxcobj;
 while (zxctbdy.tagName!='TBODY'){
  if (zxctbdy.tagName=='TR'){ zxcrow=zxctbdy; }
  zxctbdy=zxctbdy.parentNode;
 }
 zxcRRow=zxcrow;
 zxctbdy.removeChild(zxcrow);
 zxcRows=zxctbdy.getElementsByTagName('TR');
}

function zxcDrop(event) {
 if (!zxcRRow){ return; }
 chgCursor();
 zxcMse(event);
 for (var zxc0=zxcRows.length-1;zxc0>=0;zxc0--){
  if (zxcPos(zxcRows[zxc0])[1]<zxcMseY){
   zxcRows[zxc0].parentNode.insertBefore(zxcRRow,zxcRows[zxc0]);
   zxcRRow=null;
   return;
  }
 }
 zxcRows[0].parentNode.insertBefore(zxcRRow,zxcRows[0]);
}

function zxcPos(zxc){
 zxcObjLeft = zxc.offsetLeft;
 zxcObjTop = zxc.offsetTop;
 while(zxc.offsetParent!=null){
  zxcObjParent=zxc.offsetParent;
  zxcObjLeft+=zxcObjParent.offsetLeft;
  zxcObjTop+=zxcObjParent.offsetTop;
  zxc=zxcObjParent;
 }
 return [zxcObjLeft,zxcObjTop];
}

function zxcMse(event){
 if(!event) var event=window.event;
 if (document.all){ zxcMseX=event.clientX+zxcDocS()[0]; zxcMseY=event.clientY+zxcDocS()[1]; }
 else {zxcMseX=event.pageX; zxcMseY=event.pageY; }
}

function zxcDocS(){
 var zxcsx,zxcsy;
 if (!document.body.scrollTop){ zxcsx=document.documentElement.scrollLeft; zxcsy=document.documentElement.scrollTop; }
 else { zxcsx=document.body.scrollLeft; zxcsy=document.body.scrollTop; }
 return [zxcsx,zxcsy]
}

document.onmouseup=function(event){zxcDrop(event);saveOrderList('selTable');}

<?php
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr onKeyPress=\"checkEnterChg()\">";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "PAGEMAINTAIN";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";
$stmtSQL= "";
if ($maintenanceCode == "A") {
	require_once 'AddRecordSQL.php';
	require 'stmtSQLEnd.php';
} else {

	$stmtSQL = "Select * From SYDSGN Where PDTBID=$tblID and PDPGID=$pagID and PDTYPE='L'";
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
	if (is_resource ( $sqlResult )) {
		$row = db2_fetch_array ( $sqlResult );
		$xmlDesign = simplexml_load_string ( $row[9] );
	}
	
	$designTable  = $xmlDesign->table;
	$designPaging = $xmlDesign->paging;
	$designRow    = $xmlDesign->row;
	$designLink   = $xmlDesign->link;
	$designFilter = $xmlDesign->filter;
	$pageDesc = (string) htmlspecialchars($designTable->desc);
	$pageHeading1 = (string) htmlspecialchars($designTable->heading1);
	$pageHeading2 = (string) htmlspecialchars($designTable->heading2);
	if ($designTable->export)  {$allowExport="Y";}    else {$allowExport="N";}
	if ($designPaging->select) {$pageSelectList="Y";} else {$pageSelectList="N";}
	$allowPrinter = (! isset($designTable->printer) || $designTable->printer == 'Y') ? "Y" : "N";
    $allowCsv = (! isset($designTable->csv) || $designTable->csv == 'Y') ? "Y" : "N";
	$ovrRowsPerPage = (string) $designPaging->rows;
	$dftSrch = (string) $designTable->dft_Search;
	$dspSeq = 0;
	foreach ($designRow->col as $col) {
		$dspSeq++;
		$colName = (string) trim(strtoupper($col[0]['id']));
		$sel_column[$colName][seq]= $dspSeq;
		$sel_column[$colName][label]= (string) htmlspecialchars(trim($col[0]->label));
		$sel_column[$colName][colheading]= (string) htmlspecialchars(trim($col[0]->colheading));
		$sel_column[$colName][length]= (string) trim($col[0]->length);
		if ($col[0]->decimal) {$sel_column[$colName][decimal]= (string) trim($col[0]->decimal);}
		$sel_col[$dspSeq]= $colName;
	}
	ksort($sel_col);
}

if ($selectAll == "Y") {$sel_col=$col_sort;}

// Program Option Security
$sec_01="Y";
$sec_02="Y";
$sec_03=(($intHD === "Y" && $pagID<100) || ($pagID>99)) ? "Y" : "N";
$sec_04="N";
print "\n <a NAME=\"top\"></a> ";
require_once 'MaintainTop.php';
print "<table $contentTable>";
Format_Header_URL("Table", $tableDesc, $tableName, "{$homeURL}{$cGIPath}Table.d2w/REPORT{$altVarBase}&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']));
print "\n </table>";

print $hrTagAttr;
print "\n <table $quickLinkTable> ";
print "\n   <tr> ";
print "\n     <td class=\"quickLinkTabs\"><a href=\"#general\">General</a></td> ";
print "\n     <td class=\"quickLinkTabs\"><a href=\"#selected\">Selected Columns</a></td> ";
print "\n     <td class=\"quickLinkTabs\"><a href=\"#columns\">Columns</a></td> ";
if ($link_count>0)   {print "\n <td class=\"quickLinkTabs\"><a href=\"#links\">Links</a></td> ";}
if ($filter_count>0) {print "\n <td class=\"quickLinkTabs\"><a href=\"#checkbox\">Filter Checkbox</a></td> ";}
print "\n   </tr> ";
print "\n </table> ";

print $hrTagAttr;
require_once 'RequiredField.php';
require_once 'ErrorDisplay.php';

$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

$focusField= "pageDesc";
if ($admin == "Y") {$focusField= "role";}
if ($errFound != "" || $maintenanceCode=="A") {
	if ($errFound == "" && $maintenanceCode=="A") {
		$edtVar= "";
	} elseif ($errFound != "") {
		$focusField= "";
		$edtVar=EdtVarErr($profileHandle, $edtVar);
		$errVar=ErrVarErr($profileHandle, $errVar);
		$Err_CPRCLR=DecatErr_Field("@@rclr", "reclaimResourceLev");
	}
	$row['CPRCLR']=Decat_Field("@@rclr", $edtVar);

} elseif ($maintenanceCode=="Z") {
	$row['PDROLE']=$activeRole;
	$row['PDUSER']=$userProfile;
}

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";

print "\n <a name=\"general\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">General</legend> ";
require 'TopOfForm.php';
print "\n <table $contentTable>";

$fieldDesc=RetValue("RMROLE='{$row['PDROLE']}'", "SYROLM", "RMDESC");
$textOvr=SetTextOvr($Err_PDROLE);
print "\n <tr> ";
print "<td class=\"dsphdr\"><span $textOvr>Role</span></td>";
if ($admin == "Y") {
	print "\n <td class=\"inputalph\"><input type=\"text\" name=\"role\" value=\"{$row['PDROLE']}\" size=\"10\" maxlength=\"10\">";
	print "\n     <a href=\"{$homeURL}{$phpPath}RoleSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;roleFld=role&amp;descFld=roleDesc\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"roleDesc\">$fieldDesc</span>";
	print "\n </td>";
} else {
	$F_role = Format_Code($activeRole);
	print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"role\" value=\"{$row['PDROLE']}\">$fieldDesc &nbsp; $F_role</td>";
}
print "\n </tr> ";
DspErrMsg($Err_PDROLE);

$userName=RetValue("USUSER='{$row['PDUSER']}'", "SYUSER", "USDESC");
$textOvr=SetTextOvr($Err_PDUSER);
print "\n <tr> ";
print "<td class=\"dsphdr\"><span $textOvr>User Profile</span></td>";
if ($admin == "Y") {
	print "\n <td class=\"inputalph\"><input type=\"text\" name=\"user\" value=\"{$row['PDUSER']}\" size=\"10\" maxlength=\"10\">";
	print "\n     <a href=\"{$homeURL}{$phpPath}UserSearch.php{$genericVarBase}&amp;tag=REPORT&amp;docName=Chg&amp;userFld=user&amp;descFld=userName\" onclick=\"$searchWinVar\">$searchImage</a>";
	print "\n     <span class=\"dspdesc\" id=\"userName\">$userName</span>";
	print "\n </td>";
} else {
	$F_user = Format_Code($userProfile);
	print "\n <td class=\"inputalph\"><input type=\"hidden\" name=\"user\" value=\"{$row['PDUSER']}\">$userName &nbsp; $F_user</td>";
}
print "\n </tr> ";
DspErrMsg($Err_PDUSER);

Build_Fld_Entry("Description","pageDesc","inputalph","","PDDESC",$pageDesc,$Err_PDDESC,"50","50","Y","","");
Build_Fld_Entry("Header 1","heading1","inputalph","","PDHDG1",$pageHeading1,$Err_PDHDG1,"50","50","","","");
Build_Fld_Entry("Header 2","heading2","inputalph","","PDHDG2",$pageHeading2,$Err_PDHDG2,"50","50","","","");
Build_Fld_Entry("Override Rows","overrideRows","inputnmbr","","PDOVRR",$ovrRowsPerPage,$Err_PDOVRR,"5","5","","","");
Build_Fld_Entry("Page Selection List","pageSelect","inputalph","YORN","PDPAGS",$pageSelectList,$Err_PDPAGS,"1","1","","","");
Build_Fld_Entry("Allow XML Export","allowExport","inputalph","YORN","PDXMLX",$allowExport,$Err_PDXMLX,"1","1","","","");
Build_Fld_Entry("Allow Printer Friendly", "allowPrinter", "inputalph", "YORN", "PDPRNT", $allowPrinter, $Err_PDPRNT, "1", "1", "", "", "");
Build_Fld_Entry("Allow CSV Download", "allowCsv", "inputalph", "YORN", "PDCSV", $allowCsv, $Err_PDCSV, "1", "1", "", "", "");

print "\n </table> ";
print "\n </fieldset> ";


print "\n <a name=\"selected\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Selected Columns</legend> ";
require 'TopOfForm.php';
print "\n <div class=\"page\">To move a column, select the Click to move icon $wildSortImage and while holding down <br>the button move the mouse to the column you want to move it above and then release the button.<br>&nbsp;</div> ";

print "\n <table id=\"selTable\" $contentTable>";
print "<tr>";
print "\n <th class=\"colhdr\" colspan=\"2\">Opt</th>";
print "\n <th class=\"colhdr\">Name</th>";
print "\n <th class=\"colhdr\">Description</th>";
print "\n <th class=\"colhdr\">Column Heading</th>";
print "\n <th class=\"colhdr\">Search<br>Default</th>";
print "\n <th class=\"colhdr\">Length</th>";
print "\n <th class=\"colhdr\">Decimal</th>";
print "\n <th class=\"colhdr\">Display<br>Total</th>";
print "\n <th class=\"colhdr\">Operand</th>";
print "\n <th class=\"colhdr\">Value</th>";
print "\n <th class=\"colhdr\">Color</th>";
print "\n </tr>";
print "<tr><td><INPUT TYPE=\"hidden\" ID=\"srtCol\" NAME=\"srtCol\"></td></tr>";
print "\n </table> ";
print "\n </fieldset> ";

foreach ($sel_col as $colName) {
	$col  = $docsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
	$colID      = (string) $col[0]['id'];
	$colLabel   = (string) trim($col[0]->label);
	$colHeading = (string) trim($col[0]->colheading);
	$colSize    = (string) $col[0]->length;
	$colDecimal = (string) $col[0]->decimal;
	$relRow = null;
	$relRow = (string)  $col[0]->related_column_1;
	$selRow = null;
	if ($designRow) {$selrow = $designRow->xpath("col[@id='" . $colName . "']");}
	$selLabel = $colLabel;
	$selHeading = $colHeading;
	$selSize = $colSize;
	$colOper = (string) $selrow[0]->operand;
	$colCompare = (string) $selrow[0]->compare;
	$colColor = (string) $selrow[0]->color;
	if ($colDecimal > 1) {$selDec = $colDecimal;} else {$selDec = null;}
	$userDef = "";
	if (strpos($colLabel, "@@parm[") !== false) {
		$userDef = "Y";
		$parmName = Decat_Parm($colLabel);
		while (strpos($selLabel, "@@parm[") !== false) {
			$selLabel = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $selLabel);
		}
		while (strpos($colHeading, "@@parm[") !== false) {
			$parmName = Decat_Parm($colHeading);
			$colHeading = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colHeading);
		}
		$selHeading = $colHeading;
	} else {
		if ($sel_column[$colName][label] != "")      {$selLabel = (string) $sel_column[$colName][label];}
		if ($sel_column[$colName][colheading] != "") {$selHeading = (string) $sel_column[$colName][colheading];}
	}
	if ($sel_column[$colName][length] != "")     {$selSize = (string) $sel_column[$colName][length];}
	if ($sel_column[$colName][decimal] != "")    {$selDec = (string) $sel_column[$colName][decimal];}
	if ($selrow[0]->total) {$selTot = "Y";} else {$selTot = "";}
	$colFormat  = (string) strtoupper($col[0]->format);
	if ($colFormat == "CYMD" || $colFormat == "ISODATE") {$selSize=""; $selDec="";}
	$colRel1 = (string) trim($col[0]->related_column_1);
	if ($colRel1 == $dftSrch) {$dftSrch = $colName;}
	if ($colName == $dftSrch) {$dftSrchFlag = "Y";} else {$dftSrchFlag = "";}
	$seq=$sel_column[$colName][seq];
	print "\n <script TYPE=\"text/javascript\">";
	print "\n addRow('selTable','". trim($selLabel) . "','" . trim($selHeading) . "','" . trim($colName) . "','" . trim($seq) . "','" . trim($selSize) . "','" . trim($selDec) . "','" . trim($selTot) . "','{$homeURL}{$imagePath}smDelete.gif','{$homeURL}{$imagePath}lgSqSort.gif','" . trim($userDef) . "','" . trim($dftSrchFlag) . "','" . trim($colOper) . "','" . trim($colCompare) . "','" . trim($colColor) . "')";
	print "\n </script>";
}

print "\n <a name=\"columns\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Columns</legend> ";
print "\n <div class=\"quickLinksTop\"><a href=\"{$baseURL}&amp;tag=MAINTAIN&amp;selectAll=Y&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">$selectAllImage</a> <a href=\"#top\">$topOfFormImage</a></div> ";
print "\n <table $contentTable>";
print "<tr>";
print "\n <th class=\"colhdr\">Name</th>";
print "\n <th class=\"colhdr\">Description</th>";
print "\n </tr>";

foreach ($col_sort as $colName) {
	$col  = $docsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
	$colID      = (string) $col[0]['id'];
	$colLabel   = (string) trim($col[0]->label);
	$userDef = "";
	if (strpos($colLabel, "@@parm[") !== false) {
		$userDef = "Y";
		$parmName = Decat_Parm($colLabel);
		if ($GLOBALS["$parmName"] != "") {
			while (strpos($colLabel, "@@parm[") !== false) {
				$colLabel = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colLabel);
			}
		}
	}
	$colHeading = (string) trim($col[0]->colheading);
	while (strpos($colHeading, "@@parm[") !== false) {
		$parmName = Decat_Parm($colHeading);
		$colHeading = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colHeading);
	}
	$colSize    = (string) $col[0]->length;
	$colDecimal = (string) $col[0]->decimal;
	$colFormat  = (string) strtoupper($col[0]->format);
	if ($colFormat == "CYMD" || $colFormat == "ISODATE") {$colSize=""; $colDecimal="";}
	$relRow = null;
	$relRow = (string)  $col[0]->related_column_1;
	$selRow = null;
	$selRowID = (string) $sel_column[$colName];
	require  'SetRowClass.php';
	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colalph\">$colName</td>";
	print "\n     <td class=\"colalph\"><a href=\"javascript:addRow('selTable','" . trim($colLabel) . "','" . trim($colHeading) . "','" . trim($colName) . "','" . trim($seq) . "','" . trim($colSize) . "','" . trim($colDecimal) . "',' ','{$homeURL}{$imagePath}smDelete.gif','{$homeURL}{$imagePath}lgSqSort.gif','" . trim($userDef) . "','','','','')\" title=\"Select Column\">$colLabel</a></td>";
	print "\n </tr> ";
}
print "\n </table> ";
print "\n </fieldset> ";

if ($link_count>0) {
	print "\n <a name=\"links\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Links</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	print "<tr>";
	print "\n <th class=\"colhdr\">Sel</th>";
	print "\n <th class=\"colhdr\">Option</th>";
	print "\n <th class=\"colhdr\">Column</th>";
	print "\n <th class=\"colhdr\">Description</th>";
	print "\n </tr>";

	$saveID = null;
	foreach ($docsetLink->linkid as $col) {
		$colID   = (string) $col['id'];
		/*		$colCond = (string) trim($col[0]->condition_criteria);
		if ($colCond) {
		$colCond = urldecode($colCond);
		while (strpos($colCond, "@@parm") !== false) {
		$colCond = str_replace("\"", "'", $colCond);
		$parmName = Decat_Parm($colCond);
		if ($parmName == "") {break;}
		$colCond = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colCond);
		}
		eval("\$testCond = " . trim($colCond).";");
		if ((int)$testCond==false) {continue;}
		}*/
		$linkType   = (string) $col->type;
		if ($linkType == "C") {
			$row  = $docsetRow->xpath("col[@id='" . trim(strtoupper($col->column_name)) . "']");
			$colHeading   = (string) trim($row[0]->label);
			$linkImage   = "";
		} else {
			$linkImage   = (string) trim($col[0]->link_image);
			if (!$linkImage) {$linkImage   = (string) trim($col[0]->image);}
			$linkImage  = "{$$linkImage}";
			$colHeading   = "";
		}
		$colDesc = (string) urldecode($col->link_title);
		$linkIDSel = "N";
		if ($designLink) {
			$linkID  = $designLink->xpath("linkid[@id='" . $colID . "']");
			if ($linkID[0]) {$linkIDSel = "Y";}
		}
		$fldChecked=Field_Checked($linkIDSel,"Y");
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\"> ";
		print "\n     <td><input type=\"checkbox\" name=\"lk$colID\" value=\"Y\" $fldChecked></td>";
		print "\n     <td class=\"colcode\"><span $textOvr>$linkImage</span></td> ";
		print "\n     <td class=\"colalph\">$colHeading</td>";
		print "\n     <td class=\"colalph\">$colDesc</td>";
		print "\n </tr> ";
	}

	print "\n </table> ";
	print "\n </fieldset> ";
}

if ($filter_count>0) {
	print "\n <a name=\"checkbox\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Filter Checkbox</legend> ";
	require 'TopOfForm.php';
	print "\n <table $contentTable>";
	print "<tr>";
	print "\n <th class=\"colhdr\">Sel</th>";
	print "\n <th class=\"colhdr\">Default</th>";
	print "\n <th class=\"colhdr\">Description</th>";
	print "\n <th class=\"colhdr\">Criteria</th>";
	print "\n </tr>";

	foreach ($docsetFilter->checkbox as $col) {
		$colID      = (string) $col['id'];
		$colHeading = (string) $col->label;
		$colDesc    = (string) urldecode($col->desc);
		if ($designFilter) {$checkBoxID  = $designFilter->xpath("checkbox[@id='" . $colID . "']");}
		$checkBoxSel = "N";
		$checkBoxDft = "N";
		if ($checkBoxID[0]['id']>0) {$checkBoxSel = "Y";}
		$fldChecked=Field_Checked($checkBoxSel,"Y");
		if ($checkBoxID[0]->default) {$checkBoxDft="Y";}
		$dftChecked=Field_Checked($checkBoxDft,"Y");
		require  'SetRowClass.php';
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"cb$colID\" value=\"Y\" $fldChecked></td>";
		print "\n     <td class=\"colcode\"><input type=\"checkbox\" name=\"df$colID\" value=\"Y\" $dftChecked></td>";
		print "\n     <td class=\"colalph\"><span $textOvr>$colHeading</span></td> ";
		print "\n     <td class=\"colalph\">$colDesc</td>";
		print "\n </tr> ";
	}

	print "\n </table> ";
	print "\n </fieldset> ";
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
exit;
}

if ($tag == "Edit_Data") {
	if ($maintenanceCode == "D") {	$_POST['pageDesc']  = RetValue("PDTBID=$tblID and PDPGID=$pagID", "SYDSGN", "PDDESC");}
	if ($maintenanceCode == "D") {
		$stmtSQL = "Delete From SYDSGN Where PDTBID=$tblID and PDPGID=$pagID";
		$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "Page", $_POST['pageDesc'], "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}Page.php{$scriptVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
		exit;
	} elseif ($maintenanceCode == "Z") {
		$pagID = 0;
	}
	$edtVar= "";
	Concat_Field("@@tstp", $_POST['originalTimeStamp']);
	if (!isset($_POST['defaultPage'])) {$_POST['defaultPage']="";}
	$role = strtoupper($_POST['role']);
	$user = strtoupper($_POST['user']);
	$dftPage = "";
	$edtVar .= "}{";

	if ($errFound == "") {
		if ($maintenanceCode == "A" || $maintenanceCode == "Z") {
			if ($intHD == "Y") {$pagID=RetValue("PDTBID=$tblID and PDPGID<99", "SYDSGN", "Coalesce(Max(PDPGID),0)+1");}
			else               {$pagID=RetValue("PDTBID=$tblID and PDPGID>99", "SYDSGN", "Coalesce(Max(PDPGID),99)+1");}
		} else {
			$stmtSQL = "Delete From SYDSGN Where PDTBID=$tblID and PDPGID=$pagID";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		}
		if ($dftPage == "Y") {
			$stmtSQL = "Update SYDSGN Set PDDFLT='' Where PDTBID=$tblID and PDUSER='$user' and PDROLE='$role'";
			$status = db2_exec($i5Connect->getConnection (), $stmtSQL);
		}
		// Page Header
		$xmlTableDoc = new SimpleXMLElement("<hdList></hdList>");

		$pageDesc = $xmlTableDoc->addChild('table', '');
		$pageDesc=hd_addChild($pageDesc,'name', $_POST['tableName']);
		$pageDesc=hd_addChild($pageDesc,'desc', htmlspecialchars($_POST['pageDesc']));
		$pageDesc=hd_addChild($pageDesc,'heading1', htmlspecialchars($_POST['heading1']));
		$pageDesc=hd_addChild($pageDesc,'heading2', htmlspecialchars($_POST['heading2']));
		if (isset($_POST['allowExport'])) {$pageDesc = hd_addEmpty($pageDesc, 'export');}
		$allowPrinter = (isset($_POST['allowPrinter'])) ? "Y" : "N";
        $pageDesc = hd_addChild($pageDesc, 'printer', $allowPrinter);
		$allowCsv = (isset($_POST['allowCsv'])) ? "Y" : "N";
        $pageDesc = hd_addChild($pageDesc, 'csv', $allowCsv);

		$pageControl = $xmlTableDoc->addChild('paging', '');
		if (isset($_POST['pageSelect'])) {$pageControl=hd_addEmpty($pageControl, 'select');}
		if ($_POST['overrideRows'] > 0)  {$pageControl=hd_addChild($pageControl,'rows', $_POST['overrideRows']);}
		$dftSrch = trim($_POST['dftSrch']);
		if ($dftSrch) {
			$col  = $docsetRow->xpath("col[@id='" . trim(strtoupper($dftSrch)) . "']");
			$colRel1 = (string) trim($col[0]->related_column_1);
			if ($colRel1) {$dftSrch = $colRel1;}
		}
		$pageDesc=hd_addChild($pageDesc,'dft_Search', $dftSrch);

		$rows = $xmlTableDoc->addChild('row', '');

		$col_sort = explode('|',$_POST['srtCol'] );
		foreach ($col_sort as $colID) {
			if (trim($colID) == "" || trim($colID) == "null") {continue;}
			$lb = "lb$colID";
			$ch = "ch$colID";
			$ln = "ln$colID";
			$dc = "dc$colID";
			$op = "op$colID";
			$cm = "cm$colID";
			$co = "co$colID";
			$ct = "ct$colID";
			$col  = $docsetRow->xpath("col[@id='" . trim(strtoupper($colID)) . "']");
			$colLabel   = (string) trim($col[0]->label);
			$colHeading = (string) urlencode(trim($col[0]->colheading));
			$colSize    = (string) $col[0]->length;
			$colDecimal = (string) $col[0]->decimal;
			$columnName = $rows->addChild('col');
			$columnName->addAttribute('id', $colID);
			if (trim($_POST[$lb]) != "" && (strtoupper($_POST[$lb]) != strtoupper($colLabel))) {$columnName = hd_addChild($columnName, 'label', htmlspecialchars($_POST[$lb]));}
			if (trim($_POST[$ch]) != "" && (strtoupper($_POST[$ch]) != strtoupper($colHeading))) {$columnName = hd_addChild($columnName, 'colheading', htmlspecialchars($_POST[$ch]));}
			if (isset($_POST[$ln]) && $_POST[$ln] < $colSize) {$columnName = hd_addChild($columnName, 'length', $_POST[$ln]);}
			if (isset($_POST[$dc]) && $_POST[$dc] < $colDecimal) {$columnName = hd_addChild($columnName, 'decimal', $_POST[$dc]);}
			if (isset($_POST[$op])) {$columnName = hd_addChild($columnName, 'operand', $_POST[$op]);}
			if (isset($_POST[$cm])) {$columnName = hd_addChild($columnName, 'compare', $_POST[$cm]);}
			if (trim($_POST[$op]) != '' && trim($_POST[$cm]) != '') {$columnName = hd_addChild($columnName, 'color', $_POST[$co]);}

			if ($_POST[$ct] == 'Y') {$columnName = hd_addEmpty($columnName, 'total');}
		}

		// Load Links
		$linkID = null;
		if (isset($docsetLink->linkid)) {
			foreach ($docsetLink->linkid as $col) {
				$colID = (string) $col['id'];
				$lk = "lk$colID";
				if ($_POST[$lk] == "Y") {
					if (!$linkID) {$rows = $xmlTableDoc->addChild('link', '');}
					$linkID = $rows->addChild('linkid');
					$linkID->addAttribute('id', $colID);
				}
			}
		}

		// Load Filter Checkboxes
		$filterID = null;
		if (isset($docsetFilter->checkbox)) {
			foreach ($docsetFilter->checkbox as $col) {
				$colID = (string) $col['id'];
				$cb  = "cb$colID";
				$dft = "df$colID";
				if ($_POST[$cb] == "Y") {
					if (!$filterID) {$rows = $xmlTableDoc->addChild('filter', '');}
					$filterID = $rows->addChild('checkbox');
					$filterID->addAttribute('id', $colID);
					if ($_POST[$dft] == "Y") {$filterID = hd_addEmpty($filterID, 'default');}
				}
			}
		}

		$xmlStr = $xmlTableDoc->asXML();
		$domTableDoc = new DOMDocument("1.0");
		$domTableDoc->loadXML($xmlStr);
		$xmlStr = str_replace('\'', '"', $xmlStr);
		$stmtSQL = "Insert into SYDSGN (PDTBID, PDPGID, PDTYPE, PDDESC, PDROLE, PDUSER, PDDFLT, PDCRTB, PDXML)
                                VALUES (?,?,?,?,?,?,?,?,?)";
		$sqlResult = db2_prepare($i5Connect->getConnection (), $stmtSQL);
		if ($sqlResult) {
			$var = array($tblID, $pagID, "L", $_POST['pageDesc'], $role, $user, $dftPage, $userProfile, $xmlStr);
			$ret = db2_execute($sqlResult, $var);
		}
		if ($maintenanceCode == "Z") {$maintenanceCode = "A";}
		$confMessage=Format_ConfMsg_Desc($maintenanceCode, "Page", $_POST['pageDesc'], "", "", "", "");
		print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}Page.php{$scriptVarBase}&amp;tag=REPORT&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
	} else {
		EdtVarErr($profileHandle, $edtVar);
		ErrVarErr($profileHandle, $errVar);
		print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;tag=MAINTAIN&amp;wrnVar=" . urlencode(trim($wrnVar)) . "&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "&amp;errFound=" . urlencode(trim($errFound)) . "&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " \"> ";
	}
}

function hd_addChild($obj, $element, $val){
	if ($val !=''){
		$obj->addChild($element, trim($val));
	}
	return $obj;
}
function hd_addEmpty($obj, $element){
	$obj->addChild($element, null);
	return $obj;
}
function Decat_Parm ($edtVar) {
	$fieldValue = "";
	$ssStart    = strpos($edtVar, "@@parm") + 7;
	$ssLength   = strpos($edtVar, "]", $ssStart) - $ssStart;
	if ($ssStart !== false) {
		$fieldValue = substr($edtVar, $ssStart, $ssLength);
	}
	return $fieldValue;
}
?>