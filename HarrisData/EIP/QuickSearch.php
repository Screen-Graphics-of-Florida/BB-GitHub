<?php
$andOr = $_POST['andOr'];
if ($andOr == "clear") {
    $andOr = null;
}
require_once 'WildCardClear.php';
$col_srch = explode('|', $_POST['qsName']);
$colName = $col_srch[0];
$colDesc = $col_srch[2];
$colType = $col_srch[3];
$colUpper = $col_srch[4];
if (trim($_POST["qsOper"]) == "") {
    if ($colType == "A") {
        $_POST["qsOper"] = "LIKE";
    } else {
        $_POST["qsOper"] = "=";
    }
} elseif (($_POST["qsOper"] == "LIKE" || $_POST["qsOper"] == "NOT LIKE") && $colType == "N") {
    $colType = "A";
}
if (trim($_POST["qsOper"]) == "") {
    $_POST["qsOper"] = $operDft;
}
$returnValue = Build_WildCard($colName, $colDesc, $_POST['qsValue'], $colUpper, $_POST['qsOper'], $colType);
require_once 'WildCardUpdate.php';
?>