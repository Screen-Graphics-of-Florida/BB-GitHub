<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';
require_once 'GetGLDDParm.php';

$accountNumber  = $_GET['accountNumber'];
$subAccount     = $_GET['subAccount'];
$coNumber       = $_GET['coNumber'];
$facNumber      = $_GET['facNumber'];
$accountAmount  = $_GET['accountAmount'];
$fromPer        = $_GET['fromPer'];
$toPer          = $_GET['toPer'];
$columnValue    = $_GET['columnValue'];
$ap_orderBy     = $_GET['ap_orderBy'];
$ar_orderBy     = $_GET['ar_orderBy'];
$fa_orderBy     = $_GET['fa_orderBy'];
$iv_orderBy     = $_GET['iv_orderBy'];
$gl_orderBy     = $_GET['gl_orderBy'];
$oth_orderBy    = $_GET['oth_orderBy'];

require_once 'SetLibraryList.php';

require_once "ARControl$dataBaseID.php";
require_once "SystemControl$dataBaseID.php";

require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';

$page_title     = "Drill Down Subsystem Entries";
$scriptName     = "GLDDSubsystem.php";
$scriptVarBase  = "{$genericVarBase}{$glDDVarBase}&amp;accountNumber=" . urlencode(trim($accountNumber)) . "&amp;subAccount=" . urlencode(trim($subAccount)) . "&amp;coNumber=" . urlencode(trim($coNumber)) . "&amp;facNumber=" . urlencode(trim($facNumber)) . "&amp;accountAmount=" . urlencode(trim($accountAmount)) . "&amp;fromPer=" . urlencode(trim($fromPer)) . "&amp;toPer=" . urlencode(trim($toPer)) . "&amp;columnValue=" . urlencode(trim($columnValue)) . "&amp;ap_orderBy=" . urlencode(trim($ap_orderBy)) . "&amp;ar_orderBy=" . urlencode(trim($ar_orderBy)) . "&amp;fa_orderBy=" . urlencode(trim($fa_orderBy)) . "&amp;iv_orderBy=" . urlencode(trim($iv_orderBy)) . "&amp;gl_orderBy=" . urlencode(trim($gl_orderBy)) . "&amp;oth_orderBy=" . urlencode(trim($oth_orderBy)) ;
$nextPrevVar    = "{$scriptVarBase}";
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$dspMaxRows     = $dspMaxRowsDft;
$prtMaxRows     = $prtMaxRowsDft;
$dftScriptName  = "GLDDSubsystem.php";

// Set up different Order By ****************************************
$scriptName     = "GLDDSubAP";
$dftOrderBy = array(array("VMVNA1U","A","Vendor Name"),array("APVOU","A","Voucher"),array("APLINE","A","Voucher Line"));
require 'FilterInit.php';
require 'FilterDefault.php';
$ap_orderBy        = $orderBy;
$ap_orderByDisplay = $orderByDisplay;

