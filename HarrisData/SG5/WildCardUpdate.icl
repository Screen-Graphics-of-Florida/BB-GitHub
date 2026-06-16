%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Display/Reset Of WilCard Selection Criteria                 *
*********************************************************************
%}

  %if (wildCardTemp != "")
      @dtw_concat(wildCardSearch, wildCardTemp, wildCardSearch)
      @dtw_concat(wildCardSearch, "))", wildCardSearch)
      @dtw_concat(wildCardDisplay, wildDisplayTemp, wildCardDisplay)
  %endif

  @Save_WebReg(webRegCurFile, profileHandle, d2wName, wildCardSearch, orderBy, orderByDisplay, wildCardDisplay)
  %if (updateSearch == "Y")
      <meta http-equiv="refresh" content="0; URL=$(masterSearchVar)$(orderByVarBase)$(wildCardVarBase)&amp;timeStamp=@dtw_rurlescseq(@dtw_rconcat(@dtw_rdate(),@dtw_rtime("X")))">
  %else
      @DTW_ASSIGN(RPT_MAX_ROWS, dspMaxRows)
      @Retrieve_WebReg(webRegCurFile, profileHandle, d2wName, wildCardSearch, orderBy, orderByDisplay, wildCardDisplay)
      %if (wildCardSearch != "")
          @Save_WebReg(webRegCurFile, profileHandle, d2wName, wildCardSearch, orderBy, orderByDisplay, wildCardDisplay)
      %endif
      @Set_SQL(stmtSQL)
  %endif