%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Trailer Include                                             *
*********************************************************************
%}

  %if (formatToPrint == "" || formatToPrint == "N")
      %if (popUpWin == "Y")
          %INCLUDE "$(popTrailer)"
      %else
          %INCLUDE "$(trailer)"
      %endif
  %else
      %INCLUDE "$(fmtTrailer)"
  %endif