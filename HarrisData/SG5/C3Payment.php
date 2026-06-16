<?php
require_once 'GetURLParm.php';
require_once 'C3Config.php';
$parms = "?baseVar=" . $altBaseVar;
$parms .= "&eID=" . $_GET['eID'];
$parms .= "&portal=" . $_GET['portal'];
$parms .= "&customerNumber=" . $_GET['customerNumber'];
$parms .= "&customerName=" . $_GET['customerName'];
$parms .= "&orderControlNumber=" . $_GET['orderControlNumber'];
$parms .= "&check=" . $_GET['check'];
$parms .= "&memo=" . $_GET['memo'];

/**
 * OPTIONAL
 * Most of the following code is not strictly necessary for integration with PLP, and more or less just
 * simulates the processing that would occur in a generic shopping cart.
 * The only reason why it is "necessary" in this context is because we use it set the values of certain
 * variables (like $MFMRCH, for example) that are referenced further below in the "PLP Critical Code" section.
 */

// set the displayed version number for this file
$pagevers = '1.7a';


if (array_key_exists('MFMRCH', $_GET)) {
    $MFMRCH = $_GET['MFMRCH'];
} else {
    $MFMRCH = 99998;
}

if (array_key_exists('MFTYPE', $_GET) && in_array($_GET['MFTYPE'], array('RA', 'RY'))) {
    $MFTYPE = $_GET['MFTYPE'];
} else {
    $MFTYPE = 'RA';
}

if (array_key_exists('MFTYP2', $_GET) && in_array($_GET['MFTYPE'], array('PA', 'SA'))) {
    $MFTYP2 = $_GET['MFTYP2'];
} else {
    $MFTYP2 = 'PA';
}

if (array_key_exists('MFORDR', $_GET) && !empty($_GET['MFORDR'])) {
    $MFORDR = $_GET['MFORDR'];
} else {
    $MFORDR = 0;
}

if (array_key_exists('MFAMT1', $_GET)) {
    $MFAMT1 = $_GET['MFAMT1'];
} else {
    $MFAMT1 = 0;
}

if (array_key_exists('MFAMT2', $_GET)) {
    $MFAMT2 = $_GET['MFAMT2'];
} else {
    if ($MFAMT1<100) {
        $MFAMT2 = 1;
    } else {
        $MFAMT2 = $MFAMT1*.01;
    }
}

if (array_key_exists('MFNAME', $_GET) && !empty($_GET['MFNAME'])) {
    $MFNAME = $_GET['MFNAME'];
} else {
    $MFNAME = '';
}

if (array_key_exists('MFADD1', $_GET) && !empty($_GET['MFADD1'])) {
    $MFADD1 = $_GET['MFADD1'];
} else {
    $MFADD1 = '';
}

if (array_key_exists('MFADD2', $_GET) && !empty($_GET['MFADD2'])) {
    $MFADD2 = addslashes($_GET['MFADD2']);
} else {
    $MFADD2 = '';
}

if (array_key_exists('MFCITY', $_GET) && !empty($_GET['MFCITY'])) {
    $MFCITY = $_GET['MFCITY'];
} else {
    $MFCITY = '';
}

if (array_key_exists('MFSTAT', $_GET) && !empty($_GET['MFSTAT'])) {
    $MFSTAT = $_GET['MFSTAT'];
} else {
    $MFSTAT = '';
}

if (array_key_exists('MFZIPC', $_GET) && !empty($_GET['MFZIPC'])) {
    $MFZIPC = $_GET['MFZIPC'];
} else {
    $MFZIPC = '';
}

if (array_key_exists('MFDSTZ', $_GET) && !empty($_GET['MFDSTZ'])) {
    $MFDSTZ = $_GET['MFDSTZ'];
} else {
    $MFDSTZ = '';
}

if (array_key_exists('MPCUST', $_GET) && !empty($_GET['MPCUST'])) {
    $MPCUST = $_GET['MPCUST'];
} else {
    $MPCUST = 0;
}

if (array_key_exists('MFREFR', $_GET) && !empty($_GET['MFREFR'])) {
    $MFREFR = $_GET['MFREFR'];
} else {
    $MFREFR = '';
}

if (array_key_exists('MPCUSF', $_GET) && !empty($_GET['MPCUSF'])) {
    $MPCUSF = $_GET['MPCUSF'];
} else {
    $MPCUSF = '';
}

