<?php
// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// DVG - Update to get pricing to work.
// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

require_once 'GetURLParm.php';                 // REQUIRED ...or SetLibraryList.php won't work properly.
require_once 'CopyrightBanner.php';
// require_once 'Copyright_ProITRG.php';

// require_once 'SetLibraryList_ProITRG.php';  // Test to verify $libraryList; doesn't interrupt production.
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'PricingDetailFunctions.php';
require_once 'SG_PricingDetailFunctions.php';


$page_title     = "Screen Graphics Pricing Inquiry*";
$scriptName     = "SG_PrcInq2.php";
$scriptVarBase  = "($genericVarBase)&amp;pricingLevel=" .urlencode(trim($priceLevel)) . "&amp;levelDesc" . urlencode(trim($levelDesc));
$baseURL        = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$programName    = "HOEPRC_W";
$backURL = $_SESSION[$fromURL];


if ($backURL == "") {
					$backURL="{$homeURL}{$phpPath}SG_PrcInq2.php{$scriptVarBase}";
					}

// Write header...
print "\n <html> <head>";
require_once ($headInclude);      // Required to tie into existing .css for the environment
	print "\n <script TYPE=\"text/javascript\">";
	require_once 'CheckEnterSearch.php';
	print "\n </script>";
require_once ($genericHead);      // Required to get the black bar across the top with Home, Contact Us, and Zone
print "\n </head>";
require_once 'Banner.php';        // Required to get the Screen Graphics logo

// Write page title and set up the form...
print "\n <table $baseTable>";
print "\n <tr valign=\"top\">";
$pageID = "PRICINGINQUIRY";
print "\n <td class=\"content\">";
print "<tr><td>&nbsp;</td></tr>";
print "<tr><td><h1>&nbsp;&nbsp;&nbsp;Screen Graphics - Pricing Inquiry*</h1></td></tr>";
print "<tr><td>&nbsp;</td></tr>";
print "</table>";


// Capture the data submitted so it can be redisplayed with detail...
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  // Turn on error reporting first...
  // error_reporting(E_ALL | E_STRICT);
  // ini_set("display_errors", 1);

  // Write each $_POST value to a variable named the same as the $key...
  foreach($_POST As $key => $value)
  {
     $$key = $value;
  }

  $custClassDesc = ""; 
  // Pull the customer information to display...
  $stmtSQL = "";
  $stmtSQL .= " Select CMCNA1, CMCCLS, CCCCDS ";
  $stmtSQL .= " From HDCUST ";
  $stmtSQL .= " Left Outer Join HDCCLS on CMCCLS = CCCCLS ";
  $stmtSQL .= " Where CMCUST = '$custNumber' ";

  $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
  if ($row = db2_fetch_assoc($sqlResult)){
         $custName = $row['CMCNA1'];
         $F_CMCCLS = $row['CMCCLS'];
         $custClass = $row['CMCCLS'];
         $custClassDesc = $row['CCCCDS']; 
     } else {
         $custName = "*** Customer $custNumber does not exist ***";
     }

  // Pull the customer class information for display...
  if ($custClass) {
     $stmtSQL = "";
     $stmtSQL .= " Select CCCCDS from HDCCLS ";
     $stmtSQL .= "  Where CCCCLS = '$custClass' ";
     $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
     if ($row = db2_fetch_assoc($sqlResult)){
        $custClassDesc = $row['CCCCDS'];
     }
  }     
  

  // Pull the item information to display...
  $stmtSQL = "";
  $stmtSQL .= " Select IMIMDS ";
  $stmtSQL .= " From HDIMST ";
  $stmtSQL .= " Where IMITEM = '$itemNumber' ";

  $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
  if ($row = db2_fetch_assoc($sqlResult)){
         $itemDescription = $row['IMIMDS'];
     } else {
         $itemDescription = "*** Item $itemNumber does not exist ***";
     }
 }

// Default $reqQty to 1
if ($reqQty < 1) { $reqQty = 1; }
 
 ?>

<!-- // Display the form the user fills in... -->

