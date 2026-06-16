<?php

/**
 * Build the View Check Box SQL Variable
 *
 * @param array $vcbd
 * @param array $vcb
 * @return string
 */
function Build_CheckBoxSQL($vcbd, $vcb)
{
    global $userProfile;
    $vcbSQL = array();
    $vcbGrp = "";
    if (isset($vcbd)) {
        foreach ($vcbd as $cx => $cv) {
            if ($vcb[$cx]) {
                $group = trim($cv[7]);
                if ($vcbGrp != "" && $group != $vcbGrp) {
                    $vcbSQL[$vcbGrp] .= ")";
                }
                if ($vcbSQL[$group]) {
                    $vcbSQL[$group] .= " or {$cv[5]}";
                } else {
                    $vcbSQL[$group] .= " ({$cv[5]}";
                }
                $vcbGrp = $group;
            }
        }
        if ($vcbSQL[$group] != "") {
            $vcbSQL[$group] .= ")";
        }
    }
    $wrkSQL = null;
    foreach ($vcbSQL as $grpSQL) {
        if ($wrkSQL) {
            $wrkSQL .= " and ";
        }
        $wrkSQL .= $grpSQL;
    }
    while (strpos($wrkSQL, (string)"@@global") !== false) {
        $parmName = Decat_Global($wrkSQL);
        if ($parmName == "") {
            break;
        }
        $wrkSQL = str_replace("@@global[$parmName]", trim($GLOBALS[$parmName]), $wrkSQL);
    }
    return $wrkSQL;
}

function subval_sort($a, $subkey)
{
    foreach ($a as $k => $v) {
        $b[$k] = strtolower($v[$subkey]);
    }
    asort($b);
    foreach ($b as $key => $val) {
        $c[] = $a[$key];
    }
    return $c;
}

function Build_SelData($selData, $upperCase, $operand, $fldType)
{
    global $wildSearchDft;

    $selData = trim($selData);
    if ($selData != "") {
        if ($fldType != "N") {
            $selData = str_replace("'", "''", $selData);
        }

        if (($operand == "LIKE" || $operand == "NOT LIKE") && ($wildSearchDft == "1" || $wildSearchDft == "2" || $wildSearchDft == "3")) {
            if (strpos($selData, (string)"*") === false && (strpos($selData, (string)"?") === false)) {
                if ($wildSearchDft == "1") {
                    $selData = "*{$selData}";
                } elseif ($wildSearchDft == "2") {
                    $selData = "{$selData}*";
                } elseif ($wildSearchDft == "3") {
                    $selData = "*{$selData}*";
                }
            }
        }

        if ($upperCase == "U") {
            $selData = strtoupper($selData);
        }

        if ($fldType == "A") {
            $selData = str_replace("?", "_", $selData);
            $selData = str_replace("*", "%", $selData);
        } elseif ($fldType == "D") {
            $selData = DateToCYMD($selData);
        } elseif ($fldType == "DP") {
            $selData = PeriodToCYP($selData);
        } elseif ($fldType == "DG") {
            $selData = str_replace("?", "_", $selData);
            $selData = str_replace("*", "%", $selData);
        } elseif ($fldType == "I") {
            if ($selData == "0") {
                $selData = "000000";
            }
            $dateOut = Date_To_ISO($selData);
        }
    }
    return $selData;
}

/**
 * Enter description here...
 *
 * @param string $fldName
 * @param string $fldDesc
 * @param string $selData
 * @param (U,b) $upperCase
 * @param string $operand
 * @param string $fldType
 * @return string
 */
