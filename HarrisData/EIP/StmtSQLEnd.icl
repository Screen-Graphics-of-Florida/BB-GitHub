%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Set SQL Optimize                                            *
*********************************************************************
%}

    %if (formatToPrint == "")
        %if (dspMaxRows == "")
            @dtw_assign(dspMaxRows, "10")
        %endif
        @dtw_concat(stmtSQL, " For Fetch Only with NC Optimize For $(dspMaxRows) Rows @@endsql", stmtSQL)
    %else
        %if (prtMaxRows == "")
            @dtw_assign(prtMaxRows, "All")
        %endif
        @dtw_concat(stmtSQL, " For Fetch Only with NC Optimize For $(prtMaxRows) Rows @@endsql", stmtSQL)
    %endif
