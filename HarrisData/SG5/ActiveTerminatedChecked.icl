%{
**********************************************************************
*  Copr 1979 2002 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Set Active/Terminated Check Box                              *
**********************************************************************
%}
  %if (inclActive == "Y")
      @dtw_assign(activeChecked, "CHECKED")
  %else
      @dtw_assign(activeChecked, "")
  %endif

  %if (inclTerminated == "Y")
      @dtw_assign(terminatedChecked, "CHECKED")
  %else
      @dtw_assign(terminatedChecked, "")
  %endif