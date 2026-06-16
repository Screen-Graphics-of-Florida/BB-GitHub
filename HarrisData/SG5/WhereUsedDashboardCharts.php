<?php
if ($viewHide == 'N') {
    require 'WhereUsedDashboardLoadCharts.php';
}
?>

<div id="YTD_Charts" style="width: 1350px;">
    <div id="Total_Usage"
         style="display: <?php echo $displayChart ?>; width: 440px; height: 225px;"></div>
    <div id="YTD_Usage"
         style="display: <?php echo $displayChart ?>; width: 440px; height: 225px;"></div>
    <div id="YTD_Sales"
         style="display: <?php echo $displayChart ?>; width: 440px; height: 225px;"></div>
</div>

<?php print $hrTagAttr; ?>