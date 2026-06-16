%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Decat Employee Selection Column Errors                      *
*********************************************************************
%}
  @DecatErr_Field("@@rpsq", "reportSequence") @dtw_assign(Err_SLRPSQ,  fieldValue)
  @DecatErr_Field("@@actv", "activeEmpl")     @dtw_assign(Err_SLACTV,  fieldValue)
  @DecatErr_Field("@@term", "terminatedEmpl") @dtw_assign(Err_SLTERM,  fieldValue)
  @DecatErr_Field("@@fcmp", "fromCompany")    @dtw_assign(Err_SLFCMP,  fieldValue)
  @DecatErr_Field("@@tcmp", "toCompany")      @dtw_assign(Err_SLTCMP,  fieldValue)
  @DecatErr_Field("@@acmp", "allCompany")     @dtw_assign(Err_SLACMP,  fieldValue)
  @DecatErr_Field("@@ffac", "fromFacility")   @dtw_assign(Err_SLFFAC,  fieldValue)
  @DecatErr_Field("@@tfac", "toFacility")     @dtw_assign(Err_SLTFAC,  fieldValue)
  @DecatErr_Field("@@afac", "allFacility")    @dtw_assign(Err_SLAFAC,  fieldValue)
  @DecatErr_Field("@@floc", "fromLocation")   @dtw_assign(Err_SLFCLO,  fieldValue)
  @DecatErr_Field("@@tloc", "toLocation")     @dtw_assign(Err_SLTLOC,  fieldValue)
  @DecatErr_Field("@@aloc", "allLocation")    @dtw_assign(Err_SLALOC,  fieldValue)
  @DecatErr_Field("@@fdpt", "fromDepartment") @dtw_assign(Err_SLFDPT,  fieldValue)
  @DecatErr_Field("@@tdpt", "toDepartment")   @dtw_assign(Err_SLTDPT,  fieldValue)
  @DecatErr_Field("@@adpt", "allDepartment")  @dtw_assign(Err_SLADPT,  fieldValue)
  @DecatErr_Field("@@fdpt", "fromPrEmpl")     @dtw_assign(Err_SLFPRE,  fieldValue)
  @DecatErr_Field("@@tdpt", "toPrEmpl")       @dtw_assign(Err_SLTPRE,  fieldValue)
  @DecatErr_Field("@@adpt", "allPrEmpl")      @dtw_assign(Err_SLAPRE,  fieldValue)
  @DecatErr_Field("@@fdpt", "fromHrEmpl")     @dtw_assign(Err_SLFHRE,  fieldValue)
  @DecatErr_Field("@@tdpt", "toHrEmpl")       @dtw_assign(Err_SLTHRE,  fieldValue)
  @DecatErr_Field("@@adpt", "allHrEmpl")      @dtw_assign(Err_SLAHRE,  fieldValue)
