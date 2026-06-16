%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Submit/Schedule Job Confirmation Message                    *
*********************************************************************
%}
  %if (submitPageTitle != "")
      @DTW_ASSIGN(workTitle, submitPageTitle)
  %else
      @DTW_ASSIGN(workTitle, "Your Request")
  %endif

  %if (submitSchedule == "M")
      @DTW_ASSIGN(confMessage, "$(workTitle) Has Been Submitted For Processing")
  %elif (submitSchedule == "S")
      @DTW_ASSIGN(confMessage, "$(workTitle) Has Been Scheduled For Processing")
  %endif