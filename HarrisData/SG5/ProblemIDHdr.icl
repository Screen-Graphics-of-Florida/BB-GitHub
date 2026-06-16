%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*                                                                    *
*  Job: Software Update Header                                       *
**********************************************************************
%}

@dtw_assign(fixlib, V_PRPGMS)

@Format_Header("CPP Release", "$(V_PRCLRL)/$(V_PRCLLL)", "")
%if (V_PRENVR=="F")
    @Format_Header("Required Refresh Level", $(V_PRDFRF), "")
%endif
@Format_Header_URL("Software Update", $(problemDescription), $(problemIDNumber), "$(homeURL)$(cGIPath)ProblemIDSelect.d2w/REPORT$(d2wVarBase)")
