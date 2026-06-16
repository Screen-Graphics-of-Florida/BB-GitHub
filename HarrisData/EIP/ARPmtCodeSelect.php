<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'ARPmtTypeInclude.php';

$fromPmtCode        = $_GET['fromPmtCode'];
$backHome           = $_GET['backHome'];

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';
require_once 'QuickLink.php';

$page_title     = "Payment Code";
$scriptName     = "ARPmtCodeSelect.php";
$scriptVarBase  = "{$genericVarBase}&amp;fromPmtCode=" . urlencode(trim($fromPmtCode)) . "&amp;backHome=" . urlencode(trim($backHome));
$scriptD2WVarBase  = "{$altVarBase}&amp;fromPmtCode=" . urlencode(trim($fromPmtCode)) . "&amp;backHome=" . urlencode(trim($backHome));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$deleteURL      = "{$baseURL}&amp;tag=Edit_Data&amp;maintenanceCode=D";
$cancelWFURL    = "{$baseURL}&amp;tag=CancelWF";
$programName    = "HARPYU_E";
$quickLinkByUser= "Y";
$_SESSION[$fromURL]=$baseURL;
require_once ($docType);
print "\n <html> <head> ";
$title="$fromPmtCode";
require_once ($headInclude);

print "\n <script TYPE=\"text/javascript\">  ";
print "\n function confirmDelete(text) {return confirm(\"$delRecordConf\" + \"\\n\" + \"\\n\" + text);} \n";
require_once 'Menu.js';
require_once 'NewWindowOpen.php';
	require_once 'SaveCurrentURL.php';
print "\n </script> ";

require_once ($genericHead);
print "\n    </head> ";
print "\n    <body $bodyTagAttr> ";
require_once 'Banner.php';
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID="ARPMTCODESELECT";
require_once 'MenuDisplay.php';
print "\n <td class=\"content\">";

require 'QuickLinkTable.php';                            // QuickLink Table
require 'QuickLinkByUser.php';                           // QuickLink By User

// *****************************************************************************
// Payment Code Heading
// *****************************************************************************
require 'stmtSQLClear.php';
$appendUserView="N";  // Do not append user view security
$appendWildCard="N";  // Do not append wildCardSearch
$stmtSQL   .= " Select  PYPYCD, PYPYDS ";
$fileSQL   .= " ARPYCD ";
$selectSQL .= " PYPYCD='$fromPmtCode' ";
require 'stmtSQLSelect.php';
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

// Program Option Security
$harpyu_OPT=pgmOptSecurity($profileHandle, $dataBaseID, $programName);
$sec_01=$harpyu_OPT['sec_01'];
$sec_02=$harpyu_OPT['sec_02'];
$sec_03=$harpyu_OPT['sec_03'];
$sec_04=$harpyu_OPT['sec_04'];
print "\n <table $contentTable>";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\">";
print "\n <tr><td><h1>$page_title</h1></td>";

if ($formatToPrint != "Y") {
	$maintainVar="{$scriptVarBase}";
	print "\n <td class=\"toolbar\">";
	print "\n <a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=5\" title=\"Back Home\">$portalHome</a> ";
	if ($sec_01=="Y")                 {print "\n <a onClick=\"saveCurrentURL();\" href=\"{$homeURL}{$phpPath}ARPmtCodeMaintain.php{$genericVarBase}&amp;tag=MAINTAIN&amp;maintenanceCode=A\">$addImageLrg</a> "; }
	if ($sec_02=="Y" || $sec_03=="Y") {print "\n <a onClick=\"saveCurrentURL();\" href=\"{$homeURL}{$phpPath}ARPmtCodeMaintain.php{$maintainVar}&amp;tag=MAINTAIN&amp;maintenanceCode=C\">$changeImageLrg</a> "; }
	if ($sec_03=="Y") {
		$pmtCodeDesc=RetValue("PYPYCD='$fromPmtCode'", "ARPYCD", "PYPYDS");
		$confirmDesc = Format_Confirm_Desc("$pmtCodeDesc $fromPmtCode", "", "", "", "", "");
		print "\n <a onClick=\"return confirmDelete('$confirmDesc'); saveCurrentURL();\" href=\"{$homeURL}{$phpPath}ARPmtCodeMaintain.php{$maintainVar}&amp;tag=Edit_Data&amp;maintenanceCode=D\">$deleteImageLrg</a> ";
	}
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	print "\n </td> ";
}
print "\n </tr> ";
print "\n </table> ";
require_once 'ConfMessageDisplay.php';

print $hrTagAttr;

$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);

print "\n <table $contentTable>";

$F_PYPYCD=Format_Code($row['PYPYCD']);
print "\n <tr> <td class=\"dsphdr\">Payment Code</td> ";
print "\n      <td class=\"dspalph\">$row[PYPYDS] $F_PYPYCD</td> ";
print "\n </tr> ";
print "\n </table> ";