<form name="PriceInq" id="PriceInq" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<table td class = "content"> 
     <tr><td class="dsphdr" width="135">Customer Number:</td> 
         <td class="inputnmbr"><input name="custNumber" value="<?php echo $custNumber;?>" type="text" size="6" maxlength="7">
         <a href="CustomerSearch.php?baseVar=BaseConfiguration.php&amp;portal=REPORT&amp;docName=PriceInq&amp;fldName=custNumber&amp;fldDesc=custName" onclick="NewWindow(this.href, 'search_win', '70','50','yes','yes','yes','yes','yes');return false;"> <?php echo $searchImage?> </a> <input name="custName" type="hidden"></td>
         <td class="hdrdata"><?php if ($custNumber) {echo $custNumber ." [" .$custName. "]";}?> </td> </tr> 

     <tr><td class="dsphdr" width="135">Customer Class:</td> 
         <td class="inputnmbr" width="120"><input type="text" id="custClass" name="custClass" size="2" 
              value="<?php echo $custClass;?>"></td>
         <td class="hdrdata"><?php if ($custClass) {echo $custClass ." [" .$custClassDesc. "]";}?> </tr>

     <tr><td class="dsphdr" align="right">Item Number:</td>
         <td class="inputalph"><input type="text" id="itemNumber" name="itemNumber" size="15" 
              value="<?php echo $itemNumber;?>">
              <a href="ItemSearch.php?baseVar=BaseConfiguration.php&amp;portal=REPORT&amp;docName=PriceInq&amp;fldName=itemNumber&amp;fldDesc=itemDescription" onclick="NewWindow(this.href, 'search_win', '70','50','yes','yes','yes','yes','yes');return false;"> <?php echo $searchImage?> </a> <input name="itemDescription" type="hidden"></td>
              <td class="hdrdata"><?php if ($itemNumber) { print "<a href=\"{$homeURL}/harris-CGI/ItemSearch.php/REPORT?baseVar=BaseConfiguration.icl&amp;portal=Item&amp;ItemDesc=$itemDesc&amp;itemNumber=$itemNumber\" target=\"_blank\"</a><title=\"View Item $itemNumber\">" .Trim($itemNumber). "</a>"; } ?> 
                                  <?php if ($itemDescription) {echo " [" .$itemDescription. "]";}?></td></tr>
              
     <tr><td class="dsphdr" align="right">Quantity:</td>
         <td class="inputnmbr"><input type="text" id="reqQty" name="reqQty" size="15"   
              value="<?php echo $reqQty;?>"></td></tr>
     <tr><td>&nbsp</td></tr>
     <tr><td></td><td><span><input type="submit" name="submit" value="Submit"></span></td><tr></table>
</form>



<?php 

print "\n <table $contentTable>";
print $hrTagAttr;



///////////////////////////////////////
// Display Availability by Warehouse //
///////////////////////////////////////

  // Quantities extracted below are as follows...
  // IWOHQT = Quantity On Hand
  // IWQOO  = Quantity On Order (for Purchased Items Only)
  // IPQMRL = Manufacturing Order Quantity Released/Scheduled
  // IWRESQ = Quantity Reserved (Customer Orders)
  // IWQOPK = Quantity on Pick Tickets
  // IPCMTO = Quantity Committed to Manufacturing    

  // Reset the save variables...
  $SV_IPPLT = "";
  $SV_IWWHS = "";
  
  // If there are no errors apparent, process the form data...
  $stmtSQL = "";
  $stmtSQL .= " Select IPPLT, IWWHS, IPITEM, IMIMDS, IMPTYP, IWOHQT, IWQOO, IPQMRL, IWRESQ, IWQOPK, IPCMTO, IWPGRP, IWLIST ";
  $stmtSQL .= " From HDIPLT ";
  $stmtSQL .= " Left Outer Join HDIMST on IPITEM = IMITEM ";
  $stmtSQL .= " Left Outer Join HDIWHS on IPITEM = IWITEM ";
  $stmtSQL .= " Where IPITEM = '$itemNumber' ";
  $stmtSQL .= " Order By IPITEM, IPPLT, IWWHS ";

  $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );

  while ($row = db2_fetch_assoc($sqlResult)){
	   // require  'SetRowClass.php';

         $F_IPPLT = $row['IPPLT'];
         $F_IWWHS = $row['IWWHS'];
         $F_IPITEM= $row['IPITEM'];
         $F_IMIMDS= $row['IMIMDS'];
         $F_IMPTYP= $row['IMPTYP'];
         $F_IWOHQT= $row['IWOHQT'];
         $F_IWQOO = $row['IWQOO'];
         $F_IPQMRL= $row['IPQMRL'];
         $F_IWRESQ= $row['IWRESQ'];
         $F_IWQOPK= $row['IWQOPK'];
         $F_IPCMTO= $row['IPCMTO'];
         $F_IWPGRP= $row['IWPGRP'];
         $F_IWLIST= $row['IWLIST'];
           
         // Show AVAILABILITY by Plant and Warehouse
            
            // Skip a line between each Plant/Warehouse combination found...
            if ($SV_IPPLT == "") {
               print "<br>";
               print "<table align=\"left\" hspace=\"15\" frame=\"box\" cellspacing=\"1\" cellpadding=\"10\">";
               print "\n <tr><td colspan=\"100%\" bgcolor=\"#000000\" p style=\"color:#FFFFFF\" align=\"center\"><b>Availability</b></td></tr>";
            }

            // Write secondary headers for Availability (Plant, Warehouse, and Availability)
            print "\n <tr><td colspan=\"2\" bgcolor=\"#08088A\" p style=\"color:#FFFFFF\" align=\"center\"><font size=\"5\"><b>Plant " .$row['IPPLT']. "</b></td>";
            print "<td colspan=\"2\" bgcolor=\"#B40404\" p style=\"color:#FFFFFF\" align=\"center\"><font size=\"5\"><b>Warehouse " .$row['IWWHS']. "</b></td>";
            $F_IWWHS = $row['IWWHS'];
            $QtyAvail = $row['IWOHQT'] + $row['IWQOO'] - $row['IWRESQ'] - $row['IWQOPK'] - $row['IPCMTO'];
            print "<td colspan=\"1\" bgcolor=\"#000000\" p style=\"color:#FFFFFF\" align=\"center\"><font size=\"6\"><b>" . Format_Nbr($QtyAvail, '0', $amtEditCode, 'N', '', ''). "</b></font></td></tr>";


            // Write tertiary headers for Availability contents...
            print "\n <th class=\"colhdr\">Qty<br>On Hand</th>";
            print "\n <th class=\"colhdr\">Committed<br>To Mfg. Orders</th>";
            print "\n <th class=\"colhdr\">Qty Scheduled<br>To Manufacture</th>";
            print "\n <th class=\"colhdr\">Qty on<br>Pick Tickets</th>";
            print "\n <th class=\"colhdr\">Qty on<br>Customer Orders</th>";
            print "\n </tr>";
            
            // Print Availability data... 
            print "\n <tr><td class=\"colcode\">" .Format_Nbr($row['IWOHQT'], '0', $amtEditCode, 'Y','', ''). "</td>";
            print "\n <td class=\"colcode\">" .Format_Nbr($row['IPCMTO'], '0', $amtEditCode, 'Y', '', ''). "</td>";
            print "\n <td class=\"colcode\">" .Format_Nbr($row['IPQMRL'], '0', $amtEditCode, 'Y', '', ''). "</td>";
            print "\n <td class=\"colcode\">" .Format_Nbr($row['IWQOPK'], '0', $amtEditCode, 'Y', '', ''). "</td>";
            print "\n <td class=\"colcode\">" .Format_Nbr($row['IWRESQ'], '0', $amtEditCode, 'Y', '', ''). "</td></tr>";
          
            $SV_IPPLT = $F_IPPLT;
            if ($SV_IWWHS == "") {
               $primaryWhse = $F_IWWHS; }
     		$SV_IWWHS = $F_IWWHS;
            $rowCount++;
  }
	    
  // End the Availability reporting so the next "group" of data can be aligned to the right...
  print "\n </table>"; 



