%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Submit/Schedule Update Processing                           *
*********************************************************************
%}
  %if ((errFound == "" && saveSelection == "Y") || rtvSelection == "Y")
      @dtw_assign(errFound, "Y")
      @EdtVarErr(profileHandle, typeValue, edtVar)
      @ErrVarErr(profileHandle, typeError, typeReset)
      @dtw_SetCookie("save_sel", "Y", "path=/")
      <meta http-equiv="refresh" content="0; URL=$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;errFound=@dtw_rurlescseq(errFound)&amp;timeStamp=@dtw_rurlescseq(@dtw_rconcat(@dtw_rdate(),@dtw_rtime("X")))">
  %elif (errFound == "" && saveSelection != "S")
      @EdtVarErr(profileHandle, typeValue, edtVar)
      @ErrVarErr(profileHandle, typeError, typeReset)
      %if (submitSchedule != "S")
          @dtw_assign(submitSchedule, "M")
      %endif
      %INCLUDE "SubmitScheduleMessage.icl"
	  @dtw_pos(".php?", backHome, fromPhp)
      %if (submitScheduleD2W != "")
          <meta http-equiv="refresh" content="1; URL=$(homeURL)$(cGIPath)$(submitScheduleD2W)/REPORT$(d2wVarBase)&amp;confMessage=@dtw_rurlescseq(confMessage)">
      %elif (backHome != "" && fromPhp > "0")
          <meta http-equiv="refresh" content="1; URL=$(homeURL)$(phpPath)$(backHome)&amp;confMessage=@dtw_rurlescseq(confMessage)">
      %elif (backHome != "")
          <meta http-equiv="refresh" content="1; URL=$(homeURL)$(cGIPath)$(backHome)$(d2wVarBase)&amp;confMessage=@dtw_rurlescseq(confMessage)">
      %else
          <meta http-equiv="refresh" content="1; URL=$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;jobSbmSched=Y&amp;scheduleJobSwitch=Y&amp;confMessage=@dtw_rurlescseq(confMessage)">
      %endif
  %else
      %if (saveSelection == "S")
          %if (errFound == "")
              @ErrVarErr(profileHandle, typeError, typeReset)
          %endif
      %endif
      @EdtVarErr(profileHandle, typeValue, edtVar)
      @ErrVarErr(profileHandle, typeError, errVar)
      <meta http-equiv="refresh" content="0; URL=$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;errFound=@dtw_rurlescseq(errFound)&amp;scheduleJobSwitch=Y&amp;timeStamp=@dtw_rurlescseq(@dtw_rconcat(@dtw_rdate(),@dtw_rtime("X")))">
  %endif