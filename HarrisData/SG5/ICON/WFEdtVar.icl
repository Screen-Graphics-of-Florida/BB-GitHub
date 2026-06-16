%{
*********************************************************************
* Copr 1979 2005 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Decatonate/Concatonate EdtVar                               *
*********************************************************************
%}

%Define {
  testParameter           = ""
  parameter               = ""
  fieldValue              = ""
  prvValue                = ""
  posNext                 = ""
  edtVar                  = ""
  prvVar                  = ""
%}

%MACRO_FUNCTION WF_Decat_EdtVar (IN CHAR(5) startPosition)
{
  @dtw_assign(parameter, "")
  @dtw_assign(fieldValue, "")
  @dtw_pos("@@", edtVar, $(startPosition), pos)

  %if (pos > "0")
      @dtw_substr(edtVar, pos, "6",testParameter)
      @dtw_strip(@dtw_rReplace(testParameter, "@", ""), parameter)
      @dtw_pos("}{", edtVar, @dtw_radd(pos, "1"), posNext)
      @dtw_substr(edtVar, @dtw_radd(pos, "6"), @dtw_rsubtract(posNext, @dtw_radd(pos, "6")), fieldValue)
  %endif
%}

%MACRO_FUNCTION WF_Decat_PrvValue (IN CHAR(6) parameter)
{
  @dtw_assign(prvValue, "")
  @dtw_pos("$(parameter)", prvVar, "1", pos)
  %if (pos > "0")
      @dtw_pos("}{", prvVar, @dtw_radd(pos, "1"), posNext)
      @dtw_substr(prvVar, @dtw_radd(pos, "6"), @dtw_rsubtract(posNext, @dtw_radd(pos, "6")), prvValue)
  %endif
%}
