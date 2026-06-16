%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                              *
*  Job: Quick Link Display Include                                   *
**********************************************************************
%}
  @RtvFldDesc("QDD2WNU='@dtw_ruppercase(d2wName)' and QDQLNKU='LINKSPERROW'", "SYQLND", " char(QDNROW)", linksPerRow)
  %if (linksPerRow <= "0")
      @dtw_assign(linksPerRow, quickLinksPerRow)
  %endif

  %if (quicklinkLoaded == "" && quickLinkByUser == "Y")
      @dtw_assign(quicklinkLoaded, "hideAll")
      @dtw_assign(initialLoad, "Y")
  %else
      @dtw_assign(initialLoad, "")
  %endif
  %if (quicklinkCount == "1" && quickLinkByUser == "Y")
      @dtw_assign(initialLoad, "Y")
      @dtw_tb_getv(quicklinkSeqTable, "1", "1", quicklinkRef)
      @dtw_lowercase(quicklinkRef,quicklinkRef)
      @dtw_assign(quicklinkLoaded, "$(quicklinkRef)")
  %endif

  %if ((formatToPrint == "" || formatToPrint == "N") && quicklinkCount > "0")
      %if (quickLinkByUser == "Y")
          <div class="quickLinksTop">
               <a href="$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;quicklinkSelected=useDefault">$(qlinkDftLrg)</a>
               <a href="$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;quicklinkSelected=saveDefault">$(qlinkSetLrg)</a>
               <a href="$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;quicklinkSelected=viewAll">$(qlinkAllLrg)</a>
               <a href="$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;quicklinkSelected=hideAll">$(qlinkClearLrg)</a>
               %if (quickLinkAllRows == "Y")
                   @dtw_pos("allRows", "$(quicklinkLoaded)", allRowsPos)
                   %if (allRowsPos != "0")
                       <a href="$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;quicklinkSelected=defaultRows">$(qlinkDftRowLrg)</a>
                   %else
                       <a href="$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;quicklinkSelected=allRows">$(qlinkMaxRowLrg)</a>
                   %endif
               %endif
          </div>
      %endif
      <table $(quickLinkTable)>
           @dtw_assign(x, "1")
           @dtw_assign(tdCount, "1")
           %while (x <= quicklinkCount) {
               @dtw_tb_getv(quicklinkSeqTable, x, "1", quicklinkRef)
               @dtw_lowercase(quicklinkRef,quicklinkRef)
               @dtw_tb_getv(quicklinkSeqTable, x, "2", quicklinkTitle)
               @dtw_tb_getv(quicklinkSeqTable, x, "4", quicklinkImage)
               @dtw_tb_getv(quicklinkSeqTable, x, "6", quicklinkURLID)
               @dtw_tb_getv(quicklinkSeqTable, x, "7", quicklinkClass)
               %if (tdCount == "1")
                   <tr>
               %endif
               @dtw_assign(imageExists, "")
               %if (quicklinkImage != "" && quicklinkDspImage == "Y")
                   @dtw_assign(quickLinkImagePath, "$(quicklinkImage)")
                   @dtwf_exists(quickLinkImagePath, imageExists)
               %endif
               @dtw_assign(workURL, "")
               %if (quicklinkURLID != "")
                   @RtvFldDesc("FUID='$(quicklinkURLID)'", "SYURLM", "FUURL", workURL)
                   %if (workURL != "")
                       @RtvFldDesc("FUID='$(quicklinkURLID)'", "SYURLM", "FUTRGT", V_FUTRGT)
    	          %if (V_FUTRGT == "")
                           @dtw_assign(tgt, "")
    	          %elif (@dtw_ruppercase(V_FUTRGT) == "COMMENT")
                           @dtw_assign(tgt, "  onclick=""$(commentWinVar)"" ")
    	          %elif (@dtw_ruppercase(V_FUTRGT) == "INQUIRY")
                           @dtw_assign(tgt, "  onclick=""$(inquiryWinVar)"" ")
    	          %elif (V_FUTRGT != "")
                           @dtw_assign(tgt, " Target=""$(V_FUTRGT)"" ")
    		       %endif

                       @dtw_assign(baseWrk, baseVar)
                       @dtw_pos("@@phpPath", "$(workURL)", posPHP)
                       %if (posPHP > "0")
                           @dtw_replace(baseWrk, ".icl", ".php", "1", "a", baseWrk)
                           @dtw_replace(workURL, "@@phpPath", "$(phpPath)", "1", "a", workURL)
                       %else
                           @dtw_replace(workURL, "@@cGIPath", "$(cGIPath)", "1", "a", workURL)
                       %endif
                       @dtw_pos("@@homeURL", "$(workURL)", poshomeURL)
                       @dtw_replace(workURL, "@@homeURL", "$(homeURL)", "1", "a", workURL)
                       @dtw_replace(workURL, "@@helpPath", "$(helpPath)", "1", "a", workURL)
                       @dtw_replace(workURL, "@@timeStamp", "@dtw_rurlescseq(@dtw_rconcat(@dtw_rdate(),@dtw_rtime("X")))", "1", "a", workURL)

                       @dtw_pos("@@meapid", "$(workURL)", posME)
                       %if (posME != "0")
                           @RtvFldDesc("HDMERL>0", "HDCTRL", "CHAR(HDMERL)", V_HDMERL)
                           %if (V_HDMERL>"0")
                               @dtw_assign(meapid, "ME")
                           %else
                               @dtw_assign(meapid, "ET")
                           %endif
                           @dtw_replace(workURL, "@@meapid", "$(meapid)", "1", "a", workURL)
                       %endif

                       @dtw_pos("?", "$(workURL)", posQ)
                       %if (posQ != "0")
                           @dtw_assign(workAmp, "&amp;")
                       %else
                           @dtw_assign(workAmp, "?")
                       %endif
                       %if (poshomeURL != "0")
                           @dtw_pos("@@baseVar", "$(workURL)", pos)
                           %if (pos == "0")
                               @dtw_concat(workURL, "$(workAmp)baseVar=", workURL)
                               @dtw_concat(workURL, $(baseWrk), workURL)
                               @dtw_assign(workAmp, "&amp;")
                               @dtw_concat(workURL, "$(workAmp)eID=", workURL)
                               @dtw_concat(workURL, $(eID), workURL)
                           %else
                               @dtw_replace(workURL, "@@baseVar", "@dtw_rurlescseq(baseWrk)", "1", "a", workURL)
                           %endif
                           @dtw_pos("@@portal", "$(workURL)", pos)
                           %if (pos == "0")
                               @dtw_concat(workURL, "$(workAmp)portal=", workURL)
                               @dtw_concat(workURL, $(portal), workURL)
                           %else
                               @dtw_replace(workURL, "@@portal", "@dtw_rurlescseq(portal)", "1", "a", workURL)
                           %endif
                       %endif
                       @Set_URL(workURL)
                   %endif
               %endif

               %if (initialLoad == "Y")
                   @dtw_concat(quicklinkLoaded, " $(quicklinkRef)", quicklinkLoaded)
               %endif

               %if (quickLinkByUser == "Y")
                   @dtw_pos(" $(quicklinkRef)", "$(quicklinkLoaded)", qLinkPos)
               %endif

               %if (quicklinkClass!="")
                   @dtw_assign(classOverride, quicklinkClass)
               %elif (qLinkPos != "0" && quickLinkByUser == "Y")
                   @dtw_assign(classOverride, "quickLinkLoaded")
               %else
                   @dtw_assign(classOverride, "quickLinkTabs")
               %endif

               %if (workURL != "" && imageExists == "Y")
		      <td><a href="$(workURL)"$(tgt) title="Click here to go to $(quicklinkTitle)"><img border="$(imageBorder)" src="$(quickLinkImagePath)" alt="$(quicklinkTitle)"></a></td> 	
               %elif (workURL != "")
		      <td class="$(classOverride)"><a href="$(workURL)"$(tgt) title="Click here to go to $(quicklinkTitle)">$(quicklinkTitle)</a></td> 	
               %elif (imageExists == "Y")
                   <td><a href="#$(quicklinkRef)" title="Click here to go to $(quicklinkTitle)"><img border="$(imageBorder)" src="$(quickLinkImagePath)" alt="$(quicklinkTitle)"></a></td>
               %else
                   %if (qLinkPos != "0" || initialLoad == "Y")
                       <td class="$(classOverride)"><a href="#$(quicklinkRef)" title="Click here to go to $(quicklinkTitle)">$(quicklinkTitle)</a></td>
                   %else
                       <td class="$(classOverride)"><a href="$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;quicklinkSelected=@dtw_rurlescseq(quicklinkRef)&amp;quicklinkSelSeq=@dtw_rurlescseq(x)" title="Click here to display $(quicklinkTitle)">$(quicklinkTitle)</a></td>
                   %endif
               %endif
               @dtw_add(x, "1", x)
               @dtw_add(tdCount, "1", tdCount)
               %if (tdCount > linksPerRow)
                   </tr>
                   @dtw_assign(tdCount, "1")
               %endif
           %}
           %if (tdCount > "1")
               </tr>
           %endif
      </table>
  %endif
  %if (initialLoad == "Y")
      %INCLUDE "stmtSQLClear.icl"
      @dtw_concat(stmtSQL, " Update SYQLBW Set QWQSEL='$(quicklinkLoaded)' ", stmtSQL)
      @dtw_concat(stmtSQL, " Where QWXHND='$(profileHandle)' and QWD2WN='$(d2wName)' @@endsql", stmtSQL)
      @SQL_Update(stmtSQL, status)
  %endif