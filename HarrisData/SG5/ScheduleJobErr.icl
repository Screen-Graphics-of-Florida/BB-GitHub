%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Return Schedule Job Errors                                  *
*********************************************************************
%}
  @DecatErr_Field("@@jnam", "schJobName")        @dtw_assign(Err_J_JNAM,  fieldValue)
  @DecatErr_Field("@@jobd", "schJobDescription") @dtw_assign(Err_J_JOBD,  fieldValue)
  @DecatErr_Field("@@jobq", "schJobQueue")       @dtw_assign(Err_J_JOBQ,  fieldValue)
  @DecatErr_Field("@@jfrq", "schFrequency")      @dtw_assign(Err_J_JFRQ,  fieldValue)
  @DecatErr_Field("@@jtim", "schTime")           @dtw_assign(Err_J_JTIM,  fieldValue)
  @DecatErr_Field("@@jdat", "schDate")           @dtw_assign(Err_J_JDAT,  fieldValue)
  @DecatErr_Field("@@jday", "schDays")           @dtw_assign(Err_J_JDAY,  fieldValue)