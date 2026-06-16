%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Build SQL WildCard Selection Variable                       *
*********************************************************************
%}

%Define {	
  wildDisplayTemp   = ""
  wildCardTemp      = ""
%}

%MACRO_FUNCTION Build_WildCard (IN CHAR(10)  fldName,
			                   CHAR(100) fldDesc,
			                   CHAR(100) selData,
			                   CHAR(1)   upperCase,
			                   CHAR(10)  operand,
			                   CHAR(2)   fldType)
{
  @dtw_assign(selData, @dtw_rstrip(selData))
  @dtw_replace(selData, "'", "''", "1", "a", selData)

  %if (selData != "")
      %if ($(operand) == "LIKE" && (wildSearchDft == "1" || wildSearchDft == "2" || wildSearchDft == "3"))
          @dtw_assign(wildPos, @dtw_rpos("*", selData))
          %if (wildPos == "0")
              @dtw_assign(wildPos, @dtw_rpos("?", selData))
          %endif
          %if (wildPos == "0")
              %if (wildSearchDft == "1")
                  @dtw_concat("*", selData, selData)
              %elseif (wildSearchDft == "2")
                  @dtw_concat(selData, "*", selData)
              %elseif (wildSearchDft == "3")
                  @dtw_concat("*", selData, selData)
                  @dtw_concat(selData, "*", selData)
              %endif
          %endif
      %endif


      %if (upperCase == "U")
          @dtw_mUPPERCASE(selData)
      %endif

      %if (operand == "<>")
          @dtw_assign(displayOper, "Not=")
      %else
          @dtw_assign(displayOper, operand)
      %endif

      %if (andOr == "")
          @dtw_assign(andOr, "or")
      %endif

      %if (wildCardTemp == "")
          %if (wildCardSearch == "")
              @dtw_assign(wildCardSearch, "and ( (")
              @dtw_assign(wildDisplayTemp, "&nbsp; &nbsp; &nbsp;")
          %else
              @dtw_replace(wildCardSearch, ")", " ", @dtw_rlastpos(")", wildCardSearch), wildCardSearch)
              @dtw_concat(wildCardSearch, " $(andOr) (", wildCardSearch)
          %endif
      %endif

      %if (wildCardTemp != "")
          @dtw_concat(wildCardTemp, " and ", wildCardTemp)
          @dtw_concat(wildDisplayTemp, " and ", wildDisplayTemp)
      %elseif (wildCardDisplay != "")
          @dtw_concat(wildDisplayTemp, " <br> $(andOr) ", wildDisplayTemp)
      %endif

      %if (fldType == "A")
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(selData) ", wildDisplayTemp)
          @dtw_replace(selData, "?", "_", "1", "a", selData)
          @dtw_replace(selData, "*", "%", "1", "a", selData)
          @dtw_concat(wildCardTemp, " trim($(fldName)) $(operand) '$(selData)'", wildCardTemp)

      %elseif (fldType == "D")
          @dtw_assign(selDate, selData)
          @dtw_insert(dateEdit,  selDate, "2", "1",  selDate)
          @dtw_insert(dateEdit,  selDate, "5", "1",  selDate)
          @DateToCYMD(selData)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(selDate)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) $(selData)", wildCardTemp)

      %elseif (fldType == "DG")
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(selData)", wildDisplayTemp)
          @dtw_replace(selData, "?", "_", "1", "a", selData)
          @dtw_replace(selData, "*", "%", "1", "a", selData)
          @dtw_concat(wildCardTemp, " digits($(fldName)) $(operand) '$(selData)'", wildCardTemp)

      %elseif (fldType == "I")
          %if (selData == "0")
              @dtw_assign(selData, "000000")
          %endif
          @dtw_assign(selDate, selData)
          @dtw_insert(dateEdit,  selDate, "2", "1",  selDate)
          @dtw_insert(dateEdit,  selDate, "5", "1",  selDate)
          @Date_MDY_ISO("$(selData)", dateOut)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(selDate)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) '$(dateOut)'", wildCardTemp)

      %elseif (fldType == "N")
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(selData)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) $(selData)", wildCardTemp)

      %elseif (fldType == "P" || fldType == "PA")
          %if (fldType == "PA")
              @dtw_assign(qte, "'")
          %else
              @dtw_assign(qte, "")
          %endif
          @dtw_assign(fromPhoneNumber, selData)
          @dtw_assign(toPhoneNumber, selData)
          @dtw_assign(count, @dtw_rsubtract("10", @dtw_rlength(selData)))
          %While (count > "0") {
              @dtw_subtract(count, "1", count)
	             @dtw_concat(fromPhoneNumber, "0", fromPhoneNumber)
	             @dtw_concat(toPhoneNumber, "9", toPhoneNumber)
	         %}
          @dtw_concat(wildCardTemp, " ($(fldName) between $(qte)", wildCardTemp)
          @dtw_concat(wildCardTemp, fromPhoneNumber, wildCardTemp)
	         @dtw_concat(wildCardTemp, "$(qte) and $(qte)", wildCardTemp)
	         @dtw_concat(wildCardTemp, toPhoneNumber, wildCardTemp)
	         @dtw_concat(wildCardTemp, "$(qte)) ", wildCardTemp)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) between $(fromPhoneNumber) and $(toPhoneNumber) ", wildDisplayTemp)

      %elseif (fldType == "V")
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(selData)", wildDisplayTemp)
      %endif
  %endif
