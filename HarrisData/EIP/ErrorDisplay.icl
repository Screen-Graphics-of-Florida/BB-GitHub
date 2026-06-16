%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Error Message Display                                       *
*********************************************************************
%}
  %if (errFound != "")
      <span class="error" $(textOvr)> &nbsp; &nbsp; Please correct all errors</span>
  %endif