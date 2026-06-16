%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Attachment SQL Include                                      *
*********************************************************************
%}
  %if (quicklinkRef == "attachments" || quickLinksInUse == "N")
      @RtvFldDesc("USUSER='$(userProfile)'", "SYUSER", "USADMN", adminUser)
      %INCLUDE "QuickLinkClear.icl"
      %INCLUDE "stmtSQLClear.icl"
      @dtw_concat(stmtSQL, " Select * ", stmtSQL)
      @dtw_concat(fileSQL, " SYD2WA ", fileSQL)
      @dtw_concat(selectSQL, " ATFOLD<>' ' and ATFOLD='@dtw_rUPPERCASE(attachFolder)' and ATVKEY='$(attachVarKey)' ", selectSQL)
      @dtw_concat(selectSQL, " and (ATUSER='$(userProfile)' or ATPRIV=' ' or '$(adminUser)'='Y') ", selectSQL)
      %INCLUDE "stmtSQLSelect.icl"
      @DTW_ASSIGN(orderBy, "ATDESCU,ATATNSU")
      @DTW_ASSIGN(orderByDisplay, "Description, Attachment Name")
      @dtw_concat(stmtSQL, " Order By $(orderBy) ", stmtSQL)
      %INCLUDE "stmtSQLEnd.icl"
      %INCLUDE "stmtSQLTotalRows.icl"
      @Select_Attachments(profileHandle, dataBaseID, stmtSQL)
  %endif