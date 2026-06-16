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

    %if (operandValue == "=") @dtw_assign(selS1, "SELECTED")
    %elif (operandValue == "<>") @dtw_assign(selS2, "SELECTED")
    %elif (operandValue == "<") @dtw_assign(selS3, "SELECTED")
    %elif (operandValue == "<=") @dtw_assign(selS4, "SELECTED")
    %elif (operandValue == ">") @dtw_assign(selS5, "SELECTED")
    %elif (operandValue == ">=") @dtw_assign(selS6, "SELECTED")
    %endif

    <SELECT NAME="$(operNbr)" SIZE=1>
        <OPTION $(S1) $(selS1) VALUE="=">=
        <OPTION $(S2) $(selS2) VALUE="<>">Not=
        <OPTION $(S3) $(selS3) VALUE="<"><
        <OPTION $(S4) $(selS4) VALUE="<="><=
        <OPTION $(S5) $(selS5) VALUE=">">>
        <OPTION $(S6) $(selS6) VALUE=">=">>=
    </SELECT>