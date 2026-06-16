%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Check To See If Enter Key Pressed                           *
*********************************************************************
%}

%INCLUDE "NewWindowOpen.icl"

function checkEnterAdd() {
  document.onkeyup = function(e){
     var k = (e) ? e.which:event.keyCode;
     var t = (e) ? e.which:event.srcElement.type;
     if (k == 13 && t != 'textarea')
         check(document.Add);
  }
}

function check(addForm) {
    if (validate(addForm))
        addForm.submit();
}