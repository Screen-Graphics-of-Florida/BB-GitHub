<?php
if ($quicklinkRef == "attachments" || $quickLinksInUse == "N") {
	$attachFolderU = strtoupper($attachFolder);
	require 'QuickLinkClear.php';
	require 'stmtSQLClear.php';
	$stmtSQL    =  " Select SYD2WA.*, Coalesce(USDESC,' ') as USDESC ";
	$fileSQL    =  "  SYD2WA ";
	$fileSQL   .= " left join SYUSER on ATUSER=USUSER ";
	$selectSQL  = " ATFOLD<>' ' and ATFOLD='{$attachFolderU}' and ATVKEY='{$attachVarKey}'";
	$selectSQL .= "  and (ATUSER='{$userProfile}' or ATPRIV=' ' or '$admin' ='Y')";
	require 'stmtSQLSelect.php';
	$orderBy="ATDESCU,ATATNSU";
	$stmtSQL   .= " Order By $orderBy";
	require 'stmtSQLEnd.php';
	require 'stmtSQLTotalRows.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($quickLinksInUse != "N") {
		print "\n <a name=\"attachments\"></a> ";
		$moreWinVar=$selectionWinVar;
		$moreURL="{$homeURL}{$phpPath}Attachment.PHP{$scriptVarBase}&amp;attachFolder=" . urlencode($attachFolder) . "&amp;attachForDesc=" . urlencode($attachForDesc) . "&amp;attachVarKey=" . urlencode($attachVarKey) . "&amp;userProfile=" . urlencode($userProfile) . "&amp;attachPrg1=" . urlencode($attachPrg1) . "&amp;attachPrg2=" . urlencode($attachPrg2) . "&amp;attachPrg3=" . urlencode($attachPrg3) . "&amp;attachPrg4=" . urlencode($attachPrg4) . "&amp;attachPrg5=" . urlencode($attachPrg4);
		require 'QuickLinkTopOfForm.php';
	} else {
		print "\n <fieldset class=\"legendBody\"> ";
		print "\n     <legend class=\"legendTitle\">Attachments</legend> ";
	}

	print "\n <table $contentTable>";

	$rowCount = 0;
	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult, $startRow)){
		if ($startRow==1) {
			print "\n <tr> ";
			Format_Column_Header("ATDESCU", "Description");
			Format_Column_Header("ATATNSU", "Attachment Name");
			Format_Column_Header("ATUSER", "User");
			Format_Column_Header("date(ATTSTP)", "Date");
			Format_Column_Header("time(ATTSTP)", "Time");
			if ($attachFolderU == "DOCUMENT") {
				Format_Column_Header("ATBODY", "Body");
			}
			print "\n </tr> ";
		}

		if ($rowCount >= $dspMaxRows) {break;}
			
		$attDate = TimeStamp_CYMD($row[ATTSTP]);
		$attDate = Format_Date($attDate, "D");
		$attTime = TimeStamp_TIME($row[ATTSTP]);
		$attTime = EditHrsMinSec($attTime);
		require 'SetRowClass.php';
		print "\n <tr class=\"$rowClass\"> ";
		print "\n     <td class=\"colalph\">$row[ATDESC]</td>";
		$longName = trim($row['ATATNL']);
		if (trim($row['ATDIRL']) != "Y") {$longName = "{$homePath}{$longName}";}
		$fileFound = file_exists($longName);
		if ($fileFound) {print "\n     <td class=\"colalph\"><a href=\"{$longName}\" target=_blank title=\"Click here to view attachment\">$row[ATATNS]</a></td>";}
		else            {print "\n     <td class=\"colalph\">$row[ATATNS]</td>";}
		print "\n     <td class=\"colalph\">$row[USDESC]</td>";
		print "\n <td class=\"colalph\">$attDate</td>";
		print "\n <td class=\"colalph\">$attTime</td>";
		if ($attachFolderU == "DOCUMENT") {
			if ($row[ATBODY] == "Y") {$bodyImage=$checkImage;} else {$bodyImage="&nbsp;";}
			print "\n <td class=\"colcode\">$bodyImage</td>";
		}
		print "\n </tr>";

		$startRow ++;
		$rowCount ++;
	}
	if ($rowCount==0) {require 'QuickLinkNoInfoMsg.php';}
	
	print "\n </table> ";
	print "\n </fieldset> ";
}
?>	
									