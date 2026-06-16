<?php
print "\n <SELECT NAME=\"$operNbr\" SIZE=1>";
print "\n <OPTION {$returnValue['S1']} VALUE=\"\">";
print "\n <OPTION {$returnValue['S2']} VALUE=\"BETWEEN\">Between";
print "\n <OPTION {$returnValue['S3']} VALUE=\"=\">Equal To";
print "\n <OPTION {$returnValue['S4']} VALUE=\"<>\">Not Equal To";
print "\n <OPTION {$returnValue['S5']} VALUE=\"<\">Less Than";
print "\n <OPTION {$returnValue['S6']} VALUE=\"<=\">Less Than Or Equal To";
print "\n <OPTION {$returnValue['S7']} VALUE=\">\">Greater Than";
print "\n <OPTION {$returnValue['S8']} VALUE=\">=\">Greater Than Or Equal To";
print "\n </SELECT>";
?>