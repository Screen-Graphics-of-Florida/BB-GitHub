<?php
Concat_Field("@@sbjb", submitSchedule);
Concat_Field("@@jnam", strtoupper($_POST['schJobName']));
Concat_Field("@@jobd", strtoupper($_POST['schJobDescription']));
Concat_Field("@@jobq", strtoupper($_POST['schJobQueue']));
Concat_Field("@@jfrq", strtoupper($_POST['schFrequency']));
Concat_Field("@@jtim", strtoupper($_POST['schTime']));
Concat_Field("@@jdat", strtoupper($_POST['schDate']));

$schDayList = "";
foreach ($_POST['schDays'] as $schDayElement) {
	if ($schDayList == "") {$schDayList = $schDayElement;}
	else                   {$schDayList .= ",$schDayElement";}
}
Concat_Field("@@jday", strtoupper($schDayList));
?>