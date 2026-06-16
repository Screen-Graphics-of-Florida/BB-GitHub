%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Employee Report Selection, Set Fields For Edit              *
*********************************************************************
%}

  @dtw_mUPPERCASE(allCompany)
  @dtw_mUPPERCASE(allFacility)
  @dtw_mUPPERCASE(fromLocation)
  @dtw_mUPPERCASE(toLocation)
  @dtw_mUPPERCASE(allLocation)
  @dtw_mUPPERCASE(fromDepartment)
  @dtw_mUPPERCASE(toDepartment)
  @dtw_mUPPERCASE(allDepartment)
  @dtw_mUPPERCASE(allPrEmpl)
  @dtw_mUPPERCASE(allHrEmpl)

  @dtw_assign(fromCompany,@dtw_rstrip(fromCompany))
  @dtw_assign(toCompany,@dtw_rstrip(toCompany))
  @dtw_assign(fromFacility,@dtw_rstrip(fromFacility))
  @dtw_assign(toFacility,@dtw_rstrip(toFacility))
  @dtw_assign(fromLocation,@dtw_rstrip(fromLocation))
  @dtw_assign(toLocation,@dtw_rstrip(toLocation))
  @dtw_assign(fromDepartment,@dtw_rstrip(fromDepartment))
  @dtw_assign(toDepartment,@dtw_rstrip(toDepartment))
  @dtw_assign(fromPrEmpl,@dtw_rstrip(fromPrEmpl))
  @dtw_assign(toPrEmpl,@dtw_rstrip(toPrEmpl))
  @dtw_assign(fromHrEmpl,@dtw_rstrip(fromHrEmpl))
  @dtw_assign(toHrEmpl,@dtw_rstrip(toHrEmpl))

  %if (seqAlpha != "1")
      @dtw_assign(seqAlpha, "0")
  %endif
  %if (seqPrEmpl != "1")
      @dtw_assign(seqPrEmpl, "0")
  %endif
  %if (seqHrEmpl != "1")
      @dtw_assign(seqHrEmpl, "0")
  %endif
  %if (activeEmpl != "1")
      @dtw_assign(activeEmpl, "0")
  %endif
  %if (terminatedEmpl != "1")
      @dtw_assign(terminatedEmpl, "0")
  %endif
  %if (allCompany != "ALL")
      @dtw_assign(allCompany, "")
  %endif

  %if (allFacility != "ALL")
      @dtw_assign(allFacility, "")
  %endif
  %if (allLocation != "ALL")
      @dtw_assign(allLocation, "")
  %endif

  %if (allDepartment != "ALL")
      @dtw_assign(allDepartment, "")
  %endif

  %if (allPrEmpl != "ALL")
      @dtw_assign(allPrEmpl, "")
  %endif

  %if (allHrEmpl != "ALL")
      @dtw_assign(allHrEmpl, "")
  %endif
