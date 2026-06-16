
function checkSel(colSel) {
	var curSel = colSel.split('|');
	hideSel('calSrch'); hideSel('flagSrch');
	if (!curSel[1]){return;}
	if (curSel[1] == 'DATE'){showSel('calSrch'); return;}
	if (curSel[1] != '' && curSel[1] != 'null'){
		var url = '<?php print "<a id=\"flagSrch\" style=\"position: absolute; visibility: hidden;\" href=\"" . $homeURL . $phpPath . "FlagSearch.php" . $genericVarBase . "&amp;tag=REPORT&amp;docName=Search&amp;fldName=qsValue&amp;flagType=' + escape(curSel[1])+ '&amp;flagSrchHdr=' + escape(curSel[2]) + '\" onclick=\"NewWindow(this.href,\'flag_search_win\',\'70\',\'60\',\'yes\',\'yes\',\'no\',\'no\',\'no\');return false;\">$searchDesc<\/a>"?>';
		document.getElementById('fsURL').innerHTML=url;
		showSel('flagSrch');
	}
}
