%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Header Inclusion Tags                                       *
*********************************************************************
%}

%INCLUDE "Meta.icl"
    %if (meta_title == "")
        @dtw_assign(meta_title, page_title)
    %endif
	   <title>$(meta_title) - $(title)</title>
    %if (formatToPrint == "Y")
        <link rel=stylesheet type="text/css" href="$(homeURL)$(homePath)$(fmtStyleSheet)">
    %else
        <link rel=stylesheet type="text/css" href="$(homeURL)$(homePath)$(casStyleSheet)">
    %endif
    <link rel=stylesheet type="text/css" href="$(homeURL)$(homePath)$(menuStyleSheet)">
    <link rel=stylesheet type="text/css" href="$(homeURL)$(homePath)$(qlinkStyleSheet)">
    <link rel=stylesheet type="text/css" href="$(homeURL)$(homePath)$(tabStyleSheet)">
    
    %if (touchScreen == "Y")
        <link rel=stylesheet type="text/css" href="$(homeURL)$(homePath)$(touchStyleSheet)">
    %endif    

    %INCLUDE "ExitStyleSheet.icl"