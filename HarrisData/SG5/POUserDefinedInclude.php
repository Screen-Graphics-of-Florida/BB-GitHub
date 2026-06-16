<?php

function Check_UserDefined_Errors($orderControl, $line)
{
    $file = ($line > 0) ? 'POOUMD' : 'POOUMS';
    $edtVar = "@@file" . $file . "}{@@octl" . $orderControl . "}{" . "@@line" . $line . "}{";
    $errVar = "";
    $errFound = "V";
    $returnValue = Maintain_UserDefined("HPOUHU_W", $errFound, $edtVar, $errVar);
    return ($returnValue['errFound']);
}

// Maintenance Edit (passing Hex Handle)
function Maintain_UserDefined($pgmName, $errFound, $edtVar, $errVar)
{
    global $pgmLibrary, $i5Connect, $userProfile;
    if (is_null($errFound)) $errFound = "";
    if (is_null($edtVar)) $edtVar = "";
    if (is_null($errVar)) $errVar = "";

    $pgmCall = array(
        array("Name" => "userProfile", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "10"),
        array("Name" => "errFound", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "1"),
        array("Name" => "edtVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"),
        array("Name" => "errVar", "IO" => I5_INOUT, "Type" => I5_TYPE_CHAR, "Length" => "32000"));

    $pgm = i5_program_prepare("$pgmName", $pgmCall);
    if (!$pgm) {
        die("<br>Validate_Data ($pgmName) prepare error. Error Number=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $parmIn = array(
        "userProfile" => $userProfile,
        "errFound" => $errFound,
        "edtVar" => $edtVar,
        "errVar" => $errVar);

    $parmOut = array(
        "userProfile" => "userProfile",
        "errFound" => "errFound",
        "edtVar" => "edtVar",
        "errVar" => "errVar");

    $ret = i5_program_call($pgm, $parmIn, $parmOut);
    if (function_exists('i5_output')) extract(i5_output());
    if (!$ret) {
        die("<br>Validate_Data ($pgmName) call errno=" . i5_errno() . " msg=" . i5_errormsg());
    }

    $returnValue['userProfile'] = $userProfile;
    $returnValue['errFound'] = $errFound;
    $returnValue['edtVar'] = $edtVar;
    $returnValue['errVar'] = $errVar;
    return $returnValue;
}

function Display_PO_UserDefined_Columns(array $udCol, $poNumber, $seq, $line = 0)
{
    $poCol = Rtv_UserDefined_OpenHistory($poNumber, $seq, $line);
    foreach ($udCol as $udFld) {
        $UFFLDN = trim($udFld['UFFLDN']);
        $UFDESC = trim($udFld['UFDESC']);
        $UFTYPE = trim($udFld['UFTYPE']);
        $UFDECM = trim($udFld['UFDECM']);
        $UFBOXS = trim($udFld['UFBOXS']);
        $OUFLDD = "";
        $OUFLDR = "";
        $OUFLDV = "";

        foreach ($poCol as $poFld) {
            if ($UFFLDN == trim($poFld['FLDN'])) {
                $OUFLDD = trim($poFld['FLDD']);
                $OUFLDR = trim($poFld['FLDR']);
                $OUFLDV = trim($poFld['FLDV']);
            }
        }

        if ($UFTYPE == "N") {
            $OUFLDR = number_format($OUFLDR, $UFDECM, '.', '');
            Build_DspFld($UFDESC,$OUFLDR,"","N");
        } else if ($UFTYPE == "D") {
            $OUFLDD = DateFromISO($OUFLDD);
            Build_DspFld($UFDESC,$OUFLDD,"","D");
        } else if ($UFTYPE == "A") {
            Build_DspFld($UFDESC,$OUFLDV,"","A");
        } else if ($UFTYPE == "C") {
            print "\n <tr><td class=\"dsphdr\">$UFDESC</td> ";
            print "\n <td class=\"inputalph\" colspan=\"10\"> ";
            print "\n   <textarea readonly name=\"$UFFLDN\" id=\"$UFFLDN\" ROWS=$UFBOXS COLS=60>" . rtrim($OUFLDV) . "</textarea>";
            print "\n </td></tr> ";
        }
    }
}

function Rtv_PO_UserDefined_Columns($fileName, $vendorNumber, $type, $pcls = null)
{
    global $i5Connect;
    if ($fileName == 'POOUHH') {
        $fileName = 'POOUMS';
    } elseif ($fileName == 'POOUHD'){
        $fileName = 'POOUMD';
    }
    $stmtSQL = "Select * From SYUDFM a                                        
				Where a.UFFILN='" . $fileName . "' and (not exists (Select * From SYUDCC 
				Where CCFILN=a.UFFILN and CCFLDN=a.UFFLDN) or                 
				(exists (Select * From SYUDCC                                 
				Where CCFILN=a.UFFILN and CCFLDN=a.UFFLDN and                 
				(CCCUST>0 and CCCUST=" . $vendorNumber . " or 
                 CCORTY<>' ' and CCORTY='" . $type . "'";
    if ($fileName == 'POOUMD') {
        $stmtSQL .= " or CCPCLS<>' ' and CCPCLS='" . $pcls . "'";
    }
    $stmtSQL .= "))))";
    $stmtSQL .= "Order By a.UFFSEQ ";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));
    $udCol = [];
    while ($row = db2_fetch_assoc($sqlResult)) {
        $udCol[] = $row;
    }
    return $udCol;
}

function Rtv_UserDefined_Values($orderControl, $fromPO, $line)
{
    global $i5Connect;
    if ($fromPO > 0) {
        if ($line > 0) {
            $stmtSQL = "Select DUFLDN as WUFLDN,DUFLDD as WUFLDD,DUFLDR as WUFLDR,DUFLDV as WUFLDV from POOUMD  ";
            $stmtSQL .= "Where DUORD=$fromPO and DULINE=$line Order By DUFLDN ";
        } else {
            $stmtSQL = "Select SUFLDN as WUFLDN,SUFLDD as WUFLDD,SUFLDR as WUFLDR,SUFLDV as WUFLDV from POOUMS  ";
            $stmtSQL .= "Where SUORD=$fromPO Order By SUFLDN ";
        }
    } else {
        if ($line > 0) {
            $stmtSQL = "Select * from PODTLWU  ";
            $stmtSQL .= "Where WUOCTL=$orderControl and WULINE=$line Order By WUFLDN ";
        } else {
            $stmtSQL = "Select * from POHDRWU  ";
            $stmtSQL .= "Where WUOCTL=$orderControl Order By WUFLDN ";
        }
    }
    $stmtSQL .= "For Fetch Only with NC ";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));
    while ($row = db2_fetch_assoc($sqlResult)) {
        $poCol[] = $row;
    }
    return $poCol;
}


function Rtv_UserDefined_OpenHistory($poNumber, $seq, $line)
{
    global $i5Connect;
    if ($line > 0) {
        if ($seq > 0) {
            $stmtSQL = "Select coalesce(HUFLDN,'') as FLDN,coalesce(HUFLDD,'0001-01-01') as FLDD,coalesce(HUFLDR,0) as FLDR,
                               coalesce(HUFLDV,'') as FLDV From POOUHD  ";
            $stmtSQL .= "Where HUORD=$poNumber and HUSEQ=$seq and HULINE=$line Order By HUFLDN";
        } else {
            $stmtSQL = "Select coalesce(DUFLDN,'') as FLDN,coalesce(DUFLDD,'0001-01-01') as FLDD,coalesce(DUFLDR,0) as FLDR,
                               coalesce(DUFLDV,'') as FLDV From POOUMD  ";
            $stmtSQL .= "Where DUORD=$poNumber and DULINE=$line Order By DUFLDN";
        }
    } else {
        if ($seq > 0) {
            $stmtSQL = "Select coalesce(HUFLDN,'') as FLDN,coalesce(HUFLDD,'0001-01-01') as FLDD,coalesce(HUFLDR,0) as FLDR,
                               coalesce(HUFLDV,'') as FLDV From POOUHH  ";
            $stmtSQL .= "Where HUORD=$poNumber and HUSEQ=$seq Order By HUFLDN";
        } else {
            $stmtSQL = "Select coalesce(SUFLDN,'') as FLDN,coalesce(SUFLDD,'0001-01-01') as FLDD,coalesce(SUFLDR,0) as FLDR,
                               coalesce(SUFLDV,'') as FLDV From POOUMS  ";
            $stmtSQL .= "Where SUORD=$poNumber Order By SUFLDN";
        }
    }
    $stmtSQL .= " For Fetch Only with NC ";
    $sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array('cursor' => DB2_SCROLLABLE));
    while ($row = db2_fetch_assoc($sqlResult)) {
        $poCol[] = $row;
    }
    return $poCol;
}
?>