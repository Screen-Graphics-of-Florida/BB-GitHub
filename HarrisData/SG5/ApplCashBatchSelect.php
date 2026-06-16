<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromBatchNumber    = $_GET['fromBatchNumber'];
$fromBatchDate      = $_GET['fromBatchDate'];
$fromBatchBank      = $_GET['fromBatchBank'];
$noMenu             = $_GET['noMenu'];
$WFReview           = $_GET['WFReview'];
$wfInstance         = $_GET['wfInstance'];
$wfInstanceDate     = $_GET['wfInstanceDate'];
$wfWorkItem         = $_GET['wfWorkItem'];
$wfWorkItemSequence = $_GET['wfWorkItemSequence'];
$wfParticipantId    = $_GET['wfParticipantId'];
$backHome           = $_GET['backHome'];

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

$CRPERN=RetValue("RRN(ARCTRL)=1", "ARCTRL", "CRDPER");
$ARPdBegDate=RetValue("PDPER#=$CRPERN", "HDPBED", "PDBDAT");

$page_title     = "Application of Cash: Batch";
$scriptName     = "ApplCashBatchSelect.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromBatchNumber=" . urlencode(trim($fromBatchNumber)) . "&amp;fromBatchDate=" . urlencode(trim($fromBatchDate)) . "&amp;fromBatchBank=" . urlencode(trim($fromBatchBank)) . "&amp;noMenu=" . urlencode(trim($noMenu)) . "&amp;fromScript=" . urlencode(trim($fromScript)) . "&amp;backHome=" . urlencode(trim($backHome)) . "&amp;WFReview=" . urlencode(trim($WFReview)) . "&amp;wfInstance=" . urlencode(trim($wfInstance)) . "&amp;wfInstanceDate=" . urlencode(trim($wfInstanceDate)) . "&amp;wfWorkItem=" . urlencode(trim($wfWorkItem)) . "&amp;wfWorkItemSequence=" . urlencode(trim($wfWorkItemSequence)) . "&amp;wfParticipantId=" . urlencode(trim($wfParticipantId));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$cancelWFURL    = "{$baseURL}&amp;tag=CancelWF";
$programName    = "HARABH_E";
$quickLinkByUser= "Y";
$attachFolder   = "ApplCashBatch";

require_once ($docType);
print "\n <html> <head> ";
$F_fromBatchDate=Format_Date($fromBatchDate, "D");
$title="$fromBatchNumber $F_fromBatchDate $fromBatchBank";
require_once ($headInclude);

print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> ";

