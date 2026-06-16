<?php
$purchaseOrderNumber = $_GET['purchaseOrderNumber'];
$orderSequence = $_GET['orderSequence'];
$reOpenPO = (isset($_GET['reOpenPO'])) ? $_GET['reOpenPO'] : null;
$closeCancel = (isset($_GET['closeCancel'])) ? $_GET['closeCancel'] : null;
$receiptCnt = RetValue("PHPO=$purchaseOrderNumber", "POPOHH", "coalesce(max(PHSEQ#),0)");

if ($reOpenPO == 'Y') {
    $stmtSQL = " Update POPOMS Set POSTAT='O' Where POPO={$purchaseOrderNumber} ";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
}

if (!is_null($closeCancel)) {
    $busy = RetValue("POPO=$purchaseOrderNumber", "POPOMS", "POBUSY");
    if ($busy == 'B') {
        print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}SelectPO.php{$scriptVarBase}&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber)) . "&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . " \"> ";
        exit();
    }
    $prevOrder = $_GET['prevOrder'];
    $lineNumber = (isset($_GET['lineNumber'])) ? $_GET['lineNumber'] : null;

    $edtVar = "";
    Concat_Field("@@popo", $purchaseOrderNumber);
    if (!is_null($lineNumber)) {
        Concat_Field("@@line", $lineNumber);
    }
    Concat_Field("@@clcn", $closeCancel);
    $edtVar .= "}{";
    UpdatePO($edtVar);

    if ($closeCancel == "X") {
        if (!is_null($lineNumber)) {
            $confMessage = Format_ConfMsg_Desc("D", "Line", $lineNumber, "", "", "", "");
        } else {
            $confMessage = Format_ConfMsg_Desc($closeCancel, "Purchase Order", $purchaseOrderNumber, "", "", "", "");
        }
        if (!is_null($lineNumber)) {
            print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}SelectPO.php{$scriptVarBase}&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber)) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        } elseif (trim($prevOrder) == "") {
            print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        } else {
            print "\n <meta http-equiv=\"refresh\" content=\"1; URL={$homeURL}{$phpPath}SelectPO.php{$scriptVarBase}&amp;purchaseOrderNumber=" . urlencode(trim($prevOrder)) . "&amp;confMessage=" . urlencode(trim($confMessage)) . "\"> ";
        }
        exit();
    }
}

require 'stmtSQLClear.php';
$stmtSQL .= " WITH Order as (Select POPO, POSEQ#, POVEND, POSTAT, POFL01, POFL04, POBUSY From POPOMS union Select PHPO as POPO, PHSEQ# as POSEQ#, PHVEND as POVEND, 'C' as POSTAT, '0' as POFL01, '0' as POFL04, '' as POBUSY From POPOHH left exception join POPOMS on PHPO=POPO and POSEQ#=0)";
$stmtSQL .= " Select POPO,POSEQ#,POVEND,POSTAT,POFL01,POFL04,POBUSY,(Select min(POPO) From Order b Where b.POVEND=a.POVEND and b.POPO < a.POPO) as FIRST ,(Select max(POPO) From Order b Where b.POVEND=a.POVEND and b.POPO > a.POPO) as LAST, ";
$stmtSQL .= " (Select max(POPO) From Order b Where b.POVEND=a.POVEND and b.POPO < a.POPO) as PREV, (Select min(POPO) From Order b Where b.POVEND=a.POVEND and b.POPO > a.POPO) as NEXT ";
$stmtSQL .= " From Order a Where POVEND=$vendorNumber and POPO=$purchaseOrderNumber ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

$page_title = ($row['POSTAT'] == "C") ? "Purchase Order History" : "Purchase Order";
$_SESSION['POSTAT'] = $row['POSTAT'];
if ($orderSequence == 0) {
    $orderSequence = $row['POSEQ#'];
}
if ($orderSequence > 0 && $tabID == "") {
    $tabID = "RECEIPTS";
} elseif ($orderSequence == 0 && $tabID == "") {
    $tabID = "REVIEW";
}

