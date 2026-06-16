%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Check To See If Enter Key Pressed                           *
*********************************************************************
%}

%INCLUDE "NewWindowOpen.icl"

function goToPage(selectedPage) {if (selectedPage != "") {self.location=selectedPage;}}

function checkEnterChg() {
  document.onkeyup = function(e){
   if (!e) {
     var k = (e) ? e.which:event.keyCode;
     var t = (e) ? e.which:event.srcElement.type;
   } else {
     var node = e.target;
     while(node.nodeType != node.ELEMENT_NODE)
	        node = node.parentNode;
     var t = node.type;
     var k = e.which;
   }
     if (k == 13 && t != 'textarea')
         check(document.Chg);
  }
}

function check(chgForm) {
    if (validate(chgForm))
        chgForm.submit();
}