%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                     *
*  Job: Order Entry Header Include                                   *
**********************************************************************
%}
  @dtw_assign(attachFolder, "SalesOrder")
  @dtw_assign(totalType, "")
  %if (tabID == "REVIEW" || tabID == "PAYMENT" || tabID == "CREDITCARD")
      @dtw_assign(totalType, "T")
  %elif (useQtyShipped=="Y")
      @dtw_assign(totalType, "S")
  %endif

  %if (inclRetInfo != "N")
      %INCLUDE "OrderEntryRetInfo.icl"
  %endif

  %if (H1MNCD == "S")
      @dtw_assign(page_name, "Order Shipping")
  %else
      @dtw_assign(page_name, "Order Entry")
  %endif

  @Format_Nbr(orderTotal, F_orderTotal, "2", $(amtEditCode), "", "$", "")
  <table $(contentTable)>
      <tr><td>
              <table $(contentTable)>
                  <tr><td><h1>$(page_name)</h1></td></tr>
              </table>
          </td>
          <td>
              <table $(contentTable)>
                  %if ((H1MNCD != "A" && H1MNCD != "Z") || accessOrderType != "Y")
                      @Format_Header_URL("Order Type", $(orderTypeDesc), $(H1ORTY), "")
                  %else
                      @Format_Header_URL("Order Type", $(orderTypeDesc), $(H1ORTY), "$(homeURL)$(cGIPath)OrderEntryOrderType.d2w/ENTRY$(d2wVarBase)"" onclick=""$(searchWinVar)")
                  %endif
                  @Format_Header("Customer", $(customerName), $(customerNumber))
                  %if (H1CONT > "0")
                      @Format_Header("Contact", "$(contactName)", "$(H1CONT)")
                  %endif
              </table>
          </td>
          <td>&nbsp; &nbsp; &nbsp; &nbsp;</td>
          <td>
              <table $(contentTable)>
                  %if (H1ORD# > "0")
                      @dtw_assign(dspOrderNumber, "$(H1ORD#)")
                  %else
                      @dtw_assign(dspOrderNumber, "New Order")
                  %endif
                  @Format_Header("Order Number", $(dspOrderNumber), "")
                  %if (H1MNCD == "S")
                      @Format_Header("Turnaround", $(H1TURN), "")
                  %endif
                  @Format_Header("Item Count", $(numberOfItems), "")
                  %if (dspWeight == "Y")
                      @Format_Header("Weight", $(weightTotal), "")
                  %endif
                  %if (totalType == "T")
                      @Format_Header("Order Total", $(F_orderTotal), "")
                  %else
                      @Format_Header("Subtotal", $(F_orderTotal), "")
                  %endif
              </table>
          </td>
          <td>&nbsp; &nbsp; &nbsp; &nbsp;</td>
          <td>
              <table $(contentTable)>
                  <tr>
                      <th class="colicon">
                          %if (lineError != "Y" && hdrError != "Y" && userDefError != "Y" && userDtlError != "E" && errLinkedPOTab != "Y" && errPaymentTab != "Y" && numberOfItems > "0" && exitErrorMsg == "")
                              %if (H1MNCD == "S")
                                  %if (invoiceAtShipping == "Y")
                                      @RtvFldDesc("IHTURN=$(H1TURN)", "OEORHP", "IHSTAT", turnStatus)
                                      %if (turnStatus != "B")
                                          @RtvFldDesc("O1OCTL=$(orderControlNumber) and O1QSTC<>0", "OEDTWK", " char(count(*))", shipCnt)
                                          %if (shipCnt > "0"  && oe_sec_13 == "Y" && (flag_04 == "1" || flag_04 == "3"))
                                              <a onClick="return confirmShipInv()" href="$(homeURL)$(cGIPath)OrderShippingReview.d2w/ACCEPT$(d2wVarBase)&amp;orderNumber=@dtw_rurlescseq(H1ORD#)&amp;shipInv=Y">$(shipWithInvoice)</a>
                                          %endif
                                      %endif
                                  %endif
                                  <a onClick="return confirmShip()" href="$(homeURL)$(cGIPath)OrderShippingReview.d2w/ACCEPT$(d2wVarBase)&amp;orderNumber=@dtw_rurlescseq(H1ORD#)">$(orderAcceptImage)</a>
                              %else
                                  <a onClick="return confirmAccept()" href="$(homeURL)$(cGIPath)OrderEntryOrderReview.d2w/ACCEPT$(d2wVarBase)&amp;orderNumber=@dtw_rurlescseq(H1ORD#)">$(orderAcceptImage)</a>
                              %endif
                          %endif
                          %if (lineError != "Y" && hdrError != "Y" && userDefError != "Y" && errLinkedPOTab != "Y" && creditCardReq != "Y" && numberOfItems > "0")
                              <a href="$(homeURL)$(cGIPath)OrderEntryOrderPrintDocumentSelect.d2w/REPORT$(d2wVarBase)" onclick="$(searchWinVar)">$(formatPrintMed)</a>
                          %endif
                          %if (H1ORD# > "0")
                              %if (H1MNCD == "S")
                                  <a onClick="return confirmNoUpdate()" href="$(homeURL)$(cGIPath)OrderShippingReview.d2w/NOUPDATE$(d2wVarBase)&amp;orderNumber=@dtw_rurlescseq(H1ORD#)">$(orderNoUpdateImage)</a>
                              %else
                                  <a onClick="return confirmNoUpdate()" href="$(homeURL)$(cGIPath)OrderEntryOrderReview.d2w/NOUPDATE$(d2wVarBase)&amp;orderNumber=@dtw_rurlescseq(H1ORD#)">$(orderNoUpdateImage)</a>
                              %endif
                              @dtw_assign(attachVarKey, H1ORD#)
                              @dtw_concat(customerName, @dtw_rconcat(" Order ", $(H1ORD#)), attachForDesc)
                              @dtw_assign(attachPrg1, "OEORHD Where OEORD#=$(H1ORD#)")
                              @dtw_assign(attachPrg2, "OEORHH Where HHORD#=$(H1ORD#)")
                              %while(@dtw_rlength(attachVarKey) != "8") {@dtw_insert("0", attachVarKey, attachVarKey)%}
                              @DTW_REPLACE(attachVarKey, "/", "+", attachVarKey)
                              @DTW_REPLACE(attachVarKey, " ", "+", attachVarKey)
                              <a href="$(homeURL)$(phpPath)Attachment.PHP$(altVarBase)&amp;attachFolder=@dtw_rurlescseq(attachFolder)&amp;attachForDesc=@dtw_rurlescseq(attachForDesc)&amp;attachVarKey=@dtw_rurlescseq(attachVarKey)&amp;userProfile=@dtw_rurlescseq(userProfile)&amp;attachPrg1=@dtw_rurlescseq(attachPrg1)&amp;attachPrg2=@dtw_rurlescseq(attachPrg2)&amp;attachPrg3=@dtw_rurlescseq(attachPrg3)&amp;attachPrg4=@dtw_rurlescseq(attachPrg4)&amp;attachPrg5=@dtw_rurlescseq(attachPrg5)" onclick="$(selectionWinVar)">$(attachImageSml)</a>
                          %else
                              <a onClick="return confirmCancel()" href="$(homeURL)$(cGIPath)OrderEntryOrderReview.d2w/CANCEL$(d2wVarBase)">$(orderCancelImage)</a>
                              <a  onClick="return confirmAssignOrder()" href="$(homeURL)$(cGIPath)OrderEntryOrderReview.d2w/ASSIGNORDER$(d2wVarBase)">$(attachImageSml)</a>
                          %endif
                          @dtw_assign(medIcon, "Y")
                          %INCLUDE "HelpPage.icl"
                          %if (useProdGroup == "Y" && H1MNCD == "C")
                              <a href="$(homeURL)$(cGIPath)OrderEntryOrderReview.d2w/RECALC$(d2wVarBase)">$(orderUpdPriceImage)</a>
                          %endif
                          %if (fixedPrcOptCnt > "0")
                              <a href="$(homeURL)$(cGIPath)OrderEntryByItem.d2w/REGENERATE$(d2wVarBase)">$(regenOptionsImage)</a>
                          %endif
                          %if (H1MNCD == "C" && H1ORTY != "Q" && flag_03 == "2")
                               <a onClick="return confirmSetPrintAck()" href="$(homeURL)$(cGIPath)OrderEntryOrderReview.d2w/SETPRINTACK$(d2wVarBase)"><img border="$(imageBorder)" src="$(homeURL)$(imagePath)lgSqUpdate.gif" title="Click here to set acknowledgement to print" alt="Print Ack"></a>
                          %endif
                        
                      </th>
                  </tr>
              </table>
          </td>
      </tr>
     %if (exitInfoMsg != "")
         <tr><td class="confMsg" colspan="10">$(exitInfoMsg)</td></tr>
     %endif
     %if (exitErrorMsg != "")
         <tr><td class="error" colspan="10">$(exitErrorMsg)</td></tr>
     %endif
  </table>
	<div id="header">
     %if (checkCCL != "")
         @Rtv_Error_Desc(checkCCL, warningMessage)
         <div class="error" colspan="10">$(warningMessage)</div>
     %endif
     <ul id="primary">
         %if (tabID == "ITEM")
             <li><span>Item</span></li>
         %else
             %if (H1MNCD == "S")
                 <li><a href="$(homeURL)$(cGIPath)OrderShippingEntry.d2w/INPUT$(d2wVarBase)" title="Display Items">Item</a></li>
             %else
                 <li><a href="$(homeURL)$(cGIPath)OrderEntryByItem.d2w/ENTRY$(d2wVarBase)" title="Display Items">Item</a></li>
             %endif
         %endif

         %if (tabID == "ITEMCOMMENT")
             <li><span>Item Comment</span></li>
         %endif

         %if (tabID == "ITEMDETAIL")
             <li><span>Item Detail</span></li>
         %elif (tabID == "ITEMDETAIL" && H1MNCD != "S")
             <li><a href="$(homeURL)$(cGIPath)OrderEntryDetail.d2w/DETAIL$(d2wVarBase)&amp;lineNumber=000&amp;releaseNumber=000" title="Add Detail Line">Item Detail</a></li>
         %elif (H1MNCD != "S")
             <li><a href="$(homeURL)$(cGIPath)OrderEntryDetail.d2w/DETAIL$(d2wVarBase)&amp;lineNumber=000&amp;releaseNumber=000" title="Add Detail Line">Item Detail</a></li>
         %endif

         %if (vendCustItemsFound == "Y" && H1MNCD != "S")
             %if (tabID == "CUSTOMERITEM")
                 <li><span>Customer Item</span></li>
             %else
                 <li><a href="$(homeURL)$(cGIPath)OrderEntryByCustomerItem.d2w/ENTRY$(d2wVarBase)" title="Display Customer Items">Customer Item</a></li>
             %endif
         %endif

         %if (tabID == "HEADER")
             <li><span>Header</span></li>
         %else
             %if (hdrError == "Y") <ul id="reqtab"> %endif
                <li><a href="$(homeURL)$(cGIPath)OrderEntryHeader.d2w/HEADER$(d2wVarBase)" title="Click here to maintain header information">Header</a></li>
		     %if (hdrError == "Y") </ul> %endif
         %endif

         %if (oe_sec_06 == "Y")
             %if (tabID == "FLAGS")
                 <li><span>Flags</span></li>
             %else
                 <li><a href="$(homeURL)$(phpPath)OrderEntryFlags.php$(altVarBase)&amp;orderControlNumber=@dtw_rurlescseq(orderControlNumber)" onclick="$(selectionWinVar)" title="Click here to maintain order flags">Flags</a></li>
             %endif
         %endif

         %if (tabID == "COMMENT")
             <li><span>Comment</span></li>
         %else
             <li><a href="$(homeURL)$(cGIPath)OrderEntryComments.d2w/COMMENTS$(genericVarBase)&amp;customerNumber=@dtw_rurlescseq(customerNumber)&amp;customerName=@dtw_rurlescseq(customerName)&amp;orderControlNumber=@dtw_rurlescseq(orderControlNumber)" title="Click here to maintain header/trailer comments">Comment</a></li>
         %endif

         %if (tabID == "ADDRESS")
             <li><span>Address</span></li>
			 <ul id="secondary">
				  <li><a href="$(homeURL)$(cGIPath)OrderEntryDropShip.d2w/REPORT$(genericVarBase)&amp;vendCustNumber=@dtw_rurlescseq(customerNumber)&amp;vendCustName=@dtw_rurlescseq(customerName)&amp;vendCustFlag=C&amp;orderControlNumber=@dtw_rurlescseq(orderControlNumber)" onclick="$(orderEntryWinVar)" title="Select drop ship for this order">Drop Ship</a></li>
			 </ul>
         %else
             <li><a href="$(homeURL)$(cGIPath)OrderEntryAddress.d2w/DISPLAY$(d2wVarBase)" title="Click here to view addresses">Address</a></li>
         %endif

         %if (tabID == "CHARGES")
             <li><span>Charges</span></li>
         %else
             <li><a href="$(homeURL)$(cGIPath)OrderEntryMiscChg.d2w/REPORT$(d2wVarBase)&amp;subTotal=Y" title="Click here to maintain freight/special charges">Charges</a></li>
         %endif

         %if (oe_sec_12 == "Y")
             %if (tabID == "EMAILFAX")
                 <li><span>E-mail/Fax</span></li>
             %else
                 <li><a href="$(homeURL)$(cGIPath)EmailFaxRecipient.d2w/REPORT$(d2wVarBase)" title="Click here to maintain e-mail/fax recipients">E-mail/Fax</a></li>
             %endif
         %endif

         %if (tabID == "REVIEW")
             <li><span>Review</span></li>
         %else
             %if (H1MNCD != "S")
                 <li><a href="$(homeURL)$(cGIPath)OrderEntryOrderReview.d2w/DISPLAY$(d2wVarBase)" title="Click here to continue the order process">Review</a></li>
             %else
                 <li><a href="$(homeURL)$(cGIPath)OrderShippingReview.d2w/DISPLAY$(d2wVarBase)" title="Click here to continue the shipping process">Review</a></li>
             %endif
         %endif

         %if (tabID == "PAYMENT")
             <li><span>Payment</span></li>
         %else
             %if (allowPaymentTab == "Y")
                 %if (errPaymentTab == "Y") <ul id="reqtab"> %endif
                     <li><a href="$(homeURL)$(cGIPath)OrderEntryPayment.d2w/MAINTAIN$(d2wVarBase)&amp;timeStamp=@dtw_rurlescseq(@dtw_rconcat(@dtw_rdate(),@dtw_rtime("X")))" title="Click here to maintain payments information">Payment</a></li>
    		%if (errPaymentTab == "Y") </ul> %endif
             %endif
         %endif

         %if (userDefinedTab == "Y")
	         %if (tabID == "USERDEFINED")
	             <li><span>User-Defined</span></li>
	         %else
	             %if (userDefError == "Y") <ul id="reqtab"> %endif
	                <li><a href="$(homeURL)$(cGIPath)OrderEntryUserDefined.d2w/REPORT$(d2wVarBase)" title="Click here to maintain user-defined information">User-Defined</a></li>
			     %if (userDefError == "Y") </ul> %endif
	         %endif
         %endif

         %if (tabID == "CREDITCARD")
             <li><span>Credit Card</span></li>
         %endif

         %if (orderReviewReq == "Y")
             %if (tabID == "PRIORITY")
                 <li><span>Priority</span></li>
             %else
                 <li><a href="$(homeURL)$(cGIPath)OrderEntryPriority.d2w/REPORT$(d2wVarBase)" title="Clear here to set order review priority">Priority</a></li>
             %endif
         %endif

         %if (dspLinkedPOTab == "Y" || tabID == "LINKEDPO")
             %if (tabID == "LINKEDPO")
                 <li><span>Linked PO</span></li>
             %else
                 %if (errLinkedPOTab == "Y") <ul id="reqtab"> %endif
                     <li><a href="$(homeURL)$(cGIPath)OrderEntryLinkedPO.d2w/REPORT$(d2wVarBase)&amp;subTotal=Y" title="Click here to maintain linked purchase orders">Linked PO</a></li>
    		%if (errLinkedPOTab == "Y") </ul> %endif
             %endif
         %endif

         @RtvFldDesc("MOOCTL=$(orderControlNumber)", "WKVMOC", " char(count(*))", mfgReviewCnt)
         %if (H1MNCD != "S" && oe_sec_14 == "Y" && V_HDPDRL > "0" && mfgReviewCnt > "0")
             %if (tabID == "MFGREVIEW")
                 <li><span>Mfg Review</span></li>
             %else
                 @RtvFldDesc("MOOCTL=$(orderControlNumber) and MOUPDAT<>' '", "WKVMOC", " char(count(*))", mfgReviewChg)
                 %if (mfgReviewChg > "0")
                     <li><a href="$(homeURL)$(cGIPath)OrderEntryMfgReview.d2w/DISPLAY$(d2wVarBase)" title="Click here to review mfg orders" style="background-color:yellow">Mfg Review</a></li>
                 %else
                     <li><a href="$(homeURL)$(cGIPath)OrderEntryMfgReview.d2w/DISPLAY$(d2wVarBase)" title="Click here to review mfg orders">Mfg Review</a></li>
                 %endif
             %endif
         %endif
     </ul>
	</div>
	<div id="main">
     <div id="contents">
