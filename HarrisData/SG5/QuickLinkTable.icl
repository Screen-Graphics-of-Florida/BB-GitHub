%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Quick Link Table                                             *
**********************************************************************
%}

  @dtw_assign(RPT_MAX_ROWS, "99")
  @dtw_assign(dspMaxRows, "99")
  @QuicklinkSequence(profileHandle, dataBaseID, d2wName, quicklinkSeqTable)
  @dtw_tb_rows(quicklinkSeqTable, quicklinkCount)
  %if (quicklinkCount == "0" || quicklinkCount == "")
      @dtw_assign(quicklinkCount, "0")
  %endif