////////////////////////////////////////////////
// Display Pricing Definition and Detail Data //
////////////////////////////////////////////////

//****************************************************************************************************// 
// Per Bill Busch on March 28, 2014:                                                                  //
// Pull the lowest price level for the item specified.                                                //
//****************************************************************************************************// 

if ($itemNumber) { 
   // Extract/Display pricing table...
   $todayCYMD = (date('Y') >= 2000? 1 : 0) . date('ymd');
   $stmtSQL = "";
   $startRow = 0;
   $priceDefDsp = 0;
   $bracketQtyHdrs = "Not Displayed";
   $found = "No";

   $stmtSQL = "";
   $stmtSQL .= " Select Distinct PMPMLV from HDPRCD Where PMITEM = '$itemNumber' ";
   $stmtSQL .= " Order by PMPMLV ";
   $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
   
   while (($sqlResult) and ($found == "No")) {
      $prcLevel = db2_fetch_assoc($sqlResult);
      // print_r($prcLevel);
      $priceLevel = $prcLevel['PMPMLV'];
      if (($priceLevel == 10) And (!$custNumber) And (!$custClass)) {
         die("<br><br><table border=\"0\" cellspacing=\"1\" cellpadding=\"5\"><tr><td>&nbsp</td><td><b>This item has Customer Class pricing.  You must enter either a customer number or a customer class.</b></td></tr></table>");
      }
        
      // print "<br><br>Price Level: " .$priceLevel. "<br>";
     
      switch($priceLevel)
         {   case 10:
                if ($custNumber) {
                   // Customer Class Pricing by Warehouse, Item, and Customer Class...
                   $stmtSQL = "";
                   $stmtSQL .= " Select PMPMLV, PVLVDS, PVCN, PVLL, PVCP, PVDL, PVUP, PVBP, PVBA, ";
                   $stmtSQL .= " PMPMKY, PMSTDT, PMEXDT, PMLCAM, PMLCPC, PBSTDT, PBLMT, PBPRC ";
                   $stmtSQL .= " From HDPRCD ";
                   $stmtSQL .= " Left Outer Join HDPRLC on PMPMLV = PVPMLV ";
                   $stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY ";
                   $stmtSQL .= " Where PMPMLV = '$priceLevel' And PMWHS = '$primaryWhse' And ";
                   $stmtSQL .= " PMITEM = '$itemNumber' And PMCCLS = '$F_CMCCLS' ";
                }
                elseif ($custClass) { 
                   $stmtSQL = "";
                   $stmtSQL .= " Select PMPMLV, PVLVDS, PVCN, PVLL, PVCP, PVDL, PVUP, PVBP, PVBA, ";
                   $stmtSQL .= " PMPMKY, PMSTDT, PMEXDT, PMLCAM, PMLCPC, PBSTDT, PBLMT, PBPRC ";
                   $stmtSQL .= " From HDPRCD ";
                   $stmtSQL .= " Left Outer Join HDPRLC on PMPMLV = PVPMLV ";
                   $stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY ";
                   $stmtSQL .= " Where PMPMLV = '$priceLevel' And PMWHS = '$primaryWhse' And ";
                   $stmtSQL .= " PMITEM = '$itemNumber' And PMCCLS = '$custClass' ";
                }
                else {
                   $stmtSQL = "";
                   $stmtSQL .= " Select PMPMLV, PVLVDS, PVCN, PVLL, PVCP, PVDL, PVUP, PVBP, PVBA, ";
                   $stmtSQL .= " PMPMKY, PMSTDT, PMEXDT, PMLCAM, PMLCPC, PBSTDT, PBLMT, PBPRC, PMCCLS ";
                   $stmtSQL .= " From HDPRCD ";
                   $stmtSQL .= " Left Outer Join HDPRLC on PMPMLV = PVPMLV ";
                   $stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY ";
                   $stmtSQL .= " Where PMPMLV = '$priceLevel' And PMWHS = '$primaryWhse' And ";
                   $stmtSQL .= " PMITEM = '$itemNumber' ";
                }
                break;

              case 20:
                 // Customer Number Pricing by Customer and Item...
                 $stmtSQL = "";
                 $stmtSQL .= " Select PMPMLV, PVLVDS, PVCN, PVLL, PVCP, PVDL, PVUP, PVBP, PVBA, ";
                 $stmtSQL .= " PMPMKY, PMSTDT, PMEXDT, PMLCAM, PBSTDT, PBLMT, PBPRC ";
                 $stmtSQL .= " From HDPRCD ";
                 $stmtSQL .= " Left Outer Join HDPRLC on PMPMLV = PVPMLV ";
                 $stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY ";
                 $stmtSQL .= " Where PMPMLV = '$priceLevel' And PMITEM = '$itemNumber' ";
                 $stmtSQL .= " And PMCCLS = '$custClass' And PMWHS = '$primaryWhse' ";
                 break;

              case 30:
                 // Customer Class Catalog Items -5%...
                 $stmtSQL = "";
                 $stmtSQL .= " Select PMPMLV, PVLVDS, PVCN, PVLL, PVCP, PVDL, PVUP, PVBP, PVBA, ";
                 $stmtSQL .= " PMPMKY, PMSTDT, PMEXDT, PMLCAM, PMLCPC, PBSTDT, PBLMT, PBPRC ";
                 $stmtSQL .= " From HDPRCD ";
                 $stmtSQL .= " Left Outer Join HDPRLC on PMPMLV = PVPMLV ";
                 $stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY ";
                 $stmtSQL .= " Where PMPMLV = '$priceLevel' And PMITEM = '$itemNumber' ";
                 $stmtSQL .= " And PMCCLS = '$custClass' ";
                 break;

             case 35:
             	// Customer Class Catalog Items -10%...
             	$stmtSQL = "";
              	$stmtSQL .= " Select PMPMLV, PVLVDS, PVCN, PVLL, PVCP, PVDL, PVUP, PVBP, PVBA, ";
             	$stmtSQL .= " PMPMKY, PMSTDT, PMEXDT, PMLCAM, PMLCPC, PBSTDT, PBLMT, PBPRC ";
             	$stmtSQL .= " From HDPRCD ";
             	$stmtSQL .= " Left Outer Join HDPRLC on PMPMLV = PVPMLV ";
             	$stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY ";
             	$stmtSQL .= " Where PMPMLV = '$priceLevel' And PMITEM = '$itemNumber' ";
             	$stmtSQL .= " And PMCCLS = '$custClass' ";
             	break;
                 
              case 40:
                 // Product Group Pricing by Product Group, Warehouse, and Item...
                 $stmtSQL = "";
                 $stmtSQL .= " Select PMPMLV, PVLVDS, PVCN, PVLL, PVCP, PVDL, PVUP, PVBP, PVBA, ";
                 $stmtSQL .= " PMPMKY, PMSTDT, PMEXDT, PMLCAM, PMLCPC, PBSTDT, PBLMT, PBPRC ";
                 $stmtSQL .= " From HDPRCD ";
                 $stmtSQL .= " Left Outer Join HDPRLC on PMPMLV = PVPMLV ";
                 $stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY ";
                 $stmtSQL .= " Where PMPMLV = '$priceLevel' And PMPGRP = '$F_IWPGRP' ";
                 $stmtSQL .= " And PMWHS = '$primaryWhse' And PMITEM = '$itemNumber' ";
                 break;

              case 70:
                 // Standard Pricing by Warehouse and Item...
                 $stmtSQL = "";
                 $stmtSQL .= " Select PMPMLV, PVLVDS, PVCN, PVLL, PVCP, PVDL, PVUP, PVBP, PVBA, ";
                 $stmtSQL .= " PMPMKY, PMSTDT, PMEXDT, PMLCAM, PMLCPC, PBSTDT, PBLMT, PBPRC ";
                 $stmtSQL .= " From HDPRCD ";
                 $stmtSQL .= " Left Outer Join HDPRLC on PMPMLV = PVPMLV ";
                 $stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY ";
                 $stmtSQL .= " Where PMPMLV = '$priceLevel' And PMWHS = '$primaryWhse' ";
                 $stmtSQL .= " And PMITEM = '$itemNumber' ";
                 break;
           }

           $lvlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
         		
           while ($row = db2_fetch_assoc($lvlResult)) {
                 $found = "Yes";
              
             if ($priceDefDsp != 1) {
                 $priceLevel   = $row['PMPMLV'];
                 $levelDesc    = $row['PVLVDS'];
                 $contract     = $row['PVCN'];
                 $listLess     = $row['PVLL'];
                 $costPlus     = $row['PVCP'];
                 $dollarAmt    = $row['PVDL'];
                 $usePercent   = $row['PVUP'];
                 $bracketQty   = $row['PVBP'];
                 $bracketAmt   = $row['PVBA'];
           

                 ////////////////////////////////////////////////////////////////////////////////////////////
                 // This routine is used in pricing detail maintenance to determine the pricing definition //
                 ////////////////////////////////////////////////////////////////////////////////////////////
                 print "<table align=\"left\" hspace=\"15\" frame=\"box\" cellspacing=\"1\" cellpadding=\"0\">";
                 print "\n <tr><td colspan=\"100%\" cellpadding=\"10\" bgcolor=\"#000000\" p style=\"color:#FFFFFF\" align=\"center\"><b>Pricing Definition / Detail</b></td></tr>";
                 print "\n <tr><td colspan=\"100%\" cellpadding=\"10\" bgcolor=\"#FFFFFF\" p style=\"color:#FFFFFF\">&nbsp</td></tr>";
                   
                 Format_Header_URL("Pricing Level", $levelDesc, $priceLevel, "");
                 
                 if ($listLess == "Y")   {$structureDefn = "List Less";}
                 if ($costPlus == "Y")   {$structureDefn = "Cost Plus";}
                 if ($dollarAmt == "Y")  {$structureDefn = "Amount";}
                 if ($dollarAmt != "Y")  {if ($usePercent == "Y") {$structureDefn .= " Percentage";} else {$structureDefn .= " Amount";}}
                 print "\n <tr><td class=\"hdrtitl\">Definition:</td>";
                 if ($contract == "Y")  {
	              print "\n   <td class=\"hdrdata\">Contract</td></tr>";
	              print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">$structureDefn</td></tr>";
                 } else {
	              print "\n   <td class=\"hdrdata\">$structureDefn</td></tr>";
                 }
                 if ($bracketQty == "Y") {
    	              print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">Bracket By Quantity</td></tr> ";
                 } elseif ($bracketAmt == "Y") {
	              print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">Bracket By Amount</td></tr> ";
                 }
                 if ($commission != "") {
	              print "\n   <tr><td>&nbsp;</td><td class=\"hdrdata\">";
	           if ($commission == "Y")     {print "Commissionable";}
    	           elseif ($commission == "N") {print "Non-Commissionable";}
	           elseif ($commission == "L") {print "Limited Commission";}
	           print "\n   </td></tr>";
                 }
                 print "<tr><td>&nbsp</td></td>";
           
                // Print Contract Start and End Date
                if ($contract == "Y")  {
                   print "\n <tr><td class=\"hdrtitl\">Contract Start:</td><td class=\"hdrdata\">" .Format_Date($row['PMSTDT'],"D"). "</td></tr>";
                   print "\n <tr><td class=\"hdrtitl\">Contract End:</td><td class=\"hdrdata\">" .Format_Date($row['PMEXDT'],"D"). "</td></tr>";
                }  
           
	          $priceDefDsp = 1;
           }
          
  
            // Print Pricing Appropriately...
   
           if ($bracketQty != "Y" && $bracketAmt != "Y")  {
              

	          if ($usePercent == "Y") {
	          	// Use LIST... display it.
	          	print "\n <tr><td>&nbsp</td></tr>";
	          	print "\n <tr><td class=\"hdrtitl\"><span $textOvr>List Price:</span></td>";
	          	print "\n     <td class=\"hdrdata\">" .rtrim($F_IWLIST). "</td>";
	          	print "\n </tr>";
	          	
		         $textOvr=SetTextOvr($Err_PMLCPC);
		         print "\n <tr><td class=\"hdrtitl\"><span $textOvr>$structureDefn:</span></td>";
	   	         print "\n     <td class=\"hdrdata\">" .rtrim($row['PMLCPC']). "</td>";
		         print "\n </tr>";
		         DspErrMsg($Err_PMLCPC);
		         
		         if ($listLess == "Y") {
		         	// Display Price per Piece
		         	$singlePrice = $F_IWLIST - ($F_IWLIST * $row['PMLCPC']);
		         	print "\n <tr><td>&nbsp</td></tr>";
		         	print "\n <tr><td class=\"hdrtitl\"><span $testOvr>Price per Piece:</span></td>";
		         	print "\n <td class=\"hdrdata\">" .Format_Nbr(round($singlePrice, 5),'5',$amtEditCode, 'Y', '', ''). "</td>";
		         	print "\n </tr>";
		         	
		         	// Multiply out the quantity * price as well
		         	print "\n <tr><td>&nbsp</td></tr>";
		         	print "\n <tr><td class=\"hdrtitl\"><span $textOvr>Price for $reqQty:</span></td>";
		         	if ($reqQty > 1) { 
		         		print "\n <td class=\"hdrdata\">" .Format_Nbr(round(($singlePrice * $reqQty), 2),'2',$amtEditCode, 'Y', '', ''). "</td></tr>";
		         	} else {
		         		print "\n <td class=\"hdrdata\">" .Format_Nbr(round($singlePrice, 2),'2',$amtEditCode, 'Y', '', ''). "</td></tr>";
		         	}
	           
	          } else {
		         $textOvr=SetTextOvr($Err_PMLCAM);
		         print "\n <tr><td>&nbsp</td</tr>";
                 print "\n <tr><td class=\"hdrtitl\"><span $textOvr>$structureDefn</span></td>";
		         print "\n     <td class=\"hdrdata\" size=\"20\" maxlength=\"14\">" .Format_Nbr($row['PMLCAM'], '2', $amtEditCode, 'Y', '', ''). "</td>";
		         print "\n </tr>";
		         DspErrMsg($Err_PMLCAM);
              }
	        if ($commission == "Y" || $commission == "L") {
		     $textOvr=SetTextOvr($Err_PMCMPC);
		     print "\n <tr><td class=\"dsphdr\"><span $textOvr>Commission Percent</span></td>";
		     print "\n     <td class=\"inputnmbr\"><input type=\"text\"  name=\"commPercent\" value=\"" . rtrim($row['PMCMPC']) . "\" size=\"20\" maxlength=\"8\"></td>";
		     print "\n </tr>";
		     DspErrMsg($Err_PMCMPC);
	        }
           }
          }

           

        if ($bracketQty == "Y" || $bracketAmt == "Y")  {
           if ($firstPass != 'N') { $firstPass = 'Y'; }        	
        ////////////////////////////////////
        // Only want to do this part once //
        ////////////////////////////////////
        if ($bracketQty == "Y") {$brkDesc = "Quantity";} else {$brkDesc = "Amount";}
           if ($bracketQtyHdrs != 'Displayed') {  
              print "\n   <tr><td>&nbsp</td></tr><tr><td>&nbsp</td>";
              print "\n   <th class=\"colhdr\">$brkDesc Limit</th>";
	          print "\n   <th class=\"colhdr\">$structureDefn</th>";
           } 
	          if ($row['PBPRC'] != 0.00) {
	          // Break out units into an array to see are there setup costs...
	          $units = explode(".", $row['PBLMT']);
	          // Display setup costs if there are any...
	             if ($units[1] > 0 && $firstPass == 'Y') {
	          	    print "\n <tr><td class=\"dsphdr\"><span $textOvr>Setup Costs</span></td>";
	          	    print "\n      <td class=\"colcode\" size=\"20\" maxlength=\"14\">" .Format_Nbr($row['PBLMT'], '2', $amtEditCode, 'Y', '', ''). "</td>";
	          	    print "\n      <td class=\"colcode\" size=\"20\" maxlength=\"14\">" .Format_Nbr($row['PBPRC'], '5', $amtEditCode, 'Y', '', ''). "</td>";
	          	    print "\n </tr>";
	          	    // If there are setup costs, use that price for least quantity...
	          	    $Price = $row['PBPRC'] * $reqQty;
	          	    $firstPass = 'N';
	             // print "\n <tr><td>Got here!</td><td>$Price</td></tr>";
	          	    
	             } else {
	              	// There are no setup costs, use max less than requested quantity price * quantity or == price if quantity matches requested quantity...
	              	print "\n  <tr><td>&nbsp</td><td class=\"colcode\" size=\"20\" maxlength=\"14\">" .Format_Nbr($row['PBLMT'], '0', $amtEditCode, 'Y', '', ''). "</td>";
	              	print "\n      <td class=\"colcode\" size=\"20\" maxlength=\"14\">" .Format_Nbr($row['PBPRC'], '2', $amtEditCode, 'Y', '', ''). "</td>";
	              	print "\n </tr>";
	              	$intPBLMT = intval($row['PBLMT']);
	              	$intreqQty = intval($reqQty);
	                if ($intPBLMT == $intreqQty && $firstPass == 'Y') { $Price = $row['PBPRC']; }
	                if ($intPBLMT == $intreqQty && $firstPass != 'Y') { $Price = $row['PBPRC'] * $reqQty; }
	                if ($intPBLMT < $intreqQty) { $Price = $row['PBPRC'] * $reqQty; }
	                $firstPass = 'N';
	             // print "\n <tr><td>Got here!</td><td>$Price</td></tr>";
	              }
	             $bracketQtyHdrs = 'Displayed';
                }
              }
            }
            // Display the price for the quantity requested... if the quantity is lower than the lowest bracket, set the price to ALL 9s...
            print "\n <tr>&nbsp</td><td>&nbsp</td><td><hr></td><td><hr></td></tr>";
            print "\n <tr><td class=\"hdrtitl\"><span $textOvr>Price for $reqQty:</span></td>";
            if ($Price != 0.00) { 
            	print "\n <td class=\"colcode\" size=\"20\" maxlength=\"14\">" .Format_Nbr($Price, '2', $amtEditCode, 'Y', '', ''). "</td></tr>";
            } else {
            	$Price = 999999999.99999;
            	print "\n <td class=\"colcode\" size=\"20\" maxlength=\"14\">" .$Price. "</td></tr>";	
            }
          }

  if ($found == "No") {
     print "\n <br><br><b>No Pricing Information Exists for this Customer/Item Combination!</b>";
    }
   }         