%}

%MACRO_FUNCTION Range_WildCard (IN CHAR(10)  fldName,
			                   CHAR(100) fldDesc,
			                   CHAR(100) fromData,
			                   CHAR(100) toData,
			                   CHAR(1)   upperCase,
			                   CHAR(10)  operand,
			                   CHAR(3)   fldType)
{
  @dtw_assign(fromData, @dtw_rstrip(fromData))
  @dtw_assign(toData, @dtw_rstrip(toData))

  %if (fromData != "" || toData != "")
      %if ($(operand) == "LIKE" && (wildSearchDft == "1" || wildSearchDft == "2" || wildSearchDft == "3"))
          @dtw_assign(wildPos, @dtw_rpos("*", fromData))
          %if (wildPos == "0")
              @dtw_assign(wildPos, @dtw_rpos("?", fromData))
          %endif
          %if (wildPos == "0")
              %if (wildSearchDft == "1")
                  @dtw_concat("*", fromData, fromData)
              %elseif (wildSearchDft == "2")
                  @dtw_concat(fromData, "*", fromData)
              %elseif (wildSearchDft == "3")
                  @dtw_concat("*", fromData, fromData)
                  @dtw_concat(fromData, "*", fromData)
              %endif
          %endif
      %endif

      %if (upperCase == "U")
          @dtw_mUPPERCASE(fromData)
          @dtw_mUPPERCASE(toData)
      %endif

      %if (operand == "<>")
          @dtw_assign(displayOper, "Not=")
      %else
          @dtw_assign(displayOper, operand)
      %endif

      %if (andOr == "")
          @dtw_assign(andOr, "or")
      %endif

      %if (wildCardTemp == "")
          %if (wildCardSearch == "")
              @dtw_assign(wildCardSearch, "and ( (")
              @dtw_assign(wildDisplayTemp, "&nbsp; &nbsp; &nbsp;")
          %else
              @dtw_replace(wildCardSearch, ")", " ", @dtw_rlastpos(")", wildCardSearch), wildCardSearch)
              @dtw_concat(wildCardSearch, " $(andOr) (", wildCardSearch)
          %endif
      %endif

      %if (wildCardTemp != "")
          @dtw_concat(wildCardTemp, " and ", wildCardTemp)
          @dtw_concat(wildDisplayTemp, " and ", wildDisplayTemp)
      %elseif (wildCardDisplay != "")
          @dtw_concat(wildDisplayTemp, " <br> $(andOr) ", wildDisplayTemp)
      %endif

      %if (fldType == "A")
          %if (operand != "BETWEEN")
              @dtw_replace(fromData, "?", "_", "1", "a", from_Data)
              @dtw_replace(fromData, "*", "%", "1", "a", from_Data)
              @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromData) ", wildDisplayTemp)
              @dtw_concat(wildCardTemp, " trim($(fldName)) $(operand) '$(from_Data)'", wildCardTemp)
          %else
              @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromData) and $(toData)", wildDisplayTemp)
              @dtw_concat(wildCardTemp, " trim($(fldName)) $(operand) '$(fromData)' and '$(toData)'", wildCardTemp)
          %endif

      %elseif (fldType == "D" && operand != "BETWEEN")
          @dtw_assign(fromDate, fromData)
          @dtw_insert(dateEdit,  fromDate, "2", "1",  fromDate)
          @dtw_insert(dateEdit,  fromDate, "5", "1",  fromDate)
          @DateToCYMD(fromData)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromDate)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) $(fromData)", wildCardTemp)

      %elseif (fldType == "D")
          @dtw_assign(fromDate, fromData)
          @dtw_insert(dateEdit,  fromDate, "2", "1",  fromDate)
          @dtw_insert(dateEdit,  fromDate, "5", "1",  fromDate)
          @dtw_assign(toDate, toData)
          @dtw_insert(dateEdit,  toDate, "2", "1",  toDate)
          @dtw_insert(dateEdit,  toDate, "5", "1",  toDate)
          @DateToCYMD(fromData)
          @DateToCYMD(toData)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromDate) and $(toDate)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) $(fromData) and $(toData)", wildCardTemp)

      %elseif (fldType == "TSD" && operand != "BETWEEN")
          @dtw_assign(fromFormat, "*MDY")
          @dtw_assign(toFormat, "*MDYY")
          %while(@dtw_rlength(fromData) != "8") {@dtw_insert("0", fromData, fromData)%}
          @Reformat_Date_4(fromData, fromFormat, toFormat)
          @dtw_insert("/",  fromData, "2", "1",  fromData)
          @dtw_insert("/",  fromData, "5", "1",  fromData)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromData)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) '$(fromData)'", wildCardTemp)

      %elseif (fldType == "TSD")
          @dtw_assign(fromFormat, "*MDY")
          @dtw_assign(toFormat, "*MDYY")
          %while(@dtw_rlength(fromData) != "8") {@dtw_insert("0", fromData, fromData)%}
          @Reformat_Date_4(fromData, fromFormat, toFormat)
          %while(@dtw_rlength(toData) != "8") {@dtw_insert("0", toData, toData)%}
          @Reformat_Date_4(toData, fromFormat, toFormat)
          @dtw_insert("/",  fromData, "2", "1",  fromData)
          @dtw_insert("/",  fromData, "5", "1",  fromData)
          @dtw_insert("/",  toData, "2", "1",  toData)
          @dtw_insert("/",  toData, "5", "1",  toData)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromData) and $(toData)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) '$(fromData)' and '$(toData)'", wildCardTemp)

      %elseif (fldType == "TST" && ((operand == "=" || operand == "<>") && (@dtw_rlength(fromData) == "3" || @dtw_rlength(fromData) == "4")))
          %if (@dtw_rlength(fromData) == "3")
              @dtw_insert("0", fromData, fromData)
          %endif
          @dtw_assign(toData, fromData)

          %while(@dtw_rlength(fromData) != "6") {@dtw_concat(fromData, "0", fromData)%}
          %while(@dtw_rlength(toData) != "6")   {@dtw_concat(toData, "59", toData)%}

          @dtw_insert(":",  fromData, "2", "1",  fromData)
          @dtw_insert(":",  fromData, "5", "1",  fromData)
          @dtw_insert(":",  toData, "2", "1",  toData)
          @dtw_insert(":",  toData, "5", "1",  toData)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromData)", wildDisplayTemp)

          %if (operand == "=")
              @dtw_concat(wildCardTemp, " $(fldName) BETWEEN '$(fromData)' and '$(toData)'", wildCardTemp)
          %else
              @dtw_concat(wildCardTemp, " ($(fldName) < '$(fromData)' or $(fldName) > '$(toData)')", wildCardTemp)
          %endif

      %elseif (fldType == "TST" && operand != "BETWEEN")
          %if (@dtw_rlength(fromData) == "1" || @dtw_rlength(fromData) == "3" || @dtw_rlength(fromData) == "5")
              @dtw_insert("0", fromData, fromData)
          %endif
          %if (@dtw_rlength(fromData) == "4" && (operand == ">" || operand == "<="))
              %while(@dtw_rlength(fromData) != "6") {@dtw_concat(fromData, "59", fromData)%}
          %else
              %while(@dtw_rlength(fromData) != "6") {@dtw_concat(fromData, "0", fromData)%}
          %endif
          @dtw_insert(":",  fromData, "2", "1",  fromData)
          @dtw_insert(":",  fromData, "5", "1",  fromData)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromData)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) '$(fromData)'", wildCardTemp)

      %elseif (fldType == "TST")
          %if (@dtw_rlength(fromData) == "1" || @dtw_rlength(fromData) == "3" || @dtw_rlength(fromData) == "5")
              @dtw_insert("0", fromData, fromData)
          %endif
          %while(@dtw_rlength(fromData) != "6") {@dtw_concat(fromData, "0", fromData)%}
          @dtw_insert(":",  fromData, "2", "1",  fromData)
          @dtw_insert(":",  fromData, "5", "1",  fromData)
          %if (@dtw_rlength(toData) == "1" || @dtw_rlength(toData) == "3" || @dtw_rlength(toData) == "5")
              @dtw_insert("0", toData, toData)
          %endif

          %if (@dtw_rlength(toData) == "4")
              %while(@dtw_rlength(toData) != "6") {@dtw_concat(toData, "59", toData)%}
          %else
              %while(@dtw_rlength(toData) != "6") {@dtw_concat(toData, "0", toData)%}
          %endif
          @dtw_insert(":",  toData, "2", "1",  toData)
          @dtw_insert(":",  toData, "5", "1",  toData)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromData) and $(toData)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) '$(fromData)' and '$(toData)'", wildCardTemp)

      %elseif (fldType == "N" && operand != "BETWEEN")
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromData)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) $(fromData)", wildCardTemp)

      %elseif (fldType == "N")
          @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(fromData) and $(toData)", wildDisplayTemp)
          @dtw_concat(wildCardTemp, " $(fldName) $(operand) $(fromData) and $(toData)", wildCardTemp)

      %elseif (fldType == "P")
          @dtw_assign(fromPhoneNumber, fromData)
          @dtw_assign(toPhoneNumber, fromData)
          @dtw_assign(count, @dtw_rsubtract("10", @dtw_rlength(selData)))
          %While (count > "0") {
              @dtw_subtract(count, "1", count)
	             @dtw_concat(fromPhoneNumber, "0", fromPhoneNumber)
	             @dtw_concat(toPhoneNumber, "9", toPhoneNumber)
	         %}
          @dtw_concat(wildCardTemp, " ($(fldName) between ", wildCardTemp)
          @dtw_concat(wildCardTemp, fromPhoneNumber, wildCardTemp)
	         @dtw_concat(wildCardTemp, " and ", wildCardTemp)
	         @dtw_concat(wildCardTemp, toPhoneNumber, wildCardTemp)
	         @dtw_concat(wildCardTemp, ") ", wildCardTemp)
          @dtw_concat(wildDisplayTemp, "$(fldDesc) between $(fromPhoneNumber) and $(toPhoneNumber) ", wildDisplayTemp)
      %endif
  %endif
