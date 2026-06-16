<?php

if ($wildCardSearch != "" && $appendWildCard!="N"){$selectSQL .= $wildCardSearch;}
if ($uv_Sql != "" && $appendUserView!="N")        {$selectSQL .= " and ($uv_Sql)";}

$stmtSQL = trim($withSQL) . " " . trim($stmtSQL);
$stmtSQL .= " From $fileSQL ";
if (trim($selectSQL) != ""){$stmtSQL .= " Where $selectSQL";}
?>