function Build_WildCard($fldName, $fldDesc, $selData, $upperCase, $operand, $fldType)
{
    global $wildSearchDft, $wildCardTemp, $wildCardSearch, $wildDisplayTemp, $wildCardDisplay, $andOr;
    global $dateEdit;

    $selData = trim($selData);
    $today = FALSE;
    $offset = '';
    if ($fldType == "D" || $fldType == "CYR" || $fldType == "IN" || $fldType == "I" || $fldType == "TSD" || $fldType == "TIMESTAMP") {
        $todayPos = strpos(strtoupper($selData), (string)'TODAY');
        if ($todayPos !== false) {
            $offset = substr($selData, $todayPos + 6);
            $selData = 'TODAY';
            $today = TRUE;
        }
    }
    $now = false;
    $nowOffset = '';
    if ($fldType == "TIMESTAMP") {
        $nowPos = strpos(strtoupper($selData), (string)'NOW');
        if ($nowPos !== false) {
            $nowOffset = substr($selData, $nowPos + 4);
            $selData = 'NOW';
            $now = true;
        }
    }
    if ($fldType != "N") {
        $selData = str_replace("'", "''", $selData);
    }
    // if ($fldType!="A" && $fldType!="V" && $operand != "IN") {$selData = preg_replace('/\D/', '', $selData);}
    if (! $today && ! $now && $fldType != "A" && $fldType != "V" && $fldType != "TIMESTAMP" && strtoupper(trim($operand)) != "IN") {
        if ($fldType == "D" || $fldType == "I" || $fldType == "IN" || $fldType == "DP") {
            $selData = preg_replace('/[^0-9]*/', '', $selData);
        } else {
            $selData = preg_replace('/[^0-9.-]*/', '', $selData);
        }
    }

    if ($selData != "") {
        if (! $today && ! $now && ($operand == "LIKE" || $operand == "NOT LIKE") && ($wildSearchDft == "1" || $wildSearchDft == "2" || $wildSearchDft == "3")) {
            if (strpos($selData, (string)"*") === false && strpos($selData, (string)"?") === false) {
                if ($wildSearchDft == "1") {
                    $selData = "*{$selData}";
                } elseif ($wildSearchDft == "2") {
                    $selData = "{$selData}*";
                } elseif ($wildSearchDft == "3") {
                    $selData = "*{$selData}*";
                }
            }
        }

        if ($upperCase == "U") {
            $selData = strtoupper($selData);
        }

        if ($operand == "<>") {
            $displayOper = "Not=";
        } else {
            $displayOper = $operand;
        }

        if ($andOr == "") {
            $andOr = "or";
        }

        if ($wildCardTemp == "") {
            if ($wildCardSearch == "") {
                $wildCardSearch = " and ( (";
                $wildDisplayTemp = "&nbsp;";
            } else {
                $wildPos = strrpos($wildCardSearch, ")");
                if ($wildPos !== false) {
                    $wildCardSearch = substr($wildCardSearch, 0, $wildPos);
                }
                $wildCardSearch .= " {$andOr} (";
            }
        }

        if ($wildCardTemp != "") {
            $wildCardTemp .= " and ";
            $wildDisplayTemp .= " and ";
        } elseif ($wildCardDisplay != "") {
            $wildDisplayTemp .= " <br> $andOr ";
        }

        // Alpha
        if ($fldType == "A") {
            $wildDisplayTemp .= "$fldDesc $displayOper $selData";
            $selData = str_replace("?", "_", $selData);
            $selData = str_replace("*", "%", $selData);
            $wildCardTemp .= "trim($fldName) $operand '$selData'";

            // Date
        } elseif ($fldType == "CYR") {
            while (strlen($selData) < 2) {
                $selData = "0{$selData}";
            }
            if (strlen($selData) < 4) {
                $selData = "20{$selData}";
            }
            $selDate = $selData;
            if (substr($selData, 0, 2) > "19") {
                $selData = '1' . substr($selData, 2, 2);
            } else {
                $selData = '0' . substr($selData, 2, 2);
            }
            $wildDisplayTemp .= "$fldDesc $displayOper $selDate";
            $wildCardTemp .= "$fldName $operand $selData";

            // Date
        } elseif ($fldType == "D") {
            if ($today) {
                $selDate = $selData . ' ' . $offset;
                $selData = '{CYMD' . $offset . '}';
            } else {
                while (strlen($selData) < 6) {
                    $selData = "0{$selData}";
                }
                $selDate = substr($selData, 0, 2) . $dateEdit . substr($selData, 2, 2) . $dateEdit . substr($selData, 4, 2);
                $selData = DateToCYMD($selData);
            }
            $wildDisplayTemp .= "$fldDesc $displayOper $selDate";
            $wildCardTemp .= "$fldName $operand $selData";

            // Distribution Period
        } elseif ($fldType == "DP") {
            $selDate = $selData;
            $selDate = substr($selDate, 0, 2) . $dateEdit . substr($selData, 2, 2);
            $selData = PeriodToCYP($selData);
            $wildDisplayTemp .= "$fldDesc $displayOper $selDate";
            $wildCardTemp .= "$fldName $operand $selData";

            // Digits
        } elseif ($fldType == "DG") {
            $wildDisplayTemp .= " $fldDesc $displayOper $selData ";
            $selData = str_replace("?", "_", $selData);
            $selData = str_replace("*", "%", $selData);
            $wildCardTemp .= " digits($fldName) $operand '$selData'";

            // ISO Date with null
        } elseif ($fldType == "IN") {
            if ($today) {
                $wildDisplayTemp .= " $fldDesc $displayOper $selData" . " " . $offset;
                $wildCardTemp .= " $fldName $operand '{ISO" . $offset . "}'";
            } else {
                while (strlen($selData) < 6) {
                    $selData = "0{$selData}";
                }
                $selDate = substr($selData, 0, 2) . $dateEdit . substr($selData, 2, 2) . $dateEdit . substr($selData, 4, 2);
                $wildDisplayTemp .= " $fldDesc $displayOper $selDate";
                if ($selData == "000000" && ($operand == "=" || $operand == "<=")) {
                    $wildCardTemp .= " $fldName is null";
                } elseif ($selData == "000000" && $operand == ">=") {
                    $wildCardTemp .= " $fldName is null or $fldName is not null";
                } elseif ($selData == "000000" && $operand == ">") {
                    $wildCardTemp .= " $fldName is not null";
                } else {
                    $dateOut = Date_To_ISO($selData);
                    $wildCardTemp .= " $fldName $operand '$dateOut'";
                }
            }

            // ISO Date
        } elseif ($fldType == "I") {
            if ($today) {
                $selDate = $selData . ' ' . $offset;
                $dateOut = '{ISO' . $offset . '}';
            } else {
                while (strlen($selData) < 6) {
                    $selData = "0{$selData}";
                }
                $selDate = substr($selData, 0, 2) . $dateEdit . substr($selData, 2, 2) . $dateEdit . substr($selData, 4, 2);
                $dateOut = Date_To_ISO($selData);
            }
            $wildDisplayTemp .= " $fldDesc $displayOper $selDate";
            $wildCardTemp .= " $fldName $operand '$dateOut'";

            // Numeric
        } elseif ($fldType == "N") {
            $wildDisplayTemp .= " $fldDesc $displayOper $selData";
            $wildCardTemp .= "$fldName $operand $selData";

            // Percent
        } elseif ($fldType == "PCT") {
            $wrkData = $selData / 100;
            $wildDisplayTemp .= " $fldDesc $displayOper $selData";
            $wildCardTemp .= "$fldName $operand $wrkData";

            // Phone Number
        } elseif ($fldType == "P" || $fldType == "PA") {
            $qte = ($fldType == "PA") ? "'" : "";
            $fromPhoneNumber = $selData;
            $toPhoneNumber = $selData;
            $fromPhoneNumber = str_pad($fromPhoneNumber, 10, "0");
            $toPhoneNumber = str_pad($toPhoneNumber, 10, "9");
            $wildCardTemp .= " $fldName between $qte$fromPhoneNumber$qte and $qte$toPhoneNumber$qte";
            $wildDisplayTemp .= " $fldDesc between $fromPhoneNumber and $toPhoneNumber";

            // Timestamp Date
        } elseif ($fldType == "TSD") {
            if ($today) {
                $wildDisplayTemp .= "$fldDesc $displayOper $selData" . " " . $offset;
                $wildCardTemp .= "date($fldName) $operand '{ISO" . $offset . "}'";
            } else {
                while (strlen($selData) < 6) {
                    $selData = "0{$selData}";
                }
                $selDate = substr($selData, 0, 2) . $dateEdit . substr($selData, 2, 2) . $dateEdit . substr($selData, 4, 2);
                $selData = Date_MDY_ISO($selData);
                $wildDisplayTemp .= "$fldDesc $displayOper $selDate";
                $wildCardTemp .= "date($fldName) $operand '$selData'";
            }

            // Time (Hours/Decimal)
        } elseif ($fldType == "TIMEHHDD") {
            while (strlen($selData) < 4) {
                $selData = "0{$selData}";
            }
            $int = substr($selData, 0, 2);
            $dec = substr($selData, 2, 2);
            $selData = $int . ":" . $dec;
            $dec = round($dec * 1.66666, 0);
            while (strlen($int) < 2) {
                $int = "0{$int}";
            }
            while (strlen($dec) < 2) {
                $dec = "0{$dec}";
            }
            $wrkData = $int . "." . $dec;
            $wildDisplayTemp .= " $fldDesc $displayOper $selData";
            $wildCardTemp .= "$fldName $operand $wrkData";

            // Timestamp
        } elseif ($fldType == "TIMESTAMP") {
            if ($today) {
                $wildDisplayTemp .= "$fldDesc $displayOper $selData" . " " . $offset;
                if ($operand == "LIKE" || $operand == "NOT LIKE") {
                    $wildCardTemp .= "char($fldName) $operand '{ISO}%'";
                } else {
                    $wildCardTemp .= "date($fldName) $operand '{ISO" . $offset . "}'";
                }
            } elseif ($now) {
                $wildDisplayTemp .= "$fldDesc $displayOper $selData" . " " . $nowOffset;
                $wildCardTemp .= "substr(char($fldName), 1, 19) $operand '{TSTP" . $nowOffset . "}'";
            } else {
                $wildDisplayTemp .= "$fldDesc $displayOper $selData";
                $selData = str_replace("?", "_", $selData);
                $selData = str_replace("*", "%", $selData);
                $wildCardTemp .= "char($fldName) $operand '$selData'";
            }
            // Variable Field
        } elseif ($fldType == "V") {
            $wildDisplayTemp .= " $fldDesc $displayOper $selData";
        }
    }
    $returnValue['wildCardTemp'] = $wildCardTemp;
    $returnValue['wildDisplayTemp'] = $wildDisplayTemp;
    $returnValue['wildCardSearch'] = $wildCardSearch;
    return $returnValue;
}

