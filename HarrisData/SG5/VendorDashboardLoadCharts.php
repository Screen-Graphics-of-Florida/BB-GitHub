<?php
$chartYears = 2;
$groupWidth = '90%';
$fontColor = 'Black';
$fontName = 'Arial';
$fontSize = '14';

$colors = [
    '',
    '#99FF00',
    '#FFFF66',
    '#33FFFF'
];

$vendorItemFilter = '';
$vendorFilter = '';
$vendorOpenFilter = '';
if (isset($vendorNumber) && trim($vendorNumber) != '') {
    $vendorOpenFilter = 'POVEND=' . $vendorNumber . ' and ';
    $vendorFilter = 'PHVEND=' . $vendorNumber . ' and ';
    $vendorItemFilter = " inner join HDVCIT on IMITEM=VCITEM and VCVNCS=$vendorNumber and VCVCF='V' ";
}

$whsFilterOpen = '';
$whsFilterHist = '';
if ($HDPDRL > 0) {
    $whsFilterOpen = " inner join HDWHSM on PDOVWH=WHWHS and WHMRPN='Y'";
    $whsFilterHist = " inner join HDWHSM on PIOVWH=WHWHS and WHMRPN='Y'";
}

require 'stmtSQLClear.php';
$filter = (trim($wildCardSearch) != '') ? $wildCardSearch : '';
$year1From = '1' . date("y") . '0101';
$year1To = DateTodayCYMD();
$year2From = $year1From - 10000;
$year2To = $year1To - 10000;
$year3From = $year2From - 10000;
$year3To = $year2To - 10000;

$stmtSQL = " Select SUM(CASE WHEN PIDLRC between $year1From and $year1To THEN dec(round((PITRQT*PIDSCC)/PIPCPB,2),15,2) ELSE 0 END) AS Year1
                   ,SUM(CASE WHEN PIDLRC between $year2From and $year2To THEN dec(round((PITRQT*PIDSCC)/PIPCPB,2),15,2) ELSE 0 END) AS Year2
                   ,SUM(CASE WHEN PIDLRC between $year3From and $year3To THEN dec(round((PITRQT*PIDSCC)/PIPCPB,2),15,2) ELSE 0 END) AS Year3
             From POPOHH inner join POPOHD a on PHPO=a.PIPO and PHSEQ#=a.PISEQ#
                         inner join HDIMST on a.PIITEM=IMITEM $vendorItemFilter $whsFilterHist
             Where $vendorFilter PIPOLT='' and not exists (Select * From POPDEP Where a.PIPO=DPPO and a.PIPOL#=DPLINE) $filter ";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));
// echo $stmtSQL;
$unitDesc = "Dollars";
$chartDesc = "YTD Receipts in ";
$row = db2_fetch_assoc($sqlResult);
$row = (is_array($row)) ? $row : array();
$chartData = chartData($row, $colors, false);

?>
<!--Load the AJAX API-->
<script type="text/javascript"
	src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

google.charts.load('current', {packages:['corechart']});
google.charts.setOnLoadCallback(drawStuff);

  function drawStuff() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Year');
    data.addColumn('number', 'Receipts');
    data.addColumn({type: 'string', role: 'annotation'});
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(<?php print $chartData; ?>);
    var chartDesc = " <?php echo $chartDesc ?> ";
    var unitDesc = " <?php echo $unitDesc ?> ";
    var options = {
     title: chartDesc + unitDesc,
     titleTextStyle: {color: '<?php echo $fontColor ?>', fontName: '<?php echo $fontName ?>', fontSize: <?php echo $fontSize ?>, bold: true},
     legend: 'none',
     bar: {groupWidth: '<?php echo $groupWidth ?>'},
     vAxis: {
      format: 'short',
      gridlines: { count: 5 },
      title: unitDesc,
	  titleTextStyle: {color: '<?php echo $fontColor ?>', fontName: '<?php echo $fontName ?>', fontSize: <?php echo $fontSize ?>, bold: true}
  	 }
   };

   var chart = new google.visualization.ColumnChart(document.getElementById('YTD_Dollars'));
   chart.draw(data, options);

   	}
</script>
<?php

