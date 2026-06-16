function y2k(number)    { return (number < 1000) ? number + 1900 : number; }

function padout(number) { return (number < 10) ? '0' + number : number; }

var today = new Date();
var day = today.getDate(), month = today.getMonth(), year = y2k(today.getYear()), fieldName = 0;

function restart() {
    <?php if ("$sysDateFormat" == "YMD") {print ("document.$formName.elements[fieldName].value = '' + yr2(year) + padout(month - 0 + 1) + padout(day);");
    } elseif ("$sysDateFormat" == "DMY") {print ("document.$formName.elements[fieldName].value = '' + padout(day) + padout(month - 0 + 1) + yr2(year);");
    } else {print ("document.$formName.elements[fieldName].value = '' + padout(month - 0 + 1) + padout(day) + yr2(year);");
    }
    ?>
    mywindow.close();
    }

function yr2(number) { number = (number % 100);
                       if (number > 9 || number >= 10) return number;
                       if (number > 00)  return '0' + number;
                       else return '00';}

function calWindow(name) {
    fieldName = name;
    day = today.getDate(), month = today.getMonth(), year = y2k(today.getYear());
    mywindow=open('<?php print "{$homeURL}{$phpPath}Calendar.php?baseVar=" . urlencode($baseVar); ?> ','CalWin','resizable=no,width=260,height=230,left=350,top=150');
    mywindow.location.href = '<?php print "{$homeURL}{$phpPath}Calendar.php?baseVar=" . urlencode($baseVar); ?> ';
    if (mywindow.opener == null) mywindow.opener = self;}