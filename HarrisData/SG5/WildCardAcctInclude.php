<?php

function Build_WildCard_Acct ($fldName1, $fldName2, $fldDesc, $selData1, $selData2, $operand){
	global $wildSearchDft, $wildCardTemp, $wildCardSearch, $wildDisplayTemp, $wildCardDisplay, $andOr;
	global $dateEdit;

	$selData1 = trim($selData1);
	$selData2 = trim($selData2);

	if ($selData1 != "" || $selData2 != ""){
		if ($operand == "<>") {$displayOper="Not=";}
		else                  {$displayOper=$operand;}

		if ($andOr == ""){$andOr = "or";}

		if ($wildCardTemp == ""){
			if ($wildCardSearch == ""){
				$wildCardSearch  = " and ( (";
				$wildDisplayTemp = "&nbsp;";
			} else {
				$wildPos = strrpos($wildCardSearch, ")");
				if ($wildPos !== false) {$wildCardSearch = substr($wildCardSearch, 0, $wildPos);}
				$wildCardSearch .= " {$andOr} (";
			}
		}

		if ($wildCardTemp != ""){
			$wildCardTemp    .= " and ";
			$wildDisplayTemp .= " and ";
		} elseif ($wildCardDisplay != ""){
			$wildDisplayTemp .= " <br> $andOr ";
		}

		while(strlen($selData2)<4) {$selData2="0{$selData2}";}
		$wildDisplayTemp .= " $fldDesc $displayOper $selData1-$selData2";

		if ($operand == "=")     {$wildCardTemp    .= "($fldName1 $operand $selData1 and $fldName2 $operand $selData2)";}
		elseif ($operand == "<>") {$wildCardTemp   .= "($fldName1 $operand $selData1 or $fldName2 $operand $selData2)";}
		else {
			$operand1=substr($operand,0,1);
			$wildCardTemp .= " ($fldName1 $operand1 $selData1 or $fldName1 = $selData1 and $fldName2 $operand $selData2)";
		}
	}
	$returnValue['wildCardTemp']    = $wildCardTemp;
	$returnValue['wildDisplayTemp'] = $wildDisplayTemp;
	$returnValue['wildCardSearch']  = $wildCardSearch;
	return $returnValue;
}

function Build_WildCard_CoFac ($fldName1, $fldName2, $fldDesc, $selData1, $selData2, $operand){
	global $wildSearchDft, $wildCardTemp, $wildCardSearch, $wildDisplayTemp, $wildCardDisplay, $andOr;
	global $dateEdit;

	$selData1 = trim($selData1);
	$selData2 = trim($selData2);

	if ($selData1 != "" || $selData2 != ""){
		if ($operand == "<>") {$displayOper="Not=";}
		else                  {$displayOper=$operand;}

		if ($andOr == ""){$andOr = "or";}

		if ($wildCardTemp == ""){
			if ($wildCardSearch == ""){
				$wildCardSearch  = " and ( (";
				$wildDisplayTemp = "&nbsp;";
			} else {
				$wildPos = strrpos($wildCardSearch, ")");
				if ($wildPos !== false) {$wildCardSearch = substr($wildCardSearch, 0, $wildPos);}
				$wildCardSearch .= " {$andOr} (";
			}
		}

		if ($wildCardTemp != ""){
			$wildCardTemp    .= " and ";
			$wildDisplayTemp .= " and ";
		} elseif ($wildCardDisplay != ""){
			$wildDisplayTemp .= " <br> $andOr ";
		}

		while(strlen($selData2)<4) {$selData2="0{$selData2}";}
		$wildDisplayTemp .= " $fldDesc $displayOper $selData1/$selData2";

		if ($operand == "=")     {$wildCardTemp    .= "($fldName1 $operand $selData1 and $fldName2 $operand $selData2)";}
		elseif ($operand == "<>") {$wildCardTemp   .= "($fldName1 $operand $selData1 or $fldName2 $operand $selData2)";}
		else {
			$operand1=substr($operand,0,1);
			$wildCardTemp .= " ($fldName1 $operand1 $selData1 or $fldName1 = $selData1 and $fldName2 $operand $selData2)";
		}
	}
	$returnValue['wildCardTemp']    = $wildCardTemp;
	$returnValue['wildDisplayTemp'] = $wildDisplayTemp;
	$returnValue['wildCardSearch']  = $wildCardSearch;
	return $returnValue;
}

