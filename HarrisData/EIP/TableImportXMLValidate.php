<?php
$xmlElement  = $xmlTableDoc->table;
$sqlView     = (string) $xmlElement->name;
$cols = db2_columns($i5Connect->getConnection (), null, $pgmLibrary, $sqlView, null);
$list_fields = array();

// fetch and output the column definition arrays
while ($col = db2_fetch_array($cols)) {
    $list_fields[] = $col[3];
}

if (empty($list_fields)) {$columnError .= "<tr><td class='error'>Value {$sqlView} for the name in table element does not exist.</td></tr> ";}
else {
	// Key Element
	$xmlElement = $xmlTableDoc->keys;
	foreach ($xmlElement->col as $xmlElementCol) {
		$colName = trim($xmlElementCol['id']);
		if (array_search($colName,$list_fields) === false) {$columnError .= "<tr><td class='error'>Value {$colName} for the column in the key element not found.</td></tr> ";}
	}
	
	// Row Element
	$xmlElement = $xmlTableDoc->row;
	foreach ($xmlElement->col as $xmlElementCol) {
		$colName = trim($xmlElementCol['id']);
		$colRel  = (string) $xmlElementCol[0]->related_column_1;
		if (!$colRel) {
			if (array_search($colName,$list_fields) === false) {$columnError .= "<tr><td class='error'>Value {$colName} for the column in row element not found.</td></tr> ";}
		} else {
			if (array_search($colRel,$list_fields) === false) {$columnError .= "<tr><td class='error'>Value {$colRel} for a related column of the {$colName} column in the row element not found.</td></tr> ";}
			$colRel  = (string) $xmlElementCol[0]->related_column_2;
			if ($colRel && array_search($colRel,$list_fields) === false) {$columnError .= "<tr><td class='error'>Value {$colRel} for a related column of the {$colName} column in the row element not found.</td></tr> ";}
			$colRel  = (string) $xmlElementCol[0]->related_column_3;
			if ($colRel && array_search($colRel,$list_fields) === false) {$columnError .= "<tr><td class='error'>Value {$colRel} for a related column of the {$colName} column in the row element not found.</td></tr> ";}
			$colRel  = (string) $xmlElementCol[0]->related_column_4;
			if ($colRel && array_search($colRel,$list_fields) === false) {$columnError .= "<tr><td class='error'>Value {$colRel} for a related column of the {$colName} column in the row element not found.</td></tr> ";}
			$colRel  = (string) $xmlElementCol[0]->related_column_5;
			if ($colRel && array_search($colRel,$list_fields) === false) {$columnError .= "<tr><td class='error'>Value {$colRel} for a related column of the {$colName} column in the row element not found.</td></tr> ";}
		}
	}
	
	// Link Element
	$xmlElement = $xmlTableDoc->link;
	foreach ($xmlElement->linkid as $xmlElementLinkID) {
		$linkID = trim($xmlElementLinkID['id']);
		$colName  = (string) $xmlElementLinkID[0]->column_name;
		if ($colName) {
			if (array_search($colName,$list_fields) === false) {$columnError .= "<tr><td class='error'>Value {$colName} for the column of the {$linkID} link in link element not found.</td></tr> ";}
		} 
	}
}

$fileXmlSchema="TableImportXML.xsd";
$arrResults=getValidationResults($xmlTableDoc);
if ($arrResults["html"]) {
	$columnError .= "<tr><td class='error'>" . $arrResults["html"] . "</td></tr>";
}

if ($columnError!="") {
	$tag = "MAINTAIN";
	$columnError="<table {$contentTable}>$columnError</table>";
}

?>