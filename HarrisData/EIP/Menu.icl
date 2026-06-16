%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Menu Display                                                *
*********************************************************************
%}
%Define {
  endUL = ""
%}
%MACRO_FUNCTION Menu_Query (INOUT profileHandle, dataBaseID, portal, pageID) {
  @dtw_assign(SAVE_ROW_NUM, START_ROW_NUM)
  @dtw_assign(SAVE_MAX_ROWS, RPT_MAX_ROWS)
  @dtw_assign(START_ROW_NUM, "1")
  @dtw_assign(RPT_MAX_ROWS, "9999")

  %if (activeRole == "")
      <div class="accessError">No Access To Menu Items $(accessErrorDesc)</div>
      @DTW_EXIT()
  %else
      @RtvFldDesc("PRROLE='$(activeRole)'", "SYPORR", " char(count(*))", portalByRoleCnt)
      %INCLUDE "stmtSQLClear.icl"
      @dtw_concat(stmtSQL, " Select FPPORT, FPPAGE, FPDESC, FPTITL, FUTRGT, FUDESC, FUTITL, FUURL, FUIMG, ", stmtSQL)
      @dtw_concat(stmtSQL, " Case When FPPAGE=' ' Then 1 When FPPORT=FPPAGE Then 2 Else 3 End as TYPE ", stmtSQL)
      @dtw_concat(fileSQL, " SYROLD inner join SYPORT on FPPORT=RDPORT ", fileSQL)
      @dtw_concat(fileSQL, "        inner join SYURLM on FUID=FPID     ", fileSQL)
      %if (portalByRoleCnt>"0")
          @dtw_concat(fileSQL, "  inner join SYPORR on RDROLE=PRROLE and FPPORT=PRPORT and FPPAGE=PRPAGE and FPSEQ=PRSEQ ", fileSQL)
      %endif
      @dtw_concat(selectSQL, "(((RDROLE='$(activeRole)') and (FPPAGE='' or FPPAGE=FPPORT)) or ", selectSQL)
      @dtw_concat(selectSQL, "(RDROLE='$(activeRole)' and FPPORT='$(portal)' and FPPAGE='$(pageID)'))", selectSQL)
      %if (portalByRoleCnt>"0")
          @dtw_concat(selectSQL, " and PRSEL='Y' ", selectSQL)
      %endif
      @dtw_concat(stmtSQL, " From $(fileSQL) ", stmtSQL)
      %if (@DTW_rSTRIP(selectSQL) != "")
          @dtw_concat(stmtSQL, " Where $(selectSQL) ", stmtSQL)
      %endif
      @dtw_concat(stmtSQL, " Order By RDSEQN,RDPORT,TYPE,FPPAGE,FPSEQ", stmtSQL)
      @dtw_concat(stmtSQL, " For Fetch Only with NC @@endsql", stmtSQL)

      %INCLUDE "stmtSQLTotalRows.icl"
      @Menu_Display_Query(profileHandle, dataBaseID, stmtSQL)
      @dtw_assign(sql_Record_Count, "0")
  %endif
  @dtw_assign(START_ROW_NUM, SAVE_ROW_NUM)
  @dtw_assign(RPT_MAX_ROWS, SAVE_MAX_ROWS)
%}

