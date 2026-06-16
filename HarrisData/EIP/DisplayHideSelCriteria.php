function showSel(object) {
  if (document.getElementById) {document.getElementById(object).style.display='inline';}
  else if (document.layers && document.layers[object]) {document.layers[object].display='inline';}
  else if (document.all) {document.all[object].style.display='inline';}
}

function hideSel(object) {
  if (document.getElementById) {document.getElementById(object).style.display='none';}
  else if (document.layers && document.layers[object]) {document.layers[object].display='none';}
  else if (document.all) {document.all[object].style.display='none';}
}
