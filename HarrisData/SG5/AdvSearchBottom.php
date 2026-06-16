<?php

print "\n </table>";
print "\n <a href={$baseURL}&amp;tag=MASTERSEARCH&amp;chgSrch=D>$wildDftLrg</a>";
print "\n <a href=\"javascript:document.Search.updateSearch.value='Y'; check(document.Search)\">$addToImage</a>";
print "\n </fieldset>";

print "\n <script TYPE=\"text/javascript\">";
print "\n     document.Search.$focusField.focus();";
print "\n </script>";
print "\n </form>";
if     ($scriptType == "S"){print $searchhrTagAttr;}
elseif ($scriptType == "I"){print $inquiryhrTagAttr;}
else                       {print $hrTagAttr;}
require_once 'Copyright.php';
print "\n </td> </tr> </table>";
if     ($scriptType == "S"){include ($searchTrailer);}
elseif ($scriptType == "I"){include ($inquiryTrailer);}
else                       {require_once 'Trailer.php';}
print "</body> </html>";
exit;
?>