<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$vendorNumber = (isset($_GET['vendorNumber'])) ? $_GET['vendorNumber'] : 0;
$vendorName = (isset($_GET['vendorName'])) ? $_GET['vendorName'] : '';
$purchaseOrder = (isset($_GET['purchaseOrder'])) ? $_GET['purchaseOrder'] : 0;
$orderControl = (isset($_GET['orderControl'])) ? $_GET['orderControl'] : 0;
$itemNumber = (isset($_GET['itemNumber'])) ? $_GET['itemNumber'] : '';
$itemDesc = (isset($_GET['itemDesc'])) ? $_GET['itemDesc'] : '';
$cmtLine = (isset($_GET['cmtLine'])) ? $_GET['cmtLine'] : 0;
$doct = $_GET ['doct'];
$fromTable = $_GET ['fromTable'];
$specification = (isset($_GET['specification']) && trim($_GET['specification'] != '')) ? $_GET['specification'] : null;
$reset = (isset($_GET['reset']) && trim($_GET['reset'] != '')) ? $_GET['reset'] : null;

require_once 'SetLibraryList.php';
require_once "POControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$htd = 'Header';
if ($cmtLine == 999) {
    $htd = 'Trailer';
} elseif ($cmtLine > 0) {
    $htd = 'Detail';
}
$page_title = "PO {$htd} Comment Maintain";
$scriptName = "POCommentMaintain.php";
$scriptVarBase = "{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrder=" . urlencode(trim($purchaseOrder)) . "&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;doct=" . urlencode(trim($doct)) . "&amp;fromTable=" . urlencode(trim($fromTable)) . "&amp;itemNumber=" . urlencode(trim($itemNumber)) . "&amp;itemDesc=" . urlencode(trim($itemDesc)) . "&amp;cmtLine=" . urlencode(trim($cmtLine));
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$programName = "HPOPEM";
$dspMaxRows = $dspMaxRowsDft;
$prtMaxRows = $prtMaxRowsDft;

$poBusy = null;
if ($purchaseOrder > 0) {
    $busy = RetValue("POPO=$purchaseOrder", "POPOMS", "POBUSY");
    if ($busy == 'B') {
        $poBusy = 'Y';
        $tag == "MAINTAIN";
    }
}

if ($tag == "Edit_Data") {
    if ($purchaseOrder > 0) {
        $edtVar = "";
        Concat_Field("@@popo", $purchaseOrder);
        Concat_Field("@@line", $cmtLine);
        Concat_Field("@@doct", $doct);
        $edtVar .= "}{";
        UpdateComments($edtVar, $_POST['comments']);
    } else {
        $stmtSQL = "Delete From POCMTW Where CWOCTL={$orderControl} and CWDOCT='{$doct}' and CWLINE={$cmtLine}";
        $status = db2_exec($i5Connect->getConnection(), $stmtSQL);

        if (trim($_POST['comments']) != '') {
            $stmtSQL = "Insert Into POCMTW (CWOCTL,CWLINE,CWDOCT,CWCMNT)
                    Values ({$orderControl},{$cmtLine},'{$doct}','{$_POST['comments']}')";
            $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
        }
    }
    print "\n <meta http-equiv=\"refresh\" content=\"0; URL={$homeURL}{$phpPath}POComment.php{$scriptVarBase}&amp;timeStamp=" . urlencode($_SERVER['REQUEST_TIME']) . " \"> ";
    exit();
}

require_once($docType);
print "\n
<html> <head> ";
$title = "$vendorNumber $vendorName";
require_once($headInclude);

print "\n <script TYPE=\"text/javascript\">  ";
require_once 'CheckEnterChg.php';
require_once 'NoFormValidate.php';
print "\n function confirmClear() {return confirm('Confirm clear all comments');} \n";
print "\n </script> ";