require 'stmtSQLClear.php';
$stmtSQL = " Select SUM(CASE WHEN PIDLRC between $year1From and $year1To THEN PITRQT ELSE 0 END) AS Year1
                   ,SUM(CASE WHEN PIDLRC between $year2From and $year2To THEN PITRQT ELSE 0 END) AS Year2
                   ,SUM(CASE WHEN PIDLRC between $year3From and $year3To THEN PITRQT ELSE 0 END) AS Year3
             From POPOHH inner join POPOHD a on PHPO=a.PIPO and PHSEQ#=a.PISEQ#
                         inner join HDIMST on a.PIITEM=IMITEM $vendorItemFilter $whsFilterHist
             Where $vendorFilter PIPOLT='' and not exists (Select * From POPDEP Where a.PIPO=DPPO and a.PIPOL#=DPLINE) $filter ";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));

$unitDesc = "Units";
$chartDesc = "YTD Receipts in ";
$row = db2_fetch_assoc($sqlResult);
$row = (is_array($row)) ? $row : array();
$chartData = chartData($row, $colors);

?>
<script type="text/javascript">
google.charts.load('current', {packages:['corechart']});
google.charts.setOnLoadCallback(drawStuff);

  function drawStuff() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Year');
    data.addColumn('number', 'Receipts');
    data.addColumn({type: 'string', role: 'annotation'});
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(<?php print $chartData; ?>);
    var chartDesc = " <?php echo $chartDesc ?> ";
    var unitDesc = " <?php echo $unitDesc ?> ";
    var options = {
   	     title: chartDesc + unitDesc,
   	     titleTextStyle: {color: '<?php echo $fontColor ?>', fontName: '<?php echo $fontName ?>', fontSize: <?php echo $fontSize ?>, bold: true},
   	     legend: 'none',
   	     bar: {groupWidth: '<?php echo $groupWidth ?>'},
   	     vAxis: {
   	      format: 'short',
   	      gridlines: { count: 5 },
   	      title: unitDesc,
   		  titleTextStyle: {color: '<?php echo $fontColor ?>', fontName: '<?php echo $fontName ?>', fontSize: <?php echo $fontSize ?>, bold: true}
   	  	 }
   };

   var chart = new google.visualization.ColumnChart(document.getElementById('YTD_Units'));
   chart.draw(data, options);

   	}
</script>
<?php

require 'stmtSQLClear.php';
$stmtSQL = " Select SUM(CASE WHEN PDRQDT between $year1From and $year1To THEN PDQTOR ELSE 0 END) AS Year1
                   ,SUM(CASE WHEN PDRQDT between $year2From and $year2To THEN PDQTOR ELSE 0 END) AS Year2
                   ,SUM(CASE WHEN PDRQDT between $year3From and $year3To THEN PDQTOR ELSE 0 END) AS Year3
             From POPOMS inner join POPOMD a on POPO=a.PDPO
                          inner join HDIMST on a.PDITEM=IMITEM $vendorItemFilter $whsFilterOpen
              Where $vendorOpenFilter PDPOLT='' and not exists (Select * From POPDEP Where a.PDPO=DPPO and a.PDPOL#=DPLINE) $filter ";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));

$unitDesc = "Units";
$chartDesc = "YTD Ordered in ";
$row = db2_fetch_assoc($sqlResult);
$row = (is_array($row)) ? $row : array();
$chartData = chartData($row, $colors);

?>
<script type="text/javascript">
google.charts.load('current', {packages:['corechart']});
google.charts.setOnLoadCallback(drawStuff);

  function drawStuff() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Year');
    data.addColumn('number', 'Ordered');
    data.addColumn({type: 'string', role: 'annotation'});
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(<?php print $chartData; ?>);
    var chartDesc = " <?php echo $chartDesc ?> ";
    var unitDesc = " <?php echo $unitDesc ?> ";
    var options = {
   	     title: chartDesc + unitDesc,
   	     titleTextStyle: {color: '<?php echo $fontColor ?>', fontName: '<?php echo $fontName ?>', fontSize: <?php echo $fontSize ?>, bold: true},
   	     legend: 'none',
   	     bar: {groupWidth: '<?php echo $groupWidth ?>'},
   	     vAxis: {
   	      format: 'short',
   	      gridlines: { count: 5 },
   	      title: unitDesc,
   		  titleTextStyle: {color: '<?php echo $fontColor ?>', fontName: '<?php echo $fontName ?>', fontSize: <?php echo $fontSize ?>, bold: true}
   	  	 }
   };

   var chart = new google.visualization.ColumnChart(document.getElementById('YTD_Ordered'));
   chart.draw(data, options);

   	}
</script>
<?php

function chartData(array $row, array $colors, $qty = true)
{
    $data = [];
    for ($i = 1; $i <= 3; $i ++) {
        $year = date("Y") - $i + 1;
        $yeari = 'YEAR' . $i;
        $total = floatval($row[$yeari]);
        $total2 = ($qty) ? number_format($row[$yeari], $qtyNbrDec) : '$' . number_format($row[$yeari], $amtNbrDec);
        $data[] = [
            "$year",
            $total,
            $total2,
            'color:' . $colors[$i]
        ];
    }

    return json_encode($data);
}
?>
