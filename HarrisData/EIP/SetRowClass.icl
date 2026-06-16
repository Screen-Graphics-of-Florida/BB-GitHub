%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Set Table Row Class                                         *
*********************************************************************
%}
    %if (rowClass == "oddrow")
        @dtw_assign(rowClass, "evenrow")
    %else
        @dtw_assign(rowClass, "oddrow")
    %endif