<?php

function Build_User_Number ($userNumber,$wholeSize,$decimalSize) {
	$leadZero = "";
	$decZero  = "";
	$outNumber=trim($userNumber);

	if ($decimalSize>0) {
		$decPos = strpos($outNumber, ".");
		if ($decPos===false) {
			$outNumber.=".";
			$decPos = strpos($outNumber, ".");
		}
		$decPos+=1;
		$decZero=$decimalSize-(strlen($outNumber)-$decPos);
		$lx=1;
		while ($lx <= $decZero) {
			$outNumber.="0";
			$lx++;
		}
	} else {
		$decPos=strlen($outNumber)+1;
	}

	$leadZero=$wholeSize-($decPos-1);
	$lx=1;
	while($lx <= $leadZero) {
		$outNumber="0".$outNumber;
		$lx++;
	}
	return $outNumber;
}