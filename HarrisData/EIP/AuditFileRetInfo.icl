%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Audit Table Retrieve Information                             *
**********************************************************************
%}

  @dtw_assign(edtVar, "")
  @Concat_Field("@@comp", prCompany)
  @Concat_Field("@@facl", prFacility)
  @Concat_Field("@@empl", prEmployee)
  @Concat_Field("@@pecp", hrCompany)
  @Concat_Field("@@pemp", hrEmployee)
  @Concat_Field("@@tstp", timestamp)
  @Concat_Field("@@usql", uv_Sql)
  @dtw_concat(edtVar, "}{", edtVar)

  @Ret_Audit_Info(edtVar)


  @Decat_Field("@@faud")      @dtw_assign(firstAudit, fieldValue)
  @Decat_Field("@@laud")      @dtw_assign(lastAudit, fieldValue)
  @Decat_Field("@@paud")      @dtw_assign(prevAudit, fieldValue)
  @Decat_Field("@@naud")      @dtw_assign(nextAudit, fieldValue)

  @dtw_assign(edtVar, "")
