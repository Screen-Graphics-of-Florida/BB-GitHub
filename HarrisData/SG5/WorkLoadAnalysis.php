<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

$forPlant = (isset ( $_GET ['forPlant'] )) ? $_GET ['forPlant'] : 0;
$forDept = (isset ( $_GET ['forDept'] )) ? $_GET ['forDept'] : '';
$forWC = (isset ( $_GET ['forWC'] )) ? $_GET ['forWC'] : '';
$startDate = (isset ( $_GET ['startDate'] )) ? $_GET ['startDate'] : '';
$endDate = (isset ( $_GET ['endDate'] )) ? $_GET ['endDate'] : '';
$backHome = (isset ( $_GET ['backHome'] )) ? $_GET ['backHome'] : '';

require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

require_once 'EditRoutines.php';
require_once 'Menu.php';
require_once 'NewWindowVariables.php';
require_once 'VarBase.php';

$page_title = "Work Load Analysis";
$scriptName = "WorkLoadAnalysis.php";
$scriptVarBase = "{$genericVarBase}&amp;forPlant=" . urlencode ( trim ( $forPlant ) ) . "&amp;forDept=" . urlencode ( trim ( $forDept ) ) . "&amp;forWC=" . urlencode ( trim ( $forWC ) ) . "&amp;startDate=" . urlencode ( trim ( $startDate ) ) . "&amp;endDate=" . urlencode ( trim ( $endDate ) ) . "&amp;backHome=" . urlencode ( trim ( $backHome ) );
$baseURL = "{$homeURL}{$phpPath}{$scriptName}{$scriptVarBase}";
$_SESSION [$fromURL] = $baseURL;

$maxPers = 50;
$howManyPers = 0;
$howManyDays = 0;
$howManyWks = 0;
$howManyMos = 0;
$periods = array ();

// *****************************************************************************
// Retrieve the Work Center data
// *****************************************************************************
$stmtSQL = "";
$stmtSQL .= " Select  HDMWCM.*, coalesce(PLNAME,'') as PLNAME, coalesce(PLSCHD,0) as PLSCHD, coalesce(s1.SMDESC,s2.SMDESC,'') as SMDESC, coalesce(WXWDPD,0) as WXWDPD, coalesce(WXWWPD,0) as WXWWPD ";
$stmtSQL .= " From HDMWCM ";
$stmtSQL .= " Left Join HDPLNT on WCPLT=PLPLNT ";
$stmtSQL .= " Left Join HDSCHM s1 on WCSCHD=s1.SMSCHD and s1.SMEFFS is null ";
$stmtSQL .= " Left Join HDSCHM s2 on PLSCHD=s2.SMSCHD and s2.SMEFFS is null ";
$stmtSQL .= " Left Join HDMWCX on WCPLT=WXPLT and WCDEPT=WXDEPT and WCWC=WXWC ";
$stmtSQL .= " Where WCPLT=$forPlant and WCDEPT='$forDept' and WCWC='$forWC' ";
$stmtSQL .= " Fetch First 1 Row Only With NC";
$sqlResult = db2_exec ( $i5Connect->getConnection (), $stmtSQL );
$rowWC = db2_fetch_assoc ( $sqlResult );

