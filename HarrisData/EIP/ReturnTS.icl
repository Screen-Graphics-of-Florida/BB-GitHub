%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                *
*  Job: Return With No Update - Touch Screen                        *
*********************************************************************
%}
  @RtvFldDesc("ERXHND='$(profileHandle)' and ERTYPE='U'", "SYEERR", "EREERR", fromURL)
  %if (fromURL == "")
      @dtw_assign(fromURL, returnURL)
  %endif
  %if ( @dtw_rpos(".d2w", $(fromURL)) > "0")
      @dtw_assign(fromURL, "$(homeURL)$(cGIPath)$(fromURL)")
  %else
      @dtw_assign(fromURL, "$(homeURL)$(phpPath)$(fromURL)")
  %endif
  <meta http-equiv="refresh" content="1; URL=$(fromURL)&amp;confMessage=@dtw_rurlescseq(confMessage)">