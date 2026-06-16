<?php
print "&nbsp;";
if        ($viewCheckBox=="ARApplCashInvoice"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"OpenBatch\" $OpenChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgOpen=Y'\">Open";
	print "\n <input type=\"checkbox\" name=\"PaidBatch\" $PaidChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgPaid=Y'\">Fully Paid";
	print "\n";

} elseif  ($viewCheckBox=="ARInvoice"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"openInv\" $openChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgOpen=Y'\">Open";
	print "\n <input type=\"checkbox\" name=\"paidInv\" $paidChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgPaid=Y'\">With Payments";

} elseif  ($viewCheckBox=="AuditEmpl"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"common\" $commonChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgCommon=y'\">Common";
	print "\n <input type=\"checkbox\" name=\"hr\"     $hrChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgHR=y'\">HR";
	print "\n <input type=\"checkbox\" name=\"payroll\" $payrollChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgPayroll=y'\">Payroll";

} elseif  ($viewCheckBox=="Applicants.d2w"){
	print "\n View:";
	print "\n <input type=\"checkbox\" name=\"NotHired\" $NotHiredChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgNotHired=Y'\">Not Hired";
	print "\n <input type=\"checkbox\" name=\"Hired\" $HiredChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgHired=Y'\">Hired";

} elseif  ($viewCheckBox=="ErrorSource"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"inclStandard\" $viewStandard onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgStandard=y'\">Standard";
	print "\n <input type=\"checkbox\" name=\"inclCustomer\" $viewCustomer onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgCustomer=y'\">Customer";

} elseif  ($viewCheckBox=="Events"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"openEvents\" $openChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgOpen=y'\">Open";
	print "\n <input type=\"checkbox\" name=\"compEvents\" $compChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgComp=y'\">Completed";
	print "\n <input type=\"checkbox\" name=\"overDueEvents\" $overDueChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgOverDue=y'\">Overdue";

} elseif  ($viewCheckBox=="FAAsset"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"showSched\" $schedChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgSched=y'\">Schedule";

} elseif ($viewCheckBox=="HR"){
	print "\n View:";
	print "\n <input type=\"checkbox\" name=\"active\" $activeChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgActive=Y'\">Active";
	print "\n <input type=\"checkbox\" name=\"terminated\" $terminatedChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgTerminated=Y'\">Terminated";

} elseif  ($viewCheckBox=="ItemActive"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"active\" $activeChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgActive=y'\">Active";
	print "\n <input type=\"checkbox\" name=\"inactive\" $inactiveChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgInactive=y'\">Inactive";

} elseif  ($viewCheckBox=="JournalPosted"){
	print "\n View:";
	print "\n <input type=\"checkbox\" name=\"posted\" $postedChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgPosted=y'\">Posted";
	print "\n <input type=\"checkbox\" name=\"unposted\" $unpostedChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgUnposted=y'\">Unposted";

} elseif  ($viewCheckBox=="PIActive"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"active\" $activeChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgActive=y'\">Activity Flag Only";

} elseif  ($viewCheckBox=="PickedShipped"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"picked\" $pickedChecked onClick=\"window.location.href='{$baseURL}&amp;tag=SELECT&amp;chgPicked=y'\">Picked";
	print "\n <input type=\"checkbox\" name=\"shipped\" $shippedChecked onClick=\"window.location.href='{$baseURL}&amp;tag=SELECT&amp;chgShipped=y'\">Shipped";

} elseif  ($viewCheckBox=="PortalByRole"){
	print "\n  View:";
	if ($roleSetup=="Y"){
		print "\n <input type=\"checkbox\" name=\"newRec\" $newRecChecked onClick=\"window.location.href='{$baseURL}&amp;tag=MAINTAIN&amp;chgNewRec=y'\">New";
		print "\n <input type=\"checkbox\" name=\"oldRec\" $oldRecChecked onClick=\"window.location.href='{$baseURL}&amp;tag=MAINTAIN&amp;chgOldRec=y'\">Current";
	}
	print "\n <input type=\"checkbox\" name=\"selRec\" $selRecChecked onClick=\"window.location.href='{$baseURL}&amp;tag=MAINTAIN&amp;chgSelRec=y'\">Selected";
	print "\n <input type=\"checkbox\" name=\"notSel\" $notSelChecked onClick=\"window.location.href='{$baseURL}&amp;tag=MAINTAIN&amp;chgNotSel=y'\">Not Selected";

} elseif  ($viewCheckBox=="ProgSecurityUsageInquiry.d2w"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"viewUserView\" $viewUserViewChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgUserView=y'\">User View";
	print "\n <input type=\"checkbox\" name=\"viewPrivileges\" $viewPrivilegesChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgPrivileges=y'\">Privileges";
	print "\n <input type=\"checkbox\" name=\"viewProgOpt\" $viewProgOptChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgProgOpt=y'\">Program Option";

} elseif  ($viewCheckBox=="SalesOrderStatus"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"inclComponent\" $viewComponent onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgComponent=y'\">Component Orders {$reqFieldChar}";
	print "\n <input type=\"checkbox\" name=\"inclCompleted\" $viewCompleted onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgCompleted=y'\">Completed Orders";

} elseif  ($viewCheckBox=="SoftwareUpdateByObject"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"chgOnly\" $changedOnlyChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;changedOnly=y'\">Changed Only";

} elseif  ($viewCheckBox=="SourceSelect"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"chgOnly\" $changedOnlyChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;changedOnly=y'\">Assigned Only";
	if ($PRENVR!="F") {
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}$maintainVar&amp;tag=UPDATE&amp;maintenanceCode=S\">{$compDispImageSml}De-Select All</a>";
		print "\n <a href=\"{$homeURL}{$phpPath}{$scriptName}$maintainVar&amp;tag=UPDATE&amp;maintenanceCode=T\">{$compDispImageSml}Select All</a>";
	}

} elseif  ($viewCheckBox=="Usage"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"debits\" $debitsChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgDebits=y'\">Debits";
	print "\n <input type=\"checkbox\" name=\"credits\" $creditsChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgCredits=y'\">Credits";
	print "\n <input type=\"checkbox\" name=\"currency\" $currencyChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgCurrency=y'\">Currency";
	print "\n <input type=\"checkbox\" name=\"units\" $unitsChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgUnits=y'\">Units";

} elseif  ($viewCheckBox=="WFError.d2w"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"pass\" $myItemChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgMyItem=y'\">My Items";
	print "\n <input type=\"checkbox\" name=\"pass\" $reviewedChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgReviewed=y'\">Include Reviewed";

} elseif  ($viewCheckBox=="WFHistory.d2w"){
	print "\n  View:";
	print "\n <input type=\"checkbox\" name=\"pass\" $myItemChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgMyItem=y'\">My Items";
	print "\n <input type=\"checkbox\" name=\"pass\" $passChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgPass=y'\">Passed";
	print "\n <input type=\"checkbox\" name=\"fail\" $failChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgFail=y'\">Failed";
	print "\n <input type=\"checkbox\" name=\"actv\" $actvChecked onClick=\"window.location.href='{$baseURL}&amp;tag=REPORT&amp;chgActv=y'\">In Process";

}
?>
