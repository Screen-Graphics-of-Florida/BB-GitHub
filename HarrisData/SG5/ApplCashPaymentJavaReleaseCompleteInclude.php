<?php

print "\n   responseActiveCount--; // subtract 1 ";
print "\n   if (responseActiveCount==0) { ";
print "\n     document.getElementById(\"quickEntryMessage\").innerHTML='Release Complete.'; ";
print "\n     document.getElementById(\"quickEntryMessage\").style.color = 'black'; ";
print "\n   } else {";
print "\n       document.getElementById(\"quickEntryMessage\").innerHTML=responseActiveCount+' Release Pending... '; ";
print "\n   } ";

?>