<?php
require_once 'NewWindowOpen.php';
?>

function goToPage(selectedPage) {
  if (selectedPage != "") {self.location=selectedPage;}
}

function checkEnterAjax(onSubmitFunction) {
  document.onkeyup = function(e){
     var k = (e) ? e.which:event.keyCode;
     var t = (e) ? e.which:event.srcElement.type;
     if (k == 13 && t != 'textarea') {onSubmitFunction(); }
  }
}