require_once ($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
if ($noMenu =="Y") {require $inquiryBanner;}
else               {require_once 'Banner.php';}
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
if ($formatToPrint == "" && $noMenu !="Y") {
	$pageID="APPLCASHBATCHSELECT";
	require_once 'MenuDisplay.php';
}
print "\n <td class=\"content\">";

require_once 'QuickLinkTable.php';                            // QuickLink Table
require_once 'QuickLinkByUser.php';                           // QuickLink By User

// *****************************************************************************
// Batch Heading
// *****************************************************************************
$uv_BankName= "BMBCHB";
require 'userview.php';

require 'stmtSQLClear.php';
$appendWildCard="N";  // Do not append wildCardSearch
$stmtSQL   .= " Select  BMBCHN, BMBCHD, BMBCHB, BMBCHS, BMBCHT, ";
$stmtSQL   .= " BMDEPA, BMDEPE, BMDEPP, BMDEPD,  ";
$stmtSQL   .= " BMADJT, BMADJE, BMADJP,  ";
$stmtSQL   .= " BMPMTS, BMPMTE, BMINST, BMINDT, ";
$stmtSQL   .= " coalesce(BKBKNM,' ') as BKBKNM, ";
$stmtSQL   .= " coalesce(c.FLDESC,' ') as FLDESC_BMBCHS ";
$fileSQL   .= " ARPBCH ";
$fileSQL   .= " left join HDBANK on BKBANK=BMBCHB ";
$fileSQL   .= " left join SYFLAG c on (c.FLTYPE,c.FLVALU)=('ARBCHSTAT',BMBCHS) ";
$selectSQL .= " (BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);
if (! $row) {require 'UserViewErrorInclude.php'; Exit;}

// Program Option Security
$harabh_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01=$harabh_OPT['sec_01'];
$sec_02=$harabh_OPT['sec_02'];
$sec_03=$harabh_OPT['sec_03'];
$sec_04="N";

print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

$attachVarKey=trim($fromBatchNumber) . "_" . trim($fromBatchDate) . "_" . trim($fromBatchBank) ;
$attachForDesc="";
$attachPrg1= "ARPBCH Where (PEBCHN,PEBCHD,PEBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
if ($formatToPrint != "Y") {
	$maintainVar="{$scriptVarBase}&amp;fromScript=";
	print "\n <td class=\"toolbar\">";
	if ($backHome != $scriptName && $backHome != "" && $backHome != "@@backHome") {print "\n <a href=\"{$homeURL}{$phpPath}{$backHome}{$scriptVarBase}\" title=\"Back Home\">$portalHome</a> ";}
	require_once 'AttachmentInclude.php';
	// Add icon
	if ($sec_01=="Y")                 {print "\n <a href=\"{$homeURL}{$phpPath}ApplCashBatchMaintain.php{$genericVarBase}&amp;fromScript=ApplCashBatch.php&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a> "; }
	// Change icon
	if (($harabh_OPT['sec_02']=="Y" || $harabh_OPT['sec_03']=="Y") && trim($row['BMPMTS'])=="" && $row['BMBCHD']>=$ARPdBegDate && ($row['BMINST']==0 || $row['BMINST']==$wfInstance && $row['BMINDT']==$wfInstanceDate)){
		print "\n <a href=\"{$homeURL}{$phpPath}ApplCashBatchMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageLrg</a> ";
	}
	// Delete icon
	$ARYPTDRecCnt =RetValue("(YPBCH ,YPBDAT,YPBANK)=($row[BMBCHN],$row[BMBCHD],$row[BMBCHB])", "ARYPTD", "Count(*)");
	if ($harabh_OPT['sec_03']=="Y" && trim($row['BMPMTS'])=="" && !$ARYPTDRecCnt && (trim($row['BMPMTE']) == "" || $row['BMPMTE'] == "A") && $row['BMBCHT']!="D" && $row['BMINST']==0) {
		$confirmDesc = Format_Confirm_Desc("$fromBatchNumber $F_fromBatchDate $fromBatchBank", "", "", "", "", "");
		print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}ApplCashBatchMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageLrg</a> ";
	}

	require_once 'FormatToPrint.php';
	require_once 'HelpPage.php';
	print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";
require_once 'ConfMessageDisplay.php';

if ($noMenu =="Y") {print $inquiryhrTagAttr;}
else               {print $hrTagAttr;}

print "\n <table $contentTable>";
$F_BMBCHD=Format_Code(Format_Date($row['BMBCHD'], "D"));
Build_DspFld("Batch","$row[BMBCHN] $F_BMBCHD","","A");
$F_BMBCHB=Format_Code($row['BMBCHB']);
Build_DspFld("Bank","$row[BKBKNM] $F_BMBCHB","","A");
$F_BMBCHS=Format_Code($row['BMBCHS']);
Build_DspFld("Batch Status","$row[FLDESC_BMBCHS] $F_BMBCHS","","A");
print "\n </table> ";

print "\n <table $contentTable>";
print "\n <tr><td class=\"dsphdr\">&nbsp;</td> ";
print "\n     <td class=\"colhdr\">Total</td> ";
print "\n     <td class=\"colhdr\">Posted</td> ";
print "\n     <td class=\"colhdr\">Pending</td> ";
print "\n     <td class=\"colhdr\">Variance</td> ";
print "\n </tr> ";

$result=$row['BMDEPA'] - ($row['BMDEPP'] + $row['BMDEPE']);
print "\n <tr><td class=\"dsphdr\">Deposit</td> ";
print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMDEPA'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMDEPP'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMDEPE'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $result, '2', $amtEditCode, 'Y', '', '') . "</td> ";
print "\n </tr> ";

$result=$row['BMADJT'] - ($row['BMADJP'] + $row['BMADJE']);
print "\n <tr><td class=\"dsphdr\">Other</td> ";
print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMADJT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMADJP'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['BMADJE'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $result, '2', $amtEditCode, 'Y', '', '') . "</td> ";
print "\n </tr> ";

print "\n </table> ";

if ($noMenu =="Y") {print $inquiryhrTagAttr;}
else               {print $hrTagAttr;}
require_once 'QuickLinkDisplay.php';
// *****************************************************************************
$x=1;
foreach ($quicklinkSeqTable as $quickRow) {
	if ($x <= $quicklinkCount){
		require 'QuickLinkBegLoop.php';                          // Quicklink Begin
		if ($qLinkPos !== false) {

			// *****************************************************************************
			// Demographics
			// *****************************************************************************
			if ($quicklinkRef == "demographics") {
				require 'QuickLinkClear.php';
				require 'stmtSQLClear.php';
				$appendUserView="N";  // Do not append user view security
				$appendWildCard="N";  // Do not append wildCardSearch
				$stmtSQL   .= " Select  BMBCHT, BMPMTE, BMINST, BMINDT, BMWFPR, BMCHKT, BMTRNS, ";
				$stmtSQL   .= " BMAUDT, BMAUDU, BMTSTP, BMTSUS, ";
				$stmtSQL   .= " coalesce(PRDESC,' ') as PRDESC, ";
				$stmtSQL   .= " coalesce(a.FLDESC,' ') as FLDESC_BMBCHT, ";
				$stmtSQL   .= " coalesce(b.FLDESC,' ') as FLDESC_BMPMTE, ";
				$stmtSQL   .= " coalesce(e.USDESC,' ') as USDESC_BMAUDU  ";
				$fileSQL   .= " ARPBCH ";
				$fileSQL   .= " left join WFPRHD   on PRPROC=BMWFPR ";
				$fileSQL   .= " left join SYFLAG a on (a.FLTYPE,a.FLVALU)=('ARBCHTYPE',BMBCHT) ";
				$fileSQL   .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTENTR',BMPMTE) ";
				$fileSQL   .= " left join SYUSER e on e.USUSER=BMAUDU ";
				$selectSQL .= " (BMBCHN,BMBCHD,BMBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
				require 'stmtSQLSelect.php';
				require 'stmtSQLEnd.php';

				print "\n <a name=\"demographics\"></a> ";
				$displayMaxRowsMsg="N";
				$moreURL="";
				require 'QuickLinkTopOfForm.php';

				print "\n <table $contentTable> ";

				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
				$row = db2_fetch_assoc($sqlResult);

				$F_BMBCHT=Format_Code($row['BMBCHT']);
				Build_DspFld("Batch Type",$row[FLDESC_BMBCHT],$F_BMBCHT,"A");
				$F_BMPMTE=Format_Code($row['BMPMTE']);
				Build_DspFld("Payment Entry",$row[FLDESC_BMPMTE],$F_BMPMTE,"N");
				Build_DspFld("Transaction Total",Format_Nbr ( $row['BMCHKT'], '2', $amtEditCode, 'Y', '', ''),"","N");
				Build_DspFld("Number Of Transactions",Format_Nbr ( $row['BMTRNS'], '0', $amtEditCode, 'Y', '', ''),"","N");
				Build_DspFld("Initial Entry Timestamp",$row[BMAUDT],"","A");
				$F_BMAUDU=Format_Code($row['BMAUDU']);
				Build_DspFld("Initial Entry By User Profile",$row[USDESC_BMAUDU],$F_BMAUDU,"N");

				print "\n </table> ";
				print "\n </fieldset> ";
			}

			// *****************************************************************************
			// Batch In Use
			// *****************************************************************************
			if ($quicklinkRef == "batchinuse") {
				require 'QuickLinkClear.php';
				$appendUserView="N";  // Do not append user view security
				require 'stmtSQLClear.php';
				$dftOrderBy = array(array("USDESCU","A","User"),array("BUUSER","A","User Profile"));
				Retrieve_Filter("ApplCashBatchUserInquiry.php");
				$stmtSQL   .= " Select BUUSER, ";
				$stmtSQL   .= " coalesce(USDESC,' ') as USDESC, coalesce(USDESCU,' ') as USDESCU ";
				$fileSQL   .= " ARPBCU ";
				$fileSQL   .= " left join SYUSER on USUSER=BUUSER ";
				$selectSQL .= " (BUBCHN,BUBCHD,BUBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
				require 'stmtSQLSelect.php';
				$stmtSQL   .= " Order By $orderBy";
				require 'stmtSQLEnd.php';
				require 'stmtSQLTotalRows.php';
				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

				print "\n <a name=\"batchinuse\"></a> ";
				$moreURL="{$homeURL}{$phpPath}ApplCashBatchUserInquiry.php{$scriptVarBase}&amp;tag=REPORT&amp;displayMenu=Y";
				require 'QuickLinkTopOfForm.php';

				print "\n <table $contentTable>";
				$rowCount = 0;
				$startRow = 1;
				while ($row = db2_fetch_assoc($sqlResult, $startRow)){
					if ($startRow == 1) {
						print "\n <tr> ";
						Format_Column_Header("USNAME", "User");
						print "\n </tr> ";
					}

					if ($rowCount >= $dspMaxRows) {break;}
		
					require 'SetRowClass.php';
					print "\n <tr class=\"$rowClass\"> ";
					print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[BUUSER]\">$row[USDESC]</span></td>";
					print "\n </tr> ";

					$startRow ++;
					$rowCount ++;
				}
				if ($rowCount==0) {require 'QuickLinkNoInfoMsg.php';}
				
				print "\n </table> ";
				print "\n </fieldset> ";
			}

			// *****************************************************************************
			// Pending Payments
			// *****************************************************************************
			if ($quicklinkRef=="batchentry") {
				require 'QuickLinkClear.php';

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
				$moreScript = "ApplCashBatchReview.php";
				$dftOrderBy = array(array("PETYPE","A","Type"),array("PEID","A","ID"),array("PEMCHK","A","Document"));
				Retrieve_Filter($moreScript);
				$stmtSQL   .= " Select PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,PECHK,PEPLT,PEMORD,PEMEMO ";
				$stmtSQL   .= "       ,PEPTYP,PEPMID,PEISEQ,PEENID,PECRTB,PEAMT,PEDAMT,PESBCD  ";
				$stmtSQL   .= "       ,PECMNT,Case When PECMNT<>' ' Then 'Y' Else ' ' End HAS_PECMNT ";
				$stmtSQL   .= "       ,Case When PEPTYP='M' Then PEMCHK Else PECHK End as PEMCHK  ";
				$stmtSQL   .= "       ,Case When PETYPE='P' Then PEID Else 0 End as PEPAYR  ";
				$stmtSQL   .= "       ,coalesce(IVBLTO,PEBLTO) as IVBLTO ";
				$stmtSQL   .= "       ,coalesce(IVAINV,PESINV) as PESINV ";
				$stmtSQL   .= "       ,coalesce(IVIVDT,PEBCHD) as PEIVDT ";
				$stmtSQL   .= "       ,coalesce(IVLOC, PELOC ) as PELOC ";
				$stmtSQL   .= "       ,coalesce(IVORD ,PEORD ) as PEORD ";
				$stmtSQL   .= "       ,coalesce(IVARPO,' ' ) as IVARPO ";
				$stmtSQL   .= "       ,coalesce(PYPYNM,' ') as PYPYNM, coalesce(PYPYNMU,' ') as PYPYNMU ";
				$stmtSQL   .= "       ,coalesce(PSDESC,' ') as PSDESC, coalesce(PSDESCU,' ') as PSDESCU ";
				$stmtSQL   .= "       ,coalesce(CMCNA1,' ') as CMCNA1, coalesce(CMCNA1U,' ') as CMCNA1U ";
				$stmtSQL   .= "       ,coalesce(LOLNA1,' ') as LOLNA1 ";
				$stmtSQL   .= "       ,Coalesce(b.FLDESC,' ') as PEPMID_FLDESC ";
				$stmtSQL   .= "       ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
				$stmtSQL   .= "       ,coalesce((Select Count(*) from ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,PRCHK,PRPTYP,PRPMID,PRISEQ,PRENID)=(z.PEBCHN,z.PEBCHD,z.PEBCHB,z.PETYPE,z.PEID,z.PECHK,z.PEPTYP,z.PEPMID,z.PEISEQ,z.PEENID)),0) as ARPYENERROR ";
				if ($HDOERL<=0) {
					$stmtSQL .= "     ,0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
				} else {
					$stmtSQL .= "     ,(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(IVAINV,IVIVDT,IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
					$stmtSQL .= "     ,(Select Count(*) From OEORHH Where HHLIV#=IVAINV and HHBLTO=IVBLTO) as OEHISTORY " ;
					$stmtSQL .= "     ,(Select Count(*) From OEORHH Where coalesce(IVORD,PEORD)<>0 and HHORD#=coalesce(IVORD,PEORD) and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
				}
				if ($HDPDRL<=0) {
					$stmtSQL .= "     ,0 as MFGORDCOUNT " ;
				} else {
					$stmtSQL .= "     ,(Select Count(*) From HDMOHM Where (OHPLT,OHORD)=(PEPLT,PEMORD)) as MFGORDCOUNT " ;
				}
				$fileSQL   .= " ARPYEN z ";
				$fileSQL   .= " left join HDINVC on IVISEQ=PEISEQ ";
				$fileSQL   .= " left join ARPYRH on PYPAYR=Case When PETYPE='P' Then PEID Else 0 End ";
				$fileSQL   .= " left join ARPYSB on PSSBCD=PESBCD ";
				$fileSQL   .= " left join HDCUST on CMCUST=coalesce(IVBLTO,PEBLTO) ";
				$fileSQL   .= " left join HDLCTN on LOLOC#=coalesce(IVLOC, PELOC) ";
				$fileSQL   .= " left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('ARPMTID',PEPMID) ";
				$fileSQL   .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=coalesce(IVORD,PEORD) and HHLIV#=IVAINV ";
				
				$selectSQL .= " (PEBCHN,PEBCHD,PEBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
				require 'stmtSQLSelect.php';
				if ($orderBy=="") {$orderBy="CMCNA1U, PESINV";}
				$stmtSQL   .= " Order By $orderBy";
				require 'stmtSQLEnd.php';
				require 'stmtSQLTotalRows.php';
				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

				print "\n <a name=\"batchentry\"></a> ";
				$moreURL="{$homeURL}{$phpPath}{$moreScript}{$scriptVarBase}&amp;tag=REPORT";
				require 'QuickLinkTopOfForm.php';

				print "\n <table $contentTable>";

				$rowCount = 0;
				$startRow = 1;
				while ($row = db2_fetch_assoc($sqlResult, $startRow)){
					if ($startRow == 1) {
						print "\n <tr> ";
						Format_Column_Header("PEAMT " , "Payment Amount") ;
						Format_Column_Header("PEDAMT" , "Discount") ;
						Format_Column_Header("PSDESCU", "Payment Code") ;
						Format_Column_Header("PESINV" , "Invoice") ;
						Format_Column_Header("PEIVDT" , "Invoice Date") ;
						Format_Column_Header("CMCNA1U", "Customer") ;
						Format_Column_Header("PEMCHK" , "Document") ;
						Format_Column_Header("HAS_PECMNT", "Cmt") ;
						Format_Column_Header("PEMEMO" , "Memo") ;
						Format_Column_Header("IVARPO" , "Reference Number") ;
						Format_Column_Header("PELOC"  , "Loc") ;
						Format_Column_Header("PYPYNMU", "Payer") ;
						Format_Column_Header("PEORD"  , "Order Number") ;
						Format_Column_Header("PEMORD" , "Mfg Order") ;
						Format_Column_Header("PEPMID" , "Trans Type") ;
						Format_Column_Header("ARPYENERROR", "Number of Errors") ;
						print "\n </tr> ";
					}

					if ($rowCount >= $dspMaxRows) {break;}

					$maintainVar = "{$scriptVarBase}&amp;fromType=" . urlencode(trim($row['PETYPE'])) . "&amp;fromID=" . urlencode(trim($row['PEID'])) . "&amp;fromDocument=" . urlencode(trim($row['PECHK'])) . "&amp;fromPaymentType=" . urlencode(trim($row['PEPTYP'])) . "&amp;fromPaymentID=" . urlencode(trim($row['PEPMID'])) . "&amp;fromInvoiceSeq=" . urlencode(trim($row['PEISEQ'])) . "&amp;fromEntryID=" . urlencode(trim($row['PEENID'])) . "&amp;fromScript=" . urlencode(trim($scriptName));
					require 'SetRowClass.php';

					$F_PEIVDT=Format_Date($row['PEIVDT'], "D");

					print "\n <tr class=\"$rowClass\"> ";
					print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['PEAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
					print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['PEDAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
					print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PESBCD]\">$row[PSDESC]</span></td>";
					if ($row['OEINVCOUNT']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['PESINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['PEIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[PESINV]</a></td> ";}
					else                      {print "\n <td class=\"colnmbr\">$row[PESINV]</td> ";}
					if ($row['PECRTB']=="A") {print "\n <td class=\"coldate\">$F_PEIVDT</td> ";}
					else                     {print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['PEISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_PEIVDT</a></td> ";}
					print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['IVBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[IVBLTO]]\">$row[CMCNA1]</a></td> ";
					print "\n <td class=\"colalph\">$row[PEMCHK]</td> ";
					print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[PECMNT]\">$row[HAS_PECMNT]</span></td> ";
					print "\n <td class=\"colalph\">$row[PEMEMO]</td> ";
					print "\n <td class=\"colalph\">$row[IVARPO]</td> ";
					print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['PELOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location [$row[LOLNA1]]\">$row[PELOC]</a></td> ";
					print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PEPAYR]\">$row[PYPYNM]</span></td> ";
					if     ($row['OESELECT']>0)  {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['PEORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[PEORD]</a></td> ";}
					elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['IVBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['PESINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[PEORD]</a></td> ";}
					else                         {print "\n <td class=\"colnmbr\">$row[PEORD]</td> ";}
					if ($row['MFGORDCOUNT']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['ARPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['ARMORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[ARMORD]</a></td> ";}
					else                       {print "\n <td class=\"colalph\">$row[ARMORD]</td> ";}
					print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row[PEPMID_FLDESC]) . "\">$row[PEPMID]</span></td>";
					if ($row['ARPYENERROR']==0) {print "\n <td class=\"colnmbr\">$row[ARPYENERROR]</td>";}
					else                        {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashErrorInquiry.php{$scriptVarBase}{$maintainVar}&amp;tag=REPORT\" onclick=\"{$inquiryWinVar}\" title=\"View Error\">$row[ARPYENERROR]</a></td>";}
					print "\n </tr> ";

					$startRow ++;
					$rowCount ++;
				}
				if ($rowCount==0) {require 'QuickLinkNoInfoMsg.php';}
				
				print "\n </table> ";
				print "\n </fieldset> ";
			}

			// *****************************************************************************
			// Deposit Entry
			// *****************************************************************************
			if ($quicklinkRef=="depositentry") {
				require 'stmtSQLClear.php';
				$dftOrderBy = array(array("BDSEQ","A","Sequence"));
				$moreScript = "ARDepositEntryInquiry.php";
				Retrieve_Filter($moreScript);
				$appendUserView="N";  // Do not append user view security
				$stmtSQL .= " Select BDSEQ,BDAMT,BDSRCC,BDSRCN,BDDTE ,";
				$stmtSQL .= " Coalesce((Select Sum(YPAMT) from ARYPTD Where (YPBCH,YPBDAT,YPBANK,YPCHK)=(a.BDBCHN,a.BDBCHD,a.BDBCHB,a.BDSRCN) and (YPGDED='G' or YPPYCD in (Select PYPYCD from ARPYCD Where PYTYPE not in ('J','Y','D')))),0) as YPAMT, ";
				$stmtSQL .= " Coalesce((Select Sum(PEAMT) from ARPYEN Where (PEBCHN,PEBCHD,PEBCHB)=(a.BDBCHN,a.BDBCHD,a.BDBCHB) and a.BDSRCN=(Case When PEPTYP='M' Then PEMCHK Else PECHK End) and (PEGDED='G' or PESBCD in (Select PSSBCD from ARPYCD inner join ARPYSB on PSPYCD=PYPYCD Where PYTYPE not in ('J','Y','D')))),0) as PEAMT, ";
				$stmtSQL .= " Coalesce(BSDESC,' ') as BSDESC, Coalesce(Upper(BSDESC),' ') as BSDESCU ";
				$fileSQL .= " ARDEPD a ";
				$fileSQL .= " left join ARDSRC   on BSSRCC=BDSRCC ";
				$selectSQL .= " (BDBCHN,BDBCHD,BDBCHB)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
				require 'stmtSQLSelect.php';
				if ($orderBy=="") {$orderBy="BDSEQ";}
				$stmtSQL   .= " Order By $orderBy";
				require 'stmtSQLEnd.php';
				require 'stmtSQLTotalRows.php';
				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

				print "\n <a name=\"depositentry\"></a> ";
				$moreURL="{$homeURL}{$phpPath}{$moreScript}{$scriptVarBase}&amp;tag=REPORT&amp;displayMenu=Y";
				require 'QuickLinkTopOfForm.php';

				print "\n <table $contentTable>";

				$rowCount = 0;
				$startRow = 1;
				while ($row = db2_fetch_assoc($sqlResult, $startRow)){
					if ($startRow == 1) {
			 			print "\n <tr> ";
						Format_Column_Header("BDSRCC", "Source Code") ;
						Format_Column_Header("BDSRCN", "Source Number") ;
						Format_Column_Header("BDDTE", "Date") ;
						Format_Column_Header("BDAMT", "Amount") ;
						Format_Column_Header("YPAMT", "Posted Amount Paid") ;
						Format_Column_Header("PEAMT", "Pending Payment Amount") ;
						Format_Column_Header("", "Remaining Balance") ;
						print "\n </tr> ";
					}

					if ($rowCount >= $dspMaxRows) {break;}
				
					require 'SetRowClass.php';
					$F_BDDTE=Format_Date($row['BDDTE'], "D");
					$F_RemainAmt=$row['BDAMT']-$row['YPAMT']-$row['PEAMT'];
					print "\n <tr class=\"$rowClass\">";
					print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[BDSRCC]\">$row[BSDESC]</span></td>";
					print "\n <td class=\"colalph\">$row[BDSRCN]</td>";
					print "\n <td class=\"colnmbr\">$F_BDDTE</td>";
					print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['BDAMT'], '2', $amtEditCode, 'Y', '', '') . "</td>";
					print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['YPAMT'], '2', $amtEditCode, 'Y', '', '') . "</td>";
					print "\n <td class=\"colnmbr\">" . Format_Nbr ( $row['PEAMT'], '2', $amtEditCode, 'Y', '', '') . "</td>";
					print "\n <td class=\"colnmbr\">" . Format_Nbr ( $F_RemainAmt, '2', $amtEditCode, 'Y', '', '') . "</td>";
					print "\n </tr> ";

					$startRow ++;
					$rowCount ++;
				}
				if ($rowCount==0) {require 'QuickLinkNoInfoMsg.php';}
				
				print "\n </table> ";
				print "\n </fieldset> ";
			}

			// *****************************************************************************
			// Payments in History
			// *****************************************************************************
			if ($quicklinkRef=="paymenthistory") {
				require 'QuickLinkClear.php';
				$uv_BankName ="YPBANK";
				$uv_CustomerName ="YPBLTO";
				$uv_CustomerClassName ="CMCCLS";
				$uv_RegionName ="CMCRGN";
				$uv_BillingLocationName = "IVLOC";
				$uv_SalesmanName = "IVSLSM";
				$uv_PayerName = "YPPAYR";
				require 'UserView.php';

				require 'stmtSQLClear.php';
				$moreScript = "hdList.php";
				$dftOrderBy = array(array("YPBDAT","A","Date Paid"),array("YPAINV","A","Invoice"));
				Retrieve_Filter($moreScript);
				$stmtSQL   .= " Select YPBLTO,YPPAYR,YPISEQ,YPAINV,YPIVDT,YPAMT, YPDAMT,YPBDAT,YPBANK,YPCHK,YPSBCD,IVARPO ";
				$stmtSQL   .= "       ,YPCMNT,Case When YPCMNT<>' ' Then 'Y' Else ' ' End HAS_YPCMNT ";
				$stmtSQL   .= "       ,IVDUED,IVLOC,IVORD ";
				$stmtSQL   .= "       ,coalesce(CMCNA1,' ') as CMCNA1, coalesce(CMCNA1U,' ') as CMCNA1U ";
				$stmtSQL   .= "       ,coalesce(PSDESC,' ') as PSDESC, coalesce(PSDESCU,' ') as PSDESCU ";
				$stmtSQL   .= "       ,coalesce(PYPYNM,' ') as PYPYNM, coalesce(PYPYNMU,' ') as PYPYNMU ";
				$stmtSQL   .= "       ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
				if ($HDOERL<=0) {
					$stmtSQL .= "     ,0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
				} else {
					$stmtSQL .= "     ,(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(IVAINV,IVIVDT,IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
					$stmtSQL .= "     ,(Select Count(*) From OEORHH Where HHLIV#=IVAINV and HHBLTO=IVBLTO) as OEHISTORY " ;
					$stmtSQL .= "     ,(Select Count(*) From OEORHH Where IVORD<>0 and HHORD#=IVORD and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
				}
				$fileSQL   .= " ARYPTD ";
				$fileSQL   .= " inner join HDINVC on IVISEQ=YPISEQ ";
				$fileSQL   .= " left join HDCUST on CMCUST=YPBLTO ";
				$fileSQL   .= " left join ARPYSB on PSSBCD=YPSBCD ";
				$fileSQL   .= " left join ARPYRH on PYPAYR=YPPAYR ";
				$fileSQL   .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=IVORD and HHLIV#=IVAINV ";
				$selectSQL .= " (YPBCH,YPBDAT,YPBANK)=($fromBatchNumber,$fromBatchDate,$fromBatchBank) ";
				require 'stmtSQLSelect.php';
				if ($orderBy=="") {$orderBy="YPBDAT, YPAINV";}
				$stmtSQL   .= " Order By $orderBy";
				require 'stmtSQLEnd.php';
				require 'stmtSQLTotalRows.php';
				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

				print "\n <a name=\"paymenthistory\"></a> ";
				$moreURL="{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=176&amp;fKey1=YPBCH&amp;fVal1=" . urlencode($fromBatchNumber) . "&amp;fKey2=YPBDAT&amp;fVal2=" . urlencode($fromBatchDate) . "&amp;fKey3=YPBANK&amp;fVal3=" . urlencode($fromBatchBank);
				require 'QuickLinkTopOfForm.php';

				print "\n <table $contentTable>";

				$rowCount = 0;
				$startRow = 1;
				while ($row = db2_fetch_assoc($sqlResult, $startRow)){
					if ($startRow == 1) {
						print "\n <tr> ";
						Format_Column_Header("CMCNA1U", "Customer") ;
						Format_Column_Header("YPAINV" , "Invoice") ;
						Format_Column_Header("YPIVDT" , "Invoice Date") ;
						Format_Column_Header("IVDUED" , "Due Date") ;
						Format_Column_Header("YPAMT " , "Amount Paid") ;
						Format_Column_Header("YPDAMT" , "Discount") ;
						Format_Column_Header("YPCHK " , "Document") ;
						Format_Column_Header("HAS_YPCMNT " , "Cmt") ;
						Format_Column_Header("IVORD " , "Order Number") ;
						Format_Column_Header("IVARPO" , "Reference Number") ;
						Format_Column_Header("PSDESCU", "Payment Code") ;
						Format_Column_Header("IVLOC", "Loc") ;
						Format_Column_Header("PYPAYRU", "Payer") ;
						print "\n </tr> ";
					}

					if ($rowCount >= $dspMaxRows) {break;}
				
					require 'SetRowClass.php';

					$F_YPIVDT=Format_Date($row['YPIVDT'], "D");
					$F_IVDUED=Format_Date_ISO($row['IVDUED'], "D");

					print "\n <tr class=\"$rowClass\"> ";
					print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['YPBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[YPBLTO]]\">$row[CMCNA1]</a></td> ";
					if ($row['OEINVCOUNT']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['YPBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['YPAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['YPIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[YPAINV]</a></td> ";}
					else                      {print "\n <td class=\"colnmbr\">$row[YPAINV]</td> ";}
					print "\n     <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$genericVarBase}&amp;tag=REPORT&amp;customerNumber=" . urlencode(trim($row['YPBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['YPISEQ'])) . "&amp;noMenu=Y\" onclick=\"$invoiceWinVar\" title=\"View A/R Invoice\">$F_YPIVDT</a></td> ";
					print "\n     <td class=\"coldate\">$F_IVDUED</td> ";
					print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['YPAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
					print "\n     <td class=\"colnmbr\">" . Format_Nbr ( $row['YPDAMT'], '2', $amtEditCode, 'Y', '', '') . "</td> ";
					if (trim($row['YPCHK']) != "" && $row['YPBANK']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ARCheckInquiry.php{$scriptVarBase}&amp;tag=REPORT&amp;fromDocument=" . urlencode(trim($row['YPCHK'])) . "&amp;fromDatePaid=" . urlencode(trim($row['YPBDAT'])) . "&amp;fromBank=" . urlencode(trim($row['YPBANK'])) . "&amp;fromPayer=" . urlencode(trim($row['YPPAYR'])) . "&amp;fromCustomer=" . urlencode(trim($row['YPBLTO'])) . "\" onclick=\"$drillDownWinVar\" title=\"A/R Document Quickview\">$row[YPCHK]</a></td> ";}
					else                                               {print "\n <td class=\"colnmbr\">$row[YPCHK]</td> ";}
					print "\n     <td class=\"colcode\" $helpCursor><span title=\"$row[YPCMNT]\">$row[HAS_YPCMNT]</span></td> ";
					if     ($row['OESELECT']>0)  {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['IVORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[IVORD]</a></td> ";}
					elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['YPBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['YPAINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[IVORD]</a></td> ";}
					else                         {print "\n <td class=\"colnmbr\">$row[IVORD]</td> ";}
					print "\n     <td class=\"colalph\">$row[IVARPO]</td> ";
					print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[YPSBCD]\">$row[PSDESC]</span></td>";
					print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['IVLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location\">$row[IVLOC]</a></td> ";
					print "\n     <td class=\"colalph\" $helpCursor><span title=\"$row[YPPAYR]\">$row[PYPYNM]</span></td> ";
					print "\n </tr> ";

					$startRow ++;
					$rowCount ++;
				}
				if ($rowCount==0) {require 'QuickLinkNoInfoMsg.php';}
				
				print "\n </table> ";
				print "\n </fieldset> ";
			}

			// *****************************************************************************
			// Attachments
			// *****************************************************************************
			require 'AttachmentSQLInclude.php';
		}
		require 'QuickLinkEndLoop.php';                          // Quicklink End
	}
}

if ($noMenu =="Y") {print $inquiryhrTagAttr;}
else               {print $hrTagAttr;}
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
if ($noMenu =="Y") {require $inquiryTrailer;}
else               {require_once 'Trailer.php';}
print "</body> </html>";
exit;

?>										