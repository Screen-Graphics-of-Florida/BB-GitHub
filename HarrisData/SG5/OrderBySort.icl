%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Set Order By Sort                                           *
*********************************************************************
%}

%MACRO_FUNCTION OrderBy_Sort (IN  CHAR(100) orderByFld,
			              OUT CHAR(4) sortedBy)
{
  @dtw_concat(orderByFld, " DESC", orderByFldD)
  @dtw_assign(fldPosD, @dtw_rpos(orderByFldD, orderBy, "1"))
  @dtw_assign(fldPosA, @dtw_rpos(orderByFld, orderBy, "1"))
  @dtw_assign(sortedBy, "")

  %if (fldPosD == "1" || fldPosA == "1")
      @dtw_assign(sortedBy, "sort")
  %endif
%}