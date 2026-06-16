%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Build Selection Criteria                                    *
*********************************************************************
%}
%MACRO_FUNCTION Build_Selection (IN CHAR(10) fieldName,
			                    CHAR(30) fieldDesc,
			                    CHAR(1)  alphNum)
{
  @dtw_assign(operand, "")
  @dtw_assign(operDesc, "")
  @dtw_assign(fromField, "")
  @dtw_assign(toField, "")
  @dtw_assign(selectData, "")
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
          @CalcDateWithOffset(fromField)
      %endif
      %if (@dtw_radd(posT, inc) < posNext)
          @dtw_substr(selectCriteria, @dtw_radd(posT, inc), @dtw_rsubtract(posNext, @dtw_radd(posT, inc)), toField)
          @CalcDateWithOffset(toField)
      %endif
  %endif

  %if (alphNum == "D")
      %if (fromField > "0")
          @dtw_insert(dateEdit, fromField, "2", "1", fromField)
          @dtw_insert(dateEdit, fromField, "5", "1", fromField)
      %endif
      %if (toField > "0")
          @dtw_insert(dateEdit, toField, "2", "1", toField)
          @dtw_insert(dateEdit, toField, "5", "1", toField)
      %endif
  %endif

  %if (@dtw_rpos("@@$(fieldName)", selectGroupBy) > "0" || posO > "0")
      %if (operand == "BETWEEN")
          @dtw_assign(operDesc, "Between")
      %elif (operand == "LIKE")
          @dtw_assign(operDesc, "Like")
      %elif (operand == "NOT LIKE")
          @dtw_assign(operDesc, "Not Like")
      %elif (operand == "=")
          @dtw_assign(operDesc, "Equal To")
      %elif (operand == "<>")
          @dtw_assign(operDesc, "Not Equal To")
      %elif (operand == "<")
          @dtw_assign(operDesc, "Less Than")
      %elif (operand == "<=")
          @dtw_assign(operDesc, "Less Than Or Equal To")
      %elif (operand == ">")
          @dtw_assign(operDesc, "Greater Than")
      %elif (operand == ">=")
          @dtw_assign(operDesc, "Greater Than Or Equal To")
      %endif

      <tr><td class="dsphdr">$(fieldDesc)</td>
          <td class="dspalph">$(operDesc)</td>
          <td class="dspalph"> &nbsp; $(fromField)</td>
          <td class="dspalph"> &nbsp; $(toField)</td>
          <td class="dspcode">$(seqField)</td>
      </tr>
  %endif
%}