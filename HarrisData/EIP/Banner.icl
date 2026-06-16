%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Banner Code                                                 *
*********************************************************************
%}
    %if (formatToPrint != "" && formatToPrint != "N")
        %INCLUDE "$(fmtBanner)"
    %elif (popUpWin == "Y")
        %INCLUDE "$(popBanner)"
    %else
        %INCLUDE "$(banner)"
    %endif