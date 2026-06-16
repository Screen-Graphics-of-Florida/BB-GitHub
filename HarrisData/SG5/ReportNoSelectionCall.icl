%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Build Request Data Call                                     *
*********************************************************************
%}

  @dtw_assign(reportRequestData, "CALL $(reportCallProgram) PARM('BROWSER' '$(userProfile)' '  ')")
