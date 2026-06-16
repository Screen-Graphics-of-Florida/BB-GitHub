<?php
if ($tag == "EXPORT"){
	print "<PRE>";
	print htmlentities($xmlDoc->saveXML());
	print "</PRE>";
	exit();
}
?>