<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$fromPayer          = $_GET['fromPayer'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'QuickLink.php';
require_once 'StoredProcedureVariablesInclude.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Payer";
$scriptName     = "PayerSelect.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromPayer=" . urlencode(trim($fromPayer)) . "&amp;fromScript=" . urlencode(trim($fromScript));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$cancelWFURL    = "{$baseURL}&amp;tag=CancelWF";
$attachFolder   = "Payer";
$programName    = "HARPYM_E";
$quickLinkByUser= "Y";
$UFFILN         = "ARPYRU";

$userPass=PayerUserView($profileHandle, $fromPayer, "Y");
if ($userPass == "N") {
	require_once 'UserViewErrorInclude.php';
	exit;
}

$payerName=RetValue("PYPAYR=$fromPayer ", "ARPYRH", "PYPYNM");

require_once ($docType);
print "\n <html> <head> ";
$title="$fromPayer $payerName";
require_once ($headInclude);

print "\n <script TYPE=\"text/javascript\">  ";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
print "\n </script> ";

require_once ($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID="PAYERSELECT";
if ($formatToPrint == "") {require_once 'MenuDisplay.php';}
print "\n <td class=\"content\">";

require_once 'QuickLinkTable.php';                            // QuickLink Table
require_once 'QuickLinkByUser.php';                           // QuickLink By User

// *****************************************************************************
// Heading
// *****************************************************************************
$uv_Sql="";  // No user view security
$wildCardSearch="";  // No wildCardSearch

require 'stmtSQLClear.php';
$appendWildCard="N";  // Do not append wildCardSearch
$stmtSQL   .= " Select PYPAYR,PYPYNM  ";
$fileSQL   .= " ARPYRH ";
$selectSQL .= " PYPAYR=$fromPayer ";
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

// Program Option Security
$harpym_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01=$harpym_OPT['sec_01'];
$sec_02=$harpym_OPT['sec_02'];
$sec_03=$harpym_OPT['sec_03'];
$sec_04=$harpym_OPT['sec_04'];
print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

$attachVarKey=trim($fromPayer);
$attachForDesc="";
$attachPrg1= "ARPYRH Where PYPAYR=$fromPayer ";
if ($formatToPrint != "Y") {
	$maintainVar="{$scriptVarBase}&amp;fromScript=$scriptName";
	print "\n <td class=\"toolbar\">";
	print "\n <a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=100\" title=\"Back Home\">$portalHome</a> ";
	require_once 'AttachmentInclude.php';
	if ($sec_01=="Y")                 {print "\n <a href=\"{$homeURL}{$phpPath}PayerMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a> "; }
	if ($sec_01=="Y" && $sec_04=="Y") {print "\n <a href=\"{$homeURL}{$phpPath}PayerMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=Z\">$copyImageLrg</a> ";}
	if ($sec_02=="Y" || $sec_03=="Y") {print "\n <a href=\"{$homeURL}{$phpPath}PayerMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageLrg</a> "; }
	if ($sec_03=="Y") {
		$confirmDesc = Format_Confirm_Desc($payerName, $fromPayer, "", "", "", "");
		print "\n <a onClick=\"return confirmDelete('$confirmDesc')\" href=\"{$homeURL}{$phpPath}PayerMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageLrg</a> ";
	}
	require_once 'FormatToPrint.php';
	require_once 'HelpPage.php';
	print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";
require_once 'ConfMessageDisplay.php';

print $hrTagAttr;

$row = db2_fetch_assoc($sqlResult);

print "\n <table $contentTable>";
Format_Header("Payer",$row[PYPYNM], $row[PYPAYR]);
print "\n </table> ";

print $hrTagAttr;
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

				$udCol = Rtv_UserDefined_Columns($UFFILN, "");
				$arCol = Rtv_ARPYRUTable_Columns($fromPayer);

				require 'QuickLinkClear.php';
				require 'stmtSQLClear.php';
				$appendUserView="N";  // Do not append user view security
				$appendWildCard="N";  // Do not append wildCardSearch
				$stmtSQL   .= " Select PYPAYR,PYPYNM,PYADR1,PYADR2,PYADR3,PYCITY,PYST ,PYZIP, ";
				$stmtSQL   .= "        PYCTRY,PYPHON,PYFAX ,PYCTOR,PYCONT,PYCPHN,PYEMAL,PYSBCD, ";
				$stmtSQL   .= " coalesce(PSDESC,' ') as PSDESC, ";
				$stmtSQL   .= " coalesce(USDESC,' ') as USDESC ";
				$fileSQL   .= " ARPYRH ";
				$fileSQL   .= " left join ARPYSB on PSSBCD=PYSBCD ";
				$fileSQL   .= " left join SYUSER on USUSER=PYCTOR ";
				$selectSQL .= " PYPAYR=$fromPayer ";
				require 'stmtSQLSelect.php';
				require 'stmtSQLEnd.php';

				print "\n <a name=\"demographics\"></a> ";
				$displayMaxRowsMsg="N";
				$moreURL="";
				require 'QuickLinkTopOfForm.php';

				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
				$row = db2_fetch_assoc($sqlResult);

				print "\n <table $contentTable> ";

				Build_DspFld("Address Line One",$row['PYADR1'],"","A");
				if (trim($row['PYADR2']) != "") {Build_DspFld("Address Line Two",$row['PYADR2'],"","A");}
				if (trim($row['PYADR3']) != "") {Build_DspFld("Address Line Three",$row['PYADR3'],"","A");}
				
				if (trim($row['PYCITY']) != "" || trim($row['PYST']) != "" || trim($row['PYZIP']) != "") {
					Build_DspFld("City, State Zip","$row[PYCITY], $row[PYST] $row[PYZIP]","","A");
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

				print "\n <fieldset class=\"legendBody\"> ";
				print "\n     <legend class=\"legendTitle\">Contact</legend> ";
				print "\n     <table $contentTable> ";
				Build_DspFld("Name",$row['PYCONT'],"","A");

				$F_PYCPHN=EditPhoneNumber($row['PYCPHN']);
				Build_DspFld("Phone",$F_PYCPHN,"","A");

				Build_DspFld("E-mail",$row['PYEMAL'],"","A");

				print "\n </table> ";
				print "\n </fieldset> ";

				print "\n <fieldset class=\"legendBody\"> ";
				print "\n      <legend class=\"legendTitle\">User-Defined</legend> ";
				print "\n     <table $contentTable> ";
				foreach ($udCol as $udFld)  {
					$UFFLDN = trim($udFld['UFFLDN']);
					$UFDESC = trim($udFld['UFDESC']);
					$UFTYPE = trim($udFld['UFTYPE']);
					$UFSIZE = trim($udFld['UFSIZE']);
					$UFDECM = trim($udFld['UFDECM']);
					$UFVALU = trim($udFld['UFVALU']);
					$UFBOXS = trim($udFld['UFBOXS']);
					$UFREQF = trim($udFld['UFREQF']);
					$UFVLDV = trim($udFld['UFVLDV']);
					$UFFFMT = trim($udFld['UFFFMT']);
					$PUFLDD="";
					$PUFLDR="";
					$PUFLDV="";

					foreach ($arCol as $arFld)  {
						if ($UFFLDN==trim($arFld['PUFLDN'])) {
							$PUFLDD = trim($arFld['PUFLDD']);
							$PUFLDR = trim($arFld['PUFLDR']);
							$PUFLDV = trim($arFld['PUFLDV']);
						}
					}

					print "\n     <tr><td class=\"dsphdr\">$UFDESC</td> ";
					if ($UFTYPE == "A") {
						if     ($UFFFMT == "U") {print "\n         <td class=\"dspalph\"><a href=\"{$PUFLDV}\" target=_blank>$PUFLDV)</a></td> ";}
						elseif ($UFFFMT == "E") {print "\n         <td class=\"dspalph\"><a href=\"mailto:{$PUFLDV}\" target=_blank title=\"Click here to send e-mail\">$PUFLDV</a></td> ";}
						else                    {print "\n         <td class=\"dspalph\">$PUFLDV ";}
					} else if ($UFTYPE == "C") {
						print "\n     <td class=\"inputalph\"> ";
						if ($PUFLDV != "") {print "\n     <textarea name=\"$UFFLDN\" readonly ROWS=$UFBOXS COLS=60 WRAP=\"hard\">" . rtrim($PUFLDV) . "</textarea> $fldReqDesc ";}
						else               {print "\n     <textarea name=\"$UFFLDN\" readonly ROWS=$UFBOXS COLS=60 WRAP=\"hard\"></textarea> $fldReqDesc ";}
					} else if ($UFTYPE == "N") {
						$PUFLDR=number_format($PUFLDR,$UFDECM,'.','');
						if ($UFFFMT == "P") {$PUFLDR=EditPhoneNumber($PUFLDR);}
						print "\n     <td class=\"dspnmbr\">$PUFLDR ";
					} else if ($UFTYPE == "D") {
						$PUFLDD=Format_Date_ISO($PUFLDD,"H");
						print "\n     <td class=\"dspnmbr\">$PUFLDD ";
					}
					print "\n         </td></tr> ";
					DspErrMsg($errFldName);
				}
				print "\n         </table> ";
				print "\n     </fieldset> ";
				print "\n </fieldset> ";
			}

			// *****************************************************************************
			// Customer
			// *****************************************************************************
			if ($quicklinkRef == "payercustomer") {
				require 'QuickLinkClear.php';
				require 'stmtSQLClear.php';
				$appendUserView="N";  // Do not append user view security
				$appendWildCard="N";  // Do not append wildCardSearch
				$dftOrderBy = array(array("CMCNA1U","A","Name"),array("CMCUST","A","Customer"));
				$moreScript = "PayerCustomerMaintain.php";
				Retrieve_Filter($moreScript);
				$stmtSQL   .= " Select PCCUST, CMCNA1, CMCNA2, CMCCTY, CMST, CMZIP, CMPHON ";
				$fileSQL   .= " ARPYRC inner join HDCUST on CMCUST=PCCUST ";
				$selectSQL .= " PCPAYR = $fromPayer ";
				require 'stmtSQLSelect.php';
				$stmtSQL   .= " Order By $orderBy";
				require 'stmtSQLEnd.php';
				require 'stmtSQLTotalRows.php';
				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

				print "\n <a name=\"payercustomer\"></a> ";
				if ($sec_02=="Y") {$moreURL="{$homeURL}{$phpPath}{$moreScript}{$scriptVarBase}&amp;tag=REPORT";}
				else              {$moreURL = "";}
				require 'QuickLinkTopOfForm.php';

				print "\n <table $contentTable>";

				$rowCount = 0;
				$startRow = 1;
				while ($row = db2_fetch_assoc($sqlResult, $startRow)){
					if ($startRow == 1) {
						print "\n <tr> ";
						Format_Column_Header("PCCUST ", "Customer") ;
						Format_Column_Header("CMCNA1U", "Name") ;
						Format_Column_Header("CMCNA2U", "Address") ;
						Format_Column_Header("CMCCTYU", "City") ;
						Format_Column_Header("CMST   ", "State") ;
						Format_Column_Header("CMZIP  ", "Zip") ;
						Format_Column_Header("CMPHON ", "Phone") ;
						print "\n </tr> ";
					}

					if ($rowCount >= $dspMaxRows) {break;}
					
					require 'SetRowClass.php';
					$F_CMPHON=EditPhoneNumber($row['CMPHON']);
					print "\n <tr class=\"$rowClass\"> ";

					print "\n     <td class=\"colnmbr\">$row[PCCUST]</td> ";
					print "\n     <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['PCCUST'])) . "\" title=\"View Customer\">$row[CMCNA1]</a></td> ";
					print "\n     <td class=\"colalph\">$row[CMCNA2]</td> ";
					print "\n     <td class=\"colalph\">$row[CMCCTY]</td> ";
					print "\n     <td class=\"colcode\">$row[CMST]</td> ";
					print "\n     <td class=\"colalph\">$row[CMZIP]</td> ";
					print "\n     <td class=\"colalph\">$F_CMPHON</td> ";
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

print $hrTagAttr;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once 'Trailer.php';
print "</body> </html>";
exit;

?>	