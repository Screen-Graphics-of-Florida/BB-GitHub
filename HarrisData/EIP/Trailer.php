<?php
if ($formatToPrint == "" || $formatToPrint == "N"){
	if ($popUpWin == "Y"){require_once ($popTrailer);}
	else                 {require_once ($trailer);}
} else {
	require_once ($fmtTrailer);
}

?>