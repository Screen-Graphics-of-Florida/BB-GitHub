<?php
$fromURL = RetValue ( "ERXHND='$profileHandle' and ERTYPE='U'", "SYEERR", "EREERR" );
if ($fromURL == "") {
	$fromURL = $returnURL;
}
if (stripos ( $fromURL, '.php' ) === false) {
	$fromURL = $homeURL . $cGIPath . $fromURL;
} else {
	$fromURL = $homeURL . $phpPath . $fromURL;
}
print "<meta http-equiv=\"refresh\" content=\"1; URL={$fromURL}&amp;confMessage=" . urlencode ( trim ( $confMessage ) ) . "\"> ";
?>