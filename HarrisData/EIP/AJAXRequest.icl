%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Setup AJAX Request                                          *
*********************************************************************
%}
  var request = false;
  try {
     request = new XMLHttpRequest();
  } catch (trymicrosoft) {
     try {
        request = new ActiveXObject("Msxml2.XMLHTTP");
     } catch (othermicrosoft) {
       try {
          request = new ActiveXObject("Microsoft.XMLHTTP");
       } catch (failed) {
          request = false;
       }
     }
  }
  if (!request)
     alert("Error initializing XMLHttpRequest!");

<!--  if (request) alert("Created Request");   -->
