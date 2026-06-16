<?php
$dspMaxRows = 99999;
$xmlDoc = new DomDocument('1.0');
$xmlDoc->formatOutput = true;
$xmlRoot = $xmlDoc->createElement($xmlListName);
$xmlRoot = $xmlDoc->appendChild($xmlRoot);
?>