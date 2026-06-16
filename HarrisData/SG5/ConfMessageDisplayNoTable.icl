%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Confirmation Message Display                                *
*********************************************************************
%}
  %if (confMessage !="")
      <h2>$(confMessage)</h2>
      @dtw_assign(confMessage, "")
  %endif