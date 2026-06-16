<?php

if ($submitSchedule == "S") {
	if ($errFound == "") {
		$returnValue=Env_Overrides($userProfile, $applicationID, $envProgram, $envPrinter, $V_J_JNAM, $V_J_JOBD, $V_J_JOBQ, $V_J_OUTQ, $envError);
		$envJobName        = $returnValue['envJobName'];
		$envJobDescription = $returnValue['envJobDescription'];
		$envJobQueue       = $returnValue['envJobQueue'];
		$envError          = $returnValue['envError'];
		$V_J_JFRQ = "*ONCE";
		$V_J_JTIM = "*CURRENT";
		$V_J_JDAT = "*CURRENT";
		$V_J_JDAY = "*NONE";
	}

	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Schedule Parameters</legend> ";
	print "\n <table $contentTable> ";
	print "\n <tr><td> ";
	print "\n <table $contentTable> ";

	$textOvr=SetTextOvr($Err_J_JNAM);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Job Name</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"schJobName\" value=\"" . rtrim($V_J_JNAM) . "\" size=\"12\" maxlength=\"10\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_J_JNAM);

	$textOvr=SetTextOvr($Err_J_JOBD);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Job Description</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"schJobDescription\" value=\"" . rtrim($V_J_JOBD) . "\" size=\"12\" maxlength=\"10\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_J_JOBD);

	$textOvr=SetTextOvr($Err_J_JOBQ);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Job Queue</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"schJobQueue\" value=\"" . rtrim($V_J_JOBQ) . "\" size=\"12\" maxlength=\"10\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_J_JOBQ);

	$fieldDesc=RetValue("FLTYPE='SCHFREQPHP' and FLVALU='$V_J_JFRQ'", "SYFLAG", "FLDESC");
	$textOvr=SetTextOvr($Err_J_JFRQ);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Frequency</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"schFrequency\" value=\"" . rtrim($V_J_JFRQ) . "\" size=\"12\" maxlength=\"8\"> ";
	print "\n                             <a href=\"{$homeURL}{$phpPath}FlagSearch.php{$genericVarBase}&amp;flagType=SCHFREQPHP&amp;flagSrchHdr=" . urlencode("Frequency") . "&amp;docName=Chg&amp;fldName=schFrequency&amp;fldDesc=schFrequencyDesc\" onclick=\"$searchWinVar\"> $reqFieldChar $searchImage</a> ";
	print "\n                             <span class=\"dspdesc\" id=\"schFrequencyDesc\">$fieldDesc</span></td>";
	print "\n </tr> ";
	DspErrMsg($Err_J_JFRQ);

	$textOvr=SetTextOvr($Err_J_JTIM);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Schedule Time</span></td> ";
	print "\n     <td class=\"inputalph\"><input name=\"schTime\" type=\"text\" value=\"" . rtrim($V_J_JTIM) . "\" size=\"12\" maxlength=\"8\"></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_J_JTIM);
	
	print "\n </table></td> ";
	print "\n <td><table $contentTable> ";
	
	$textOvr=SetTextOvr($Err_J_JDAT);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>Schedule Date</span></td> ";
	print "\n     <td class=\"inputalph\"><input type=\"text\" name=\"schDate\" value=\"" . rtrim($V_J_JDAT) . "\" size=\"12\" maxlength=\"9\"> ";
	print "\n                             <a href=\"javascript:calWindow('schDate');\">$calendarImage</a></td> ";
	print "\n </tr> ";
	DspErrMsg($Err_J_JDAT);

	require 'ScheduleDaysTable';
	if (trim($V_J_JDAY)) {$V_J_JDAY = "*NONE";}
	$textOvr=SetTextOvr($Err_J_JDAY);
	print "\n <tr><td class=\"dsphdr\"><span $textOvr>or Schedule Days</span></td> ";
	print "\n <SELECT NAME=\"schDays\" SIZE=\"9\" MULTIPLE> ";
	$x      = 1;
	while ($x <= $Count) {
		$scheduleRow = db2_fetch_assoc($schDaysTable, $x);
		$FLVALU      =trim($scheduleRow['FLVALU']);
		$FLDESC      =trim($scheduleRow['FLDESC']);
		if (strpos($V_J_JDAY,$FLDESC) !== false) {$schOption = "SELECTED";}
		else                                     {$schOption = "";}

		print "\n <OPTION $schOption value=\"" . rtrim($FLDESC) . "\">$FLDESC ";

		$x ++;
	}
	print "\n </SELECT> ";
	print "\n </tr> ";
	DspErrMsg($Err_J_JDAY);

	print "\n </table></td></tr> ";
	print "\n </table> ";
	print "\n </fieldset> ";
}
?>