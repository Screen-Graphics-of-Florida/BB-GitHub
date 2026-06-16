%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Get Month Description                                        *
**********************************************************************
%}

%MACRO_FUNCTION Get_Month_Desc (INOUT CHAR(2) monthNbr,
			                      CHAR(3) monthAbr)
{
  %if (monthNbr == "01")
      @dtw_assign(monthAbr, "Jan")
  %elif (monthNbr == "02")
      @dtw_assign(monthAbr, "Feb")
  %elif (monthNbr == "03")
      @dtw_assign(monthAbr, "Mar")
  %elif (monthNbr == "04")
      @dtw_assign(monthAbr, "Apr")
  %elif (monthNbr == "05")
      @dtw_assign(monthAbr, "May")
  %elif (monthNbr == "06")
      @dtw_assign(monthAbr, "Jun")
  %elif (monthNbr == "07")
      @dtw_assign(monthAbr, "Jul")
  %elif (monthNbr == "08")
      @dtw_assign(monthAbr, "Aug")
  %elif (monthNbr == "09")
      @dtw_assign(monthAbr, "Sep")
  %elif (monthNbr == "10")
      @dtw_assign(monthAbr, "Oct")
  %elif (monthNbr == "11")
      @dtw_assign(monthAbr, "Nov")
  %elif (monthNbr == "12")
      @dtw_assign(monthAbr, "Dec")
  %endif
%}

%MACRO_FUNCTION Get_Month_Full_Desc (INOUT CHAR(2) monthNbr,
			                           CHAR(9) monthDesc)
{
  %if (monthNbr == "01")
      @dtw_assign(monthDesc, "January")
  %elif (monthNbr == "02")
      @dtw_assign(monthDesc, "February")
  %elif (monthNbr == "03")
      @dtw_assign(monthDesc, "March")
  %elif (monthNbr == "04")
      @dtw_assign(monthDesc, "April")
  %elif (monthNbr == "05")
      @dtw_assign(monthDesc, "May")
  %elif (monthNbr == "06")
      @dtw_assign(monthDesc, "June")
  %elif (monthNbr == "07")
      @dtw_assign(monthDesc, "July")
  %elif (monthNbr == "08")
      @dtw_assign(monthDesc, "August")
  %elif (monthNbr == "09")
      @dtw_assign(monthDesc, "September")
  %elif (monthNbr == "10")
      @dtw_assign(monthDesc, "October")
  %elif (monthNbr == "11")
      @dtw_assign(monthDesc, "November")
  %elif (monthNbr == "12")
      @dtw_assign(monthDesc, "December")
  %endif
%}