%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Build Selection Criteria                                    *
*********************************************************************
%}
%MACRO_FUNCTION Build_Select (IN CHAR(10) fieldName,
			                 CHAR(30) operand,
                                 CHAR(30) fromField,
                                 CHAR(30) toField,
                                 CHAR(2)  groupBy)
{
  %if (operand != "")
      @dtw_strip(fieldName, workField)
      @dtw_concat(selectCriteria, "@@$(workField)O$(operand)", selectCriteria)
      @dtw_concat(selectCriteria, "@@$(workField)F$(fromField)", selectCriteria)
      @dtw_concat(selectCriteria, "@@$(workField)T$(toField)", selectCriteria)
  %endif

  %if (groupBy > "00" && groupBy <= "99")
      %if (groupBy < "02")
          @dtw_assign(startPos, groupBy)
      %else
          @dtw_assign(startPos, @dtw_radd(@dtw_rmultiply(@dtw_rsubtract(groupBy, "1"), "9"), "1"))
      %endif

      %if (@dtw_rsubstr(selectGroupBy, startPos, "9") == "         ")
          @dtw_replace(selectGroupBy, "         ", "@@$(fieldName)", startPos, "F", selectGroupBy)
      %endif
  %endif
%}