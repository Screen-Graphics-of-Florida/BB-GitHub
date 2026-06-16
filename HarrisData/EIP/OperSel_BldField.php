<?php

function Build_Select ($fieldName, $operand, $fromField, $toField, $groupBy) {
	global $selectCriteria;
	if ($operand != "") {
		$selectCriteria .=  " @@" . trim($fromField) . "O" . trim($operand);
		$selectCriteria .=  " @@" . trim($fromField) . "F" . trim($fromField);
		$selectCriteria .=  " @@" . trim($fromField) . "T" . trim($toField);
	}

	if ($groupBy > "00" && $groupBy <= "99") {
		if ($groupBy < "02") {$strPos = $groupBy;}
		else                 {$strPos = ((($groupBy-1) * 9) + 1);}
	}

	if (substr($selectGroupBy, $startPos,9) == "         ") {
		$selectGroupBy=preg_replace("         ", "@@$fieldName",$selectGroupBy, $startPos, 1);
	}

	return $selectCriteria;
}
?>