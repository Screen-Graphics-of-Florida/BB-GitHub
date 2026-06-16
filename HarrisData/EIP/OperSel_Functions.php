<?php

function ConvertToDate ($inField) {
    $inField = strtoupper($inField);
    $posToday = strpos($inField,"TODAY");
    if ($posToday !== false) {
        $today = strtotime("Today");
        $calcData = substr($inField,$posToday+6);
        $calcDate = strtotime($calcData,$today);
    }
    return $calcDate;
}

function Build_DspField ($fieldName,$fieldDesc,$alphNum,$selectGroupBy,$selectCriteria,$buildArray) {
    global $dateEdit, $numElements;
    $operand   = "";
    $operDesc  = "";
    $fromField = "";
    $toField   = "";
    $selectData= "";
    $format = "m" . $dateEdit . "d" . $dateEdit . "y";

    $seqField  = "0";
    $tmpName= "@@" . $fieldName;
    $pos=strpos($selectGroupBy, $tmpName);
    if ($pos !== false) {$seqField= ($pos/9)+1;}
    $inc= strlen($fieldName)+3;
    $tmpName= "@@" . $fieldName . "O";
    $posO=strpos($selectCriteria,$tmpName);

    if ($posO !== false) {
        $tmpName= "@@" . $fieldName . "F";
    	$posF=strpos($selectCriteria, $tmpName);
        $tmpName= "@@" . $fieldName . "T";
    	$posT=strpos($selectCriteria, $tmpName);
    	$posNext=strpos($selectCriteria,"@@",$posT+1);

    	if ($posO+$inc < $posF)    {$operand  =substr($selectCriteria, $posO+$inc, $posF-($posO+$inc));}
    	if ($posF+$inc < $posT)    {$fromField=substr($selectCriteria, $posF+$inc, $posT-($posF+$inc));}
    	if ($posT+$inc < $posNext) {$toField  =substr($selectCriteria, $posT+$inc, $posNext-($posT+$inc));}
    }

    if ($alphNum == "D") {
        if ($fromField > 0) {
            $fromField=substr($fromField,0,2) . $dateEdit . substr($fromField,2,2) . $dateEdit . substr($fromField,4,2);
        } elseif (($fromField !== 0) && ($fromField !== "")) {
            $fromField = ConvertToDate($fromField);
            $fromField = date($format,$fromField); 
	}        
    	if ($toField > 0) {
	    $toField=substr($toField,0,2) . $dateEdit . substr($toField,2,2) . $dateEdit . substr($toField,4,2);
	} elseif (($toField !== 0) && ($toField !== "")) {
	    $toField = ConvertToDate($toField);
            $toField = date($format,$toField); 
	}    
    }
    $tmpName= "@@" . $fieldName;
    if (strpos($selectGroupBy, $tmpName) !== false || $posO !== false) {
        if     ($operand == "BETWEEN") {$operDesc= "Between";}
        elseif ($operand == "LIKE")    {$operDesc= "Like";}
        elseif ($operand == "=")       {$operDesc= "Equal To";}
        elseif ($operand == "<>")      {$operDesc= "Not Equal To";}
        elseif ($operand == "<")       {$operDesc= "Less Than";}
        elseif ($operand == "<=")      {$operDesc= "Less Than Or Equal To";}
        elseif ($operand == ">")       {$operDesc= "Greater Than";}
        elseif ($operand == ">=")      {$operDesc= "Greater Than Or Equal To";}
        $buildArray[$numElements]['fieldDesc'] = $fieldDesc;
        $buildArray[$numElements]['operDesc']  = $operDesc;
        $buildArray[$numElements]['fromField'] = $fromField;
        $buildArray[$numElements]['toField']   = $toField;
        $buildArray[$numElements]['seqField']  = $seqField;  
	$numElements ++;    
    }
    return $buildArray;	
}

