%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Copyright                                                   *
*********************************************************************
%}
  <div class="copr">
      @RtvFldDesc("RMROLE='$(activeRole)'", "SYROLM", "RMDESC", roleDesc)
      @dtw_assign(yr, @dtw_rsubstr(@dtw_rdate("N"), "8", "4"))
       &copy; Copyright $(yr) HarrisData &nbsp; &nbsp; @dtw_rdate("N") %INCLUDE "CurrentTime.icl" &nbsp; &nbsp; User: $(userProfile) &nbsp; Role: <span title="$(activeRole)">$(roleDesc)</span>
  </div>
  %if (formatToPrint == "" || formatToPrint == "N")
      <div class="copr">
          %INCLUDE "HelpBook.icl"
          %INCLUDE "ProgSecurityUsageInquiry.ICL"
      </div>
  %endif