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
            %if (formatToPrint != "Y")
                <td class="toolbar">
                    %INCLUDE "FormatToPrint.icl"
                    %INCLUDE "HelpPage.icl"
                    %if (displayCloseIcon == "Y")
                        &nbsp; <a href="javascript:window.close()">$(closeWinImageLrg)</a>
                    %endif
                </td>
            %endif
        </tr>
    </table>