function Build_Select ($fieldName, $operand, $fromField, $toField, $groupBy, $buildSelect) {
    $selectCriteria = $buildSelect['selectCriteria'];
    $selectGroupBy  = $buildSelect['selectGroupBy']; 
    if ($operand !== "") {
        $selectCriteria .=  "@@" . trim($fieldName) . "O" . trim($operand);
        $selectCriteria .=  "@@" . trim($fieldName) . "F" . trim($fromField);
        $selectCriteria .=  "@@" . trim($fieldName) . "T" . trim($toField);
    }
    if ($groupBy > 0 && $groupBy <= 99) {
        if ($groupBy == 1) {
            $startPos = 0;
        } else {
            $startPos = (($groupBy-1) * 9);
        }

        if (substr($selectGroupBy, $startPos, 9) == "         ") {
	    $tmpName = "@@" . $fieldName;
            $selectGroupBy = substr_replace($selectGroupBy, $tmpName, $startPos, 9);
        }
    }
    $buildSelect['selectCriteria'] = $selectCriteria;
    $buildSelect['selectGroupBy']  = $selectGroupBy;
    return $buildSelect;
}

function Build_SQL ($fieldName, $fieldType, $grpFldLvl, $groupBy, $selectGroupBy, $selectCriteria, $selectGroupWork, $selectRecSQL) {
    if     ($fieldType == "A" || $fieldType =="I") {$q= "'";}
    elseif ($fieldType == "N")                     {$q= "";}

    if ($grpFldLvl == "999") {
	if (strpos($selectGroupBy,"@@$fieldName") !== false) {
  	    if ($groupBy == "" && $selectGroupBy != "") {
		$x= 2;
		while($x <= 500 && substr($selectGroupBy, $x) != ""){
		    if ($groupBy != "") {$groupBy .=",";}
		    $groupBy .= substr($selectGroupBy,$x,7);
		    $x += 9; }
	    }
	    if ($selectGroupWork != "") {$selectGroupWork .= " and ";}
	    $selectGroupWork .= "$fieldName=$q@@$fieldName$q";
	}
    } else {
	$wrkCnt= 1;
	if ($groupBy == "" && $selectGroupBy != "") {
	    $x= 2;
	    while($x <= 500 && substr($selectGroupBy, $x) != "" && $wrkCnt <= $grpFldLvl){
		if ($groupBy != "") {$groupBy .= ",";}
		$groupBy .= substr($selectGroupBy,$x,7);
		$x += 9;
		$wrkCnt ++;
	    }
	}

	if ($selectGroupWork == "" && $selectGroupBy != "") {
	    $selLevel= ($grpFldLvl-1)*9;
	    $groupField= substr($selectGroupBy, $selLevel+2,7);
	    if (trim($groupField) == trim($fieldName)) {$selectGroupWork .= "$groupField=$q@@$groupField$q";}
	}
    }

    $inc= strlen($fieldName)+3;
    $tmpName= "@@" . $fieldName . "O";
    $posO=strpos($selectCriteria,$tmpName);
    if ($posO !== false) {
        $tmpName= "@@" . $fieldName . "F";
        $posF   =strpos($selectCriteria,$tmpName);
	$tmpName= "@@" . $fieldName . "T";
        $posT   =strpos($selectCriteria,$tmpName);
        $posNext=strpos($selectCriteria,"@@",$posT+1);
        $operand  = "";
        $fromField= "";
        $toField  = "";

        if ($posO+$inc < $posF)    {$operand  =substr($selectCriteria, $posO+$inc, $posF-($posO+$inc));}
        if ($posF+$inc < $posT)    {$fromField=substr($selectCriteria, $posF+$inc, $posT-($posF+$inc));}
        if ($posT+$inc < $posNext) {$toField  =substr($selectCriteria, $posT+$inc, $posNext-($posT+$inc));}
        if ($fieldType == "D") {
	    if ($fromField == "") {
	        $fromField= "0000000";
	    } elseif ($fromField > 0) {
	        $fromField=DateToCYMD($fromField);
	    } else {
                $fromField = ConvertToDate($fromField);
		$fromField = date("mdy",$fromField); 
                $fromField = DateToCYMD($fromField); 
	    } 
	    if ($toField == "") {
	        $toField= "0000000";
	    } elseif ($toField > 0) {
	        $toField=DateToCYMD($toField);
	    } else {
                $toField = ConvertToDate($toField);
		$toField = date("mdy",$toField); 
                $toField = DateToCYMD($toField); 
	    }        
        }
        if ($fieldType == "I") {
            while(strlen($fromField) != "7") {
	        $fromField = $fromField . "0";
	    }
	    Date_To_ISO($fromField, $fromField);
  	    while(strlen($toField) != "7") {
	        $toField = $toField . "0";
	    }
	    Date_To_ISO($toField, $toField);
        }

	if ($fieldType == "A" && $fromField == "") {$fromField= " ";}

	if ($operand == "BETWEEN") {
	    $selectRecSQL .= " and ($fieldName BETWEEN $q$fromField$q and $q$toField$q)";
	} elseif ($operand == "LIKE") {
	    if ($wildSearchDft == "1" || $wildSearchDft == "2" || $wildSearchDft == "3") {
	        $wildPos = strpos($fromField, "*");
		if ($wildPos == "0") {$wildPos=strpos($fromField,"?");}
		if ($wildPos == "0") {
		    if     ($wildSearchDft == "1") {$fromField = "*" . $fromField;}
		    elseif ($wildSearchDft == "2") {$fromField .= $fromField . "*";}
		    elseif ($wildSearchDft == "3") {$fromField = "*" . $fromField . "*";}
		}
	    }
	    $likeField= $fromField;
	    $likeField=str_replace("?", "_", $likeField);
	    $likeField=str_replace("*", "%", $likeField);
	    $likeField = "'" . trim($likeField) . "'";
	    $selectRecSQL .= " and (TRIM($fieldName) $operand $likeField)";
	} else {
	    $selectRecSQL .= " and ($fieldName $operand $q$fromField$q)";
	}
    }
    $returnValue['groupBy']          =$groupBy;
    $returnValue['selectRecSQL']     =$selectRecSQL;
    $returnValue['selectGroupWork']  =$selectGroupWork;
    return $returnValue;
}

