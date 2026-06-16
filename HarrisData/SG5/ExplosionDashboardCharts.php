<?php
if ($viewHide == 'N') {
    require 'ExplosionDashboardLoadCharts.php';
}
?>

<div id="YTD_Charts" style="width: 1350px;">
	<div id="YTD_Sales"
		style="display: <?php echo $displayChart ?>; width: 440px; height: 225px;"></div>
	<div id="YTD_Issues"
		style="display: <?php echo $displayChart ?>; width: 440px; height: 225px;"></div>
	<div id="YTD_Total"
		style="display: <?php echo $displayChart ?>; width: 440px; height: 225px;"></div>
</div>

<?php print $hrTagAttr; ?>