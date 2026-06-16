%{
*********************************************************************
* Copr 1979 2003 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Return Schedule Job Values                                  *
*********************************************************************
%}
      @dtw_mUPPERCASE(schJobName)
      @dtw_mUPPERCASE(schJobDescription)
      @dtw_mUPPERCASE(schJobQueue)
      @dtw_mUPPERCASE(schFrequency)
      @dtw_mUPPERCASE(schTime)
      @dtw_mUPPERCASE(schDate)
      @dtw_mUPPERCASE(schDays)

      @Concat_Field("@@sbjb", submitSchedule)
      @Concat_Field("@@jnam", schJobName)
      @Concat_Field("@@jobd", schJobDescription)
      @Concat_Field("@@jobq", schJobQueue)
      @Concat_Field("@@jfrq", schFrequency)
      @Concat_Field("@@jtim", schTime)
      @Concat_Field("@@jdat", schDate)
      @Concat_Field("@@jday", schDays)