function Retrieve_Field ($fieldName, $selectGroupBy, $selectCriteria) {
    $S1= "";
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
    $tmpName= "@@" . $fieldName;
    $pos = strpos ( $selectGroupBy, $tmpName );
    $seqField = ($pos === false) ? "" : ($pos/9)+1;
    $inc= strlen($fieldName)+3;
    $tmpName= "@@" . $fieldName . "O";
    $posO=strpos($selectCriteria,$tmpName);
    if ($posO !== false) {
        $tmpName= "@@" . $fieldName . "F"; 
        $posF=strpos($selectCriteria,$tmpName);
        $tmpName= "@@" . $fieldName . "T"; 
    	$posT=strpos($selectCriteria,$tmpName);
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
	if     ($operand == "")         {$S1= "SELECTED";}
	elseif ($operand == "BETWEEN")  {$S2= "SELECTED";}
	elseif ($operand == "=")        {$S3= "SELECTED";}
	elseif ($operand == "<>")       {$S4= "SELECTED";}
	elseif ($operand == "<")        {$S5= "SELECTED";}
	elseif ($operand == "<=")       {$S6= "SELECTED";}
	elseif ($operand == ">")        {$S7= "SELECTED";}
	elseif ($operand == ">=")       {$S8= "SELECTED";}
	elseif ($operand == "LIKE")     {$S9= "SELECTED";}
    }
    $returnValue['S1'] = $S1;
    $returnValue['S2'] = $S2;
    $returnValue['S3'] = $S3;
    $returnValue['S4'] = $S4;
    $returnValue['S5'] = $S5;
    $returnValue['S6'] = $S6;	
    $returnValue['S7'] = $S7;
    $returnValue['S8'] = $S8;	
    $returnValue['S9'] = $S9;
    $returnValue['fromField'] = $fromField;
    $returnValue['toField'] = $toField;
    $returnValue['seqField'] = $seqField;	
    return $returnValue;		
}
?>