// Print a blank line at the bottom of the Pricing Definition / Detail table for readability...
print "\n <tr><td colspan=\"100%\" cellpadding=\"10\" bgcolor=\"#FFFFFF\" p style=\"color:#FFFFFF\">&nbsp</td></tr></table>";

////////////////////////////////////////////////////////////////////////////////////////////////
// Display the customer information ...if provided... directly beneath the availability table //
////////////////////////////////////////////////////////////////////////////////////////////////

if (!empty($custNumber)) {
   // Pull customer information for display...
   $stmtSQL = "";
   $stmtSQL .= " Select CMBLTO, CMCNA1, CMCNA2, CMCNA3, CMCNA4, CMCCTY, CMST, CMZIP, CMCTRY, CMCCLS, CMPHON, CMCCRL, CMSLSM, ";
   $stmtSQL .= " CMALSM, CMDFPO, CMDLPO, CMDLPY, CMCTRM, TMCTDS From HDCUST Left Outer Join HDTRMS on CMCTRM = TMCTRM ";
   $stmtSQL .= " Where CMCUST = '$custNumber' ";
   $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
   
   while($row = db2_fetch_assoc($sqlResult)) {
     $F_CMBLTO = $row['CMBLTO'];
     $F_CMCNA1 = $row['CMCNA1'];
     $F_CMCNA2 = $row['CMCNA2'];
     $F_CMCNA3 = $row['CMCNA3'];
     $F_CMCNA4 = $row['CMCNA4'];
     $F_CMCCTY = $row['CMCCTY'];
     $F_CMST   = $row['CMST'];
     $F_CMZIP  = $row['CMZIP'];
     $F_CMCTRY = $row['CMCTRY'];
     $F_CMCCLS = $row['CMCCLS'];
     $F_CMPHON = $row['CMPHON'];
     $F_CMCCRL = $row['CMCCRL'];
     $F_CMSLSM = $row['CMSLSM'];
     $F_CMALSM = $row['CMDFPO'];
     $F_CMDFPO = $row['CMDLPO'];
     $F_CMDLPY = $row['CMDLPY'];
     $F_CMCTRM = $row['CMCTRM'];
     $F_TMCTDS = $row['TMCTDS'];

     print "<table align=\"left\" hspace=\"30\" frame=\"box\" cellspacing=\"1\" cellpadding=\"0\">";
     print "\n <tr><td colspan=\"100%\" bgcolor=\"#000000\" p style=\"color:#FFFFFF\" align=\"center\"><b>Customer Information</b></td></tr>";
     print "\n <tr><td colspan=\"100%\" bgcolor=\"#FFFFFF\" p style=\"color:#FFFFFF\">&nbsp</td></tr>";

     print "<tr><td class=\"hdrtitl\">Customer:</td><td class=\"hdrdata\">$custNumber</td>";
     print "<td class=\"dspalph\"><a href=\"{$homeURL}/harris-CGI/CustomerSelect.d2w/REPORT?baseVar=BaseConfiguration.icl&amp;portal=CUSTOMER&amp;customerName=$custName&amp;customerNumber=$custNumber\" target=\"_blank\"</a><title=\"View customer $custName\">$F_CMCNA1</td></tr>";

     print "<tr><td>&nbsp;</td><td>&nbsp;</td>";     
     if (trim($F_CMCNA2) != "") {
        print "\n <td class=\"dspalph\">$F_CMCNA2</td></tr>";
        print "<tr><td>&nbsp;</td><td>&nbsp;</td>";     
     }  
     if (trim($F_CMCNA3) != "") {
        print "\n <tr><td>&nbsp;</td><td>&nbsp;</td><td class=\"dspalph\">$F_CMCNA3</td></tr>";
        print "<tr><td>&nbsp;</td><td>&nbsp;</td>";     
     }
     if (trim($F_CMCNA4) != "") {
        print "\n <tr><td>&nbsp;</td><td class=\"dspalph\">$F_CMCNA4</td></tr>";
        print "<tr><td>&nbsp;</td><td>&nbsp;</td>";     
     }
     print "\n <td class=\"dspalph\">$F_CMCCTY,&nbsp;&nbsp;$F_CMST&nbsp;&nbsp;$F_CMZIP</td></tr>"; 
     print "\n <tr><td>&nbsp;</td></tr>";
     print "\n <tr><td class=\"hdrtitl\">Class:</td><td class=\"hdrdata\">$F_CMCCLS</td><td class=\"dspalph\">$custClassDesc</td class=\"dspalph\"</tr>";
     print "\n <tr><td class=\"hdrtitl\">Terms:</td><td class=\"hdrdata\">$F_CMCTRM</td><td class=\"dspalph\">$F_TMCTDS</td></tr></table>";
     }
  }

