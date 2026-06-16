<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$purchaseOrder = (isset($_GET['purchaseOrderNumber'])) ? $_GET['purchaseOrderNumber'] : 0;
$lineNumber = (isset($_GET['lineNumber'])) ? $_GET['lineNumber'] : 0;

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title = "PO Blanket Line Maintenance";
$scriptName = "POBlanketMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrder)) . "&amp;lineNumber=" . urlencode(trim($lineNumber));
$nextPrevVar = "{$scriptVarBase}";
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$filterURL = "{$scriptName}{$scriptVarBase}&amp;tag=REPORT";
$dspMaxRows = $prtMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;

$stmtSQL = " Select * From POPOMD Where PDPO={$purchaseOrder} and PDPOL#={$lineNumber} Fetch First Row Only";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$dtlRow = db2_fetch_assoc($sqlResult);
$item = $dtlRow[PDITEM];
$whs = $dtlRow[PDOVWH];

if ($tag == "Edit_Data") {
    $errorMsg = null;
    $errorDates = null;
    $errorDatesMsg = null;
    $busy = RetValue("POPO=$purchaseOrder", "POPOMS", "POBUSY");
    if ($busy == 'B') {
        $errorMsg = 'Order is being maintained by another user.';
        $errFound = 'Y';
    }
    $blkQty = 0;
    for ($i = 1; $i <= 99; $i++) {
        if ($i < 10) {
            $i = "0" . $i;
        }
        $rqd = "rq" . $i;
        $qty = "qt" . $i;
        $rcv = "qr" . $i;
        $err = "er" . $i;
        $_POST[$err] = '';
        if ($_POST[$qty] > 0) {
            $blkQty += floatval($_POST[$qty]);
            if ($_POST[$rqd] >0) {
                $isValid = validWorkDay($_POST[$rqd]);
                if ($isValid['error'] == 'Y') {
                    $_POST[$err] = $isValid['error'];
                    $errorDates = $isValid['error'];
                    $errFound = 'Y';
                }
            }
        }
        if ($_POST[$err] == '' && $_POST[$rcv] > 0 && $_POST[$qty] < $_POST[$rcv]) {
            $_POST[$err] = 'Q';
        }
    }
    $testQty = str_replace( ',', '', $dtlRow[PDQTOR] );
    if ($blkQty > 0 && floatval($blkQty) != floatval($testQty)) {
        $errorMsg = 'Total blanket quantity must equal Order Quantity';
        $errFound = 'Y';
    }

    if (is_null($errorMsg) && is_null($errorDates)) {
        $relLns = null;
        for ($i = 1; $i <= 99; $i++) {
            $i = ($i < 10) ? "0" . $i : $i;
            $rel = "rl" . $i;
            if ($_POST[$rel] > 0) {
                $relLns = (is_null($relLns)) ? $_POST[$rel] : $relLns . ',' . $_POST[$rel];
            }
        }

        $inRel = (!is_null($relLns)) ? "and PDPORL not in ({$relLns})" : '';
        $stmtSQL = " Delete From POPOMD Where PDPO={$purchaseOrder} and PDPOL#={$lineNumber} and 
                         PDPORL > 0 {$inRel} ";
        $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);

        $lineType = ($blkQty == 0) ? '' : 'B';
        $stmtSQL = " Update POPOMD Set PDPOLT='{$lineType}'
                     Where PDPO={$purchaseOrder} and PDPOL#={$lineNumber} and PDPORL = 0 ";
        $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);

        $nextRel = RetValue("PDPO=$purchaseOrder and PDPOL#={$lineNumber} and PDPORL>0", "POPOMD", "max(PDPORL)");
        for ($i = 1; $i <= 99; $i++) {
            $i = ($i < 10) ? "0" . $i : $i;
            $rqd = "rq" . $i;
            $qty = "qt" . $i;
            $sts = "st" . $i;
            $rel = "rl" . $i;

            if ($_POST[$sts] == 'C') continue;  // Bypass Closed Releases

            if (floatval($_POST[$qty]) > 0) {
                $dateCYMD = ($_POST[$rqd] > 0) ? DateToCYMD($_POST[$rqd]) : 0;
                if ($_POST[$rel] > 0) {
                    $stmtSQL = " Update POPOMD Set PDRQDT={$dateCYMD},PDQTOR={$_POST[$qty]} ";
                    $stmtSQL .= " Where PDPO={$purchaseOrder} and PDPOL#={$lineNumber} and PDPORL={$_POST[$rel]} ";
                    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
                } else {
                    $nextRel++;
                    $stmtSQL = " Insert Into POPOMD (PDSTAT,PDPO,PDPOL#,PDPORL,PDPOEC,PDITEM,PDOVWH,PDPLT,PDIMDS,PDPCLS,
                     PDDSCC,PDSTCS,PDDSCF,PDITCS,PDQTOR,PDQRST,PDQRRT,PDQRVT,PDQRFT,PDQHRT,PDBOAL,PDDLRC,PDRQDT,PDVDSN,
                     PDSUOM,PDBUOM,PDPCPB,PDPOLT,PDPRPO,PDPRRC,PDTXCD,PDOEPO,PDRORD,PDECN,PDUCC,PDOPDT,PDCPDT,PDLADT,
                     PDLATY,PDOOQT,PDNRCP)
                     Values ('$dtlRow[PDSTAT]',$dtlRow[PDPO],$lineNumber,$nextRel,'$dtlRow[PDPOEC]','$dtlRow[PDITEM]',
                     $dtlRow[PDOVWH],$dtlRow[PDPLT],'$dtlRow[PDIMDS]','$dtlRow[PDPCLS]',$dtlRow[PDDSCC],$dtlRow[PDSTCS],
                     '$dtlRow[PDDSCF]',$dtlRow[PDITCS],$_POST[$qty],0,0,0,0,0,'$dtlRow[PDBOAL]',0,$dateCYMD,'$dtlRow[PDVDSN]',
                     '$dtlRow[PDSUOM]','$dtlRow[PDBUOM]',$dtlRow[PDPCPB],'','$dtlRow[PDPRPO]','$dtlRow[PDPRRC]',
                     '$dtlRow[PDTXCD]','$dtlRow[PDOEPO]','$dtlRow[PDRORD]','$dtlRow[PDECN]','$dtlRow[PDUCC]',0,0,0,'',0,0)";
                    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
                }
            }
        }

        $edtVar = "";
        Concat_Field("@@popo", $purchaseOrder);
        Concat_Field("@@line", $lineNumber);
        Concat_Field("@@clcn", "");
        $edtVar .= "}{";
        UpdBlanketChg($edtVar);

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
    var rcv = 0;
    if (validate(document.Chg)) {
        addRow('dataTable',reqdate,qty,rcv,'','0',delimg,ndec,'');
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
function addRow(tableID,reqdate,qty,rcv,stat,rel,delimg,ndec,err) {
ndec = parseInt(ndec);
if (qty == "") {qty = 0;}
if (rcv == "") {rcv = 0;}
if (rel == "") {rel = 0;}
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
//if (isEven(rowCount)) {
//row.setAttribute('class','evenrow');
//row.setAttribute('className','evenrow');  // For IE
//} else {
row.setAttribute('class','oddrow');
row.setAttribute('className','oddrow');  // For IE
//}

str = reqdate.replace(/(\S{2})/g,"$1-");
str = str.replace(/-$/,"");
var td1 = document.createElement("TD")
var colClass = 'inputnmbr';
if (reqdate == '') reqdate = 0;
td1.setAttribute('class',colClass);
td1.setAttribute('className',colClass);  // For IE
if (stat == 'C') {
    if (rowCount < 10) {td1.innerHTML = "<input type=hidden id=rq0"+rowCount+" name=rq0"+rowCount+" value="+reqdate+" size=\"6\">"+reqdate+"";}
    else               {td1.innerHTML = "<input type=hidden id=rq"+rowCount+" name=rq"+rowCount+" value="+reqdate+" size=\"6\">"+reqdate+"";}
} else {
    if (rowCount < 10) {td1.innerHTML = "<input type=text id=rq0"+rowCount+" name=rq0"+rowCount+" value="+reqdate+" size=\"6\">";}
    else               {td1.innerHTML = "<input type=text id=rq"+rowCount+" name=rq"+rowCount+" value="+reqdate+" size=\"6\">";}
}
fmtQty = parseFloat(qty).toFixed(ndec);
fmtQty = numberWithCommas(fmtQty);
var td2 = document.createElement("TD")
td2.setAttribute('class','inputnmbr');
td2.setAttribute('className','inputnmbr');  // For IE
if (stat == 'C') {
    if (rowCount < 10) {td2.innerHTML = "<input type=hidden id=qt0"+rowCount+" name=qt0"+rowCount+" value="+qty+" size=\"12\">Closed";}
    else               {td2.innerHTML = "<input type=hidden id=qt"+rowCount+" name=qt"+rowCount+" value="+qty+" size=\"12\">Closed";}
} else {
    if (rowCount < 10) {td2.innerHTML = "<input type=text id=qt0"+rowCount+" name=qt0"+rowCount+" value="+qty+" size=\"12\">";}
    else               {td2.innerHTML = "<input type=text id=qt"+rowCount+" name=qt"+rowCount+" value="+qty+" size=\"12\">";}
}
if (rcv == 0) {
    fmtRcv = '';
} else {
    fmtRcv = parseFloat(rcv).toFixed(ndec);
    fmtRcv = numberWithCommas(fmtRcv);
}
var td3 = document.createElement("TD")
td3.setAttribute('class','colnmbr');
td3.setAttribute('className','colnmbr');  // For IE
if (rowCount < 10) {td3.innerHTML = "<input type=hidden id=qr0"+rowCount+" name=qr0"+rowCount+" value="+rcv+" size=\"12\">"+fmtRcv+"";}
else               {td3.innerHTML = "<input type=hidden id=qr"+rowCount+" name=qr"+rowCount+" value="+rcv+" size=\"12\">"+fmtRcv+"";}

var td4 = document.createElement("TD")
td4.setAttribute('class','colicon');
td4.setAttribute('className','colicon');  // For IE
if (rcv == 0 && err != 'Q') {
    var img = document.createElement('IMG');
    img.setAttribute('src', delimg);
    img.setAttribute('title', 'Remove row');
    img.onclick = function(){delRow(row,tableID,qty,ndec);}
    td4.appendChild(img);
}

if (err == 'Y' || err == 'Q') {
    var td5 = document.createElement("TD")
    td5.setAttribute('class','error');
    td5.setAttribute('className','error');  // For IE
    if (err == 'Y') {
       var errMsg = 'Invalid Working Day Per Schedule';
    } else {
        var errMsg = 'Blanket Quantity cannot be less than Received Quantity';
    }
    td5.innerHTML = "<input type=hidden >"+errMsg+"";
}

var td6 = document.createElement("TD")
td6.setAttribute('class','colalph');
td6.setAttribute('className','colalph');  // For IE
if (rowCount < 10) {td6.innerHTML = "<input type=hidden id=st0"+rowCount+" name=st0"+rowCount+" value="+stat+" size=\"1\">";}
else               {td6.innerHTML = "<input type=hidden id=st"+rowCount+" name=st"+rowCount+" value="+stat+" size=\"1\">";}

var td7 = document.createElement("TD")
td7.setAttribute('class','colalph');
td7.setAttribute('className','colalph');  // For IE
if (rowCount < 10) {td7.innerHTML = "<input type=hidden id=rl0"+rowCount+" name=rl0"+rowCount+" value="+rel+" size=\"3\">";}
else               {td7.innerHTML = "<input type=hidden id=rl"+rowCount+" name=rl"+rowCount+" value="+rel+" size=\"3\">";}

// append data to row
row.appendChild(td1);
row.appendChild(td2);
row.appendChild(td3);
row.appendChild(td4);

if (err == 'Y' || err == 'Q') {
    row.appendChild(td5);
}
row.appendChild(td6);
row.appendChild(td7);
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
print "\n <body $bodyTagAttr onKeyPress=\"checkAdd('{$homeURL}{$imagePath}smDelete.gif', '$qtyNbrDec')\">";
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
print "\n </td></tr></table>";
print "$hrTagAttr";

print "\n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data\">";

print "<table $contentTable>";
print "\n <tr> ";
print "\n <th class=\"colhdr\">Purchase<br>Order</th>";
print "\n <th class=\"colhdr\">Line</th>";
print "\n <th class=\"colhdr\">Item Number</th>";
print "\n <th class=\"colhdr\">Description</th>";
print "\n <th class=\"colhdr\">Order<br>Quantity</th>";
print "\n <th class=\"colhdr\">Blanket<br>Quantity</th>";
print "\n <th class=\"colhdr\">Variance<br>Quantity</th>";
print "\n </tr> ";

$F_PDQTOR = Format_Nbr($dtlRow[PDQTOR], $qtyNbrDec, $qtyEditCode, '', '', '');
print "\n  <tr><td class=\"colnmbr\">{$purchaseOrder}</td>";
print "\n      <td class=\"colnmbr\">{$lineNumber}</td>";
print "\n      <td class=\"colalph\">$dtlRow[PDITEM]</td>";
print "\n      <td class=\"colalph\">$dtlRow[PDIMDS]</td>";
print "\n      <td class=\"colnmbr\" id=\"oQty\">$F_PDQTOR</td>";
print "\n      <td class=\"colnmbr\" id=\"bQty\" value=\"\">.0000</td> ";
print "\n      <td class=\"colnmbr\" id=\"vQty\" value=\"$psqtor\">$F_psqtor</td> ";
if (!is_null($errorMsg)) {
    print "\n <td class=\"error\">{$errorMsg}</td>";
}
print "\n </tr>";
print "\n <tr><td>&nbsp;</td></tr> ";
print "\n </table>";

print "<table $contentTable id=\"dataTable\">";
print "\n <tr> ";
print "\n <th class=\"colhdr\">Required<br>Date</th>";
print "\n <th class=\"colhdr\">Blanket<br>Quantity</th>";
print "\n <th class=\"colhdr\">Received<br>Quantity</th>";
print "\n <th class=\"colhdr\">Opt</th>";
print "\n </tr> ";

print "\n  <tr><td class=\"inputnmbr\"><input tabindex=1 type=\"text\" name=\"reqdate\" id=\"reqdate\" value=\"\" size=\"6\" maxlength=\"6\">";
print "\n                         <a href=\"javascript:calWindow('reqdate');\">$calendarImage</a></td> ";
print "\n      <td class=\"inputnmbr\"><input tabindex=2 type=\"text\" name=\"qty\" id=\"qty\" value=\"\" size=\"12\" maxlength=\"12\"></td>";
print "\n <td>&nbsp;</td><td class=\"colicon\"><a href=\"javascript:addIt(document.Chg,'{$homeURL}{$imagePath}smDelete.gif','$qtyNbrDec')\">&nbsp; $addMoreImage</a></td>";
print "\n </tr>";

$startRow = 1;
if ($errFound == "") {
    $stmtSQL = "Select PDPORL,PDSTAT,PDRQDT,PDQTOR,PDQRST+PDQRRT+PDQRFT as QTYRCV
                From POPOMD Where PDPO=$purchaseOrder and PDPOL#={$lineNumber} and PDPORL>0
                Order By PDPORL";
    $startRow = 1;
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));
    while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
        $dateMDY = ($row[PDRQDT] > 0) ? DateInputFromCYMD($row[PDRQDT]) : '';
        print "\n <script TYPE=\"text/javascript\">";
        print "\n addRow('dataTable','$dateMDY','$row[PDQTOR]','$row[QTYRCV]','$row[PDSTAT]','$row[PDPORL]','{$homeURL}{$imagePath}smDelete.gif','$qtyNbrDec','')";
        print "\n </script>";
        $startRow++;
    }
} else {
    for ($i = 1; $i <= 99; $i++) {
        if ($i < 10) {
            $i = "0" . $i;
        }
        $rqd = "rq" . $i;
        $qty = "qt" . $i;
        $rcv = "qr" . $i;
        $sts = "st" . $i;
        $rel = "rl" . $i;
        $err = "er" . $i;
        if (isset($_POST[$rqd])) {
            $reqDate = ($_POST[$rqd] > 0) ? $_POST[$rqd] : '';
            print "\n <script TYPE=\"text/javascript\">";
            print "\n addRow('dataTable','$reqDate','$_POST[$qty]','$_POST[$rcv]','$_POST[$sts]','$_POST[$rel]','{$homeURL}{$imagePath}smDelete.gif','$qtyNbrDec','$_POST[$err]')";
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


function validWorkDay($reqDate)
{
    global $i5Connect, $item, $whs;
    $whs = str_pad($whs, 3, '0', STR_PAD_LEFT);
    if (!$i5Connect) die("<br>Validate Working Day Connection Failed. Error number =" . i5_errno() . " msg=" . i5_errormsg());

    $pgmCall = array(
        array("Name" => "item", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "15"),
        array("Name" => "whs", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "3"),
        array("Name" => "reqDate", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "6"),
        array("Name" => "error", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1")
    );

    $pgm = i5_program_prepare("HHDSCD_W", $pgmCall);
    if (!$pgm) {
        die("<br>Validate Working Day Program (HHDSCD_W) Prepare error. Error Number=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = array(
        "item" => $item,
        "whs" => $whs,
        "reqDate" => $reqDate,
        "error" => ''
    );

    $parmOut = array(
        "item" => "item",
        "whs" => "whs",
        "reqDate" => "reqDate",
        "error" => "error"
    );

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br>Validate Working Day Program (HHDSCD_W) call errno=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $returnValue['error'] = $error;
    return $returnValue;
}

function UpdBlanketChg ($edtVar){
    global $i5Connect, $userProfile;
    $maintenanceCode = "B";
    if (!$i5Connect) die("<br>UpdatePO Connection Failed. Error number =".i5_errno()." msg=".i5_errormsg());

    $pgmCall = [
        ["Name"=>"userProfile",     "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"10"],
        ["Name"=>"maintenanceCode", "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"1"],
        ["Name"=>"edtVar",          "IO"=>I5_IN,    "Type"=>I5_TYPE_CHAR, "Length"=>"32000"]
    ];

    $pgm = i5_program_prepare("HPOUPD_W", $pgmCall);
    if (!$pgm) {die("<br>UpdatePO Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

    $parmIn = [
        "userProfile"      =>$userProfile,
        "maintenanceCode"  =>$maintenanceCode,
        "edtVar"           =>$edtVar
    ];

    $parmOut = [];

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {die("<br> UpdatePO Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmIn");}
    return;
}
?>