/**
 * Gets the Check Box Checked/Unchecked Attributes
 *
 * @param array $vcbd
 * @param string $vcbs
 * @return array
 */
function Get_CheckBox($vcbd, $vcbs)
{
    $vcb = array();
    if (isset($vcbd)) {
        foreach ($vcbd as $cx => $cv) {
            $cbxv = "@@c" . str_pad($cv[3], 3, "0", STR_PAD_LEFT);
            $checkBoxVal = Decat_Field($cbxv, $vcbs);
            $vcb[$cx] = ($checkBoxVal != "") ? $checkBoxVal : $cv[4];
        }
    }
    return $vcb;
}

function Get_OrderBy($orderByV)
{
    $orderByS = "";
    $orderByD = "";

    for ($i = 1; $i <= 9; $i ++) {
        $orbf = "@@ob{$i}f";
        $orbs = "@@ob{$i}s";
        $orbd = "@@ob{$i}d";
        $orderByFld = Decat_Field($orbf, $orderByV);
        if ($orderByFld != "") {
            if ($orderByS != "") {
                $orderByS .= ",";
                $orderByD .= ",";
            }
            $orderByS .= $orderByFld;
            $orderBySeq = Decat_Field($orbs, $orderByV);
            $orderByDsc = Decat_Field($orbd, $orderByV);
            $orderByD .= $orderByDsc;
            if ($orderBySeq == "D") {
                $orderByS .= " DESC";
                $orderByD .= " (descending)";
            }
        }
    }
    $returnArray['orderBy'] = $orderByS;
    $returnArray['orderByDisplay'] = $orderByD;
    return $returnArray;
}

