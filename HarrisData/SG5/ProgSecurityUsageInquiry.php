<?php
if ($scriptName != ""){
	if ($allowSecInq == "Y"){
		$scriptNameU = strtoupper($scriptName);
		$progSecCnt=RetValue("PSPGTP='SCRIPT' and PSPGNMU LIKE '{$scriptNameU}%' and (PSPOSP<>' ' or PSUVFN<>' ')", "SYPSUM", "count(*)");
		if ($progSecCnt){
			print "<a href=\"{$homeURL}{$cGIPath}ProgSecurityUsageInquiry.d2w/REPORT{$altVarBase}&amp;progType=SCRIPT&amp;progName=" . urlencode($scriptName) . "&amp;orderBy=PSDESCU&amp;formatToPrint=Y&amp;hideSelectCriteria=Y\" onclick=\"{$inquiryWinVar}\">{$securityInqImage}</a>";
		}
	}
}
?>