$scriptName     = "GLDDSubAR";
$dftOrderBy = array(array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A",""));
require 'FilterInit.php';
require 'FilterDefault.php';
$ar_orderBy        = $orderBy;
$ar_orderByDisplay = $orderByDisplay;

$scriptName     = "GLDDSubFA";
$dftOrderBy = array(array("GFRPTS","A","Transaction Type"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number"));
require 'FilterInit.php';
require 'FilterDefault.php';
$fa_orderBy        = $orderBy;
$fa_orderByDisplay = $orderByDisplay;

$scriptName     = "GLDDSubIV";
$dftOrderBy = array(array("IVTRDT","D","Transaction Date"),array("IVITEM","A","Item Number"));
require 'FilterInit.php';
require 'FilterDefault.php';
$iv_orderBy        = $orderBy;
$iv_orderByDisplay = $orderByDisplay;

$scriptName     = "GLDDSubPO";
$dftOrderBy = array(array("POTRDT","D","Transaction Date"),array("POITEM","A","Item Number"));
require 'FilterInit.php';
require 'FilterDefault.php';
$po_orderBy        = $orderBy;
$po_orderByDisplay = $orderByDisplay;

$scriptName     = "GLDDSubGL";
$dftOrderBy = array(array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Journal Sequence Number"),array("TDREC","A",""));
require 'FilterInit.php';
require 'FilterDefault.php';
$gl_orderBy        = $orderBy;
$gl_orderByDisplay = $orderByDisplay;

$scriptName     = "GLDDSubOTH";
$dftOrderBy = array(array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Journal Sequence Number"),array("TDREC","A",""));
require 'FilterInit.php';
require 'FilterDefault.php';
$oth_orderBy        = $orderBy;
$oth_orderByDisplay = $orderByDisplay;

$scriptName     = $dftScriptName;   // Put back to actual Page
require 'FilterInit.php';
require 'FilterDefault.php';

if ($formatToPrint != ""){$dspMaxRows = $prtMaxRows;}
$maxRows = $dspMaxRows;

if ($tag == "AP_ORDERBY"){
	if     ($sequence == "Amount")      {$orby = array(array("APAMT","A","Amount"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "Source")      {$orby = array(array("APRPTS","A","Source"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "Invoice")     {$orby = array(array("APINV","A","Invoice"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "InvDate")     {$orby = array(array("APINVD","A","Invoice Date"),array("APINV","A","Invoice"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "VendorName")  {$orby = array(array("VMVNA1U","A","Vendor Name"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "DistPd")      {$orby = array(array("APDSTD","A","Distribution Period"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "PONumber")    {$orby = array(array("APPO","A","Purchase Order"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "Memo")        {$orby = array(array("APMEMO","A","Memo"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "Voucher")     {$orby = array(array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "Bank")        {$orby = array(array("APBANK","A","Bank"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "CheckNumber") {$orby = array(array("APCHK","A","Check"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "CheckDate")   {$orby = array(array("APCHKD","A","Check Date"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}
	elseif ($sequence == "CheckCode")   {$orby = array(array("APCKCD","A","Check Code"),array("APVOU","A","Voucher"),array("APLINE","A","")) ;}

	$scriptName    = "GLDDSubAP";
	$orderBy       =$ap_orderBy;
	$orderByDisplay=$ap_orderByDisplay;
	require 'FilterInit.php';
	require 'OrderByUpdate.php';
	$ap_orderBy        = $orderBy;
	$ap_orderByDisplay = $orderByDisplay;
	$scriptName     = $dftScriptName;   // Put back to actual Page
}

if ($tag == "AR_ORDERBY") {
	if     ($sequence == "Amount")       {$orby = array(array("ARAMT","A","Amount"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Source")       {$orby = array(array("ARRPTS","A","Source"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "PmtCode")      {$orby = array(array("ARSBCD","A","Payment Code"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Invoice")      {$orby = array(array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "InvDate")      {$orby = array(array("ARIVDT","A","Invoice Date"),array("ARAINV","A","Invoice"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "DueDate")      {$orby = array(array("ARDUED","A","Due Date"),array("ARAINV","A","Invoice"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "CustomerName") {$orby = array(array("CMCNA1U","A","Customer Name"),array("ARBLTO","A","Customer"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "DatePaid")     {$orby = array(array("ARDTPD","A","Date Paid"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "CheckNumber")  {$orby = array(array("ARCHK","A","Document"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Comment")      {$orby = array(array("HAS_YPCMNT","A","Comment"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Memo")         {$orby = array(array("YPMEMO","A","Memo"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Location")     {$orby = array(array("ARLOC","A","Location"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Salesman")     {$orby = array(array("ARSLSM","A","Salesman"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Payer")        {$orby = array(array("ARPAYR","A","Payer"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Bank")         {$orby = array(array("ARBANK","A","Bank"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "Batch")        {$orby = array(array("YPBCH","A","Batch"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "OrderNumber")  {$orby = array(array("ARORD","A","Order Number"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "MfgOrder")     {$orby = array(array("ARMORD","A","Mfg Order"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}
	elseif ($sequence == "ForeignAmt")   {$orby = array(array("ARFAMT","A","Foreign Amount"),array("ARAINV","A","Invoice"),array("ARIVDT","A","Invoice Date"),array("ARBLTO","A","Customer"),array("ARISEQ","A","")) ;}

	$scriptName    = "GLDDSubAR";
	$orderBy       =$ar_orderBy;
	$orderByDisplay=$ar_orderByDisplay;
	require 'FilterInit.php';
	require 'OrderByUpdate.php';
	$ar_orderBy        = $orderBy;
	$ar_orderByDisplay = $orderByDisplay;
	$scriptName     = $dftScriptName;   // Put back to actual Page
}

if ($tag == "FA_ORDERBY") {
	if     ($sequence == "Amount")        {$orby = array(array("GFAMT","A","Amount"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "TransType")     {$orby = array(array("GFRPTS","A","Transaction Type"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "Asset")         {$orby = array(array("GFASST","A","Asset Number"),array("GFSITE","A","Site")) ;}
	elseif ($sequence == "AssetDescr")    {$orby = array(array("GFDESC","A","Asset Description"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "PropType")      {$orby = array(array("GFPROP","A","Property Type"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "RetireCode")    {$orby = array(array("GFFPCD","A","Retirement Code"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "RetireDate")    {$orby = array(array("GFRTDT","A","Retirement Date"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "RetireReason")  {$orby = array(array("GFRRES","A","Retirement Reason"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "TrfCode")       {$orby = array(array("GFFPCD","A","Transfer Code"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "TrfDate")       {$orby = array(array("GFRTDT","A","Transfer Date"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "TrfToAsset")    {$orby = array(array("GFTSIT","A","Transfer To Asset"),array("GFTAST","A",""),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "TrfPeriod")     {$orby = array(array("GFTFPD","A","Transfer Period"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "TrfFrAsset")    {$orby = array(array("GFTSIT","A","Transfer From Asset"),array("GFTAST","A",""),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "BeginDeprDate") {$orby = array(array("GFBDDT","A","Begin Depr Date"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "Life")          {$orby = array(array("GFLFYY","A","Life"),array("GFLFMM","A",""),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "DeprMeth")      {$orby = array(array("GFDPCD","A","Depreciation Method"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "DeprPeriod")    {$orby = array(array("GFPER","A","Depreciation Period"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "SiteName")      {$orby = array(array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}
	elseif ($sequence == "Schedule")      {$orby = array(array("GFSCHD","A","Schedule Name"),array("GFSITE","A","Site"),array("GFASST","A","Asset Number")) ;}

	$scriptName    = "GLDDSubFA";
	$orderBy       =$fa_orderBy;
	$orderByDisplay=$fa_orderByDisplay;
	require 'FilterInit.php';
	require 'OrderByUpdate.php';
	$fa_orderBy        = $orderBy;
	$fa_orderByDisplay = $orderByDisplay;
	$scriptName     = $dftScriptName;   // Put back to actual Page
}

if ($tag == "IV_ORDERBY") {
	if     ($sequence == "Amount")    {$orby = array(array("IVAMT","A","Amount")) ;}
	elseif ($sequence == "TransDate") {$orby = array(array("IVTRDT","D","Transaction Date")) ;}
	elseif ($sequence == "TransType") {$orby = array(array("IVIVTT","A","Transaction Type")) ;}
	elseif ($sequence == "ItemNumber"){$orby = array(array("IVITEM","A","Item Number")) ;}
	elseif ($sequence == "ItemDesc")  {$orby = array(array("IMIMDS","A","Description")) ;}
	elseif ($sequence == "Warehouse") {$orby = array(array("IVWHS","A","Warehouse")) ;}
	elseif ($sequence == "Plant")     {$orby = array(array("IVPLT","A","Plant")) ;}
	elseif ($sequence == "MfgOrder")  {$orby = array(array("IVMORD","A","Mfg Order")) ;}
	elseif ($sequence == "Quantity")  {$orby = array(array("IVQTY","A","Quantity")) ;}
	elseif ($sequence == "ProdClass") {$orby = array(array("IVPCLS","A","Product Class")) ;}
	elseif ($sequence == "InvType")   {$orby = array(array("IVITC","A","Inventory Type")) ;}

	$scriptName    = "GLDDSubIV";
	$orderBy       =$iv_orderBy;
	$orderByDisplay=$iv_orderByDisplay;
	require 'FilterInit.php';
	require 'OrderByUpdate.php';
	$iv_orderBy        = $orderBy;
	$iv_orderByDisplay = $orderByDisplay;
	$scriptName     = $dftScriptName;   // Put back to actual Page
}

if ($tag == "PO_ORDERBY") {
	if     ($sequence == "Amount")        {$orby = array(array("POAMT","A","Amount")) ;}
	elseif ($sequence == "TransDate")     {$orby = array(array("POTRDT","A","Transaction Date")) ;}
	elseif ($sequence == "TransType")     {$orby = array(array("POIVTT","A","Transaction Type")) ;}
	elseif ($sequence == "ItemNumber")    {$orby = array(array("POITEM","A","Item Number")) ;}
	elseif ($sequence == "ItemDesc")      {$orby = array(array("POIMDSU","A","Description")) ;}
	elseif ($sequence == "Warehouse")     {$orby = array(array("POWHS","A","Warehouse")) ;}
	elseif ($sequence == "Plant")         {$orby = array(array("POPLT","A","Plant")) ;}
	elseif ($sequence == "PurchaseOrder") {$orby = array(array("POPO","A","Purchase Order")) ;}
	elseif ($sequence == "Quantity")      {$orby = array(array("POQTY","A","Quantity")) ;}
	elseif ($sequence == "ProdClass")     {$orby = array(array("POPCLS","A","Product Class")) ;}
	elseif ($sequence == "InvType")       {$orby = array(array("POITC","A","Inventory Type")) ;}
	elseif ($sequence == "VendorName")    {$orby = array(array("VMVNA1U","A","Vendor Name")) ;}
	elseif ($sequence == "VendorNumber")  {$orby = array(array("POVEND","A","Vendor Number")) ;}

	$scriptName    = "GLDDSubPO";
	$orderBy       =$po_orderBy;
	$orderByDisplay=$po_orderByDisplay;
	require 'FilterInit.php';
	require 'OrderByUpdate.php';
	$po_orderBy        = $orderBy;
	$po_orderByDisplay = $orderByDisplay;
	$scriptName     = $dftScriptName;   // Put back to actual Page
}

if ($tag == "GL_ORDERBY") {
	if     ($sequence == "Amount")      {$orby = array(array("TDAMT","A","Amount"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Journal")     {$orby = array(array("TDJRNL","A","Journal"),array("TDTDTE","A","Transaction Date"),array("TDJSEQ","A","Journal Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Type")        {$orby = array(array("TJTTYP","A","Type"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Reference")   {$orby = array(array("TDREF","A","Reference"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Period")      {$orby = array(array("TDPER","A","Period"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Description") {$orby = array(array("TDDESC","A","Description"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Date")        {$orby = array(array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Journal Sequence"),array("TDREC","A","")) ;}

	$scriptName    = "GLDDSubGL";
	$orderBy       =$gl_orderBy;
	$orderByDisplay=$gl_orderByDisplay;
	require 'FilterInit.php';
	require 'OrderByUpdate.php';
	$gl_orderBy        = $orderBy;
	$gl_orderByDisplay = $orderByDisplay;
	$scriptName     = $dftScriptName;   // Put back to actual Page
}

if ($tag == "OTH_ORDERBY") {
	if     ($sequence == "Amount")      {$orby = array(array("TDAMT","A","Amount"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Journal")     {$orby = array(array("TDJRNL","A","Journal"),array("TDTDTE","A","Transaction Date"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Type")        {$orby = array(array("TJTTYP","A","Type"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Reference")   {$orby = array(array("TDREF","A","Reference"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Period")      {$orby = array(array("TDPER","A","Period"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Description") {$orby = array(array("TDDESC","A","Description"),array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}
	elseif ($sequence == "Date")        {$orby = array(array("TDTDTE","A","Transaction Date"),array("TDJRNL","A","Journal"),array("TDJSEQ","A","Sequence"),array("TDREC","A","")) ;}

	$scriptName    = "GLDDSubOTH";
	$orderBy       =$oth_orderBy;
	$orderByDisplay=$oth_orderByDisplay;
	require 'FilterInit.php';
	require 'OrderByUpdate.php';
	$oth_orderBy        = $orderBy;
	$oth_orderByDisplay = $orderByDisplay;
	$scriptName     = $dftScriptName;   // Put back to actual Page
}

$DDUNPS=RetValue("DDGRSN='$ddReport'", "GLDDRP", "DDUNPS");
$DDEOYA=RetValue("DDGRSN='$ddReport'", "GLDDRP", "DDEOYA");

// Build User View for the different subsystems ****************************************
// Accounts Payable User View
$uv_CompanyName   ="TDCO";
$uv_FacilityName  ="TDFAC";
$uv_AccountName   ="TDACCT";
$uv_SubaccountName="TDSUB";
$uv_VendorName    ="APVEND";
$uv_VendorTypeName="VMVTYP";
require 'UserView.php';
$uv_Sql_ap=$uv_Sql;

$ap_fileSQL="";
$ap_selectSQL="";
$ap_fileSQL .=" APGLDD ";
$ap_fileSQL .=" inner join GLTRDT on (TDPER#,TDDDSQ,TDDDFL,TDDDPG,TDAPID)=(APPER#,APDDSQ,APDDFL,' ','AP') and TDDDSQ>0";
$ap_fileSQL .=" left join HDVEND on VMVEND=APVEND";
$ap_fileSQL .=" left join HDBANK on BKBANK=APBANK";
$ap_fileSQL .=" left join SYFLAG a on (a.FLTYPE,a.FLVALU)=('APFEED',APRPTS)";
$ap_fileSQL .=" left join SYFLAG b on (b.FLTYPE,b.FLVALU)=('APCHECKCD',APCKCD)";
$ap_selectSQL .= " (TDCO,TDFAC,TDACCT,TDSUB)=($coNumber,$facNumber,$accountNumber,$subAccount) ";
$ap_selectSQL .= " and TDPER# between $fromPer and $toPer ";
if ($uv_Sql_ap != "") {$ap_selectSQL . " and ($uv_Sql_ap)";}
$view_ap=RetValue($ap_selectSQL, $ap_fileSQL, "CHAR(Count(*))");

// Accounts Receivable User View
$uv_CompanyName        ="TDCO";
$uv_FacilityName       ="TDFAC";
$uv_AccountName        ="TDACCT";
$uv_SubaccountName     ="TDSUB";
$uv_CustomerName       ="ARBLTO";
$uv_CustomerClassName  ="CMCCLS";
$uv_RegionName         ="CMCRGN";
$uv_BillingLocationName="CMLOC#";
$uv_SalesmanName       ="CMSLSM";
$uv_WarehouseName      ="CMWH#";
require 'UserView.php';
$uv_Sql_ar=$uv_Sql;

$ar_fileSQL="";
$ar_selectSQL="";
$ar_fileSQL .= " ARGLDD";
$ar_fileSQL .= " inner join GLTRDT on (TDPER#,TDDDSQ,TDDDFL,TDDDPG,TDAPID)=(ARPER,ARDDSQ,ARDDFL,' ','AR') and TDDDSQ>0 ";
$ar_fileSQL .= " left join HDCUST on CMCUST=ARBLTO";
$ar_fileSQL .= " left join HDBANK on BKBANK=ARBANK";
$ar_fileSQL .= " left join ARPYSB on PSSBCD=ARSBCD and ARGLDD.ARRPTS <> 'SLS'";
$ar_fileSQL .= " left join HDSLSM on SMSLSM=ARSLSM";
$ar_fileSQL .= " left join ARPYRH on PYPAYR=ARPAYR ";
$ar_fileSQL .= " left join ARYPTD on (YPISEQ,YPPSEQ)=(ARISEQ,ARPSEQ) ";
$ar_fileSQL .= " left join HDINVC on IVISEQ=ARISEQ ";
$ar_fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('ARFEED',ARRPTS)";
$ar_fileSQL .= " left join HDLCTN on LOLOC#=ARLOC";
$ar_fileSQL .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=ARORD and HHLIV#=ARAINV ";
$ar_selectSQL .= " (TDCO,TDFAC,TDACCT,TDSUB)=($coNumber,$facNumber,$accountNumber,$subAccount) ";
$ar_selectSQL .= " and TDPER# between $fromPer and $toPer ";
if ($uv_Sql_ar != "") {$ar_selectSQL .= " and ($uv_Sql_ar) ";}
$view_ar=RetValue($ar_selectSQL, $ar_fileSQL, "CHAR(Count(*))");

// Fixed Assets User View
$uv_CompanyName     ="TDCO";
$uv_FacilityName    ="TDFAC";
$uv_AccountName     ="TDACCT";
$uv_SubaccountName  ="TDSUB";
$uv_SiteName        ="GFSITE";
$uv_PropertyTypeName="GFPROP";
$uv_FamilyCodeName  ="GFFMCD";
require 'UserView.php';
$uv_Sql_fa=$uv_Sql;

$fa_fileSQL="";
$fa_selectSQL="";
$fa_fileSQL .= " FAGLDD ";
$fa_fileSQL .= " inner join GLTRDT on (TDPER#,TDDDSQ,TDDDFL,TDDDPG,TDAPID)=(GFPER#,GFDDSQ,GFDDFL,' ','FA') and TDDDSQ>0 ";
$fa_fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('FAFEED',GFRPTS) ";
$fa_selectSQL .= " (TDCO,TDFAC,TDACCT,TDSUB)=($coNumber,$facNumber,$accountNumber,$subAccount) ";
$fa_selectSQL .= " and TDPER# between $fromPer and $toPer ";
if ($uv_Sql_fa != "") {$fa_selectSQL .= " and ($uv_Sql_fa) ";}
$view_fa=RetValue($fa_selectSQL, $fa_fileSQL, "CHAR(Count(*))");

// Inventory User View
$uv_CompanyName   ="TDCO";
$uv_FacilityName  ="TDFAC";
$uv_AccountName   ="TDACCT";
$uv_SubaccountName="TDSUB";
require 'UserView.php';
$uv_Sql_iv=$uv_Sql;

$iv_fileSQL="";
$iv_selectSQL="";
$iv_fileSQL .= " IVGLDD ";
$iv_fileSQL .= " inner join GLTRDT on (TDPER#,TDDDSQ,TDDDFL,TDDDPG,TDAPID)=(IVPER#,IVDDSQ,IVDDFL,' ','IV') and TDDDSQ>0 ";
$iv_fileSQL .= " left join HDIMST on IVITEM=IMITEM ";
$iv_fileSQL .= " left join HDTTYP on TTTYPE=IVIVTT ";
$iv_fileSQL .= " left join HDWHSM on WHWHS =IVWHS ";
$iv_fileSQL .= " left join HDPLNT on PLPLNT=IVPLT ";
$iv_fileSQL .= " left join HDPCLS on PCPCLS=IVPCLS ";
$iv_fileSQL .= " left join HDITYP on ITITC =IVITC ";
$iv_selectSQL .= " (TDCO,TDFAC,TDACCT,TDSUB)=($coNumber,$facNumber,$accountNumber,$subAccount) ";
$iv_selectSQL .= " and TDPER# between $fromPer and $toPer ";
if ($uv_Sql_iv != "") {$iv_selectSQL .= " and ($uv_Sql_iv) ";}
$view_iv=RetValue($iv_selectSQL, $iv_fileSQL, "CHAR(Count(*))");

// Purchasing User View
$uv_CompanyName   ="TDCO";
$uv_FacilityName  ="TDFAC";
$uv_AccountName   ="TDACCT";
$uv_SubaccountName="TDSUB";
require 'UserView.php';
$uv_Sql_po=$uv_Sql;

$po_fileSQL="";
$po_selectSQL="";
$po_fileSQL .= " POGLDD ";
$po_fileSQL .= " inner join GLTRDT on (TDPER#,TDDDSQ,TDDDFL,TDDDPG,TDAPID)=(POPER#,PODDSQ,PODDFL,' ','PO') and TDDDSQ>0 ";
$po_fileSQL .= " left join HDTTYP on TTTYPE=POIVTT ";
$po_fileSQL .= " left join HDWHSM on WHWHS =POWHS ";
$po_fileSQL .= " left join HDPLNT on PLPLNT=POPLT ";
$po_fileSQL .= " left join HDVEND on VMVEND = POVEND ";
$po_fileSQL .= " left join HDPCLS on PCPCLS=POPCLS ";
$po_fileSQL .= " left join HDITYP on ITITC =POITC ";
$po_selectSQL .= " (TDCO,TDFAC,TDACCT,TDSUB)=($coNumber,$facNumber,$accountNumber,$subAccount) ";
$po_selectSQL .= " and TDPER# between $fromPer and $toPer ";
if ($uv_Sql_po != "") {$po_selectSQL .= " and ($uv_Sql_po) ";}
$view_po=RetValue($po_selectSQL, $po_fileSQL, "CHAR(Count(*))");

// General Ledger User View
$uv_CompanyName   ="TDCO";
$uv_FacilityName  ="TDFAC";
$uv_AccountName   ="TDACCT";
$uv_SubaccountName="TDSUB";
require 'UserView.php';
$uv_Sql_gl=$uv_Sql;

$gl_fileSQL="";
$gl_selectSQL="";
$gl_fileSQL .= " GLTRDT ";
$gl_fileSQL .= " inner join GLTRJL on (TJJRNL,TJPER#,TJTDTE,TJJSEQ)=(TDJRNL,TDPER#,TDTDTE,TDJSEQ) ";
$gl_fileSQL .= " left join SYFLAG on (FLTYPE,FLVALU)=('GLTRANTYP',TJTTYP) ";
$gl_selectSQL .= " (TDCO,TDFAC,TDACCT,TDSUB,TDDDSQ)=($coNumber,$facNumber,$accountNumber,$subAccount,0) ";
$gl_selectSQL .= " and TDPER# between $fromPer and $toPer ";
if ($DDUNPS == "N") {$gl_selectSQL .= " and TJSTAT='P' ";}
if ($DDEOYA == "N") {$gl_selectSQL .= " and TJTTYP<>'YE' ";}
if ($uv_Sql_gl != "") {$gl_selectSQL .= " and ($uv_Sql_gl) ";}
$view_gl=RetValue($gl_selectSQL, $gl_fileSQL, "CHAR(Count(*))");

// Other
$ot_fileSQL="";
$ot_selectSQL="";
$ot_fileSQL .= " GLTRDT ";
$ot_fileSQL .= " inner join GLTRJL on (TJJRNL,TJPER#,TJTDTE,TJJSEQ)=(TDJRNL,TDPER#,TDTDTE,TDJSEQ) ";
$ot_selectSQL .= " (TDCO,TDFAC,TDACCT,TDSUB,TDDDPG)=($coNumber,$facNumber,$accountNumber,$subAccount,'Y') ";
$ot_selectSQL .= " and TDPER# between $fromPer and $toPer ";
$ot_selectSQL .= " and TDDDSQ<>0 ";
if ($uv_Sql_gl != "") {$ot_selectSQL .= " and ($uv_Sql_gl) ";}
$view_ot=RetValue($ot_selectSQL, $ot_fileSQL, "CHAR(Count(*))");

// Build Page ****************************************
require_once ($docType);
print "\n <html> \n	<head>";
require_once ($headInclude);
print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}{$GLDDStyleSheet}\"> ";

print "\n \n <script TYPE=\"text/javascript\">";
require_once 'NewWindowOpen.php';
print "\n </script> \n";

require_once ($genericHead);
print "\n </head>";
print "\n <body $bodyTagAttr>";
require $inquiryBanner;
// Header ************************************************
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
print "\n <td class=\"content\">";

require 'stmtSQLClear.php';
$appendUserView="N";
$appendWildCard="N";
$stmtSQL .= " SELECT DHUSRN,DHGRH1,DHGRH2,DHGRH3,DHGRHP ";
$fileSQL .= " GLDDHD ";
$selectSQL .= " (DHGRSN,DHCO,DHFAC)=('$ddReport',$ddCompany,$ddFacility) ";
require 'stmtSQLSelect.php';
$stmtSQL   .= " Order By DHGRSN ";
require 'stmtSQLEnd.php';
$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
$row = db2_fetch_assoc($sqlResult);
if (! $row) {exit;}

print "\n <a NAME=\"top\"></a> ";
print "\n <table $contentTable> ";
print "\n <colgroup><col width=\"80%\"><col width=\"15%\"> ";
print "\n <tr><td><h1>$page_title</h1></td> ";
if ($formatToPrint != "Y") {
	print "\n <td class=\"toolbar\"> ";
	if ($ddReport!="") {print "\n <a href=\"{$homeURL}{$cGIPath}GLDDReport.d2w/REPORT{$altVarBase}{$glDDVarBase}&amp;timeStamp=" . urlencode(trim($_SERVER['REQUEST_TIME'])) . " title=\"Return To Drill Down Report\">$portalHome</a> ";}
	require_once 'FormatToprint.php';
	require_once 'HelpPage.php';
	print "\n </td> ";
}
print "\n </tr> ";

print "\n <tr><td><h2>$row[DHUSRN]</h2></td></tr> ";
if (trim($row['DHGRH1']) != "") {print "\n <tr><td><h2>$row[DHGRH1]</h2></td></tr> ";}
if (trim($row['DHGRH2']) != "") {print "\n <tr><td><h2>$row[DHGRH2]</h2></td></tr> ";}
if (trim($row['DHGRH3']) != "") {print "\n <tr><td><h2>$row[DHGRH3]</h2></td></tr> ";}
print "\n <tr><td><h2>$row[DHGRHP]</h2></td></tr> ";

if ($uv_Sql_ap != "" || $uv_Sql_ar != "" || $uv_Sql_fa != "" || $uv_Sql_iv != "" || $uv_Sql_po != "" || $uv_Sql_gl != "") {
	print "\n <tr><td><h3> You may not be authorized to view some transactions </h3></td></tr> ";
}

print "\n </table> ";

require $inquiryBanner;

if ($formatToPrint != "Y") {
	print "\n <table $contentTable> ";
	if ($view_ap>0) {print "\n <tr><td><a href=\"#accountspayable\">Accounts Payable</a></td></tr> ";}
	if ($view_ar>0) {print "\n <tr><td><a href=\"#accountsreceivable\">Accounts Receivable</a></td></tr> ";}
	if ($view_fa>0) {print "\n <tr><td><a href=\"#fixedassets\">Fixed Assets</a></td></tr> ";}
	if ($view_iv>0) {print "\n <tr><td><a href=\"#inventory\">Inventory</a></td></tr> ";}
	if ($view_gl>0) {print "\n <tr><td><a href=\"#generalledger\">General Ledger</a></td></tr> ";}
	if ($view_po>0) {print "\n <tr><td><a href=\"#purchasing\">Purchasing</a></td></tr> ";}
	if ($view_ot>0) {print "\n <tr><td><a href=\"#other\">Other</a></td></tr> ";}
	print "\n <tr><td>&nbsp;</td></tr> ";
	print "\n </table> ";
}

print "\n <table $contentTable> ";
print "\n     <tr><th class=\"colhdr\">Co/Fac</th> ";
print "\n         <th class=\"colhdr\">Name</th> ";
print "\n         <th class=\"colhdr\">Account</th> ";
print "\n         <th class=\"colhdr\">Account Name</th> ";
print "\n         <th class=\"colhdr\">Type</th> ";
print "\n         <th class=\"colhdr\">Usage</th> ";
if ($HDMCRL>0) {print "\n <th class=\"colhdr\">Cur</th> ";}
print "\n         <th class=\"colhdr\">Amount</th> ";
if ($columnValue=="") {print "\n  <th class=\"colhdr\">Opening Balance</th> ";}
print "\n     </tr> ";

$returnValue=Retrieve_AcctJrnl_Data($profileHandle, $dataBaseID, $coNumber, $facNumber, $accountNumber, $subAccount, $fromPer, $toPer, $DDUNPS);
$acctName      = $returnValue['acctName'];
$coFacName     = $returnValue['coFacName'];
$balanceIncome = $returnValue['balanceIncome'];
$currencyUnit  = $returnValue['currencyUnit'];
$currencyType  = $returnValue['currencyType'];
$beginBal      = $returnValue['beginBal'];

$F_CoFac=Format_CoFac($coNumber, $facNumber,"N");
$F_AcctSub=Format_Acct($accountNumber, $subAccount,"N");

require 'SetRowClass.php';

print "\n <tr class=\"$rowClass\"> ";
print "\n     <td class=\"colnmbr\">$F_CoFac</td> ";
print "\n     <td class=\"colalph\">$coFacName</td> ";
print "\n     <td class=\"colnmbr\">$F_AcctSub</td> ";
print "\n     <td class=\"colalph\">$acctName</td> ";
print "\n     <td class=\"colalph\">$balanceIncome</td> ";
print "\n     <td class=\"colalph\">$currencyUnit </td> ";
if ($HDMCRL>0) {print "\n <td class=\"colalph\">$currencyType </td> ";}
print "\n     <td class=\"colnmbr\">$accountAmount</td> ";
if ($columnValue=="") {
	$F_beginBal=Format_Nbr($beginBal,  "2", $amtEditCode, "Y", "", "");
	print "\n     <td class=\"colnmbr\">$F_beginBal</td> ";
}
print "\n </tr> ";
print "\n </table> ";

// Accounts Payable ****************************************
if ($view_ap>0) {
	require 'stmtSQLClear.php';
	$appendUserView="N";
	$appendWildCard="N";
	$stmtSQL .= " SELECT APDDSQ,APDDFL,APRPTS,APJRNL,APAMT,APBANK,APVOU# as APVOU ";
	$stmtSQL .= "       ,APLINE,APVEND,APCHK# as APCHK,APCHKD,APCKCD,APINV# as APINV ";
	$stmtSQL .= "       ,APINVD,APMEMO,APPO# as APPO,APDSTD ";
	$stmtSQL .= "       ,VMVNA1,VMVNA1U ";
	$stmtSQL .= "       ,BKBKNM";
	$stmtSQL .= "       ,a.FLDESC as srcFLDESC ";
	$stmtSQL .= "       ,b.FLDESC as pmtFLDESC ";
	$fileSQL=$ap_fileSQL;
	$selectSQL=$ap_selectSQL;
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By $ap_orderBy ";
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($formatToPrint != "Y") {
		print "\n <table $contentTable> ";
		print "\n     <tr><td><a href=\"#top\">$topOfFormImage</a></td></tr> ";
		print "\n </table> ";
	}

	print "\n <a name=\"accountspayable\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Accounts Payable</legend> ";

	print "\n <table $contentTable> ";
	$headOnce="";

	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult)){

		if ($headOnce == "") {
			$headOnce="Y";
			print "\n <tr> ";
			$orderByVar="{$scriptVarBase}{$searchVarBase}";
			$orderBy=$ap_orderBy;
			$returnValue=OrderBy_Sort("APAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount\">{$sortPoint}Amount</a></th> ";
			$returnValue=OrderBy_Sort("APRPTS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=Source\" title=\"Sequence By Source\">{$sortPoint}Source</a></th> ";
			$returnValue=OrderBy_Sort("APINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=Invoice\" title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th> ";
			$returnValue=OrderBy_Sort("APINVD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=InvDate\" title=\"Sequence By Invoice Date\">{$sortPoint}Invoice Date</a></th> ";
			$returnValue=OrderBy_Sort("VMVNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=VendorName\" title=\"Sequence By Vendor Name\">{$sortPoint}Vendor Name</a></th> ";
			$returnValue=OrderBy_Sort("APDSTD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=DistPd\" title=\"Sequence By Distribution Period\">{$sortPoint}Dist Period</a></th> ";
			$returnValue=OrderBy_Sort("APPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=PONumber\" title=\"Sequence By Purchase Order\">{$sortPoint}Purchase Order</a></th> ";
			$returnValue=OrderBy_Sort("APMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=Memo\" title=\"Sequence By Memo\">{$sortPoint}Memo</a></th> ";
			$returnValue=OrderBy_Sort("APVOU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=Voucher\" title=\"Sequence By Voucher\">{$sortPoint}Voucher</a></th> ";
			$returnValue=OrderBy_Sort("APBANK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=Bank\" title=\"Sequence By Bank\">{$sortPoint}Bank</a></th> ";
			$returnValue=OrderBy_Sort("APCHK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=CheckNumber\" title=\"Sequence By Check\">{$sortPoint}Check</a></th> ";
			$returnValue=OrderBy_Sort("APCHKD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=CheckDate\" title=\"Sequence By Check Date\">{$sortPoint}Check Date</a></th> ";
			$returnValue=OrderBy_Sort("APCKCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AP_ORDERBY&amp;sequence=CheckCode\" title=\"Sequence By Check Code\">{$sortPoint}Check Code</a></th> ";
			print "\n </tr> ";
		}

		require 'SetRowClass.php';

		$F_APDSTD=PeriodFromCYP($row['APDSTD']);
		$F_APAMT=Format_Nbr($row['APAMT'], "2", $amtEditCode, "Y", "", "");
		$F_APINVD=Format_Date($row['APINVD'], "D");
		$F_APCHKD=Format_Date($row['APCHKD'], "D");

		print "\n <tr class=\"$rowClass\"> ";
		if     ($row['APDDSQ']>0 && $row['APDDFL'] == "APDIST") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDApDist.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;vendorNumber=" . urlencode(trim($row['APVEND'])) . "&amp;bankNumber=" . urlencode(trim($row['APBANK'])) . "&amp;voucherNumber=" . urlencode(trim($row['APVOU'])) . "&amp;lineNumber=" . urlencode(trim($row['APLINE'])) . "&amp;checkNumber=" . urlencode(trim($row['APCHK'])) . "&amp;checkDate=" . urlencode(trim($row['APCHKD'])) . "&amp;checkCode=" . urlencode(trim($row['APCKCD'])) . "&amp;glDDSeq=" . urlencode(trim($row['APDDSQ'])) . "\" title=\"View A/P Invoice Detail - Distribution\">$F_APAMT</a></td> ";}
		elseif ($row['APDDSQ']>0 && $row['APDDFL'] == "APPAID") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDApPymt.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;vendorNumber=" . urlencode(trim($row['APVEND'])) . "&amp;bankNumber=" . urlencode(trim($row['APBANK'])) . "&amp;voucherNumber=" . urlencode(trim($row['APVOU'])) . "&amp;lineNumber=" . urlencode(trim($row['APLINE'])) . "&amp;checkNumber=" . urlencode(trim($row['APCHK'])) . "&amp;checkDate=" . urlencode(trim($row['APCHKD'])) . "&amp;checkCode=" . urlencode(trim($row['APCKCD'])) . "&amp;glDDSeq=" . urlencode(trim($row['APDDSQ'])) . "\" title=\"View A/P Invoice Detail - Payments\">$F_APAMT</a></td> ";}
		else                                                    {print "\n <td class=\"colnmbr\">$F_APAMT</td> ";}

		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[srcFLDESC]\">$row[APRPTS]</span></td> ";
		print "\n <td class=\"colalph\">$row[APINV]</td> ";
		print "\n <td class=\"coldate\">$F_APINVD</td> ";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}VendorInquiry.d2w/DISPLAY{$altVarBase}&amp;vendorNumber=" . urlencode(trim($row['APVEND'])) . "\" onclick=\"$inquiryWinVar\" title=\"Vendor Quickview\">$row[VMVNA1]</a></td> ";
		print "\n <td class=\"colnmbr\">$F_APDSTD</td> ";
		$recordsHave=RetValue("(PHRTOV,PHPO)=($row[APVEND],$row[APPO])", "POPOHH", "Char(Count(*))");
		if ($recordsHave>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=215&amp;fKey1=PHRTOV&amp;fVal1=" . urlencode(trim($row['APVEND'])) . "&amp;fKey2=PHPO&amp;fVal2=" . urlencode(trim($row['APPO'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Vendor Order History\">$row[APPO]</a></td> ";}
		else                {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}

		print "\n <td class=\"colalph\">$row[APMEMO]</td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}APInvoice.d2w/ENTRY{$altVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;vendorNumber=" . urlencode(trim($row['APVEND'])) . "&amp;voucherNumber=" . urlencode(trim($row['APVOU'])) . "&amp;ddReport=" . urlencode(trim($ddReport)) . "&amp;glJrnl=" . urlencode(trim($row['APJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($row['APDDSQ'])) . "&amp;ddDescr=" . urlencode(trim($ddDescr)) . "&amp;ddCompany=" . urlencode(trim($ddCompany)) . "&amp;ddFacility=" . urlencode(trim($ddFacility)) . "&amp;noMenu=Y\" title=\"View A/P Invoice\">$row[APVOU]</a></td> ";
		if ($row['APBANK'] != 0) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[BKBKNM]\">$row[APBANK]</span></td> ";}
		else                     {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}

		if ($row['APCHK'] != 0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}APCheckInquiry.d2w/ENTRY{$altVarBase}&amp;checkNumber=" . urlencode(trim($row['APCHK'])) . "&amp;checkDate=" . urlencode(trim($row['APCHKD'])) . "&amp;bankNumber=" . urlencode(trim($row['APBANK'])) . "\" onclick=\"$drillDownWinVar\" title=\"A/P Check Quickview\">$row[APCHK]</a></td> ";}
		else                    {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}

		print "\n <td class=\"coldate\">$F_APCHKD</td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[pmtFLDESC]\">$row[APCKCD]</span></td> ";
		print "\n </tr> ";
		$startRow ++;
		$rowCount ++;
	}
	print "\n </table> ";
	print "\n </fieldset> ";
}

// Accounts Receivable ****************************************
if ($view_ar>0) {
	$DifCurCount=RetValue("($ar_selectSQL) and ARCURT<>ARCURD and ARFAMT<>0", $ar_fileSQL, "Char(Count(*))");

	require 'stmtSQLClear.php';
	$appendUserView="N";
	$appendWildCard="N";
	$stmtSQL .= " SELECT ARDDSQ,ARDDFL,ARRPTS,ARJRNL,ARSBCD,ARCHK,ARPAYR ";
	$stmtSQL .= "       ,ARDTPD,ARBANK,ARAINV,ARIVDT,ARDUED,ARPAYR,ARBLTO ";
	$stmtSQL .= "       ,ARCUST,ARAMT ,ARLOC ,ARPLT ,ARMORD,ARORD ,ARSLSM ";
	$stmtSQL .= "       ,ARISEQ,ARPSEQ ";
	if ($HDMCRL>0 && $CRPRMC=="Y") {$stmtSQL .= "       ,ARFAMT,ARCURT,ARCURD,ARDSTP,ARCRTE,AROPER ";}
	$stmtSQL .= "       ,CMCNA1,CMCNA1U,BKBKNM,FLDESC,PSDESC,LOLNA1,SMSNA1 ";
	$stmtSQL .= "       ,Coalesce(PYPYNM,' ') as PYPYNM ";
	$stmtSQL .= "       ,Coalesce(YPCMNT,' ') as YPCMNT,Case When YPCMNT<>' ' Then 'Y' Else ' ' End HAS_YPCMNT ";
	$stmtSQL .= "       ,Coalesce(YPMEMO,' ') as YPMEMO,Coalesce(YPBCH,0) as YPBCH ";
	$stmtSQL .= "       ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
	if ($HDOERL<=0) {
		$stmtSQL .= ",0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
	} else {
		$stmtSQL .= ",(Select Count(*) From OEIVHH Where (HIAIV#,HIIVDT,HIBLTO)=(ARAINV,ARIVDT,ARBLTO) and IVIVCD='C') as OEINVCOUNT " ;
		$stmtSQL .= ",(Select Count(*) From OEORHH Where HHLIV#=ARAINV and HHBLTO=ARBLTO) as OEHISTORY " ;
		$stmtSQL .= ",(Select Count(*) From OEORHH Where ARORD<>0 and HHORD#=ARORD and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
	}
	if ($HDPDRL<=0) {
		$stmtSQL .= ",0 as MFGORDCOUNT " ;
	} else {
		$stmtSQL .= ",(Select Count(*) From HDMOHM Where (OHPLT,OHORD)=(ARPLT,ARMORD)) as MFGORDCOUNT " ;
	}
	$fileSQL=$ar_fileSQL;
	$selectSQL=$ar_selectSQL;
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By $ar_orderBy ";
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($formatToPrint != "Y") {
		print "\n <table $contentTable> ";
		print "\n     <tr><td><a href=\"#top\">$topOfFormImage</a></td></tr> ";
		print "\n </table> ";
	}

	print "\n <a name=\"accountsreceivable\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Accounts Receivable</legend> ";

	print "\n <table $contentTable> ";
	$hdrOnce="";

	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult)){
		if ($hdrOnce == "") {
			$hdrOnce="Y";
			print "\n <tr> ";
			$orderByVar="{$scriptVarBase}{$searchVarBase}";
			$orderBy=$ar_orderBy;
			$returnValue=OrderBy_Sort("ARAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount\">{$sortPoint}Amount</a></th> ";
			$returnValue=OrderBy_Sort("ARRPTS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Source\" title=\"Sequence By Source\">{$sortPoint}Source</a></th> ";
			$returnValue=OrderBy_Sort("ARSBCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=PmtCode\" title=\"Sequence By Payment Code\">{$sortPoint}Payment Code</a></th> ";
			$returnValue=OrderBy_Sort("ARAINV"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Invoice\" title=\"Sequence By Invoice\">{$sortPoint}Invoice</a></th> ";
			$returnValue=OrderBy_Sort("ARIVDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=InvDate\" title=\"Sequence By Invoice Date\">{$sortPoint}Invoice Date</a></th> ";
			$returnValue=OrderBy_Sort("ARDUED"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=DueDate\" title=\"Sequence By Due Date\">{$sortPoint}Due Date</a></th> ";
			$returnValue=OrderBy_Sort("CMCNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=CustomerName\" title=\"Sequence By Customer Name\">{$sortPoint}Customer Name</a></th> ";
			$returnValue=OrderBy_Sort("ARDTPD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=DatePaid\" title=\"Sequence By Date Paid\">{$sortPoint}Date Paid</a></th> ";
			$returnValue=OrderBy_Sort("ARCHK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=CheckNumber\" title=\"Sequence By Document\">{$sortPoint}Document</a></th> ";
			$returnValue=OrderBy_Sort("HAS_YPCMNT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Comment\" title=\"Sequence By Comment\">{$sortPoint}Cmt</a></th> ";
			$returnValue=OrderBy_Sort("YPMEMO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Memo\" title=\"Sequence By Memo\">{$sortPoint}Memo</a></th> ";
			$returnValue=OrderBy_Sort("ARLOC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Location\" title=\"Sequence By Location\">{$sortPoint}Loc</a></th> ";
			$returnValue=OrderBy_Sort("ARSLSM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Salesman\" title=\"Sequence By Salesman\">{$sortPoint}Salesman</a></th> ";
			$returnValue=OrderBy_Sort("ARPAYR"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Payer\" title=\"Sequence By Payer\">{$sortPoint}Payer</a></th> ";
			$returnValue=OrderBy_Sort("ARBANK"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Bank\" title=\"Sequence By Bank\">{$sortPoint}Bank</a></th> ";
			$returnValue=OrderBy_Sort("YPBCH"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=Batch\" title=\"Sequence By Batch\">{$sortPoint}Batch</a></th> ";
			$returnValue=OrderBy_Sort("ARORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=OrderNumber\" title=\"Sequence By Order Number\">{$sortPoint}Order Number</a></th> ";
			$returnValue=OrderBy_Sort("ARMORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=MfgOrder\" title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th> ";
			if ($HDMCRL>0 && $CRPRMC=="Y" && $DifCurCount>0) {
				$returnValue=OrderBy_Sort("ARFAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
				print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=AR_ORDERBY&amp;sequence=ForeignAmt\" title=\"Sequence By Foreign Amount\">{$sortPoint}Foreign Amount</a></th> ";
			}
			print "\n </tr> ";
		}

		require 'SetRowClass.php';

		$F_ARAMT=Format_Nbr($row['ARAMT'], "2", $amtEditCode, "Y", "", "");
		$F_ARIVDT=Format_Date($row['ARIVDT'], "D");
		$F_ARDUED=Format_Date_ISO($row['ARDUED'], "D");
		$F_ARDTPD=Format_Date($row['ARDTPD'], "D");
		if ($HDMCRL>0 && $CRPRMC=="Y" && $DifCurCount>0 && $row['ARFAMT']!=0 && $row['ARCURT']!=$row['ARCURD']) {
			$F_ARFAMT=Format_Nbr($row['ARFAMT'],  "2", $amtEditCode, "Y", "", "");
			$F_DomHover=Format_Domestic_Hover_Info($row['ARCURT'], $row['ARCURD'], $row['ARDSTP'], $row['AROPER'], $row['ARCRTE']);
		}

		print "\n <tr class=\"$rowClass\"> ";
		if     ($row['ARDDSQ'] != 0 && $row['ARDDFL']=="HDARDS") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}GLDDArDist.php{$scriptVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($row['ARJRNL'])) . "&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;locationNumber=" . urlencode(trim($row['ARLOC'])) . "&amp;glDDSeq=" . urlencode(trim($row['ARDDSQ'])) . "&amp;glDDFile=" . urlencode(trim($row['ARDDFL'])) . "&amp;invoiceSequence=" . urlencode(trim($row['ARISEQ'])) . "&amp;paymentSequence=" . urlencode(trim($row['ARPSEQ'])) . "\" title=\"View Invoice Detail - Distribution\">$F_ARAMT</a></td> ";}
		elseif ($row['ARDDSQ'] != 0 && $row['ARDDFL']=="HDINVC") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}GLDDArDist.php{$scriptVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($row['ARJRNL'])) . "&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;locationNumber=" . urlencode(trim($row['ARLOC'])) . "&amp;glDDSeq=" . urlencode(trim($row['ARDDSQ'])) . "&amp;glDDFile=" . urlencode(trim($row['ARDDFL'])) . "&amp;invoiceSequence=" . urlencode(trim($row['ARISEQ'])) . "&amp;paymentSequence=" . urlencode(trim($row['ARPSEQ'])) . "\" title=\"View Invoice Detail - Revaluation\">$F_ARAMT</a></td> ";}
		elseif ($row['ARDDSQ'] != 0 && $row['ARDDFL']=="ARYPTD") {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}GLDDArPymt.php{$scriptVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($row['ARJRNL'])) . "&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;locationNumber=" . urlencode(trim($row['ARLOC'])) . "&amp;glDDSeq=" . urlencode(trim($row['ARDDSQ'])) . "&amp;invoiceSequence=" . urlencode(trim($row['ARISEQ'])) . "&amp;paymentSequence=" . urlencode(trim($row['ARPSEQ'])) . "\" title=\"View Invoice Detail - Payment\">$F_ARAMT</a></td> ";}
		else                                                     {print "\n <td class=\"colnmbr\">$F_ARAMT</td> ";}

		print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row['FLDESC']) . "\">$row[ARRPTS]</span></td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[ARSBCD]\">$row[PSDESC]</span></td> ";
		if ($row['OEINVCOUNT']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectInvoice.d2w/DISPLAY{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;invoiceNumber=" . urlencode(trim($row['ARAINV'])) . "&amp;invoiceDate=" . urlencode(trim($row['ARIVDT'])) . "&amp;formatToPrint=Y\" onclick=\"$invoiceWinVar\" title=\"Invoice Quickview\">$row[ARAINV]</a></td> ";}
		else                      {print "\n <td class=\"colnmbr\">$row[ARAINV]</td> ";}
		print "\n <td class=\"coldate\"><a href=\"{$homeURL}{$phpPath}ARInvoiceInquiry.php{$scriptVarBase}{$glDDVarBase}&amp;tag=REPORT&amp;backHome=" . urlencode(trim($backHome)) . "&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;invoiceSequence=" . urlencode(trim($row['ARISEQ'])) . "&amp;glJrnl=" . urlencode(trim($row['ARJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($row['ARDDSQ'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View A/R Invoice\">$F_ARIVDT</a></td> ";
		print "\n <td class=\"coldate\">$F_ARDUED</td> ";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}CustomerSelect.d2w/REPORT{$altVarBase}&amp;customerNumber=" . urlencode(trim($row['ARBLTO'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Customer [$row[ARBLTO]]\">$row[CMCNA1]</a></td> ";
		print "\n <td class=\"coldate\">$F_ARDTPD</td> ";
		if (trim($row['ARCHK']) != "" && $row['ARBANK']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}ARCheckInquiry.php{$scriptVarBase}&amp;tag=REPORT&amp;fromDocument=" . urlencode(trim($row['ARCHK'])) . "&amp;fromDatePaid=" . urlencode(trim($row['ARDTPD'])) . "&amp;fromBank=" . urlencode(trim($row['ARBANK'])) . "&amp;fromPayer=" . urlencode(trim($row['ARPAYR'])) . "&amp;fromCustomer=" . urlencode(trim($row['ARBLTO'])) . "\" onclick=\"$drillDownWinVar\" title=\"A/R Document Quickview\">$row[ARCHK]</a></td> ";}
		else                                               {print "\n <td class=\"colalph\">$row[ARCHK]</td> ";}
		print "\n <td class=\"colcode\" $helpCursor><span title=\"$row[YPCMNT]\">$row[HAS_YPCMNT]</span></td> ";
		print "\n <td class=\"colalph\">$row[YPMEMO]</td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}LocationSelect.d2w/REPORT{$altVarBase}&amp;locationNumber=" . urlencode(trim($row['ARLOC'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Location [$row[LOLNA1]]\">$row[ARLOC]</a></td> ";
		print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[SMSNA1]\">$row[ARSLSM]</span></td> ";
		print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[PYPYNM]\">$row[ARPAYR]</span></td> ";
		if ($row['ARBANK'] != 0) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[BKBKNM]\">$row[ARBANK]</span></td> ";}
		else                     {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
		if ($row[YPBCH]>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}ApplCashBatchSelect.php{$scriptVarBase}&amp;fromBatchNumber=" . urlencode(trim($row['YPBCH'])) . "&amp;fromBatchDate=" . urlencode(trim($row['ARDTPD'])) . "&amp;fromBatchBank=" . urlencode(trim($row['ARBANK'])) . "&amp;noMenu=Y\" onclick=\"$searchWinVar\" title=\"View Batch\">$row[YPBCH]</a></td> ";}
		else               {print "\n <td class=\"colnmbr\">$row[YPBCH]</td> ";}
		if     ($row['OESELECT']>0)  {print "\n     <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}SelectOrderHistory.d2w/REPORT{$altVarBase}&amp;orderNumber=" . urlencode(trim($row['ARORD'])) . "&amp;orderSequence=" . urlencode(trim($row['MAX_HHSEQ'])) . "&amp;noMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Shipment\">$row[ARORD]</a></td> ";}
		elseif ($row['OEHISTORY']>0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$phpPath}hdList.php{$scriptVarBase}&amp;tblID=205&amp;fKey1=HHBLTO&amp;fVal1=" . urlencode(trim($row['ARBLTO'])) . "&amp;fKey2=HHLIV&amp;fVal2=" . urlencode(trim($row['ARAINV'])) . "&amp;nMenu=Y\" onclick=\"$drillDownWinVar\" title=\"View Customer Order History\">$row[ARORD]</a></td> ";}
		else                         {print "\n <td class=\"colnmbr\">$row[ARORD]</td> ";}
		if ($row['MFGORDCOUNT']>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['ARPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['ARMORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[ARMORD]</a></td> ";}
		else                       {print "\n <td class=\"colalph\">$row[ARMORD]</td> ";}
		if ($HDMCRL>0 && $CRPRMC=="Y" && $DifCurCount>0) {
			if ($row['ARFAMT']!=0 && $row['ARCURT']!=$row['ARCURD']) {print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$F_DomHover\">$F_ARFAMT</span></td> ";}
			else                                                     {print "\n <td class=\"colnmbr\">&nbsp;</td> ";}
		}

		print "\n </tr> ";
		$startRow ++;
		$rowCount ++;
	}
	print "\n </table> ";
	print "\n </fieldset> ";
}

// Fixed Assets ****************************************
if ($view_fa>0) {
	require 'stmtSQLClear.php';
	$appendUserView="N";
	$appendWildCard="N";
	$stmtSQL .= " SELECT GFPER# as GFPER,GFDDSQ,GFDDFL,GFRPTS,GFJRNL,GFAMT ";
	$stmtSQL .= "        ,GFSITE,GFASST,GFSCHD,GFDESC,GFPROP,GFFMCD,GFFPCD ";
	$stmtSQL .= "        ,GFRTDT,GFRRES,GFTRFG,GFTFPD,GFTSIT,GFTAST,GFBDDT ";
	$stmtSQL .= "        ,GFLFYY,GFLFMM,GFDPCD ";
	$stmtSQL .= "        ,FLDESC ";
	$fileSQL=$fa_fileSQL;
	$selectSQL=$fa_selectSQL;
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By  $fa_orderBy ";
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($formatToPrint != "Y") {
		print "\n <table $contentTable> ";
		print "\n     <tr><td><a href=\"#top\">$topOfFormImage</a></td></tr> ";
		print "\n </table> ";
	}

	print "\n <a name=\"fixedassets\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Fixed Assets</legend> ";

	print "\n <table $contentTable> ";
	$headOnce="";

	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult)){
		if ($headOnce == "") {
			$headOnce="Y";
			print "\n <tr> ";
			$orderByVar="{$scriptVarBase}{$searchVarBase}";
			$orderBy=$fa_orderBy;
			$returnValue=OrderBy_Sort("GFAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount, Asset, Site\">{$sortPoint}Amount</a></th> ";
			$returnValue=OrderBy_Sort("GFRPTS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=TransType\" title=\"Sequence By Transaction Type, Asset, Site\">{$sortPoint}Trans Type</a></th> ";
			$returnValue=OrderBy_Sort("GFASST"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=Asset\" title=\"Sequence By Asset, Site\">{$sortPoint}Asset</a></th> ";
			$returnValue=OrderBy_Sort("GFDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=AssetDescr\" title=\"Sequence By Asset Description, Asset, Site\">{$sortPoint}Asset Description</a></th> ";
			$returnValue=OrderBy_Sort("GFPROP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=PropType\" title=\"Sequence By Property Type, Asset, Site\">{$sortPoint}Property Type</a></th> ";
			$returnValue=OrderBy_Sort("GFBDDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=BeginDeprDate\" title=\"Sequence By Begin Depr Date, Asset, Site\">{$sortPoint}Begin Depr Date</a></th> ";
			$returnValue=OrderBy_Sort("GFLFYY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=Life\" title=\"Sequence By Life, Asset, Site\">{$sortPoint}Life</a></th> ";
			$returnValue=OrderBy_Sort("GFDPCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=DeprMeth\" title=\"Sequence By Depreciation Method, Asset, Site\">{$sortPoint}Depr Meth</a></th> ";
			$returnValue=OrderBy_Sort("GFPER"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=DeprPeriod\" title=\"Sequence By Depreciation Period, Asset, Site\">{$sortPoint}Depr Period</a></th> ";
			$returnValue=OrderBy_Sort("GFSITE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=SiteName\" title=\"Sequence By Site, Asset \">{$sortPoint}Site</a></th> ";
			$returnValue=OrderBy_Sort("GFSCHD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=Schedule\" title=\"Sequence By Schedule, Asset, Site\">{$sortPoint}Sched</a></th> ";
			$returnValue=OrderBy_Sort("GFFPCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=RetireCode\" title=\"Sequence By Retirement Code, Asset, Site\">{$sortPoint}Retire Code</a></th> ";
			$returnValue=OrderBy_Sort("GFRTDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=RetireDate\" title=\"Sequence By Retirement Date, Asset, Site\">{$sortPoint}Retirement Date</a></th> ";
			$returnValue=OrderBy_Sort("GFRRES"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=RetireReason\" title=\"Sequence By Retirement Reason, Asset, Site\">{$sortPoint}Retirement Reason</a></th> ";
			$returnValue=OrderBy_Sort("GFFPCD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=TrfCode\" title=\"Sequence By Transfer Code, Asset, Site\">{$sortPoint}Transfer Code</a></th> ";
			$returnValue=OrderBy_Sort("GFRTDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=TrfDate\" title=\"Sequence By Transfer Date, Asset, Site\">{$sortPoint}Transfer Date</a></th> ";
			$returnValue=OrderBy_Sort("GFTSIT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=TrfToAsset\" title=\"Sequence By Transfer To Asset\">{$sortPoint}Transfer To Asset</a></th> ";
			$returnValue=OrderBy_Sort("GFTFPD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=TrfPeriod\" title=\"Sequence By Transfer Period, Asset, Site\">{$sortPoint}Transfer Period</a></th> ";
			$returnValue=OrderBy_Sort("GFTSIT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=FA_ORDERBY&amp;sequence=TrfFrAsset\" title=\"Sequence By Transfer From Asset\">{$sortPoint}Transfer From Asset</a></th> ";
			print "\n </tr> ";
		}

		require 'SetRowClass.php';

		$F_GFTFPD=PeriodFromCYP($row['GFTFPD']);
		$F_GFPER=PeriodFromCYP($row['GFPER']);
		$F_GFAMT=Format_Nbr($row['GFAMT'], "2", $amtEditCode, "Y", "", "");
		$F_GFRTDT=Format_Date($row['GFRTDT'], "D");
		$F_GFBDDT=Format_Date($row['GFBDDT'], "D");

		print "\n <tr class=\"$rowClass\"> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDFAAssetDetail.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;siteNumber=" . urlencode(trim($row['GFSITE'])) . "&amp;assetNumber=" . urlencode(trim($row['GFASST'])) . "&amp;scheduleNumber=" . urlencode(trim($row['GFSCHD'])) . "&amp;ddRptRef=" . urlencode(trim($row['GFRPTS'])) . "&amp;deprPeriod=" . urlencode(trim($row['GFPER'])) . "&amp;glJrnl=" . urlencode(trim($row['GFJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($row['GFDDSQ'])) . "\" title=\"View Asset Drill Down History For Transaction\">$F_GFAMT</a></td> ";
		print "\n <td class=\"colcode\" $helpCursor><span title=\"" . trim($row['FLDESC']) . "\">$row[GFRPTS]</span></td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDFAAssetDetail.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;siteNumber=" . urlencode(trim($row['GFSITE'])) . "&amp;assetNumber=" . urlencode(trim($row['GFASST'])) . "&amp;scheduleNumber=" . urlencode(trim($row['GFSCHD'])) . "&amp;glJrnl=" . urlencode(trim($row['GFJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($row['GFDDSQ'])) . "\" title=\"View Asset Drill Down History\">$row[GFASST]</a></td> ";
		$recordsHave=RetValue("(AMSITE,AMASST)=($row[GFSITE],$row[GFASST])", "FAMSTR", "Char(Count(*))");
		if ($recordsHave>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}FAAssetSelect.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;backHome=" . urlencode(trim($backHome)) . "&amp;siteNumber=" . urlencode(trim($row['GFSITE'])) . "&amp;assetNumber=" . urlencode(trim($row['GFASST'])) . "&amp;scheduleNumber=" . urlencode(trim($row['GFSCHD'])) . "&amp;glJrnl=" . urlencode(trim($row['GFJRNL'])) . "&amp;glDDSeq=" . urlencode(trim($row['GFDDSQ'])) . "\" title=\"View Asset\">$row[GFDESC]</a></td> ";}
		else                {print "\n <td class=\"colalph\">$row[GFDESC]</td> ";}

		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}FAPropertyInquiry.d2w/DISPLAY{$altVarBase}&amp;propertyType=" . urlencode(trim($row['GFPROP'])) . "\" onclick=\"$inquiryWinVar\" title=\"Property Type Quickview\">$row[GFPROP]</a></td> ";
		print "\n <td class=\"coldate\">$F_GFBDDT</td> ";
		print "\n <td class=\"colnmbr\">$row[GFLFYY]/$row[GFLFMM]</td> ";
		print "\n <td class=\"colcode\"><a href=\"{$homeURL}{$cGIPath}FADeprMethodInquiry.d2w/DISPLAY{$altVarBase}&amp;deprMethod=" . urlencode(trim($row['GFDPCD'])) . "\" onclick=\"$inquiryWinVar\" title=\"Depreciation Method Quickview\">$row[GFDPCD]</a></td> ";
		print "\n <td class=\"colnmbr\">$F_GFPER</td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}FASiteInquiry.d2w/DISPLAY{$altVarBase}&amp;siteNumber=" . urlencode(trim($row['GFSITE'])) . "\" onclick=\"$inquiryWinVar\" title=\"Site Quickview\">$row[GFSITE]</a></td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}FAScheduleInquiry.d2w/DISPLAY{$altVarBase}&amp;scheduleNumber=" . urlencode(trim($row['GFSCHD'])) . "\" onclick=\"$inquiryWinVar\" title=\"Schedule Quickview\">$row[GFSCHD]</a></td> ";
		if ($row['GFRPTS']=="RET") {
			print "\n <td class=\"colcode\">$row[GFFPCD]</td> ";
			print "\n <td class=\"coldate\">$F_GFRTDT</td> ";
			print "\n <td class=\"colalph\">$row[GFRRES]</td> ";
		} else {
			print "\n <td class=\"colalph\">&nbsp;</td> ";
			print "\n <td class=\"colalph\">&nbsp;</td> ";
			print "\n <td class=\"colalph\">&nbsp;</td> ";
		}
		if ($row['GFRPTS']=="TRF" && $row['GFTRFG'] == "F") {
			print "\n <td class=\"colcode\">$row[GFFPCD]</td> ";
			print "\n <td class=\"coldate\">$F_GFRTDT</td> ";
			print "\n <td class=\"colnmbr\">$row[GFTSIT]-$row[GFTAST]</td> ";
		} else {
			print "\n <td class=\"colalph\">&nbsp;</td> ";
			print "\n <td class=\"colalph\">&nbsp;</td> ";
			print "\n <td class=\"colalph\">&nbsp;</td> ";
		}
		if ($row['GFRPTS']=="TRF") {print "\n <td class=\"colnmbr\">$F_GFTFPD</td> ";}
		else                       {print "\n <td class=\"colalph\">&nbsp;</td> ";}

		if ($row['GFRPTS']=="TRF" && $row['GFTRFG'] == "T") {print "\n <td class=\"colnmbr\">$row[GFTSIT]-$row[GFTAST]</td> ";}
		else                                                {print "\n <td class=\"colalph\">&nbsp;</td> ";}

		print "\n </tr> ";
		$startRow ++;
		$rowCount ++;
	}
	print "\n </table> ";
	print "\n </fieldset> ";
}

// Inventory **************************************************
if ($view_iv>0) {
	require 'stmtSQLClear.php';
	$appendUserView="N";
	$appendWildCard="N";
	$stmtSQL .= " SELECT IVAMT ,IVQTY ,IVDSEQ,IVOSEQ,IVIVTT,IVTRDT,IVWHS,IVPCLS ";
	$stmtSQL .= "       ,IVITC ,IVITEM,IVPLT ,IVMORD ";
	$stmtSQL .= "       ,IMIMDS,TTDESC,WHWHNM,PLNAME,PCPCDS,ITDESC ";
	$fileSQL=$iv_fileSQL;
	$selectSQL=$iv_selectSQL;
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By $iv_orderBy ";
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($formatToPrint != "Y") {
		print "\n <table $contentTable> ";
		print "\n     <tr><td><a href=\"#top\">$topOfFormImage</a></td></tr> ";
		print "\n </table> ";
	}

	print "\n <a name=\"inventory\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Inventory</legend> ";

	print "\n <table $contentTable> ";
	$hdrOnce="";

	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult)){

		if ($hdrOnce=="") {
			$hdrOnce="Y";
			print "\n <tr> ";
			$orderByVar="{$scriptVarBase}{$searchVarBase}";
			$orderBy=$iv_orderBy;
			$returnValue=OrderBy_Sort("IVAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount\">{$sortPoint}Amount</a></th> ";
			$returnValue=OrderBy_Sort("IVTRDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=TransDate\" title=\"Sequence By Transaction Date\">{$sortPoint}Trans Date</a></th> ";
			$returnValue=OrderBy_Sort("IVIVTT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=TransType\" title=\"Sequence By Transaction Type\">{$sortPoint}Trans Type</a></th> ";
			$returnValue=OrderBy_Sort("IVITEM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=ItemNumber\" title=\"Sequence By Item Number\">{$sortPoint}Item Number</a></th> ";
			$returnValue=OrderBy_Sort("IMIMDS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=ItemDesc\" title=\"Sequence By Description\">{$sortPoint}Description</a></th> ";
			$returnValue=OrderBy_Sort("IVWHS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=Warehouse\" title=\"Sequence By Warehouse\">{$sortPoint}Whs</a></th> ";
			$returnValue=OrderBy_Sort("IVPLT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=Plant\" title=\"Sequence By Plant\">{$sortPoint}Plant</a></th> ";
			$returnValue=OrderBy_Sort("IVMORD"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=MfgOrder\" title=\"Sequence By Mfg Order\">{$sortPoint}Mfg Order</a></th> ";
			$returnValue=OrderBy_Sort("IVQTY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=Quantity\" title=\"Sequence By Quantity\">{$sortPoint}Quantity</a></th> ";
			$returnValue=OrderBy_Sort("IVPCLS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=ProdClass\" title=\"Sequence By Product Class\">{$sortPoint}Prod Class</a></th> ";
			$returnValue=OrderBy_Sort("IVITC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=IV_ORDERBY&amp;sequence=InvType\" title=\"Sequence By Inventory Type\">{$sortPoint}Inv Type</a></th> ";
			print "\n </tr> ";
		}

		require 'SetRowClass.php';

		$F_IVAMT=Format_Nbr($row['IVAMT'], "2", $amtEditCode, "Y", "", "");
		$F_IVQTY=Format_Nbr($row['IVQTY'], $qtyNbrDec, $qtyEditCode, "Y", "", "");
		$F_IVTRDT=Format_Date($row['IVTRDT'], "D");

		print "\n <tr class=\"$rowClass\"> ";
		if ($row['IVOSEQ'] != 0) {print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDIvOrigTrans.d2w/ENTRY{$altVarBase}&amp;origTransSequence=" . urlencode(trim($row['IVOSEQ'])) . "\" title=\"View Transaction\">$F_IVAMT</a></td> ";}
		else                     {print "\n <td class=\"colnmbr\">$F_IVAMT</td> ";}

		print "\n <td class=\"coldate\">$F_IVTRDT</td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[TTDESC]\">$row[IVIVTT]</span></td> ";
		if (trim($row['IMIMDS']) != "") {
			print "\n <td class=\"colalph\">$row[IVITEM]</td> ";
			print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemInquiry.d2w/DISPLAY{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['IVITEM'])) . "\" onclick=\"$inquiryWinVar\" title=\"Item Quickview\">$row[IMIMDS]</a></td> ";
		} else {
			print "\n <td class=\"colalph\">$row[IVITEM]</td> ";
			print "\n <td class=\"colalph\">&nbsp;</td> ";
		}
		print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[WHWHNM]\">$row[IVWHS]</span></td> ";
		print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[PLNAME]\">$row[IVPLT]</span></td> ";

		if (trim($row['IVMORD']) != "") {$mfgOrderCnt=RetValue("(OHPLT,OHORD)=($row[IVPLT],'$row[IVMORD]')", "HDMOHM", "CHAR(COUNT(OHORD))");}
		else                            {$mfgOrderCnt=0;}
		if ($mfgOrderCnt>0) {print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}SelectMfgOrder.d2w/REPORT{$altVarBase}&amp;plantNumber=" . urlencode(trim($row['IVPLT'])) . "&amp;mfgOrder=" . urlencode(trim($row['IVMORD'])) . "\" onclick=\"$inquiryWinVar\" title=\"View Mfg Order\">$row[IVMORD]</a></td> ";}
		else                {print "\n <td class=\"colalph\">$row[IVMORD]</td> ";}

		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}TransactionHistoryInquiry.d2w/DISPLAY{$altVarBase}&amp;origTransSequence=" . urlencode(trim($row['IVOSEQ'])) . "&amp;transSequence=" . urlencode(trim($row['IVDSEQ'])) . "\" onclick=\"$inquiryWinVar\" title=\"Transaction Quickview\">$F_IVQTY</a></td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PCPCDS]\">$row[IVPCLS]</span></td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[ITDESC]\">$row[IVITC]</span></td> ";
		print "\n </tr> ";
		$startRow ++;
		$rowCount ++;
	}
	print "\n </table> ";
	print "\n </fieldset> ";
}

// Purchasing  ****************************************
if ($view_po>0) {
	require 'stmtSQLClear.php';
	$appendUserView="N";
	$appendWildCard="N";
	$stmtSQL .= " SELECT POAMT ,POQTY ,POIVTT,POTRDT,POWHS ,POPLT ,POVEND ";
	$stmtSQL .= "       ,POPO  ,POSEQ ,POPCLS,POITC ,POPOEC,POITEM,POIMDS,POIMDSU ";
	$stmtSQL .= "       ,TTDESC,WHWHNM,PLNAME,VMVNA1,PCPCDS,ITDESC ";
	$fileSQL=$po_fileSQL;
	$selectSQL=$po_selectSQL;
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By $po_orderBy ";
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($formatToPrint != "Y") {
		print "\n <table $contentTable> ";
		print "\n     <tr><td><a href=\"#top\">$topOfFormImage</a></td></tr> ";
		print "\n </table> ";
	}

	print "\n <a name=\"purchasing\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Purchasing</legend> ";

	print "\n <table $contentTable> ";
	$hdrOnce="";

	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult)){

		if ($hdrOnce=="") {
			$hdrOnce="Y";
			print "\n <tr> ";
			$orderByVar="{$scriptVarBase}{$searchVarBase}";
			$orderBy=$po_orderBy;
			$returnValue=OrderBy_Sort("POAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount\">{$sortPoint}Amount</a></th> ";
			$returnValue=OrderBy_Sort("POTRDT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=TransDate\" title=\"Sequence By Transaction Date\">{$sortPoint}Trans Date</a></th> ";
			$returnValue=OrderBy_Sort("POIVTT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=TransType\" title=\"Sequence By Transaction Type\">{$sortPoint}Trans Type</a></th> ";
			$returnValue=OrderBy_Sort("POITEM"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=ItemNumber\" title=\"Sequence By Item Number\">{$sortPoint}Item Number</a></th> ";
			$returnValue=OrderBy_Sort("POIMDSU"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=ItemDesc\" title=\"Sequence By Description\">{$sortPoint}Description</a></th> ";
			$returnValue=OrderBy_Sort("POWHS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=Warehouse\" title=\"Sequence By Warehouse\">{$sortPoint}Whs</a></th> ";
			$returnValue=OrderBy_Sort("POPLT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=Plant\" title=\"Sequence By Plant\">{$sortPoint}Plant</a></th> ";
			$returnValue=OrderBy_Sort("POPO"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=PurchaseOrder\" title=\"Sequence By Purchase Order\">{$sortPoint}Purchase Order</a></th> ";
			$returnValue=OrderBy_Sort("POQTY"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=Quantity\" title=\"Sequence By Quantity\">{$sortPoint}Quantity</a></th> ";
			$returnValue=OrderBy_Sort("POPCLS"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=ProdClass\" title=\"Sequence By Product Class\">{$sortPoint}Prod Class</a></th> ";
			$returnValue=OrderBy_Sort("POITC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=InvType\" title=\"Sequence By Inventory Type\">{$sortPoint}Inv Type</a></th> ";
			$returnValue=OrderBy_Sort("VMVNA1U"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=VendorName\" title=\"Sequence By Vendor Name\">{$sortPoint}Vendor Name</a></th> ";
			$returnValue=OrderBy_Sort("POVEND"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
			print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=PO_ORDERBY&amp;sequence=VendorNumber\" title=\"Sequence By Vendor Number\">{$sortPoint}Vendor Number</a></th> ";
			print "\n </tr> ";
		}

		require 'SetRowClass.php';

		$F_POAMT=Format_Nbr($row['POAMT'], "2", $amtEditCode, "Y", "", "");
		$F_POQTY=Format_Nbr($row['POQTY'], $qtyNbrDec, $qtyEditCode, "Y", "", "");
		$F_POTRDT=Format_Date($row['POTRDT'], "D");

		print "\n <tr class=\"$rowClass\"> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}GLDDPoReceiptDetail.d2w/ENTRY{$altVarBase}&amp;poNumber=" . urlencode(trim($row['POPO'])) . "&amp;receiptSeq=" . urlencode(trim($row['POSEQ'])) . "\" title=\"View P/O Receipt Detail\">$F_POAMT</a></td> ";
		print "\n <td class=\"coldate\">$F_POTRDT</td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[TTDESC]\">$row[POIVTT]</span></td> ";
		if ($row['POPOEC']!="N") {
			print "\n <td class=\"colalph\">$row[POITEM]</td> ";
			print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}ItemInquiry.d2w/DISPLAY{$altVarBase}&amp;itemNumber=" . urlencode(trim($row['POITEM'])) . "\" onclick=\"$inquiryWinVar\" title=\"Item Quickview\">$row[POIMDS]</a></td> ";
		} else {
			print "\n <td class=\"colalph\">$row[POITEM]</td> ";
			print "\n <td class=\"colalph\">&nbsp;</td> ";
		}
		print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[WHWHNM]\">$row[POWHS]</span></td> ";
		print "\n <td class=\"colnmbr\" $helpCursor><span title=\"$row[PLNAME]\">$row[POPLT]</span></td> ";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$phpPath}SelectPO.php{$genericVarBase}&amp;tabID=RECEIPTS&amp;vendorNumber=" . urlencode(trim($row['POVEND'])) . "&amp;purchaseOrderNumber=" . urlencode(trim($row['POPO'])) . "&amp;orderSequence=" . urlencode(trim($row['POSEQ'])) . "&amp;noMenu=Y\" onclick=\"$inquiryWinVar\" title=\"View Purchase Order\">$row[POPO]</a></td> ";
		print "\n <td class=\"colnmbr\">$F_POQTY</a></td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[PCPCDS]\">$row[POPCLS]</span></td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$row[ITDESC]\">$row[POITC]</span></td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}VendorSelect.d2w/REPORT{$altVarBase}&amp;vendorNumber=" . urlencode(trim($row['POVEND'])) . "\" title=\"View Vendor\">$row[VMVNA1]</a></td> ";
		print "\n <td class=\"colnmbr\"><a href=\"{$homeURL}{$cGIPath}VendorInquiry.d2w/DISPLAY{$altVarBase}&amp;vendorNumber=" . urlencode(trim($row['POVEND'])) . "\" onclick=\"$inquiryWinVar\" title=\"Vendor Quickview\">$row[POVEND]</a></td> ";
		print "\n </tr> ";
		$startRow ++;
		$rowCount ++;
	}
	print "\n </table> ";
	print "\n </fieldset> ";
}

// General Ledger ****************************************
if ($view_gl>0) {
	require 'stmtSQLClear.php';
	$appendUserView="N";
	$appendWildCard="N";
	$stmtSQL .= " SELECT TDJRNL,TDPER# as TDPER,TDTDTE,TDJSEQ,TDREC,TDREF ";
	$stmtSQL .= "       ,TDAMT ,TDDESC,TDSEQ# as TDSEQ,TDAPID ";
	$stmtSQL .= "      , TJTTYP, FLDESC ";
	$fileSQL=$gl_fileSQL;
	$selectSQL=$gl_selectSQL;
	require 'stmtSQLSelect.php';
	$stmtSQL .= "  Order By  $gl_orderBy ";
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($formatToPrint != "Y") {
		print "\n <table $contentTable> ";
		print "\n     <tr><td><a href=\"#top\">$topOfFormImage</a></td></tr> ";
		print "\n </table> ";
	}

	print "\n <a name=\"generalledger\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">General Ledger</legend> ";

	print "\n <table $contentTable> ";
	$orderByVar="{$scriptVarBase}{$searchVarBase}";
	$orderBy=$gl_orderBy;
	print "\n <tr> ";
	$returnValue=OrderBy_Sort("TDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=GL_ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount, Date, Journal, Sequence\">{$sortPoint}Amount</a></th> ";
	$returnValue=OrderBy_Sort("TDJRNL"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=GL_ORDERBY&amp;sequence=Journal\" title=\"Sequence By Journal, Date, Sequence\">{$sortPoint}Journal</a></th> ";
	$returnValue=OrderBy_Sort("TJTTYP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=GL_ORDERBY&amp;sequence=Type\" title=\"Sequence By Transaction Type, Date, Journal, Sequence\">{$sortPoint}Type</a></th> ";
	$returnValue=OrderBy_Sort("TDREF"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=GL_ORDERBY&amp;sequence=Reference\" title=\"Sequence By Reference, Date, Journal, Sequence\">{$sortPoint}Reference</a></th> ";
	$returnValue=OrderBy_Sort("TDPER"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=GL_ORDERBY&amp;sequence=Period\" title=\"Sequence By Period, Date, Journal, Sequence\">{$sortPoint}Period</a></th> ";
	$returnValue=OrderBy_Sort("TDDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=GL_ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Date, Journal, Sequence\">{$sortPoint}Description</a></th> ";
	$returnValue=OrderBy_Sort("TDTDTE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=GL_ORDERBY&amp;sequence=Date\" title=\"Sequence By Date, Journal, Sequence\">{$sortPoint}Date</a></th> ";
	print "\n </tr> ";


	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult)){
		require 'SetRowClass.php';

		$F_TDPER=PeriodFromCYP($row['TDPER']);
		$F_TDAMT=Format_Nbr($row['TDAMT'], "2", $amtEditCode, "Y", "", "");
		$F_TDTDTE=Format_Date($row['TDTDTE'], "D");

		print "\n <tr class=\"$rowClass\"> ";
		print "\n <td class=\"colnmbr\">$F_TDAMT</td> ";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}GLDDGLJrnl.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($row['TDJRNL'])) . "&amp;glPer=" . urlencode(trim($row['TDPER'])) . "&amp;glDate=" . urlencode(trim($row['TDTDTE'])) . "&amp;glJrnlSeq=" . urlencode(trim($row['TDJSEQ'])) . "\" title=\"View General Ledger Journal\">$row[TDJRNL]</a></td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"" . trim($row['FLDESC']) . "\">$row[TJTTYP]</span></td> ";
		print "\n <td class=\"colalph\">$row[TDREF]</td> ";
		print "\n <td class=\"colnmbr\">$F_TDPER</td> ";
		print "\n <td class=\"colalph\">$row[TDDESC]</td> ";
		print "\n <td class=\"coldate\">$F_TDTDTE</td> ";
		if ($row['TDSEQ'] != 0) {print "\n <td class=\"colicon\"><a href=\"{$homeURL}{$cGIPath}GLDDAcctJrnlComment.d2w/ENTRY{$altVarBase}&amp;glJrnl=" . urlencode(trim($row['TDJRNL'])) . "&amp;glPer=" . urlencode(trim($row['TDPER'])) . "&amp;glDate=" . urlencode(trim($row['TDTDTE'])) . "&amp;glJrnlSeq=" . urlencode(trim($row['TDJSEQ'])) . "&amp;glJrnlRec=" . urlencode(trim($row['TDREC'])) . "&amp;glLdSeq=" . urlencode(trim($row['TDSEQ'])) . "\" onclick=\"$commentWinVar\">$commentView</a></td> ";}
		print "\n </tr> ";
		$startRow ++;
		$rowCount ++;
	}
	print "\n </table> ";
	print "\n </fieldset> ";
}

// Other *********************************************************
if ($view_ot>0) {
	require 'stmtSQLClear.php';
	$appendUserView="N";
	$appendWildCard="N";
	$stmtSQL .= " SELECT TDJRNL,TDPER# as TDPER,TDTDTE,TDJSEQ,TDREC,TDREF ";
	$stmtSQL .= "       ,TDAMT ,TDDESC,TDSEQ# as TDSEQ,TDAPID ";
	$fileSQL=$ot_fileSQL;
	$selectSQL=$ot_selectSQL;
	require 'stmtSQLSelect.php';
	$stmtSQL .= " Order By  $oth_orderBy ";
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

	if ($formatToPrint != "Y") {
		print "\n <table $contentTable> ";
		print "\n     <tr><td><a href=\"#top\">$topOfFormImage</a></td></tr> ";
		print "\n </table> ";
	}

	print "\n <a name=\"other\"></a> ";
	print "\n <fieldset class=\"legendBody\"> ";
	print "\n <legend class=\"legendTitle\">Other</legend> ";

	print "\n <table $contentTable> ";
	$orderByVar="{$scriptVarBase}{$searchVarBase}";
	$orderBy=$oth_orderBy;
	print "\n <tr> ";
	$returnValue=OrderBy_Sort("TDAMT"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=OTH_ORDERBY&amp;sequence=Amount\" title=\"Sequence By Amount, Date, Journal, Sequence\">{$sortPoint}Amount</a></th> ";
	$returnValue=OrderBy_Sort("TDJRNL"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=OTH_ORDERBY&amp;sequence=Journal\" title=\"Sequence By Journal, Date, Sequence\">{$sortPoint}Journal</a></th> ";
	$returnValue=OrderBy_Sort("TJTTYP"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=OTH_ORDERBY&amp;sequence=Type\" title=\"Sequence By Transaction Type, Date, Journal, Sequence\">{$sortPoint}Type</a></th> ";
	$returnValue=OrderBy_Sort("TDREF"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=OTH_ORDERBY&amp;sequence=Reference\" title=\"Sequence By Reference, Date, Journal, Sequence\">{$sortPoint}Reference</a></th> ";
	$returnValue=OrderBy_Sort("TDPER"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=OTH_ORDERBY&amp;sequence=Period\" title=\"Sequence By Period, Date, Journal, Sequence\">{$sortPoint}Period</a></th> ";
	$returnValue=OrderBy_Sort("TDDESC"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=OTH_ORDERBY&amp;sequence=Description\" title=\"Sequence By Description, Date, Journal, Sequence\">{$sortPoint}Description</a></th> ";
	$returnValue=OrderBy_Sort("TDTDTE"); $sortVar=$returnValue['sortedBy']; $sortPoint=$returnValue['sortPoint'];
	print "\n <th class=\"colhdr$sortVar\"><a href=\"{$homeURL}{$phpPath}{$scriptName}{$orderByVar}&amp;tag=OTH_ORDERBY&amp;sequence=Date\" title=\"Sequence By Date, Journal, Sequence\">{$sortPoint}Date</a></th> ";
	print "\n </tr> ";


	$startRow = 1;
	while ($row = db2_fetch_assoc($sqlResult)){
		require 'SetRowClass.php';

		$F_TDPER=PeriodFromCYP($row['TDPER']);
		$F_TDAMT=Format_Nbr($row['TDAMT'], "2", $amtEditCode, "Y", "", "");
		$F_TDTDTE=Format_Date($row['TDTDTE'], "D");

		if ($row['TDAPID']=="IV" || $row['TDAPID']=="PO") {$referenceDesc=RetValue("TTTYPE='$row[TDREF]'", "HDTTYP", "TTDESC");}
		else {
			$referenceDesc=RetValue("(FLTYPE,FLVALU)=('{$row[TDAPID]}FEED','$row[TDREF]')", "SYFLAG", "FLDESC");
			if ($referenceDesc=="" && $row['TDAPID']=="AP") {$referenceDesc=RetValue("VMVEND=$row[TDREF]", "HDVEND", "VMVNA1");}
		}
		print "\n <tr class=\"$rowClass\"> ";
		print "\n <td class=\"colnmbr\">$F_TDAMT</td> ";
		print "\n <td class=\"colalph\"><a href=\"{$homeURL}{$cGIPath}GLDDGLJrnl.d2w/ENTRY{$altVarBase}{$glDDVarBase}&amp;glJrnl=" . urlencode(trim($row['TDJRNL'])) . "&amp;glPer=" . urlencode(trim($row['TDPER'])) . "&amp;glDate=" . urlencode(trim($row['TDTDTE'])) . "&amp;glJrnlSeq=" . urlencode(trim($row['TDJSEQ'])) . "\" title=\"View General Ledger Journal\">$row[TDJRNL]</a></td> ";
		print "\n <td class=\"colalph\">$row[TJTTYP]</td> ";
		print "\n <td class=\"colalph\" $helpCursor><span title=\"$referenceDesc\">$row[TDREF]</span></td> ";
		print "\n <td class=\"colnmbr\">$F_TDPER</td> ";
		print "\n <td class=\"colalph\">$row[TDDESC]</td> ";
		print "\n <td class=\"coldate\">$F_TDTDTE</td> ";
		if ($row['TDSEQ'] != 0) {
			print "\n <td class=\"colicon\"><a href=\"{$homeURL}{$cGIPath}GLDDAcctJrnlComment.d2w/ENTRY{$altVarBase}&amp;glJrnl=" . urlencode(trim($row['TDJRNL'])) . "&amp;glPer=" . urlencode(trim($row['TDPER'])) . "&amp;glDate=" . urlencode(trim($row['TDTDTE'])) . "&amp;glJrnlSeq=" . urlencode(trim($row['TDJSEQ'])) . "&amp;glJrnlRec=" . urlencode(trim($row['TDREC'])) . "&amp;glLdSeq=" . urlencode(trim($row['TDSEQ'])) . "\" onclick=\"$commentWinVar\">$commentView</a></td> ";
		}
		print "\n </tr> ";
		$startRow ++;
		$rowCount ++;
	}
	print "\n </table> ";
	print "\n </fieldset> ";
}

require $inquiryBanner;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require $inquiryTrailer;
print "\n </body> \n </html>";

?>
