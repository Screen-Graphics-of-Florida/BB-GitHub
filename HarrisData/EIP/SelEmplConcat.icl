%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Employee Report Selection, Concat Fields For Edit           *
*********************************************************************
%}

  @Concat_Field("@@rpsq", emplSequence)
  @Concat_Field("@@actv", activeEmpl)
  @Concat_Field("@@term", terminatedEmpl)
  @Concat_Field("@@fcmp", fromCompany)
  @Concat_Field("@@tcmp", toCompany)
  @Concat_Field("@@acmp", allCompany)
  @Concat_Field("@@ffac", fromFacility)
  @Concat_Field("@@tfac", toFacility)
  @Concat_Field("@@afac", allFacility)
  @Concat_Field("@@floc", fromLocation)
  @Concat_Field("@@tloc", toLocation)
  @Concat_Field("@@aloc", allLocation)
  @Concat_Field("@@fdpt", fromDepartment)
  @Concat_Field("@@tdpt", toDepartment)
  @Concat_Field("@@adpt", allDepartment)
  @Concat_Field("@@fpre", fromPrEmpl)
  @Concat_Field("@@tpre", toPrEmpl)
  @Concat_Field("@@apre", allPrEmpl)
  @Concat_Field("@@fhre", fromHrEmpl)
  @Concat_Field("@@thre", toHrEmpl)
  @Concat_Field("@@ahre", allHrEmpl)
