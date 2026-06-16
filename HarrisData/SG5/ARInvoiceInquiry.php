<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$customerNumber     = $_GET['customerNumber'];
$invoiceSequence    = $_GET['invoiceSequence'];
$glJrnl             = $_GET['glJrnl'];
$glDDSeq            = $_GET['glDDSeq'];
$glDDFile           = $_GET['glDDFile'];
$noMenu             = $_GET['noMenu'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'QuickLink.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "A/R Invoice";
$scriptName     = "ARInvoiceInquiry.php";
$scriptVarBase  = "{$genericVarBase}{$glDDVarBase}&amp;customerNumber=" . urlencode(trim($customerNumber)) . "&amp;invoiceSequence=" . urlencode(trim($invoiceSequence)) . "&amp;glJrnl=" . urlencode(trim($glJrnl)) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDFile=" . urlencode(trim($glDDFile)) . "&amp;noMenu=" . urlencode(trim($noMenu));
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;

if (CustomerUserView($profileHandle, $dataBaseID, $customerNumber, "Y") == "N") {
	require 'UserViewErrorInclude.php';
	exit;
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
print "\n \n <script TYPE=\"text/javascript\">";
if ($noMenu !="Y") require_once 'Menu.js';
require_once 'NewWindowOpen.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
if ($noMenu =="Y") {require $inquiryBanner;}
else               {require_once 'Banner.php';}
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
if ($formatToPrint == "" && $noMenu !="Y") {
	$pageID= "ARINVOICE";
	require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";
// Header ***************************************************
require 'stmtSQLClear.php';
$appendUserView="N";  // Do not append user view security
$appendWildCard="N";  // Do not append wildCardSearch
$stmtSQL   .= " Select IVBLTO,IVLOC ";
if ($HDMCRL>0 && $CRPRMC=="Y") {$stmtSQL   .= "       ,IVCURT,IVCURD ";}
$stmtSQL   .= "       ,coalesce(CMCUST,0) as CMCUST ";
$stmtSQL   .= "       ,coalesce(CMCNA1,' ') as CMCNA1,coalesce(CMCNA2,' ') as CMCNA2 ";
$stmtSQL   .= "       ,coalesce(CMCNA3,' ') as CMCNA3,coalesce(CMCNA4,' ') as CMCNA4 ";
$stmtSQL   .= "       ,coalesce(CMCCTY,' ') as CMCCTY,coalesce(CMST,' ') as CMST ";
$stmtSQL   .= "       ,coalesce(CMZIP,' ')  as CMZIP ";
$stmtSQL   .= "       ,coalesce(LOLOC#,0) as LOLOC ";
$stmtSQL   .= "       ,coalesce(LOLNA1,' ') as LOLNA1,coalesce(LOLNA2,' ') as LOLNA2 ";
$stmtSQL   .= "       ,coalesce(LOLNA3,' ') as LOLNA3,coalesce(LOLNA4,' ') as LOLNA4 ";
$stmtSQL   .= "       ,coalesce(LOLCTY,' ') as LOLCTY,coalesce(LOST,' ') as LOST ";
$stmtSQL   .= "       ,coalesce(LOZIP,' ')  as LOZIP ";
$fileSQL   .= " HDINVC ";
$fileSQL   .= " left join HDCUST on CMCUST=IVBLTO ";
$fileSQL   .= " left join HDLCTN on LOLOC#=IVLOC ";
$selectSQL .= " IVISEQ=$invoiceSequence ";
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

$row = db2_fetch_assoc($sqlResult);
if ($HDMCRL>0 && $CRPRMC=="Y") {
	$IVCURT=$row['IVCURT'];
	$IVCURD=$row['IVCURD'];
}
print "\n <a NAME=\"top\"></a> ";
print "\n <table $contentTable> ";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td> ";
if ($formatToPrint != "Y"){
	print "\n <td class=\"toolbar\"> ";
	if ($backHome!="" && strpos($backHome,".d2w")>0) {print "\n <a href=\"{$homeURL}{$cGIPath}{$backHome}{$altVarBase}{$glDDVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;glJrnl=" . urlencode(trim($glJrnl)) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDFile=" . urlencode(trim($glDDFile)) . "\" title=\"BackHome\">$portalHome</a> ";}
	elseif ($backHome!="")                           {print "\n <a href=\"{$homeURL}{$phpPath}{$backHome}{$genericVarBase}{$glDDVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;glJrnl=" . urlencode(trim($glJrnl)) . "&amp;glDDSeq=" . urlencode(trim($glDDSeq)) . "&amp;glDDFile=" . urlencode(trim($glDDFile)) . "\" title=\"BackHome\">$portalHome</a> ";}
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";

if ($noMenu =="Y") {print $inquiryhrTagAttr;}
else               {print $hrTagAttr;}

if ($formatToPrint != "Y") {
	print "\n <table $contentTable> ";
	print "\n <tr><td><a href=\"#Invoice\">Invoice</a></td></tr> ";
	print "\n <tr><td><a href=\"#PendingPayments\">Pending Payments</a></td></tr> ";
	print "\n <tr><td><a href=\"#Payments\">Payments</a></td></tr> ";
	print "\n <tr><td><a href=\"#distribution\">Distribution</a></td></tr> ";
	print "\n <tr><td>&nbsp;</td></tr> ";
	print "\n </table> ";
}

print "\n <table $contentTable> ";
print "\n <colgroup><col width=\"30%\"><col width=\"10%\"><col width=\"30%\"><col width=\"10%\"> ";

// Customer **************************************************
$custadr[1]= "";
$custadr[2]= "";
$custadr[3]= "";
$custadr[4]= "";
$adrCount=1;

if (trim($row['CMCNA2']) != "") {
	$custadr[$adrCount]=$row['CMCNA2'];
	$adrCount ++;
}
if (trim($row['CMCNA3']) != "") {
	$custadr[$adrCount]=$row['CMCNA3'];
	$adrCount ++;
}
if (trim($row['CMCNA4']) != "") {
	$custadr[$adrCount]=$row['CMCNA4'];
	$adrCount ++;
}

if (trim($row['CMCCTY'])!="" || trim($row['CMST'])!="" || trim($row['CMZIP'])!="") {
	$custadr[$adrCount]=trim($row['CMCCTY']) . ", " . trim($row['CMST']) . " " . trim($row['CMZIP']);
	$adrCount ++;
}

// Location
$locadr[1]="";
$locadr[2]="";
$locadr[3]="";
$locadr[4]="";
$adrCount=1;

if (trim($row['LOLNA2']) != "") {
	$locadr[$adrCount]=$row['LOLNA2'];
	$adrCount ++;
}
if (trim($row['LOLNA3']) != "") {
	$locadr[$adrCount]=$row['LOLNA3'];
	$adrCount ++;
}
if (trim($row['LOLNA4']) != "") {
	$locadr[$adrCount]=$row['LOLNA4'];
	$adrCount ++;
}
if (trim($row['LOLCTY'])!="" || trim($row['LOST'])!="" || trim($row['LOZIP'])!="") {
	$locadr[$adrCount]=trim($row['LOLCTY']) . ", " . trim($row['LOST']) . " " . trim($row['LOZIP']);
	$adrCount ++;
}

print "\n <tr><td class=\"colhdr\">Customer</td> ";
print "\n     <td>&nbsp;</td> ";
print "\n     <td class=\"colhdr\">Location</td> ";
print "\n </tr> ";

print "\n <tr><td class=\"dspalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['CMCUST'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Customer [$row[CMCUST]]\">$row[CMCNA1]</a></td> ";
print "\n     <td>&nbsp;</td> ";
print "\n     <td class=\"dspalph\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['LOLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location [$row[LOLOC]]\">$row[LOLNA1]</a></td> ";
print "\n </tr> ";

print "\n <tr><td class=\"dspalph\">$custadr[1]</td> ";
print "\n     <td>&nbsp;</td> ";
print "\n     <td class=\"dspalph\">$locadr[1]</td> ";
print "\n </tr> ";

print "\n <tr><td class=\"dspalph\">$custadr[2]</td> ";
print "\n     <td>&nbsp;</td> ";
print "\n     <td class=\"dspalph\">$locadr[2]</td> ";
print "\n </tr> ";

print "\n <tr><td class=\"dspalph\">$custadr[3]</td> ";
print "\n     <td>&nbsp;</td> ";
print "\n     <td class=\"dspalph\">$locadr[3]</td> ";
print "\n </tr> ";

print "\n <tr><td class=\"dspalph\">$custadr[4]</td> ";
print "\n     <td>&nbsp;</td> ";
print "\n     <td class=\"dspalph\">$locadr[4]</td> ";
print "\n </tr> ";
print "\n </table> ";


// Invoice ***************************************************
print "\n <a name=\"Invoice\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Invoice</legend> ";
if ($formatToPrint != "Y") {require 'TopOfForm.php';} 

require 'stmtSQLClear.php';
$appendUserView="N";  // Do not append user view security
$appendWildCard="N";  // Do not append wildCardSearch
$stmtSQL   .= " Select IVBLTO,IVLOC,IVCUST,IVAINV,IVIVDT,IVDUED,IVSLSM,IVARPO ";
$stmtSQL   .= "       ,IVORD,IVORDT,IVTRMS,IVSBCD,IVPAYR ";
$stmtSQL   .= "       ,IVCTDT,IVIVCD,IVARAC,IVARSB,IVPER,IVBCH,IVAUDT,IVAUDU ";
$stmtSQL   .= "       ,IVDSCD,IVDSCC,IVPSDT,IVPDFL,IVPCNT,IVORLN,IVPLT,IVMORD ";
$stmtSQL   .= "       ,IVIVAM,IVDSCT,IVIVCT,IVSTAX,IVFRT,IVSPC,IVNPOS,IVDSTK,IVPPOS ";
if ($HDMCRL>0 && $CRPRMC=="Y") {
	$stmtSQL   .= "       ,IVDAMT,IVDVCT,IVDSTX,IVDFRT,IVDSPC,IVONPO,IVDDIS ";
	$stmtSQL   .= "       ,IVCRTE,IVOPER,IVDSTP,IVCURT,IVCURD ";
}
$stmtSQL   .= "       ,coalesce(PYPYNM,' ') as PYPYNM ";
$stmtSQL   .= "       ,coalesce(PSDESC,' ') as PSDESC ";
$stmtSQL   .= "       ,coalesce(CHCHDS,' ') as CHCHDS ";
$stmtSQL   .= "       ,coalesce(PLNAME,' ') as PLNAME ";
$stmtSQL   .= "       ,coalesce(SMSNA1,' ') as SMSNA1 ";
$stmtSQL   .= "       ,coalesce(TMCTDS,' ') as TMCTDS ";
$stmtSQL   .= "       ,coalesce(USDESC,' ') as USDESC ";
$stmtSQL   .= "       ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
if ($HDOERL<=0) {
	$stmtSQL .= ",0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
} else {
	$stmtSQL .= ",(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(IVAINV,IVIVDT,IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where HHLIV#=IVAINV and HHBLTO=IVBLTO) as OEHISTORY " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where IVORD<>0 and HHORD#=IVORD and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
}
if ($HDPDRL<=0) {
	$stmtSQL .= ",0 as MFGORDCOUNT " ;
} else {
	$stmtSQL .= ",(Select Count(*) From HDMOHM Where (OHPLT,OHORD)=(IVPLT,IVMORD)) as MFGORDCOUNT " ;
}
$fileSQL   .= " HDINVC ";
$fileSQL   .= " left join ARPYRH on PYPAYR=IVPAYR ";
$fileSQL   .= " left join ARPYSB on PSSBCD=IVSBCD ";
$fileSQL   .= " left join HDCHRT on (CHACCT,CHSUB)=(IVARAC,IVARSB) ";
$fileSQL   .= " left join HDPLNT on PLPLNT=IVPLT ";
$fileSQL   .= " left join HDSLSM on SMSLSM=IVSLSM ";
$fileSQL   .= " left join HDTRMS on TMCTRM=IVTRMS ";
$fileSQL   .= " left join SYUSER on USUSER=IVAUDU ";
$fileSQL   .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=IVORD and HHLIV#=IVAINV ";
$selectSQL .= " IVISEQ=$invoiceSequence ";
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

if (! $row) {
	print "\n <table $contentTable> ";
	print "\n <tr><td class=\"colalph\">No invoice info found for this Invoice</td></tr> ";
	print "\n </table> ";
} else {
	print "\n <table $contentTable> ";
	Build_DspFld("Bill-To",$row['IVBLTO'],"","N");
	Build_DspFld("Location",$row['IVLOC'],"","N");

	if ($row['IVBLTO']!=$row['IVCUST']) {
		$F_IVCUST=Format_Code($row['IVCUST']);
		Build_DspFld("Ship-To","$row[CMCNA1] $F_IVCUST","","A");
	}

	print "\n <tr><td class=\"dsphdr\">Invoice</td> ";
	if ($row['OEINVCOUNT']>0) {print "\n <td class=\"dspnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['IVAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['IVIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[IVAINV]</a></td> ";}
	else                      {print "\n <td class=\"dspnmbr\">$row[IVAINV]</td> ";}
	print "\n </tr> ";

	$F_IVIVDT=Format_Date($row['IVIVDT'], "D");
	Build_DspFld("Invoice Date",$F_IVIVDT,"","N");

	$F_IVDUED=Format_Date_ISO($row['IVDUED'], "D");
	Build_DspFld("Due Date",$F_IVDUED,"","N");

	$F_IVSLSM=Format_Code($row['IVSLSM']);
	Build_DspFld("Salesman","$row[SMSNA1] $F_IVSLSM","","A");

	Build_DspFld("Reference Number",$row['IVARPO'],"","A");

	if ($row['IVORD']>0) {
		print "\n <tr><td class=\"dsphdr\">Order Number</td> ";
		if     ($row['OESELECT']>0)  {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['IVORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[IVORD]</a></td> ";}
		elseif ($row['OEHISTORY']>0) {print "\n <td class=\"dspnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($customerNumber)) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['IVAINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[IVORD]</a></td> ";}
		else                         {print "\n <td class=\"dspnmbr\">$row[IVORD]</td> ";}
		print "\n </tr> ";

		$F_IVORDT=Format_Date($row['IVORDT'], "D");
		Build_DspFld("Order Date",$F_IVORDT,"","N");

		if ($row['IVORLN']!=0) {Build_DspFld("Order Line",$row['IVORLN'],"","N");}
	}

	if ($row['IVPLT']!=0) {
		$F_IVPLT=Format_Code($row['IVPLT']);
		Build_DspFld("Plant","$row[PLNAME] $F_IVPLT","","A");
	}

	if (trim($row['IVMORD'])!="") {
		print "\n <tr><td class=\"dsphdr\">Mfg Order</td> ";
		if ($row['MFGORDCOUNT']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['IVPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['IVMORD'])) . "\" onclick=\"$drillDownWinVar\" title=\"View Mfg Order\">$row[IVMORD]</a></td> ";}
		else                       {print "\n <td class=\"colalph\">$row[IVMORD]</td> ";}
		print "\n </tr> ";
	}

	$F_IVTRMS=Format_Code($row['IVTRMS']);
	Build_DspFld("Terms","$row[TMCTDS] $F_IVTRMS","","A");

	if ($row['IVCTDT'] !=0) {Build_DspFld("Net Days Till Due",$row['IVCTDT'],"","N");}

	Build_DspFld("Invoice Code",$row['IVIVCD'],"","N");

	$F_ARACCT=Format_Code(Format_Acct($row['IVARAC'],$row['IVARSB'],"N"));
	Build_DspFld("A/R Account","$row[CHCHDS] $F_ARACCT","","A");

	$F_IVPER=PeriodFromCYP($row['IVPER']);
	Build_DspFld("Distribution Period",$F_IVPER,"","N");

	if ($row['IVBCH']!=0) {Build_DspFld("Batch",$row['IVBCH'],"","N");}

	Build_DspFld("Initial Entry Timestamp",$row['IVAUDT'],"","A");

	$F_IVAUDU=Format_Code($row['IVAUDU']);
	Build_DspFld("Initial Entry By User Profile","$row[USDESC] $F_IVAUDU","","A");

	$F_IVDSCD=Format_Date_ISO($row['IVDSCD'], "D");
	Build_DspFld("Discount Date",$F_IVDSCD,"","N");

	$F_IVDSCC=Format_Date_ISO($row['IVDSCC'], "D");
	Build_DspFld("Date Discount Calculated",$F_IVDSCC,"","N");

	print "\n </table> ";

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Amounts</legend> ";
	print "\n <table $contentTable> ";

	if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
		$F_DomHover=Format_Domestic_Hover_Info($IVCURT, $IVCURD, $row['IVDSTP'], $row['IVOPER'], $row['IVCRTE']);
		print "\n         <tr><td>&nbsp;</td> ";
		print "\n             <td class=\"colhdr\">Invoice</td> ";
		print "\n             <td class=\"colhdr\">Domestic</td> ";
		print "\n         </tr> ";
		$amtClass="colnmbr";
	} else {
		$amtClass="dspnmbr";
	}

	$F_IVIVAM=Format_Nbr($row['IVIVAM'],  "2", $amtEditCode, "Y", "", "");
	print "\n <tr><td class=\"dsphdr\">Invoice</td> ";
	print "\n     <td class=\"$amtClass\">$F_IVIVAM</td> ";
	if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
		$F_IVDAMT=Format_Nbr($row['IVDAMT'],  "2", $amtEditCode, "Y", "", "");
		print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_IVDAMT</span></td> ";
	}
	print "\n </tr> ";

	if ($row['IVDSCT']!=0) {
		$F_IVDSCT=Format_Nbr($row['IVDSCT'],  "2", $amtEditCode, "Y", "", "");
		print "\n <tr><td class=\"dsphdr\">Discount</td> ";
		print "\n     <td class=\"$amtClass\">$F_IVDSCT</td> ";
		print "\n </tr> ";
	}

	if ($row['IVSTAX']!=0) {
		$F_IVSTAX=Format_Nbr($row['IVSTAX'],  "2", $amtEditCode, "Y", "", "");
		print "\n <tr><td class=\"dsphdr\">Sales Tax</td> ";
		print "\n     <td class=\"$amtClass\">$F_IVSTAX</td> ";
		if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
			$F_IVDSTX=Format_Nbr($row['IVDSTX'],  "2", $amtEditCode, "Y", "", "");
			print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_IVDSTX</span></td> ";
		}
		print "\n </tr> ";
	}

	if ($row['IVFRT']!=0) {
		$F_IVFRT=Format_Nbr($row['IVFRT'],  "2", $amtEditCode, "Y", "", "");
		print "\n <tr><td class=\"dsphdr\">Freight</td> ";
		print "\n     <td class=\"$amtClass\">$F_IVFRT</td> ";
		if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
			$F_IVDFRT=Format_Nbr($row['IVDFRT'],  "2", $amtEditCode, "Y", "", "");
			print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_IVDFRT</span></td> ";
		}
		print "\n </tr> ";
	}

	if ($row['IVSPC']!=0) {
		$F_IVSPC=Format_Nbr($row['IVSPC'],  "2", $amtEditCode, "Y", "", "");
		print "\n <tr><td class=\"dsphdr\">Special Charge</td> ";
		print "\n     <td class=\"$amtClass\">$F_IVSPC</td> ";
		if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
			$F_IVDSPC=Format_Nbr($row['IVDSPC'],  "2", $amtEditCode, "Y", "", "");
			print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_IVDSPC</span></td> ";
		}
		print "\n </tr> ";
	}

	print "\n </table> ";
	print "\n </fieldset> ";

	if ($row['IVPSDT']!=0 || $row['IVNPOS']!=0 || $row['IVPCNT']>0) {
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n <legend class=\"legendTitle\">Payments</legend> ";
		print "\n <table $contentTable> ";

		if ($row['IVPAYR']>0) {
			$F_IVPAYR=Format_Code($row['IVPAYR']);
			Build_DspFld("Originating Payer","$row[PYPYNM] $F_IVPAYR","","A");
		}

		if (trim($row['IVSBCD'])!="") {
			$F_IVSBCD=Format_Code($row['IVSBCD']);
			Build_DspFld("Originating Payment Code","$row[PSDESC] $F_IVSBCD","","A");
		}

		if ($row['IVPSDT']>0) {
			$F_IVPSDT=Format_Date($row['IVPSDT'], "D");
			Build_DspFld("Last Date Posted",$F_IVPSDT,"","N");
		}

		if ($row['IVPDFL']) {
			$F_IVPDFL=Format_Date_ISO($row['IVPDFL'], "D");
			Build_DspFld("Date Invoice Paid In Full",$F_IVPDFL,"","N");
		}

		if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
			print "\n         <tr><td>&nbsp;</td> ";
			print "\n             <td class=\"colhdr\">Invoice</td> ";
			print "\n             <td class=\"colhdr\">Domestic</td> ";
			print "\n         </tr> ";
		}

		$F_IVNPOS=Format_Nbr($row['IVNPOS'],  "2", $amtEditCode, "Y", "", "");
		print "\n <tr><td class=\"dsphdr\">Net Amount Posted To A/R</td> ";
		print "\n     <td class=\"$amtClass\">$F_IVNPOS</td> ";
		if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
			$F_IVONPO=Format_Nbr($row['IVONPO'],  "2", $amtEditCode, "Y", "", "");
			print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_IVONPO</span></td> ";
		}
		print "\n </tr> ";

		if ($row['IVDSTK']!=0) {
			$F_IVDSTK=Format_Nbr($row['IVDSTK'],  "2", $amtEditCode, "Y", "", "");
			print "\n <tr><td class=\"dsphdr\">Discount Taken</td> ";
			print "\n     <td class=\"$amtClass\">$F_IVDSTK</td> ";
			if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
				$F_IVDDIS=Format_Nbr($row['IVDDIS'],  "2", $amtEditCode, "Y", "", "");
				print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_IVDDIS</span></td> ";
			}
			print "\n </tr> ";
		}

		if ($row['IVPPOS']!=0) {
			$F_IVPPOS=Format_Nbr($row['IVPPOS'],  "2", $amtEditCode, "Y", "", "");
			print "\n <tr><td class=\"dsphdr\">Pending Amount Posted</td> ";
			print "\n     <td class=\"$amtClass\">$F_IVPPOS</td> ";
			print "\n </tr> ";

			Build_DspFld("Pending Payment Count",$row['IVPCNT'],"","N");
		}

		print "\n </table> ";
		print "\n </fieldset> ";
	}
}
print "\n </fieldset> ";


