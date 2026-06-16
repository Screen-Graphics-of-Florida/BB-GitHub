<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

require_once 'SetLibraryList.php';

require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'VarBase.php';

$scriptName    = "ProgSecurityUsageUpdate.php";
$comments      = "";
$quote         = "\"";
$PSPGTP        = "SCRIPT";

require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n </head>";

print "\n <table $contentTable><colgroup><col width=\"80%\"> <col width=\"15%\">";
print "\n <tr><td><h1>Mass Update Program Security Usage</h1></td></tr></table>";
print $hrTagAttr;

print "\n <table $contentTable>";
print "\n      <tr><th class=\"colhdr\">Member</th></tr>";

require 'stmtSQLClear.php';
$stmtSQL .= " Select * ";
$fileSQL .= " PIPRD2W ";
$selectSQL .= " (PWD2WMU LIKE '%.D2W%' or PWD2WMU LIKE '%.PHP%')  ";
$selectSQL .= " and PWD2WMU Not LIKE 'PROGSECURITYUSAGEUPDATE.PHP%' ";
$selectSQL .= " and PWD2WMU Not LIKE 'DOCREFERENCEUPDATE%' ";
$selectSQL .= " and PWRLS<>'O' ";
require 'stmtSQLSelect.php';
$stmtSQL .= " Order By PWD2WMU ";
require 'stmtSQLEnd.php';
require 'stmtSQLTotalRows.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

