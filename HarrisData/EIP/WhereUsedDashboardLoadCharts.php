<?php
$chartYears = 2;
$groupWidth = '90%';
$fontColor = 'Black';
$fontName = 'Arial';
$fontSize = '14';
$colors = ['', '#99FF00', '#FFFF66', '#33FFFF'];

$unitDesc = "Units";
$chartDesc = "Total Annual Usage";
$chartData = chartData($chartTotal, $colors);

?>
<!--Load the AJAX API-->
<script type="text/javascript"
        src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

    google.charts.load('current', {packages: ['corechart']});
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
            titleTextStyle: {
                color: '<?php echo $fontColor ?>',
                fontName: '<?php echo $fontName ?>',
                fontSize: <?php echo $fontSize ?>,
                bold: true
            },
            legend: 'none',
            bar: {groupWidth: '<?php echo $groupWidth ?>'},
            vAxis: {
                format: 'short',
                gridlines: {count: 5},
                title: unitDesc,
                titleTextStyle: {
                    color: '<?php echo $fontColor ?>',
                    fontName: '<?php echo $fontName ?>',
                    fontSize: <?php echo $fontSize ?>,
                    bold: true
                }
            }
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('Total_Usage'));
        chart.draw(data, options);
    }

</script>
<?php
$unitDesc = "Units";
$chartDesc = "YTD Issues";
$chartData = chartData($chartIssues, $colors);
?>
<script type="text/javascript">
    google.charts.load('current', {packages: ['corechart']});
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
            titleTextStyle: {
                color: '<?php echo $fontColor ?>',
                fontName: '<?php echo $fontName ?>',
                fontSize: <?php echo $fontSize ?>,
                bold: true
            },
            legend: 'none',
            bar: {groupWidth: '<?php echo $groupWidth ?>'},
            vAxis: {
                format: 'short',
                gridlines: {count: 5},
                title: unitDesc,
                titleTextStyle: {
                    color: '<?php echo $fontColor ?>',
                    fontName: '<?php echo $fontName ?>',
                    fontSize: <?php echo $fontSize ?>,
                    bold: true
                }
            }
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('YTD_Usage'));
        chart.draw(data, options);
    }

</script>
<?php
$unitDesc = "Units";
$chartDesc = "YTD Sales";
$chartData = chartData($chartSales, $colors);
?>
<script type="text/javascript">
    google.charts.load('current', {packages: ['corechart']});
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
            titleTextStyle: {
                color: '<?php echo $fontColor ?>',
                fontName: '<?php echo $fontName ?>',
                fontSize: <?php echo $fontSize ?>,
                bold: true
            },
            legend: 'none',
            bar: {groupWidth: '<?php echo $groupWidth ?>'},
            vAxis: {
                format: 'short',
                gridlines: {count: 5},
                title: unitDesc,
                titleTextStyle: {
                    color: '<?php echo $fontColor ?>',
                    fontName: '<?php echo $fontName ?>',
                    fontSize: <?php echo $fontSize ?>,
                    bold: true
                }
            }
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('YTD_Sales'));
        chart.draw(data, options);
    }

</script>
<?php
function chartData(array $row, array $colors, $qty = true)
{
    global $qtyNbrDec, $amtNbrDec;
    $data = [];
    for ($i = 1; $i <= 3; $i++) {
        $year = date("Y") - $i + 1;
        $yeari = 'YEAR' . $i;
        $total = floatval($row[$yeari]);
        $total2 = ($qty) ? number_format($row[$yeari], $qtyNbrDec) : '$' . number_format($row[$yeari], $amtNbrDec);
        $data[] = ["$year", $total, $total2, 'color:' . $colors[$i]];
    }
    return json_encode($data);
}
?>
