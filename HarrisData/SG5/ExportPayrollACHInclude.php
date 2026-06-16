<?php

$xmlStr = $xmlACHDoc->asXML();
$domTableDoc = new DOMDocument("1.0");
$domTableDoc->loadXML($xmlStr);

if (!$domTableDoc->schemaValidate('ExportPayrollACH.xsd')) {
	print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
	libxml_display_errors();
	$exportXSL="N";
} else {$exportXSL="Y";}

$exportPath = "{$homePath}{$exportDirectory}";
if (!file_exists("$exportPath")) {exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$exportPath\")'");}
$dbPath = "{$exportPath}{$dataBaseID}/";
if (!file_exists("$dbPath")) {exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$dbPath\")'");}
$prACHPath = "{$dbPath}{$prACHDirectory}";
if (!file_exists("$prACHPath"))   {exec("/QOpenSys/usr/bin/system 'MKDIR DIR(\"$prACHPath\")'");}

print $domTableDoc->save("$prACHPath{$bankFile}.xml");

// Reset Pre-Notification in Bank
$stmtSQL = "";
$stmtSQL .= " Update PRBANK ";
$stmtSQL .= " Set BAACH='N' ";
$stmtSQL .= " Where BAACH='Y' and BABKNO in (Select Distinct DDBKNO From PRDDWK) ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

// Reset Pre-Notification in Bank
$stmtSQL = "";
$stmtSQL .= " Update PREACH ";
$stmtSQL .= " Set ACPRE2='N' ";
$stmtSQL .= " Where (ACPRE2='Y') ";
$stmtSQL .= " and (ACCOMP,ACFACL,ACEMPL) in (Select Distinct DDCOMP,DDFACL,DDEMPL From PRDDWK) ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

$stmtSQL = "";
$stmtSQL .= " Update PREACH ";
$stmtSQL .= " Set ACPRE3='N' ";
$stmtSQL .= " Where (ACPRE3='Y') ";
$stmtSQL .= " and (ACCOMP,ACFACL,ACEMPL) in (Select Distinct DDCOMP,DDFACL,DDEMPL From PRDDWK) ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

$stmtSQL = "";
$stmtSQL .= " Update PREACH ";
$stmtSQL .= " Set ACPRE4='N' ";
$stmtSQL .= " Where (ACPRE4='Y') ";
$stmtSQL .= " and (ACCOMP,ACFACL,ACEMPL) in (Select Distinct DDCOMP,DDFACL,DDEMPL From PRDDWK) ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

$stmtSQL = "";
$stmtSQL .= " Update PREACH ";
$stmtSQL .= " Set ACPRE5='N' ";
$stmtSQL .= " Where (ACPRE5='Y') ";
$stmtSQL .= " and (ACCOMP,ACFACL,ACEMPL) in (Select Distinct DDCOMP,DDFACL,DDEMPL From PRDDWK) ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

$stmtSQL = "";
$stmtSQL .= " Update PREACH ";
$stmtSQL .= " Set ACPRE6='N' ";
$stmtSQL .= " Where (ACPRE6='Y') ";
$stmtSQL .= " and (ACCOMP,ACFACL,ACEMPL) in (Select Distinct DDCOMP,DDFACL,DDEMPL From PRDDWK) ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

$stmtSQL = "";
$stmtSQL .= " Update PREACH ";
$stmtSQL .= " Set ACPRE1='N' ";
$stmtSQL .= " Where (ACPRE1='Y') ";
$stmtSQL .= " and (ACCOMP,ACFACL,ACEMPL) in (Select Distinct DDCOMP,DDFACL,DDEMPL From PRDDWK) ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);

$stmtSQL = "";
$stmtSQL .= " Update PREACH ";
$stmtSQL .= " Set ACPRE7='N' ";
$stmtSQL .= " Where (ACPRE7='Y') ";
$stmtSQL .= " and (ACCOMP,ACFACL,ACEMPL) in (Select Distinct DDCOMP,DDFACL,DDEMPL From PRDDWK) ";
$status = db2_exec($i5Connect->getConnection (), $stmtSQL);


if ($exportXSL=="Y") {
	require "ExportPayrollXSLInclude.php";
}

?>
