<?php

print "\n nextPESSEQ++; ";
print "\n responseActiveCount++; // Add 1 ";
print "\n document.getElementById('quickEntryMessage').innerHTML= responseActiveCount +' Release Pending... '; ";
print "\n document.getElementById('quickEntryMessage').style.color = '" . $errorBackground . "'; ";

?>