%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                *
*  Job: Employee User View                                          *
*********************************************************************
%}
  @RtvFldDesc("EMCOMP=$(prCompany) and EMFACL=$(prFacility) and EMEMPL=$(prEmployee)", "HREMPL", "EMEMID", employeeID)
  @dtw_assign(attachPrg1, "HREMPL Where EMEMID=$(employeeID)")
  %while(@dtw_rlength(employeeID) != "11") {@dtw_insert("0", employeeID, employeeID)%}
  @dtw_assign(attachVarKey, "$(employeeID)$(attVar)")