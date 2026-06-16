%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Retrieve Column From Selection Criteria                     *
*********************************************************************
%}

%MACRO_FUNCTION Retrieve_Field (IN CHAR(10) fieldName)
{
  @dtw_assign(S0, "")
  @dtw_assign(S1, "")
  @dtw_assign(S2, "")
  @dtw_assign(S3, "")
  @dtw_assign(S4, "")
  @dtw_assign(S5, "")
  @dtw_assign(S6, "")
  @dtw_assign(S7, "")
  @dtw_assign(S8, "")
  @dtw_assign(S9, "")

  @dtw_assign(operand, "")
  @dtw_assign(fromField, "")
  @dtw_assign(toField, "")
  @dtw_assign(seqField, "")

  @dtw_pos("@@$(fieldName)", selectGroupBy, pos)
  %if (pos > "0")
      @dtw_assign(seqField, @dtw_radd(@dtw_rdivide(@dtw_rsubtract(pos, "1"), "9"), "1"))
  %endif

  @dtw_assign(inc, @dtw_radd(@dtw_rlength(fieldName), "3"))
  @dtw_pos("@@$(fieldName)O", selectCriteria, posO)
  %if (posO > "0")
      @dtw_pos("@@$(fieldName)F", selectCriteria, posF)
      @dtw_pos("@@$(fieldName)T", selectCriteria, posT)
      @dtw_pos("@@", selectCriteria, @dtw_radd(posT, "1"), posNext)

      %if (@dtw_radd(posO, inc) < posF)
          @dtw_substr(selectCriteria, @dtw_radd(posO, inc), @dtw_rsubtract(posF, @dtw_radd(posO, inc)), operand)
      %endif
      %if (@dtw_radd(posF, inc) < posT)
          @dtw_substr(selectCriteria, @dtw_radd(posF, inc), @dtw_rsubtract(posT, @dtw_radd(posF, inc)), fromField)
      %endif
      %if (@dtw_radd(posT, inc) < posNext)
          @dtw_substr(selectCriteria, @dtw_radd(posT, inc), @dtw_rsubtract(posNext, @dtw_radd(posT, inc)), toField)
      %endif
      %if (operand == "")
          @dtw_assign(S1, "SELECTED")
      %elif (operand == "BETWEEN")
          @dtw_assign(S2, "SELECTED")
      %elif (operand == "=")
          @dtw_assign(S3, "SELECTED")
      %elif (operand == "<>")
          @dtw_assign(S4, "SELECTED")
      %elif (operand == "<")
          @dtw_assign(S5, "SELECTED")
      %elif (operand == "<=")
          @dtw_assign(S6, "SELECTED")
      %elif (operand == ">")
          @dtw_assign(S7, "SELECTED")
      %elif (operand == ">=")
          @dtw_assign(S8, "SELECTED")
      %elif (operand == "LIKE")
          @dtw_assign(S9, "SELECTED")
      %elif (operand == "NOT LIKE")
          @dtw_assign(S0, "SELECTED")
      %endif
  %endif
%}