%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Setup AJAX Request                                          *
*********************************************************************
%}
function getXMLHTTPRequest() {
  var request = null;
  try {request = new XMLHttpRequest();}
  catch (trymicrosoft) {
     try {request = new ActiveXObject("Msxml2.XMLHTTP");}
     catch (othermicrosoft) {
       try {request = new ActiveXObject("Microsoft.XMLHTTP");}
       catch (failed) {request = false;}
     }
  }
  if (!request) {alert("Error initializing XMLHttpRequest!");}
  else          {return request;}
}

function ajaxObject(url, callbackFunction) {
  var that=this;
  this.updating = false;
  this.abort = function() {
    if (that.updating) {
      that.updating=false;
      that.AJAX.abort();
      that.AJAX=null;
    }
  }

  this.update = function(passData,postMethod) {
    if (that.updating) {return false;}

    that.AJAX = null;
    that.AJAX = new getXMLHTTPRequest();
    if (that.AJAX==null) {return false;
    } else {
      that.AJAX.onreadystatechange = function() {
        if (that.AJAX.readyState==4) {
          that.updating=false;
          that.callback(that.AJAX.responseText,that.AJAX.status,that.AJAX.responseXML);
          that.AJAX=null;
        }
      }
      that.updating = new Date();
      if (/post/i.test(postMethod)) {
        var uri=urlCall+'?'+that.updating.getTime();
        that.AJAX.open("POST", uri, true);
        that.AJAX.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        that.AJAX.setRequestHeader("Content-Length", passData.length);
        that.AJAX.send(passData);
      } else {
        var uri=urlCall+'&timestamp='+(that.updating.getTime());
        that.AJAX.open("GET", uri, true);
        that.AJAX.send(null);
      }
      return true;
    }
  }
  var urlCall = url;
  this.callback = callbackFunction || function () { };
}
