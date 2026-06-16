<?php
$includeName = "{$homePath}APControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "APControl$dataBaseID.php";
}
$includeName = "{$homePath}ARControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "ARControl$dataBaseID.php";
}
$includeName = "{$homePath}ETControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "ETControl$dataBaseID.php";
}
$includeName = "{$homePath}GLControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "GLControl$dataBaseID.php";
}
$includeName = "{$homePath}InventoryControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "InventoryControl$dataBaseID.php";
}
$includeName = "{$homePath}OEControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "OEControl$dataBaseID.php";
}
$includeName = "{$homePath}PEControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "PEControl$dataBaseID.php";
}
$includeName = "{$homePath}POControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "POControl$dataBaseID.php";
}
$includeName = "{$homePath}PRControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "PRControl$dataBaseID.php";
}
$includeName = "{$homePath}SystemControl{$dataBaseID}.php";
if (file_exists($includeName)) {
    require_once "SystemControl$dataBaseID.php";
}

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

// $fTblID = (isset($_GET['fTblID'])) ? $_GET['fTblID'] : 0;
// $fTitle = (isset($_GET['fTitle'])) ? $_GET['fTitle'] : null;
// $fDesc = (isset($_GET['fDesc'])) ? $_GET['fDesc'] : null;
$nMenu = (isset($_GET['nMenu'])) ? $_GET['nMenu'] : null;
$fRel = (isset($_GET['fRel'])) ? $_GET['fRel'] : null;
$fKey1 = (isset($_GET['fKey1'])) ? $_GET['fKey1'] : null;
$fKey2 = (isset($_GET['fKey2'])) ? $_GET['fKey2'] : null;
$fKey3 = (isset($_GET['fKey3'])) ? $_GET['fKey3'] : null;
$fKey4 = (isset($_GET['fKey4'])) ? $_GET['fKey4'] : null;
$fKey5 = (isset($_GET['fKey5'])) ? $_GET['fKey5'] : null;
$fVal1 = (isset($_GET['fVal1'])) ? $_GET['fVal1'] : null;
$fVal2 = (isset($_GET['fVal2'])) ? $_GET['fVal2'] : null;
$fVal3 = (isset($_GET['fVal3'])) ? $_GET['fVal3'] : null;
$fVal4 = (isset($_GET['fVal4'])) ? $_GET['fVal4'] : null;
$fVal5 = (isset($_GET['fVal5'])) ? $_GET['fVal5'] : null;
$fDsc1 = (isset($_GET['fDsc1'])) ? $_GET['fDsc1'] : null;
$fDsc2 = (isset($_GET['fDsc2'])) ? $_GET['fDsc2'] : null;
$fDsc3 = (isset($_GET['fDsc3'])) ? $_GET['fDsc3'] : null;
$fDsc4 = (isset($_GET['fDsc4'])) ? $_GET['fDsc4'] : null;
$fDsc5 = (isset($_GET['fDsc5'])) ? $_GET['fDsc5'] : null;
$debug = (isset($_GET['debug'])) ? $_GET['debug'] : null;
$downloadToCsv = (isset($_GET['downloadToCsv'])) ? $_GET['downloadToCsv'] : null;

if (! isset($_GET['pagID'])) {
    $pagID = Rtv_Default_Page($tblID, $activeRole, $userProfile, "L");
}

$stmtSQL = "Select DSXML From SYDCST Where DSTBID=$tblID";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
if (is_resource($sqlResult)) {
    ($row = db2_fetch_array($sqlResult));
    $hdDocset = simplexml_load_string($row[0]);
} else {
    print "Document Set for Table $tblID not found";
    exit();
}

$stmtSQL = "Select PDXML From SYDSGN Where PDTBID=$tblID and PDPGID=$pagID and PDTYPE='L'";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL);
if (is_resource($sqlResult)) {
    ($row = db2_fetch_array($sqlResult));
    $hdList = simplexml_load_string($row[0]);
} else {
    print "Design Page for Table $tblID not found";
    exit();
}

$hdDocsetTable = $hdDocset->table;
$hdDocsetKeys = $hdDocset->keys;
$hdDocsetRow = $hdDocset->row;
$hdDocsetLink = $hdDocset->link;
$hdDocsetFilter = $hdDocset->filter;
$sqlView = (string) $hdDocsetTable->name;
$docScript = (string) $hdDocsetTable->doc_script_name;
$programName = (string) $hdDocsetTable->prog_opt_sec_prog;
$xmlContainer = (string) $hdDocsetTable->XML_container;
if (! $docScript)
    $docScript = (string) $hdDocsetTable->maint_script_name;
$hdList_OPT = array(
    "sec_01" => "Y",
    "sec_02" => "Y",
    "sec_03" => "Y",
    "sec_04" => "Y",
    "sec_05" => "Y",
    "sec_06" => "Y",
    "sec_07" => "Y",
    "sec_08" => "Y",
    "sec_09" => "Y",
    "sec_10" => "Y",
    "sec_11" => "Y",
    "sec_12" => "Y",
    "sec_13" => "Y",
    "sec_14" => "Y",
    "sec_15" => "Y"
);
if (! $formatToPrint && ! $downloadToCsv && $HDRPGO != "N") {
    $hdList_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);
}

$hdListTable = $hdList->table;
$hdListPaging = $hdList->paging;
$hdListRow = $hdList->row;
$hdListLink = $hdList->link;
$hdListFilter = $hdList->filter;
$pageHeading1 = (string) $hdListTable->heading1;
$pageHeading2 = (string) $hdListTable->heading2;
$dftSrchColumn = (string) $hdListTable->dft_Search;
$ovrRowsPerPage = (string) $hdListPaging->rows;
if ($hdListPaging->select && $pageSelectList == "Y") {
    $pagingSelectList = "Y";
}
Search_Column();

