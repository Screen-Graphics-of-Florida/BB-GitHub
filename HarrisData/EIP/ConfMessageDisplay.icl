%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Confirmation Message Display                                *
*********************************************************************
%}
  %if (confMessage !="")
      <div class="confMsg">
          $(confMessage)
      </div>
      @dtw_assign(confMessage, "")
  %endif