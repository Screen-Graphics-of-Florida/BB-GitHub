%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Page Title Submit/Schedule Display Top                      *
*********************************************************************
%}
  <table $(contentTable)>
	       <colgroup>
	           <col width="80%">
	           <col width="15%">
        <tr><td><h1>$(page_title)</h1></td>
            <td class="toolbar">
                <a $(sbmSchConfirm) href="javascript:check(document.Chg)">$(sbmSchdImage)</a>
                %INCLUDE "HelpPage.icl"
            </td>
        </tr>
  </table>