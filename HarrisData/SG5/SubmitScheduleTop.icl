%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					             *
*  Job: Page Title Submit/Schedule Display Top                      *
*********************************************************************
%}
  <table $(contentTable)>
	       <colgroup>
	           <col width="80%">
	           <col width="15%">
        <tr><td><h1>$(page_title)</h1></td>
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
                    @RtvFldDesc("DRD2WN<>' ' and DRD2WN='@dtw_ruppercase(d2wName)'", "SYD2WR", " char(count(DRD2WN))", reportCount)
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