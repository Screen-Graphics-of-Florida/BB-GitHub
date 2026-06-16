<?php
require_once 'GetURLParm.php';

$vendorNumber = $_GET['vendorNumber'];
$vendorName   = $_GET['vendorName'];
$purchaseOrderNumber  = $_GET['purchaseOrderNumber'];
$orderSequence= $_SESSION['orderSeq'];

require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'POUserDefinedInclude.php';
require_once 'VarBase.php';
$scriptVarBase = "{$genericVarBase}&amp;vendorNumber=" . urlencode(trim($vendorNumber)) . "&amp;vendorName=" . urlencode(trim($vendorName)) . "&amp;purchaseOrderNumber=" . urlencode(trim($purchaseOrderNumber));
$programName = "HPOPEM";
$hpopem_OPT = pgmOptSecurity($profileHandle, $dataBaseID, $programName);

require 'stmtSQLClear.php';
if ($orderSequence > 0) {
	$userDefTable = "POOUHH";
	$stmtSQL .= " Select PHPO as POPO, PHVEND as POVEND, PHRTOV as PORTOV, PHDSHP as PODSHP, PHWHS as POWHS, PHTYPE as POTYPE,";
	$stmtSQL .= " PHBUYR as POBUYR, PHPORF as POPORF, PHRQDT as PORQDT, PHBOAL as POBOAL, PHFOB as POFOB,";
	$stmtSQL .= " PHFOBC as POFOBC, PHSVSV as POSVSV, PHSVDS as POSVDS, PHPTRM as POPTRM, PHPOTD as POPOTD,";
	$stmtSQL .= " PHDSC1 as PODSC1, PHDSC2 as PODSC2, PHDSC3 as PODSC3, ";
	$stmtSQL .= " PHDT2 as PODT2, PHDT3 as PODT3, PHDT4 as PODT4, 'B' as POBUSY, ";
	$stmtSQL .= " PHUDF1 as POUDF1, PHUDF2 as POUDF2, PHUDF3 as POUDF3, PHUDF4 as POUDF4, PHUDF5 as POUDF5, ";
	$stmtSQL .= " HDOTYP.*,coalesce(VMVNA1,'') as VMVNA1,coalesce(BMBNA1,' ') as BMBNA1,";
	$stmtSQL .= " coalesce(a.DSNAME,b.DSNAME,'') as DSNAME, coalesce(WHWHNM,'') as WHWHNM";
	$fileSQL .= " POPOHH";
	$fileSQL .= " left join HDVEND on PHRTOV=VMVEND";
	$fileSQL .= " left join HDDSHP a on PHVEND=a.DSVNCS and PHDSHP=a.DSNMBR and a.DSVCF='C' and PHDSHP>0 and PHDSHC='C'";
	$fileSQL .= " left join HDDSHP b on PHDSHP=b.DSNMBR and b.DSVCF='V' and PHDSHP>0 and PHDSHC=' '";
	$fileSQL .= " left join HDWHSM on PHWHS=WHWHS";
	$fileSQL .= " left join HDBUYR on PHBUYR=BMBUYR";
	$fileSQL .= " left join HDOTYP on OTOTCD=PHTYPE and OTAPID='PO'";
	$selectSQL = "PHPO=$purchaseOrderNumber and PHSEQ#=$orderSequence";
} else {
	$userDefTable = "POOUMS";
	$stmtSQL .= " Select POPOMS.*,HDOTYP.*,coalesce(VMVNA1,'') as VMVNA1,coalesce(BMBNA1,' ') as BMBNA1,";
	$stmtSQL .= " coalesce(a.DSNAME,b.DSNAME,'') as DSNAME, coalesce(WHWHNM,'') as WHWHNM";
	$fileSQL .= " POPOMS";
	$fileSQL .= " left join HDVEND on PORTOV=VMVEND";
	$fileSQL .= " left join HDDSHP a on POVEND=a.DSVNCS and PODSHP=a.DSNMBR and a.DSVCF='C' and PODSHP>0 and PODSHC='C'";
	$fileSQL .= " left join HDDSHP b on PODSHP=b.DSNMBR and b.DSVCF='V' and PODSHP>0 and PODSHC=' '";
	$fileSQL .= " left join HDWHSM on POWHS=WHWHS";
	$fileSQL .= " left join HDBUYR on POBUYR=BMBUYR";
	$fileSQL .= " left join HDOTYP on OTOTCD=POTYPE and OTAPID='PO'";
	$selectSQL = "POPO=$purchaseOrderNumber";
}

require 'stmtSQLSelect.php';
$stmtSQL .= " Order By POPO";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

