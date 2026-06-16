<?php

print "\n   responseActiveCount--; // subtract 1 ";
print "\n   if (responseActiveCount==0) { ";
print "\n     document.getElementById(\"quickEntryMessage\").innerHTML='Update Complete.'; ";
print "\n     document.getElementById(\"quickEntryMessage\").style.color = 'black'; ";
print "\n   } else {";
print "\n       document.getElementById(\"quickEntryMessage\").innerHTML=responseActiveCount+' Updates Pending... '; ";
print "\n   } ";

?>