function OrderBy_Sort($orderByFld)
{
    Global $orderBy, $sortAscPrimary, $sortAscSecondary, $sortDscPrimary, $sortDscSecondary;
    $sortedBy = " ";
    $sortPoint = " ";

    $fldPos = strpos($orderBy, (string)$orderByFld);

    if ($fldPos !== false) {
        $sortedBy = "sort";
        $dscFld = "$orderByFld DESC";
        $dscPos = strpos($orderBy, (string)$dscFld);
        if ($dscPos !== false) {
            if ($dscPos == 0) {
                $sortPoint = $sortDscPrimary;
            } else {
                $sortPoint = $sortDscSecondary;
            }
        } else {
            if ($fldPos == 0) {
                $sortPoint = $sortAscPrimary;
            } else {
                $sortPoint = $sortAscSecondary;
            }
        }
    }
    $returnValue['sortedBy'] = $sortedBy;
    $returnValue['sortPoint'] = $sortPoint;
    return $returnValue;
}

function Range_WildCard($fldName, $fldDesc, $fromData, $toData, $upperCase, $operand, $fldType)
{
    global $wildSearchDft, $wildCardTemp, $wildCardSearch, $wildDisplayTemp, $wildCardDisplay, $andOr;
    global $dateEdit;

    $fromData = trim($fromData);
    $fromData = str_replace("'", "''", $fromData);
    $toData = trim($toData);
    $toData = str_replace("'", "''", $toData);

    if ($fromData != "" || $toData != "") {
        if (($operand == "LIKE" || $operand == "NOT LIKE") && ($wildSearchDft == "1" || $wildSearchDft == "2" || $wildSearchDft == "3")) {
            if (strpos($fromData, (string)"*") === false && strpos($fromData, (string)"?") === false) {
                if ($wildSearchDft == "1") {
                    $fromData = "*$fromData";
                } elseif ($wildSearchDft == "2") {
                    $fromData = "$fromData*";
                } elseif ($wildSearchDft == "3") {
                    $fromData = "*$fromData*";
                }
            }
        }

        if ($upperCase == "U") {
            $fromData = strtoupper($fromData);
            $toData = strtoupper($toData);
        }

        if ($operand == "<>") {
            $displayOper = "Not=";
        } else {
            $displayOper = $operand;
        }

        if ($andOr == "") {
            $andOr = "or";
        }

        if ($wildCardTemp == "") {
            if ($wildCardSearch == "") {
                $wildCardSearch = "and ( (";
                $wildDisplayTemp = "&nbsp;";
            } else {
                $wildPos = strrpos($wildCardSearch, ")");
                if ($wildPos !== false) {
                    $wildCardSearch = substr($wildCardSearch, 0, $wildPos);
                }
                $wildCardSearch .= " $andOr (";
            }
        }

        if ($wildCardTemp != "") {
            $wildCardTemp .= " and ";
            $wildDisplayTemp .= " and ";
        } elseif ($wildCardDisplay != "") {
            $wildDisplayTemp .= " <br> $andOr ";
        }

        if ($fldType == "A" && $operand != "BETWEEN") {
            $wildDisplayTemp .= " $fldDesc $displayOper $fromData";
            $fromData = str_replace("?", "_", $fromData);
            $fromData = str_replace("*", "%", $fromData);
            $wildCardTemp .= " trim($fldName) $operand '$fromData'";
        } elseif ($fldType == "A") {
            $wildDisplayTemp .= " $fldDesc $displayOper $fromData and $toData";
            $wildCardTemp .= " trim($fldName) $operand '$fromData' and '$toData'";
        } elseif ($fldType == "D" && $operand != "BETWEEN") {
            $fromDate = substr($fromData, 0, 2) . $dateEdit . substr($fromData, 2, 2) . $dateEdit . substr($fromData, 4, 2);
            $fromData = DateToCYMD($fromData);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromDate";
            $wildCardTemp .= " $fldName $operand $fromData";
        } elseif ($fldType == "D") {
            if ($fromData == "") {
                $fromData = "000000";
            }
            if ($toData == "") {
                $toData = "000000";
            }
            $fromDate = substr($fromData, 0, 2) . $dateEdit . substr($fromData, 2, 2) . $dateEdit . substr($fromData, 4, 2);
            $toDate = substr($toData, 0, 2) . $dateEdit . substr($toData, 2, 2) . $dateEdit . substr($toData, 4, 2);
            $fromData = DateToCYMD($fromData);
            $toData = DateToCYMD($toData);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromDate and $toDate";
            $wildCardTemp .= " $fldName $operand $fromData and $toData";
        } elseif ($fldType == "DP" && $operand != "BETWEEN") {
            $fromDate = substr($fromData, 0, 2) . $dateEdit . substr($fromData, 2, 2);
            $fromData = PeriodToCYP($fromData);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromDate";
            $wildCardTemp .= " $fldName $operand $fromData";
        } elseif ($fldType == "DP") {
            if ($fromData == "") {
                $fromData = "0000";
            }
            if ($toData == "") {
                $toData = "0000";
            }
            $fromDate = substr($fromData, 0, 2) . $dateEdit . substr($fromData, 2, 2);
            $toDate = substr($toData, 0, 2) . $dateEdit . substr($toData, 2, 2);
            $fromData = PeriodToCYP($fromData);
            $toData = PeriodToCYP($toData);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromDate and $toDate";
            $wildCardTemp .= " $fldName $operand $fromData and $toData";
        } elseif ($fldType == "I" && $operand != "BETWEEN") {
            $fromDate = substr($fromData, 0, 2) . $dateEdit . substr($fromData, 2, 2) . $dateEdit . substr($fromData, 4, 2);
            while (strlen($fromData) < 7) {
                $fromData = "0{$fromData}";
            }
            $fromdateOut = Date_To_ISO($fromData);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromDate";
            $wildCardTemp .= " $fldName $operand '$fromdateOut'";
        } elseif ($fldType == "I") {
            if ($fromData == "") {
                $fromData = "000000";
            }
            if ($toData == "") {
                $toData = "000000";
            }
            $fromDate = substr($fromData, 0, 2) . $dateEdit . substr($fromData, 2, 2) . $dateEdit . substr($fromData, 4, 2);
            while (strlen($fromData) < 7) {
                $fromData = "0{$fromData}";
            }
            $fromdateOut = Date_To_ISO($fromData);
            $toDate = substr($toData, 0, 2) . $dateEdit . substr($toData, 2, 2) . $dateEdit . substr($toData, 4, 2);
            While (strlen($toData) < 7) {
                $toData = "0{$toData}";
            }
            $todateOut = Date_To_ISO($toData);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromDate and $toDate";
            $wildCardTemp .= " $fldName $operand '$fromdateOut' and '$todateOut'";
        } elseif ($fldType == "N" && $operand != "BETWEEN") {
            $wildDisplayTemp .= " $fldDesc $displayOper $fromData";
            $wildCardTemp .= " $fldName $operand $fromData";
        } elseif ($fldType == "N") {
            if ($fromData == "") {
                $fromData = "0";
            }
            if ($toData == "") {
                $toData = "0";
            }
            $wildDisplayTemp .= " $fldDesc $displayOper $fromData and $toData";
            $wildCardTemp .= " $fldName $operand $fromData and $toData";
        } elseif ($fldType == "P") {
            $fromPhoneNumber = $fromData;
            $toPhoneNumber = $toData;
            while (strlen($fromPhoneNumber) < 10) {
                $fromPhoneNumber .= "0";
            }
            while (strlen($toPhoneNumber) < 10) {
                $toPhoneNumber .= "9";
            }
            $wildDisplayTemp .= " $fldDesc between $fromPhoneNumber and $toPhoneNumber";
            $wildCardTemp .= " ($fldName between $fromPhoneNumber and $toPhoneNumber)";
        } elseif ($fldType == "TSD" && $operand != "BETWEEN") {
            $fromFormat = "*MDY";
            $toFormat = "*MDYY";
            while (strlen($fromData) < 8) {
                $fromData = "0{$fromData}";
            }
            $fromData = Reformat_Date_4($fromData, $fromFormat, $toFormat);
            $fromDate = substr($fromData, 0, 2) . "/" . substr($fromData, 2, 2) . "/" . substr($fromData, 4, 4);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromData";
            $wildCardTemp .= " $fldName $operand '$fromData'";
        } elseif ($fldType == "TSD") {
            $fromFormat = "*MDY";
            $toFormat = "*MDYY";
            While (strlen($fromData) < 8) {
                $fromData = "0{$fromData}";
            }
            $fromData = Reformat_Date_4($fromData, $fromFormat, $toFormat);
            $fromDate = substr($fromData, 0, 2) . "/" . substr($fromData, 2, 2) . "/" . substr($fromData, 4, 4);
            While (strlen($toData) < 8) {
                $toData = "0{$toData}";
            }
            $toData = Reformat_Date_4($toData, $fromFormat, $toFormat);
            $toDate = substr($toData, 0, 2) . "/" . substr($toData, 2, 2) . "/" . substr($toData, 4, 4);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromData and $toData";
            $wildCardTemp .= " $fldName $operand '$fromData' and '$toData'";
        } elseif ($fldType == "TST" && (($operand == "=" || $operand == "<>") && (strlen($fromData) == "3" || strlen($fromData) == "4"))) {
            if (strlen($fromData) == "3") {
                $fromData = "0{$fromData}";
            }
            $toData = $fromData;
            if (strlen($fromData) == "4") {
                $fromData .= "00";
            }
            if (strlen($toData) == "4") {
                $fromData .= "59";
            }

            $fromTime = substr($fromData, 0, 2) . ":" . substr($fromData, 2, 2) . ":" . substr($fromData, 4, 2);
            $toTime = substr($toData, 0, 2) . ":" . substr($toData, 2, 2) . ":" . substr($toData, 4, 2);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromTime";

            if ($operand == "=") {
                $wildCardTemp .= " $fldName BETWEEN '$fromTime' and '$toTime'";
            } else {
                $wildCardTemp .= " ($fldName < '$fromTime' or $fldName > '$toTime')";
            }
        } elseif ($fldType == "TST" && $operand != "BETWEEN") {
            if (strlen($fromData) == "1" || strlen($fromData) == "3" || strlen($fromData) == "5") {
                $fromData = "0{$fromData}";
            }

            if (strlen($fromData) == "4" && ($operand == ">" || $operand == "<=")) {
                $fromData .= "59";
            } elseif (strlen($fromData) == "4") {
                $fromData .= "00";
            } elseif (strlen($fromData) == "2") {
                $fromData .= "0000";
            }
            $fromTime = substr($fromData, 0, 2) . ":" . substr($fromData, 2, 2) . ":" . substr($fromData, 4, 2);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromData";
            $wildCardTemp .= " $fldName $operand '$fromData'";
        } elseif ($fldType == "TST") {
            if (strlen($fromData) == "1" || strlen($fromData) == "3" || strlen($fromData) == "5") {
                $fromData = "0{$fromData}";
            }
            if (strlen($fromData) == "4") {
                $fromData .= "00";
            } elseif (strlen($fromData) == "2") {
                $fromData .= "0000";
            }
            $fromDate = substr($fromData, 0, 2) . $dateEdit . substr($fromData, 2, 2) . $dateEdit . substr($fromData, 4, 2);

            if (strlen($toData) == "1" || strlen($toData) == "3" || strlen($toData) == "5") {
                $toData = "0{$toData}";
            }
            if (strlen($toData) == "4") {
                $toData .= "59";
            } elseif (strlen($toData) == "2") {
                $toData .= "0000";
            }
            $toDate = substr($toData, 0, 2) . $dateEdit . substr($toData, 2, 2) . $dateEdit . substr($toData, 4, 2);
            $wildDisplayTemp .= " $fldDesc $displayOper $fromDate and $toDate";
            $wildCardTemp .= " $fldName $operand '$fromDate' and '$toDate'";
        }
    }

    $returnValue['wildCardTemp'] = $wildCardTemp;
    $returnValue['wildDisplayTemp'] = $wildDisplayTemp;
    $returnValue['wildCardSearch'] = $wildCardSearch;
    return $returnValue;
}

