<?php

require_once 'GetURLParm.php';    // REQUIRED ...or SetLibraryList.php won't work properly.
// require_once 'CopyrightBanner.php';
// require_once 'Copyright_ProITRG.php';

// require_once 'SetLibraryList_ProITRG.php';  // Test to verify $libraryList; doesn't interrupt production.
require_once 'SetLibraryList.php';
require_once 'EditRoutines.php';
require_once 'EdtVar.php';
require_once 'GenericDirectCallVariables.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';
require_once 'WildCard.php';



$page_title     = "Pricing Inquiry*";
$scriptName     = "SG_Price_Inquiry_Screen_CM.php";

$programName    = "HOEPRC";


require_once 'Menu.php';
require_once ($docType);
print  "<html> <head>";
require_once ($headInclude);
print "\n <script TYPE=\"text/javascript\">";
require_once 'NewWindowOpen.php';
require_once 'Menu.js';
print "\n </script>";
require_once ($genericHead);
print  "\n </head> <body $bodyTagAttr>";
require_once 'Banner.php';
print  "\n <table $baseTable>  <tr valign=\"top\">";
$formatToPrint = "";
$pageID = "";
require_once 'MenuDisplay.php';
print  "\n <td class=\"content\">";

// define variables and set to empty values
$custNumberErr = $itemNumberErr = $qtyErr = "";
$custNumber = $itemNumber = $reqQty = "";

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   if (empty($_POST["custNumber"]))
     {$custNumberErr = "Customer Number is required";}
   else
     {$custNumber = test_input($_POST["custNumber"]);}
  
   if (empty($_POST["itemNumber"]))
     {$itemNumberErr = "Item Number is required";}
   else
     {$itemNumber = test_input($_POST["itemNumber"]);}
    
   if (empty($_POST["reqQty"]))
     {$qtyErr = "Quantity is required";}
   else
     {$reqQty = test_input($_POST["reqQty"]);}
}

function test_input($data)
{
     $data = trim($data);
     $data = stripslashes($data);
     $data = htmlspecialchars($data);
     return $data;
}
?>




<h1>Price Inquiry</h1>
<!-- // Display an empty form for the user to fill in... -->
<!-- // Testing isset -->
<form name="PriceInq" id="PriceInq" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
Customer Number:&nbsp;&nbsp;<input type="text" id="custNumber" name="custNumber" size="7"
         value="<?php echo $custNumber;?>"><span class="error"><?php echo $custNumberErr;?></span>
          <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Item Number:&nbsp;&nbsp;<input type="text" id="itemNumber" name="itemNumber" size="15" 
         value="<?php echo $itemNumber;?>"><span class="error"><?php echo $itemNumberErr;?></span>
         <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Quantity:&nbsp;&nbsp;&nbsp;<input type="text" id="reqQty" name="reqQty" size="15" value="<?php echo $reqQty;?>"> 
         <span class="error"><?php echo $qtyErr;?></span><br/>
         <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <input type="submit" name="submit" value="Submit"><br><br>
</form>




<?php 