function Range_WildCard_Acct ($fldName1, $fldName2, $fldDesc, $fromData1, $fromData2, $toData1, $toData2, $operand){
	global $wildSearchDft, $wildCardTemp, $wildCardSearch, $wildDisplayTemp, $wildCardDisplay, $andOr;
	global $dateEdit;

	$fromData1 = trim($fromData1);
	$fromData2 = trim($fromData2);
	$toData1   = trim($toData1);
	$toData2   = trim($toData2);

	if ($fromData1 != "" || $fromData2 != "" || $toData1 != "" || $toData2 != "") {
		if ($operand == "<>"){$displayOper = "Not=";}
		else                 {$displayOper = $operand;}

		if ($andOr == ""){$andOr = "or";}

		if ($wildCardTemp == ""){
			if ($wildCardSearch == ""){
				$wildCardSearch = "and ( (";
				$wildDisplayTemp = "&nbsp;";
			} else {
				$wildPos = strrpos($wildCardSearch, ")");
				if ($wildPos !== false) {$wildCardSearch = substr($wildCardSearch, 0, $wildPos);}
				$wildCardSearch .= " $andOr (";
			}
		}

		if ($wildCardTemp != ""){
			$wildCardTemp    .= " and ";
			$wildDisplayTemp .= " and ";
		} elseif ($wildCardDisplay != "") {
			$wildDisplayTemp .= " <br> $andOr ";
		}

		while(strlen($fromData2)<4) {$fromData2="0{$fromData2}";}
		while(strlen($toData2)<4) {$toData2="0{$toData2}";}
		if ($operand != "BETWEEN") {$wildDisplayTemp .= " $fldDesc $displayOper $fromData1-$fromData2";}
		else                       {$wildDisplayTemp .= " $fldDesc $displayOper $fromData1-$fromData2 and $toData1-$toData2";}

		if ($operand == "BETWEEN") {
			$wildCardTemp .= " ($fldName1>$fromData1 or $fldName1=$fromData1 and $fldName2>=$fromData2)";
			$wildCardTemp .= " and ($fldName1<$toData1 or $fldName1=$toData1 and $fldName2<=$toData2)";
		} elseif ($operand == "=")  {
			$wildCardTemp    .= "$fldName1 $operand $fromData1 and $fldName2 $operand $fromData2";
		} elseif ($operand == "<>") {
			$wildCardTemp    .= "$fldName1 $operand $fromData1 or $fldName2 $operand $fromData2";
		} else {
			$operand1=substr($operand,0,1);
			$wildCardTemp    .= " $fldName1 $operand1 $fromData1 or $fldName1 = $fromData1 and $fldName2 $operand $fromData2";
		}
	}

	$returnValue['wildCardTemp']    = $wildCardTemp;
	$returnValue['wildDisplayTemp'] = $wildDisplayTemp;
	$returnValue['wildCardSearch']  = $wildCardSearch;
	return $returnValue;
}

function Range_WildCard_CoFac ($fldName1, $fldName2, $fldDesc, $fromData1, $fromData2, $toData1, $toData2, $operand){
	global $wildSearchDft, $wildCardTemp, $wildCardSearch, $wildDisplayTemp, $wildCardDisplay, $andOr;
	global $dateEdit;

	$fromData1 = trim($fromData1);
	$fromData2 = trim($fromData2);
	$toData1   = trim($toData1);
	$toData2   = trim($toData2);

	if ($fromData1 != "" || $fromData2 != "" || $toData1 != "" || $toData2 != "") {
		if ($operand == "<>"){$displayOper = "Not=";}
		else                 {$displayOper = $operand;}

		if ($andOr == ""){$andOr = "or";}

		if ($wildCardTemp == ""){
			if ($wildCardSearch == ""){
				$wildCardSearch = "and ( (";
				$wildDisplayTemp = "&nbsp;";
			} else {
				$wildPos = strrpos($wildCardSearch, ")");
				if ($wildPos !== false) {$wildCardSearch = substr($wildCardSearch, 0, $wildPos);}
				$wildCardSearch .= " $andOr (";
			}
		}

		if ($wildCardTemp != ""){
			$wildCardTemp    .= " and ";
			$wildDisplayTemp .= " and ";
		} elseif ($wildCardDisplay != "") {
			$wildDisplayTemp .= " <br> $andOr ";
		}

		while(strlen($fromData2)<4) {$fromData2="0{$fromData2}";}
		while(strlen($toData2)<4) {$toData2="0{$toData2}";}
		if ($operand != "BETWEEN") {$wildDisplayTemp .= " $fldDesc $displayOper $fromData1/$fromData2";}
		else                       {$wildDisplayTemp .= " $fldDesc $displayOper $fromData1/$fromData2 and $toData1/$toData2";}

		if ($operand == "BETWEEN") {
			$wildCardTemp .= " ($fldName1>$fromData1 or $fldName1=$fromData1 and $fldName2>=$fromData2)";
			$wildCardTemp .= " and ($fldName1<$toData1 or $fldName1=$toData1 and $fldName2<=$toData2)";
		} elseif ($operand == "=")  {
			$wildCardTemp    .= "$fldName1 $operand $fromData1 and $fldName2 $operand $fromData2";
		} elseif ($operand == "<>") {
			$wildCardTemp    .= "$fldName1 $operand $fromData1 or $fldName2 $operand $fromData2";
		} else {
			$operand1=substr($operand,0,1);
			$wildCardTemp    .= " $fldName1 $operand1 $fromData1 or $fldName1 = $fromData1 and $fldName2 $operand $fromData2";
		}
	}

	$returnValue['wildCardTemp']    = $wildCardTemp;
	$returnValue['wildDisplayTemp'] = $wildDisplayTemp;
	$returnValue['wildCardSearch']  = $wildCardSearch;
	return $returnValue;
}
?>

