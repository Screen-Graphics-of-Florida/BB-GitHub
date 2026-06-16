<?php
$filterScript = "";
$sylflwSQL    = "";
$sylflwNoSEQ    = "";
if (!$sylMaxSeq) $sylMaxSeq = 1;
$sylfltSQL    = "";
$filterScript = strtoupper($scriptName);
if (!$seqID) $seqID = 0;
$sylflwSQL = "LWXHND='$profileHandle' and LWSCRNU='$filterScript' and LWTBID=$tblID and LWPGID=$pagID and LWSEQ=$seqID";
$sylflwNoSEQ = "LWXHND='$profileHandle' and LWSCRNU='$filterScript' and LWTBID=$tblID and LWPGID=$pagID";
$sylfltSQL = "LTSCRNU='$filterScript' and LFTBID=$tblID and LFPGID=$pagID and LFSEQ=$seqID";
?>
