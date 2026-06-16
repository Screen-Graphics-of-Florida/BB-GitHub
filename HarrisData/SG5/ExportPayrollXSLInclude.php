<?php

// Create new XSLTProcessor
$xslt = new XSLTProcessor();

// Load the XSLT stylesheet
$xsl = new DOMDocument();
$xsl->load($saveBAXSLT);

// Load the stylesheet into the processor
$xslt->importStylesheet($xsl);

// Load XML input file
$xml = new DOMDocument();
$xml->load("{$exportDirectory}{$dataBaseID}/{$prACHDirectory}{$bankFile}.xml");

// Transform to a file
$results = $xslt->transformToURI($xml, "file:///{$homePath}{$exportDirectory}{$dataBaseID}/{$prACHDirectory}{$bankFile}.txt");

?>
