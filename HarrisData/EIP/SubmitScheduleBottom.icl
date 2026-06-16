%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Submit/Schedule Display Bottom                              *
*********************************************************************
%}
  <table $(contentTable)>
        <tr>
            <td class="toolbar">
                %if (@dtw_rpos(".php", backHome) > "0")
                    <a href="$(homeURL)$(phpPath)$(backHome)" title="Back Home">$(portalHome)</a>
                %elif (@dtw_rpos("baseVar=", backHome) > "0")
                    <a href="$(homeURL)$(cGIPath)$(backHome)" title="Back Home">$(portalHome)</a>
                %elif ((backHome != "$(d2wName)/REPORT") && (backHome != ""))
                    <a href="$(homeURL)$(cGIPath)$(backHome)$(d2wVarBase)" title="Back Home">$(portalHome)</a>
                %endif
                <a $(sbmSchConfirm) href="javascript:check(document.Chg)">$(sbmSchdImage)</a>
                %if (submitNoSelection=="")
                    <a href="javascript:document.Chg.saveSelection.value='Y'; check(document.Chg)">$(sbmSchdSaveImage)</a>
                    %if (reportCount > "0")
                        <a href="$(homeURL)$(cGIPath)ReportSelection.d2w/REPORT$(d2wVarBase)&amp;reportSelD2W=@dtw_rurlescseq(d2wName)&amp;reportDesc=@dtw_rurlescseq(page_title)&amp;reportSelUser=@dtw_rurlescseq(userProfile)&amp;rtvSelection=Y&amp;maintenanceCode=C" onclick="$(searchWinVar)">$(sbmSchdRtvImage)</a>
                    %endif
                %endif
                %if (allowScheduleJob != "N")
                    <a href="javascript:document.Chg.selScheduleJob.value='Y'; check(document.Chg)">$(sbmSchdParmImage)</a>
                %endif
                %if (submitNoReset=="")
                    <a href="$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;resetSelectionFlag=Y&amp;timeStamp=@dtw_rurlescseq(@dtw_rconcat(@dtw_rdate(),@dtw_rtime("X")))">$(sbmSchdResetImage)</a>
                %endif
                %INCLUDE "HelpPage.icl"
            </td>
        </tr>
  </table>
  @dtw_GetCookie("save_sel", saveSel)
  %if (saveSel == "Y")
      @dtw_SetCookie("save_sel", "", "path=/")
      @dtw_assign(browser, HTTP_USER_AGENT)
      <script TYPE="text/javascript">
          %INCLUDE "NewWindowOpen.icl"
          %if (@dtw_rpos("MSIE", HTTP_USER_AGENT) > "0")
              NewWindow('$(homeURL)$(cGIPath)ReportSelection.d2w/REPORT$(d2wVarBase)&amp;reportSelD2W=@dtw_rurlescseq(d2wName)&amp;reportDesc=@dtw_rurlescseq(page_title)&amp;reportSelUser=@dtw_rurlescseq(userProfile)&amp;rtvSelection=@dtw_rurlescseq(rtvSelection)&amp;maintenanceCode=C','report_win','$(searchWinPctH)','$(searchWinPctW)','$(searchWinSB)','$(searchWinRZ)','$(searchWinTB)','$(searchWinMB)','$(searchWinST)');
          %else
              @dtw_replace(d2wVarBase, "&amp;", "&", wrkVar)
              NewWindow('$(homeURL)$(cGIPath)ReportSelection.d2w/REPORT$(wrkVar)&reportSelD2W=@dtw_rurlescseq(d2wName)&reportDesc=@dtw_rurlescseq(page_title)&reportSelUser=@dtw_rurlescseq(userProfile)&rtvSelection=@dtw_rurlescseq(rtvSelection)&maintenanceCode=C','report_win','$(searchWinPctH)','$(searchWinPctW)','$(searchWinSB)','$(searchWinRZ)','$(searchWinTB)','$(searchWinMB)','$(searchWinST)');
          %endif
      </script>
  %endif
