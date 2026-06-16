%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Build SQL Selection                                         *
*********************************************************************
%}

%MACRO_FUNCTION Build_SQL (IN CHAR(10) fieldName,
			              CHAR(1)  fieldType)
{
  %if (fieldType == "A" || fieldType =="I")
      @dtw_assign(q, "'")
  %elif (fieldType == "N")
      @dtw_assign(q, "")
  %endif
  %if (grpFldLvl == "999")
      %if (@dtw_rpos("@@$(fieldName)", selectGroupBy) > "0")
          %if (groupBy == "" && selectGroupBy != "")
              @dtw_assign(x, "3")
              %while(x <= "500" && @dtw_rsubstr(selectGroupBy, x) != ""){
                     %if (groupBy != "")
                         @dtw_concat(groupBy, ", ", groupBy)
                     %endif
                     @dtw_concat(groupBy, @dtw_rsubstr(selectGroupBy, x, "7"), groupBy)
                     @dtw_add(x, "9", x)%}
          %endif

          %if (selectGroupWork != "")
              @dtw_concat(selectGroupWork, " and ", selectGroupWork)
          %endif
          @dtw_concat(selectGroupWork, "$(fieldName)=$(q)@@$(fieldName)$(q)", selectGroupWork)
      %endif

  %else

      @dtw_assign(wrkCnt, "1")
      %if (groupBy == "" && selectGroupBy != "")
          @dtw_assign(x, "3")
          %while(x <= "500" && @dtw_rsubstr(selectGroupBy, x) != "" && wrkCnt <= grpFldLvl){
                 %if (groupBy != "")
                     @dtw_concat(groupBy, ", ", groupBy)
                 %endif
                 @dtw_concat(groupBy, @dtw_rsubstr(selectGroupBy, x, "7"), groupBy)
                 @dtw_add(x, "9", x)
                 @dtw_add(wrkCnt, "1", wrkCnt)
          %}
      %endif

      %if (selectGroupWork == "" && selectGroupBy != "")
          %if (grpFldLvl == "1")
              @dtw_assign(selLevel, grpFldLvl)
          %else
              @dtw_assign(selLevel, @dtw_radd(@dtw_rmultiply(@dtw_rsubtract(grpFldLvl, "1"), "9"), "1"))
          %endif
          @dtw_assign(groupField, @dtw_rsubstr(selectGroupBy, @dtw_radd(selLevel, "2"), "7"))

          %if (@dtw_rstrip(groupField) == @dtw_rstrip(fieldName))
              @dtw_concat(selectGroupWork, "$(groupField)=$(q)@@$(groupField)$(q)", selectGroupWork)
          %endif
      %endif
  %endif

  @dtw_assign(inc, @dtw_radd(@dtw_rlength(fieldName), "3"))
  @dtw_pos("@@$(fieldName)O", selectCriteria, posO)

  %if (posO > "0")
      @dtw_pos("@@$(fieldName)F", selectCriteria, posF)
      @dtw_pos("@@$(fieldName)T", selectCriteria, posT)
      @dtw_pos("@@", selectCriteria, @dtw_radd(posT, "1"), posNext)

      @dtw_assign(operand, "")
      @dtw_assign(fromField, "")
      @dtw_assign(toField, "")

      %if (@dtw_radd(posO, inc) < posF)
          @dtw_substr(selectCriteria, @dtw_radd(posO, inc), @dtw_rsubtract(posF, @dtw_radd(posO, inc)), operand)
      %endif
      %if (@dtw_radd(posF, inc) < posT)
          @dtw_substr(selectCriteria, @dtw_radd(posF, inc), @dtw_rsubtract(posT, @dtw_radd(posF, inc)), fromField)
      %endif
      %if (@dtw_radd(posT, inc) < posNext)
          @dtw_substr(selectCriteria, @dtw_radd(posT, inc), @dtw_rsubtract(posNext, @dtw_radd(posT, inc)), toField)
      %endif

      @CalcDateWithOffset(fromField)
      @CalcDateWithOffset(toField)

      %if (fieldType == "D")
          %if (fromField == "")
              @dtw_assign(fromField, "0000000")
          %else
              @DateToCYMD(fromField)
          %endif
          %if (toField == "")
              @dtw_assign(toField, "9999999")
          %else
              @DateToCYMD(toField)
          %endif
      %endif

      %if (fieldType == "I")
          %while(@dtw_rlength(fromField) != "7")
                {@dtw_insert("0", fromField, fromField)%}
          @Date_To_ISO(fromField, fromField)

          %while(@dtw_rlength(toField) != "7")
                {@dtw_insert("0", toField, toField)%}
          @Date_To_ISO(toField, toField)
      %endif

      %if (fieldType == "A" && fromField == "")
          @dtw_assign(fromField, " ")
      %endif

      %if (operand == "BETWEEN")
          @dtw_concat(selectRecSQL, " and ($(fieldName) BETWEEN $(q)$(fromField)$(q) and $(q)$(toField)$(q) )", selectRecSQL)
      %elif (operand == "LIKE")
          %if (wildSearchDft == "1" || wildSearchDft == "2" || wildSearchDft == "3")
              @dtw_assign(wildPos, @dtw_rpos("*", fromField))
              %if (wildPos == "0")
                  @dtw_assign(wildPos, @dtw_rpos("?", fromField))
              %endif
              %if (wildPos == "0")
                  %if (wildSearchDft == "1")
                      @dtw_concat("*", fromField, fromField)
                  %elif (wildSearchDft == "2")
                      @dtw_concat(fromField, "*", fromField)
                  %elif (wildSearchDft == "3")
                      @dtw_concat("*", fromField, fromField)
                      @dtw_concat(fromField, "*", fromField)
                  %endif
              %endif
          %endif
          @dtw_assign(likeField, fromField)
          @dtw_replace(likeField, "?", "_", "1", "a", likeField)
          @dtw_replace(likeField, "*", "%", "1", "a", likeField)
          @dtw_insert("'", likeField, "0", likeField)
          @dtw_insert("'", likeField, @dtw_rlength(likeField), likeField)
          @dtw_concat(selectRecSQL, " and (TRIM($(fieldName)) $(operand) $(likeField))", selectRecSQL)
      %else
          @dtw_concat(selectRecSQL, " and ($(fieldName) $(operand) $(q)$(fromField)$(q))", selectRecSQL)
      %endif
  %endif
%}