$udCol = Rtv_PO_UserDefined_Columns($userDefTable, $row['POVEND'], $row['POTYPE']);
$userDefHdrCnt = (!empty($udCol)) ? count($udCol) : 0;

if ($hpopem_OPT['sec_02'] == 'Y' && trim($row[POBUSY]) == '' && $_SESSION['POSTAT'] == 'O') {
	if ($userDefHdrCnt > 0) {
		$lgU = "<img border=\"0\" src=\"{$homeURL}{$imagePath}lgU.gif\" title=\"Update User-Defined\" alt=\"U\">";
		print "\n <div class=\"quickLinksTop\"><a href=\"{$homeURL}{$phpPath}POUserDefinedMaintain.php{$genericVarBase}&amp;udTable=POOUMS&amp;tag=MAINTAIN&amp;fromPO=" . urlencode(trim($purchaseOrderNumber)) . "\" . onclick = \"$inquiryWinVar\">$lgU</a></div>";
	}
	print "\n <div class=\"quickLinksTop\"><a href=\"{$homeURL}{$phpPath}POHeaderMaintain.php{$scriptVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=C&amp;noMenu=Y\" onclick = \"$commentWinVar\" title=\"Comments\">$changeImageLrg</a></div>";
}
print "<table $contentTable> <tr>";

Build_DspFld("Warehouse",$row['WHWHNM'],Format_Code($row['POWHS']),$fldType);
if ($row['PODSHP'] > 0) {Build_DspFld("Drop Ship",$row['DSNAME'],Format_Code($row['PODSHP']),$fldType);}
Build_DspFld("Remit-To Vendor",$row['VMVNA1'],Format_Code($row['PORTOV']),$fldType);
Build_DspFld("Buyer",$row['BMBNA1'],Format_Code($row['POBUYR']),$fldType);
Build_DspFld("Reference",$row['POPORF']);
$PORQDT=DateInputFromCYMD($row['PORQDT']);
Build_Fld_Entry("Required Date","rqdt","dspalph","Date","",$PORQDT,"","","","","Y","");
Build_DspFld("Allow Backorders",$row['POBOAL']);
Build_DspFld("FOB",$row['POFOB'],Format_Code($row['POFOBC']),$fldType);
Build_DspFld("Ship Via",$row['POSVDS'],Format_Code($row['POSVSV']),$fldType);
Build_DspFld("Terms",$row['POPOTD'],Format_Code($row['POPTRM']),$fldType);
if ($orderSequence == "") {
	Build_DspFld("Confirmation Flag",$row[POCONF]);
	Build_DspFld("Change Notice",$row[POCHNN]);
}
$F_PODSC1 = Format_Nbr($row['PODSC1']*100, $pctNbrDec, $pctEditCode, "", "", "");
$F_PODSC2 = Format_Nbr($row['PODSC2']*100, $pctNbrDec, $pctEditCode, "", "", "");
$F_PODSC3 = Format_Nbr($row['PODSC3']*100, $pctNbrDec, $pctEditCode, "", "", "");
Build_DspFld("Trade Discount","$F_PODSC1 &nbsp; $F_PODSC2 &nbsp; $F_PODSC3");

if ($row['OTSDT1'] !="") {
	$PODT2=DateInputFromCYMD($row['PODT2']);
	Build_Fld_Entry($row['OTSDT1'],"sdt1","dspalph","Date","",$PODT2,"","","","","Y","");
}
if ($row['OTSDT2'] !="") {
	$PODT3=DateInputFromCYMD($row['PODT3']);
	Build_Fld_Entry($row['OTSDT2'],"sdt2","dspalph","Date","",$PODT3,"","","","","Y","");
}
if ($row['OTSDT3'] !="") {
	$PODT4=DateInputFromCYMD($row['PODT4']);
	Build_Fld_Entry($row['OTSDT3'],"sdt3","dspalph","Date","",$PODT4,"","","","","Y","");
}
if ($row['OTSCR1'] !="") {Build_DspFld($row['OTSCR1'],$row['POUDF1']);}
if ($row['OTSCR2'] !="") {Build_DspFld($row['OTSCR2'],$row['POUDF2']);}
if ($row['OTSCR3'] !="") {Build_DspFld($row['OTSCR3'],$row['POUDF3']);}
if ($row['OTSCR4'] !="") {Build_DspFld($row['OTSCR4'],$row['POUDF4']);}
if ($row['OTSCR5'] !="") {Build_DspFld($row['OTSCR5'],$row['POUDF5']);}

if ($userDefHdrCnt > 0) {
	Display_PO_UserDefined_Columns($udCol, $purchaseOrderNumber, $orderSequence);
}
print "\n </table>";

require_once 'WildCardPrint.php';
print "\n  </div></div>";
?>
