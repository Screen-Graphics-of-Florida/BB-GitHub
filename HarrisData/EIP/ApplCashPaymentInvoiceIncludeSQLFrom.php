<?php
$fileSQL .= " left join Table (Select PEISEQ as OTH_PEISEQ, Sum(PEDAMT) as OTH_PEDAMT From ARPYEN Where PEBCHN<>$fromBatchNumber or PEBCHD<>$fromBatchDate or PEBCHB<>$fromBatchBank or PETYPE<>'$fromType' or PEID<>$fromID or trim(PECHK)<>'" . trim($fromDocument) . "' or PEPTYP<>'$paymentType' or PEPMID<>'$paymentID' Group By PEISEQ) as OTHER on OTH_PEISEQ=a.IVISEQ ";
$fileSQL .= " left join Table (Select PEISEQ as MIN_PEISEQ, min(PEENID) as MIN_PEENID From ARPYEN Where  (PEBCHN,PEBCHD,PEBCHB,PETYPE,PEID,trim(PECHK),PEPTYP,PEPMID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "','$paymentType','$paymentID') Group By PEISEQ) as MINENID on MIN_PEISEQ=a.IVISEQ ";
$fileSQL .= " left join Table (Select PRISEQ as ERR_PRISEQ, PRENID as ERR_PRENID, Count(*) as ERR_COUNT From ARPYER Where (PRBCHN,PRBCHD,PRBCHB,PRTYPE,PRID,trim(PRCHK),PRPTYP,PRPMID)=($fromBatchNumber,$fromBatchDate,$fromBatchBank,'$fromType',$fromID,'" . trim ( $fromDocument ) . "','$paymentType','$paymentID') Group By PRISEQ, PRENID) as ERRENID on (ERR_PRISEQ,ERR_PRENID)=(b.PEISEQ,b.PEENID) ";
$fileSQL .= " left join HDCUST aa on aa.CMCUST=IVBLTO ";
$fileSQL .= " left join HDCUST bb on bb.CMCUST=IVCUST ";
$fileSQL .= " left join HDTRMS on TMCTRM=IVTRMS ";
$fileSQL .= " left join HDLCTN on LOLOC#=IVLOC  ";
$fileSQL .= " left join HDPLNT on PLPLNT=IVPLT  ";
$fileSQL .= " left join HDSLSM on SMSLSM=IVSLSM ";
$fileSQL .= " left join ARPYSB on PSSBCD=IVSBCD and PSSBCD<>' ' ";
$fileSQL .= " left join table (Select HHORD#,HHLIV#,Max(HHSEQ#) as MAX_HHSEQ From OEORHH Group By HHORD#,HHLIV#) as MAXOEORHH on HHORD#=coalesce(IVORD,PEORD) and HHLIV#=IVAINV ";

?>