$howManyDays = $rowWC ['WXWDPD'] * 7;
$howManyWks = $rowWC ['WXWWPD'];
$howManyMos = $maxPers - $howManyDays - $howManyWks;
// print_r($howManyDays);
// print_r($howManyWks);
// print_r($howManyMos);
// exit ();
if ($howManyDays == 0 and $howManyWks == 0) {
	$howManyMos = 0;
	// *****************************************************************************
	// Retrieve the Capacity Periods
	// *****************************************************************************
	$stmtSQL = "";
	$stmtSQL .= " Select  PEPER, PETYP ";
	$stmtSQL .= " From CPMPER ";
	$stmtSQL .= " Where PEPLT=$forPlant ";
	$stmtSQL .= " Order By PEPER ";
	$dspMaxRows = $maxPers;
	require 'stmtSQLEnd.php';
	$sqlResultMPER = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
	$rowCount = 0;
	while ( $rowMPER = db2_fetch_assoc ( $sqlResultMPER, $startRow ) ) {
		if ($rowCount >= $maxPers) {
			break;
		}
		if ($rowMPER ['PETYP'] == 'D') {
			$howManyDays ++;
		} elseif ($rowMPER ['PETYP'] == 'W') {
			$howManyWks ++;
		} elseif ($rowMPER ['PETYP'] == 'M') {
			$howManyMos ++;
		}
		$startRow ++;
		$rowCount ++;
	}
}
// print_r($howManyDays);
// print_r($howManyWks);
// print_r($howManyMos);
// exit ();
$output_format = 'Y-m-d';
$start = strtotime ( $startDate );
$limit = strtotime ( $endDate );
for($d = 0; $d < $howManyDays and $start <= $limit; $d ++) {
	$end = $start;
	// $periods[] = array('start' => date($output_format, $start),'end' => date($output_format, $end));
	$periods [] = array ('start' => $start, 'end' => $end, 'startf' => date ( $output_format, $start ), 'endf' => date ( $output_format, $end ) );
	// $periods [] = array ('start' => $start, 'end' => $end );
	$start = strtotime ( '+1 day', $end );
}
for($w = 0; $w < $howManyWks and $start <= $limit; $w ++) {
	$end = strtotime ( '+6 day', $start );
	// $periods[] = array('start' => date($output_format, $start),'end' => date($output_format, $end));
	$periods [] = array ('start' => $start, 'end' => $end, 'startf' => date ( $output_format, $start ), 'endf' => date ( $output_format, $end ) );
	// $periods [] = array ('start' => $start, 'end' => $end );
	$start = strtotime ( '+1 day', $end );
}
for($m = 0; $m < $howManyMos and $start <= $limit; $m ++) {
	$end = strtotime ( '+27 day', $start );
	// $periods[] = array('start' => date($output_format, $start),'end' => date($output_format, $end));
	$periods [] = array ('start' => $start, 'end' => $end, 'startf' => date ( $output_format, $start ), 'endf' => date ( $output_format, $end ) );
	// $periods [] = array ('start' => $start, 'end' => $end );
	$start = strtotime ( '+1 day', $end );
}
$howManyPers = count ( $periods );
// print_r ( $periods );
// exit ();
// *****************************************************************************
// Retrieve the Work Center Load
// *****************************************************************************
$stmtSQL = "";
$stmtSQL .= " Select  WLDATE, WLLCAP, WLLLHR, WLMCAP, WLMLHR ";
$stmtSQL .= " From HDWCLD ";
$stmtSQL .= " Where WLPLT=$forPlant and WLDEPT='$forDept' and WLWC='$forWC' ";
$stmtSQL .= " and WLDATE between '$startDate' and '$endDate' ";
$stmtSQL .= " Order By WLDATE ";
$dspMaxRows = $maxPers;
require 'stmtSQLEnd.php';
$sqlResultWCLD = db2_exec ( $i5Connect->getConnection (), $stmtSQL, array ('cursor' => DB2_SCROLLABLE ) );
$startRow = 1;
$rowCount = 0;
while ( $rowWCLD = db2_fetch_assoc ( $sqlResultWCLD, $startRow ) ) {
// 	if ($rowCount >= $howManyPers) {
// 		break;
// 	}
	$loadDate = strtotime ( $rowWCLD ['WLDATE'] );
	foreach ( $periods as &$period ) {
		// if ($loadDate < $period ['start'] or $loadDate > $period ['end']) {
		// continue;
		// }
		if ($loadDate >= $period ['start'] and $loadDate <= $period ['end']) {
			$period ['lcap'] += $rowWCLD ['WLLCAP'];
			$period ['llhr'] += $rowWCLD ['WLLLHR'];
			$period ['mcap'] += $rowWCLD ['WLMCAP'];
			$period ['mlhr'] += $rowWCLD ['WLMLHR'];
			break;
		}
	}
	
unset ( $period );
	$startRow ++;
	$rowCount ++;
}
// print_r ( $periods );
// exit ();
// *****************************************************************************
// Build the Chart Data
// *****************************************************************************
$chartData [] = array ('Date', '% of Labor Capacity', '% of Machine Capacity' );
foreach ( $periods as $period ) {
	$f_date = Format_Date_ISO ( $period ['startf'], 'D' );
	$lcap = isset ( $period ['lcap'] ) ? $period ['lcap'] : 0;
	$llhr = isset ( $period ['llhr'] ) ? $period ['llhr'] : 0;
	$mcap = isset ( $period ['mcap'] ) ? $period ['mcap'] : 0;
	$mlhr = isset ( $period ['mlhr'] ) ? $period ['mlhr'] : 0;
	$lper = '0';
	if ($lcap > 0) {
		$lper = bcmul ( 100, bcadd ( .005, bcdiv ( $llhr, $lcap, 3 ), 2 ) );
	}
	$mper = '0';
	if ($mcap > 0) {
		$mper = bcmul ( 100, bcadd ( .005, bcdiv ( $mlhr, $mcap, 3 ), 2 ) );
	}
	
	$chartData [] = array ($f_date, intval ( $lper ), intval ( $mper ) );
}
$jseChartData = json_encode ( $chartData );
// print_r ( $jseChartData );
// exit ();

