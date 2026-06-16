%{
**********************************************************************
*  Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.             *
*             	 	        	                                         *
*  Job: Order Entry Confirm Include                                  *
**********************************************************************
%}

  function confirmAccept()        {return confirm("Confirm Accept Of Order");}
  function confirmAssignOrder()   {return confirm("Order Number is required for Attachments" + "\n" + "\n" + "Click OK to assign Order Number and then reselect Attachments icon");}
  function confirmCancel()        {return confirm("Confirm Cancel Of Order");}
  function confirmDeleteCC()      {return confirm("Confirm Removal Of Credit Card Payment");}
  function confirmDelete(text)    {return confirm("$(delRecordConf)" + "\n" + "\n" + text);}
  function confirmNoUpdate()      {return confirm("Confirm No Update Of Order");}
  function confirmNoDropShip()    {return confirm("Confirm Removal Of Drop Ship From This Order")}
  function confirmShip()          {return confirm("Confirm Shipment Of Order");}
  function confirmShipInv()       {return confirm("Confirm Shipment Of Order and Print Invoice");}
  function confirmSetPrintAck()   {return confirm("Confirm Set Acknowledgement to print");}
  function confirmBreakPOLink(url) {
           var answer = confirm("Confirm Removal Of Link To P/O")
           if (answer) {window.location.href=url;}
           else {window.location.href=window.location.href}
  }