%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Return To Search Include                                    *
*********************************************************************
%}
  %if (returnToSearch == "Y")
      <meta http-equiv="refresh" content="0; URL=$(homeURL)$(cGIPath)$(d2wName)/MASTERSEARCH$(d2wVarBase)$(orderByVarBase)&amp;defaultSearch=Y$(advSrchVar)&amp;timeStamp=@dtw_rurlescseq(@dtw_rconcat(@dtw_rdate(),@dtw_rtime("X")))">
  %else
      @Set_SQL(stmtSQL)
  %endif