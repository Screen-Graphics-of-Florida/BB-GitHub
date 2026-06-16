%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Decat H/R Selection Fields                                  *
*********************************************************************
%}

      @DecatErr_Field("@@fcmp","fromCompany")      @dtw_assign(V_SLFCMP,  fieldValue)
      @DecatErr_Field("@@tcmp","toCompany")        @dtw_assign(V_SLTCMP,  fieldValue)
      @DecatErr_Field("@@acmp","allCompany")       @dtw_assign(V_SLACMP,  fieldValue)
      @DecatErr_Field("@@ffac","fromFacility")     @dtw_assign(V_SLFFAC,  fieldValue)
      @DecatErr_Field("@@tfac","toFacility")       @dtw_assign(V_SLTFAC,  fieldValue)
      @DecatErr_Field("@@afac","allFacility")      @dtw_assign(V_SLAFAC,  fieldValue)
      @DecatErr_Field("@@floc","fromLocation")     @dtw_assign(V_SLFLOC,  fieldValue)
      @DecatErr_Field("@@tloc","toLocation")       @dtw_assign(V_SLTLOC,  fieldValue)
      @DecatErr_Field("@@aloc","allLocation")      @dtw_assign(V_SLALOC,  fieldValue)
      @DecatErr_Field("@@fdpt","fromDepartment")   @dtw_assign(V_SLFDPT,  fieldValue)
      @DecatErr_Field("@@tdpt","toepartment")      @dtw_assign(V_SLTDPT,  fieldValue)
      @DecatErr_Field("@@adpt","allepartment")     @dtw_assign(V_SLADPT,  fieldValue)
