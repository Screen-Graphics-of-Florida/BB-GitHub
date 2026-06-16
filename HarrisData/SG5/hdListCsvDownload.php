<?php ini_set('display_errors',0); ini_set('log_errors',0); ini_set('report_memleaks',0);
chdir(dirname($_SERVER['argv'][0]));

$Arg = explode("::",$argv[1]);
$_GET['baseVar'] = $Arg[0];
$_SERVER['PHP_AUTH_PW']=$Arg[1];
$_SERVER['PHP_AUTH_USER']="HDS";
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
require_once 'VarBase.php';
$fromScript  = "HDLIST.PHP";
$tblID       = $Arg[2];
$pagID       = $Arg[3];
$filterID    = $Arg[4];
$user        = $Arg[5];
$role        = $Arg[6];

$_GET['tblID'] = $tblID;
$_GET['pagID'] = $pagID;
$_GET['downloadToCsv'] = $user;
$downloadToCsv = $user;

require 'stmtSQLClear.php';
$stmtSQL = " Delete From SYLFLW Where LWXHND='$profileHandle' and LWSCRNU='$fromScript' and LWTBID=$tblID and LWPGID=$pagID ";
$status = db2_exec($i5Connect->getConnection(), $stmtSQL);

require 'stmtSQLClear.php';
$stmtSQL  = " Insert Into SYLFLW (LWXHND,LWSCRNU,LWTBID,LWPGID,LWFLID,LWSEQ,LWNAME,LWFVAR,LWOVAR,LWCVAR) ";
$stmtSQL .= " Select '$profileHandle',LFSCRNU,LFTBID,LFPGID,LFFLID,LFSEQ,LFNAME,LFFVAR,LFOVAR,LFCVAR ";
$stmtSQL .= " From SYLFLT Where ";
$stmtSQL .= " LFSCRNU='$fromScript' and LFTBID=$tblID and LFPGID=$pagID ";
$stmtSQL .= " and LFROLE='$role' and LFUSER='$user' and LFFLID=$filterID ";
$status = db2_exec($i5Connect->getConnection(), $stmtSQL);

require_once 'hdList.php';
?>