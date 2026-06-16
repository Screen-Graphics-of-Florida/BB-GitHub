%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Set Table Row Color                                         *
*********************************************************************
%}
    %if (backGround == tableBackOddRow)
        @dtw_assign(backGround, tableBackEvenRow)
    %else
        @dtw_assign(backGround, tableBackOddRow)
    %endif