// Pending Payments ************************************************
print "\n <a name=\"PendingPayments\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Pending Payments</legend> ";
if ($formatToPrint != "Y") {require 'TopOfForm.php';}

$uv_BankName ="PEBCHB";
$uv_CustomerName ="@@BLTO";
$uv_CustomerClassName ="CMCCLS";
$uv_RegionName ="CMCRGN";
$uv_BillingLocationName = "@@LOC";
$uv_SalesmanName = "@@SLSM";
require 'UserView.php';
if ($uv_Sql!="") {
	$uv_Sql=str_replace('@@BLTO','Coalesce(IVBLTO,PEBLTO)',$uv_Sql);
	$uv_Sql=str_replace('@@LOC','Coalesce(IVLOC,PELOC)',$uv_Sql);
	$uv_Sql=str_replace('@@SLSM','Coalesce(IVSLSM,PESLSM)',$uv_Sql);
	$uv_Sql="PEUSER='$userProfile' or $uv_Sql";
}

require 'stmtSQLClear.php';
$stmtSQL .= " Select PEBCHN,PEBCHD,PEBCHB,PETYPE,PEPMID,PEAMT,PEDAMT,PESBCD,PEMEMO,PECMNT ";
$stmtSQL .= "       ,Case When PETYPE='P' Then PEID Else 0 End as PEPAYR  ";
$stmtSQL .= "       ,Case When PEPTYP='M' Then PEMCHK Else PECHK End as PECHK ";
$stmtSQL .= "       ,Case When PECMNT<>' ' Then 'Y' Else ' ' End HAS_PECMNT ";
$stmtSQL .= "       ,Coalesce(PSPYCD,' ') as PSPYCD, Coalesce(PSDESC,' ') as PSDESC ";
$stmtSQL .= "       ,Coalesce(BKBKNM,' ') as BKBKNM ";
$stmtSQL .= "       ,Coalesce(PYPYNM,' ') as PYPYNM, Coalesce(PYPYNMU,' ') as PYPYNMU ";
$stmtSQL .= "       ,Coalesce(b.FLDESC,' ') as PEPMID_FLDESC ";
$fileSQL .= " ARPYEN ";
$fileSQL .= " left join ARPYSB on PSSBCD=PESBCD ";
$fileSQL .= " left join HDINVC on IVISEQ=PEISEQ ";
$fileSQL .= " left join HDCUST on CMCUST=Coalesce(IVBLTO,PEBLTO) ";
$fileSQL .= " left join HDBANK on BKBANK=PEBCHB ";
$fileSQL .= " left join ARPYRH on PYPAYR=Case When PETYPE='P' Then PEID Else 0 End ";
$fileSQL .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTID',PEPMID) ";
$selectSQL .= " PEISEQ=$invoiceSequence ";
require 'stmtSQLSelect.php';
if ($orderBy=="") {$orderBy="PECHK, PESBCD";}
$stmtSQL   .= " Order By $orderBy";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable> ";

