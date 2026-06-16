<?php
$srch = array(" ", "/");
$attachVarKey = str_replace($srch, "+", $attachVarKey);
print "\n <a href=\"{$homeURL}{$phpPath}Attachment.PHP{$scriptVarBase}&amp;attachFolder=" . urlencode($attachFolder) . "&amp;attachForDesc=" . urlencode($attachForDesc) . "&amp;attachVarKey=" . urlencode($attachVarKey) . "&amp;userProfile=" . urlencode($userProfile) . "&amp;attachPrg1=" . urlencode($attachPrg1) . "&amp;attachPrg2=" . urlencode($attachPrg2) . "&amp;attachPrg3=" . urlencode($attachPrg3) . "&amp;attachPrg4=" . urlencode($attachPrg4) . "&amp;attachPrg5=" . urlencode($attachPrg5) . "\" onclick=\"$selectionWinVar\">$attachImageLrg</a> ";
?>