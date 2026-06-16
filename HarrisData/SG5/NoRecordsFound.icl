%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: No Rows Found For Selection Criteria Message                *
*********************************************************************
%}
  %if (sql_Record_Count == "0")
      <tr>
          <td class="confMsg" colspan="10">No Data Found For Selection Criteria</td>
      </tr>                	
  %endif