%}

%MACRO_FUNCTION OrderBy_Sort (IN  CHAR(100) orderByFld,
			              OUT CHAR(4) sortVar)
{
  @dtw_concat(orderByFld, " DESC", orderByFldD)
  @dtw_assign(fldPosD, @dtw_rpos(orderByFldD, orderBy, "1"))
  @dtw_assign(fldPosA, @dtw_rpos(orderByFld, orderBy, "1"))
  @dtw_assign(sortVar, "")

  %if (fldPosD == "1" || fldPosA == "1")
      @dtw_assign(sortVar, "sort")
  %endif
%}

%MACRO_FUNCTION Named_OrderBy_Sort (IN  CHAR(100) orderByFld,
	                                   IN  CHAR(100) orderByName,
	                                   OUT CHAR(4) sortVar)
{
  @dtw_concat(orderByFld, " DESC", orderByFldD)
  @dtw_assign(fldPosD, @dtw_rpos(orderByFldD, $(orderByName), "1"))
  @dtw_assign(fldPosA, @dtw_rpos(orderByFld, $(orderByName), "1"))
  @dtw_assign(sortVar, "")

  %if (fldPosD == "1" || fldPosA == "1")
      @dtw_assign(sortVar, "sort")
  %endif
%}

%MACRO_FUNCTION Save_WebReg (IN    CHAR(10)   userProfile,
			                   CHAR(64)   profileHandle,
			                   CHAR(50)   d2wName,
			                   CHAR(1025) wildCardSearch,
			                   CHAR(1025) orderBy,
			                   CHAR(1025) orderByDisplay,
			                   CHAR(1025) wildCardDisplay)
{
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)wildCardSearch")
  @dtwr_addentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)wildCardSearch", "$(wildCardSearch)")
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)wildCardDisplay")
  @dtwr_addentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)wildCardDisplay", "$(wildCardDisplay)")
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderBy")
  @dtwr_addentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderBy", "$(orderBy)")
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderByDisplay")
  @dtwr_addentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderByDisplay", "$(orderByDisplay)")
