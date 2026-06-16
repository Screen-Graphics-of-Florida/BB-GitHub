<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$psjob = $_GET ['psjob'];
$pstype = $_GET ['pstype'];
$psreqn = $_GET ['psreqn'];
$psitem = $_GET ['psitem'];
$psimds = $_GET ['psimds'];
$pswhs  = $_GET ['pswhs'];
$psqtor = $_GET ['psqtor'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "PO Blanket Line Maintenance";
$scriptName = "CreatePOBlanketMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;psjob=" . urlencode(trim($psjob)) . "&amp;pstype=" . urlencode(trim($pstype)) . "&amp;psreqn=" . urlencode(trim($psreqn)) . "&amp;psitem=" . urlencode(trim($psitem)) . "&amp;psimds=" . urlencode(trim($psimds)) . "&amp;pswhs=" . urlencode(trim($pswhs)) . "&amp;psqtor=" . urlencode(trim($psqtor));
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $prtMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;

if ($tag == "Edit_Data") {
    $errorMsg = null;
    $errorDates = null;
    $errorDatesMsg = null;
    $deleteAll = (isset($_GET['deleteAll']) && trim($_GET['deleteAll'] == 'Y')) ? true : null;

    if (is_null($deleteAll)) {
        $blkQty = 0;
        for ($i = 1; $i <= 99; $i++) {
            if ($i < 10) {
                $i = "0" . $i;
            }
            $rqd = "rq" . $i;
            $qty = "qt" . $i;
            $err = "er" . $i;
            $_POST[$err] = '';
            if (isset($_POST[$rqd])) {
                $blkQty += floatval($_POST[$qty]);
                $isValid = validWorkDay($_POST[$rqd]);
                if ($isValid['error'] == 'Y') {
                    $_POST[$err] = $isValid['error'];
                    $errorDates = $isValid['error'];
                }
            }
        }
        if (!is_null($errorDates)) {
            $errorDatesMsg = 'Invalid Working Days Per Schedule';
            $errFound = 'Y';

        }
        $testQty = str_replace( ',', '', $psqtor );
        if (floatval($blkQty) > floatval($testQty)) {
            $F_blkQty = Format_Nbr($blkQty, $qtyNbrDec, $qtyEditCode, '', '', '');
            $errorMsg = 'Total blanket quantity ' . $F_blkQty . ' is greater than the Order Quantity ' . $psqtor;
            $errFound = 'Y';
        }
    }

    if (is_null($errorMsg) && is_null($errorDates)) {
        $stmtSQL = " Delete From PODTBW Where DBOCTL={$psjob} and DBTYPE='{$pstype}' and DBREQN='{$psreqn}' and DBITEM='{$psitem}' ";
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
        if (is_null($deleteAll)) {
            for ($i = 1; $i <= 99; $i++) {
                if ($i < 10) {
                    $i = "0" . $i;
                }
                $rqd = "rq" . $i;
                $qty = "qt" . $i;
                if (isset($_POST[$rqd]) && floatval($_POST[$qty]) > 0) {
                    $dateCYMD = ($_POST[$rqd] >0) ? DateToCYMD($_POST[$rqd]) : 0;
                    $stmtSQL = " Insert Into PODTBW (DBOCTL,DBTYPE,DBREQN,DBITEM,DBRELN,DBRQDT,DBQTY) ";
                    $stmtSQL .= " Values ($psjob,'$pstype','$psreqn','$psitem',$i,$dateCYMD,$_POST[$qty]) ";
                    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
                }
            }
        }
        print "\n <script TYPE=\"text/javascript\">";
        print "\n opener.location=opener.location";
        print "\n window.close()";
        print "\n </script> \n";
        exit();
    }
}

require_once($docType);
print "\n <html> <head>";
require_once($headInclude);
$formName = "Chg";
print "\n <script TYPE=\"text/javascript\">";
require_once 'CalendarInclude.php';
require_once 'CheckEnterChg.php';
require_once 'DateEdit.php';
require_once 'NumEdit.php';

print "\n function validate(chgForm) {";
print "\n if (editNum(document.Chg.qty, 9, 4) && ";
print "\n     editdate(document.Chg.reqdate)) ";
print "\n return true;";
print "\n }";

print "\n  function confirmDeleteAll() {return confirm(\"Confirm delete of all blanket lines\")} ";
?>
function checkAdd(delimg,ndec) {
document.onkeyup = function(e){
if (!e) {
var k = (e) ? e.which:event.keyCode;
var t = (e) ? e.which:event.srcElement.type;
} else {
var node = e.target;
while(node.nodeType != node.ELEMENT_NODE)
node = node.parentNode;
var t = node.type;
var k = e.which;
}
if (k == 13 && t != 'textarea')
if (validate(document.Chg)) {
addIt(document.Chg,delimg,ndec);
}
}
}

function addIt(chgForm,delimg,ndec) {
var reqdate = document.getElementById('reqdate').value;
var qty = document.getElementById('qty').value;
if (validate(document.Chg)) {
addRow('dataTable',reqdate,qty,delimg,ndec,'');
}
}

function deleteElement(id){
var el = document.getElementById(id);
el.parentNode.removeChild(el);
return false;
}

function isEven(value) {
if (value%2 == 0)
return true;
else
return false;
}

var count = "1";
function addRow(tableID,reqdate,qty,delimg,ndec,err) {
ndec = parseInt(ndec);
if (qty == "") {qty = 0;}
var tbody = document.getElementById(tableID).getElementsByTagName("TBODY")[0];
var table = document.getElementById(tableID);
var s = document.getElementById('bQty').innerHTML;
s = s.replace(",", "");
var total=parseFloat(s) + parseFloat(qty);
var ftotal = parseFloat(total).toFixed(ndec)
ftotal = numberWithCommas(ftotal);
document.getElementById('bQty').innerHTML = ftotal;

var o = document.getElementById('oQty').innerHTML;
o = o.replace("&nbsp;", "");
o = o.replace(",", "");
var b = document.getElementById('bQty').innerHTML;
b = b.replace(",", "");
var v = parseFloat(o) - parseFloat(b);
var fV = parseFloat(v).toFixed(ndec)
fV = numberWithCommas(fV);
document.getElementById('vQty').innerHTML = fV;

var rows=document.getElementById(tableID).getElementsByTagName('tr');
var rowCount=rows.length;
rowCount = parseInt(rowCount) - 1;

// Check for duplicate row id due to deletes
for (i = 0; i < 90; i++) {
    var tableName = 'row' + rowCount;
    var ele = document.getElementById(tableName);
    if(ele !== null){ rowCount++;} else {break;}
}

if (rowCount > 98) {alert ('You have reached the maximum of 99 entries'); return;}
var row = document.createElement("TR");
row.setAttribute('id',tableName);
if (isEven(rowCount)) {
row.setAttribute('class','evenrow');
row.setAttribute('className','evenrow');  // For IE
} else {
row.setAttribute('class','oddrow');
row.setAttribute('className','oddrow');  // For IE
}

str = reqdate.replace(/(\S{2})/g,"$1-");
str = str.replace(/-$/,"");
var td1 = document.createElement("TD")
var colClass = 'colalph';
if (err == 'Y') {
  colClass = 'error';
}
td1.setAttribute('class',colClass);
td1.setAttribute('className',colClass);  // For IE
if (rowCount < 10) {td1.innerHTML = "<input type=hidden id=rq0"+rowCount+" name=rq0"+rowCount+" value="+reqdate+" size=\"6\">"+str+"";}
else               {td1.innerHTML = "<input type=hidden id=rq"+rowCount+" name=rq"+rowCount+" value="+reqdate+" size=\"6\">"+str+"";}

fmtQty = parseFloat(qty).toFixed(ndec);
fmtQty = numberWithCommas(fmtQty);
var td2 = document.createElement("TD")
td2.setAttribute('class','colnmbr');
td2.setAttribute('className','colnmbr');  // For IE
if (rowCount < 10) {td2.innerHTML = "<input type=hidden id=qt0"+rowCount+" name=qt0"+rowCount+" value="+qty+" size=\"15\">"+fmtQty+"";}
else               {td2.innerHTML = "<input type=hidden id=qt"+rowCount+" name=qt"+rowCount+" value="+qty+" size=\"15\">"+fmtQty+"";}

var td3 = document.createElement("TD")
td3.setAttribute('class','colicon');
td3.setAttribute('className','colicon');  // For IE
var img = document.createElement('IMG');
img.setAttribute('src', delimg);
img.setAttribute('title', 'Remove row');
img.onclick = function(){delRow(row,tableID,qty,ndec);}
td3.appendChild(img);

// append data to row
row.appendChild(td1);
row.appendChild(td2);
row.appendChild(td3);
tbody.appendChild(row);

document.Chg.reqdate.value="";
document.Chg.qty.value="";
setTimeout(function () { document.Chg.reqdate.focus() }, 50);
}

function delRow(row,tableID,qty,ndec){
  ndec = parseInt(ndec);
  row.parentNode.removeChild(row);
  var tbody = document.getElementById(tableID).getElementsByTagName("TBODY")[0];
  var table = document.getElementById(tableID);
  var s = document.getElementById('bQty').innerHTML;
  s = s.replace(",", "");
  var total=parseFloat(s) - parseFloat(qty);
  var ftotal = parseFloat(total).toFixed(ndec)
  ftotal = numberWithCommas(ftotal);
  document.getElementById('bQty').innerHTML = ftotal;

  var o = document.getElementById('oQty').innerHTML;
  o = o.replace("&nbsp;", "");
  o = o.replace(",", "");
  var b = document.getElementById('bQty').innerHTML;
  b = b.replace(",", "");
  var v = parseFloat(o) - parseFloat(b);
  var fV = parseFloat(v).toFixed(ndec)
  fV = numberWithCommas(fV);
  document.getElementById('vQty').innerHTML = fV;
}

function numberWithCommas(x) {
var parts = x.toString().split(".");
parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
return parts.join(".");
}
</script>

<?php
require_once($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr onKeyPress=\"checkAdd('{$homeURL}{$imagePath}smDelete.gif','$qtyNbrDec')\">";
require_once($searchBanner);
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td>";
print "\n     <td class=\"toolbar\">";
print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
print "\n <a href=\"javascript:window.close()\">$cancelImageLrg</a>";
print "\n <a  onClick=\"return confirmDeleteAll()\" href=\"{$homeURL}{$phpPath}CreatePOBlanketMaintain.php{$scriptVarBase}&amp;tag=Edit_Data&amp;deleteAll=Y\">$deleteImageMed</a>";
print "\n </td></tr></table>";

if (!is_null($errorMsg)) {print "\n <span class=\"error\" $textOvr>{$errorMsg}</span>";}

print "$hrTagAttr";

print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";

print "<table $contentTable>";
print "\n <tr> ";
print "\n <th class=\"colhdr\">Type</th>";
print "\n <th class=\"colhdr\">Item Number</th>";
print "\n <th class=\"colhdr\">Description</th>";
print "\n <th class=\"colhdr\">Order<br>Quantity</th>";
print "\n <th class=\"colhdr\">Blanket<br>Quantity</th>";
print "\n <th class=\"colhdr\">Variance<br>Quantity</th>";
print "\n </tr> ";

$F_psqtor = Format_Nbr($psqtor, $qtyNbrDec, $qtyEditCode, '', '', '');
print "\n  <tr><td class=\"colcode\">$pstype</td>";
print "\n      <td class=\"colalph\">$psitem</td>";
print "\n      <td class=\"colalph\">$psimds</td>";
print "\n      <td class=\"colnmbr\" id=\"oQty\" value=\"$psqtor\">$F_psqtor</td>";
print "\n      <td class=\"colnmbr\" id=\"bQty\" value=\"\">.0000</td> ";
print "\n      <td class=\"colnmbr\" id=\"vQty\" value=\"$psqtor\">$F_psqtor</td> ";
print "\n </tr>";
print "\n </table>";

if (!is_null($errorDatesMsg)) {print "\n <span class=\"error\" $textOvr>{$errorDatesMsg}</span><br>";}

print "<table $contentTable id=\"dataTable\">";
print "\n <tr> ";
print "\n <th class=\"colhdr\">Required Date</th>";
print "\n <th class=\"colhdr\">Quantity</th>";
print "\n </tr> ";

print "\n  <tr><td class=\"inputnmbr\"><input tabindex=1 type=\"text\" name=\"reqdate\" id=\"reqdate\" value=\"\" size=\"6\" maxlength=\"6\">";
print "\n                         <a href=\"javascript:calWindow('reqdate');\">$calendarImage</a></td> ";
print "\n      <td class=\"inputnmbr\"><input tabindex=2 type=\"text\" name=\"qty\" id=\"qty\" value=\"\" size=\"10\" maxlength=\"15\"></td>";
print "\n <td><a href=\"javascript:addIt(document.Chg,'{$homeURL}{$imagePath}smDelete.gif','$qtyNbrDec')\">&nbsp; $acceptImageMed</a></td>";
print "\n </tr>";

$startRow = 1;
if ($errFound == "") {
    $stmtSQL = "Select * From PODTBW Where DBOCTL={$psjob} and DBTYPE='{$pstype}' and DBREQN='{$psreqn}' and DBITEM='{$psitem}' Order By DBRQDT";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, ['cursor' => DB2_SCROLLABLE]);
    while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
        $dateMDY = ($row['DBRQDT']>0) ? DateInputFromCYMD($row['DBRQDT']) : '';
        print "\n <script TYPE=\"text/javascript\">";
        print "\n addRow('dataTable','$dateMDY','$row[DBQTY]','{$homeURL}{$imagePath}smDelete.gif','$qtyNbrDec','')";
        print "\n </script>";
        $startRow++;
    }
} else {
    for ($i = 1; $i <= 99; $i++) {
        if ($i < 10) {$i = "0" . $i;}
        $rqd = "rq" . $i;
        $qty = "qt" . $i;
        $err = "er" . $i;
        if (isset($_POST[$rqd])) {
            $reqDate = ($_POST[$rqd]>0) ? $_POST[$rqd] : '';
            print "\n <script TYPE=\"text/javascript\">";
            print "\n addRow('dataTable','$reqDate','$_POST[$qty]','{$homeURL}{$imagePath}smDelete.gif','$qtyNbrDec','$_POST[$err]')";
            print "\n </script>";
        }
    }
}

