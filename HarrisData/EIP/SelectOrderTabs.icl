%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                             *
*  Job: Order Entry Header Include                                   *
**********************************************************************
%}
  @dtw_assign(orderControlNumber, "0")
  @dtw_assign(prevOrder, "0")
  @dtw_assign(nextOrder, "0")
  @dtw_assign(prevNextSeq, "000")
  @dtw_assign(totalType, "T")
  @dtw_assign(attachFolder, "SalesOrder")

  %INCLUDE "OrderEntryRetInfo.icl"
  @dtw_assign(maintainVar, "$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(H1SHTO)&amp;customerName=@dtw_rurlescseq(shipToName)&amp;orderNumber=@dtw_rurlescseq(orderNumber)&amp;batchNumber=@dtw_rurlescseq(batchNumber)&amp;turnaround=@dtw_rurlescseq(turnaround)")

  @dtw_assign(savVar, edtVar)
  %if (V_HDAPRV != "N")
      @dtw_assign(edtVar, "")
      @Concat_Field("@@user", userProfile)
      @Concat_Field("@@ortx", H1ORTY)
      @Concat_Field("@@orty", "Y")
      @dtw_concat(edtVar, "}{", edtVar)
      @Check_Privileges(edtVar)
      @Decat_Field("@@pv01") @dtw_assign(pvAdd, fieldValue)
      @Decat_Field("@@pv02") @dtw_assign(pvChg, fieldValue)
      @Decat_Field("@@pv03") @dtw_assign(pvDel, fieldValue)
      @Decat_Field("@@pv05") @dtw_assign(pvCpy, fieldValue)
      @Decat_Field("@@nosu") @dtw_assign(userSetup, fieldValue)
  %endif
  %if (userSetup != "Y" || V_HDAPRV == "N")
      @dtw_assign(pvAdd, "Y")
      @dtw_assign(pvChg, "Y")
      @dtw_assign(pvDel, "Y")
      @dtw_assign(pvCpy, "Y")
  %endif   
  @dtw_assign(edtVar, savVar)


  %if (tabHistory == "Y")
      @dtw_assign(hdrTitle, "Order History")
      @dtw_assign(openHist, "History")
      @dtw_assign(reviewTitle, "Shipment")
      @RtvFldDesc("HHORD#=$(orderNumber) and HHSEQ#=000", "OEORHH", "char(count(*))", histZero)
      %if (histZero > "0")
          @dtw_assign(useHistory, "Y")
      %else
          @dtw_assign(useHistory, "")
      %endif
  %else
      @dtw_assign(hdrTitle, "Open Order")
      @dtw_assign(openHist, "")
      @dtw_assign(reviewTitle, "Open Order")
  %endif

  @dtw_assign(IHIBPL, "")
  @dtw_assign(IHSTAT, "")
  @dtw_assign(linkedDropShip, "0")
  %if (orderStatus == "O")
      @RtvFldDesc("ODORD#=$(orderNumber) and ODGNPO='Y' and ODPO#>0 and ODDSHP='Y' and ODORST='O'", "OEORDT", " char(count(*))", linkedDropShip)
      @RtvFldDesc("IHORD#=$(orderNumber) and IHIBPL<>'N'", "OEORHP", "IHIBPL", IHIBPL)
      @RtvFldDesc("IHORD#=$(orderNumber)", "OEORHP", "IHSTAT", IHSTAT)
  %endif
  <table $(contentTable)>
	       <colgroup>
	           <col width="85%">
	           <col width="10%">
      <tr><td><h1>$(hdrTitle)</h1></td>
          %if (formatToPrint != "Y")
              %INCLUDE "OrderEntryProgOpt.icl"
              @dtw_assign(progName2, "HOEOEM_2")
              @pgmOptSecurity(profileHandle, dataBaseID, progName2, o2_sec_01, o2_sec_02, o2_sec_03, o2_sec_04, o2_sec_05, o2_sec_06, o2_sec_07, o2_sec_08, o2_sec_09, o2_sec_10, o2_sec_11, o2_sec_12, o2_sec_13, o2_sec_14, o2_sec_15)
              %if ((IHSTAT == "B" && o2_sec_02 == "N") || (IHSTAT == "S" && o2_sec_01 == "N"))
                   @dtw_assign(oe_sec_02, "N")
              %endif
              <td class="toolbar">
                  @dtw_assign(attachVarKey, orderNumber)
                  @dtw_concat(customerName, @dtw_rconcat(" Order ", $(orderNumber)), attachForDesc)
                  @dtw_assign(attachPrg1, "OEORHD Where OEORD#=$(orderNumber)")
                  @dtw_assign(attachPrg2, "OEORHH Where HHORD#=$(orderNumber)")
                  %while(@dtw_rlength(attachVarKey) != "8") {@dtw_insert("0", attachVarKey, attachVarKey)%}
                  %INCLUDE "Attachment.icl"

                  %if (batchNumber > "0")
                      <a href="$(homeURL)$(cGIPath)BillingSelectUpdate.d2w/REPORT$(d2wVarBase)" title="Back Home">$(portalHome)</a>
                      %if (flag_07 == "0" || IHIBPL == "Y" || IHIBPL == "S" || linkedDropShip > "0")
                          <a onClick="return confirmOmit('$(turnaround)','$(orderNumber)')" href="$(homeURL)$(cGIPath)BillingSelectUpdate.d2w/Edit_Data$(maintainVar)&amp;status=Y" title="Omit This Billing Cycle">$(lgO)</a>
                      %else
                          <a onClick="return confirmHold('$(turnaround)','$(orderNumber)')" href="$(homeURL)$(cGIPath)BillingSelectUpdate.d2w/Edit_Data$(maintainVar)&amp;status=H" title="Hold This Shipment">$(lgH)</a>
                          %if (linkedDropShip == "0")
                              <a onClick="return confirmCancel('$(turnaround)','$(orderNumber)')" href="$(homeURL)$(cGIPath)BillingSelectUpdate.d2w/Edit_Data$(maintainVar)&amp;status=C" title="Cancel Shipment">$(lgC)</a>
                          %endif
                      %endif
                  %endif
                  %if (oe_sec_05 == "Y" && tabID != "COPY" && turnaround == "" && pvCpy == "Y")
                      <a href="$(homeURL)$(cGIPath)SelectOrderCopy.d2w/REPORT$(maintainVar)&amp;fromHistory=@dtw_rurlescseq(useHistory)&amp;tabHistory=@dtw_rurlescseq(tabHistory)&amp;orderSequence=@dtw_rurlescseq(orderSequence)">$(copyImageLrg)</a>
                  %endif
                  %if (oe_sec_02 == "Y" && orderStatus == "O" && H1ORTY != "I" && H1ORTY != "C" && tabHistory != "Y" && turnaround == "" && IHIBPL != "Y" && IHIBPL != "S" && pvChg == "Y")
                      %if (IHSTAT == "B")
                          <a onClick="return confirmBilling();" href="$(homeURL)$(cGIPath)OrderEntry.d2w/CHANGE$(maintainVar)&amp;maintCode=C">$(changeImageLrg)</a>
                      %else
                          <a onClick="saveCurrentURL();" href="$(homeURL)$(cGIPath)OrderEntry.d2w/CHANGE$(maintainVar)&amp;maintCode=C">$(changeImageLrg)</a>
                      %endif
                  %elif (oe_sec_02 == "Y" && orderStatus == "O" && H1ORTY != "I" && H1ORTY != "C" && H1ORTY != "R" && turnaround > "0" && linkedDropShip == "0" && IHIBPL != "Y" && IHIBPL != "S" && pvChg == "Y")
   	               <a onClick="saveCurrentURL();" href="$(homeURL)$(cGIPath)OrderEntry.d2w/CHANGE$(maintainVar)&amp;orderControlNumber=00000000&amp;maintCode=S&amp;timeStamp=@dtw_rurlescseq(@dtw_rconcat(@dtw_rdate(),@dtw_rtime("X")))">$(changeImageLrg)</a>
                  %endif
                  %if (oe_sec_04 == "Y" && orderStatus == "O" && H1ORTY != "I" && tabHistory != "Y" && turnaround == "" && linkedDropShip == "0" && IHIBPL != "Y" && IHIBPL != "S" && IHSTAT != "B" && pvDel == "Y")
                      <a onClick="return confirmCloseCancel(); saveCurrentURL();" href="$(homeURL)$(cGIPath)OrderEntry.d2w/CHANGE$(maintainVar)&amp;maintCode=X">$(orderCloseImage)</a>
                  %endif
                  %if (tabHistory != "Y")
                      <a href="$(homeURL)$(cGIPath)SelectOrderPrint.d2w/REPORT$(maintainVar)" target=_blank>$(formatPrintDesc)</a>
                  %endif
                  %INCLUDE "HelpPage.icl"
              </td>
          %endif
      </tr>
  </table>
  <table $(contentTable)>
      @Format_Header_URL("Customer", $(shipToName), $(H1SHTO), "$(homeURL)$(cGIPath)CustomerSelect.d2w/REPORT$(maintainVar)")
      %if (turnaround > "0")
          @dtw_assign(dspTitle, "Turnaround")
          @dtw_assign(dspNumber, turnaround)
          @dtw_assign(dspFirst, firstTurnaround)
          @dtw_assign(dspPrev,  prevTurnaround)
          @dtw_assign(dspNext,  nextTurnaround)
          @dtw_assign(dspLast,  lastTurnaround)
          @Format_Header("Order Number", $(H1ORD#), "")
      %else
          @dtw_assign(dspTitle, "Order Number")
          @dtw_assign(dspNumber, orderNumber)
          @dtw_assign(dspFirst, firstOrder)
          @dtw_assign(dspPrev,  prevOrder)
          @dtw_assign(dspNext,  nextOrder)
          @dtw_assign(dspLast,  lastOrder)
      %endif
      <tr><td class="hdrtitl">$(dspTitle):</td>
          <td><h2>&nbsp;
              %if (dspFirst > "0")
                  @dtw_assign(previousImageBegTitle, "View First $(dspTitle) $(dspFirst)")
                  <a href="$(homeURL)$(cGIPath)SelectOrder$(openHist).d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;salesmanNumber=@dtw_rurlescseq(salesmanNumber)&amp;salesmanName=@dtw_rurlescseq(salesmanName)&amp;orderNumber=@dtw_rurlescseq(firstOrder)&amp;orderSequence=@dtw_rurlescseq(prevNextSeq)&amp;batchNumber=@dtw_rurlescseq(batchNumber)&amp;turnaround=@dtw_rurlescseq(firstTurnaround)&amp;noMenu=@dtw_rurlescseq(noMenu)">$(previousImageBegSml)</a>
              %endif
              %if (dspPrev > "0")
                  @dtw_assign(previousImageTitle, "View Previous $(dspTitle) $(dspPrev)")
                  <a href="$(homeURL)$(cGIPath)SelectOrder$(openHist).d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;salesmanNumber=@dtw_rurlescseq(salesmanNumber)&amp;salesmanName=@dtw_rurlescseq(salesmanName)&amp;orderNumber=@dtw_rurlescseq(prevOrder)&amp;orderSequence=@dtw_rurlescseq(prevNextSeq)&amp;batchNumber=@dtw_rurlescseq(batchNumber)&amp;turnaround=@dtw_rurlescseq(prevTurnaround)&amp;noMenu=@dtw_rurlescseq(noMenu)">$(previousImageSml)</a>
              %endif
              $(dspNumber)
              %if (dspNext > "0")
                  @dtw_assign(nextImageTitle, "View Next $(dspTitle) $(dspNext)")
                  <a href="$(homeURL)$(cGIPath)SelectOrder$(openHist).d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;salesmanNumber=@dtw_rurlescseq(salesmanNumber)&amp;salesmanName=@dtw_rurlescseq(salesmanName)&amp;orderNumber=@dtw_rurlescseq(nextOrder)&amp;orderSequence=@dtw_rurlescseq(prevNextSeq)&amp;batchNumber=@dtw_rurlescseq(batchNumber)&amp;turnaround=@dtw_rurlescseq(nextTurnaround)&amp;noMenu=@dtw_rurlescseq(noMenu)">$(nextImageSml)</a>
              %endif
              %if (dspLast > "0")
                  @dtw_assign(nextImageEndTitle, "View Last $(dspTitle) $(dspLast)")
                  <a href="$(homeURL)$(cGIPath)SelectOrder$(openHist).d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;salesmanNumber=@dtw_rurlescseq(salesmanNumber)&amp;salesmanName=@dtw_rurlescseq(salesmanName)&amp;orderNumber=@dtw_rurlescseq(lastOrder)&amp;orderSequence=@dtw_rurlescseq(prevNextSeq)&amp;batchNumber=@dtw_rurlescseq(batchNumber)&amp;turnaround=@dtw_rurlescseq(lastTurnaround)&amp;noMenu=@dtw_rurlescseq(noMenu)">$(nextImageEndSml)</a>
              %endif
           </h2>
          </td>
          %if (contractOrder > "0" && H1ORTY != "X")
              @Format_Header_URL("Contract Order", "$(contractOrder)", "", "$(homeURL)$(cGIPath)SelectOrder.d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(H1SHTO)&amp;customerName=@dtw_rurlescseq(shipToName)&amp;orderNumber=@dtw_rurlescseq(contractOrder)")
          %endif
      </tr>
  </table>
  %INCLUDE "ConfMessageDisplay.icl"

	 <div id="header">
     <ul id="primary">

         %if (tabID == "HEADER")
             <li><span>Header</span></li>
         %else
             <li><a href="$(homeURL)$(cGIPath)SelectOrder$(openHist)Header.d2w/REPORT$(maintainVar)&amp;orderSequence=@dtw_rurlescseq(orderSequence)" title="Click here to view header information">Header</a></li>
         %endif

         %if (tabID == "ITEMDETAIL")
             <li><span>Item Detail</span></li>
         %endif

         %if (hdrTrlCmtCnt > "0")
             %if (tabID == "COMMENT")
                 <li><span>Comment</span></li>
             %else
                 <li><a href="$(homeURL)$(cGIPath)SelectOrder$(openHist)Comments.d2w/REPORT$(maintainVar)&amp;orderSequence=@dtw_rurlescseq(orderSequence)&amp;lineNumber=000" title="Click here to view header/trailer comments">Comment</a></li>
             %endif
         %endif

         %if (emailFaxCnt > "0" && tabHistory != "Y")
             %if (tabID == "EMAILFAX")
                 <li><span>E-mail/Fax</span></li>
             %else
                 <li><a href="$(homeURL)$(cGIPath)SelectOrderEmailFax.d2w/REPORT$(maintainVar)&amp;orderSequence=@dtw_rurlescseq(orderSequence)" title="Click here to view e-mail/fax recipients">E-mail/Fax</a></li>
             %endif
         %endif

         %if (tabID == "FLAGS")
             <li><span>Flags</span></li>
         %else
             <li><a href="$(homeURL)$(cGIPath)SelectOrder$(openHist)Flags.d2w/REPORT$(maintainVar)&amp;orderSequence=@dtw_rurlescseq(orderSequence)&amp;orderType=@dtw_rurlescseq(H1ORTY)&amp;orderApplication=OE" title="Click here to view order flags">Flags</a></li>
         %endif

         %if (tabID == "REVIEW" && (tabHistory != "Y" || orderSequence > "0"))
             <li><span>$(reviewTitle)</span></li>
         %else
             %if (tabHistory == "Y" && orderSequence == "0")
                 <li><a href="$(homeURL)$(cGIPath)SelectOrderHistory.d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;salesmanNumber=@dtw_rurlescseq(salesmanNumber)&amp;salesmanName=@dtw_rurlescseq(salesmanName)&amp;orderNumber=@dtw_rurlescseq(orderNumber)&amp;orderSequence=001&amp;noMenu=@dtw_rurlescseq(noMenu)" title="Click here to go to Shipments">$(reviewTitle)</a></li>
             %elif (tabHistory == "Y")
                 <li><a href="$(homeURL)$(cGIPath)SelectOrderHistory.d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;salesmanNumber=@dtw_rurlescseq(salesmanNumber)&amp;salesmanName=@dtw_rurlescseq(salesmanName)&amp;orderNumber=@dtw_rurlescseq(orderNumber)&amp;orderSequence=@dtw_rurlescseq(orderSequence)&amp;noMenu=@dtw_rurlescseq(noMenu)" title="Click here to go to Shipments">$(reviewTitle)</a></li>
             %else
                 <li><a href="$(homeURL)$(cGIPath)SelectOrder$(openHist).d2w/REPORT$(maintainVar)" title="Click here to go to Review">$(reviewTitle)</a></li>
             %endif
         %endif

         %if (tabHistory == "Y")
             %if (histZero > "0")
                 %if (tabID == "REVIEW" && orderSequence == "0")
                     <li><span>Summary</span></li>
                 %else
                     <li><a href="$(homeURL)$(cGIPath)SelectOrderHistory.d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;salesmanNumber=@dtw_rurlescseq(salesmanNumber)&amp;salesmanName=@dtw_rurlescseq(salesmanName)&amp;orderNumber=@dtw_rurlescseq(orderNumber)&amp;orderSequence=000&amp;noMenu=@dtw_rurlescseq(noMenu)" title="Click here to view summary information">Summary</a></li>
                 %endif
             %endif
         %endif

         %if (tabHistory !="Y"  && turnaround == "" && shipCount > "0")
             <li><a href="$(homeURL)$(cGIPath)SelectOrderHistory.d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;salesmanNumber=@dtw_rurlescseq(salesmanNumber)&amp;salesmanName=@dtw_rurlescseq(salesmanName)&amp;orderNumber=@dtw_rurlescseq(orderNumber)&amp;orderSequence=001&amp;noMenu=@dtw_rurlescseq(noMenu)" title="Click here to go to Shipments">Shipment</a></li>
         %elif (tabHistory =="Y" && openCount > "0")
             <li><a href="$(homeURL)$(cGIPath)SelectOrder.d2w/REPORT$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;salesmanNumber=@dtw_rurlescseq(salesmanNumber)&amp;salesmanName=@dtw_rurlescseq(salesmanName)&amp;orderNumber=@dtw_rurlescseq(orderNumber)&amp;noMenu=@dtw_rurlescseq(noMenu)" title="Click here to view open order">Open Order</a></li>
         %endif

         %if (tabID == "COPY")
             <li><span>Copy To</span></li>
         %endif
     </ul>
	</div>
	<div id="main">
     <div id="contents">