$sqlWhere = null;
$fKey = array(
    $fKey1 => array(
        $fVal1,
        $fDsc1
    ),
    $fKey2 => array(
        $fVal2,
        $fDsc2
    ),
    $fKey3 => array(
        $fVal3,
        $fDsc3
    ),
    $fKey4 => array(
        $fVal4,
        $fDsc4
    ),
    $fKey5 => array(
        $fVal5,
        $fDsc5
    )
);
Build_Where($fKey);

function Rtv_Default_Page($tblID, $activeRole, $userProfile, $pageType)
{
    global $i5Connect;

    $pagID = 0;
    $stmtSQL = "Select PDPGID From SYDSGN ";
    $stmtSQL .= "Where PDTBID=$tblID and ";
    $stmtSQL .= "(PDROLE='$activeRole' or PDROLE=' ') and ";
    $stmtSQL .= "(PDUSER='$userProfile' or PDUSER=' ') and ";
    $stmtSQL .= "(PDTYPE='$pageType')  ";
    $stmtSQL .= "Order By PDUSER DESC,PDROLE DESC,PDPGID DESC ";
    $stmtSQL .= "Fetch First Row Only ";

    $sqlResult = db2_exec($i5Connect->GetConnection(), $stmtSQL);
    $row = db2_fetch_assoc($sqlResult);
    return $row[PDPGID];
}

function Decat_Parm($edtVar)
{
    $fieldValue = "";
    $ssStart = strpos($edtVar, (string)"@@parm") + 7;
    $ssLength = strpos($edtVar, (string)"]", $ssStart) - $ssStart;
    if ($ssStart !== false) {
        $fieldValue = substr($edtVar, $ssStart, $ssLength);
    }
    return $fieldValue;
}

function Decat_Global($edtVar)
{
    $fieldValue = "";
    $ssStart = strpos($edtVar, (string)"@@global") + 9;
    $ssLength = strpos($edtVar, (string)"]", $ssStart) - $ssStart;
    if ($ssStart !== false) {
        $fieldValue = substr($edtVar, $ssStart, $ssLength);
    }
    return $fieldValue;
}

function Get_Column()
{
    global $hdListRow, $hdDocsetRow, $listCol, $col, $colName, $colHeading, $colFormat, $coldType, $colfType, $colSize, $colDecm, $coldftVal;
    $listCol = $hdListRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
    $col = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
    if ($listCol[0]->label) {
        $colHeading = (string) $listCol[0]->label;
    } else {
        $colHeading = (string) $col[0]->label;
    }
    $colHeading = Format_Heading($colHeading);
    $colFormat = (string) strtoupper($col[0]->format);
    $coldType = (string) strtoupper($col[0]->data_type);
    $colfType = (string) strtoupper($col[0]->flag_type);
    $colSize = (string) $col[0]->length;
    $coldftVal = (string) strtoupper($col[0]->default_value);
    if ($listCol[0]->length) {
        $colSize = (string) $listCol[0]->length;
    } else {
        $colSize = (string) $col[0]->length;
    }
    if ($listCol[0]->decimal) {
        $colDecm = (string) $listCol[0]->decimal;
    } else {
        $colDecm = (string) $col[0]->decimal;
    }
    if (! $colDecm) {
        $colDecm = 0;
    }
}

function Search_Column()
{
    global $dspCol, $srchCol, $hdDocsetRow, $hdListRow, $hdDocsetKeys, $dspTot;
    $srchCol = array();
    $dspTot = [];
    foreach ($hdListRow->col as $listCol) {
        $colName = trim($listCol['id']);
        if ($listCol->total) {
            $dspTot[] = $colName;
        }
        $wrkCol = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
        $colHeading = (string) $wrkCol[0]->label;
        if (trim($colHeading) == "") {
            continue;
        }
        $colFormat = (string) strtoupper($wrkCol[0]->format);
        $col = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
        $colRel1 = (string) $col[0]->related_column_1;

        // Don't display columns passed a key fields
        if ($GLOBALS["fKey1"] == $colName || $GLOBALS["fKey2"] == $colName || $GLOBALS["fKey3"] == $colName || $GLOBALS["fKey4"] == $colName || $GLOBALS["fKey5"] == $colName) {
            continue;
        }

        if ($colRel1 != '') {
            if ($GLOBALS["fKey1"] == $colRel1 || $GLOBALS["fKey2"] == $colRel1 || $GLOBALS["fKey3"] == $colRel1 || $GLOBALS["fKey4"] == $colRel1 || $GLOBALS["fKey5"] == $colRel1) {
                continue;
            }
        }

        $userDef = true;
        if (strpos($colHeading, (string)"@@parm[") !== false) {
            $parmName = Decat_Parm($colHeading);
            if ($parmName == "" || $GLOBALS["$parmName"] == "") {
                $userDef = false;
            }
        }

        $colCond = (string) trim($col[0]->condition_criteria);
        if (! $colCond && $colRel1) {
            $relCol = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colRel1)) . "']");
            $colCond = (string) trim($relCol[0]->condition_criteria);
        }
        $colCond = urldecode($colCond);
        if ($colCond) {
            while (strpos($colCond, (string)"@@parm") !== false) {
                $colCond = str_replace("\"", "'", $colCond);
                $parmName = Decat_Parm($colCond);
                if ($parmName == "") {
                    break;
                }
                $colCond = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colCond);
            }
            eval("\$testCond = " . trim($colCond) . ";");
        }

        if ($userDef && (! $colCond || (int) $testCond == true)) {
            $dspCol[] = $colName;
            if ($colFormat == "SSN") {
                continue;
            }
            if ($colRel1 == '') {
                if (! in_array($colName, $srchCol)) {
                    $srchCol[] = $colName;
                }
            } else {
                if (! in_array($colRel1, $srchCol)) {
                    $srchCol[] = $colRel1;
                }
                $colRel2 = (string) $col[0]->related_column_2;
                if ($colRel2 != '' && ! in_array($colRel2, $srchCol)) {
                    $srchCol[] = $colRel2;
                }
                if ($colFormat == "HRNAME") {
                    continue;
                }
                $colRel3 = (string) $col[0]->related_column_3;
                if ($colRel3 != '' && ! in_array($colRel3, $srchCol)) {
                    $srchCol[] = $colRel3;
                }
                $colRel4 = (string) $col[0]->related_column_4;
                if ($colRel4 != '' && ! in_array($colRel4, $srchCol)) {
                    $srchCol[] = $colRel4;
                }
                $colRel5 = (string) $col[0]->related_column_5;
                if ($colRel5 != '' && ! in_array($colRel5, $srchCol)) {
                    $srchCol[] = $colRel5;
                }
            }
        }
    }
}

