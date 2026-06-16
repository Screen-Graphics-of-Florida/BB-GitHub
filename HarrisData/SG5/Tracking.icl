%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: Tracking                                                    *
*********************************************************************
%}
%Define {
  trackingBy    = ""
  trackDftRef   = ""
  trackDftOrder = ""
  trackDftInv   = ""
  trackDftCust  = ""
  dftLeadZero   = ""
  dftSeparator  = ""
%}

%FUNCTION(dtw_directcall) Retrieve_Tracking_URL(IN    CHAR(2)    trackShipVia,
                                                INOUT CHAR(1)    trackingBy,
                                                INOUT CHAR(1)    trackDftRef,
                                                INOUT CHAR(1)    trackDftOrder,
                                                INOUT CHAR(1)    trackDftInv,
                                                INOUT CHAR(1)    trackDftCust,
                                                INOUT CHAR(1)    dftLeadZero,
                                                INOUT CHAR(1)    dftSeparator,
                                                INOUT CHAR(3000) trackingURL)
{%EXEC {HHDTRK_W.PGM %}
%}

%MACRO_FUNCTION Update_Tracking_URL (INOUT CHAR(50)   trackingNumber,
                                     IN    CHAR(7)    shipDate,
                                     IN    CHAR(5)    zipCode,
                                     IN    DEC(7,0)   customerNumber,
                                     IN    DEC(8,0)   orderNumber,
                                     IN    DEC(7,0)   invoiceNumber,
                                     IN    CHAR(22)   referenceNumber,
                                     INOUT CHAR(3000) trackingURL)
  {

      %if (trackingBy == "R")
          %if (dftLeadZero == "Y")
              %while(@dtw_rlength(customerNumber) != "7") {@dtw_insert("0", customerNumber, customerNumber)%}
              %while(@dtw_rlength(orderNumber) != "8")    {@dtw_insert("0", orderNumber, orderNumber)%}
              %while(@dtw_rlength(invoiceNumber) != "7")  {@dtw_insert("0", invoiceNumber, invoiceNumber)%}
          %endif

          @dtw_assign(trackingNumber, "")
          @dtw_assign(trackX, "1")
          %while(trackX <= "4"){
              %if (trackDftCust == trackX)
                  %if (trackingNumber != "" && dftSeparator != "")
                      @dtw_concat(trackingNumber, dftSeparator, trackingNumber)
                  %endif
                  @dtw_concat(trackingNumber, customerNumber, trackingNumber)
              %elif (trackDftOrder == trackX)
                  %if (trackingNumber != "" && dftSeparator != "")
                      @dtw_concat(trackingNumber, dftSeparator, trackingNumber)
                  %endif
                  @dtw_concat(trackingNumber, orderNumber, trackingNumber)
              %elif (trackDftRef == trackX)
                  %if (trackingNumber != "" && dftSeparator != "")
                      @dtw_concat(trackingNumber, dftSeparator, trackingNumber)
                  %endif
                  @dtw_concat(trackingNumber, referenceNumber, trackingNumber)
              %elif (trackDftInv == trackX)
                  %if (trackingNumber != "" && dftSeparator != "")
                      @dtw_concat(trackingNumber, dftSeparator, trackingNumber)
                  %endif
                  @dtw_concat(trackingNumber, invoiceNumber, trackingNumber)
              %endif
          @dtw_add(trackX, "1", trackX)%}
      %endif


      %if (shipDate > "0")
          @Date_CYMD_ISO(shipDate, fromISO)
          @dtw_assign(toISO, fromISO)
          @Calc_ISO_Date(profileHandle, dataBaseID, fromISO, "S", "D", "7")
          @dtw_assign(fromYear, @dtw_rsubstr(fromISO, "1", "4"))
          @dtw_assign(fromMonth, @dtw_rsubstr(fromISO, "6", "2"))
          @dtw_assign(fromDay, @dtw_rsubstr(fromISO, "9", "2"))
          @Calc_ISO_Date(profileHandle, dataBaseID, toISO, "A", "D", "7")
          @dtw_assign(toYear, @dtw_rsubstr(toISO, "1", "4"))
          @dtw_assign(toMonth, @dtw_rsubstr(toISO, "6", "2"))
          @dtw_assign(toDay, @dtw_rsubstr(toISO, "9", "2"))

          @dtw_replace(trackingURL, "@@fromShipYear", fromYear, "1", "A", trackingURL)
          @dtw_replace(trackingURL, "@@fromShipMonth", fromMonth, "1", "A", trackingURL)
          @dtw_replace(trackingURL, "@@fromShipDay", fromDay, "1", "A", trackingURL)

          @dtw_replace(trackingURL, "@@toShipYear", toYear, "1", "A", trackingURL)
          @dtw_replace(trackingURL, "@@toShipMonth", toMonth, "1", "A", trackingURL)
          @dtw_replace(trackingURL, "@@toShipDay", toDay, "1", "A", trackingURL)
      %endif

      @dtw_replace(trackingURL, "@@trackingNumber", trackingNumber, "1", "A", trackingURL)
      @dtw_replace(trackingURL, "@@toZipCode", zipCode, "1", "A", trackingURL)
  %}