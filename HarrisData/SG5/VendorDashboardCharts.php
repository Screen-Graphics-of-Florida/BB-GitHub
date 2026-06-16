<?php
if ($viewHide == 'N') {
    require 'VendorDashboardLoadCharts.php';
}
?>
<div id="YTD_Charts" style="width: 1350px;">
	<div id="YTD_Dollars"
		style="display: <?php echo $displayChart ?>; width: 440px; height: 225px;"></div>
	<div id="YTD_Units"
		style="display: <?php echo $displayChart ?>; width: 440px; height: 225px;"></div>
	<div id="YTD_Ordered"
		style="display: <?php echo $displayChart ?>; width: 440px; height: 225px;"></div>
</div>
<?php print $hrTagAttr; ?>