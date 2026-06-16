
sfHover = function() {
	if (document.getElementById("nav")) {
		var sfEls = document.getElementById("nav").getElementsByTagName("LI");
		for (var i=0; i<sfEls.length; i++) {
			sfEls[i].onmouseover=function() {
				this.className+=" sfhover";
			}
			sfEls[i].onmouseout=function() {
				this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
			}
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

function getMenu($c, $e, $i, $r, $p, $id) {
	if ($i !== "") {htmlLoad = '<img src="/images/ajax-loader.gif" alt="Loading... " class="loading">';}
	else {htmlLoad = 'Loading... ';}
	menu=document.getElementById("container");
	menu.innerHTML=htmlLoad;
	if(window.XMLHttpRequest) {
		xmlMenu=new XMLHttpRequest();
	}else{
		xmlMenu=new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlMenu.onreadystatechange=function(){
		if (xmlMenu.readyState==4 && xmlMenu.status==200){
			menu = document.getElementById("container")
			menu.innerHTML=xmlMenu.responseText;
		}
	}
	menuURL = "/getMenu.php?baseVar="+$c+"&eID="+$e+"&activeRole="+$r+"&portal="+$p+"&id="+$id+""
	xmlMenu.open("GET", menuURL, true);
	xmlMenu.send("");
}
