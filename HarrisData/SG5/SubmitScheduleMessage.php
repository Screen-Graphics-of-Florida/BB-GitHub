<?php
if (!isset($updateSearch)) {$updateSearch="";}
if ($submitPageTitle != "") {$workTitle=$submitPageTitle;} else {$workTitle="Your Request";}

if     ($submitSchedule == "M" && $updateSearch=="") {$confMessage = "$workTitle Has Been Submitted For Processing";}
elseif ($submitSchedule == "S" && $updateSearch=="") {$confMessage = "$workTitle Has Been Scheduled For Processing";}
?>