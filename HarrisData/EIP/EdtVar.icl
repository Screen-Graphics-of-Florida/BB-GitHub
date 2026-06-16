%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Decatonate/Concatonate EdtVar                               *
*********************************************************************
%}

%Define {
  fieldValue              = ""
  operandValue            = ""
  focusField              = ""
  edtVar                  = ""
  errVar                  = ""
  userEdtVar              = ""
  errorColor              = ""
  errorSpan               = ""
  textOvr                 = ""
  typeError               = "E"
  typeReset               = "{Reset}"
  typeValue               = "V"
  typeUserDef             = "D"
  typeParmDef             = "W"
  pgmOptAuth              = ""
%}

%MACRO_FUNCTION Decat_Field (IN CHAR(6) fieldName) {
  @dtw_assign(fieldValue, "")
  @dtw_pos("$(fieldName)", edtVar, pos)
  %if (pos > "0")
      @dtw_pos("}{", edtVar, @dtw_radd(pos, "1"), posNext)
      @dtw_substr(edtVar, @dtw_radd(pos, "6"), @dtw_rsubtract(posNext, @dtw_radd(pos, "6")), fieldValue)
  %endif
%}

%MACRO_FUNCTION Decat_Negative (IN CHAR(6) fieldName) {
  @dtw_assign(fieldValue, "")
  @dtw_pos("$(fieldName)", edtVar, pos)
  %if (pos > "0")
      @dtw_pos("}{", edtVar, @dtw_radd(pos, "1"), posNext)
      @dtw_substr(edtVar, @dtw_radd(pos, "6"), @dtw_rsubtract(posNext, @dtw_radd(pos, "6")), fieldValue)
  %endif
  @dtw_lastpos("-", fieldValue, pos)
  %if (pos > "0")
      @dtw_concat("-", @dtw_rsubstr(fieldValue, "1", @dtw_rsubtract(pos, "1")), fieldValue)
  %endif
%}

%MACRO_FUNCTION Concat_Field (IN CHAR(6)     fieldName,
			                 CHAR(32000) fieldValue)
{
  %if (edtVar == "")
      @dtw_concat(edtVar, "$(fieldName)$(fieldValue)", edtVar)
  %else
      @dtw_concat(edtVar, "}{$(fieldName)$(fieldValue)", edtVar)
  %endif
%}

%MACRO_FUNCTION Concat_Error (IN CHAR(6)     fieldName,
			                 CHAR(32000) fieldValue)
{
  %if (errVar == "")
      @dtw_concat(errVar, "$(fieldName)$(fieldValue)", errVar)
  %else
      @dtw_concat(errVar, "}{$(fieldName)$(fieldValue)", errVar)
  %endif
%}

%{ EditVar Error Set/Retrieve %}
%FUNCTION(dtw_directcall) EdtVarErr (IN    CHAR(64)    profileHandle,
                                           CHAR(1)     typeValue,
			                     INOUT CHAR(32000) edtVar)
  {%EXEC {HSYEER_W.PGM %}
%}

%MACRO_FUNCTION DecatErr_Field (IN CHAR(6)  fieldName,
                                   CHAR(10) inputField)
{
  @dtw_assign(fieldValue, "")
  @dtw_pos("$(fieldName)", errVar, pos)
  %if (pos > "0")
      @dtw_pos("}{", errVar, @dtw_radd(pos, "1"), posNext)
      @dtw_substr(errVar, @dtw_radd(pos, "6"), @dtw_rsubtract(posNext, @dtw_radd(pos, "6")), fieldValue)
      %if (focusField == "")
          @dtw_assign(focusField, inputField)
      %endif
  %endif
%}

%{ EditVar Error Set/Retrieve %}
%FUNCTION(dtw_directcall) ErrVarErr (INOUT CHAR(64)    profileHandle,
                                           CHAR(1)     typeError,
			                           CHAR(32000) errVar)
  {%EXEC {HSYEER_W.PGM %}
%}

%MACRO_FUNCTION Decat_UserDef_Field (IN CHAR(6) fieldName) {
  @dtw_assign(fieldValue, "")
  @dtw_pos("$(fieldName)", userEdtVar, pos)
  %if (pos > "0")
      @dtw_pos("}{", userEdtVar, @dtw_radd(pos, "1"), posNext)
      @dtw_substr(userEdtVar, @dtw_radd(pos, "6"), @dtw_rsubtract(posNext, @dtw_radd(pos, "6")), fieldValue)
  %endif
%}

%{ UserEdtVar Error Set/Retrieve %}
%FUNCTION(dtw_directcall) UsrVarErr (IN    CHAR(64)    profileHandle,
                                           CHAR(1)     typeUserDef,
			                     INOUT CHAR(32000) userEdtVar)
  {%EXEC {HSYEER_W.PGM %}
%}

%MACRO_FUNCTION Decat_WFParm_Field (IN CHAR(6) fieldName) {
  @dtw_assign(fieldValue, "")
  @dtw_assign(operandValue, "")
  @dtw_pos("$(fieldName)", parmEdtVar, pos)
  %if (pos > "0")
      @dtw_pos("}{", parmEdtVar, @dtw_radd(pos, "1"), posNext)
      @dtw_substr(parmEdtVar, @dtw_radd(pos, "6"), @dtw_rsubtract(posNext, @dtw_radd(pos, "6")), operandValue)

      @dtw_assign(pos, posNext)
      @dtw_pos("}{", parmEdtVar, @dtw_radd(pos, "2"), posNext)
      @dtw_substr(parmEdtVar, @dtw_radd(pos, "2"), @dtw_rsubtract(posNext, @dtw_radd(pos, "2")), fieldValue)
  %endif
%}

%{ ParmEdtVar Error Set/Retrieve %}
%FUNCTION(dtw_directcall) ParmVarErr (IN    CHAR(64)    profileHandle,
                                            CHAR(1)     typeParmDef,
                                      INOUT CHAR(32000) parmEdtVar)
  {%EXEC {HSYEER_W.PGM %}
%}

%MACRO_FUNCTION DspErrMsg (IN CHAR(100) inputField) {
  %if (inputField != "")
      <tr><td>&nbsp;</td><td class="error" colspan="10">$(inputField)</td></tr>
  %endif
%}

%MACRO_FUNCTION  SetTextOvr (IN CHAR(10) inputField) {
  %if (inputField != "")
      @dtw_assign(textOvr, fldTextErrOvr)
  %else
      @dtw_assign(textOvr, "")
  %endif
%}

%MACRO_FUNCTION  ReqTextOvr (IN CHAR(10) inputField) {
  %if (inputField == "")
      @dtw_assign(textOvr, fldTextErrOvr)
  %endif
%}

%MACRO_FUNCTION  SetErrorSpan (IN CHAR(100) inputField) {
  %if (inputField != "")
      @dtw_assign(errorSpan, "<span title=""$(inputField)"">")
      @dtw_assign(errorColor, "style=background-color:red;")
  %else
      @dtw_assign(errorSpan, "<span>")
      @dtw_assign(errorColor, "")
  %endif
%}