%{
*********************************************************************
* Copr 1979 2005 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Work List Options (on Approval page)                        *
*********************************************************************
%}

  @RtvFldDesc("ININST=$(wfInstance) and ININDT=$(wfInstanceDate) ", "WFINST", "INPROC", wfProcess)
  @RtvFldDesc("WIINST=$(wfInstance) and WIINDT=$(wfInstanceDate) and WIWITM=$(wfWorkItem) and WIWISQ=$(wfWorkItemSequence) ", "WFWITM", "CHAR(WIPONT)", wfPoint)
  @RtvFldDesc("WIINST=$(wfInstance) and WIINDT=$(wfInstanceDate) and WIWITM=$(wfWorkItem) and WIWISQ=$(wfWorkItemSequence) ", "WFWITM", "CHAR(WISEQ)", wfSequence)
  @RtvFldDesc("WPINST=$(wfInstance) and WPINDT=$(wfInstanceDate) and WPWITM=$(wfWorkItem) and WPWISQ=$(wfWorkItemSequence) and WPPTID<>$(wfParticipantId) and (WPRVWS=' ' or WPRVWS='Y') ", "WFWIPT", "CHAR(COUNT(*))", lastPartCount)

  %if (lastPartCount >"0")
      @dtw_assign(passRecCount, "0")
  %else
      @RtvFldDesc("TRPROC='$(wfProcess)' and TRFPNT=$(wfPoint) and TRFSEQ=$(wfSequence) and TRFAIL='P' and TROPT='O'", "WFPRTR", "CHAR(COUNT(*))", passRecCount)
  %endif
  @RtvFldDesc("TRPROC='$(wfProcess)' and TRFPNT=$(wfPoint) and TRFSEQ=$(wfSequence) and TRFAIL='F' and TROPT='O'", "WFPRTR", "CHAR(COUNT(*))", failRecCount)

  <table $(contentTable)>
      <tr>
          %if (showIconPassWith=="Y")
              <td>
                  %if (passRecCount>"0")
                      <a href="$(homeURL)$(cGIPath)WFWorkListOptionalOptions.d2w/REPORT$(maintainVar)&amp;wfPassFailFlag=P&amp;wfPassFail=Pass&amp;wfPassFailLink=PASS_WF&amp;refreshD2W=N" onclick="$(smallPromptWinVar)">$(wfPassImageOpt)</a>
                  %else
                       <a href="$(homeURL)$(cGIPath)WFWorkListOptions.d2w/PASS_WF$(d2wVarBase)&amp;refreshD2W=N">$(wfPassImage)</a>
                  %endif
              </td>
          %endif

          %if (showIconPassWithout=="Y")
              <td>
                  %if (passRecCount>"0")
                      <a href="$(homeURL)$(cGIPath)WFWorkListOptionalOptions.d2w/REPORT$(maintainVar)&amp;wfPassFailFlag=P&amp;wfPassFail=Pass&amp;wfPassFailLink=PASSUNDO_WF&amp;refreshD2W=N" onclick="$(smallPromptWinVar)">$(wfPassUndoImageOpt)</a>
                  %else
                       <a href="$(homeURL)$(cGIPath)WFWorkListOptions.d2w/PASSUNDO_WF$(d2wVarBase)&amp;refreshD2W=N">$(wfPassUndoImage)</a>
                  %endif
              </td>
          %endif

          <td>
              %if (failRecCount>"0")
                  <a href="$(homeURL)$(cGIPath)WFWorkListOptionalOptions.d2w/REPORT$(maintainVar)&amp;wfPassFailFlag=F&amp;wfPassFail=Fail&amp;wfPassFailLink=FAIL_WF&amp;refreshD2W=N" onclick="$(smallPromptWinVar)">$(wfFailImageOpt)</a>
              %else
                  <a onClick="return confirmFail('$(confirmDesc)')" href="$(homeURL)$(cGIPath)WFWorkListOptions.d2w/FAIL_WF$(maintainVar)&amp;refreshD2W=N">$(wfFailImage)</a>
              %endif
          </td>

          <td><a href="$(homeURL)$(cGIPath)WFWorkListOptions.d2w/CANCEL$(d2wVarBase)&amp;refreshD2W=N">$(wfCancelImage)</a></td>
          <td><a href="$(homeURL)$(cGIPath)WFWorkItemComment.d2w/MAINTAIN$(d2wVarBase)&amp;refreshD2W=N" onclick="$(commentWinVar)">$(commentImage)</a></td>

          %while(@dtw_rlength(wfInstance) < "15")        {@dtw_insert("0", wfInstance, wfInstance)%}
          %while(@dtw_rlength(wfInstanceDate) < "7")     {@dtw_insert("0", wfInstanceDate, wfInstanceDate)%}
          %while(@dtw_rlength(wfWorkItem) < "5")         {@dtw_insert("0", wfWorkItem, wfWorkItem)%}
          %while(@dtw_rlength(wfWorkItemSequence) < "5") {@dtw_insert("0", wfWorkItemSequence, wfWorkItemSequence)%}
          @dtw_assign(attachD2W,    "WFWorkList.d2w")
          @dtw_assign(attachVarKey, "$(wfInstance)$(wfInstanceDate)$(wfWorkItem)$(wfWorkItemSequence)")
          @dtw_assign(attachForDesc, V_INDESC)
          @dtw_assign(attachPrg1, "WFWIHS Where WHINST=$(wfInstance) and WHINDT=$(wfInstanceDate) and WHWITM=$(wfWorkItem) and WHWISQ=$(wfWorkItemSequence) ")

          <td><a href="$(homeURL)$(cGIPath)Attachment.d2w/REPORT$(genericVarBase)&amp;attachD2W=@dtw_rurlescseq(attachD2W)&amp;attachForDesc=@dtw_rurlescseq(attachForDesc)&amp;attachVarKey=@dtw_rurlescseq(attachVarKey)&amp;userProfile=@dtw_rurlescseq(userProfile)" onclick="$(inquiryWinVar)">$(attachImageSml)</a></td>
      </tr>
  </table>

  <fieldset class="legendBody">
      <legend class="legendTitle">Reason For Review</legend>
      <table $(contentTable)>
          @RtvFldDesc("WIINST=$(wfInstance) and WIINDT=$(wfInstanceDate) and WIWITM=$(wfWorkItem) and WIWISQ=$(wfWorkItemSequence)", "WFWITM", "WICONDE", V_WICONDE)
          %if (V_WICONDE!="")
              <tr><td><h2>$(V_WICONDE)</h2></td></tr>
          %endif
          @RtvFldDesc("WPINST=$(wfInstance) and WPINDT=$(wfInstanceDate) and WPWITM=$(wfWorkItem) and WPWISQ=$(wfWorkItemSequence) and WPPTID=$(wfParticipantId)", "WFWIPT", "WPDPRT", V_WPDPRT)
          %if (V_WPDPRT !="")
              @RtvFldDesc("USUSER='$(V_WPDPRT)' ", "SYUSER", "USDESC", V_USDESC)
              <tr><td><h2>Delegated From: $(V_USDESC)</h2></td></tr>
          %endif
      </table>
  </fieldset>