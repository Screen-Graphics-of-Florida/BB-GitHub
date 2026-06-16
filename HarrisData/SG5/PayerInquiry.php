<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromType        = $_GET['fromType'];
$fromID          = $_GET['fromID'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'GenericDirectCallVariables.php';
require_once 'QuickLink.php';
require_once 'VarBase.php';

$page_title       = "Payer Inquiry";
$scriptName       = "PayerInquiry.php";
$scriptVarBase    = "{$genericVarBase}&amp;fromType=" . urlencode($fromType) . "&amp;fromID=" . urlencode($fromID);
$nextPrevVar      = "{$scriptVarBase}";
$baseURL          = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows       = $dspMaxRowsDft;
$prtMaxRows       = $prtMaxRowsDft;
$displayCloseIcon = "Y";
$medIcon          = "Y";

if ($fromType=="C") {$userPass=CustomerUserView($profileHandle, $dataBaseID, $fromID, "Y");}
else                {$userPass=PayerUserView($profileHandle, $fromID, "Y");}
if ($userPass == "N") {
	require_once 'UserViewErrorInclude.php';
	exit;
}

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n </head>";

print "\n <body $bodyTagAttr onLoad=\"window.focus()\"> ";
require ($inquiryBanner);

require_once 'PageTitleInclude.php';

// *****************************************************************************
// Heading
// *****************************************************************************
print "\n <table $contentTable> ";
if ($fromType=="C") {
	$CMCNA1=RetValue("CMCUST=$fromID", "HDCUST", "CMCNA1");
	Format_Header("Customer", $CMCNA1, $fromID);
} else {
	$PYPYNM=RetValue("PYPAYR=$fromID", "ARPYRH", "PYPYNM");
	Format_Header("Payer", $PYPYNM, $fromID);
}
print "\n </table> ";

print $inquiryhrTagAttr;

// *****************************************************************************
// Show Demographics of Customer or Payer
// *****************************************************************************
if ($fromType=="C") {
	// Customer
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select CMCNA1 as PYPYNM,CMCNA2 as PYADR1,CMCNA3 as PYADR2,CMCNA4 as PYADR3 ";
	$stmtSQL .= "       ,CMCCTY as PYCITY,CMST   as PYST  ,CMZIP  as PYZIP ,CMCTRY as PYCTRY ";
	$stmtSQL .= "       ,CMPHON as PYPHON,CMFAX  as PYFAX ,CMSBCD as PYSBCD,CMCLCT as PYCTOR ";
	$stmtSQL .= "       ,CMCRCT as PYCONT ";
	$stmtSQL .= "       ,Coalesce(PSDESC,' ') as PSDESC ";
	$stmtSQL .= "       ,Coalesce(USDESC,' ') as USDESC ";
	$fileSQL .= " HDCUST ";
	$fileSQL .= " left join ARPYSB on PSSBCD=CMSBCD ";
	$fileSQL .= " left join SYUSER on USUSER=CMCLCT ";
	$selectSQL .= " CMCUST = $fromID ";
} else {
	// Payer
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select PYPYNM,PYADR1,PYADR2,PYADR3,PYCITY,PYST,PYZIP,PYCTRY,PYPHON,PYFAX,PYSBCD,PYCTOR,PYCONT,PYCPHN,PYEMAL ";
	$stmtSQL .= "       ,Coalesce(PSDESC,' ') as PSDESC ";
	$stmtSQL .= "       ,Coalesce(USDESC,' ') as USDESC ";
	$fileSQL .= " ARPYRH ";
	$fileSQL .= " left join ARPYSB on PSSBCD=PYSBCD ";
	$fileSQL .= " left join SYUSER on USUSER=PYCTOR ";
	$selectSQL .= "PYPAYR=$fromID ";
}
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Demographics</legend> ";
print "\n <table $contentTable> ";

$row = db2_fetch_assoc($sqlResult);

Build_DspFld("Address",$row['PYADR1'],"","A");
if (trim($row['PYADR2']) != "") {Build_DspFld("",$row['PYADR2'],"","A");}
if (trim($row['PYADR3']) != "") {Build_DspFld("",$row['PYADR3'],"","A");}
if (trim($row['PYCITY']) != "" || trim($row['PYST']) != "" || trim($row['PYZIP']) != "") {
	Build_DspFld("","$row[PYCITY], $row[PYST] $row[PYZIP]","","A");
}
if (trim($row['PYCTRY']) != "" && $row['PYCTRY'] != $HDCTCD) {
	$fieldDesc=RetValue("CNCTCD='$row[PYCTRY]'", "HDCTRY", "CNCDES");
	$F_PYCTRY=Format_Code($row['PYCTRY']);
	Build_DspFld("Country",$fieldDesc,$F_PYCTRY,"A");
}

$F_PYPHON=EditPhoneNumber($row['PYPHON']);
Build_DspFld("Phone",$F_PYPHON,"","A");

$F_PYFAX=EditPhoneNumber($row['PYFAX']);
Build_DspFld("Fax",$F_PYFAX,"","A");

$F_PYSBCD=Format_Code($row['PYSBCD']);
Build_DspFld("Preferred Payment Method",$row['PSDESC'],$F_PYSBCD,"A");

$F_PYCTOR=Format_Code($row['PYCTOR']);
Build_DspFld("Collector",$row['USDESC'],$F_PYCTOR,"A");

print "\n </table> ";

// *****************************************************************************
// Demographics - Contact
// *****************************************************************************
print "\n <fieldset class=\"legendBody\"> ";
print "\n <legend class=\"legendTitle\">Contact</legend> ";
print "\n <table $contentTable> ";

Build_DspFld("Name",$row['PYCONT'],"","A");

if ($fromType=="P") {
	$F_PYCPHN=EditPhoneNumber($row['PYCPHN']);
	Build_DspFld("Phone",$F_PYCPHN,"","A");

	Build_DspFld("E-mail",$row['PYEMAL'],"","A");
}
print "\n </table> ";
print "\n </fieldset> ";

// *****************************************************************************
// Demographics - User Defined
// *****************************************************************************
if ($fromType=="P") {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select UFFLDN,UFDESC,UFTYPE,UFSIZE,UFDECM,UFVALU,UFBOXS,UFREQF,UFVLDV,UFFFMT, ";
	$stmtSQL .= " PUFLDD,Coalesce(PUFLDR,0) as PUFLDR,Coalesce(PUFLDV,' ') as PUFLDV ";
	$fileSQL .= " SYUDFM ";
	$fileSQL .= " left join ARPYRU on (PUFLDN,PUPAYR)=(UFFLDN,$fromID) ";
	$selectSQL .= " (UFFILN,UFEVNT)=('ARPYRU',' ') ";
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By UFFSEQ ";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">User-Defined</legend> ";
	print "\n <table $contentTable> ";

	$rowCount = 0;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($rowCount >= $dspMaxRows) {break;}

		require  'SetRowClass.php';

		print "\n <tr><td class=\"dsphdr\">$row[UFDESC]</td> ";
		if ($row['UFTYPE'] == "A") {
			if       ($row['UFFFMT'] == "U") {print "\n <td class=\"dspalph\"><a href=\"$row[PUFLDV]\" target=_blank>$row[PUFLDV]</a></td> ";
			} elseif ($row['UFFFMT'] == "E") {print "\n <td class=\"dspalph\"><a href=\"mailto:$row[PUFLDV]\" target=_blank title=\"Click here to send e-mail\">$row[PUFLDV]</a></td> ";
			} else                           {print "\n <td class=\"dspalph\">$row[PUFLDV] ";}
		} elseif ($row['UFTYPE'] == "C") {
			$printRows=substr_count(bin2hex($row['PUFLDV']),"0d0a");
			print "\n <td><textarea name=\"$row[UFFLDN]\" readonly ROWS=\"$printRows\" COLS=\"60\" WRAP=\"hard\">$row[PUFLDV]</textarea> ";
		} elseif ($row['UFTYPE'] == "N") {
			$row['PUFLDR']=number_format($row['PUFLDR'],$row['UFDECM'],".","");
			if   ($row['UFFFMT'] == "P") {$row['PUFLDR']=EditPhoneNumber($row['PUFLDR']);}
			print "\n <td class=\"dspnmbr\">$row[PUFLDR] ";

		} elseif ($row['UFTYPE'] == "D") {
			$row['PUFLDD']=Format_Date(Date_FromISO_ToCYMD($row['PUFLDD']), "H");
			print "\n <td class=\"dspnmbr\">$row[PUFLDD] ";
		}
		print "\n </td></tr> ";

		$startRow ++;
		$rowCount ++;
	}

	print "\n </table> ";
	print "\n </fieldset> ";
}