function Format_Related()
{
    global $col, $row, $colRel1, $coldType, $colFormat, $colLength;
    $colLength = 50;
    $colRel2 = (string) $col[0]->related_column_2;
    $colRel3 = (string) $col[0]->related_column_3;
    $colRel4 = (string) $col[0]->related_column_4;
    $colRel5 = (string) $col[0]->related_column_5;
    $colData1 = trim($row[$colRel1]);
    $colData2 = trim($row[$colRel2]);
    $colData3 = trim($row[$colRel3]);
    $colData4 = trim($row[$colRel4]);
    $colData5 = trim($row[$colRel5]);
    $coldType = "NUMERIC";
    if ($colFormat == "ACCOUNT") {
        $colData = Format_Acct($colData1, $colData2, "N");
    } elseif ($colFormat == "COFAC") {
        $colData = Format_CoFac($colData1, $colData2, "N");
    } elseif ($colFormat == "HRNAME") {
        $coldType = "CHAR";
        $colData = Format_EmplName($colData2, $colData1, $colData3, $colData4, $colData5, "D");
    } elseif ($colFormat == "STKLOC") {
        $coldType = "CHAR";
        if ($colData1 && ! $colData2 && ! $colData3) {
            $colData = $colData1;
        } elseif ($colData1 || $colData2 || $colData3) {
            $colData = $colData1 . " - " . $colData2 . " - " . $colData3;
        }
    } elseif ($colFormat == "DTLPOINT") {
        $colData = $colData1 . " - " . $colData2;
    } else {
        $colData = $colData1 . $colData2 . $colData3 . $colData4 . $colData5;
    }
    return $colData;
}

function Format_Heading($hdg)
{
    while (strpos($hdg, (string)"@@parm[") !== false) {
        $parmName = Decat_Parm($hdg);
        if ($parmName == "") {
            break;
        }
        $hdg = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $hdg);
    }
    return $hdg;
}

function Rtv_Filter()
{
    global $hdDocsetFilter, $hdListFilter;

    $onClickAction = "window.location.href='" . $GLOBALS['baseURL'] . "&amp;chgBox={checkBoxNumber}'";
    $viewCheckBoxDef = array();
    foreach ($hdListFilter->checkbox as $col) {
        $colID = (string) $col['id'];
        $cb = $hdDocsetFilter->xpath("checkbox[@id='" . trim(strtoupper($colID)) . "']");
        $colHeading = (string) $cb[0]->label;
        if ($col[0]->default) {
            $colDflt = 1;
        } else {
            $colDflt = 0;
        }
        $colSQL = (string) urldecode($cb[0]->sql);
        $colDesc = (string) urldecode($cb[0]->desc);
        $colGroup = (string) urldecode($cb[0]->group);
        $viewCheckBoxDef[] = array(
            "",
            trim($colHeading),
            $onClickAction,
            $colID,
            $colDflt,
            trim($colSQL),
            trim($colDesc),
            trim($colGroup)
        );
    }
    return $viewCheckBoxDef;
}

