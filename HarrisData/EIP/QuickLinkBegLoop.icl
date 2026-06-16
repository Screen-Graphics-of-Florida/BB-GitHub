%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                              *
*  Job: Quick Link Begin Loop                                        *
**********************************************************************
%}

  @dtw_assign(x, "1")
  %while((x <= quicklinkCount) || (quicklinkSelSeq >"0")) {
      %if (quicklinkSelSeq >"0")
          @dtw_assign(x, "$(quicklinkSelSeq)")
      %endif
      @dtw_tb_getv(quicklinkSeqTable, x, "1", quicklinkRef)
      @dtw_lowercase(quicklinkRef,quicklinkRef)
      @dtw_tb_getv(quicklinkSeqTable, x, "2", quicklinkTitle)
      @dtw_tb_getv(quicklinkSeqTable, x, "3", quicklinkMaxRows)
      @dtw_pos("allRows", "$(quicklinkLoaded)", allRowsPos)
      %if (allRowsPos != "0")
          @dtw_assign(quicklinkMaxRows, "9999")
      %elif (quicklinkMaxRows == "0" || quicklinkMaxRows == "" )
          @dtw_assign(quicklinkMaxRows, dspMaxRowsDft)
      %endif
      @dtw_assign(RPT_MAX_ROWS, quicklinkMaxRows)
      @dtw_assign(dspMaxRows, quicklinkMaxRows)

      @dtw_pos(" $(quicklinkRef)", "$(quicklinkLoaded)", qLinkPos)

      %if (qLinkPos != "0" && x != quicklinkSavSeq)