print "\n </fieldset> ";

// *****************************************************************************
// Show Payers of Customer or Customers of Payer
// *****************************************************************************
if ($fromType=="C") {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select PCPAYR as PCCUST, ";
	$stmtSQL .= " Coalesce(PYPYNM,' ') as CMCNA1, Coalesce(PYPYNMU,' ') as CMCNA1U, ";
	$stmtSQL .= " Coalesce(PYADR1,' ') as CMCNA2, ";
	$stmtSQL .= " Coalesce(PYCITY,' ') as CMCCTY, Coalesce(PYST  ,' ') as CMST  , ";
	$stmtSQL .= " Coalesce(PYZIP  ,' ') as CMZIP  , Coalesce(PYPHON,0) as CMPHON, ";
	$stmtSQL .= " Coalesce((Select Max(CIIBCH) from HDCUSI Where (CITYPE,CIID)=('P',a.PCPAYR)),0) as CIIBCH,  ";
	$stmtSQL .= " Coalesce((Select Max(CIIUSR) from HDCUSI Where (CITYPE,CIID)=('P',a.PCPAYR)),' ') as CIIUSR, ";
	$stmtSQL .= " Coalesce((Select Max(USDESC) from HDCUSI inner join SYUSER on USUSER=CIIUSR Where (CITYPE,CIID)=('P',a.PCPAYR)),' ') as USDESC ";
	$fileSQL .= " ARPYRC a ";
	$fileSQL .= " inner join ARPYRH on PYPAYR=PCPAYR ";
	$selectSQL .= " PCCUST = $fromID ";
} else {
	require 'stmtSQLClear.php';
	$stmtSQL .= " Select PCCUST, ";
	$stmtSQL .= " Coalesce(CMCNA1,' ') as CMCNA1, Coalesce(CMCNA1U,' ') as CMCNA1U, ";
	$stmtSQL .= " Coalesce(CMCNA2,' ') as CMCNA2, ";
	$stmtSQL .= " Coalesce(CMCCTY,' ') as CMCCTY, Coalesce(CMST  ,' ') as CMST  , ";
	$stmtSQL .= " Coalesce(CMZIP ,' ') as CMZIP , Coalesce(CMPHON,0) as CMPHON, ";
	$stmtSQL .= " Coalesce(CIIBCH,0) as CIIBCH, Coalesce(CIIUSR,' ') as CIIUSR, ";
	$stmtSQL .= " Coalesce(USDESC,' ') as USDESC, ";
	$stmtSQL .= " Coalesce(ARCURT,' ') as ARCURT,Coalesce(ARCARB,0) as ARCARB ";
	$fileSQL .= " ARPYRC ";
	$fileSQL .= " inner join HDCUST on CMCUST=PCCUST ";
	$fileSQL .= " left join HDCUSI on CICUST=PCCUST ";
	$fileSQL .= " left join SYUSER on USUSER=CIIUSR ";
	$fileSQL .= " left join HDCARB on (ARCTYP,ARCUST)=('I',PCCUST) ";
	if ($HDMCRL<=0 || $CRPRMC!="Y") {$fileSQL .= " and ARCURT=' '"; }
	$selectSQL .= " PCPAYR = $fromID ";
}
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By CMCNA1U";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

