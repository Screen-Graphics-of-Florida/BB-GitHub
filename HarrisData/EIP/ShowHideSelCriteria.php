function showSel(object) {
  if (document.getElementById) {document.getElementById(object).style.visibility = 'visible';}
  else if (document.layers && document.layers[object]) {document.layers[object].visibility = 'visible';}
  else if (document.all) {document.all[object].style.visibility = 'visible';}
}

function hideSel(object) {
  if (document.getElementById) {document.getElementById(object).style.visibility = 'hidden';}
  else if (document.layers && document.layers[object]) {document.layers[object].visibility = 'hidden';}
  else if (document.all) {document.all[object].style.visibility = 'hidden';}
}