function formatColumn(&$cssClass, &$value, $dType, $fType, $fLen, $colDecm, $dftDec, $display, $encode = true)
{
    $dType = strtoupper($dType);
    $fType = strtoupper($fType);
    if ($dType == "CHAR") {
        if ($fType == "PHONE") {
            $value = EditPhoneNumber($value);
        } elseif ($fType == "SSN") {
            $dataType = "SSNO";
            $mode = "I";
            $value = Format_SSN(Convert_Data($dataType, $mode, $value));
        } elseif ($fType == "CREDITCARD") {
            $dataType = "CCNO";
            $mode = "I";
            $value = Convert_Data($dataType, $mode, $value);
        } else {
            if ($fType == "CODE" || ($fType == "" && $fLen == 1)) {
                $cssClass = "colcode";
            }
            if ($display > 0 && $display <= 99 && $display < strlen($value)) {
                $value = substr($value, 0, $display) . "...";
            }
            if ($encode) {
                $value = htmlentities($value, ENT_QUOTES);
            }
        }
    }
    if ($dType == "NUMERIC" || $dType == "INTEGER" || $dType == "DATE") {
        if ($fType == "CYMD") {
            $cssClass = "coldate";
            $value = Format_Date($value, "D");
        } elseif ($fType == "CYR") {
            $cssClass = "colnmbr";
            while (strlen($value) < 3) {
                $value = "0{$value}";
            }
            if ($value > 0) {
                $yr = substr($value, 0, 1);
                if ($yr) {
                    $value = '20' . substr($value, 1, 2);
                } else {
                    $value = '19' . substr($value, 1, 2);
                }
            }
        } elseif ($fType == "ISODATE") {
            $cssClass = "coldate";
            $value = Format_Date_ISO($value, "D");
        } elseif ($fType == "PERIOD") {
            $cssClass = "colnmbr";
            $value = PeriodFromCYP($value);
        } elseif ($fType == "PHONE") {
            $cssClass = "colnmbr";
            $value = EditPhoneNumber($value);
        } elseif ($fType == "TIMEHHDD") {
            $cssClass = "colnmbr";
            $int = intval($value);
            $dec = $value - $int;
            $dec = round($dec * 60, 0);
            while (strlen($int) < 2) {
                $int = "0{$int}";
            }
            while (strlen($dec) < 2) {
                $dec = "0{$dec}";
            }
            $value = $int . $dec;
            $value = EditHrsMin($value);
        } elseif ($fType == "TIMEHHMM") {
            $cssClass = "colnmbr";
            $value = EditHrsMin($value);
        } elseif ($fType == "TIMEHHMMSS") {
            $cssClass = "colnmbr";
            $value = EditHrsMinSec($value);
        } elseif ($fType == "ZEROS") {
            $cssClass = "colnmbr";
            $value = str_pad($value, $fLen, "0", STR_PAD_LEFT);
        } elseif ($fType == "NOZERO") {
            $cssClass = "colnmbr";
            if ($value == "0")
                $value = "";
        } elseif ($fType == "CODE") {
            $cssClass = "colcode";
        } else {
            if (($colDecm > 0 || $colDecm == 0 && intval($dftDec) > 0) && $fType == "")
                $fType = "QUANTITY";
            if ($value < 0) {
                $cssClass = "colnmbrneg";
            } else {
                $cssClass = "colnmbrpos";
            }
            switch ($fType) {
                case "AMOUNT":
                    $typ = "amt";
                    break;
                case "COST":
                    $typ = "cst";
                    break;
                case "HOURS":
                    $typ = "hrs";
                    break;
                case "QUANTITY":
                    $typ = "qty";
                    break;
                case "PRICE":
                    $typ = "prc";
                    break;
                case "PERCENT":
                    $typ = "pct";
                    $value = $value * 100;
                    break;
                case "RATE":
                    $typ = "rte";
                    break;
            }
            // if ($colDecm == $dftDec) {$colDecm = $GLOBALS[$typ.'NbrDec'];}
            $value = Format_Number($value, $colDecm, $GLOBALS[$typ . 'EditCode'], $GLOBALS[$typ . 'RoundNbr'], $GLOBALS[$typ . 'BeforeChar'], $GLOBALS[$typ . 'AfterChar']);
        }
    }
}

function formatColor($operand, $compare, $color, $value)
{
    if ($operand === '' || $compare === '' || $color === '') {
        return '';
    }
    $bgColor = null;
    $compare = strtoupper(trim($compare));
    $value = strtoupper(trim($value));
    switch ($operand) {
        case "LK":         // Like
            $bgColor = (like_match($compare,$value)) ? $color : null;
            break;
        case "NL":         // Not Like
            $bgColor = (!like_match($compare,$value)) ? $color : null;
            break;
        case "EQ":         // Equal
            $bgColor = ($value === $compare) ? $color : null;
            break;
        case "NE":         // Not Equal
            $bgColor = ($value != $compare) ? $color : null;
            break;
        case "LT":         // Less Than
            $bgColor = ($value < $compare) ? $color : null;
            break;
        case "LE":         // Less Than or Equal To
            $bgColor = ($value <= $compare) ? $color : null;
            break;
        case "GT":         // Greater Than
            $bgColor = ($value > $compare) ? $color : null;
            break;
        case "GE":         // Greater Than or Equal To
            $bgColor = ($value >= $compare) ? $color : null;
            break;
    }
    return (!is_null($bgColor)) ? 'bgColor="' . $bgColor . '"' : '';
}


function formatTooltip($operand, $compare, $colHeading, $savCmp = null)
{
    $oprDesc = '';
    switch ($operand) {
        case "LK":         // Like
            $oprDesc = 'Like';
            break;
        case "NL":         // Not Like
            $oprDesc = 'Not Like';
            break;
        case "EQ":         // Equal
            $oprDesc = '=';
            break;
        case "NE":         // Not Equal
            $oprDesc = 'Not =';
            break;
        case "LT":         // Less Than
            $oprDesc = '<';
            break;
        case "LE":         // Less Than or Equal To
            $oprDesc = '<=';
            break;
        case "GT":         // Greater Than
            $oprDesc = '>';
            break;
        case "GE":         // Greater Than or Equal To
            $oprDesc = '>=';
            break;
    }
    $tip = '&#10; (' . trim($colHeading) . ' ' . $oprDesc . ' ' . $compare . ')';
    $tip .= (!is_null($savCmp)) ? '&#10; (' . $savCmp . ')' : '';
    return $tip;
}

/**
 * SQL Like operator in PHP.
 * Returns TRUE if match else FALSE.
 * @param string $pattern
 * @param string $subject
 * @return bool
 */
function like_match($pattern, $subject)
{
    while (strpos($pattern, '?') !== false) {
        $pos = strpos($pattern, '?');
        $pattern = substr_replace($pattern, "", $pos, 1);
        $subject = substr_replace($subject, "", $pos, 1);
    }

    if (strpos($pattern, '*') !== false) {
        $pattern = str_replace("*", '%', $pattern);
    } else {
        $pattern = '%' . $pattern . '%';
    }
    $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
    return (bool) preg_match("/^{$pattern}$/i", $subject);
}

