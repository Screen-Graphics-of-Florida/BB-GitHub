%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Quick Link By User                                           *
**********************************************************************
%}
  %if (quicklinkSelected == "useDefault")
      %INCLUDE "stmtSQLClear.icl"
      @dtw_concat(stmtSQL, " Delete From SYQLBW Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)
      @SQL_Update(stmtSQL, status)
      @dtw_assign(quicklinkLoaded, "")
  %else
      @RtvFldDesc("QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)'", "SYQLBW", "QWQSEL", quicklinkLoaded)
  %endif

  %INCLUDE "stmtSQLClear.icl"
  %if (quicklinkLoaded == "")
      @RtvFldDesc("QUUSER='$(userProfile)' and QUD2WN='$(d2wName)'", "SYQLBU", "QUQSEL", userLinkLoaded)

      %if (userLinkLoaded == "")
          %if (quickLinkViewAllDft == "Y")
              @dtw_assign(quicklinkSelected, "viewAll")
          %else
              @dtw_assign(quicklinkLoaded, "hideAll")
          %endif
          @dtw_concat(stmtSQL, " Insert Into SYQLBW (QWXHND,QWD2WN,QWQSEL) ", stmtSQL)
          @dtw_concat(stmtSQL, " Values ('$(profileHandle)','$(d2wName)','$(quicklinkLoaded)')  @@endsql", stmtSQL)
      %else
          @dtw_assign(quicklinkLoaded, userLinkLoaded)
          @dtw_concat(stmtSQL, " Insert Into SYQLBW (QWXHND,QWD2WN,QWQSEL) ", stmtSQL)
          @dtw_concat(stmtSQL, " Select '$(profileHandle)',QUD2WN,QUQSEL", stmtSQL)
          @dtw_concat(stmtSQL, " From SYQLBU ", stmtSQL)
          @dtw_concat(stmtSQL, " Where QUUSER='$(userProfile)' and QUD2WN='$(d2wName)' @@endsql", stmtSQL)
      %endif

  %elif (quicklinkRemove != "")
      @DTW_DELWORD(quicklinkLoaded, "@DTW_rWORDPOS(quicklinkRemove, quicklinkLoaded)", "1", quicklinkLoaded)
      @dtw_concat(stmtSQL, " Update SYQLBW Set QWQSEL='$(quicklinkLoaded)'", stmtSQL)
      @dtw_concat(stmtSQL, " Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)

  %elif (quicklinkSelected == "useDefault")
      @RtvFldDesc("QUUSER='$(userProfile)' and QUD2WN='$(d2wName)'", "SYQLBU", "QUQSEL", userLinkLoaded)
      @dtw_concat(stmtSQL, " Delete From SYQLBW Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)
      @SQL_Update(stmtSQL, status)
      %INCLUDE "stmtSQLClear.icl"
      @dtw_assign(quicklinkLoaded, userLinkLoaded)
      @dtw_concat(stmtSQL, " Insert Into SYQLBW (QWXHND,QWD2WN,QWQSEL) ", stmtSQL)
      @dtw_concat(stmtSQL, " Select '$(profileHandle)',QUD2WN,QUQSEL", stmtSQL)
      @dtw_concat(stmtSQL, " From SYQLBU ", stmtSQL)
      @dtw_concat(stmtSQL, " Where QUUSER='$(userProfile)' and QUD2WN='$(d2wName)' @@endsql", stmtSQL)

  %elif (quicklinkSelected == "saveDefault")
      @dtw_concat(stmtSQL, " Delete From SYQLBU Where QUUSER='$(userProfile)' and QUD2WN='$(d2wName)' @@endsql", stmtSQL)
      @SQL_Update(stmtSQL, status)
      %INCLUDE "stmtSQLClear.icl"
      @dtw_concat(stmtSQL, " Insert Into SYQLBU (QUUSER,QUD2WN,QUQSEL) ", stmtSQL)
      @dtw_concat(stmtSQL, " Select '$(userProfile)',QWD2WN,QWQSEL", stmtSQL)
      @dtw_concat(stmtSQL, " From SYQLBW ", stmtSQL)
      @dtw_concat(stmtSQL, " Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)

  %elif (quicklinkSelected == "viewAll")
      @dtw_assign(quicklinkLoaded, "")
      @dtw_concat(stmtSQL, " Update SYQLBW Set QWQSEL='' ", stmtSQL)
      @dtw_concat(stmtSQL, " Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)

  %elif (quicklinkSelected == "hideAll")
      @dtw_assign(quicklinkLoaded, "hideAll")
      @dtw_concat(stmtSQL, " Update SYQLBW Set QWQSEL='hideAll' ", stmtSQL)
      @dtw_concat(stmtSQL, " Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)

  %elif (quicklinkSelected == "allRows")
      @dtw_pos("allRows", "$(quicklinkLoaded)", allRowsPos)
      %if (allRowsPos == "0")
          @dtw_concat(quicklinkLoaded, " allRows", quicklinkLoaded)
          @dtw_concat(stmtSQL, " Update SYQLBW Set QWQSEL='$(quicklinkLoaded)' ", stmtSQL)
          @dtw_concat(stmtSQL, " Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)
      %endif

  %elif (quicklinkSelected == "defaultRows")
      @DTW_DELWORD(quicklinkLoaded, "@DTW_rWORDPOS(" allRows", quicklinkLoaded)", "1", quicklinkLoaded)
      @dtw_concat(stmtSQL, " Update SYQLBW Set QWQSEL='$(quicklinkLoaded)' ", stmtSQL)
      @dtw_concat(stmtSQL, " Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)

  %elif (quicklinkSelected != "")
      @DTW_DELWORD(quicklinkLoaded, "@DTW_rWORDPOS(quicklinkSelected, quicklinkLoaded)", "1", quicklinkLoaded)
      @dtw_concat(quicklinkLoaded, " $(quicklinkSelected)", quicklinkLoaded)
      @dtw_concat(stmtSQL, " Update SYQLBW Set QWQSEL='$(quicklinkLoaded)' ", stmtSQL)
      @dtw_concat(stmtSQL, " Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)
  %endif

  %if (stmtSQL != "")
      @SQL_Update(stmtSQL, status)
  %endif