<?php
$stmtSQL .= " ,Coalesce(IVBLTO,0) as IVBLTO, Coalesce(IVIVDT,0) as IVIVDT ";
$stmtSQL .= " ,IVFRT ,IVSTAX,IVSPC ,IVTRMS,IVDUED,IVARPO ";
$stmtSQL .= " ,IVORD,IVORDT,IVORLN,IVPLT,IVMORD,IVIVAM,IVLOC,IVSLSM,IVCUST,IVPSDT,IVSBCD ";
$stmtSQL .= " ,Coalesce(aa.CMCNA1, ' ')   as BLTO_CMCNA1,Coalesce(aa.CMCNA1U, ' ') as BLTO_CMCNA1U ";
$stmtSQL .= " ,Coalesce(bb.CMCNA1, ' ')   as SHTO_CMCNA1,Coalesce(bb.CMCNA1U, ' ') as SHTO_CMCNA1U ";
$stmtSQL .= " ,Coalesce(TMCTDS, ' ')   as TMCTDS, Coalesce(Upper(TMCTDS), ' ') as TMCTDSU ";
$stmtSQL .= " ,Coalesce(LOFACT, ' ')   as LOFACT ";
$stmtSQL .= " ,Coalesce(LOLNA1, ' ')   as LOLNA1, Coalesce(Upper(LOLNA1), ' ') as LOLNA1U ";
$stmtSQL .= " ,Coalesce(LOCO#,0)   as LOCO, Coalesce(LOFAC#,0) as LOFAC ";
$stmtSQL .= " ,Coalesce(PLNAME, ' ')   as PLNAME, Coalesce(Upper(PLNAME), ' ') as PLNAMEU ";
$stmtSQL .= " ,Coalesce(SMSNA1, ' ')   as SMSNA1, Coalesce(Upper(SMSNA1), ' ') as SMSNA1U ";
$stmtSQL .= " ,Coalesce(PSDESC, ' ')   as PSDESC, Coalesce(PSDESCU, ' ') as PSDESCU ";
$stmtSQL .= " ,Coalesce(MAX_HHSEQ,0) as MAX_HHSEQ ";
$stmtSQL .= " ,Coalesce(MIN_PEENID, 0) as MINPEENID ";
$stmtSQL .= " ,Coalesce(ERR_COUNT, 0) as ARPYENERROR ";
if ($HDOERL<=0) {
	$stmtSQL .= ",0 as OEINVCOUNT,0 as OEHISTORY,0 as OESELECT " ;
} else {
	$stmtSQL .= ",(Select Count(*) From OEIVHH Where (HIAIV#,HIBLTO)=(a.IVAINV,a.IVBLTO) and IVIVCD='C') as OEINVCOUNT " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where a.IVAINV<>0 and HHLIV#=a.IVAINV and (HHBLTO=a.IVBLTO or HHSHTO=a.IVBLTO)) as OEHISTORY " ;
	$stmtSQL .= ",(Select Count(*) From OEORHH Where coalesce(IVORD,PEORD)<>0 and HHORD#=coalesce(IVORD,PEORD) and HHSEQ#=Coalesce(MAX_HHSEQ,0)) as OESELECT " ;
}

?>