/**
 * Formats a number for display
 *
 * @param mixed $inNumber
 * @param string $decimals
 * @param string $editcode
 * @param string $roundNbr
 * @param string $beforeChar
 * @param string $afterChar
 * @return string
 */
function Format_Number($inNumber, $decimals, $editcode, $roundNbr, $beforeChar, $afterChar)
{
    global $decimalChar, $creditCodeOvr, $thousandChar;

    $valEdCd = "1234ABCDJKLMZ";
    if (strpos($valEdCd, (string)$editcode) === false || is_numeric($decimals) == false) {
        return $inNumber;
    }

    $outNumber = "";
    $commas = "12ABJK";
    $zeroBal = "13ACJL";
    $negSign = "JKLM";
    $crSign = "ABCD";
    $noSign = "1234Z";
    $dec = ($decimals == "0" || $editcode == "Z") ? "" : $decimalChar;
    $thc = (strpos($commas, (string)$editcode) === false) ? "" : $thousandChar;
    $decPos = strpos($inNumber, (string)".");
    $length = strlen($inNumber);
    $scale = ($decPos === false) ? 0 : $length - $decPos - 1;

    if ($roundNbr == "Y") {
        $inNumber = stdround($inNumber, $decimals);
    } elseif ($decPos !== false && $decimals < $scale) {
        $inNumber = substr($inNumber, 0, ($decPos + $decimals + 1));
    }
    $inNumber = number_format($inNumber, $decimals, $dec, $thc);

    if ($inNumber == "0" && strpos($zeroBal, (string)$editcode) === false) {
        return $outNumber;
    }

    $negPos = strpos($inNumber, (string)"-");
    $length = strlen($inNumber);

    if ($afterChar != "") {
        $outNumber = $afterChar . $outNumber;
    }

    if (strpos($negSign, (string)$editcode) !== false) {
        if ($negPos !== false && $creditCodeOvr == "Y") {
            $outNumber = ")" . $outNumber;
        } elseif ($negPos !== false) {
            $outNumber = "-" . $outNumber;
        }
    }

    if (strpos($crSign, (string)$editcode) !== false) {
        if ($negPos !== false && $creditCodeOvr == "Y") {
            $outNumber = ")" . $outNumber;
        } elseif ($negPos !== false) {
            $outNumber = "CR" . $outNumber;
        }
    }

    $ssStart = ($negPos === false) ? 0 : $negPos + 1;
    $ssLength = ($length - $ssStart);
    $outNumber = substr($inNumber, $ssStart, $ssLength) . $outNumber;

    if (strPos($noSign, (string)$editcode) === false && $negPos !== false && $creditCodeOvr == "Y") {
        $outNumber = "(" . $outNumber;
    }

    if ($beforeChar != "") {
        $outNumber = $beforeChar . $outNumber;
    }

    return $outNumber;
}

function dspColHeadings($colD, $colK, $colName, $colHeading)
{
    $colAltSort = (string) $colD[0]->alt_sort;
    if (trim($colAltSort) == 'UPPER') {
        $colAltSort = "upper($colName)";
    }
    $sortFld = ($colAltSort) ? $colAltSort : $colName;
    $sortFld = "({$sortFld})";
    $returnV = OrderBy_Sort($sortFld);
    $sortVar = $returnV['sortedBy'];
    $sortPoint = $returnV['sortPoint'];
    $url = urlSelfBuild("ORDERBY", 1, $colName, null);
    $colHeading = str_replace('&lt;br&gt;', '<br>', $colHeading);
    $colHeading = ($colRelID) ? $colRelDesc : $colHeading;
    $colTitle = str_replace("<br>", " ", $colHeading);
    print "<th class=\"colhdr$sortVar\"><a href=\"$url\" title=\"Sequence By $colTitle\">" . $sortPoint . $colHeading . "</a></th>";
}

function urlSelfBuild($entry, $rF, $sC, $pF, $dL = "")
{
    /* if parameter not supplied, assume not changed from current status */
    if ($rF === null) {
        $rF = $GLOBALS["startRow"];
    }

    /* do not pass default values for parameters (unnecessary, wastes space) */
    if ($rF == "1") {
        $rF = null;
    }

    $url = $GLOBALS["homeURL"] . $GLOBALS["phpPath"] . $GLOBALS["scriptName"] . "?";
    $urlParm = array(
        'baseVar' => $GLOBALS["baseVar"],
        'portal' => $GLOBALS["portal"],
        'eID' => $GLOBALS["eID"],
        'tblID' => $GLOBALS["tblID"],
        'pagID' => $GLOBALS["pagID"],
        'tag' => $entry,
        'startRow' => $rF,
        'sequence' => $sC,
        'formatToPrint' => $pF,
        'downloadToCsv' => $dL
    );
    $url .= http_build_query($urlParm, '', '&amp;');
    // if ($GLOBALS["fTblID"]) {$url .= "&amp;fTblID=" . urlencode($GLOBALS["fTblID"]);}
    // if ($GLOBALS["fDesc"]) {$url .= "&amp;fDesc=" . urlencode($GLOBALS["fDesc"]);}
    // if ($GLOBALS["fTitle"]) {$url .= "&amp;fTitle=" . urlencode($GLOBALS["fTitle"]);}
    if ($GLOBALS["nMenu"]) {
        $url .= "&amp;nMenu=" . urlencode($GLOBALS["nMenu"]);
    }
    if ($GLOBALS["fRel"]) {
        $url .= "&amp;fRel=" . urlencode($GLOBALS["fRel"]);
    }
    if ($GLOBALS["fKey1"]) {
        $url .= "&amp;fKey1=" . urlencode($GLOBALS["fKey1"]) . "&amp;fVal1=" . urlencode($GLOBALS["fVal1"]);
    }
    if ($GLOBALS["fKey2"]) {
        $url .= "&amp;fKey2=" . urlencode($GLOBALS["fKey2"]) . "&amp;fVal2=" . urlencode($GLOBALS["fVal2"]);
    }
    if ($GLOBALS["fKey3"]) {
        $url .= "&amp;fKey3=" . urlencode($GLOBALS["fKey3"]) . "&amp;fVal3=" . urlencode($GLOBALS["fVal3"]);
    }
    if ($GLOBALS["fKey4"]) {
        $url .= "&amp;fKey4=" . urlencode($GLOBALS["fKey4"]) . "&amp;fVal4=" . urlencode($GLOBALS["fVal4"]);
    }
    if ($GLOBALS["fKey5"]) {
        $url .= "&amp;fKey5=" . urlencode($GLOBALS["fKey5"]) . "&amp;fVal5=" . urlencode($GLOBALS["fVal5"]);
    }
    if ($GLOBALS["fDsc1"]) {
        $url .= "&amp;fDsc1=" . urlencode($GLOBALS["fDsc1"]);
    }
    if ($GLOBALS["fDsc2"]) {
        $url .= "&amp;fDsc2=" . urlencode($GLOBALS["fDsc2"]);
    }
    if ($GLOBALS["fDsc3"]) {
        $url .= "&amp;fDsc3=" . urlencode($GLOBALS["fDsc3"]);
    }
    if ($GLOBALS["fDsc4"]) {
        $url .= "&amp;fDsc4=" . urlencode($GLOBALS["fDsc4"]);
    }
    if ($GLOBALS["fDsc5"]) {
        $url .= "&amp;fDsc5=" . urlencode($GLOBALS["fDsc5"]);
    }
    if ($GLOBALS["listWidgetCodeTrace"] == "Y") {
        $url .= "&amp;dump_data=1";
    }

    return $url;
}