print "\n <fieldset class=\"legendBody\"> ";
if ($fromType=="C") {print "\n <legend class=\"legendTitle\">Payer</legend> ";}
else                {print "\n <legend class=\"legendTitle\">Customer</legend> ";}
print "\n <table $contentTable> ";
print "\n <tr> ";
if ($fromType=="C") {print "\n <th class=\"colhdr\">Payer</th> ";}
else                {print "\n <th class=\"colhdr\">Customer</th> ";}
print "\n <th class=\"colhdr\">Name</th> ";
print "\n <th class=\"colhdr\">Address</th> ";
print "\n <th class=\"colhdr\">City</th> ";
print "\n <th class=\"colhdr\">State</th> ";
print "\n <th class=\"colhdr\">Zip</th> ";
print "\n <th class=\"colhdr\">Phone</th> ";
print "\n <th class=\"colhdr\">In Use By Batch</th> ";
print "\n <th class=\"colhdr\">In Use By User</th> ";
if ($fromType=="P") {
	if ($HDMCRL>0 || $CRPRMC=="Y") {
		print "\n <th class=\"colhdr\">Currency</th> ";
	}
	print "\n <th class=\"colhdr\">Open A/R</th> ";
}
print "\n </tr> ";

$rowCount = 0;
$startRow = 1;
while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	if ($rowCount >= $dspMaxRows) {break;}

	require  'SetRowClass.php';

	$row['CMPHON']=EditPhoneNumber($row['CMPHON']);
	print "\n <tr class=\"$rowClass\"> ";
	print "\n     <td class=\"colnmbr\">$row[PCCUST]</td> ";
	print "\n     <td class=\"colalph\">$row[CMCNA1]</td> ";
	print "\n     <td class=\"colalph\">$row[CMCNA2]</td> ";
	print "\n     <td class=\"colalph\">$row[CMCCTY]</td> ";
	print "\n     <td class=\"colcode\">$row[CMST]</td> ";
	print "\n     <td class=\"colalph\">$row[CMZIP]</td> ";
	print "\n     <td class=\"colalph\">$row[CMPHON]</td> ";
	print "\n <td class=\"colnmbr\">$row[CIIBCH]</td> ";
	print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[CIIUSR]\">$row[USDESC]</span></td>";
	if ($fromType=="P") {
		if ($HDMCRL>0 && $CRPRMC=="Y") {
			print "\n <td class=\"colalph\">$row[ARCURT]</td> ";
		}
		print "\n <td class=\"colnmbr\">" . number_format($row['ARCARB'],2). "</td> ";
	}
	print "\n </tr> ";

	$startRow ++;
	$rowCount ++;
}
print "\n </table> ";
print "\n </fieldset> ";

print $inquiryhrTagAttr;
require_once 'Copyright.php';

require ($inquiryTrailer);
print "\n </body></html> ";

?>
