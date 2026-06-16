%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Billing Stored Procedure                                     *
**********************************************************************
%}

%DEFINE{
 billingCodeTable     = %table
 orderTypeTable       = %table
%}
			
%FUNCTION(DTW_SQL) Get_BillingTables
				(IN  DECIMAL(4,0) batchNumber,
			    OUT billingCodeTable,
			        orderTypeTable)

                   {call $(pgmLibrary)HOEBCT_P
%}

%MACRO_FUNCTION Billing_Selections (IN  DECIMAL(4,0) batchNumber)
{
  @Get_BillingTables(batchNumber, billingCodeTable, orderTypeTable)

  <table $(contentTable)>
     <tr valign=top>
         <td>
             <table $(contentTable)>
                 <tr>
                     <th class="colhdr">Billing<br>Code</th>
                     <th class="colhdr">Description</th>
                 </tr>

                 @dtw_tb_rows(billingCodeTable, billingCodeCount)
                 @dtw_assign(x, "1")
                 %while(x <= billingCodeCount) {
                    @dtw_tb_getv(billingCodeTable, x, "2", billingCode)
                    @RtvFldDesc("BCBCDE='$(billingCode)'", "OEBCDE", "BCDESC", billingCodeDesc)
                    %INCLUDE "SetRowClass.icl"
                    <tr class="$(rowClass)">
                        <td class="colalph">$(billingCode)</td>
                        <td class="colalph">$(billingCodeDesc)</td>
                    </tr>
                    @dtw_add(x, "1", x)
                 %}
             </table>
         </td>
         <td>
             <table $(contentTable)>
                 <tr>
                     <th class="colhdr">Order<br>Type</th>
                     <th class="colhdr">Description</th>
                 </tr>

                 @dtw_tb_rows(orderTypeTable, orderTypeCount)
                 @dtw_assign(x, "1")
                 %while(x <= orderTypeCount) {
                    @dtw_tb_getv(orderTypeTable, x, "2", orderType)
                    @RtvFldDesc("OTAPID='OE' and OTOTCD='$(orderType)'", "HDOTYP", "OTDESC", orderTypeDesc)
                    %INCLUDE "SetRowClass.icl"
                    <tr class="$(rowClass)">
                        <td class="colcode">$(orderType)</td>
                        <td class="colalph">$(orderTypeDesc)</td>
                    </tr>
                    @dtw_add(x, "1", x)
                 %}
             </table>
         </td>
     </tr>
  </table>
%}
