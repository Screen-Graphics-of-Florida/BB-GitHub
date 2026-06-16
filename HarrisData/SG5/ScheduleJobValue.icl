%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Return Schedule Job Values                                  *
*********************************************************************
%}
  @Decat_Field("@@jnam")   @dtw_assign(V_J_JNAM,  fieldValue)
  @Decat_Field("@@jobd")   @dtw_assign(V_J_JOBD,  fieldValue)
  @Decat_Field("@@jobq")   @dtw_assign(V_J_JOBQ,  fieldValue)
  @Decat_Field("@@jfrq")   @dtw_assign(V_J_JFRQ,  fieldValue)
  @Decat_Field("@@jtim")   @dtw_assign(V_J_JTIM,  fieldValue)
  @Decat_Field("@@jdat")   @dtw_assign(V_J_JDAT,  fieldValue)
  @Decat_Field("@@jday")   @dtw_assign(V_J_JDAY,  fieldValue)