function Set_OrderBy($orby)
{
    Global $orderBy;
    $sortBy = "";
    $ascDsc = "{$orby[0][0]} DESC";
    if (strpos($orderBy, (string)$ascDsc) === 0) {
        $orby[0][1] = "A";
    } else {
        $ascDsc = $orby[0][0];
        if (strpos($orderBy, (string)$ascDsc) === 0) {
            $orby[0][1] = "D";
        }
    }
    for ($row = 0; $row < 9; $row ++) {
        if ($orby[$row][0] != "") {
            $inc = $row + 1;
            $sortBy .= "@@ob{$inc}f{$orby[$row][0]}}{";
            $sortBy .= "@@ob{$inc}s{$orby[$row][1]}}{";
            $sortBy .= "@@ob{$inc}d{$orby[$row][2]}}{";
        }
    }

    return $sortBy;
}

/**
 * Sets the Check Box Variable for Saving
 *
 * @param array $vcbd
 * @param array[optional] $vcb
 * @return string
 */
function Set_CheckBox($vcbd, $vcb)
{
    $vcbs = "";
    if (isset($vcbd)) {
        foreach ($vcbd as $cx => $cv) {
            $cbxv = "@@c" . str_pad($cv[3], 3, "0", STR_PAD_LEFT);
            $cbxc = (isset($vcb)) ? $vcb[$cx] : $cv[4];
            $vcbs .= $cbxv . $cbxc . "}{";
        }
    }
    return $vcbs;
}

function Retrieve_Filter($script)
{
    global $i5Connect, $profileHandle, $tblID, $pagID, $wildCardSearch, $orderBy, $orderByDisplay, $wildCardDisplay, $dftOrderBy;
    $wildCardSearch = "";
    $orderBy = "";
    $orderByDisplay = "";
    $wildCardDisplay = "";
    $scriptName = $script;
    require 'FilterInit.php';
    require 'FilterDefault.php';
}

?>