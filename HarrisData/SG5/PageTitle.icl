%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Page Title Display                                          *
*********************************************************************
%}
    <table $(contentTable)>
	       <colgroup>
	           <col width="80%">
	           <col width="15%">
        <tr><td><h1>$(page_title)</h1></td>
            <td class="toolbar">
                %INCLUDE "HelpPage.icl"
                %if (displayCloseIcon == "Y")
                    &nbsp;<a href="javascript:window.close()">$(closeImageMed)</a>
                %endif
            </td>
        </tr>
    </table>
