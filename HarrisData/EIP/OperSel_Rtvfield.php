<?php

function Retrieve_Field ($fieldName) {
	$S1= "";;
	$S2= "";
	$S3= "";
	$S4= "";
	$S5= "";
	$S6= "";
	$S7= "";
	$S8= "";
	$S9= "";

	$operand  = "";
	$fromField= "";
	$toField  = "";
	$seqField = "";

	$pos=strpos($selectGroupBy,"@@$fieldName");
	if ($pos !== false) {$seqField= (($pos-1)/9)+1;}

	$inc= strlen($fieldName)+3;
	$posO=strpos($selectCriteria,"@@$fieldNameO");
	if ($posO !== false) {
		$posF=strpos($selectCriteria,"@@$fieldNameF");
		$posT=strpos($selectCriteria,"@@$fieldNameT");
		$posNext=strpos($selectCriteria,"@@", $posT+1);

		if ($pos0+$inc < $posF) {
			$operand=substr($selectCriteria, $posO+$inc, $posF-($posO+$inc));
		}
		if ($posF+$inc < $posT) {
			$fromField=substr($selectCriteria, $posF+$inc, $posT-($posF+$inc));
		}
		if ($posT+$inc < $posNext) {
			$toField=substr($selectCriteria, $posT+$inc, $posNext-($posT+$inc));
		}
		if     ($operand == "")        {$S1= "SELECTED";}
		elseif ($operand == "BETWEEN") {$S2= "SELECTED";}
		elseif ($operand == "=")       {$S3= "SELECTED";}
		elseif ($operand == "<>")      {$S4= "SELECTED";}
		elseif ($operand == "<")       {$S5= "SELECTED";}
		elseif ($operand == "<=")      {$S6= "SELECTED";}
		elseif ($operand == ">")       {$S7= "SELECTED";}
		elseif ($operand == ">=")      {$S8= "SELECTED";}
		elseif ($operand == "LIKE")    {$S9= "SELECTED";}
	}
}
?>