/*
if ($priceLevel) {
  
  // Set up the $edtVar for the program call
  $edtVar = "";
  Concat_Field("@@cust", $custNumber);
  Concat_Field("@@item", $itemNumber);
  // Concat_Field("@@whse", $primaryWhse);
  Concat_Field("@@rqty", $reqQty);
  Concat_Field("@@pric", $price);
  $edtVar .= "}{";

  $returnVar = Rtv_Price_By_Cust_Item_Qty($profileHandle, $edtVar);

  $price = DeCat_Field("@@pric", $returnVar);
  
     print "<table align=\"none\" hspace=\"30\" frame=\"box\" cellspacing=\"1\" cellpadding=\"0\">";
     print "\n <tr><td colspan=\"100%\" bgcolor=\"#000000\" p style=\"color:#FFFFFF\" align=\"center\"><b>Price * Quantity requestd</b></td></tr>";
     print "\n <tr><td colspan=\"100%\" bgcolor=\"#FFFFFF\" p style=\"color:#FFFFFF\">&nbsp</td></tr>";

     print "\n <tr><td class=\"hdrtitl\">Quantity Requested:</td><td class=\"dspnmbr\">$reqQty</td>";
     print "\n <tr><td class=\"hdrtitl\">Calculated Price:</td><td class=\"dspalph\">$price</td></tr>";

     print "\n <tr><td>&nbsp;</td><td>&nbsp;</td>";
     print "\n <tr><td>&nbsp;</td><td class=\"hdrdata\">$price * $reqQty</td></tr>";
     print "\n <tr><td>$edtvar</td></tr></table>";     
 }
*/

