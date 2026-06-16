%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Help Link Include                                           *
*********************************************************************
%}
  %if (d2wName != "" && formatToPrint !="Y")
      %if (d2wName == "SubmitJob.d2w")
          @dtw_uppercase(submitEnvProgram,  helpVar)
          @RtvFldDesc("DRPGNMU='$(helpVar)'", "SYDOCR", "DRPAGE", helpDocument)
      %else
          @dtw_assign(helpVar, "$(d2wName)$(helpExt)")
          @dtw_uppercase(helpVar,  helpVar)
          @RtvFldDesc("DRPGNMU='$(helpVar)'", "SYDOCR", "DRPAGE", helpDocument)
      %endif

      %if (helpDocument  != "")
          @dtw_assign(docPath, "$(helpDocument)")
          @dtwf_exists(docPath, docExists)
          %if (docExists == "Y" && medIcon=="Y")
              <a href="$(docPath)" onclick="$(helpWinVar)">$(helpPageImageMed)</a>
          %elif (docExists == "Y")
              <a href="$(docPath)" onclick="$(helpWinVar)">$(helpPageImageLrg)</a>
          %endif
      %endif
  %endif