$startRow = 1;
while ($row = db2_fetch_assoc($sqlResult)){

	if ($startRow==1) {
		print "\n <tr>";
		Format_Column_Header("PEAMT " , "Payment Amount") ;
		Format_Column_Header("PEDAMT" , "Discount") ;
		Format_Column_Header("PSDESCU", "Payment Code") ;
		Format_Column_Header("PEBCHD" , "Batch Date") ;
		Format_Column_Header("PECHK", "Document") ;
		Format_Column_Header("HAS_PECMNT", "Cmt") ;
		Format_Column_Header("PEMEMO", "Memo") ;
		Format_Column_Header("PYPYNMU", "Payer") ;
		Format_Column_Header("BKBKNM" , "Bank") ;
		Format_Column_Header("PEBCHN" , "Batch") ;
		Format_Column_Header("PEPMID" , "Trans Type") ;
		print "\n </tr> ";
	}

	require 'SetRowClass.php';

	$F_PEAMT=Format_Nbr($row['PEAMT'],  "2", $amtEditCode, "Y", "", "");
	$F_PEDAMT=Format_Nbr($row['PEDAMT'],  "2", $amtEditCode, "Y", "", "");
	$F_PEBCHD=Format_Date($row['PEBCHD'], "D");

	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colnmbr\">$F_PEAMT</td> ";
	print "\n     <td class=\"colnmbr\">$F_PEDAMT</td> ";
	print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[PESBCD] [$row[PSPYCD]]\">$row[PSDESC]</span></td>";
	print "\n     <td class=\"colalph\">$F_PEBCHD</td> ";
	print "\n     <td class=\"colnmbr\">$row[PECHK]</td> ";
	print "\n     <td class=\"colcode\" $helpCursor><span title=\"$row[PECMNT]\">$row[HAS_PECMNT]</span></td> ";
	print "\n     <td class=\"colalph\">$row[PEMEMO]</td>";
	print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[PEPAYR]\">$row[PYPYNM]</span></td> ";
	print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[PEBCHB]\">$row[BKBKNM]</span></td>";
	print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashBatchSelect.php{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['PEBCHN'])) . "&amp;fromBatchDate=" . urlencode(trim($row['PEBCHD'])) . "&amp;fromBatchBank=" . urlencode(trim($row['PEBCHB'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Batch\">$row[PEBCHN]</a></td> ";
	print "\n     <td class=\"colcode\" $helpCursor><span title=\"" . trim($row[PEPMID_FLDESC]) . "\">$row[PEPMID]</span></td>";
	print "\n </tr> ";

	$startRow ++;
	$rowCount ++;
}
if ($startRow==1) {
	print "\n <tr><td class=\"colalph\">No Pending Payments Found For This Invoice</td></tr> ";
}

print "\n </table> ";
print "\n </fieldset> ";


// Payment History **************************************************
print "\n <a name=\"Payments\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Payments</legend> ";
if ($formatToPrint != "Y") {require 'TopOfForm.php';}

$uv_BankName    = "YPBANK";
$uv_CustomerName= "YPBLTO";
$uv_CustomerClassName ="CMCCLS";
$uv_RegionName ="CMCRGN";
$uv_BillingLocationName = "IVLOC";
$uv_SalesmanName = "IVSLSM";
require 'userview.php';
require 'stmtSQLClear.php';
$stmtSQL .= " SELECT YPBCH,YPBANK,YPBDAT,YPPYCD,YPSBCD,YPGDED,YPCHK,YPAMT,YPBLTO,YPPAYR,YPMEMO,YPPMID ";
if ($HDMCRL>0 && $CRPRMC=="Y") {$stmtSQL   .= "       ,YPCAMT,YPCRTE,YPOPER,YPDSTP,YPCURT,YPCURD ";}
$stmtSQL .= "       ,YPCMNT, Case When YPCMNT<>' ' Then 'Y' Else ' ' End HAS_YPCMNT ";
$stmtSQL .= "       ,coalesce(BKBKNM,' ') as BKBKNM ";
$stmtSQL .= "       ,coalesce(PSDESC,' ') as PSDESC ";
$stmtSQL .= "       ,Coalesce(PYPYNM,' ') as PYPYNM, Coalesce(PYPYNMU,' ') as PYPYNMU ";
$stmtSQL .= "       ,coalesce(FLDESC,' ') as FLDESC ";
$fileSQL .=" ARYPTD ";
$fileSQL .=" left join HDINVC on IVISEQ=YPISEQ ";
$fileSQL .=" left join HDCUST on CMCUST=YPBLTO ";
$fileSQL .=" left join HDBANK on BKBANK=YPBANK ";
$fileSQL .=" left join ARPYSB on PSSBCD=YPSBCD ";
$fileSQL .= " left join ARPYRH on PYPAYR=YPPAYR ";
$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('ARPMTID',YPPMID) ";
$selectSQL .= " (YPISEQ,YPBLTO)=($invoiceSequence,$customerNumber) ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By YPISEQ,YPPSEQ ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable> ";

$startRow = 1;
while ($row = db2_fetch_assoc($sqlResult)){
	if ($startRow==1) {
		print "\n <tr> ";
		Format_Column_Header("YPAMT" , "Amount Paid") ;
		Format_Column_Header("PSDESC", "Payment Code") ;
		Format_Column_Header("YPGDED" , "General Deduction") ;
		Format_Column_Header("YPPMID" , "Transaction Type") ;
		Format_Column_Header("YPBDAT", "Date Paid") ;
		Format_Column_Header("YPCHK ", "Document") ;
		Format_Column_Header("HAS_YPCMNT" , "Cmt") ;
		Format_Column_Header("YPMEMO" , "Memo") ;
		Format_Column_Header("PYPYNMU", "Payer") ;
		Format_Column_Header("BKBKNM", "Bank") ;
		Format_Column_Header("YPBCH" , "Batch") ;
		if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {print "\n     <th class=\"colhdr\">Domestic Amount Paid</th> ";}
		print "\n </tr> ";
	}

	require 'SetRowClass.php';

	$F_YPAMT=Format_Nbr($row['YPAMT'],  "2", $amtEditCode, "Y", "", "");
	$F_YPBDAT=Format_Date($row['YPBDAT'], "D");
	if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
		$F_YPCAMT=Format_Nbr($row['YPCAMT'],  "2", $amtEditCode, "Y", "", "");
		$F_DomHover=Format_Domestic_Hover_Info($row['YPCURT'], $row['YPCURD'], $row['YPDSTP'], $row['YPOPER'], $row['YPCRTE']);
	}

	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colnmbr\">$F_YPAMT</td> ";
	print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[YPSBCD] [$row[YPPYCD]]\">$row[PSDESC]</span></td>";
	print "\n     <td class=\"colcode\">$row[YPGDED]</td> ";
	print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[YPPMID]\">$row[FLDESC]</span></td>";
	print "\n     <td class=\"coldate\">$F_YPBDAT</td> ";
	if (trim($row['YPCHK']) != "" && $row['YPBANK']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ARCheckInquiry.php{$scriptVarBase}&amp;tag=REPORT&amp;fromDocument=" . urlencode(trim($row['YPCHK'])) . "&amp;fromDatePaid=" . urlencode(trim($row['YPBDAT'])) . "&amp;fromBank=" . urlencode(trim($row['YPBANK'])) . "&amp;fromPayer=" . urlencode(trim($row['YPPAYR'])) . "&amp;fromCustomer=" . urlencode(trim($row['YPBLTO'])) . "\" onclick=\"$drillDownWinVar\" title=\"A/R Document Quickview\">$row[YPCHK]</a></td> ";}
	else                                               {print "\n <td class=\"colnmbr\">$row[YPCHK]</td> ";}
	print "\n     <td class=\"colcode\" $helpCursor><span title=\"$row[YPCMNT]\">$row[HAS_YPCMNT]</span></td> ";
	print "\n     <td class=\"colnmbr\">$row[YPMEMO]</td> ";
	print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[YPPAYR]\">$row[PYPYNM]</span></td> ";
	print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[YPBANK]\">$row[BKBKNM]</span></td>";
	print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashBatchSelect.php{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['YPBCH'])) . "&amp;fromBatchDate=" . urlencode(trim($row['YPBDAT'])) . "&amp;fromBatchBank=" . urlencode(trim($row['YPBANK'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Batch\">$row[YPBCH]</a></td> ";
	if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_YPCAMT</span></td> ";}
	print "\n </tr> ";

	$startRow ++;
}

if ($startRow==1) {
	print "\n <tr><td class=\"colalph\">No Payments Found For This Invoice</td></tr> ";
}

print "\n </table> ";
print "\n </fieldset> ";


// Distribution *****************************************************
print "\n <a name=\"distribution\"></a> ";
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Distribution</legend> ";
if ($formatToPrint != "Y") {require 'TopOfForm.php';}

require 'stmtSQLClear.php';
$appendUserView="N";  // Do not append user view security
$appendWildCard="N";  // Do not append wildCardSearch
$stmtSQL .= " Select IDCO,IDFAC,IDACCT,IDSUB,IDARTY ";
$stmtSQL .= "       ,Case When IDDRCR='D' Then IDAMT Else -IDAMT End as IDAMT ";
if ($HDMCRL>0 && $CRPRMC=="Y") {
	$stmtSQL   .= "       ,Case When IDDRCR='D' Then IDDAMT Else -IDDAMT End as IDDAMT ";
	$stmtSQL   .= "       ,IDCRTE,IDOPER,IDDSTP,IDCURT,IDCURD ";
}
$stmtSQL .= "       ,coalesce(CFCFNM,' ') as CFCFNM ";
$stmtSQL .= "       ,coalesce(CHCHDS,' ') as CHCHDS ";
$stmtSQL .= "       ,coalesce(FLDESC,' ') as FLDESC ";
$fileSQL .= " HDARDS ";
$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(IDCO,IDFAC) ";
$fileSQL .= " left join HDCHRT on (CHACCT,CHSUB)=(IDACCT,IDSUB) ";
$fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('ARDISTTYPE',IDARTY) ";
$selectSQL .= " IDISEQ=$invoiceSequence  ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By IDCO,IDFAC,IDACCT,IDSUB,IDARTY ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <table $contentTable> ";

$startRow = 1;
while ($row = db2_fetch_assoc($sqlResult)){
	if ($startRow==1) {
		print "\n <tr> ";
		Format_Column_Header("IDAMT", "Amount") ;
		Format_Column_Header("IDARTY", "Type") ;
		Format_Column_Header("IDCO", "Co/Fac") ;
		Format_Column_Header("CFCFNM", "Name") ;
		Format_Column_Header("IDACCT", "Account") ;
		Format_Column_Header("CHCHDS", "Description") ;
		if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {print "\n     <th class=\"colhdr\">Domestic Amount</th> ";}
		print "\n </tr> ";
	}

	require 'SetRowClass.php';
	$F_CoFac=Format_CoFac($row['IDCO'], $row['IDFAC'],"N");
	$F_AcctSub=Format_Acct($row['IDACCT'], $row['IDSUB'],"N");
	$F_IDAMT=Format_Nbr($row['IDAMT'],  "2", $amtEditCode, "Y", "", "");
	if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {
		$F_IDDAMT=Format_Nbr($row['IDDAMT'],  "2", $amtEditCode, "Y", "", "");
		$F_DomHover=Format_Domestic_Hover_Info($row['IDCURT'], $row['IDCURD'], $row['IDDSTP'], $row['IDOPER'], $row['IDCRTE']);
	}

	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colnmbr\">$F_IDAMT</td> ";
	print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[IDARTY]\">$row[FLDESC]</span></td>";
	print "\n     <td class=\"colnmbr\">$F_CoFac</td> ";
	print "\n     <td class=\"colalph\">$row[CFCFNM]</td> ";
	print "\n     <td class=\"colnmbr\">$F_AcctSub</td> ";
	print "\n     <td class=\"colalph\">$row[CHCHDS]</td> ";
	if ($HDMCRL>0 && $CRPRMC=="Y" && $IVCURT!=$IVCURD) {print "\n     <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_IDDAMT</span></td> ";}
	print "\n </tr> ";

	$startRow ++;
}
if ($startRow==1) {
	print "\n <tr><td class=\"colalph\">No Distribution Found For This Invoice</td></tr>";
}

print "\n </table> ";
print "\n </fieldset> ";

if ($noMenu =="Y") {print $inquiryhrTagAttr;}
else               {print $hrTagAttr;}

require_once 'Copyright.php';
print "\n </td> </tr> </table>";
if ($noMenu =="Y") {require $inquiryTrailer;}
else               {require_once 'Trailer.php';}
print "\n </body> \n </html>";

?>
