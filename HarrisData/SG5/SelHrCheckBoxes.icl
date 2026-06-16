%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Set check boxes for H/R report selection                    *
*********************************************************************
%}
  %if (errFound != "" || scheduleJobSwitch == "Y")
      %if (V_SLACMP == "ALL")
          @dtw_assign(checkedCompany, "CHECKED")
      %else
          @dtw_assign(checkedCompany, "")
      %endif
      %if (V_SLAFAC == "ALL")
          @dtw_assign(checkedFacility, "CHECKED")
      %else
          @dtw_assign(checkedFacility, "")
      %endif
      %if (V_SLALOC == "ALL")
          @dtw_assign(checkedLocation, "CHECKED")
      %else
          @dtw_assign(checkedLocation, "")
      %endif
      %if (V_SLADPT == "ALL")
          @dtw_assign(checkedDepartment, "CHECKED")
      %else
          @dtw_assign(checkedDepartment, "")
      %endif
 %else
       @dtw_assign(checkedCompany, "CHECKED")
       @dtw_assign(checkedFacility, "CHECKED")
       @dtw_assign(checkedLocation, "CHECKED")
       @dtw_assign(checkedDepartment, "CHECKED")
 %endif