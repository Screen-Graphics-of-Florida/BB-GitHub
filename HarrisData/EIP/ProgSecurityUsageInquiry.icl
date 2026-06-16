%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Program Security Usage Inquiry                              *
*********************************************************************
%}

    %if (d2wName != "")
        @RtvFldDesc("USUSER='$(userProfile)'", "SYUSER", "USAASI", allowSecInq)
        %if (allowSecInq == "Y")
            %if (d2wNameExt != "")
                @dtw_assign(progName, "$(d2wName) $(d2wNameExt)%")
            %else
                @dtw_assign(progName, "$(d2wName)%")
            %endif
            @dtw_assign(progNameU, @dtw_ruppercase(progName))
            @RtvFldDesc("PSPGTP='SCRIPT' and PSPGNMU LIKE '$(progNameU)' and (PSPOSP<>' ' or PSUVFN<>' ')", "SYPSUM", "char(count(PSPGNM))", progSecCnt)
            %if (progSecCnt > "0")
                <a href="$(homeURL)$(cGIPath)ProgSecurityUsageInquiry.d2w/REPORT$(genericVarBase)&amp;progType=SCRIPT&amp;progName=@dtw_rurlescseq(progName)&amp;orderBy=PSDESCU&amp;formatToPrint=Y&amp;hideSelectCriteria=Y"  onclick="$(inquiryWinVar)">$(securityInqImage)</a>
            %endif
        %endif
    %endif