require_once($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "POCOMMENTMAINTAIN";
print "\n <td class=\"content\">";

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

$doctDesc = RetValue("DODOCT='{$doct}' and (DOAPID='PO' or DOAPID='')", "HDDOCT", "coalesce(DODESC,'')");
if ($formatToPrint != "Y") {
    print "\n <td class=\"toolbar\">";
    if (is_null($poBusy)) {
        $maintainVar = "&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrder=" . urlencode(trim($purchaseOrder)) . "&amp;orderControl=" . urlencode(trim($orderControl)) . "&amp;documentType=" . urlencode(trim($doct))  . "&amp;docDescription=" . urlencode(trim($doctDesc)). "&amp;fromTable=" . urlencode(trim($fromTable)) . "&amp;itemNumber=" . urlencode(trim($itemNumber)) . "&amp;itemDesc=" . urlencode(trim($itemDesc)) . "&amp;lineNumber=" . urlencode(trim($cmtLine));
        print "\n <a href=\"{$homeURL}{$cGIPath}SpecificationSearch.d2w/REPORT{$altVarBase}{$maintainVar}\" onclick=\"$searchWinVar\">$searchSpecLrg</a> ";
        print "\n <a href=\"javascript:check(document.Chg)\">$acceptImageMed</a>";
        print "\n <a onClick=\"return confirmClear()\" href=\"{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}&amp;reset=Y\">$commentClearImage</a> ";
    }
    print "\n <a href=\"{$homeURL}{$phpPath}POComment.php{$scriptVarBase}\">$closeImageMed</a> ";
    print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";

print "\n <table $contentTable>";
Format_Header_URL("Vendor", $vendorName, $vendorNumber, "");
if ($purchaseOrder > 0) {
    Format_Header_URL("Order", $purchaseOrder, "", "");
}
if ($cmtLine > 0) {
    Format_Header_URL("Line", $cmtLine, "", "");
    Format_Header_URL("Item", $itemDesc, $itemNumber, "");
}
Format_Header_URL("Document", $doctDesc, $doct, "");
print "\n </table> ";

if (!is_null($poBusy)) {
    print "\n <span class=\"error\" $textOvr> &nbsp; &nbsp; Cannot update - Order is being maintained by another user</span>";
}

$comments = '';
if (is_null($reset)) {
    $comments = getComments();
}

print "\n \n <form class=\"formClass\" METHOD=POST NAME=\"Chg\" ACTION=\"{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=" . urlencode(trim($maintenanceCode)) . "\">";
print "\n <table $contentTable>";
print "\n <tr><td class=\"dsphdr\"><span $textOvr>Comments</span></td> ";
print "\n     <td class=\"inputalph\">";
print "\n  <textarea name=\"comments\" ROWS=20 COLS=60 WRAP=\"hard\">{$comments}</textarea>";
print "\n </td></tr> ";
print "\n </table> ";

print $hrTagAttr;
require_once 'Copyright.php';

function getComments()
{
    global $i5Connect, $purchaseOrder, $orderControl, $vendorNumber, $itemNumber, $doct, $fromTable, $cmtLine, $specification;
    $ht = ($cmtLine == 0) ? 'H' : 'T';
    if ($fromTable == 'POOCMT') {
        $stmtSQL = "Select OCCMNT as CMT From {$fromTable} Where OCORD#=$purchaseOrder and OCORL#=$cmtLine and OCDOCT='$doct'";
    } elseif ($fromTable == 'POCMTW') {
        $stmtSQL = "Select CWCMNT as CMT From {$fromTable} Where CWOCTL=$orderControl and CWLINE=$cmtLine and CWDOCT='$doct'";
    } elseif ($fromTable == 'HDCCMT') {
        $stmtSQL = "Select CXCMNT as CMT From {$fromTable} Where CXVCF='V' and CXNMBR=$vendorNumber and CXDOCT='$doct' and CXHT='$ht'";
    } elseif ($fromTable == 'HDVCIC') {
        $stmtSQL = "Select ICCMNT as CMT From {$fromTable} inner join HDVCIT on VCID=ICIC Where VCVCF='V' and VCNMBR=$vendorNumber and VCITEM='$itemNumber' and VCDOCT='$doct'";
    } elseif ($fromTable == 'HDIMXD') {
        $stmtSQL = "Select IXDESC as CMT From {$fromTable} Where IXITEM='$itemNumber' and IXDOCT='$doct'";
    } elseif ($fromTable == 'HDSPCD') {
        $stmtSQL = "Select SDCMNT as CMT From {$fromTable} Where SDSPEC='$specification'";
    } else {
        $stmtSQL = "Select DCCMNT as CMT From {$fromTable} Where (DCAPID='PO' or DCAPID=' ') and DCDOCT='$doct' and DCHT='$ht'";
    }
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));

    $comments = '';
    $startRow = 1;
    while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
        foreach ($row as $cmt) {
            $comments .= $cmt . "\n";
        }
        $startRow++;
    }
    return $comments;
}

function UpdateComments($edtVar, $comment)
{
    global $i5Connect;
    if (!$i5Connect) die("<br>UpdateComments Connection Failed. Error number =" . i5_errno() . " msg=" . i5_errormsg());

    $pgmCall = [
        ["Name" => "edtVar", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "32000"],
        ["Name" => "comment", "IO" => I5_IN, "Type" => I5_TYPE_CHAR, "Length" => "32000"]
    ];

    $pgm = i5_program_prepare("HPOCMT_W", $pgmCall);
    if (!$pgm) {
        die("<br>UpdateComments Program prepare errno=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = [
        "edtVar" => $edtVar,
        "comment" => $comment
    ];

    $parmOut = [];

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br> UpdateComments Program call errno=" . i5_errno() . " msg=" . i5_errormsg() . "Program:$pgm In:$parmIn");
    }
    return;
}

?>