$maintainVar = "{$genericVarBase}&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber)) . "&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName));

print "\n \n <script TYPE=\"text/javascript\">";
print "\n function confirmReset() {return confirm(\"Confirm reset of Print Document flag\");} \n";
print "\n function confirmReopen() {return confirm(\"Confirm reopen of closed Purchase Order\");} \n";
print "\n function confirmCancel() {return confirm(\"Confirm cancel of Purchase Order\");} \n";
print "\n function confirmClose() {return confirm(\"Confirm close of Purchase Order\");} \n";
print "\n function confirmDeleteLine(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n function confirmDeleteLinked(text) {return confirm(\"Warning - Delete will remove O/E link\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n function confirmCloseLine(text)  {return confirm(\"Confirm Close of:\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n function confirmCloseLinked(text) {return confirm(\"Warning - Close will remove O/E link\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> \n";

print "\n <table $contentTable>
	         <colgroup>
	           <col width=\"85%\">
	           <col width=\"10%\">
             <tr><td><h1>$page_title</h1></td>";
if ($formatToPrint != "Y") {
    $programName = "HPOPEM";
    $hpopem_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
    print "\n <td class=\"toolbar\">";
    $attachFolder = "PurchaseOrder";
    $attachVarKey = str_pad($purchaseOrderNumber, 8, "0", STR_PAD_LEFT); ;
    $attachForDesc = "Vendor " . trim($vendorName) . "  Purchase Order " .  $purchaseOrderNumber;
    $attachPrg1 = "POPOMS Where POPO={$purchaseOrderNumber}";
    $attachPrg2 = "POPOHH Where PHPO={$purchaseOrderNumber}";
    require_once 'AttachmentInclude.php';
    if ($row['POBUSY'] != "B" && $row['POSTAT'] == "O") {
        if ($row['POFL01'] == "1" && $updOrderFlags == "Y") {
            $printDocImage = "<img border=\"$imageBorder\" src=\"{$homeURL}{$imagePath}lgForm.gif\" title=\"Click here to Print Document\">";
            print "\n  <a href=\"{$homeURL}{$cGIPath}PODocumentPrint.d2w/REPORT{$altVarBase}&amp;forPO=" . urlencode(trim($purchaseOrderNumber)) . "\">{$printDocImage}</a>";
        }
        if ($row['POFL01'] == "2" && $updOrderFlags == "Y") {
            $resetDocImage = "<img border=\"$imageBorder\" src=\"{$homeURL}{$imagePath}lgErase.gif\" title=\"Click here to reset the Print Document flag\">";
            print "\n <a onClick=\"return confirmReset()\" href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;resetPrint=Y\">{$resetDocImage}</a>";
        }
        if ($hpopem_OPT['sec_02'] == 'Y') {
            print "<a href=\"{$homeURL}{$phpPath}POComment.php{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrder=" . urlencode(trim($purchaseOrderNumber)) . "&amp;cmtLine=0&amp;noMenu=Y\" onclick = \"$commentWinVar\" title=\"Comments\">$commentExistImageLrg </a>";
            $cmtIntCnt = RetValue("OCORD#=$purchaseOrderNumber and OCDOCT='INT'", "POOCMT", "coalesce(max(OCCSEQ),0)");
            if ($cmtIntCnt > 0) {
                $cmtIntImage= (string) "<img border=\"0\" src=\"{$homeURL}{$imagePath}foryourapproval.gif\" title=\"View Internal Comments\" alt=\"Internal\">";
                print "<a href=\"{$homeURL}{$phpPath}POCommentInquiry.php{$genericVarBase}&amp;purchaseOrder=" . urlencode(trim($purchaseOrderNumber)) . "&amp;document=INT&amp;noMenu=Y\" onclick = \"$imageWinVar\" title=\"View Internal Comments\">$cmtIntImage </a>";
            }
            $openLineCnt = RetValue("PDPO=$purchaseOrderNumber and PDSTAT='O' and PDPORL=0", "POPOMD", "char(count(*))");
            if ($openLineCnt != "0") {
                $updReqDateIcon = (string)"<img border=\"0\" src=\"{$homeURL}{$imagePath}lgCalendar.gif\" title=\"Change Required Date\" alt=\"Required\">";
                print "\n <a href=\"{$homeURL}{$phpPath}POUpdateRequiredDate.php{$maintainVar}&amp;tag=MAINTAIN\" onclick=\"$smallPromptWinVar\">{$updReqDateIcon}</a> ";
            }
        }
        if ($row['POFL04'] == "0" && $hpopem_OPT['sec_02'] == 'Y' && $hpopem_OPT['sec_04'] == 'Y') {
            $inRcvCnt = RetValue("PDPO=$purchaseOrderNumber and PDQRRT<>0", "POPOMD", "char(count(*))");
            if ($inRcvCnt == "0") {
                $rcvCnt = RetValue("PDPO=$purchaseOrderNumber and (PDSTAT='C' or PDQRST<>0 or PDQRVT<>0 or PDQRFT<>0 or PDQHRT<>0)", "POPOMD", "char(count(*))");
                if ($rcvCnt == "0") {
                    $cancelIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgDelete.gif\" title=\"Cancel Purchase Order\" alt=\"Cancel\">";
                    print "\n <a onClick=\"return confirmCancel()\" href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;closeCancel=X&amp;prevOrder=" . urlencode(trim($row[PREV])) . "\">{$cancelIcon}</a>";
                } else {
                    $closeIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgC.gif\" title=\"Close Purchase Order\" alt=\"Close\">";
                    print "\n <a onClick=\"return confirmClose()\" href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;closeCancel=C\">{$closeIcon}</a>";
                }
            }
        }
    }
    if ($row['POSTAT'] == "C" && $hpopem_OPT['sec_04'] == 'Y') {
        $openPOImage = (string)"<img border=\"0\" src=\"{$homeURL}{$imagePath}available.gif\" title=\"Click here to reopen this Purchase Order\" alt=\"Reopen\">";
        print "\n <a onClick=\"return confirmReopen()\" href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;reOpenPO=Y\">{$openPOImage}</a>";
    }
    require_once 'FormatToprint.php';
    if (file_exists('R15.0_Purchasing_In_EIP_III.pdf')) {
        $designIcon = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgHelp.gif\" title=\"View Purchase Order Design\" alt=\"Help\">";
        print "<a href=\"R15.0_Purchasing_In_EIP_III.pdf\" target=\"_blank\">&nbsp; {$designIcon}</a>";
    }
    print "\n </td>";
}
print "\n </tr>";
print "\n </table>";