%FUNCTION(dtw_sql) Menu_Display_Query (IN CHAR(64) profileHandle,
				                       CHAR(2)     dataBaseID,
				                       CHAR(32000) stmtSQL)
{CALL $(pgmLibrary)HSYSQL_W

  %REPORT{ 
      %if (sql_Record_Count == "0")
          <div class="accessError">No Access To Menu Items $(accessErrorDesc)</div>
          @DTW_EXIT()
      %endif

      <!-- Start Of Menu Code -->
      <div id="container">
         <ul id="nav">
          %ROW{
              %if (V_FUTRGT == "")
                  @dtw_assign(tgt, "")
              %elif (@dtw_ruppercase(V_FUTRGT) == "COMMENT")
                  @dtw_assign(tgt, "  onclick=""$(commentWinVar)"" ")
              %elif (@dtw_ruppercase(V_FUTRGT) == "INQUIRY")
                  @dtw_assign(tgt, "  onclick=""$(inquiryWinVar)"" ")
              %elif (@dtw_ruppercase(V_FUTRGT) == "INVOICE")
                  @dtw_assign(tgt, "  onclick=""$(invoiceWinVar)"" ")
              %elif (V_FUTRGT != "")
                  @dtw_assign(tgt, " target=""$(V_FUTRGT)"" ")
              %endif

              %if (V_FPDESC != "")
                  @dtw_assign(desc, $(V_FPDESC))
              %else
                  @dtw_assign(desc, $(V_FUDESC))
              %endif

              %if (V_FPTITL != "")
                  @dtw_assign(titl, $(V_FPTITL))
              %else
                  @dtw_assign(titl, $(V_FUTITL))
              %endif

              @dtw_assign(workURL, V_FUURL)
              @dtw_pos("@@homeURL", "$(workURL)", poshomeURL)
              @dtw_replace(workURL, "@@homeURL", "$(homeURL)", "1", "a", workURL)
              @dtw_pos("@@newsLink", "$(workURL)", newsLinkPos)
              %if (newsLinkPos != "0")
                  @RtvFldDesc("USUSER='$(userProfile)'", "SYUSER", "USNEWS", newsLink)
                  @dtw_replace(workURL, "@@newsLink", "$(newsLink)", "1", "a", workURL)
              %endif
              @dtw_assign(baseWrk, baseVar)
              @dtw_pos("@@phpPath", "$(workURL)", posPHP)
              %if (posPHP > "0")
                  @dtw_pos(".ICL", @dtw_ruppercase(baseVar), posPHP)
                  @dtw_substr(baseVar, "1", posPHP, baseWrk)
                  @dtw_concat(baseWrk, "php", baseWrk)
                  @dtw_replace(workURL, "@@phpPath", "$(phpPath)", "1", "a", workURL)
              %else
                  @dtw_replace(workURL, "@@cGIPath", "$(cGIPath)", "1", "a", workURL)
              %endif
              @dtw_replace(workURL, "@@helpPath", "$(helpPath)", "1", "a", workURL)
              @dtw_replace(workURL, "@@prfh", "@dtw_rurlescseq(profileHandle)", "1", "a", workURL)
              @dtw_replace(workURL, "@@userProfile", "$(userProfile)", "1", "a", workURL)

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

                  @dtw_pos("@@browser", "$(workURL)", pos)
                  %if (pos > "0")
                      @dtw_replace(workURL, "@@browser", "$(browser)", "1", "a", workURL)
                  %endif

                  @dtw_pos("@@portal", "$(workURL)", pos)
                  %if (pos == "0")
                      @dtw_concat(workURL, "$(workAmp)portal=", workURL)
                      @dtw_concat(workURL, $(V_FPPORT), workURL)
                  %else
                      @dtw_replace(workURL, "@@portal", "@dtw_rurlescseq(V_FPPORT)", "1", "a", workURL)
                  %endif
              %endif

              @dtw_replace(workURL, "@@timeStamp", "@dtw_rurlescseq(@dtw_rconcat(@dtw_rdate(),@dtw_rtime("X")))", "1", "a", workURL)
              @Set_URL(workURL)

              %if (V_FPPORT == portal)
                  @dtw_assign(menuClass, "curMenu")
              %else
                  @dtw_assign(menuClass, "")
              %endif

		 %if (svport != "" && svport != V_FPPORT)
			  %if (endUL == "Y")
			      @dtw_assign(endUL, "")
					</ul></li>
			  %endif
			  @dtw_assign(svpage, "")
		 %endif

		 %if (V_FPPAGE != "")
			  %if (svpage != "" && svpage != V_FPPAGE && svpage != pageID)
			      @dtw_assign(endUL, "")
			      </ul></li>
			  %endif
			  %if (svpage == "" && V_FPPAGE != pageID || svpage != V_FPPAGE && V_FPPAGE != pageID || svpage == "" && V_FPPORT == pageID)
			      %if (endUL == "Y") </ul> %endif
			      @dtw_assign(endUL, "Y")
			      <ul>
			  %endif
			  @dtw_assign(svpage, V_FPPAGE)
		 %endif

		 %if (svport != V_FPPORT && V_FPPAGE != pageID)
		     %if (menuClass != "")
			      <li class="$(menuClass)">
			  %else
			    	<li>
			  %endif
		 %endif
				
		 @dtw_assign(svport, V_FPPORT)

              %if (V_FUURL == "")
                  %if (V_FUIMG != "" && displayMenuImages == "Y")
  	          <img border=$(imageBorder) src="$(homeURL)$(imagePath)$(V_FUIMG)" alt="$(desc)">
                  %else
                       <a href="JavaScript:void(0);" title="$(titl)">$(desc)</a>
                  %endif
              %elif (V_FUIMG != "" && displayMenuImages == "Y")
                  <a class="$(menuClass)" href="$(workURL)" $(tgt) title="$(titl)"><img border=$(imageBorder) src="$(homeURL)$(imagePath)$(V_FUIMG)" alt="$(desc)"></a>	
              %elif (V_FPPAGE != "")
                  %if (V_FPPORT != pageID && V_FPPAGE == pageID)
                      <li class="sub1Menu"><a href="$(workURL)" $(tgt) title="$(titl)">$(desc)</a>	
                  %else
                      <li class="sub2Menu"><a href="$(workURL)" $(tgt) title="$(titl)">$(desc)</a></li> 	
                  %endif
              %else
                  <a href="$(workURL)" $(tgt) title="$(titl)">$(desc)</a> 	
              %endif
          %}
	         %if (endUL == "Y")
	             </ul></li>
	         %endif
          @RtvFldDesc("RUUSER='$(userProfile)'", "SYROLU", "char(count(RUUSER))", roleCount)
          %if (roleCount>"1")
              <li><a href="javascript:;" title="Click here to change roles" onClick="window.open('$(homeURL)$(phpPath)RoleSelect.php?baseVar=@dtw_rurlescseq(altBaseVar)&amp;eID=@dtw_rurlescseq(eID)&amp;portal=@dtw_rurlescseq(portal)','role_win','height=500,width=600,top=100,left=200,resizable'); return false">Change Role</a></li>
          %endif
          </ul>
      </div>
  %}
%}