%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: SQL Selection Parameter                                     *
*********************************************************************
%}
  %if (wildCardSearch != "" && appendWildCard!="N")
      @dtw_concat(selectSQL, " $(wildCardSearch)", selectSQL)
  %endif
  %if (uv_Sql != "" && appendUserView!="N")
      @dtw_concat(selectSQL, " and ($(uv_Sql))", selectSQL)
  %endif

  @dtw_concat(stmtSQL, " From $(fileSQL) ", stmtSQL)
  %if (@DTW_rSTRIP(selectSQL) != "")
      @dtw_concat(stmtSQL, " Where $(selectSQL) ", stmtSQL)
  %endif