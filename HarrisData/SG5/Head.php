<?php
require 'Meta.php';
if ($meta_title == ""){$meta_title = $page_title;}

print " <title> $meta_title - $title </title>\n";

if ($formatToPrint == "Y"){print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}$fmtStyleSheet\">";
}else                     {print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}$casStyleSheet\">";
}

print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}$menuStyleSheet\">";
print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}$qlinkStyleSheet\">";
print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}$tabStyleSheet\">";

if ($touchScreen == "Y") {print "\n <link rel=stylesheet type=\"text/css\" href=\"{$homeURL}{$homePath}$touchStyleSheet\">";}

require 'ExitStyleSheet.php';
?>