print "\n </table>";
print "\n <script TYPE=\"text/javascript\">document.Chg.reqdate.focus();</script>";
print "\n </form>";

print "$hrTagAttr";
require_once 'Copyright.php';
print "\n </td></tr>";
print "\n </table>";
print "\n </body> \n </html>";


function validWorkDay ($reqDate) {
    global $i5Connect, $psitem, $pswhs;
    $whs = str_pad($pswhs, 3, '0', STR_PAD_LEFT);
    if (!$i5Connect) die("<br>Validate Working Day Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

    $pgmCall = array(
        array("Name"=>"item"		, "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR		, "Length"=>"15"),
        array("Name"=>"whs"			, "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR	    , "Length"=>"3"),
        array("Name"=>"reqDate"     , "IO"=>I5_IN   , "Type"=>I5_TYPE_CHAR		, "Length"=>"6"),
        array("Name"=>"error"		, "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR	    , "Length"=>"1")
    );

    $pgm = i5_program_prepare("HHDSCD_W", $pgmCall);
    if (!$pgm) {die("<br>Validate Working Day Program (HHDSCD_W) Prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

    $parmIn = array(
        "item"	=>$psitem,
        "whs"	=>$whs,
        "reqDate"	=>$reqDate,
        "error" => ''
    );

    $parmOut = array(
        "item"		=>"item",
        "whs"	    =>"whs",
        "reqDate"	=>"reqDate",
        "error" =>"error"
    );

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {die("<br>Validate Working Day Program (HHDSCD_W) call errno=".i5_errno()." msg=".i5_errormsg());}

    $returnValue['error']	= $error;
    return $returnValue;
}

?>
