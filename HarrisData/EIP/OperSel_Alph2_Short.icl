%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: SQL Operand Selection List                                  *
*********************************************************************
%}

    <SELECT NAME="$(operNbr)" SIZE=1>
        <OPTION $(S1) VALUE="BETWEEN">Between
        <OPTION $(S2) VALUE="LIKE">Like
        <OPTION $(S0) VALUE="NOT LIKE">Not Like
        <OPTION $(S3) VALUE="=">=
        <OPTION $(S4) VALUE="<>">Not=
        <OPTION $(S5) VALUE="<"><
        <OPTION $(S6) VALUE="<="><=
        <OPTION $(S7) VALUE=">">>
        <OPTION $(S8) VALUE=">=">>=
    </SELECT>