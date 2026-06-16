<?php
// Enable user error handling
libxml_use_internal_errors(true);

function getValidationResults($xmlDoc) {
	global $fileXmlSchema;
	$htmlCode="";
	$doc = new DOMDocument('1.0');
	$doc->formatOutput = true;
	$domnode = dom_import_simplexml($xmlDoc);
	$domnode = $doc->importNode($domnode, true);
	$domnode = $doc->appendChild($domnode);

	if (!file_exists($fileXmlSchema)) {
		$htmlCode = "Schema not found - ".$fileXmlSchema;
	} else {
		if (!$doc->schemaValidate($fileXmlSchema)) {
			$htmlCode = '<b>XML Document is not valid.</b><br>';
			$xmlErrors = libxml_get_errors();
			$i = 0;
			foreach ($xmlErrors as $xmlError) {
				$arrErrors[$i] = $xmlError;
				$i++;
				$htmlCode.= htmlXmlValidationErrors($xmlError);
			}
			libxml_clear_errors();
		}
	}
	$arrResults['html'] = $htmlCode;
	$arrResults['errors'] = $arrErrors;
	return $arrResults;
}

function htmlXmlValidationErrors($error) {
	global $arrErrorLines;
	$htmlCode = "<br>";
	switch ($error->level) {
		case LIBXML_ERR_WARNING:
			$htmlCode .= '<span style="width: 12em; font-weight: bold;">Warning</span>: ';
			break;
		case LIBXML_ERR_ERROR:
			$htmlCode .= '<span style="width: 12em; color: red; font-weight: bold;">Error</span>: ';
			break;
		case LIBXML_ERR_FATAL:
			$htmlCode .= '<span style="width: 12em; background-color: red; color: white; font-weight: bold;">Fatal Error</span>: ';
			break;
	}
	// 	$htmlCode.= $error->message;
	$htmlCode.= htmlErrorMessagePretty($error->message);
	$htmlCode.= '<br>[Error Code '.$error->code.' detected at Line '.$error->line.'] <br> ';
	return $htmlCode;
}

function htmlErrorMessagePretty($htmlCode) {
	$htmlCode = htmlHighlight($htmlCode, "''", "", 'font-weight: bold;');
	$htmlCode = htmlHighlight($htmlCode, "{}", "", 'color: #CCCCCC;');
	return $htmlCode;
}

function htmlHighlight($htmlCode, $needle, $class, $style) {
	$startNeedle = substr($needle, 0, 1);
	if (strlen($needle) > 1) {
		$endNeedle = substr($needle, 1, 1);
	} else {
		$endNeedle = $startNeedle;
	}
	$styleAttr = makeAttr('style', $style);
	$classAttr = makeAttr('class', $class);
	$startHighlight = '<span'.$classAttr.$styleAttr.'>';
	$endHighlight = '</span>';
	$findNeedle = $startNeedle;
	$i = strpos($htmlCode, $findNeedle, $i);
	$j = true;
	while ($i !== false) {
		if ($j == true) {
			$j = false;
			$findNeedle = $endNeedle;
			$iStart = $i;
		} elseif ($j == false) {
			$j = true;
			$findNeedle = $startNeedle;
			$htmlCode = substr_replace($htmlCode, $endHighlight, $i+1, 0);
			$htmlCode = substr_replace($htmlCode, $startHighlight, $iStart, 0);
			$i = $i + strlen($startHighlight) + strlen($endHighlight);
		}
		$i = strpos($htmlCode, $findNeedle, $i+1);
	}
	return $htmlCode;
}

function libxml_display_error($error) {
	$return = "<br/>\n";
	switch ($error->level) {
		case LIBXML_ERR_WARNING:
			$return .= "<b>Warning $error->code</b>: ";
			break;
		case LIBXML_ERR_ERROR:
			$return .= "<b>Error $error->code</b>: ";
			break;
		case LIBXML_ERR_FATAL:
			$return .= "<b>Fatal Error $error->code</b>: ";
			break;
	}
	$return .= trim($error->message);
	if ($error->file) {$return .=    " in <b>$error->file</b>";}
	$return .= " on line <b>$error->line</b>\n";

	return $return;
}

function libxml_display_errors() {
	$errors = libxml_get_errors();
	foreach ($errors as $error) {print libxml_display_error($error);}
	libxml_clear_errors();
}

function makeAttr($attr, $value) {
	$attrStr = ($value != '') ? (' ' + $attr + '="' + $value + '"') : '';
	return $attrStr;
}


?>