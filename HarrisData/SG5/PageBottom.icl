%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Assign Page Value                                           *
*********************************************************************
%}
    %if (nextPrevPos != "1" && formatToPrint == "")
        <div class="pageBottom">
            %if (START_ROW_NUM > RPT_MAX_ROWS)
                <a href="input$(nextPrevVar)&amp;START_ROW_NUM=@DTW_RSUBTRACT(START_ROW_NUM, RPT_MAX_ROWS)&amp;RPT_MAX_ROWS=$(RPT_MAX_ROWS)">$(previousImage)</a>
            %else
                %if (sql_Record_Count > RPT_MAX_ROWS)
                    $(nextPrevBlank)
                %endif
            %endif

            %if (sql_Record_Count >= rowIndexNext)
                <a href="input$(nextPrevVar)&amp;START_ROW_NUM=$(rowIndexNext)&amp;RPT_MAX_ROWS=$(RPT_MAX_ROWS)">$(nextImage)</a>
            %else
                %if (sql_Record_Count > RPT_MAX_ROWS)
                    $(nextPrevBlank)
                %endif
            %endif
        </div>
    %endif