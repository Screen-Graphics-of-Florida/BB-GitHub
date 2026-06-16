<?php
deleteUserHandle($profileHandle, $dataBaseID);
print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}{$casStyleSheet}\"> ";
print "\n <div class=\"accessError\">$accessErrorDesc</div> ";
print "\n <meta http-equiv=\"refresh\" content=\"$accessErrorTime; URL=$signonURL\"> ";
?>										