<?php
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
require_once 'ToolkitService.php';

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
              <td class="hdrdata"><?php if ($itemNumber) { print "<a href=\"{$homeURL}/harris-CGI/ItemSearch.php/REPORT?baseVar=BaseConfiguration.icl&amp;portal=Item&amp;ItemDesc=$itemDescription&amp;itemNumber=$itemNumber\" target=\"_blank\"</a><title=\"View Item $itemNumber\">" .Trim($itemNumber). "</a>"; } ?> 
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
 
	    
  // End the Availability reporting so the next "group" of data can be aligned to the right...
  print "\n </table>"; 

}     
