%{
*********************************************************************
* Copr 1979 2001 An Unpublished Work By Harris Business Group, Inc. *
* All rights reserved. This work contains trade secrets.            *
*                  					                                  *
*  Job: View Check Box (Included in WildCardPage.icl)               *
*********************************************************************
%}
  &nbsp;
  %if (viewCheckBox=="HR")
      View:
          <input type="checkbox" name="active" $(activeChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgActive=Y'">Active
          <input type="checkbox" name="terminated" $(terminatedChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgTerminated=Y'">Terminated

  %elif (viewCheckBox=="Applicants.d2w")
      View:
          <input type="checkbox" name="NotHired" $(NotHiredChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgNotHired=Y'">Not Hired
          <input type="checkbox" name="Hired" $(HiredChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgHired=Y'">Hired

  %elif (viewCheckBox=="JournalPosted")
      View:
          <input type="checkbox" name="posted" $(postedChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgPosted=Y'">Posted
          <input type="checkbox" name="unposted" $(unpostedChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgUnposted=Y'">Unposted

  %elif (viewCheckBox=="ItemActive")
      View:
          <input type="checkbox" name="active" $(activeChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgActive=Y'">Active
          <input type="checkbox" name="inactive" $(inactiveChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgInactive=Y'">Inactive

  %elif (viewCheckBox=="PickedShipped")
      View:
          <input type="checkbox" name="picked" $(pickedChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/SELECT$(d2wVarBase)&amp;chgPicked=Y'">Picked
          <input type="checkbox" name="shipped" $(shippedChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/SELECT$(d2wVarBase)&amp;chgShipped=Y'">Shipped

  %elif (viewCheckBox=="ProgSecurityUsageInquiry.d2w")
      View:
          <input type="checkbox" name="viewUserView" $(viewUserViewChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgUserView=Y'">User View
          <input type="checkbox" name="viewPrivileges" $(viewPrivilegesChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgPrivileges=Y'">Privileges
          <input type="checkbox" name="viewProgOpt" $(viewProgOptChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgProgOpt=Y'">Program Option

  %elif (viewCheckBox=="WFHistory.d2w")
      View:
          <input type="checkbox" name="pass" $(myItemChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgMyItem=Y'">My Items
          <input type="checkbox" name="pass" $(passChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgPass=Y'">Passed
          <input type="checkbox" name="fail" $(failChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgFail=Y'">Failed
          <input type="checkbox" name="actv" $(actvChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgActv=Y'">In Process

  %elif (viewCheckBox=="WFError.d2w")
      View:
          <input type="checkbox" name="pass" $(myItemChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgMyItem=Y'">My Items
          <input type="checkbox" name="pass" $(reviewedChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgReviewed=Y'">Include Reviewed

  %elif (viewCheckBox=="PIActive")
      View:
          <input type="checkbox" name="active" $(activeChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgActive=Y'">Activity Flag Only

  %elif (viewCheckBox=="PIFileMaint")
      View:
          <input type="checkbox" name="active" $(activeChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgActive=Y'">Activity Flag Only
          <input type="checkbox" name="all" $(allChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgAll=Y'">Display All Types

  %elif (viewCheckBox=="Events")
      View:
          <input type="checkbox" name="openEvents" $(openChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgOpen=Y'">Open
          <input type="checkbox" name="compEvents" $(compChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgComp=Y'">Completed
          <input type="checkbox" name="overDueEvents" $(overDueChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgOverDue=Y'">Overdue

  %elif (viewCheckBox=="ARInvoice")
      View:
          <input type="checkbox" name="openInv" $(openChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgOpen=Y'">Open
          <input type="checkbox" name="paidInv" $(paidChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgPaid=Y'">With Payments

  %elif (viewCheckBox=="FAAsset")
      View:
          <input type="checkbox" name="showSched" $(schedChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgSched=Y'">Schedule

  %elif (viewCheckBox=="Usage")
      View:
          <input type="checkbox" name="debits" $(debitsChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgDebits=Y'">Debits
          <input type="checkbox" name="credits" $(creditsChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgCredits=Y'">Credits
          <input type="checkbox" name="currency" $(currencyChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgCurrency=Y'">Currency
          <input type="checkbox" name="units" $(unitsChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgUnits=Y'">Units

  %elif (viewCheckBox=="PortalByRole")
      View:
          %if (roleSetup=="Y")
              <input type="checkbox" name="newRec" $(newRecChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/DISPLAY$(d2wVarBase)&amp;chgNewRec=Y'">New
              <input type="checkbox" name="oldRec" $(oldRecChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/DISPLAY$(d2wVarBase)&amp;chgOldRec=Y'">Current
          %endif
          <input type="checkbox" name="selRec" $(selRecChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/DISPLAY$(d2wVarBase)&amp;chgSelRec=Y'">Selected
          <input type="checkbox" name="notSel" $(notSelChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/DISPLAY$(d2wVarBase)&amp;chgNotSel=Y'">Not Selected

  %elif (viewCheckBox=="SalesOrderStatus")
      View:
          <input type="checkbox" name="inclComponent" $(viewComponent) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgComponent=Y'">Component Orders $(reqFieldChar)
          <input type="checkbox" name="inclCompleted" $(viewCompleted) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgCompleted=Y'">Completed Orders

  %elif (viewCheckBox=="ErrorSource")
      View:
          <input type="checkbox" name="inclStandard" $(viewStandard) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgStandard=Y'">Standard
          <input type="checkbox" name="inclCustomer" $(viewCustomer) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgCustomer=Y'">Customer

  %elif (viewCheckBox=="SoftwareUpdateByObject")
      View:
          <input type="checkbox" name="chgOnly" $(changedOnlyChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;changedOnly=Y'">Changed Only

  %elif (viewCheckBox=="SoftwareUpdate")
      View:
          <input type="checkbox" name="showInstalled" $(showInstalledOnlyChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;showInstalledOnly=Y'">Show Installed
          <input type="checkbox" name="showUnInstalled" $(showUnInstalledOnlyChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;showUnInstalledOnly=Y'">Show Available

  %elif (viewCheckBox=="SourceSelect")
      View:
          <input type="checkbox" name="chgOnly" $(changedOnlyChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;changedOnly=Y'">Assigned Only
          %if (V_PRENVR!="F")
             <a href="$(homeURL)$(cGIPath)$(d2wName)/UPDATE$(maintainVar)&amp;maintenanceCode=S">$(compDispImageSml) De-Select All</a>
             <a href="$(homeURL)$(cGIPath)$(d2wName)/UPDATE$(maintainVar)&amp;maintenanceCode=T">$(compDispImageSml) Select All</a>
          %endif

  %elif (viewCheckBox=="AuditEmpl")
      View:
          <input type="checkbox" name="common"  $(commonChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgCommon=Y'">Common
          <input type="checkbox" name="hr"      $(hrChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgHR=Y'">HR
          <input type="checkbox" name="payroll" $(payrollChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/REPORT$(d2wVarBase)&amp;chgPayroll=Y'">Payroll

  %elif (viewCheckBox=="StockLocations")
      View:
       %if  (touchScreen == "Y")
          <input  class="bigcheck" type="checkbox" name="inclInclZero" $(inclInclZeroChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/ENTRY$(d2wVarBase)&amp;chgInclZero=Y'">Include Zero Quantity Locations
   	   %else
   	      <input type="checkbox" name="inclInclZero" $(inclInclZeroChecked) onClick="window.location.href='$(homeURL)$(cGIPath)$(d2wName)/ENTRY$(d2wVarBase)&amp;chgInclZero=Y'">Include Zero Quantity Locations
   	   %endif
  %endif
