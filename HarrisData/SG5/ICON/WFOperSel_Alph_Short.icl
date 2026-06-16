%{
*********************************************************************
* Copr 1979 2005 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: SQL Operand Selection List                                  *
*********************************************************************
%}

    @dtw_assign(selS1, "")
    @dtw_assign(selS2, "")
    @dtw_assign(selS3, "")
    @dtw_assign(selS4, "")
    @dtw_assign(selS5, "")
    @dtw_assign(selS6, "")
    @dtw_assign(selS7, "")

    %if (operandValue == "LIKE") @dtw_assign(selS1, "SELECTED")
    %elseif (operandValue == "=") @dtw_assign(selS2, "SELECTED")
    %elseif (operandValue == "<>") @dtw_assign(selS3, "SELECTED")
    %elseif (operandValue == "<") @dtw_assign(selS4, "SELECTED")
    %elseif (operandValue == "<=") @dtw_assign(selS5, "SELECTED")
    %elseif (operandValue == ">") @dtw_assign(selS6, "SELECTED")
    %elseif (operandValue == ">=") @dtw_assign(selS7, "SELECTED")
    %endif

    <SELECT NAME="$(operNbr)" SIZE=1>
        <OPTION $(S1) $(selS1) VALUE="LIKE">Like
        <OPTION $(S2) $(selS2) VALUE="=">=
        <OPTION $(S3) $(selS3) VALUE="<>">Not=
        <OPTION $(S4) $(selS4) VALUE="<"><
        <OPTION $(S5) $(selS5) VALUE="<="><=
        <OPTION $(S6) $(selS6) VALUE=">">>
        <OPTION $(S7) $(selS7) VALUE=">=">>=
    </SELECT>