function qsFormat(&$cssClass, &$operValue, $dType)
{
    /* Valid Types: A Alphanumeric; N Numeric; C Code; D Date; T Time; I Icon; S Summary; Phone Phone */
    /* Valid Displays: [A] b|nn where nn = max number of characters; */
    if ($dType == "CHAR") {
        $cssClass = "inputalph";
        $operValue = "LIKE";
    } elseif ($dType == "NUMERIC" || $dType == "DATE") {
        $cssClass = "inputnmbr";
        $operValue = "=";
    } elseif ($dType == "S") {
        $cssClass = "inputnmbr";
        $operValue = "=";
    } elseif ($dType == "C") {
        $cssClass = "inputalph";
        $operValue = "LIKE";
    } elseif ($dType == "D") {
        $cssClass = "inputalph";
        $operValue = "LIKE";
    } elseif ($dType == "T") {
        $cssClass = "inputalph";
        $operValue = "LIKE";
    } elseif ($dType == "I") {
        $cssClass = "inputalph";
        $operValue = "LIKE";
    } elseif ($dType == "Phone") {
        $cssClass = "inputnmbr";
        $operValue = "=";
    }
}

function dspLinks($link_sort)
{
    global $hdDocsetLink, $profileHandle, $dataBaseID, $commentWinVar, $inquiryWinVar, $invoiceWinVar, $retURL;
    foreach ($link_sort as $linkID) {
        foreach ($hdDocsetLink->xpath("linkid[@id='" . trim($linkID) . "']") as $col) {
            $colD2WN = (string) $col->script_name;
            $colTITL = (string) $col->link_title;
            $colTRGT = (string) $col->link_target;
            $colPARM = (string) $col->link_parm;
            $colIMG = (string) $col->image;
            if (! $colIMG) {
                $colIMG = (string) $col->link_image;
            }
            $colURL = (string) $col->link_URL;
            $colURL = str_replace("&", "&amp;", $colURL);
            if ($colURL == "@@returnURL") {
                $colURL = $_SESSION[$retURL];
            } else {
                if (strpos($colURL, (string)"@@confirm=") !== false) {
                    $confStart = strpos($colURL, (string)"@@confirm=") + 10;
                    $confEnd = strpos($colURL, (string)"@@", $confStart + 1);
                    $confLen = strpos($colURL, (string)"@@", $confStart + 1) - $confStart;
                    $confMsg = substr($colURL, $confStart, $confLen);
                    $confirmDel = "return confirmOpt('$confMsg','');";
                    $colURL = substr($colURL, $confEnd);
                }

                $colURLSearch = array();
                $colURLSearch[] = "@@homeURL";
                $colURLSearch[] = (strpos($colURL, (string)"@@phpPath") !== false) ? "@@phpPath" : "@@cGIPath";
                $colURLSearch[] = "@@optionD2W";
                $colURLReplace = array();
                $colURLReplace[] = $GLOBALS["homeURL"];
                $colURLReplace[] = (strpos($colURL, (string)"@@phpPath") !== false) ? $GLOBALS["phpPath"] : $GLOBALS["cGIPath"];
                $colURLReplace[] = $colD2WN;
                $colURLParm = array();
                $colURLParm['baseVar'] = (strpos($colURL, (string)"@@phpPath") !== false) ? $GLOBALS["baseVar"] : $GLOBALS["altBaseVar"];
                $colURLParm['portal'] = $GLOBALS["portal"];
                $colURLParm['eID'] = $GLOBALS["eID"];
                if (strpos($colURL, (string)"tblID") === false) {
                    $colURLParm['tblID'] = $GLOBALS["tblID"];
                    $colURLParm['pagID'] = $GLOBALS["pagID"];
                }
                if ($GLOBALS["fKey1"]) {
                    $colURLParm['fKey1'] = $GLOBALS["fKey1"];
                    $colURLParm['fVal1'] = $GLOBALS["fVal1"];
                }
                if ($GLOBALS["fKey2"]) {
                    $colURLParm['fKey2'] = $GLOBALS["fKey2"];
                    $colURLParm['fVal2'] = $GLOBALS["fVal2"];
                }
                if ($GLOBALS["fKey3"]) {
                    $colURLParm['fKey3'] = $GLOBALS["fKey3"];
                    $colURLParm['fVal3'] = $GLOBALS["fVal3"];
                }
                if ($GLOBALS["fKey4"]) {
                    $colURLParm['fKey4'] = $GLOBALS["fKey4"];
                    $colURLParm['fVal4'] = $GLOBALS["fVal4"];
                }
                if ($GLOBALS["fKey5"]) {
                    $colURLParm['fKey5'] = $GLOBALS["fKey5"];
                    $colURLParm['fVal5'] = $GLOBALS["fVal5"];
                }

                while (strpos($colPARM, (string)"@@parm") !== false) {
                    $parmName = Decat_Field("@@parm", $colPARM);
                    if ($parmName == "") {
                        break;
                    }
                    $parmValue = rtrim($GLOBALS[$parmName]);
                    $colPARM = str_replace("@@parm$parmName}{", urlencode($parmValue), $colPARM);
                }

                $colURL = str_replace($colURLSearch, $colURLReplace, $colURL);
                $colURL .= (strpos($colURL, (string)"?") !== false) ? "&amp;" : "?";
                $colURL .= http_build_query($colURLParm, '', '&amp;');
                $colURL .= $colPARM;
            }
            if ($colTRGT != "") {
                if (strtoupper($colTRGT) == "COMMENT") {
                    $tgt = " onclick=\"$commentWinVar\" ";
                } elseif (strtoupper($colTRGT) == "INQUIRY") {
                    $tgt = " onclick=\"$inquiryWinVar\"";
                } elseif (strtoupper($colTRGT) == "INVOICE") {
                    $tgt = " onclick=\"$invoiceWinVar\"";
                } else {
                    $tgt = " target=\"$colTRGT\"";
                }
                print "\n <a href=\"$colURL\" title=\"$colTITL\" $tgt>$GLOBALS[$colIMG]</a>";
            } else {
                print "\n <a onClick=\"saveCurrentURL();{$confirmDel}{$imageWindow}\" href=\"$colURL\" title=\"$colTITL\">$GLOBALS[$colIMG]</a>";
            }
        }
    }
}

