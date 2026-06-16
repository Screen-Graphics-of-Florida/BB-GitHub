<?php

function Menu_Set_URL($workURL){
	$returnValue['workURL']=$workURL;
	return $returnValue;
}

function  Menu_Query ($profileHandle, $dataBaseID, $portal, $pageID, $userProfile) {
	$SAVE_ROW_NUM =  (string) $startRow;
	$SAVE_MAX_ROWS=  (string) $maxRows;
	$startRow =  (string) '1';
	$maxRows =  (string) '9999';
	$svport = "";
	$svpage = "";
	if (!$pageID) {$pageID="noPageID";}

	global $pgmLibrary, $accessErrorDesc, $menuTable, $activeRole, $homeURL, $cGIPath, $phpPath, $homePath, $displayMenuImages, $imagePath, $baseVar, $eID, $i5Connect, $storedProcedureToCall, $dataBaseID, $commentWinVar, $inquiryWinVar, $invoiceWinVar, $newsLink;

	// Access to the menu system was denied
	if ($activeRole == ""){
		print "<div class=\"accessError\">No Access To Menu Items $accessErrorDesc </div>";
		exit;
	} else{
		$portalByRoleCnt= RetValue("PRROLE='$activeRole'", "SYPORR", "count(*)");
		require 'stmtSQLClear.php';
		$stmtSQL .= " Select FPPORT, FPPAGE, FPDESC, FPTITL, FUTRGT, FUDESC, FUTITL, FUURL, FUIMG, ";
		$stmtSQL .= " Case When FPPAGE=' ' Then 1 When FPPORT=FPPAGE Then 2 Else 3 End as TYPE ";
		$fileSQL .= " SYROLD inner join SYPORT on FPPORT=RDPORT ";
		$fileSQL .= "        inner join SYURLM on FUID=FPID     ";
		if ($portalByRoleCnt){$fileSQL .= "  inner join SYPORR on RDROLE=PRROLE and FPPORT=PRPORT and FPPAGE=PRPAGE and FPSEQ=PRSEQ ";}
		$selectSQL .= "(((RDROLE='{$activeRole}') and (FPPAGE='' or FPPAGE=FPPORT)) or ";
		$selectSQL .= "(RDROLE='{$activeRole}' and FPPORT='{$portal}' and FPPAGE='{$pageID}'))";
		if ($portalByRoleCnt){$selectSQL .= " and PRSEL='Y' ";}
		$stmtSQL .= " From $fileSQL";
		if (trim($selectSQL) != ""){$stmtSQL .= " Where {$selectSQL}";}
		$stmtSQL .= " Order By RDSEQN,RDPORT,TYPE,FPPAGE,FPSEQ";
		$sql_Record_Count = 0;
		$startRow = $SAVE_ROW_NUM;
		$maxRows =$SAVE_MAX_ROWS;
		$stmtSQL .= "  For Fetch Only with NC";
		// require 'stmtSQLEnd.php';
		require 'stmtSQLTotalRows.php';
		$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

		print "\n <!-- Start Of Menu Code --> \n";
		print "\n <div id=\"container\">";
		print "<ul id=\"nav\">";
		while ($row = db2_fetch_assoc($sqlResult)){
			if ($row =="0"){
				print  "\n <div class=\"accessError\">No Access To Menu Items $accessErrorDesc</div>";
				exit;
			} else {
				$V_FPPORT = rtrim($row['FPPORT']);
				$V_FPPAGE = rtrim($row['FPPAGE']);
				$V_FPDESC = rtrim($row['FPDESC']);
				$V_FUDESC = rtrim($row['FUDESC']);
				$V_FUURL  = rtrim($row['FUURL']);
				$V_FUTRGT = rtrim($row['FUTRGT']);
				$V_FPTITL = rtrim($row['FPTITL']);
				$V_FUTITL = rtrim($row['FUTITL']);

				if ($V_FUTRGT == "")  {$tgt =  "";}
				elseif (strtoupper($V_FUTRGT) == "COMMENT"){$tgt = " onclick=\"$commentWinVar\"";}
				elseif (strtoupper($V_FUTRGT) == "INQUIRY"){$tgt = " onclick=\"$inquiryWinVar\"";}
				elseif (strtoupper($V_FUTRGT) == "INVOICE"){$tgt = " onclick=\"$invoiceWinVar\"";}
				elseif ($V_FUTRGT != "")                   {$tgt = " target=\"$V_FUTRGT\"";}

				if ($V_FPDESC != ""){$desc = trim($V_FPDESC);}
				else                {$desc = trim($V_FUDESC);}

				if ($V_FPTITL != ""){$titl = trim($V_FPTITL);}
				else                {$titl = trim($V_FUTITL);}

				$workURL  = trim($V_FUURL);
				$poshomeURL= strpos($workURL, "@@homeURL");
				$newsLinkPos = strpos($workURL, "@@newsLink");
				if ($newsLinkPos >= 0){
					$workURL = str_replace("@@newsLink", $newsLink, $workURL);
				}

				$phpPos = strpos(strtoupper($workURL), ".PHP");
				if ($phpPos>0) {
					$baseVarWrk = $baseVar;
					$workURL = str_replace("@@phpPath", $phpPath, $workURL);
				}else{
					$phpPos = strpos(strtoupper($baseVar), ".PHP");
					$baseVarWrk = substr($baseVar, 0, $phpPos);
					$baseVarWrk .= ".icl";
					$workURL = str_replace("@@cGIPath", $cGIPath, $workURL);
				}
				$workURL = str_replace("@@SGHelpPath", $SGHelpPath, $workURL);
				$workURL = str_replace("@@homeURL", $homeURL, $workURL);
				$workURL = str_replace("@@helpPath", $helpPath, $workURL);
				$profileHandleURL = urlencode($profileHandle);
				$workURL = str_replace("@@prfh", $profileHandleURL, $workURL);
				$userProfile = urlencode($_SERVER['PHP_AUTH_USER']);
				$workURL = str_replace("@@userProfile", $userProflie, $workURL);
				if (strpos($workURL, "@@meapid") !== false){
					if ($HDMERL>"0") {$meapid = "ME";}
					else             {$meapid = "ET";}
					$workURL = str_replace("@@meapid", $meapid, $workURL);
				}
				if (strpos($workURL, "?") !== false){$workAmp = "&amp;";}
				else                                {$workAmp = "?";}

				if ($poshomeURL>= 0){
					$pos= strpos($workURL, "@@baseVar");
					if ($pos === false){
						$workURL = trim($workURL);
						$workURL .= "{$workAmp}baseVar=" . urlencode($baseVarWrk) . "&amp;eID=" . urlencode($eID);
						$workAmp = "&amp;";
					}else{
						$baseVarURL = urlencode($baseVarWrk);
						$workURL = str_replace("@@baseVar", $baseVarURL, $workURL);
					}
					$pos= strpos($workURL, "@@browser");
					if ($pos){$workURL = str_replace("@@browser", $browser, $workURL);}

					$pos= strpos($workURL, "@@portal");
					if ($pos === false){
						$workURL .= "{$workAmp}portal=". trim($V_FPPORT);
					}else{
						$V_FPPORTURL = urlencode($V_FPPORT);
						$workURL = str_replace("@@portal", $V_FPPORTURL, $workURL);
					}
				}

				$workURL=str_replace("@@timeStamp", urlencode($_SERVER['REQUEST_TIME']), $workURL);
				$returnValue=Menu_Set_URL($workURL);
				$workURL=$returnValue['workURL'];

				if (trim($V_FPPORT) == trim($portal)){$menuClass = "curMenu";}
				else {$menuClass = "";}

				if ($svport != "" && $svport != $V_FPPORT) {
					if ($svpage != "") {
						if ($svpage != $pageID) {$endUL=0; print "\n </ul></li>";}
						$svpage = "";
					}
				}

				if ($V_FPPAGE != "") {
					if ($svpage != "" && $svpage != $V_FPPAGE && $svpage != $pageID) {$endUL=0; print "\n </ul></li>";}
					if ($svpage == "" ||  $svpage != $V_FPPAGE && $V_FPPAGE != $pageID) {if ($endUL) {print "\n </ul>";} $endUL=1; print "\n <ul>";}
					$svpage = $V_FPPAGE;
				}

				if ($svport != $V_FPPORT && $V_FPPAGE != $pageID) {
					if ($menuClass != "") {print "\n <li class=\"{$menuClass}\">";}
					else {print "\n <li>";}
				}

				if   ($V_FPPAGE != "" && $V_FPPAGE == $pageID) {$svport = "";}
				else {$svport = $V_FPPORT;}

				if ($V_FUURL == ""){
					if (($V_FUIMG != "") and ($displayMenuImages == "Y")) {print "\n <img border=$imageBorder src=\"$homeURL $imagePath $V_FUIMG\" alt=\"$desc\">";}
					else {print "\n <a href=\"JavaScript:void(0);\" title=\"$titl\">$desc</a>";}
				}
				elseif (($V_FUIMG != "" ) and ($displayMenuImages == "Y")){
					print "\n <a class=\"$menuClass\" href=\"$workURL\"$tgt title=\"$titl\"><img border=$imageBorder src=\"$homeURL $imagePath $V_FUIMG\" alt=\"$desc\"></a>";
				}
				elseif ($V_FPPAGE != ""){
					if ($V_FPPAGE == $pageID) {print "\n <li class=\"sub1Menu\"><a href=\"$workURL\"$tgt title=\"$titl\">$desc</a>";}
					else                      {print "\n <li class=\"sub2Menu\"><a href=\"$workURL\"$tgt title=\"$titl\">$desc</a></li>";}
				}
				else {print "\n <a href=\"$workURL\"$tgt title=\"$titl\">$desc</a>";}
			}
		}
		if ($svpage != "") {
			if ($svpage != $pageID) {$endUL=0; print "\n </ul></li>";}
			$svpage = "";
		}
		$roleCount=RetValue("RUUSER='$userProfile'", "SYROLU", "count(*)");
		if ($roleCount){
			$baseVarURL = urlencode($baseVar);
			$eIDURL     = urlencode($eID);
			$portalURL  = urlencode($portal);
			print "\n <li class=\"{$menuClass}\">";
			print "<a href=\"javascript:;\"  title=\"Click here to change roles\" onClick=\"window.open('{$homeURL}{$phpPath}RoleSelect.php?baseVar=" . urlencode($baseVarURL) . "&amp;eID=" . urlencode($eIDURL) . "&amp;portal=" . urlencode($portal) . "&amp;tag=REPORT','role_win','height=500,width=600,top=100,left=200,resizable'); return false\">Change Role</a>";
		}
		print "</ul>";
		print "\n </div>";
		print "\n <!-- End Of Menu Code -->\n ";
	}
}

?>
