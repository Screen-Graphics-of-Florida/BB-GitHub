%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Kanban Maintain                                             *
*********************************************************************
%}

  <table $(contentTable)>
      <colgroup>
          <col width="15%">
          <col width="15%">
      @Format_Code(partType, F_partType)
      @Format_Code(policyCode, F_policyCode)
      <tr><td class="dsphdr">Part Type</td>
          <td class="dspalph">$(partTypeDesc)</td>
          <td class="dspalph">$(F_partType) &nbsp; </td>
          <td class="dsphdr">Kanban Policy Code</td>
          <td class="dspalph">$(policyDesc)</td>
          <td class="dspalph">$(F_policyCode)</td>
      </tr>
      @Format_Code(containerID, F_containerID)
      <tr><td class="dsphdr">Number Of Containers</td>
          <td class="dspalph" colspan=2> $(numberContainer)</td>
          <td class="dsphdr">Container ID Code</td>
          <td class="dspalph">$(containerDesc)</td>
          <td class="dspalph">$(F_containerID)</td>
      </tr>
      <tr><td class="dsphdr">Container Adjustment</td>
          <td class="dspalph" colspan=2> $(containerAdjustment)</td>
          <td class="dsphdr">Containers In Use</td>
          <td class="dspalph">$(containerInUse)</td>
      </tr>
      <tr><td class="dsphdr">Fixed Order Quantity</td>
          <td class="dspalph" colspan=2> $(orderQuantity)</td>
          <td class="dsphdr">Kanban Order Point</td>
          <td class="dspalph">$(orderPoint)</td>
      </tr>
      %if (vendorNumber != "0")
          @Format_Code(vendorNumber, F_vendorNumber)
          <tr><td class="dsphdr">Vendor/Customer Number</td>
              <td class="dspalph">$(vendorName)</td>
              <td class="dspalph">$(F_vendorNumber)</td>
          </tr>
      %endif
      %if (analystNumber != "0")
          @Format_Code(analystNumber, F_analystNumber)
          <tr><td class="dsphdr">Analyst Number</td>
              <td class="dspalph">$(analystName)</td>
              <td class="dspalph">$(F_analystNumber)</td>
          </tr>
      %endif
      @Format_Code(documentDefinition, F_documentDefinition)
      <tr><td class="dsphdr">Kanban Document Defn</td>
          <td class="dspalph">$(documentDesc)</td>
          <td class="dspalph">$(F_documentDefinition)</td>
          %if (d2wName == "KanbanCardMaintain.d2w")
              <td class="dsphdr">Kanban Turnaround</td>
              <td class="dspalph">$(turnaroundNumber)</td>
          %endif
      </tr>
  </table>

  $(hrTagAttr)
