%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Alert Message Display                                       *
*********************************************************************
%}
  %if (alertMessage !="")
      <script type="text/javascript">
          alert("$(alertMessage)");
      </script>
      @dtw_assign(alertMessage, "")
  %endif