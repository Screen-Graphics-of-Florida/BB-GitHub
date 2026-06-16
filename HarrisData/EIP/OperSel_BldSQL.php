<?php

function Build_SQL ($fieldName,$fieldType) {
	if     ($fieldType == "A" || $fieldType =="I") {$q= "'";}
	elseif ($fieldType == "N")                     {$q= "";}

	if ($grpFldLvl == "999") {
		if (strpos($selectGroupBy,"@@$fieldName") !== false) {
			if ($groupBy == "" && $selectGroupBy != "") {
				$x= "3";
				while($x <= "500" && substr($selectGroupBy, $x) != ""){
					if (groupBy != "") {$groupBy .=",";}
					$groupBy .= substr($selectGroupBy,$x,7);
					$x += 9; }
			}

			if ($selectGroupWork != "") {$selectGroupWork .= " and ";}

			$selectGroupWork .= "$fieldName=$q@@$fieldName$q";
		}
	} else {
		$wrkCnt= "1";
		if ($groupBy == "" && $selectGroupBy != "") {
			$x= "3";
			while($x <= "500" && substr($selectGroupBy, $x) != "" && $wrkCnt <= $grpFldLvl){
				if ($groupBy != "") {$groupBy .= ",";}
				$groupBy .= substr($selectGroupBy,$x,7);
				$x += 9;
				$wrkCnt ++;
			}
		}

		if ($selectGroupWork == "" && $selectGroupBy != "") {
			if ($grpFldLvl == "1") {$selLevel= $grpFldLvl;}
			else                   {$selLevel= (($grpFldLvl-1)*9)+1;}
			$groupField= substr($selectGroupBy, $selLevel+2,7);

			if (strip($groupField) == strip($fieldName)) {$selectGroupWork .= "$groupField=$q@@$groupField$q";}
		}
	}

	$inc= strlen($fieldName)+3;
	$posO=strpos($selectCriteria,"@@$fieldNameO");
	if ($posO !== false) {
		$posF   =strpos($selectCriteria,"@@$fieldNameF");
		$posT   =strpos($selectCriteria,"@@$fieldNameT");
		$posNext=strpos($selectCriteria,"@@",$posT+1);

		$operand  = "";
		$fromField= "";
		$toField  = "";

		if ($posO+$inc < $posF)    {$operand  =substr($selectCriteria, $posO+$inc, $posF-($posO+$inc));}
		if ($posF+$inc < $posT)    {$fromField=substr($selectCriteria, $posF+$inc, $posT-($posF+$inc));}
		if ($posT+$inc < $posNext) {$toField  =substr($selectCriteria, $posT+$inc, $posNext-($posT+$inc));}

		if ($fieldType == "D") {
			if ($fromField == "") {$fromField= "0000000";}
			else                  {$fromField=DateToCYMD($fromField);}

			if ($toField == "") {$toField= "9999999";}
			else                {$toField= DateToCYMD($toField);}

			if ($fieldType == "I") {
				while(strlen($fromField) != "7") {$fromField="0{$fromField}";}
				Date_To_ISO($fromField, $fromField);

				while(strlen($toField) != "7") {$toField="0{$toField}";}
				Date_To_ISO($toField, $toField);
			}
		}

		if ($fieldType == "A" && $fromField == "") {$fromField= " ";}

		if ($operand == "BETWEEN") {
			$selectRecSQL .= " and ($fieldName BETWEEN $q$fromField$q and $q$toField$q)";
		} elseif (operand == "LIKE") {
			if ($wildSearchDft == "1" || $wildSearchDft == "2" || $wildSearchDft == "3") {
				$wildPos = strpos($fromField, "*");
				if ($wildPos == "0") {$wildPos=strpos($fromField,"?");}
				if ($wildPos == "0") {
					if     ($wildSearchDft == "1") {$fromField = "*{$fromField}";}
					elseif ($wildSearchDft == "2") {$fromField .= "{$fromField}*";}
					elseif ($wildSearchDft == "3") {$fromField = "*{$fromField}*";}
				}
			}
			$likeField= fromField;
			$likeField=str_replace("?", "_", $likeField);
			$likeField=str_replace("*", "%", $likeField);
			$likeField = "'" . trim($likeField) . "'";
			$selectRecSQL .= " and (TRIM($fieldName) $operand $likeField)";
		} else {
			$selectRecSQL .= " and ($fieldName $operand $q$fromField$q)";
		}
	}
}

?>