// If data is submitted, validate both fields have values...
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

  // Quantities extracted below are as follows...
  // IWOHQT = Quantity On Hand
  // IWQOO  = Quantity On Order (for Purchased Items Only)
  // IPQMRL = Manufacturing Order Quantity Released/Scheduled
  // IWRESQ = Quantity Reserved (Customer Orders)
  // IWQOPK = Quantity on Pick Tickets
  // IPCMTO = Quantity Committed to Manufacturing    

  // If there are no errors apparent, process the form data...
  $stmtSQL = "";
  $stmtSQL .= " Select IPPLT, IWWHS, IPITEM, IMIMDS, IMPTYP, IWOHQT, IWQOO, IPQMRL, IWRESQ, IWQOPK, IPCMTO ";
  $stmtSQL .= " From HDIPLT ";
  $stmtSQL .= " Left Outer Join HDIMST on IPITEM = IMITEM ";
  $stmtSQL .= " Left Outer Join HDIWHS on IPITEM = IWITEM ";
  $stmtSQL .= " Where IPITEM = '$itemNumber' ";
  $stmtSQL .= " Order By IPITEM, IPPLT, IWWHS ";

  $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
  
  $startRow = 0;
  while ($row = db2_fetch_assoc($sqlResult, $startRow)){
	   require  'SetRowClass.php';

         $F_IPPLT = $row['IPPLT'];
         $F_IPITEM= $row['IPITEM'];
         $F_IMIMDS= $row['IMIMDS'];
         $F_IMPTYP= $row['IMPTYP'];
         $F_IWOHQT= $row['IWOHQT'];
         $F_IWQOO = $row['IWQOO'];
         $F_IPQMRL= $row['IPQMRL'];
         $F_IWRESQ= $row['IWRESQ'];
         $F_IWQOPK= $row['IWQOPK'];
         $F_IPCMTO= $row['IPCMTO'];

		 $itemLink = '<a href="/harris-CGI/ItemInquiry.d2w/DISPLAY?baseVar=BaseConfiguration.icl&portal=ITEM&eID='.$eID.'&itemNumber='.$row['IPITEM'].' ">'.$itemNumber.'</a>';
         // Print header info (Item, Item Description, and eventually show the image)...  
	   
         if ($startRow == 0){	
            // echo "StartRow = $startRow<br/>";
	      print "<table border=\"1\" cellspacing=\"1\" cellpadding=\"5\">";
            print "\n <tr class=\"$rowClass\">";
            print "\n <td class=\"colalph\">Item:&nbsp;&nbsp;" .$itemLink. "</td>";
 	      print "\n <td class=\"colalph\">Description:&nbsp;&nbsp;" .$row['IMIMDS']. "</td></tr></table></br></br>";
            $F_IWWHS = $row['IWWHS'];
            $startRow ++;
            }

         // Write secondary headers (Plant, Warehouse)...
	   print "<table border=\"1\" cellspacing=\"1\" cellpadding=\"5\">";
	   print "\n <tr><td class=\"colalph\">Plant:&nbsp;&nbsp;" .$row['IPPLT']. "</td>";
	   print "\n <td class=\"colnmbr\">Whse:&nbsp;&nbsp;" .$row['IWWHS']. "</td></tr>";


         // Write details for this Item's Plant & Warehouse (Qty Available, Qty Committed to MOs, Qty on COs)... 
         print "\n <tr><td class=\"colnmbr\">Qty on Hand:&nbsp;&nbsp;" . Format_Nbr ( $row['IWOHQT'], '1', $amtEditCode, 'N','', ''). "</td>";
         $QtyAvail = $row['IWOHQT'] + $row['IWQOO'] + $row['IPQMRL'] - $row['IWRESQ'] - $row['IWQOPK'] - $row['IPCMTO'];
         print "\n <td class=\"colnmbr\">Qty Available:&nbsp;&nbsp;" . Format_Nbr ( $QtyAvail, '1', $amtEditCode, 'N', '', ''). "</td></tr>";
         print "\n <tr><td class=\"colnmbr\">Qty Committed</br>To Mfg. Orders:&nbsp;&nbsp;" .Format_Nbr($row['IPCMTO'], '1', $amtEditCode, 'N', '', ''). "</td>";
         print "\n <td class=\"colnmbr\">Qty Scheduled</br>To Manufacture:&nbsp;&nbsp;" .Format_Nbr($row['IPQMRL'], '1', $amtEditCode, 'N', '', ''). "</td></tr>";
         print "\n <tr><td class=\"colnmbr\">Qty on Pick Tickets:&nbsp;&nbsp;" .Format_Nbr($row['IWQOPK'], '1', $amtEditCode, 'N', '', ''). "</td>";
         print "\n <td class=\"colnmbr\">Qty on Customer Orders:&nbsp;&nbsp;" .Format_Nbr($row['IWRESQ'], '1', $amtEditCode, 'N', '', ''). "</td></tr></table></br></br>";

         $startRow ++;
 }


