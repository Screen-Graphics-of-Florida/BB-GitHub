<?php
if ($chgSrch == "D" || $resetSelectionFlag == "Y") {
    require 'stmtSQLClear.php';
    $stmtSQL .= " Delete From SYLFLW Where $sylflwNoSEQ With NC";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
}

require 'stmtSQLClear.php';
$stmtSQL .= " Select * From SYLFLW Where $sylflwNoSEQ For Fetch Only with NC ";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

if (! $row) {
    $userProfile = strtoupper($_SERVER['PHP_AUTH_USER']);
    require 'stmtSQLClear.php';
    $stmtSQL .= " Insert Into SYLFLW (LWXHND,LWSCRNU,LWTBID,LWPGID,LWFLID,LWSEQ,LWNAME,LWFVAR,LWOVAR,LWCVAR) ";
    $stmtSQL .= " Select '$profileHandle',LFSCRNU,LFTBID,LFPGID,LFFLID,LFSEQ,LFNAME,LFFVAR,LFOVAR,LFCVAR ";
    $stmtSQL .= " From SYLFLT Where ";
    $stmtSQL .= " LFTBID=$tblID and LFSCRNU='$filterScript' and LFPGID=$pagID and LFDFLT='Y' and ";
    $stmtSQL .= " (LFROLE='$activeRole' or LFROLE=' ') and (LFUSER='$userProfile' or LFUSER=' ')";
    $stmtSQL .= " Order By LFUSER DESC, LFROLE DESC, LFPGID, LFSEQ ";
    // $stmtSQL .= " Fetch first row only ";
    $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);

    if (! $row) {
        $totRow = $sylMaxSeq - 1;
        For ($i = 0; $i <= $totRow; $i ++) {
            if ($i == 0) {
                $ordByVar = Set_OrderBy($dftOrderBy);
                $viewCheckBoxString = Set_CheckBox($viewCheckBoxDef, null);
            } else {
                $ordByVar = "";
                $viewCheckBoxString = "";
            }
            require 'stmtSQLClear.php';
            $stmtSQL .= " Insert Into SYLFLW (LWXHND,LWSCRNU,LWTBID,LWPGID,LWFLID,LWSEQ,LWNAME,LWFVAR,LWOVAR,LWCVAR) ";
            $stmtSQL .= " Values ('$profileHandle','$filterScript',$tblID,$pagID,0,$i,'Default',' ','$ordByVar','$viewCheckBoxString') ";
            $status = db2_exec($i5Connect->getConnection(), $stmtSQL);
        }
    }
}
$totRow = $sylMaxSeq - 1;

For ($i = 0; $i <= $totRow; $i ++) {

    $sylflwSQL = "LWXHND='$profileHandle' and LWSCRNU='$filterScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=$i";

    if ($i == 0) {
        $ordByVar = RetValue("$sylflwSQL", "SYLFLW", "LWOVAR");
        if (trim($ordByVar) != "") {
            $returnArray = Get_OrderBy($ordByVar);
            $orderBy = $returnArray['orderBy'];
            $orderByDisplay = $returnArray['orderByDisplay'];
        }
    }

    $filterName = RetValue("$sylflwSQL", "SYLFLW", "LWNAME");
    $filterVar = RetValue("$sylflwSQL", "SYLFLW", "LWFVAR");
    if (trim($filterVar) != "") {
        if ($i == 0) {
            $wildCardSearch = Decat_Field("@@filv", $filterVar);
            $wildCardDisplay = Decat_Field("@@fild", $filterVar);
        } elseif ($i == 1) {
            $wildCardSearch1 = Decat_Field("@@filv", $filterVar);
            $wildCardDisplay1 = Decat_Field("@@fild", $filterVar);
        } elseif ($i == 2) {
            $wildCardSearch2 = Decat_Field("@@filv", $filterVar);
            $wildCardDisplay2 = Decat_Field("@@fild", $filterVar);
        } elseif ($i == 3) {
            $wildCardSearch3 = Decat_Field("@@filv", $filterVar);
            $wildCardDisplay3 = Decat_Field("@@fild", $filterVar);
        } elseif ($i == 4) {
            $wildCardSearch4 = Decat_Field("@@filv", $filterVar);
            $wildCardDisplay4 = Decat_Field("@@fild", $filterVar);
        }
    }

    if ($tag != 'QSEARCH' && $tag != 'MASTERSEARCH' && $tag != 'WILDCARD') {
        require_once 'WildCardDate.php';
    }

    if ($i == 0) {
        $viewCheckBoxString = RetValue("$sylflwSQL", "SYLFLW", "LWCVAR");
        $viewCheckBox = Get_CheckBox($viewCheckBoxDef, $viewCheckBoxString);
    }
}
?>