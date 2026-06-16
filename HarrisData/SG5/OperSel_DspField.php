<?php

function Build_Selection ($fieldName,$fieldDesc,$alphNum) {
	$operand   = "";
	$operDesc  = "";
	$fromField = "";
	$toField   = "";
	$selectData= "";
	$seqField  = "";

	$pos=strpos($selectGroupBy,"@@$fieldName");
	if ($pos !== false) {$seqField= (($pos-1)/9)+1;}

	$inc= strlen($fieldName)+3;
	$posO=strpos($selectCriteria,"@@$fieldNameO");

	if ($posO !== false) {
		$posF=strpos($selectCriteria,"@@$fieldNameF");
		$posT=strpos($selectCriteria,"@@$fieldNameT");
		$posNext=strpos($selectCriteria,"@@",$posT+1);

		if ($posO+$inc < $posF)    {$operand  =substr($selectCriteria, $posO+$inc, $posF-($posO+$inc));}
		if ($posF+$inc < $posT)    {$fromField=substr($selectCriteria, $posF+$inc, $posT-($posF+$inc));}
		if ($posT+$inc < $posNext) {$toField  =substr($selectCriteria, $posT+$inc, $posNext-($posT+$inc));}
	}

	if ($alphNum == "D") {
		if ($fromField > "0") {$fromField=substr($fromField,1,2) . $dateEdit . substr($fromField,3,2) . $dateEdit . substr($fromField,5,2);}
		if ($toField   > "0") {$toField  =substr($toField,1,2) . $dateEdit . substr($toField,3,2) . $dateEdit . substr($toField,5,2);}
	}

	if (strpos($selectGroupBy,"@@$fieldName") !== false || $posO !== false) {
		if     ($operand == "BETWEEN") {$operDesc= "Between";}
		elseif ($operand == "LIKE")    {$operDesc= "Like";}
		elseif ($operand == "=")       {$operDesc= "Equal To";}
		elseif ($operand == "<>")      {$operDesc= "Not Equal To";}
		elseif ($operand == "<")       {$operDesc= "Less Than";}
		elseif ($operand == "<=")      {$operDesc= "Less Than Or Equal To";}
		elseif ($operand == ">")       {$operDesc= "Greater Than";}
		elseif ($operand == ">=")      {$operDesc= "Greater Than Or Equal To";}

		print "\n <tr><td class=\"dsphdr\">$fieldDesc</td> ";
		print "\n <td class=\"dspalph\">$operDesc</td> ";
		print "\n <td class=\"dspalph\"> &nbsp; $fromField</td> ";
		print "\n <td class=\"dspalph\"> &nbsp; $toField</td> ";
		print "\n <td class=\"dspcode\">$seqField</td> ";
		print "\n </tr> ";
	}
}
?>