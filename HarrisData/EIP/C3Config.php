<?php
// Curbstone CorrectConnect
// Payment Landing Pages "PLP" Client Configuration file
// This is part of the demo code for the reference PLP client side code
// Comment lines in this file are preceded by the semicolon ";"
// //ttps://curb911.com   Support Site
// 888-844-8533

// do not change
$version=1.7;

// Client base url
// This must be the working folder for the shopping cart
// from which the included "index.php" is executed.
// Trailing/ slash/ is/ required/

// SHOPPING CART URL
$base_url = "https://curbstone.com/plp/";

// URL pointing to PLP installation
// This will be provided by Curbstone for your implementation
// In the demo, the URL has to be
//     https://c3plp.net/curbstone/plp/

// C3 PORTAL URL
$plp_api_url = "https://c3plp.net/curbstone/plp/";

// URL to which control is returned - this must be a program
// that can accept the POSTed data from the C3 portal and
// parse it.  Change this to match the program in YOUR Shopping
// Cart that will receive the response from the C3 server as to
// the success or failure of the authorization/storage request.
// This must be a fully-qualified URL that includes the filename
// of the receiving program, unless it is "index.php", in which
// case it must end/ with/ a/ trailing/ slash/!

// SHOPPING CART URL
$target_url = $homeURL . "/harris-cgi/OrderEntryPayment.d2w/PAY";
?>	
