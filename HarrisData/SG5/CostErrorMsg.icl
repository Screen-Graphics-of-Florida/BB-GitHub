%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Retrieve Cost Error Message                                  *
**********************************************************************
%}

%MACRO_FUNCTION Retrieve_Cost_Error (INOUT CHAR(1) messageCode,
			                           CHAR(50) errorDesc)
{
  %if (messageCode == "A")
      @dtw_assign(errorDesc, "Invalid Part Type")
  %elif (messageCode == "B")
      @dtw_assign(errorDesc, "No Routings Exist For This Item")
  %elif (messageCode == "C")
      @dtw_assign(errorDesc, "No Routings Exist For Bill Of Material Seq")
  %elif (messageCode == "D")
      @dtw_assign(errorDesc, "Labor Method Measured Hours Are Zero")
  %elif (messageCode == "E")
      @dtw_assign(errorDesc, "Machine Method Measured Hours Are Zero")
  %elif (messageCode == "G")
      @dtw_assign(errorDesc, "Routing Per Unit Rate Is Zero")
  %elif (messageCode == "H")
      @dtw_assign(errorDesc, "Item Purchase Cost Is Zero")
  %elif (messageCode == "I")
      @dtw_assign(errorDesc, "Item Weight Is Zero")
  %elif (messageCode == "J")
      @dtw_assign(errorDesc, "Item Cubic Volume Is Zero")
  %elif (messageCode == "K")
      @dtw_assign(errorDesc, "Detail Cost Does Not Exist For Factored Element")
  %elif (messageCode == "L")
      @dtw_assign(errorDesc, "No Product Structure Exists For The Item")
  %elif (messageCode == "M")
      @dtw_assign(errorDesc, "Low Level Code Of Component Less Than/Equal Parent")
  %elif (messageCode == "N")
      @dtw_assign(errorDesc, "No Material Cost Exists For The Component")
  %elif (messageCode == "O")
      @dtw_assign(errorDesc, "Overflow Of Cost Amount During BOM Extension")
  %elif (messageCode == "P")
      @dtw_assign(errorDesc, "Prior Error In Component(s)")
  %elif (messageCode == "Q")
      @dtw_assign(errorDesc, "Overflow Of Detail Cost Amount")
  %elif (messageCode == "X")
      @dtw_assign(errorDesc, "Multiple Errors Occured During Roll")
  %elif (messageCode == "W")
      @dtw_assign(errorDesc, "No Cost Element Detail Rate For Work Center")
  %elif (messageCode == "Z")
      @dtw_assign(errorDesc, "Item Cost Is Zero, No Cost Row Created")
  %endif
%}