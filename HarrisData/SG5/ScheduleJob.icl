%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					             *
*  Job: Schedule Job                                                *
*********************************************************************
%}
  <table $(contentTable)>
      <tr>
          <td><input type="hidden" name="submitSchedule" value="$(submitSchedule)">
              <input type="hidden" name="saveSelection" value="N">
              <input type="hidden" name="rtvSelection" value="">
              <input type="hidden" name="selScheduleJob" value="">
          </td>
      </tr>
  </table>

  %if (submitSchedule == "S")
      %if (errFound == "" && jobSbmSched != "Y")
            @Env_Overrides(userProfile, applicationID, envProgram, envPrinter, V_J_JNAM, V_J_JOBD, V_J_JOBQ, V_J_OUTQ, envError)
            @dtw_assign(V_J_JFRQ, "*ONCE")
            @dtw_assign(V_J_JTIM, "*CURRENT")
            @dtw_assign(V_J_JDAT, "*CURRENT")
            @dtw_assign(V_J_JDAY, "*NONE")
      %endif
      @schFreq_Query(profileHandle, dataBaseID, "SCHFREQ   ", schFreqTable)
      @schDays_Query(profileHandle, dataBaseID, "SCHDAY   ", schDaysTable)	

      <fieldset class="legendBody">
          <legend class="legendTitle">Schedule Parameters </legend>
          <table $(contentTable)>
              <tr><td>&nbsp;</td>
                  <td align="right">
                      <a href="javascript:document.Chg.selScheduleJob.value='N'; check(document.Chg)">$(closeWinImageLrg)</a>
                  </td>
              </tr>
              <tr>
                  <td>
                      <table $(contentTable)>
                          @SetTextOvr(Err_J_JNAM)
                          <tr><td class="dsphdr"><span $(textOvr)>Job Name</span></td>
                              <td class="inputalph"><input name="schJobName" type="text" value="$(V_J_JNAM)" size="12" maxlength="10"></td>
                          </tr>
                          @DspErrMsg(Err_J_JNAM)

                          @SetTextOvr(Err_J_JOBD)
                          <tr><td class="dsphdr"><span $(textOvr)>Job Description</span></td>
                              <td class="inputalph"><input name="schJobDescription" type="text" value="$(V_J_JOBD)" size="12" maxlength="10"></td>
                          </tr>
                          @DspErrMsg(Err_J_JOBD)

                          @SetTextOvr(Err_J_JOBQ)
                          <tr><td class="dsphdr"><span $(textOvr)>Job Queue</span></td>
                              <td class="inputalph"><input name="schJobQueue" type="text" value="$(V_J_JOBQ)" size="12" maxlength="10"></td>
                          </tr>
                          @DspErrMsg(Err_J_JOBQ)

                          @SetTextOvr(Err_J_JFRQ)
                          <tr><td class="dsphdr"><span $(textOvr)>Frequency</span></td>
                              @dtw_tb_rows(schFreqTable, maxRows)
                              @dtw_assign(x, "1")
                              %while((@dtw_tb_rgetv(schFreqTable, x, "2") != V_J_JFRQ) && (x < maxRows))
                                    {@dtw_add(x, "1", x)%}
                              <td>@dtw_tb_select(schFreqTable, "schFrequency", "2", "1", "N", "", x, "2")</td>
                          </tr>
                          @DspErrMsg(Err_J_JFRQ)

                          @SetTextOvr(Err_J_JTIM)
                          <tr><td class="dsphdr"><span $(textOvr)>Schedule Time</span></td>
                              <td class="inputalph"><input name="schTime" type="text" value="$(V_J_JTIM)" size="12" maxlength="8"></td>
                          </tr>
                          @DspErrMsg(Err_J_JTIM)
                      </table>
                  </td>
                  <td>
                      <table $(contentTable)>
                          @SetTextOvr(Err_J_JDAT)
                          <tr><td class="dsphdr"><span $(textOvr)>Schedule Date</span></td>
                              <td class="inputalph"><input name="schDate" type="text" value="$(V_J_JDAT)" size="12" maxlength="9">
                              <a href="javascript:calWindow('schDate');">$(calendarImage)</a></td>
                          </tr>
                          @DspErrMsg(Err_J_JDAT)

                          @SetTextOvr(Err_J_JDAY)
                          <tr><td class="dsphdr"><span $(textOvr)>or Schedule Days</span></td>
                              @dtw_tb_rows(schDaysTable, maxRows)
                              @dtw_assign(y, "1")
                              @dtw_assign(beg, "1")
                              @dtw_assign(daysSelected, "")
                              %while(y < maxRows){
                                  @dtw_pos(",", V_J_JDAY, beg, pos)
                                  %if (pos != "0")
                                      @dtw_substr(V_J_JDAY, beg, @dtw_rsubtract(pos, beg), fieldy)
                                      @dtw_add(pos, "1", beg)
                                  %else
                                      @dtw_substr(V_J_JDAY, beg, "5", fieldy)
                                      @dtw_assign(y, maxRows)
                                  %endif

                                  @dtw_assign(x, "1")
                                  %while(x <= maxRows){
                                      %if (@dtw_tb_rgetv(schDaysTable, x, "2") == @dtw_rStrip(fieldy))
                                           @dtw_concat(daysSelected, " $(x)", daysSelected)
                                           @dtw_assign(x, maxRows)
                                      %endif
                                      @dtw_add(x, "1", x)
                                  %}
                                  @dtw_add(y, "1", y)
                              %}
                              <td>@dtw_tb_select(schDaysTable, "schDays", "2", "", "Y", "", daysSelected, "2")</td>
                          </tr>
                          @DspErrMsg(Err_J_JDAY)
                      </table>
                  </td>
              </tr>
          </table>
      </fieldset>
  %endif