print "\n <table $contentTable>";
Format_Header_URL("Vendor", $vendorName, $vendorNumber, "{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)));
print "\n <tr><td class=\"hdrtitl\">Order Number:</td>";
print "\n     <td><h2>&nbsp;";
if ($formatToPrint != "Y") {
    $selectVar = "$genericVarBase&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;orderSequence=000&amp;tabID=";
    if ($row[FIRST] > 0) {
        print "\n  <a href=\"{$homeURL}{$phpPath}SelectPO.php{$selectVar}&amp;purchaseOrderNumber=" . urlencode(trim($row[FIRST])) . "\">$previousImageBegSml</a>";
    }
    if ($row[PREV] > 0) {
        print "\n  <a href=\"{$homeURL}{$phpPath}SelectPO.php{$selectVar}&amp;purchaseOrderNumber=" . urlencode(trim($row[PREV])) . "\">$previousImageSml</a>";
    }
}
print $purchaseOrderNumber;
if ($formatToPrint != "Y") {
    if ($row[NEXT] > 0) {
        print "\n  <a href=\"{$homeURL}{$phpPath}SelectPO.php{$selectVar}&amp;purchaseOrderNumber=" . urlencode(trim($row[NEXT])) . "\">$nextImageSml</a>";
    }
    if ($row[LAST] > 0) {
        print "\n  <a href=\"{$homeURL}{$phpPath}SelectPO.php{$selectVar}&amp;purchaseOrderNumber=" . urlencode(trim($row[LAST])) . "\">$nextImageEndSml</a>";
    }
}

print "\n </h2></td>";
if ($row['POBUSY'] == "B") {
    print "\n <td class=\"warningtext\">(Order is being maintained)</td>";
}
print "\n </tr></table>";

require_once 'ConfMessageDisplay.php';

print "\n <div id=\"header\">
       \n <ul id=\"primary\">";

if ($tabID == "HEADER") {
    print "\n <li><span>Header</span></li>";
} else {
    print "\n <li><a href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;tabID=HEADER&amp;orderSequence=" . urlencode(trim($orderSequence)) . "\" title=\"Click here to view header information\">Header</a></li>";
}

if ($tabID == "LINE") {
    print "\n <li><span>Item Detail</span></li>";
}

if ($orderSequence > 0) {
    $commentCnt = RetValue("OHORD#=$purchaseOrderNumber and (OHORL#=000 or OHORL#=999)", "POHCMT", "coalesce(max(OHCSEQ),0)");
} else {
    $commentCnt = RetValue("OCORD#=$purchaseOrderNumber and (OCORL#=000 or OCORL#=999)", "POOCMT", "coalesce(max(OCCSEQ),0)");
}

if ($commentCnt > 0) {
    if ($tabID == "COMMENTS") {
        print "\n <li><span>Comments</span></li>";
    } else {
        print "\n <li><a href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;orderSequence=" . urlencode(trim($orderSequence)) . "&amp;tabID=COMMENTS\" title=\"Click here to view header/trailer comments\">Comments</a></li>";
    }
}

if ($tabID == "FLAGS") {
    print "\n <li><span>Flags</span></li>";
} else {
    print "\n <li><a href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;orderSequence=" . urlencode(trim($orderSequence)) . "&amp;tabID=FLAGS\" title=\"Click here to view order flags\">Flags</a></li>";
}

if ($row['POSEQ#'] == 0) {
    if ($row['POSTAT'] == "C") {
        $tabTitle = "Closed Order";
    } else {
        $tabTitle = "Open Order";
    }
    if ($tabID == "REVIEW" && ($tabHistory != "Y" || $orderSequence > 0)) {
        print "\n <li><span>$tabTitle</span></li>";
    } else {
        print "\n <li><a href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}\">$tabTitle</a></li>";
    }
}

if ($receiptCnt > 0) {
    if ($tabID == "RECEIPTS") {
        print "\n <li><span>Receipts</span></li>";
    } else {
        print "\n <li><a href=\"{$homeURL}{$phpPath}SelectPO.php{$maintainVar}&amp;tabID=RECEIPTS&amp;orderSequence=1\" title=\"Click here to view receipts\">Receipts</a></li>";
    }
}

print "\n </ul>
       \n </div>
       \n <div id=\"main\">
       \n <div id=\"contents\">";


function UpdatePO($edtVar)
{
    global $i5Connect, $userProfile;
    $maintenanceCode = "X";
    if (!$i5Connect) die("<br>UpdatePO Connection Failed. Error number =" . i5_errno() . " msg=" . i5_errormsg());

    $pgmCall = [
        ["Name" => "userProfile", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "10"],
        ["Name" => "maintenanceCode", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "1"],
        ["Name" => "edtVar", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "32000"]
    ];

    $pgm = i5_program_prepare("HPOUPD_W", $pgmCall);
    if (!$pgm) {
        die("<br>UpdatePO Program prepare errno=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = [
        "userProfile" => $userProfile,
        "maintenanceCode" => $maintenanceCode,
        "edtVar" => $edtVar
    ];

    $parmOut = [];

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br> UpdatePO Program call errno=" . i5_errno() . " msg=" . i5_errormsg() . "Program:$pgm In:$parmIn");
    }
    return;
}