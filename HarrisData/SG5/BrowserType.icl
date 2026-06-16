%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Browser Type                                                *
*********************************************************************
%}

%Define {
  HTTP_USER_AGENT = %ENVVAR
%}

%MACRO_FUNCTION BrowserType (INOUT browserCode) {
  %if ( @dtw_rpos("MSIE", HTTP_USER_AGENT) > "0")
      @DTW_ASSIGN(browserCode, "IE")
  %elif ( @dtw_rpos("UPG1", HTTP_USER_AGENT) > "0")
      @DTW_ASSIGN(browserCode, "MB")
  %elif ( @dtw_rpos("Netscape", HTTP_USER_AGENT) > "0")
      @DTW_ASSIGN(browserCode, "NS")
  %elif ( @dtw_rpos("Firefox", HTTP_USER_AGENT) > "0")
      @DTW_ASSIGN(browserCode, "FF")
  %else
      @DTW_ASSIGN(browserCode, "XX")
  %endif

%}