print $hrTagAttr;
require 'QuickLinkDisplay.php';
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
				require 'stmtSQLClear.php';
				$appendUserView="N";  // Do not append user view security
				$appendWildCard="N";  // Do not append wildCardSearch
				$stmtSQL   .= " Select  PYSTDS, PYACCT, PYSUB, PYTYPE, ";
				$stmtSQL   .= " coalesce(CHCHDS,' ') as CHCHDS, ";
				$stmtSQL   .= " coalesce(CPDESC,' ') as CPDESC ";
				$fileSQL   .= " ARPYCD ";
				$fileSQL   .= " left join HDCHRT on (CHACCT,CHSUB)=(PYACCT,PYSUB) ";
				$fileSQL   .= " left join ARPAYT on CPTYPE=PYTYPE ";
				$selectSQL .= " PYPYCD='$fromPmtCode' ";
				require 'stmtSQLSelect.php';
				require 'stmtSQLEnd.php';
				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

				print "\n <a name=\"demographics\"></a> ";
				$displayMaxRowsMsg="N";
				$moreURL="";
				require 'QuickLinkTopOfForm.php';

				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
				$row = db2_fetch_assoc($sqlResult);

				print "\n <table $contentTable> ";

				print "\n <tr><td class=\"dsphdr\">Statement Description</td> ";
				print "\n     <td class=\"dspalph\">$row[PYSTDS]</td> ";
				print "\n </tr> ";

				$F_PYACCT=Format_Code(Format_Acct ($row['PYACCT'], $row['PYSUB'],"N"));
				print "\n <tr><td class=\"dsphdr\">Account</td> ";
				print "\n     <td class=\"dspalph\">$row[CHCHDS] $F_PYACCT</td> ";
				print "\n </tr> ";

				$F_PYTYPE=Format_Code($row['PYTYPE']);
				print "\n <tr><td class=\"dsphdr\">Payment Type</td> ";
				print "\n     <td class=\"dspalph\">$row[CPDESC] $F_PYTYPE</td> ";
				print "\n </tr> ";

				print "\n </table> ";
				print "\n </fieldset> ";
			}

			// *****************************************************************************
			// Payment Sub Code
			// *****************************************************************************
			if ($quicklinkRef == "pmtsubcode") {

				$PYTYPE=RetValue("PYPYCD='$fromPmtCode'", "ARPYCD", "PYTYPE");
				if     ($PYTYPE=="C") {$CPCFOV=$C_CPCFOV; $CPACOV=$C_CPACOV; $CPCSAC=$C_CPCSAC; $CPARAC=$C_CPARAC; $CPOFAC=$C_CPOFAC;}
				elseif ($PYTYPE=="D") {$CPCFOV=$D_CPCFOV; $CPACOV=$D_CPACOV; $CPCSAC=$D_CPCSAC; $CPARAC=$D_CPARAC; $CPOFAC=$D_CPOFAC;}
				elseif ($PYTYPE=="J") {$CPCFOV=$J_CPCFOV; $CPACOV=$J_CPACOV; $CPCSAC=$J_CPCSAC; $CPARAC=$J_CPARAC; $CPOFAC=$J_CPOFAC;}
				elseif ($PYTYPE=="M") {$CPCFOV=$M_CPCFOV; $CPACOV=$M_CPACOV; $CPCSAC=$M_CPCSAC; $CPARAC=$M_CPARAC; $CPOFAC=$M_CPOFAC;}
				elseif ($PYTYPE=="U") {$CPCFOV=$U_CPCFOV; $CPACOV=$U_CPACOV; $CPCSAC=$U_CPCSAC; $CPARAC=$U_CPARAC; $CPOFAC=$U_CPOFAC;}
				elseif ($PYTYPE=="Y") {$CPCFOV=$Y_CPCFOV; $CPACOV=$Y_CPACOV; $CPCSAC=$Y_CPCSAC; $CPARAC=$Y_CPARAC; $CPOFAC=$Y_CPOFAC;}
				else                  {$CPCFOV="N"; $CPACOV="N"; $CPCSAC="N"; $CPARAC="N"; $CPOFAC="N";}

				require 'QuickLinkClear.php';
				require 'stmtSQLClear.php';
				$stmtSQL .= " Select PSSBCD,PSDESC,PSPYCD,PSSTDS,PSCSAC,PSCSSB,PSARAC,PSARSB,PSOFCO,PSOFFC,PSOFAC,PSOFSB,PSDTDE,   ";
				$stmtSQL .= " coalesce(PYPYDS,' ') as PYPYDS, upper(coalesce(PYPYDS,' ')) as PYPYDSU,   ";
				$stmtSQL .= " coalesce(a.CHCHDS,' ') as CSH_CHCHDS, coalesce(a.CHCHDSU,' ') as CSH_CHCHDSU,   ";
				$stmtSQL .= " coalesce(b.CHCHDS,' ') as AR_CHCHDS, coalesce(b.CHCHDSU,' ') as AR_CHCHDSU,   ";
				$stmtSQL .= " coalesce(CFCFNM,' ') as CFCFNM, coalesce(CFCFNMU,' ') as CFCFNMU,   ";
				$stmtSQL .= " coalesce(c.CHCHDS,' ') as OFF_CHCHDS, coalesce(c.CHCHDSU,' ') as OFF_CHCHDSU   ";
				$fileSQL .= " ARPYSB ";
				$fileSQL .= " left join ARPYCD on PYPYCD=PSPYCD ";
				$fileSQL .= " left join HDCHRT a on (a.CHACCT,a.CHSUB)=(PSCSAC,PSCSSB) ";
				$fileSQL .= " left join HDCHRT b on (b.CHACCT,b.CHSUB)=(PSARAC,PSARSB) ";
				$fileSQL .= " left join HDCFAC on (CFCO#,CFFAC#)=(PSOFCO,PSOFFC) ";
				$fileSQL .= " left join HDCHRT c on (c.CHACCT,c.CHSUB)=(PSOFAC,PSOFSB) ";
				$selectSQL .= " PSPYCD='$fromPmtCode' ";
				require 'stmtSQLSelect.php';
				if ($orderBy=="") {$orderBy="PSDESCU";}
				$stmtSQL   .= " Order By $orderBy";
				require 'stmtSQLEnd.php';
				require 'stmtSQLTotalRows.php';
				$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

				print "\n <a name=\"pmtsubcode\"></a> ";
				$moreURL="{$homeURL}{$phpPath}ARPmtSubCode.php{$scriptVarBase}&amp;tag=REPORT";
				require 'QuickLinkTopOfForm.php';

				print "\n <table $contentTable>";

				$rowCount = 0;
				$startRow = 1;
				while ($row = db2_fetch_assoc($sqlResult, $startRow)){
					if ($startRow == 1) {
						print "\n <tr> ";
						Format_Column_Header("PSDESCU"   , "Description");
						Format_Column_Header("PSSBCD"    , "Payment Sub Code");
						if ($CPCSAC=="Y") {Format_Column_Header("CSH_CHCHDSU" , "Cash Account");}
						if ($CPARAC=="Y") {Format_Column_Header("AR_CHCHDSU" , "A/R Account");}
						if ($CPOFAC=="Y") {
							Format_Column_Header("CFCFNMU", "Offset Co/Fac");
							Format_Column_Header("OFF_CHCHDSU", "Offset Account");
						}
						Format_Column_Header("PSSTDSU"   , "Statement Description");
						Format_Column_Header("PSDTDE"    , "Date Deactivated");
						print "\n </tr> ";
					}

					if ($rowCount >= $dspMaxRows) {break;}
			
					require 'SetRowClass.php';
					print "\n <tr class=\"$rowClass\"> ";
					print "\n     <td class=\"colalph\">$row[PSDESC]</td> ";
					print "\n     <td class=\"colalph\">$row[PSSBCD]</td> ";
					if ($CPCSAC=="Y") {
						$F_AcctSub=Format_Acct($row['PSCSAC'],$row['PSCSSB'],"N");
						print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_AcctSub\">$row[CSH_CHCHDS]</span></td>";
					}
					if ($CPARAC=="Y") {
						$F_AcctSub=Format_Acct($row['PSARAC'], $row['PSARSB'],"N");
						print "\n     <td class=\"colalph\" $helpCursor><span title=\"$F_AcctSub\">$row[AR_CHCHDS]</span></td> ";
					}
					if ($CPOFAC=="Y") {
						$F_CoFac=Format_CoFac($row['PSOFCO'], $row['PSOFFC'],"N");
						print "\n <td class=\"colalph\" $helpCursor><span title=\"$F_CoFac\">$row[CFCFNM]</span></td>";
						$F_AcctSub=Format_Acct($row['PSOFAC'], $row['PSOFSB'],"N");
						print "\n     <td class=\"colalph\" $helpCursor><span title=\"$F_AcctSub\">$row[OFF_CHCHDS]</span></td> ";
					}
					print "\n     <td class=\"colalph\">$row[PSSTDS]</td> ";
					$F_PSDTDE=Format_Date_ISO($row['PSDTDE'], "D");
					print "\n     <td class=\"colalph\">$F_PSDTDE</td> ";
					print "\n </tr> ";

					$startRow ++;
					$rowCount ++;
				}
				if ($rowCount==0) {require 'QuickLinkNoInfoMsg.php';}
				
				print "\n </table> ";
				print "\n </fieldset> ";
			}

			// *****************************************************************************
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