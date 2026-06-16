%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: SQL Operand Selection List                                  *
*********************************************************************
%}

    <SELECT NAME="$(operNbr)" SIZE=1 STYLE="width:75px;">
        <OPTION $(S1) VALUE="LIKE">Like
        <OPTION $(S0) VALUE="NOT LIKE">Not Like
        <OPTION $(S2) VALUE="=">=
        <OPTION $(S3) VALUE="<>">Not=
        <OPTION $(S4) VALUE="<"><
        <OPTION $(S5) VALUE="<="><=
        <OPTION $(S6) VALUE=">">>
        <OPTION $(S7) VALUE=">=">>=
    </SELECT>