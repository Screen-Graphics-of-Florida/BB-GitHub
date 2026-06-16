%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Order Entry Program Option Security                          *
**********************************************************************
%}

  @dtw_assign(orderEntryProgName, "HOEOEM")
  @pgmOptSecurity(profileHandle, dataBaseID, orderEntryProgName, oe_sec_01, oe_sec_02, oe_sec_03, oe_sec_04, oe_sec_05, oe_sec_06, oe_sec_07, oe_sec_08, oe_sec_09, oe_sec_10, oe_sec_11, oe_sec_12, oe_sec_13, oe_sec_14, oe_sec_15)
  %if (oe_sec_08 == "Y" && V_HDPDRL > "0")
      @dtw_assign(oe_sec_08, "N")
  %endif