require_once ($docType);
print "\n <html> <head> ";
require_once ($headInclude);

?>
<!--Load the AJAX API-->
<script type="text/javascript"
	src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	// Load the Visualization API and the corechart package.
	google.charts.load('current', {
		'packages' : [ 'bar' ]
	});

	// Set a callback to run when the Google Visualization API is loaded.
	google.charts.setOnLoadCallback(drawWorkLoad);

	// Callback that creates and populates a data table,
	// instantiates the chart, passes in the data and
	// draws it.
	function drawWorkLoad() {

		// Create the data table.
		var data = google.visualization.arrayToDataTable(<?php print $jseChartData; ?>);

		// set a padding value to cover the height of title and axis values
		var paddingHeight = 40;
		// set the height to be covered by the rows
		var rowHeight = data.getNumberOfRows() * 30;
		// set the total chart height
		var chartHeight = rowHeight + paddingHeight;

		// Set chart options
		var options = {
				height: chartHeight,
				chartArea: {
					height: '100%',
					width: '75%'
				},
				bar: {
					groupWidth: '90%'
				},
				bars: 'horizontal'
		};

		// Instantiate and draw our chart, passing in some options.
		var chart = new google.charts.Bar(document
				.getElementById('chart_div'));
		chart.draw(data, options);
	}
</script>
<?php
require_once ($genericHead);
print "\n    </head> ";

print "\n    <body $bodyTagAttr> ";
require_once ($inquiryBanner);
print "\n 		<table $baseTable>";
print "\n 			<tr valign=\"top\">";
print "\n 				<td class=\"content\">";
$displayCloseIcon = "Y";
require_once 'PageTitleInclude.php';

// *****************************************************************************
// Work Center Heading
// *****************************************************************************
print "\n <table $contentTable>";
Format_Header ( "Plant", $rowWC ['PLNAME'], $rowWC ['WCPLT'] );
Format_Header ( "Department / Work Center", $rowWC ['WCDESC'], trim ( $rowWC ['WCDEPT'] ) . ' / ' . trim ( $rowWC ['WCWC'] ) );
Format_Header ( "Labor Utilization Percent", $rowWC ['WCLAUT'] * 100 );
Format_Header ( "Labor Efficiency Percent", $rowWC ['WCLAEF'] * 100 );
Format_Header ( "Direct Labor Hours Per Day", $rowWC ['WCLHRD'] );
Format_Header ( "Machine Utilization Percent", $rowWC ['WCMAUT'] * 100 );
Format_Header ( "Machine Hours Per Day", $rowWC ['WCMHRD'] );
if ($rowWC ['WCSCHD'] == 0) {
	$rowWC ['WCSCHD'] = $rowWC ['PLSCHD'];
}
Format_Header ( "Schedule", $rowWC ['SMDESC'], $rowWC ['WCSCHD'] );
print "\n </table> ";

print $inquiryhrTagAttr;
?>
<!--Div that will hold the chart-->
<div id="chart_div"></div>
<?php
print $inquiryhrTagAttr;
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
require_once ($inquiryTrailer);
print "</body> </html>";
exit ();

?>										