// If the item isn't found, let the user know the item doesn't exist...
if (is_null($F_IMIMDS) AND !empty($itemNumber)) {
   print "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\">";
   print "<tr><td><span class=\"error\">*** Item $itemNumber does not exist. ***</span></td></tr>";
   print "</table><br><br>";
}


if (!empty($custNumber)) {
   // Pull customer information for display...
   $stmtSQL = "";
   $stmtSQL .= " Select CMBLTO, CMCNA1, CMCNA2, CMCNA3, CMCNA4, CMCCTY, CMST, CMZIP, CMCTRY, CMCCLS, CMPHON, CMCCRL, CMSLSM, ";
   $stmtSQL .= " CMALSM, CMDFPO, CMDLPO, CMDLPY From HDCUST Where CMCUST = '$custNumber' ";
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

     print "<table border=\"1\" cellspacing=\"1\" cellpadding=\"5\">";
     echo "<tr><td class=\"colalph\">Customer:</td><td class=\"colalph\">$custNumber</td></tr>\n";
     echo "<tr><td class=\"colalph\">Name:</td><td class=\"colalph\">$F_CMCNA1</td></tr>\n";
     echo "<tr><td class=\"colalph\">Address 1:</td><td class=\"colalph\">$F_CMCNA2</td></tr>\n";
     echo "<tr><td class=\"colalph\">Address 2:</td><td class=\"colalph\">$F_CMCNA3</td></tr>\n";
     echo "<tr><td class=\"colalph\">Address 3:</td><td class=\"colalph\">$F_CMCNA4</td></tr>\n"; 
     echo "<tr><td class=\"colalph\">City/State/Zip:</td><td class=\"colalph\">$F_CMCCTY,&nbsp;&nbsp;$F_CMST&nbsp;&nbsp;$F_CMZIP</td></tr>\n"; 
     echo "<tr><td class=\"colalph\">Class:</td><td class=\"colalph\">$F_CMCCLS</td></tr></table></br></br>\n";
     }
   
   // If the customer number was specified and no name was found, notify the user...
   if (is_null($F_CMCNA1)) {
      print "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\">";
      print "<tr><td>*** Customer $custNumber does not exist. ***</td></tr>";
      print "</table><br><br>";
   }
 }


    


  //****************************************************************************************************// 
  // Per Bill Busch on March 28, 2014:                                                                  //
  // If they do qualify a customer number, find the relative customer class in price level 10.          //
  // If the customer class is not found, then look at level 70 for a price.                             //
  //****************************************************************************************************// 
  
  // Extract/Display pricing table...
  $todayCYMD = (date('Y') >= 2000? 1 : 0) . date('ymd');
  $stmtSQL = "";
  $startRow = 0;
  
  If (!empty($custNumber)) {
     $stmtSQL .= " Select PMPMLV, PMPMKY, PMSTDT, PMEXDT, PMLCAM, PBSTDT, PBLMT, PBPRC ";
     $stmtSQL .= " From HDPRCD ";
     $stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY And PMSTDT = PBSTDT ";
     $stmtSQL .= " Where PMWHS = '$F_IWWHS' And PMITEM = '$itemNumber' And PMCCLS = '$F_CMCCLS' ";

     $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
     
     while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
        $startRow ++;
     }
     
     if ($startRow > 0) {

        // There's data to be displayed for this customer, customer class, and warehouse...
        $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
        
        $startRow = 0;
        while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
              require 'SetRowClass.php';
              // Print header info (Item, Item Description, and eventually show the image)...  
	        if ($startRow == 0) {
	           print "<table border=\"1\" cellspacing=\"1\" cellpadding=\"5\">";
	           print "\n <tr class=\"$rowClass\">";
                 
                 if (is_null($row['PBLMT'])) {
                    print "\n <td class=\"colalph\">Level</td><td class=\"colalph\">Start Date</td><td class=\"colalph\">End Date</td><td class=\"colalph\">Price</td></tr>"; }
                 else {
                    print "\n <td class=\"colalph\">Level</td><td class=\"colalph\">Start Date</td><td class=\"colalph\">End Date</td><td class=\"colalph\">Limit</td><td class=\"colalph\">Price</td></tr>"; 
                 }
              } 
              
              if (is_null($row['PBLMT'])) {
                 print "\n <td class=\"colalph\">" .$row['PMPMLV']. "</td><td class=\"colalph\">" .Date_CYMD_ISO($row['PMSTDT']). "</td><td class=\"colalph\">" .Date_CYMD_ISO($row['PMEXDT']). "</td><td class=\"colalph\">$" . Format_Nbr ( $row['PMLCAM'], '2', $amtEditCode, 'Y', '', ''). "</td></tr>"; }  
              else {
                 print "\n <td class=\"colalph\">" .$row['PMPMLV']. "</td><td class=\"colalph\">" .Date_CYMD_ISO($row['PMSTDT']). "</td><td class=\"colalph\">" .Date_CYMD_ISO($row['PMEXDT']). "</td><td class=\"colalph\">" .$row['PBLMT']. "</td><td class=\"colalph\">$" . Format_Nbr ( $row['PBPRC'], '2', $amtEditCode, 'Y', '', '').  "</td></tr>";
              }
         }
         $startRow ++;
         
      if ($startRow > 0) {
         print "</table>"; }   
      }
    }
  }

 if (($startRow == 0) or (empty($custNumber))) {

    // Pull standard pricing (Pricing Level 70)...
    $stmtSQL = "";
    $stmtSQL .= " Select PMPMLV, PMPMKY, PMSTDT, PMEXDT, PBLMT, PBPRC ";
    $stmtSQL .= " From HDPRCD ";
    $stmtSQL .= " Left Outer Join HDPRCB on PMPMLV = PBPMLV And PMPMKY = PBPMKY ";
    $stmtSQL .= " Where PMITEM = '$itemNumber' And PMPMLV = 70";
    
    $sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL, array('cursor' => DB2_SCROLLABLE) );
    
    $startRow = 0;
    while ($row = db2_fetch_assoc($sqlResult, $startRow)) {
       require  'SetRowClass.php';
	 
       if ($startRow == 0) {
          // Print header info (Item, Item Description, and eventually show the image)...  
  	    print "<table border=\"1\" cellspacing=\"1\" cellpadding=\"5\">";
	    print "\n <tr class=\"$rowClass\">";
          print "\n <td class=\"colalph\">Level</td><td class=\"colalph\">Start Date</td><td class=\"colalph\">End Date</td><td class=\"colalph\">Price</td></tr>"; }
       else {
          print "\n <tr><td class=\"colalph\">" .$row['PMPMLV']. "</td><td class=\"colalph\">" .Date_CYMD_ISO($row['PMSTDT']). "</td><td class=\"colalph\">" .Date_CYMD_ISO($row['PMEXDT']). "</td><td class=\"colalph\">$" . Format_Nbr ($row['PBPRC'], '2', $amtEditCode, 'Y', '', '') . "</td></tr>";
          }
    $startRow ++; 
    }
 }
print  "</td> </tr> </table>";
?>

<script>
defaultSchLtr = "<?php echo count($schLtrList) === 1 ? $schLtrList[0] : null; ?>";
defaultRub    = "<?php echo count($rubList) === 1 ? $rubList[0] : null; ?>";
defaultRqDateFld = "<?php echo count($rqdateList) === 1 ? Format_Date($rqdateList[0],'D') : null; ?>";

$(document).ready(function() {
    setDefaultSchLtr();
    setDefaultRub();
    setDefaultRqDateFld();
});
</script>
<?php
require_once 'Trailer.php';
if ($alertMessage){
	print "\n <script>alert(\"$alertMessage\")</script>";
	$alertMessage = "";
}
print  "</body> </html>";
?>