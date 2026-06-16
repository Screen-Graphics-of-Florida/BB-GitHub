%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Page Title Maintenance Display                              *
*********************************************************************
%}
  <table $(contentTable)>
	       <colgroup>
	           <col width="80%">
	           <col width="15%">
        <tr><td><h1>$(page_title)</h1></td>
            <td class="toolbar">
                %if ((sec_01 != "N" && (maintenanceCode == "A" || maintenanceCode == "Z")) || (sec_02 != "N" && maintenanceCode == "C") || (maintenanceCode != "A" && maintenanceCode != "C" && maintenanceCode != "D" && maintenanceCode != "Z"))
                    <a href="javascript:check(document.Chg)">$(acceptImageMed)</a>
                %endif
                %if (wfInstance > "0")
                    <a onClick="return confirmCancelWF()" href="$(cancelWFURL)">$(cancelImageMed)</a>
                %elif (backURL != "")
                    <a href="$(backURL)">$(cancelImageMed)</a>
                %else
                    <a href="javascript:history.back()">$(cancelImageMed)</a>
                %endif
                %if (sec_03 != "N" && maintenanceCode == "C")
                    <a onClick="return confirmDelete()" href="$(deleteURL)">$(deleteImageMed)</a>
                %endif

                @dtw_assign(medIcon, "Y")
                %INCLUDE "HelpPage.icl"
            </td>
        </tr>
  </table>