<?php

print "\n nextPESSEQ++; ";
print "\n responseActiveCount++; // Add 1 ";
print "\n document.getElementById('quickEntryMessage').innerHTML= responseActiveCount +' Updates Pending... '; ";
print "\n document.getElementById('quickEntryMessage').style.color = '" . $errorBackground . "'; ";

?>