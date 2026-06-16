  @pgmOptSecurity(profileHandle, dataBaseID, programName, sec_01, sec_02, sec_03, sec_04, sec_05, sec_06, sec_07, sec_08, sec_09, sec_10, sec_11, sec_12, sec_13, sec_14, sec_15)
  %if (sec_05 == "N")
      @dtw_assign(pgmOptAuth, "F")
  %else
      @dtw_assign(pgmOptAuth, "")
  %endif