while ($row = db2_fetch_assoc($sqlResult, $startRow)){

	$PSPGNM= trim($row['PWD2WM']);
	$PSDESC= "";
	$PSPOSP= "";
	$PSUVFN= "";
	$PSICLN= "";
	$fh = fopen($PSPGNM, 'r');
	$text = fread($fh, filesize($PSPGNM));
	fclose($fh);
	if (trim($text) != "") {
		print "\n <tr class=\"$rowClass\">";
		print "\n     <td class=\"colalph\">$PSPGNM</td>";
		print "\n </tr>";

		// Get Description
		$progPos = strpos($text, "page_title");
		if ($progPos>0) {
			$x=1;
			while($x < 50) {
				$character=substr($text, $progPos, 1);
				if (substr($text, $progPos, 1) == $quote) {
					$progPos+=1;
					$quotePos= strpos($text, $quote, $progPos);
					$PSDESC  = substr($text, $progPos, $quotePos-$progPos);
					$x=99;
				} else {
					$x+=1;
					$progPos+=1;
				}
			}
		}

		// See if using Program Option Security
		$progPos=strpos($text,"programName");
		if ($progPos>0) {
			$x=1;
			while($x < 50) {
				if (substr($text, $progPos, 1) == $quote) {
					$progPos+=1;
					$quotePos= strpos($text, $quote, $progPos);
					$PSPOSP  = substr($text, $progPos, $quotePos-$progPos);
					$x=99;
				} else {
					$x+=1;
					$progPos+=1;
				}
			}
		}
		if (strpos($text, "uv_CompanyName")>0)           {$PSUVFN .= "XPCO# ";}
		if (strpos($text, "uv_FacilityName")>0)          {$PSUVFN .= "XPFAC# ";}
		if (strpos($text, "uv_PayerTinName")>0)          {$PSUVFN .= "XPTIN# ";}
		if (strpos($text, "uv_BankName")>0)              {$PSUVFN .= "XPBNK# ";}
		if (strpos($text, "uv_AccountName")>0)           {$PSUVFN .= "XPACCT ";}
		if (strpos($text, "uv_BuyerName")>0)             {$PSUVFN .= "XPBUYR ";}
		if (strpos($text, "uv_VendorTypeName")>0)        {$PSUVFN .= "XPVTYP ";}
		if (strpos($text, "uv_VendorName")>0)            {$PSUVFN .= "XPVEND ";}
		if (strpos($text, "uv_SalesmanName")>0)          {$PSUVFN .= "XPSLSM ";}
		if (strpos($text, "uv_CustomerClassName")>0)     {$PSUVFN .= "XPCCLS ";}
		if (strpos($text, "uv_RegionName")>0)            {$PSUVFN .= "XPCRGN ";}
		if (strpos($text, "uv_BillingLocationName")>0)   {$PSUVFN .= "XPBLLC ";}
		if (strpos($text, "uv_CustomerName")>0)          {$PSUVFN .= "XPCUST ";}
		if (strpos($text, "uv_PayerName")>0)             {$PSUVFN .= "XPPAYR ";}
		if (strpos($text, "uv_FederalEINName")>0)        {$PSUVFN .= "XPEIN ";}
		if (strpos($text, "uv_HRCompanyName")>0)         {$PSUVFN .= "XPPECO ";}
		if (strpos($text, "uv_HRLocationName")>0)        {$PSUVFN .= "XPPELC ";}
		if (strpos($text, "uv_PRBankName")>0)            {$PSUVFN .= "XPBANK ";}
		if (strpos($text, "uv_HomeDepartmentName")>0)    {$PSUVFN .= "XPDEPT ";}
		if (strpos($text, "uv_PayTypeName")>0)           {$PSUVFN .= "XPPAYT ";}
		if (strpos($text, "uv_SalaryControlName")>0)     {$PSUVFN .= "XPPAYD ";}
		if (strpos($text, "uv_PREmployeeName")>0)        {$PSUVFN .= "XPPREM ";}
		if (strpos($text, "uv_HREmployeeName")>0)        {$PSUVFN .= "XPPEEM ";}
		if (strpos($text, "uv_ScheduleName")>0)          {$PSUVFN .= "XPSCHD ";}
		if (strpos($text, "uv_GroupName")>0)             {$PSUVFN .= "XPGRP ";}
		if (strpos($text, "uv_DataCollectionName")>0)    {$PSUVFN .= "XPCODE ";}
		if (strpos($text, "uv_LaborCodeName")>0)         {$PSUVFN .= "XPLCOD ";}
		if (strpos($text, "uv_IndirectDowntimeName")>0)  {$PSUVFN .= "XPINCD ";}
		if (strpos($text, "uv_PlantName")>0)             {$PSUVFN .= "XPPLT ";}
		if (strpos($text, "uv_MfgDepartmentName")>0)     {$PSUVFN .= "XPMFDP ";}
		if (strpos($text, "uv_WorkCenterName")>0)        {$PSUVFN .= "XPWC ";}
		if (strpos($text, "uv_PltInventoryTypeName")>0)  {$PSUVFN .= "XPITC ";}
		if (strpos($text, "uv_PltPartTypeName")>0)       {$PSUVFN .= "XPPTYP ";}
		if (strpos($text, "uv_ProductGroupName")>0)      {$PSUVFN .= "XPPGRP ";}
		if (strpos($text, "uv_PartClassName")>0)         {$PSUVFN .= "XPCLAS ";}
		if (strpos($text, "uv_ProductClassName")>0)      {$PSUVFN .= "XPPCLS ";}
		if (strpos($text, "uv_WarehouseName")>0)         {$PSUVFN .= "XPWHS ";}
		if (strpos($text, "uv_ProdInventoryTypeName")>0) {$PSUVFN .= "XPPITC ";}
		if (strpos($text, "uv_ProdPartTypeName")>0)      {$PSUVFN .= "XPPPTY ";}
		if (strpos($text, "uv_CountGroupName")>0)        {$PSUVFN .= "XPCYCL ";}
		if (strpos($text, "uv_StockroomName")>0)         {$PSUVFN .= "XPSTKR ";}
		if (strpos($text, "uv_AileName")>0)              {$PSUVFN .= "XPAILE ";}
		if (strpos($text, "uv_StockLocationName")>0)     {$PSUVFN .= "XPSLOC ";}
		if (strpos($text, "uv_OEOrderTypeName")>0)       {$PSUVFN .= "XPOOCD ";}
		if (strpos($text, "uv_POOrderTypeName")>0)       {$PSUVFN .= "XPPOCD ";}
		if (strpos($text, "uv_TransactionTypeName")>0)   {$PSUVFN .= "XPTTYP ";}
		if (strpos($text, "uv_SiteName")>0)              {$PSUVFN .= "XPSITE ";}
		if (strpos($text, "uv_PropertyTypeName")>0)      {$PSUVFN .= "XPPROP ";}
		if (strpos($text, "uv_FamilyCodeName")>0)        {$PSUVFN .= "XPFMCD ";}

		if (strpos($text, "AssetUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN= "AssetUserView";}
			if (strpos($PSUVFN, "XPSITE") === false) {$PSUVFN .= "XPSITE ";}
			if (strpos($PSUVFN, "XPPROP") === false) {$PSUVFN .= "XPPROP ";}
			if (strpos($PSUVFN, "XPFMCD") === false) {$PSUVFN .= "XPFMCD ";}
		}

		if (strpos($text, "EmplUV.icl")>0 || strpos($text, "EmplUV.php")>0) {
			if (trim($PSICLN)=="") {$PSICLN= "EmplUV";}
			if (strpos($PSUVFN, "XPCO#") === false)  {$PSUVFN .= "XPCO# ";}
			if (strpos($PSUVFN, "XPFAC#") === false) {$PSUVFN .= "XPFAC# ";}
			if (strpos($PSUVFN, "XPPECO") === false) {$PSUVFN .= "XPPECO ";}
			if (strpos($PSUVFN, "XPPELC") === false) {$PSUVFN .= "XPPELC ";}
			if (strpos($PSUVFN, "XPDEPT") === false) {$PSUVFN .= "XPDEPT ";}
			if (strpos($PSUVFN, "XPPAYT") === false) {$PSUVFN .= "XPPAYT ";}
			if (strpos($PSUVFN, "XPPREM") === false) {$PSUVFN .= "XPPREM ";}
			if (strpos($PSUVFN, "XPPEEM") === false) {$PSUVFN .= "XPPEEM ";}
		} elseif (strpos($text, "UserViewEmpl.icl")>0 || strpos($text, "UserViewEmpl.php")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "UserViewEmpl";}
			if (strpos($PSUVFN, "XPCO#") === false)  {$PSUVFN .= "XPCO# ";}
			if (strpos($PSUVFN, "XPFAC#") === false) {$PSUVFN .= "XPFAC# ";}
			if (strpos($PSUVFN, "XPPECO") === false) {$PSUVFN .= "XPPECO ";}
			if (strpos($PSUVFN, "XPPELC") === false) {$PSUVFN .= "XPPELC ";}
			if (strpos($PSUVFN, "XPDEPT") === false) {$PSUVFN .= "XPDEPT ";}
			if (strpos($PSUVFN, "XPPAYT") === false) {$PSUVFN .= "XPPAYT ";}
			if (strpos($PSUVFN, "XPPREM") === false) {$PSUVFN .= "XPPREM ";}
			if (strpos($PSUVFN, "XPPEEM") === false) {$PSUVFN .= "XPPEEM ";}
		}

		if (strpos($text, "PrBankUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "PrBankUserView";}
			if (strpos($PSUVFN, "XPBANK") === false) {$PSUVFN .= "XPBANK ";}
		} elseif (strpos($text, "BankUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "BankUserView";}
			if (strpos($PSUVFN, "XPBNK#") === false) {$PSUVFN .= "XPBNK# ";}
		}

		if (strpos($text, "HrCoFacUserView")>0 && strpos($text, "UVFac")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "HrCoFacUserView";}
			if (strpos($PSUVFN, "XPPECO") === false) {$PSUVFN .= "XPPECO ";}
		} elseif (strpos($text, "HrCoFacUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "HrCoFacUserView";}
			if (strpos($PSUVFN, "XPCO#") === false)  {$PSUVFN .= "XPCO# ";}
			if (strpos($PSUVFN, "XPFAC#") === false) {$PSUVFN .= "XPFAC# ";}
		} elseif (strpos($text, "CoFacUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "CoFacUserView";}
			if (strpos($PSUVFN, "XPCO#") === false)  {$PSUVFN .= "XPCO# ";}
			if (strpos($PSUVFN, "XPFAC#") === false) {$PSUVFN .= "XPFAC# ";}
		}

		if (strpos($text, "CustomerUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "CustomerUserView";}
			if (strpos($PSUVFN, "XPSLSM") === false) {$PSUVFN .= "XPSLSM ";}
			if (strpos($PSUVFN, "XPCCLS") === false) {$PSUVFN .= "XPCCLS ";}
			if (strpos($PSUVFN, "XPCRGN") === false) {$PSUVFN .= "XPCRGN ";}
			if (strpos($PSUVFN, "XPBLLC") === false) {$PSUVFN .= "XPBLLC ";}
			if (strpos($PSUVFN, "XPCUST") === false) {$PSUVFN .= "XPCUST ";}
			if (strpos($PSUVFN, "XPWHS") === false)  {$PSUVFN .= "XPWHS ";}
		}

		if (strpos($text, "ItemPlantUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "ItemPlantUserView";}
			if (strpos($PSUVFN, "XPBUYR") === false) {$PSUVFN .= "XPBUYR ";}
			if (strpos($PSUVFN, "XPVEND") === false) {$PSUVFN .= "XPVEND ";}
			if (strpos($PSUVFN, "XPPLT") === false)  {$PSUVFN .= "XPPLT ";}
			if (strpos($PSUVFN, "XPITC") === false)  {$PSUVFN .= "XPITC ";}
			if (strpos($PSUVFN, "XPPTYP") === false) {$PSUVFN .= "XPPTYP ";}
			if (strpos($PSUVFN, "XPCLAS") === false) {$PSUVFN .= "XPCLAS ";}
			if (strpos($PSUVFN, "XPPCLS") === false) {$PSUVFN .= "XPPCLS ";}
		} elseif (strpos($text, "PlantUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "PlantUserView";}
			if (strpos($PSUVFN, "XPPLT") === false)  {$PSUVFN .= "XPPLT ";}
		}

		if (strpos($text, "ItemWarehouseUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "ItemWarehouseUserView";}
			if (strpos($PSUVFN, "XPPGRP") === false) {$PSUVFN .= "XPPGRP ";}
			if (strpos($PSUVFN, "XPWHS") === false)  {$PSUVFN .= "XPWHS ";}
			if (strpos($PSUVFN, "XPCYCL") === false) {$PSUVFN .= "XPCYCL ";}
		} elseif (strpos($text, "WarehouseUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "WarehouseUserView";}
			if (strpos($PSUVFN, "XPWHS") === false)  {$PSUVFN .= "XPWHS ";}
		}

		if (strpos($text, "ItemUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "ItemUserView";}
			if (strpos($PSUVFN, "XPPTYP") === false) {$PSUVFN .= "XPPTYP ";}
			if (strpos($PSUVFN, "XPPCLS") === false) {$PSUVFN .= "XPPCLS ";}
			if (strpos($PSUVFN, "XPPITC") === false) {$PSUVFN .= "XPPITC ";}
		}

		if (strpos($text, "LocationUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "LocationUserView";}
			if (strpos($PSUVFN, "XPBLLC") === false) {$PSUVFN .= "XPBLLC ";}
		}

		if (strpos($text, "LotUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "LotUserView";}
			if (strpos($PSUVFN, "XPBUYR") === false) {$PSUVFN .= "XPBUYR ";}
			if (strpos($PSUVFN, "XPVEND") === false) {$PSUVFN .= "XPVEND ";}
			if (strpos($PSUVFN, "XPPTYP") === false) {$PSUVFN .= "XPPTYP ";}
			if (strpos($PSUVFN, "XPPCLS") === false) {$PSUVFN .= "XPPCLS ";}
			if (strpos($PSUVFN, "XPWHS") === false)  {$PSUVFN .= "XPWHS ";}
			if (strpos($PSUVFN, "XPPITC") === false) {$PSUVFN .= "XPPITC ";}
		}

		if (strpos($text, "VendorUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "VendorUserView";}
			if (strpos($PSUVFN, "XPVTYP") === false) {$PSUVFN .= "XPVTYP ";}
			if (strpos($PSUVFN, "XPVEND") === false) {$PSUVFN .= "XPVEND ";}
		}

		if (strpos($text, "SalesmanUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "SalesmanUserView";}
			if (strpos($PSUVFN, "XPSLSM") === false) {$PSUVFN .= "XPSLSM ";}
		}

		if (strpos($text, "WFWrkItmUserView")>0) {
			if (trim($PSICLN)=="") {$PSICLN = "WFWrkItmUserView";}
			if (strpos($PSUVFN, "XPCO#") === false)  {$PSUVFN .= "XPCO# ";}
			if (strpos($PSUVFN, "XPFAC#") === false) {$PSUVFN .= "XPFAC# ";}
			if (strpos($PSUVFN, "XPTIN#") === false) {$PSUVFN .= "XPTIN# ";}
			if (strpos($PSUVFN, "XPBNK#") === false) {$PSUVFN .= "XPBNK# ";}
			if (strpos($PSUVFN, "XPACCT") === false) {$PSUVFN .= "XPACCT ";}
			if (strpos($PSUVFN, "XPBUYR") === false) {$PSUVFN .= "XPBUYR ";}
			if (strpos($PSUVFN, "XPVTYP") === false) {$PSUVFN .= "XPVTYP ";}
			if (strpos($PSUVFN, "XPVEND") === false) {$PSUVFN .= "XPVEND ";}
			if (strpos($PSUVFN, "XPSLSM") === false) {$PSUVFN .= "XPSLSM ";}
			if (strpos($PSUVFN, "XPCCLS") === false) {$PSUVFN .= "XPCCLS ";}
			if (strpos($PSUVFN, "XPCRGN") === false) {$PSUVFN .= "XPCRGN ";}
			if (strpos($PSUVFN, "XPBLLC") === false) {$PSUVFN .= "XPBLLC ";}
			if (strpos($PSUVFN, "XPCUST") === false) {$PSUVFN .= "XPCUST ";}
			if (strpos($PSUVFN, "XPPAYR") === false) {$PSUVFN .= "XPPAYR ";}
			if (strpos($PSUVFN, "XPEIN") === false)  {$PSUVFN .= "XPEIN ";}
			if (strpos($PSUVFN, "XPPECO") === false) {$PSUVFN .= "XPPECO ";}
			if (strpos($PSUVFN, "XPPELC") === false) {$PSUVFN .= "XPPELC ";}
			if (strpos($PSUVFN, "XPBANK") === false) {$PSUVFN .= "XPBANK ";}
			if (strpos($PSUVFN, "XPDEPT") === false) {$PSUVFN .= "XPDEPT ";}
			if (strpos($PSUVFN, "XPPAYT") === false) {$PSUVFN .= "XPPAYT ";}
			if (strpos($PSUVFN, "XPPAYD") === false) {$PSUVFN .= "XPPAYD ";}
			if (strpos($PSUVFN, "XPPREM") === false) {$PSUVFN .= "XPPREM ";}
			if (strpos($PSUVFN, "XPPEEM") === false) {$PSUVFN .= "XPPEEM ";}
			if (strpos($PSUVFN, "XPSCHD") === false) {$PSUVFN .= "XPSCHD ";}
			if (strpos($PSUVFN, "XPGRP") === false)  {$PSUVFN .= "XPGRP ";}
			if (strpos($PSUVFN, "XPCODE") === false) {$PSUVFN .= "XPCODE ";}
			if (strpos($PSUVFN, "XPLCOD") === false) {$PSUVFN .= "XPLCOD ";}
			if (strpos($PSUVFN, "XPINCD") === false) {$PSUVFN .= "XPINCD ";}
			if (strpos($PSUVFN, "XPPLT") === false)  {$PSUVFN .= "XPPLT ";}
			if (strpos($PSUVFN, "XPMFDP") === false) {$PSUVFN .= "XPMFDP ";}
			if (strpos($PSUVFN, "XPWC") === false)   {$PSUVFN .= "XPWC ";}
			if (strpos($PSUVFN, "XPITC") === false)  {$PSUVFN .= "XPITC ";}
			if (strpos($PSUVFN, "XPPTYP") === false) {$PSUVFN .= "XPPTYP ";}
			if (strpos($PSUVFN, "XPPGRP") === false) {$PSUVFN .= "XPPGRP ";}
			if (strpos($PSUVFN, "XPCLAS") === false) {$PSUVFN .= "XPCLAS ";}
			if (strpos($PSUVFN, "XPPCLS") === false) {$PSUVFN .= "XPPCLS ";}
			if (strpos($PSUVFN, "XPWHS") === false)  {$PSUVFN .= "XPWHS ";}
			if (strpos($PSUVFN, "XPPITC") === false) {$PSUVFN .= "XPPITC ";}
			if (strpos($PSUVFN, "XPPPTY") === false) {$PSUVFN .= "XPPPTY ";}
			if (strpos($PSUVFN, "XPCYCL") === false) {$PSUVFN .= "XPCYCL ";}
			if (strpos($PSUVFN, "XPSTKR") === false) {$PSUVFN .= "XPSTKR ";}
			if (strpos($PSUVFN, "XPAILE") === false) {$PSUVFN .= "XPAILE ";}
			if (strpos($PSUVFN, "XPSLOC") === false) {$PSUVFN .= "XPSLOC ";}
			if (strpos($PSUVFN, "XPOOCD") === false) {$PSUVFN .= "XPOOCD ";}
			if (strpos($PSUVFN, "XPPOCD") === false) {$PSUVFN .= "XPPOCD ";}
			if (strpos($PSUVFN, "XPTTYP") === false) {$PSUVFN .= "XPTTYP ";}
			if (strpos($PSUVFN, "XPSITE") === false) {$PSUVFN .= "XPSITE ";}
			if (strpos($PSUVFN, "XPPROP") === false) {$PSUVFN .= "XPPROP ";}
			if (strpos($PSUVFN, "XPFMCD") === false) {$PSUVFN .= "XPFMCD ";}
		}

		$maintenanceCode = "U";

		$edtVar= "";
		Concat_Field("@@pgtp", $PSPGTP);
		Concat_Field("@@pgnm", $PSPGNM);
		Concat_Field("@@desc", $PSDESC);
		$PSPOSP=strtoupper($PSPOSP); Concat_Field("@@posp", $PSPOSP);
		Concat_Field("@@uvfn", $PSUVFN);
		Concat_Field("@@icln", $PSICLN);
		$edtVar .= "}{";


		$returnValue=Validate_Data($userProfile, $maintenanceCode, $errFound, $edtVar, $errVar, $comments);
		$maintenanceCode=$returnValue['maintenanceCode'];
		$errFound       =$returnValue['errFound'];
		$edtVar         =$returnValue['edtVar'];
		$errVar         =$returnValue['errVar'];
		$comments       =$returnValue['comments'];
	}
	$startRow ++;
}

print "</table>";
print "\n </body> \n </html>";

function Validate_Data($userProfile,$maintenanceCode,$errFound,$edtVar,$errVar,$comments) {
	global $pgmLibrary, $i5Connect;
	if (is_null($errFound )) $errFound="";
	if (is_null($edtVar ))   $edtVar="";
	if (is_null($errVar ))   $errVar="";
	if (is_null($comments))  $comments="";

	$pgmCall = array(
	array("Name"=>"userProfile",     "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"10"),
	array("Name"=>"maintenanceCode", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"errFound",        "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"1"),
	array("Name"=>"edtVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"errVar",          "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"),
	array("Name"=>"comments",       "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"32000"));

	$pgm = i5_program_prepare("HSYSUM_W", $pgmCall);
	if (!$pgm) {die("<br>Validate_Data (HSYSUM_W) prepare error. Error Number=".i5_errno()." msg=".i5_errormsg());}

	$parmIn = array(
	"userProfile"    =>$userProfile,
	"maintenanceCode"=>$maintenanceCode,
	"errFound"       =>$errFound,
	"edtVar"         =>$edtVar,
	"errVar"         =>$errVar,
	"comments"       =>$comments);

	$parmOut = array(
	"userProfile"    =>"userProfile",
	"maintenanceCode"=>"maintenanceCode",
	"errFound"       =>"errFound",
	"edtVar"         =>"edtVar",
	"errVar"         =>"errVar",
	"comments"       =>"comments");

	$ret = i5_program_call($pgm, $parmIn, $parmOut);
	if (function_exists('i5_output')) extract(i5_output());
	if (!$ret) {die("<br>Validate_Data (HSYSUM_W) call errno=".i5_errno()." msg=".i5_errormsg());}

	$returnValue['userProfile']    =$userProfile;
	$returnValue['maintenanceCode']=$maintenanceCode;
	$returnValue['errFound']       =$errFound;
	$returnValue['edtVar']         =$edtVar;
	$returnValue['errVar']         =$errVar;
	$returnValue['comments']       =$comments;
	return $returnValue;
}

?>