function Link_Sort($linkType, $hdList_OPT)
{
    global $hdDocsetLink, $hdListLink, $profileHandle, $dataBaseID;
    foreach ($hdDocsetLink as $link) {
        foreach ($link as $col) {
            $colID = (string) $col[0]['id'];
            if ($hdListLink) {
                $linkID = $hdListLink->xpath("linkid[@id='" . $colID . "']");
                if (! $linkID[0]) {
                    continue;
                }
            }
            $colCond = (string) trim($col[0]->condition_criteria);
            if ($colCond) {
                $colCond = urldecode($colCond);
                while (strpos($colCond, (string)"@@parm") !== false) {
                    $colCond = str_replace("\"", "'", $colCond);
                    $parmName = Decat_Parm($colCond);
                    if ($parmName == "") {
                        break;
                    }
                    $colCond = str_replace("@@parm[$parmName]", $GLOBALS["$parmName"], $colCond);
                }
                eval("\$testCond = " . trim($colCond) . ";");
                if ((int) $testCond == false) {
                    continue;
                }
            }

            $linkCriteria = (string) trim($col[0]->link_criteria);
            if (strpos($linkCriteria, (string)"@@fKey") !== false) {
                $linkCriteria = str_replace("@@fKey1", $GLOBALS['fKey1'], $linkCriteria);
                $linkCriteria = str_replace("@@fKey2", $GLOBALS['fKey2'], $linkCriteria);
                $linkCriteria = str_replace("@@fKey3", $GLOBALS['fKey3'], $linkCriteria);
                $linkCriteria = str_replace("@@fKey4", $GLOBALS['fKey4'], $linkCriteria);
                $linkCriteria = str_replace("@@fKey5", $GLOBALS['fKey5'], $linkCriteria);
                eval("\$testCond = " . trim($linkCriteria) . ";");
                if ((int) $testCond == false) {
                    continue;
                }
            }

            $colPGOV = (string) $col->pgm_opt_program_override;
            $colPOPT = (string) $col->pgm_opt_sequence;
            $colPOPT = str_pad($colPOPT, 2, "0", STR_PAD_LEFT);
            if ($colPGOV) {
                $ovp_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $colPGOV);
            }

            if ($colPOPT == "00" || (! $colPGOV && $hdList_OPT["sec_$colPOPT"] == "Y") || ($colPGOV && $ovp_OPT["sec_$colPOPT"] == "Y")) {
                $colD2WN = (string) $col->script_name;
                $colURL = (string) $col->link_URL;
                $colName = (string) $col->column_name;
                $condSeq = (string) trim($col[0]->condition_sequence);
                if (($colD2WN || $colURL) && $col->type == $linkType) {
                    $dpos = trim($col->display_sequence);
                    $cseq = trim($col->condition_sequence);
                    if ($linkType == "C") {
                        $link_sort[$colName][$condSeq] = trim(strtoupper($col['id']));
                    } else {
                        $link_sort[$dpos . $cseq] = trim(strtoupper($col['id']));
                    }
                }
            }
        }
    }
    ksort($link_sort);
    return $link_sort;
}

