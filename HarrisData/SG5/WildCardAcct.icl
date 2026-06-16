%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                                                                   *
*  Job: Wild Card Account                                           *
*********************************************************************
%}

%Define {	
  wildDisplayTemp   = ""
  wildCardTemp      = ""
%}

%MACRO_FUNCTION Build_WildCard_Acct (IN CHAR(10)  fldAcct,
                                        CHAR(10)  fldSub,
                                        CHAR(100) fldDesc,
                                        CHAR(100) selAcct,
                                        CHAR(100) selSub,
                                        CHAR(10)  operand)
{
  @dtw_assign(selAcct,@dtw_rstrip(selAcct))
  @dtw_assign(selSub,@dtw_rstrip(selSub))
  @dtw_assign(operand,@dtw_rstrip(operand))

  %if (selAcct != "" || selSub != "")

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
      %elif (wildCardDisplay != "")
          @dtw_concat(wildDisplayTemp, " <br> $(andOr) ", wildDisplayTemp)
      %endif

      @Default_Zero(selAcct)
      %while(@dtw_rlength(selSub) != "4") {@dtw_insert("0", selSub, selSub)%}
      @dtw_concat(wildDisplayTemp, "$(fldDesc) $(displayOper) $(selAcct)-$(selSub)", wildDisplayTemp)

      %if (operand == "=")
          @dtw_concat(wildCardTemp, " $(fldAcct) $(operand) $(selAcct) and $(fldSub) $(operand) $(selSub)", wildCardTemp)
      %elif (operand == "<>")
          @dtw_concat(wildCardTemp, " $(fldAcct) $(operand) $(selAcct) or $(fldSub) $(operand) $(selSub)", wildCardTemp)
      %else
          @dtw_substr(operand, "1", "1", operand1)
          @dtw_concat(wildCardTemp, " $(fldAcct) $(operand1) $(selAcct)", wildCardTemp)
          @dtw_concat(wildCardTemp, " or $(fldAcct) = $(selAcct) and $(fldSub) $(operand) $(selSub)", wildCardTemp)
      %endif
  %endif
%}
