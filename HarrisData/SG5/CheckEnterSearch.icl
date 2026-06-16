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

function checkEnterSearch() {
  document.onkeyup = function(e){
     var k = (e) ? e.which:event.keyCode;
     var t = (e) ? e.which:event.srcElement.type;
     if (k == 13 && t != 'textarea')
         check(document.Search);
  }
}

function check(searchForm) {
    if (validate(searchForm))
        searchForm.submit();
}