function Build_Where($fKey)
{
    global $hdDocsetRow, $hdListRow, $sqlWhere, $profileHandle;
    foreach ($fKey as $colName => $val) {
        if ($colName == "") {
            continue;
        }
        $col = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
        $_SESSION[$colName] = null;
        if ($col) {
            $listCol = $hdListRow->xpath("col[@id='" . trim(strtoupper($colName)) . "']");
            if ($listCol[0]->label) {
                $colHeading = (string) $listCol[0]->label;
            } else {
                $colHeading = (string) $col[0]->label;
            }
            $coldType = (string) strtoupper($col[0]->data_type);
            $colFormat = (string) strtoupper($col[0]->format);
            $relTable = (string) $col[0]->ref_table;
            $relColumn = (string) $col[0]->ref_column;
            $relSelc = (string) $col[0]->ref_criteria;
            $colRelID = (string) strtoupper($col[0]->related_column_ID);
            $colDesc = "";
            if ($dsc) {
                $colDesc = $dsc;
            } else {
                if ($relTable != "" && $relSelc != "") {
                    while (strpos($relSelc, (string)"@@parm") !== false) {
                        $parmName = Decat_Parm($relSelc);
                        if ($parmName == "") {
                            break;
                        }
                        $val = $fKey[$parmName][0];
                        if ($fKey[$parmName][0] == "null") {
                            if ($coldType == "CHAR" || $coldType == "DATE") {
                                $val = "";
                            } else {
                                $val = 0;
                            }
                        }
                        $relSelc = str_replace("@@parm[$parmName]", $val, $relSelc);
                    }
                    $colDesc = RetValue("$relSelc", "$relTable", "$relColumn");
                } elseif (strtoupper($fKey[$colName][1]) != "NULL") {
                    $colDesc = $fKey[$colName][1];
                }
            }
            $qte = "";
            if ($coldType == "CHAR" || $coldType == "DATE") {
                $qte = "'";
            }
            $fVal = $fKey[$colName][0];
            if (strpos($fVal, (string)"@@xhnd") !== false) {
                $fVal = $profileHandle;
            }
            if ($colName && $fVal != "null") {
                if ($sqlWhere) {
                    $sqlWhere .= " and $colName={$qte}{$fVal}{$qte}";
                } else {
                    $sqlWhere = "Where $colName={$qte}{$fVal}{$qte}";
                }
            }
            if ($relKeys[$colName] == "Y" || $fVal == $profileHandle) {
                continue;
            }
            if ($colRelID) {
                $rel = $hdDocsetRow->xpath("col[@id='" . trim(strtoupper($colRelID)) . "']");
                $colFormat = (string) strtoupper($rel[0]->format);
                $colHeading = (string) $rel[0]->label;
                $colRel1 = (string) $rel[0]->related_column_1;
                $colLength = 50;
                $colRel2 = (string) $rel[0]->related_column_2;
                $colRel3 = (string) $rel[0]->related_column_3;
                $colRel4 = (string) $rel[0]->related_column_4;
                $colRel5 = (string) $rel[0]->related_column_5;
                $colData1 = trim($fKey[$colRel1][0]);
                $relKeys[$colRel2] = "Y";
                $colData2 = trim($fKey[$colRel2][0]);
                if ($colRel3) {
                    $relKeys[$colRel3] = "Y";
                    $colData3 = trim($fKey[$colRel3][0]);
                }
                if ($colRel4) {
                    $relKeys[$colRel4] = "Y";
                    $colData4 = trim($fKey[$colRel4][0]);
                }
                if ($colRel5) {
                    $relKeys[$colRel5] = "Y";
                    $colData5 = trim($fKey[$colRel5][0]);
                }
                $coldType = "NUMERIC";
                if ($colFormat == "ACCOUNT") {
                    $colData = Format_Acct($colData1, $colData2, "N");
                } elseif ($colFormat == "COFAC") {
                    if ($colData2 == 0) {
                        $colHeading = "Company";
                        $colData = $colData1;
                    } else {
                        $colData = Format_CoFac($colData1, $colData2, "N");
                    }
                } elseif ($colFormat == "HRNAME") {
                    $coldType = "CHAR";
                    $colData = Format_EmplName($colData2, $colData1, $colData3, $colData4, $colData5, "D");
                } elseif ($colFormat == "STKLOC") {
                    $coldType = "CHAR";
                    if ($colData1 && $colData2 && $colData3) {
                        $colData = $colData1 . " - " . $colData2 . " - " . $colData3;
                    }
                } elseif ($colFormat == "DTLPOINT") {
                    $colData = $colData1 . " - " . $colData2;
                } else {
                    $colData = $colData1 . $colData2 . $colData3 . $colData4 . $colData5;
                }
            } else {
                $colData = $fKey[$colName][0];
            }
            if ($colFormat == "CYMD") {
                $F_colData = Format_Date($colData, "D");
            } elseif ($colFormat == "ISODATE") {
                $F_colData = Format_Date_ISO($colData, "D");
            } elseif ($colFormat == "EMPID") {
                $empInfo = RetEmpByID($fVal);
                $F_colData = $empInfo['F_Name'];
                $colHeading = "Employee";
            } else
                ($F_colData = $colData);
            if ($colDesc) {
                $F_colData = Format_Code($F_colData);
            } else {
                $colDesc = $F_colData;
                $F_colData = "";
            }
            $_SESSION[$colName] = "<tr><td class='hdrtitl'>$colHeading:</td><td class='hdrdata'>$colDesc &nbsp; $F_colData</td></tr>";
        } elseif (trim($val[1]) != '' && trim($val[0]) != '') {
            $_SESSION[$colName] = "<tr><td class='hdrtitl'>$val[1]:</td><td class='hdrdata'>$val[0]</td></tr>";
        }
    }
}
?>