$MFCUST = $curbstoneCustomerNumber;

if (array_key_exists('target', $_GET) && !empty($_GET['target'])) {
    $MPTRGT = $_GET['target'];
} else {
    $MPTRGT = $target_url;
}
/**
 * END OPTIONAL CODE
 */


/********************************************************/
/********************************************************/

/*             START PLP CRITICAL CODE                  */

/********************************************************/
/********************************************************/

// Specify the url of the PLP initialization endpoint
$PLP_API_URL = $plp_api_url;

// Specify all non-card data that will be transmitted from the client's server.
// Also, specify a target URL for PLP to return the results of the transaction to the client’s server.
$payload = array(
    'MFCUST' => $MFCUST, // Default or keyed value
    'MFMRCH' => $MFMRCH, // 00002, 00012, 00022
    'MFTYPE' => $MFTYPE, // RA or RY
    'MFTYP2' => $MFTYP2, // PA = pre-auth. SA = sale.
    'MFMETH' => '02',
    'MFORDR' => $MFORDR,
    'MFAMT1' => $MFAMT1,
    'MFNAME' => $MFNAME,
    'MFADD1' => $MFADD1,
    'MFADD2' => $MFADD2,
    'MFCITY' => $MFCITY,
    'MFSTAT' => $MFSTAT,
    'MFZIPC' => $MFZIPC,
    'MFDSTZ' => $MFDSTZ,
    'MPCUST' => $MPCUST, // formerly CUST
    'MFLTXF' => '1',
    'MFAMT2' => $MFAMT2,
    'MFREFR' => $MFREFR,
    'MPTRGT' => $target_url, // formerly TARGET. Max 500 chars
    // 'MFUSD1' => '1',
    // 'MFUSD2' => '2',
    // 'MFUSD3' => '3',
    // 'MFUSD4' => '44',
    // 'MFUSD5' => '7777777',
    // 'MFUSD6' => '1010101010',
    // 'MFUSD7' => '1616161616161616',
    // 'MFUSD8' => '2626262626262626',
    // 'MFUSD9' => '', // Client cannot set this field since we will use it to hold the session token
    // 'MFUSDA' => 1111122222333,
    // 'MFUSDB' => 2222233333444,
    // 'MFUSDC' => 3333344444555,
    'MPCUSF' => $parms // Custom query string. Max 300 chars
);

// Format the payload for transmission
$payload_string = '';
foreach ($payload as $key => $value) {
    $payload_string .= $key . '=' . urlencode($value) . '&';
}
rtrim($payload_string, '&');

// Open a connection with the PLP endpoint
$ch = curl_init();

// Configure the POST request
curl_setopt($ch, CURLOPT_URL, $PLP_API_URL . '?action=init');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_string);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Remove line or change value to 'true' when running in production

// Execute the POST and capture the response
$result = curl_exec($ch);

if ($result == false) {
    echo 'Curl error #: ' . curl_errno($ch) . "<br>";
    echo 'Curl error: ' . curl_error($ch) . "<br>";
}

// Close the connection
curl_close($ch);

$result = json_decode($result, true);

// Demonstrates basic success/error checking
switch ($result['MFRTRN']) {

    case 'UG':
        if (!array_key_exists('MFSESS', $result)) {
            die('Transaction session token was not returned');
        }
        break;

    case 'UL':
    default:
        if (is_array($result)) {
            // Single-character error code
            if (array_key_exists('MFATAL', $result)) {
                echo 'MFATAL ERROR CODE: ' . $result['MFATAL'] . '<br>';
            }

            // Human-readable error message
            if (array_key_exists('MFRTXT', $result)) {
                echo 'MFATAL ERROR MESSAGE: ' . $result['MFRTXT'] . '<br>';
            }
        }

        die('Transaction initialization failed <br><br> Payload:<br>' . print_r($payload, true));
        exit;
}

// Extract the transaction session token from the JSON payload
$transaction_token = $result['MFSESS'];

// Build the URL to point to the PLP user interface.
// This will be embedded in the HTML of the web page.
$PLP_TXN_URL = $PLP_API_URL . '?MFSESS=' . $transaction_token;

// Redirect instead of rendering the page if operating in "non-iframe" mode
if (array_key_exists('mode', $_GET) && $_GET['mode'] == 'redirect') {
    header('Location: ' . $PLP_TXN_URL . '&mode=redirect');
    exit;
}
