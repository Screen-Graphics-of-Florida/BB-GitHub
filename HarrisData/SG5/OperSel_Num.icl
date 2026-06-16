%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: SQL Operand Selection List                                  *
*********************************************************************
%}

    <SELECT NAME="$(operNbr)" SIZE=1>
        <OPTION $(S1) VALUE="">
        <OPTION $(S2) VALUE="BETWEEN">Between
        <OPTION $(S3) VALUE="=">Equal To
        <OPTION $(S4) VALUE="<>">Not Equal To
        <OPTION $(S5) VALUE="<">Less Than
        <OPTION $(S6) VALUE="<=">Less Than Or Equal To
        <OPTION $(S7) VALUE=">">Greater Than
        <OPTION $(S8) VALUE=">=">Greater Than Or Equal To
    </SELECT>