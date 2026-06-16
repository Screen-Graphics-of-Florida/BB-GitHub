%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Page Title Maintenance Display                              *
*********************************************************************
%}
  <a href="javascript:check(document.Chg)">$(acceptImageMed)</a>
  %if (backURL != "")
      <a href="$(backURL)">$(cancelImageMed)</a>
  %else
      <a href="javascript:history.back()">$(cancelImageMed)</a>
  %endif

  %if (sec_03 == "Y" && maintenanceCode == "C")
      <a onClick="return confirmDelete()" href="$(deleteURL)">$(deleteImageMed)</a>
  %endif
  @dtw_assign(medIcon, "Y")
  %INCLUDE "HelpPage.icl"