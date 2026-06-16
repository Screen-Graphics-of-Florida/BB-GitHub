%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: SQL Get Total Rows                                          *
*********************************************************************
%}

  @dtw_assign(countSQL, "*")
  %if (distinctSQL != "")
      @dtw_assign(distinctSQL, "char($(distinctSQL)")
      @DTW_REPLACE(distinctSQL, ",", ") concat char(", distinctSQL)
      @dtw_assign(countSQL, "distinct $(distinctSQL) )")
  %endif
  @RtvFldDesc("$(selectSQL)", "$(fileSQL)", "char(count($(countSQL)))", sql_Record_Count)

  %if (sql_Record_Count == "0")
      @dtw_assign(stmtSQL, "Select FLTYPE From SYFLAG Where FLTYPE='NoRecFnd'")
      %INCLUDE "stmtSQLEnd.icl"
  %endif