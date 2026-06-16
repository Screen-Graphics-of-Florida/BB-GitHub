%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Attachment Select Include                                   *
*********************************************************************
%}

%FUNCTION(dtw_sql) Select_Attachments (IN CHAR(64)    profileHandle,
				                       CHAR(2)     dataBaseID,
                                          CHAR(32000) stmtSQL)
{CALL $(pgmLibrary)HSYSQL_W

  %REPORT{
      %if (quickLinksInUse != "N")
          <a name="attachments"></a>
          %INCLUDE "AttachmentMoreURL.icl"
          %INCLUDE "QuickLinkTopOfForm.icl"
      %else
          %if (sql_Record_Count > "0")
              <fieldset class="legendBody">
                  <legend class="legendTitle">Attachments</legend>
          %endif
      %endif

      <table $(contentTable)>
          %if (sql_Record_Count == "0")
              %if (quickLinksInUse != "N")
                  @dtw_replace(noInfoFoundMsg, "XXX", $(quicklinkTitle),"1", "F", displayMsg)
                  <tr><td class="colalph">$(displayMsg)</td></tr>
              %endif
          %else
              <colgroup>
                  <col width="30%">
                  <col width="30%">
                  <col width="15%">
              </colgroup>
              <tr>
                  @Format_Column_Header("ATDESCU", "Description")
                  @Format_Column_Header("ATATNSU", "Attachment Name")
                  @Format_Column_Header("ATUSER", "User")
                  @Format_Column_Header("", "Date")
                  @Format_Column_Header("", "Time")
              </tr>
          %endif

          %ROW{
              %INCLUDE "SetRowClass.icl"
              @TimeStamp_TIME(V_ATTSTP, attTime)
              @EditHrsMinSec(attTime)
              @TimeStamp_CYMD(V_ATTSTP, dateCYMD)
              @Format_Date(dateCYMD, "D", F_date)

              <tr class="$(rowClass)">
                  %{
                  @dtw_assign(attachmentExists, "")
                  @dtw_assign(attachmentPath, "$(homePath)$(uploadDirectory)$(V_ATATNS)")
                  attachmentPath=$(attachmentPath)
                  @dtwf_exists(attachmentPath, attachmentExists)
                  %}
                  <td class="colalph">$(V_ATDESC)</td>
                  %if ($(V_ATDIRL) == "Y")
                      <td class="colalph"><a href="$(V_ATATNL)" target=_blank title="Click here to view attachment">$(V_ATATNS)</a></td>
                      @dtw_assign(directLink, "$(checkImage)")
               %{   %elif (attachmentExists != "Y") %}
                  %else
                      @dtw_assign(directLink, "")
                      <td class="colalph"><a href="$(homeURL)$(phpPath)$(V_ATATNL)" target=_blank title="Click here to view attachment">$(V_ATATNS)</a></td>
               %{     <td class="colalph">$(V_ATATNS)</td>
               %}
                  %endif
                  @RtvFldDesc("USUSER='$(V_ATUSER)'", "SYUSER", "USDESC", userName)
                  <td class="colalph">$(userName)</td>
                  <td class="coldate">$(F_date)</td>
                  <td class="colnmbr">$(attTime)</td>
              </tr>
          %}
      </table>
      %if (sql_Record_Count > "0" || quickLinksInUse != "N")
          </fieldset>
      %endif
  %}
%}