<?php

$rtvSelection       = $_POST['rtvSelection'];
$saveSelection      = $_POST['saveSelection'];
$selScheduleJob     = $_POST['selScheduleJob'];
$submitSchedule     = $_POST['submitSchedule'];

if       ($selScheduleJob == "Y") {
	$saveSelection  = "S";
	$submitSchedule = "S";
} elseif ($selScheduleJob == "N") {
	$saveSelection  = "S";
	$submitSchedule = "M";
} elseif ($rtvSelection == "Y") {
	$saveSelection = "Y";
}

Concat_Field("@@save", $saveSelection);
Concat_Field("@@sbjb", $submitSchedule);
Concat_Field("@@jnam", strtoupper($_POST['schJobName']));
Concat_Field("@@jobd", strtoupper($_POST['schJobDescription']));
Concat_Field("@@jobq", strtoupper($_POST['schJobQueue']));
Concat_Field("@@jfrq", strtoupper($_POST['schFrequency']));
Concat_Field("@@jtim", strtoupper($_POST['schTime']));
Concat_Field("@@jdat", strtoupper($_POST['schDate']));

$schDayList = "";
if ($_POST['schDays']) {
	foreach ($_POST['schDays'] as $schDayElement) {
		if ($schDayList == "") {$schDayList = $schDayElement;}
		else                   {$schDayList .= ",$schDayElement";}
	}
}
Concat_Field("@@jday", strtoupper($schDayList));
?>