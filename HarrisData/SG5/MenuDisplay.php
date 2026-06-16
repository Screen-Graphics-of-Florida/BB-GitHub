<?php
if ($formatToPrint == ""){
	$filename = $homePath . '/images/ajax-loader.gif';
	
	if (!file_exists($filename)) {
	    $filename = "";
	}	
	print ' <td class="menu">';
	print ' <div id="container">';
	print "\n <script TYPE=\"text/javascript\">";
	print "\n getMenu('$baseVar', '$eID', '$filename', '$activeRole', '$portal', '$pageID')";
	print "\n </script>";
	print ' </div>';
	print ' </td>';
}
?>