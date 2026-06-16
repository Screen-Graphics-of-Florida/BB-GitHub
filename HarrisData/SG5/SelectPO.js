
function getPO($c, $v, $n, $po) {
	htmlLoad = '<img src="/images/ajax-loader.gif" alt="Loading... " class="loading">';
	openorder=document.getElementById("poopenorder");
	openorder.innerHTML=htmlLoad;
	if(window.XMLHttpRequest) {xmlPO=new XMLHttpRequest();}
	else {xmlPO=new ActiveXObject("Microsoft.XMLHTTP");}

	xmlPO.onreadystatechange=function(){
		if (xmlPO.readyState==4 && xmlPO.status==200){
			openorder = document.getElementById("poopenorder")
			openorder.innerHTML=xmlPO.responseText;
		}
	}
	openorderURL = "/SelectPOOpen.php?baseVar="+$c+"&vendorNumber="+$v+"&vendorName="+$n+"&purchaseOrderNumber="+$po+""
	xmlPO.open("GET", openorderURL, true);
	xmlPO.send("");
}

function getPOHistory($c, $v, $n, $po, $sq) {
	htmlLoad = '<img src="/images/ajax-loader.gif" alt="Loading... " class="loading">';
	orderhistory=document.getElementById("pohistory");
	orderhistory.innerHTML=htmlLoad;
	if(window.XMLHttpRequest) {xmlHistory=new XMLHttpRequest();}
	else {xmlHistory=new ActiveXObject("Microsoft.XMLHTTP");}

	xmlHistory.onreadystatechange=function(){
		if (xmlHistory.readyState==4 && xmlHistory.status==200){
			orderhistory = document.getElementById("pohistory")
			orderhistory.innerHTML=xmlHistory.responseText;
		}
	}
	orderhistoryURL = "/SelectPOHistory.php?baseVar="+$c+"&vendorNumber="+$v+"&vendorName="+$n+"&purchaseOrderNumber="+$po+"&orderSequence="+$sq+""
	xmlHistory.open("GET", orderhistoryURL, true);
	xmlHistory.send("");
}

function getPOHeader($c, $v, $n, $po) {
	htmlLoad = '<img src="/images/ajax-loader.gif" alt="Loading... " class="loading">';
	myheader=document.getElementById("poheader");
	myheader.innerHTML=htmlLoad;
	if(window.XMLHttpRequest) {xmlHeader=new XMLHttpRequest();}
	else {xmlHeader=new ActiveXObject("Microsoft.XMLHTTP");}

	xmlHeader.onreadystatechange=function(){
		if (xmlHeader.readyState==4 && xmlHeader.status==200){
			myheader = document.getElementById("poheader")
			myheader.innerHTML=xmlHeader.responseText;
		}
	}
	myheaderURL = "/SelectPOHeader.php?baseVar="+$c+"&vendorNumber="+$v+"&vendorName="+$n+"&purchaseOrderNumber="+$po+""
	xmlHeader.open("GET", myheaderURL, true);
	xmlHeader.send("");
}

function getPOComments($c, $v, $n, $po) {
	htmlLoad = '<img src="/images/ajax-loader.gif" alt="Loading... " class="loading">';
	mycomments=document.getElementById("pocomments");
	mycomments.innerHTML=htmlLoad;
	if(window.XMLHttpRequest) {xmlComments=new XMLHttpRequest();}
	else {xmlComments=new ActiveXObject("Microsoft.XMLHTTP");}

	xmlComments.onreadystatechange=function(){
		if (xmlComments.readyState==4 && xmlComments.status==200){
			mycomments = document.getElementById("pocomments")
			mycomments.innerHTML=xmlComments.responseText;
		}
	}
	mycommentsURL = "/SelectPOComments.php?baseVar="+$c+"&vendorNumber="+$v+"&vendorName="+$n+"&purchaseOrderNumber="+$po+""
	xmlComments.open("GET", mycommentsURL, true);
	xmlComments.send("");
}

function getPOFlags($c, $v, $n, $po) {
	htmlLoad = '<img src="/images/ajax-loader.gif" alt="Loading... " class="loading">';
	mypoflags=document.getElementById("poflags");
	mypoflags.innerHTML=htmlLoad;
	if(window.XMLHttpRequest) {xmlFlags=new XMLHttpRequest();}
	else {xmlFlags=new ActiveXObject("Microsoft.XMLHTTP");}

	xmlFlags.onreadystatechange=function(){
		if (xmlFlags.readyState==4 && xmlFlags.status==200){
			mypoflags = document.getElementById("poflags")
			mypoflags.innerHTML=xmlFlags.responseText;
		}
	}
	mypoflagsURL = "/SelectPOFlags.php?baseVar="+$c+"&vendorNumber="+$v+"&vendorName="+$n+"&purchaseOrderNumber="+$po+""
	xmlFlags.open("GET", mypoflagsURL, true);
	xmlFlags.send("");
}

function getPOLine($c, $v, $n, $po, $ln, $rl) {
	htmlLoad = '<img src="/images/ajax-loader.gif" alt="Loading... " class="loading">';
	podetail=document.getElementById("poline");
	podetail.innerHTML=htmlLoad;
	if(window.XMLHttpRequest) {xmlDetail=new XMLHttpRequest();}
	else {xmlDetail=new ActiveXObject("Microsoft.XMLHTTP");}

	xmlDetail.onreadystatechange=function(){
		if (xmlDetail.readyState==4 && xmlDetail.status==200){
			podetail = document.getElementById("poline")
			podetail.innerHTML=xmlDetail.responseText;
		}
	}
	podetailURL = "/SelectPODetail.php?baseVar="+$c+"&vendorNumber="+$v+"&vendorName="+$n+"&purchaseOrderNumber="+$po+"&lineNumber="+$ln+"&releaseNumber="+$rl+""
	xmlDetail.open("GET", podetailURL, true);
	xmlDetail.send("");
}
