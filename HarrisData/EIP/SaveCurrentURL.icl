%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Save Current URL Include                                    *
*********************************************************************
%}
 function saveCurrentURL() {
    var url = "$(homeURL)$(cGIPath)SaveCurrentURL.d2w/SAVE?baseVar=@dtw_rurlescseq(baseVar)&eID=@dtw_rurlescseq(eID)&currentURL=@dtw_rurlescseq(currentURL)";
    request.open("GET", url, false);
    request.send(null);
 }
