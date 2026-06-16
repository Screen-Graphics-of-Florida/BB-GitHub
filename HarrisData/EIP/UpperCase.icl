%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Check UpperCase                                             *
*********************************************************************
%}
  function chkUpper(fld){
    if(/[^0-9A-Z]/.test(fld.value)){
        fld.value=fld.value.toUpperCase().replace(/([^0-9A-Z])/g,"");
    }
  }