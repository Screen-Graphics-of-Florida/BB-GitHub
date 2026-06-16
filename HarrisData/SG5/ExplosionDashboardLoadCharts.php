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

require 'stmtSQLClear.php';
$year1From = '1' . date("y") . '0101';
$year1To = DateTodayCYMD();
$year2From = $year1From - 10000;
$year2To = $year1To - 10000;
$year3From = $year2From - 10000;
$year3To = $year2To - 10000;

$stmtSQL = " Select SUM(CASE WHEN DTTRDT between $year1From and $year1To THEN DTQTY ELSE 0 END) AS Year1
                   ,SUM(CASE WHEN DTTRDT between $year2From and $year2To THEN DTQTY ELSE 0 END) AS Year2
                   ,SUM(CASE WHEN DTTRDT between $year3From and $year3To THEN DTQTY ELSE 0 END) AS Year3
             From HDDTRN 
             Where DTITEM='{$itemNumber}' and DTPLT={$plantNumber} and DTIVTT='SLOE'";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));

$unitDesc = "Units";
$chartDesc = "YTD Sales";
$row = db2_fetch_assoc($sqlResult);
$row = (is_array($row)) ? $row : array();
$chartData = chartData($row, $colors);

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
    data.addColumn('number', 'Sales');
    data.addColumn({type: 'string', role: 'annotation'});
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(<?php print $chartData; ?>);
    var chartDesc = " <?php echo $chartDesc ?> ";
    var unitDesc = " <?php echo $unitDesc ?> ";
    var options = {
     title: chartDesc,
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

   var chart = new google.visualization.ColumnChart(document.getElementById('YTD_Sales'));
   chart.draw(data, options);

   	}
</script>
<?php

require 'stmtSQLClear.php';
$stmtSQL = " Select SUM(CASE WHEN DTTRDT between $year1From and $year1To THEN DTQTY ELSE 0 END) AS Year1
                   ,SUM(CASE WHEN DTTRDT between $year2From and $year2To THEN DTQTY ELSE 0 END) AS Year2
                   ,SUM(CASE WHEN DTTRDT between $year3From and $year3To THEN DTQTY ELSE 0 END) AS Year3
             From HDDTRN 
             Where DTITEM='{$itemNumber}' and DTPLT={$plantNumber} and DTIVTT in ('ISIN','IFIN','KIOU')";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));

$unitDesc = "Units";
$chartDesc = "YTD Issues";
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
    data.addColumn('number', 'Issues');
    data.addColumn({type: 'string', role: 'annotation'});
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(<?php print $chartData; ?>);
    var chartDesc = " <?php echo $chartDesc ?> ";
    var unitDesc = " <?php echo $unitDesc ?> ";
    var options = {
   	     title: chartDesc,
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

   var chart = new google.visualization.ColumnChart(document.getElementById('YTD_Issues'));
   chart.draw(data, options);

   	}
</script>
<?php

require 'stmtSQLClear.php';

require 'stmtSQLClear.php';
$stmtSQL = " Select SUM(CASE WHEN DTTRDT between $year1From and $year1To THEN DTQTY ELSE 0 END) AS Year1
                   ,SUM(CASE WHEN DTTRDT between $year2From and $year2To THEN DTQTY ELSE 0 END) AS Year2
                   ,SUM(CASE WHEN DTTRDT between $year3From and $year3To THEN DTQTY ELSE 0 END) AS Year3
             From HDDTRN 
             Where DTITEM='{$itemNumber}' and DTPLT={$plantNumber} and DTIVTT in ('SLOE','ISIN','IFIN','KIOU')";
$sqlResult = db2_exec($i5Connect->getConnection(), $stmtSQL, array(
    'cursor' => DB2_SCROLLABLE
));

$unitDesc = "Units";
$chartDesc = "YTD Total";
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
    data.addColumn('number', 'Total');
    data.addColumn({type: 'string', role: 'annotation'});
    data.addColumn({type: 'string', role: 'style'});
    data.addRows(<?php print $chartData; ?>);
    var chartDesc = " <?php echo $chartDesc ?> ";
    var unitDesc = " <?php echo $unitDesc ?> ";
    var options = {
   	     title: chartDesc,
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

   var chart = new google.visualization.ColumnChart(document.getElementById('YTD_Total'));
   chart.draw(data, options);

   	}
</script>
<?php

function chartData(array $row, array $colors, $qty = true)
{
    global $qtyNbrDec, $amtNbrDec;
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
