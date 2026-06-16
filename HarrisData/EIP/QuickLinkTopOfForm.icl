%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Quick Link Top Of Form Include                               *
**********************************************************************
%}
  <fieldset class="legendBody">
  %if (sql_Record_Count == "0")
      @dtw_assign(displayMaxRowsMsg, "N")
  %elif (sql_Record_Count < quicklinkMaxRows)
      @dtw_assign(displayMaxRows, sql_Record_Count)
  %else
      @dtw_assign(displayMaxRows, quicklinkMaxRows)
  %endif

  %if (displayMaxRowsMsg != "N")
      @dtw_concat(quicklinkTitle, maxRowsMsg, displayTitle)
  %else
      @dtw_assign(displayTitle, quicklinkTitle)
  %endif
  @dtw_assign(displayMaxRowsMsg, "")
  <legend class="legendTitle">$(displayTitle)</legend>

  %if ((formatToPrint == "" || formatToPrint == "N") && (quicklinkCount > "1" || moreURL != ""))
      <div class="quickLinksTop">
          %if (quicklinkCount > "1" && moreURL != "" && moreWinVar != "")
              <a href="$(moreURL)" onclick="$(moreWinVar)">$(moreInfoImage)</a> <a href="#top">$(topOfFormImage)</a>
          %elif (moreURL != "" && moreWinVar != "")
              <a href="$(moreURL)" onclick="$(moreWinVar)">$(moreInfoImage)</a>
          %elif (quicklinkCount > "1" && moreURL != "")
              <a href="$(moreURL)">$(moreInfoImage)</a> <a href="#top">$(topOfFormImage)</a>
          %elif (moreURL != "")
              <a href="$(moreURL)">$(moreInfoImage)</a>
          %elif (quicklinkCount > "1")
              <a href="#top">$(topOfFormImage)</a>
          %endif
          %if (quickLinkByUser == "Y" && quicklinkLoaded != "" && quicklinkCount > "1")
              <a href="$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;quicklinkRemove=@dtw_rurlescseq(quicklinkRef)&amp;quicklinkLoaded=@dtw_rurlescseq(quicklinkLoaded)" title="Click here to display $(quicklinkTitle)">$(closeImageMed)</a>
          %endif
      </div>
      %if (sql_Record_Count > "0")
          <br>
      %endif
      @dtw_assign(moreWinVar, "")
  %endif