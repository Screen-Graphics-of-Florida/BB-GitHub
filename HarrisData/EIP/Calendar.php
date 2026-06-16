<?php
require_once 'GetURLParm.php';
require_once 'CopyrightBanner.php';

print "\n <html> ";
print "\n     <head> ";
print "\n         <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$phpPath}HDSCalendarSearch.css\"> ";
print "\n         <title>Select Date</title> ";
print "\n         <script type=\"text/javascript\"> ";
print "\n             function Calendar(Month,Year) { ";
print "\n                 var output = ''; ";
print "\n                 var nextMonth = Month + 1; ";
print "\n                 var prevMonth = Month - 1; ";
print "\n                 var nextYear  = Year + 1; ";
print "\n                 var prevYear  = Year - 1; ";

print "\n                 output += '<form name=\"Cal\"><table>'; ";
print "\n                 output += '<tr class=\"popupcalrow\"><td class=\"popupcaltitle\"><a href=\"javascript:nextPrevMonth('+prevMonth+');\">$previousImage</a>'; ";
print "\n                 output += '<select class=\"colhdr\" name=\"Month\" onChange=\"changeMonth();\">'; ";

print "\n                 for (month=0; month<12; month++) { ";
print "\n                     if (month == Month) output += '<option value='+month+' selected>' + names[month] + '</option>'; ";
print "\n                     else                output += '<option value='+month+'>'          + names[month] + '</option>'; ";
print "\n                 } ";

print "\n                 output += '</select><a href=\"javascript:nextPrevMonth('+nextMonth+');\">$nextImage</a></td>'; ";
print "\n                 output += '<td class=\"popupcaltitle\"><a href=\"javascript:nextPrevYear('+prevYear+');\">$previousImage</a><select name=\"Year\" onChange=\"changeYear();\">'; ";

print "\n                 for (year=1900; year<2101; year++) { ";
print "\n                     if (year == Year) output += '<option value='+year+' selected>' + year + '</option>'; ";
print "\n                     else              output += '<option value='+year+'>'          + year + '</option>'; ";
print "\n                 } ";

print "\n                 output += '</select><a href=\"javascript:nextPrevYear('+nextYear+');\">$nextImage</a></td></tr>'; ";
print "\n                 output += '<tr class=\"popupcalrow\"><td colspan=2>'; ";

print "\n                 firstDay = new Date(Year,Month,1); ";
print "\n                 startDay = firstDay.getDay(); ";

print "\n                 if (((Year % 4 == 0) && (Year % 100 != 0)) || (Year % 400 == 0)) ";
print "\n                      days[1] = 29; ";
print "\n                 else ";
print "\n                      days[1] = 28; ";

print "\n                 output += '<table $popupCalTable><tr class=\"popupcalrow\">'; ";

print "\n                 for (i=0; i<7; i++) ";
print "\n                     output += '<td class=\"popupcaldayhdr\">' + dow[i] +'</td>'; ";

print "\n                 output += '</tr><tr class=\"popupcalrow\">'; ";

print "\n                 var column = 0; ";
print "\n                 var lastMonth = Month - 1; ";
print "\n                 var lastYear  = Year; ";
print "\n                 if (lastMonth == -1) { ";
print "\n                     lastMonth = 11; ";
print "\n                     lastYear = lastYear - 1; ";
print "\n                 } ";
print "\n                 var nextMonth = Month + 1; ";
print "\n                 var nextYear  = Year; ";
print "\n                 if (nextMonth == 12) { ";
print "\n                     nextMonth = 0; ";
print "\n                     nextYear = nextYear + 1; ";
print "\n                 } ";

print "\n                 for (i=0; i<startDay; i++, column++) ";
print "\n                     output += '<td class=\"popupcalothmth\">' + '<a href=\"javascript:prevNextDay('+(days[lastMonth]-startDay+i+1)+','+lastMonth+','+lastYear+')\" title=\"'+names[lastMonth]+' '+(days[lastMonth]-startDay+i+1)+', '+lastYear+'\">' + (days[lastMonth]-startDay+i+1) + '</a>' +'</td>'; ";

