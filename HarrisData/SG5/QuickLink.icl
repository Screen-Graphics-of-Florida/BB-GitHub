%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Quick Links Stored Procedure                                 *
**********************************************************************
%}

%{ This function builds the sequence of "quicklink" display.
   Any page using dynamic quicklink sequencing should be calling this function.

   The quicklinkSeqTable includes the following columns:
       1   quicklinkReference (e.g. "demographics" identifies "#demographics")
       2   quicklinkTitle     (e.g. "Demographics")
       3   quicklinkMaxRows   Assigned to RPT_MAX_ROWS
       4   quicklinkImage     link image
       5   quicklinkSeqNbr    Used by RPG program to sort quicklinks.
       6   quicklinkURLID     URL ID.
       7   quicklinkClass     Override CSS Class

   NOTES:
       The quicklinkSeqTable must be sorted in ascending order by
       quicklinkSeqNbr.

       If a quicklink does not appear in the table, the link and the link-to info
       will not display.

       If there are no rows (empty table), NO quicklinks will appear.
%}

%DEFINE{
 quickLinksPerRow      = "5"
 quicklinkSeqTable     = %table
 quicklinkMnuTable     = %table
 quicklinkRef          = ""
 quicklinkTitle        = ""
 quicklinkSelected     = ""
 quicklinkSelSeq       = ""
 quicklinkSavSeq       = ""
 quicklinkLoaded       = ""
 quicklinkMaxRows      = "0"
 quicklinkCount        = "0"
 noInfoFoundMsg        = "No XXX Information Available."
 maxRowsMsg            = "<span class=""legendTitleData"">($(displayMaxRows) Of $(sql_Record_Count) Rows)</span>"
 noMaxRowsMsg          = ""
 orderBy               = ""
 orderByDisplay        = ""
 moreWinVar            = ""
 linkTime              = ""
 attachVarKey          = ""
 attachForDesc         = ""
 attachPrg1            = ""
 attachPrg2            = ""
 attachPrg3            = ""
 attachPrg4            = ""
 attachPrg5            = ""
%}
			
%FUNCTION(DTW_SQL) QuicklinkSequence
				(IN  	CHAR(64) profileHandle,
			         CHAR(2)  dataBaseID,
					   CHAR(50) d2wName,
				OUT  	quicklinkSeqTable)

                   {call $(pgmLibrary)hsyqln_p
%}

%MACRO_FUNCTION Format_Column_Header (IN CHAR(100) columnName,
                                      IN CHAR(100) columnDesc)
{
  @OrderBy_Sort("$(columnName)", sortVar)
  @dtw_assign(columnHdr, "$(columnDesc)")
  %INCLUDE "SelectPageColumnHdr.icl"
%}

%MACRO_FUNCTION Remove_Quick_Link (IN CHAR(100) linkName)
{
  @dtw_mUPPERCASE(linkName)
  @dtw_assign(z, "1")
  %while((@dtw_tb_rgetv(quicklinkSeqTable, z, "1") != "$(linkName)") && (z < quicklinkCount))
      {@dtw_add(z, "1", z)%}
  %if(@dtw_tb_rgetv(quicklinkSeqTable, z, "1") == "$(linkName)")
      @dtw_tb_deleterow(quicklinkSeqTable, z, "1")
      @dtw_subtract(quicklinkCount, "1", quicklinkCount)
  %endif
%}

%MACRO_FUNCTION Remove_Quick_Link_URL (IN CHAR(100) linkName)
{
  @dtw_assign(z, "1")
  %while((@dtw_tb_rgetv(quicklinkSeqTable, z, "1") != "$(linkName)") && (z < quicklinkCount))
      {@dtw_add(z, "1", z)%}
  %if(@dtw_tb_rgetv(quicklinkSeqTable, z, "1") == "$(linkName)")
      @dtw_tb_setv(quicklinkSeqTable, "", "z", "6")
  %endif
%}