// Display Customer/Item History...

if (!empty($custNumber)) {
   $rowCount = 0;
   $stmtSQL = "";
   $stmtSQL .= " Select HHORTY, HHBDTE, DHORD#, DHORL#, DHORCS, DHQORD, DHLIST, DHSLPR ";
   $stmtSQL .= " From OEORDH Left Outer Join OEORHH on DHORD# = HHORD# And DHSEQ# = HHSEQ# ";
   $stmtSQL .= " Where HHSHTO = '$custNumber' And DHITEM = '$itemNumber' And DHSEQ# = 0 ";
   $stmtSQL .= " Order By HHBDTE DESC Fetch First 10 rows only ";
   $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);
   
   while($row = db2_fetch_assoc($sqlResult)) {
     require 'SetRowClass.php';
     if ($custItemHistHdrs != "Displayed"){
        print "<table align=\"left\" hspace=\"30\" frame=\"box\" cellspacing=\"1\" cellpadding=\"0\">";
        print "\n <tr><td colspan=\"100%\" bgcolor=\"#000000\" p style=\"color:#FFFFFF\" align=\"center\"><b>Customer / Item Order History</b></td></tr>";
       // print "\n <tr><td colspan=\"100%\" bgcolor=\"#FFFFFF\" p style=\"color:#FFFFFF\">&nbsp</td></tr>";

        // Write secondary headers for Cust/Item Order History contents...
        print "\n <tr>";
        print "\n <th class=\"colhdr\">Order<br>Type</th>";
        print "\n <th class=\"colhdr\">Order<br>Date</th>";
        print "\n <th class=\"colhdr\">Order<br>Number</th>";
        print "\n <th class=\"colhdr\">Order<br>Line</th>";
        print "\n <th class=\"colhdr\">Customer<br>Item#</th>";
        print "\n <th class=\"colhdr\">Qty<br>Ordered</th>";
        print "\n <th class=\"colhdr\">List<br>Price</th>";
        print "\n <th class=\"colhdr\">Selling<br>Price</th>";
        print "\n </tr>";
        $custItemHistHdrs = "Displayed";  }

     print "\n <tr>";
     print "\n <td class=\"colcode\">" .$row['HHORTY']. "</td>";
     print "\n <td class=\"colcode\">" .Format_Date($row['HHBDTE'],"D"). "</td>";
     print "\n <td class=\"colcode\">" .$row['DHORD#']. "</td>";
     print "\n <td class=\"colcode\">" .Format_Nbr($row['DHORL#'], '0', $amtEditCode, 'Y', '', ''). "</td>";
     print "\n <td class=\"colcode\">" .$row['DHORCS']. "</td>";
     print "\n <td class=\"colcode\">" .Format_Nbr($row['DHQORD'], '0', $amtEditCode, 'Y', '', ''). "</td>";
     print "\n <td class=\"colcode\">" .Format_Nbr($row['DHLIST'], '2', $amtEditCode, 'Y', '', ''). "</td>";
     print "\n <td class=\"colcode\">" .Format_Nbr($row['DHSLPR'], '2', $amtEditCode, 'Y', '', ''). "</td>";
     print "\n </tr>";
     $rowCount ++;
   }
   print "\n </table>";
   
}     

?>
