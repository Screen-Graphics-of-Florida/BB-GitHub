%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Time Entry                                                  *
*********************************************************************
%}
    <select name="hours">
        @dtw_assign(ux, "00")
        %while(ux <= "23"){
            %if (ux == "$(hrsIn)")
                @dtw_assign(selected, "SELECTED")
            %else
                @dtw_assign(selected, "")
            %endif

            <OPTION $(selected) VALUE="$(ux)">$(ux)
            @dtw_add(ux, "1", ux)
            %if (@dtw_rlength(ux) == "1")
                @dtw_insert("0", ux, "0", ux)
            %endif
        %}
    </select>

    <select name="min">
        @dtw_assign(ux, "00")
        %while(ux <= "59"){
            %if (ux == "$(minIn)")
                @dtw_assign(selected, "SELECTED")
            %else
                @dtw_assign(selected, "")
            %endif

            <OPTION $(selected) VALUE="$(ux)">$(ux)
            @dtw_add(ux, "1", ux)
            %if (@dtw_rlength(ux) == "1")
                @dtw_insert("0", ux, "0", ux)
            %endif
        %}
    </select>

    <select name="sec">
        @dtw_assign(ux, "00")
        %while(ux <= "59"){
            %if (ux == "$(secIn)")
                @dtw_assign(selected, "SELECTED")
            %else
                @dtw_assign(selected, "")
            %endif

            <OPTION $(selected) VALUE="$(ux)">$(ux)
            @dtw_add(ux, "1", ux)
            %if (@dtw_rlength(ux) == "1")
                @dtw_insert("0", ux, "0", ux)
            %endif
        %}
    </select>