%}

%MACRO_FUNCTION Save_WebReg_OrderBy (IN  CHAR(10)   userProfile,
			                         CHAR(64)   profileHandle,
			                         CHAR(50)   d2wName,
			                         CHAR(1025) orderBy,
			                         CHAR(1025) orderByDisplay)
{
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderBy")
  @dtwr_addentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderBy", "$(orderBy)")
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderByDisplay")
  @dtwr_addentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderByDisplay", "$(orderByDisplay)")
%}

%MACRO_FUNCTION Retrieve_WebReg (IN  CHAR(10)   userProfile,
			                     CHAR(64)   profileHandle,
			                     CHAR(50)   d2wName,
			                 OUT CHAR(1025) wildCardSearch,
			                     CHAR(1025) orderBy,
			                     CHAR(1025) orderByDisplay,
			                     CHAR(1025) wildCardDisplay)
{
  @dtw_assign(wildCardSearch, "")
  @dtw_assign(wildCardDisplay, "")
  @dtw_assign(orderBy, "")
  @dtw_assign(orderByDisplay, "")
  @dtwr_rtventry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)wildCardSearch", wildCardSearch)
  @dtwr_rtventry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)wildCardDisplay", wildCardDisplay)
  @dtwr_rtventry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderBy", orderBy)
  @dtwr_rtventry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderByDisplay", orderByDisplay)
  @dtwr_rtventry("$(webRegPath)$(userProfile).file", "NoRecord", NoRecord)
%}

%MACRO_FUNCTION Delete_WebReg (IN  CHAR(10)   userProfile,
			                   CHAR(64)   profileHandle,
			                   CHAR(50)   d2wName)
{
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)wildCardSearch")
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)wildCardDisplay")
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderBy")
  @dtwr_delentry("$(webRegPath)$(userProfile).file", "$(profileHandle)$(d2wName)orderByDisplay")
%}