print "\n                 for (i=1; i<=days[Month]; i++, column++) { ";
print "\n                     output += '<td class=\"popupcalselmth\">' + '<a href=\"javascript:changeDay('+i+')\" title=\"'+names[Month]+' '+i+', '+Year+'\">' + i + '</a>' +'</td>'; ";
print "\n                     if (column == 6) { ";
print "\n                         output += '</tr><tr class=\"popupcalrow\">'; ";
print "\n                         column = -1; ";
print "\n                     } ";
print "\n                 } ";

print "\n                 if (column > 0) { ";
print "\n                     for (i=1; column<7; i++, column++) ";
print "\n                         output += '<td class=\"popupcalothmth\">' + '<a href=\"javascript:prevNextDay('+i+','+nextMonth+','+nextYear+')\" title=\"'+names[nextMonth]+' '+i+', '+nextYear+'\">' + i + '</a>' +'</td>'; ";
print "\n                 } ";

print "\n                 output += '</td></tr></table></form></td></tr></table>'; ";

print "\n                 return output; ";
print "\n             } ";

print "\n             function changeDay(day) { ";
print "\n                 opener.day = day + ''; ";
print "\n                 opener.restart(); ";
print "\n                 self.close; ";
print "\n             } ";

print "\n             function prevNextDay(day,month,year) { ";
print "\n                 lastDate = new Date(year,month,day); ";
print "\n                 lastYear = lastDate.getYear(); ";
print "\n                 opener.year = lastYear; ";
print "\n                 opener.month = month; ";
print "\n                 opener.day = day + ''; ";
print "\n                 opener.restart(); ";
print "\n                 self.close; ";
print "\n             } ";

print "\n             function nextPrevMonth(next) { ";
print "\n                 if (next == -1) { ";
print "\n                     next = 11; ";
print "\n                     opener.year = opener.year - 1; ";
print "\n                 } ";
print "\n                 if (next == 12) { ";
print "\n                     next = 0; ";
print "\n                     opener.year = opener.year + 1; ";
print "\n                 } ";
print "\n                 opener.month = next; ";
print "\n                 location.href='{$homeURL}{$phpPath}Calendar.php?baseVar=" . urlencode($baseVar) . "'; ";
print "\n             } ";

print "\n             function nextPrevYear(next) { ";
print "\n                 opener.year = opener.year - 1; ";
print "\n                 opener.year = next; ";
print "\n                 location.href='{$homeURL}{$phpPath}Calendar.php?baseVar=" . urlencode($baseVar) . "'; ";
print "\n             } ";

print "\n             function changeMonth() { ";
print "\n                 opener.month = document.Cal.Month.options[document.Cal.Month.selectedIndex].value - 0; ";
print "\n                 location.href='{$homeURL}{$phpPath}Calendar.php?baseVar=" . urlencode($baseVar) . "'; ";
print "\n             } ";

print "\n             function changeYear() { ";
print "\n                 opener.year = document.Cal.Year.options[document.Cal.Year.selectedIndex].value - 0; ";
print "\n                 location.href='{$homeURL}{$phpPath}Calendar.php?baseVar=" . urlencode($baseVar) . "'; ";
print "\n             } ";

print "\n             function makeArray0() { ";
print "\n                 for (i = 0; i<makeArray0.arguments.length; i++) ";
print "\n                 this[i] = makeArray0.arguments[i]; ";
print "\n             } ";

print "\n             var names = new makeArray0('January','February','March','April','May','June','July','August','September','October','November','December'); ";
print "\n             var days  = new makeArray0(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); ";
print "\n             var dow   = new makeArray0('Sun','Mon','Tue','Wed','Thu','Fri','Sat'); ";
print "\n         </script> ";
print "\n     </head> ";

print "\n     <body onLoad=\"self.focus()\"> ";
print "\n         <center> ";
print "\n             <script type=\"text/javascript\"> ";
print "\n                 document.write(Calendar(opener.month,opener.year)); ";
print "\n             </script> ";
print "\n         </center> ";
print "\n     </body> ";
print "\n </html> ";

