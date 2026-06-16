<?php

/**
 * Returns an array, indexed by employeeName,scheduleNum,scheduleDesc and employeeInfo, containing an employee's home demographics
 *
 * @param string $fromEmid
 * @return array
 */
function  ETRetInfo_Employee ($fromEmid) {
	global $i5Connect;
	
	//???? require 'UserViewEmpl.php';
	//???? require 'UserView.php';

	$HDPERL = $GLOBALS['HDPERL'];
	$HDPRRL = $GLOBALS['HDPRRL'];

	require 'stmtSQLClear.php';
	$appendWildCard="N";  // Do not append wildCardSearch
	$stmtSQL .= " Select  EMFNAM, EMLNAM, EMMIDI, EMRNAM, EMTRCD,";
	$stmtSQL .= " EMSCHD, EMCOMP, EMFACL, EMDEPT,";
	$stmtSQL .= " EMHGRP, EMEMPL, EMPLNT, EMMDPT, EMWC,";
	$stmtSQL .= " EMPECP, EMLOC,  EMPEMP, ";
	$stmtSQL .= " coalesce(SMDESC,' ') as SMDESC,  ";
	$stmtSQL .= " coalesce(SMSHFT,DEC(0)) as SMSHFT,  ";
	$stmtSQL .= " coalesce(BBDESC,' ') as BBDESC,  ";
	$stmtSQL   .= " coalesce(PLNAME,' ') as PLNAME, ";
	$stmtSQL   .= " coalesce(WCDESC,' ') as WCDESC, ";
	$stmtSQL   .= " coalesce(ODDESC,' ') as ODDESC ";
	if ($HDPERL>0 || $HDPRRL>0) {
		$stmtSQL   .= " ,coalesce(a.CFNAME,' ') as CFNAME ";
		$stmtSQL   .= " ,coalesce(EANAME,' ') as EANAME ";
		$stmtSQL   .= " ,coalesce(b.CFNAME,' ') as CONAME ";
	} else {
		$stmtSQL   .= " ,coalesce(a.CFCFNM,' ') as CFNAME ";
		$stmtSQL   .= " ,' ' as EANAME ";
		$stmtSQL   .= " ,coalesce(b.CFCFNM,' ') as CONAME ";
	}
	$fileSQL .= " HREMPL ";
	$fileSQL .= " left join HDSCHM on SMSCHD=EMSCHD and SMEFFS is null ";
	$fileSQL .= " left join HDGRPM on BBGRP#=EMHGRP ";
	$fileSQL .= " left join HDPLNT on PLPLNT=EMPLNT ";
	$fileSQL .= " left join HDMWCM on WCPLT=EMPLNT and WCDEPT=EMMDPT and WCWC=EMWC ";
	$fileSQL .= " left join PECODE on ODCOMP=EMPECP and ODTYPE='O' and ODCODE=EMLOC ";
	if ($HDPERL>0 || $HDPRRL>0) {
		$fileSQL   .= " left join HRCOFC a on a.CFCOMP=EMCOMP and a.CFFACL=EMFACL ";
		$fileSQL   .= " left join PREXAC on EADEPT=EMDEPT ";
		$fileSQL   .= " left join HRCOFC b on b.CFCOMP=EMPECP and b.CFFACL=EMFACL ";
	} else {
		$fileSQL   .= " left join HDCFAC a on a.CFCO#=EMCOMP and a.CFFAC#=EMFACL ";
		$fileSQL   .= " left join HDCFAC b on b.CFCO#=EMPECP and b.CFFAC#=EMFACL ";
	}
	$selectSQL .= " (EMEMID)=($fromEmid) ";
	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

	//???? if ($sql_Record_Count==0) {require 'UserViewErrorInclude.php'; Exit;}

	$row = db2_fetch_assoc($sqlResult);


	$F_EMSCHD = "";
	$F_EMPLNT = "";
	$F_coFac = "";
	$F_deptWC = "";
	$F_EMDEPT = "";
	$F_EMPECP = "";
	$F_EMHGRP = "";
	$F_EMLOC = "";
	if ($row['EMSCHD']>0) {$F_EMSCHD=Format_Code($row['EMSCHD']);}
	if ($row['EMPLNT']>0) {$F_EMPLNT=Format_Code($row['EMPLNT']);}
	if ($HDPERL>0 || $HDPRRL>0) {
		if ($row['EMCOMP']>0 || $row['EMFACL']>0) {$F_coFac=Format_HRCoFac($row['EMCOMP'],$row['EMFACL'],'F');}
	} else {
		if ($row['EMCOMP']>0 || $row['EMFACL']>0) {$F_coFac=Format_CoFac($row['EMCOMP'],$row['EMFACL'],'F');}
	}
	if ($row['EMMDPT']!=="" || $row['EMWC']!=="") {$F_deptWC=Format_Code("{$row['EMMDPT']}/{$row['EMWC']}");}
	if ($row['EMDEPT']!=="") {$F_EMDEPT=Format_Code($row['EMDEPT']);}
	if ($row['EMPECP']>0) {$F_EMPECP=Format_Code($row['EMPECP']);}
	if ($row['EMHGRP']>0) {$F_EMHGRP=Format_Code($row['EMHGRP']);}
	if ($row['EMLOC']!=="") {$F_EMLOC=Format_Code($row['EMLOC']);}

	$employeeInfo = "";
	$employeeInfo .= "<table $quickSearchTable>";
	$employeeInfo .= " <tr> ";
	$employeeInfo .= "     <td class=\"dsphdr\">Schedule</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['SMDESC']} $F_EMSCHD</td> ";
	$employeeInfo .= "     <td class=\"dsphdr\">Plant Number</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['PLNAME']} $F_EMPLNT</td> ";
	$employeeInfo .= " </tr> ";

	$employeeInfo .= " <tr> ";
	$employeeInfo .= "     <td class=\"dsphdr\">Company/Facility</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['CFNAME']} $F_coFac</td> ";
	$employeeInfo .= "     <td class=\"dsphdr\">Dept/Work Center</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['WCDESC']} $F_deptWC</td> ";
	$employeeInfo .= " </tr> ";

	$employeeInfo .= " <tr> ";
	$employeeInfo .= "     <td class=\"dsphdr\">Home Department</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['EANAME']} $F_EMDEPT</td> ";
	$employeeInfo .= "     <td class=\"dsphdr\">H/R Company</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['CONAME']} $F_EMPECP</td> ";
	$employeeInfo .= " </tr> ";

	$employeeInfo .= " <tr> ";
	$employeeInfo .= "     <td class=\"dsphdr\">Group</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['BBDESC']} $F_EMHGRP</td> ";
	$employeeInfo .= "     <td class=\"dsphdr\">Location</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['ODDESC']} $F_EMLOC</td> ";
	$employeeInfo .= " </tr> ";

	$employeeInfo .= " <tr> ";
	$employeeInfo .= "     <td class=\"dsphdr\">Employee Number</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['EMEMPL']}</td> ";
	$employeeInfo .= "     <td class=\"dsphdr\">H/R Employee</td> ";
	$employeeInfo .= "     <td class=\"dspnmbr\">{$row['EMPEMP']}</td> ";
	$employeeInfo .= " </tr> ";

	$employeeInfo .= " </table> ";

	$employeeRetInfo['employeeName'] = Format_EmplName(trim($row['EMFNAM']),trim($row['EMLNAM']),trim($row['EMMIDI']),trim($row['EMRNAM']),trim($row['EMTRCD']),"H");
	$employeeRetInfo['scheduleNum'] = $row['EMSCHD'];
	$employeeRetInfo['scheduleDesc'] = $row['SMDESC'];
	$employeeRetInfo['shiftNumber'] = $row['SMSHFT'];
	$employeeRetInfo['homeDept'] = $row['EMDEPT'];
	$employeeRetInfo['homePlant'] = $row['EMPLNT'];
	$employeeRetInfo['homeMfgDept'] = $row['EMMDPT'];
	$employeeRetInfo['homeMfgWc'] = $row['EMWC'];
	$employeeRetInfo['employeeInfo'] = $employeeInfo;
	return $employeeRetInfo;

}


function  ETRetInfo_Schedule ($fromEmid, $fromSchd, $fromDept, $fromGrp, $fromPlt, $fromDbpt, $fromWc, $fromDate, $fromHmWk) {
	global $pgmLibrary, $profileHandle, $i5Connect, $dataBaseID;

	//???? require 'UserViewEmpl.php';
	//???? require 'UserView.php';

	$HDPERL = $GLOBALS['HDPERL'];
	$HDPRRL = $GLOBALS['HDPRRL'];
	$HDMERL = $GLOBALS['HDMERL'];
	$TAHWKF = $GLOBALS['TAHWKF'];

	require 'stmtSQLClear.php';

	$hfmt  =	(isset($_SESSION['hrsFmt']))	 ?	$_SESSION['hrsFmt']	:	$TAHWKF;

	$appendWildCard="N";  // Do not append wildCardSearch
	if ($HDMERL > 0 ) {
		$apid = "ME";
	} else {
		$apid = "ET";
	}

	$stmtSQL .= " Select  EMFNAM, EMLNAM, EMMIDI, EMRNAM, EMTRCD,";
	$stmtSQL .= " EMSCHD, EMCOMP, EMFACL, EMDEPT,";
	$stmtSQL .= " EMHGRP, EMEMPL, EMPLNT, EMMDPT, EMWC,";
	$stmtSQL .= " EMPECP, EMLOC,  EMPEMP, ";
	$stmtSQL .= " coalesce(SMDESC,' ') as SMDESC,  ";
	$stmtSQL .= " coalesce(BBDESC,' ') as BBDESC,  ";
	$stmtSQL   .= " coalesce(PLNAME,' ') as PLNAME, ";
	$stmtSQL   .= " coalesce(WCDESC,' ') as WCDESC, ";
	$stmtSQL   .= " coalesce(ODDESC,' ') as ODDESC ";
	if ($HDPERL>0 || $HDPRRL>0) {
		$stmtSQL   .= " ,coalesce(a.CFNAME,' ') as CFNAME ";
		$stmtSQL   .= " ,coalesce(EANAME,' ') as EANAME ";
		$stmtSQL   .= " ,coalesce(b.CFNAME,' ') as CONAME ";
	} else {
		$stmtSQL   .= " ,coalesce(a.CFCFNM,' ') as CFNAME ";
		$stmtSQL   .= " ,' ' as EANAME ";
		$stmtSQL   .= " ,coalesce(b.CFCFNM,' ') as CONAME ";
	}
	$fileSQL .= " HREMPL ";
	if ($fromHmWk == 'H') {
		$fileSQL .= " left join HDSCHM on SMSCHD=EMSCHD and SMEFFS is null ";
		$fileSQL .= " left join HDGRPM on BBGRP#=EMHGRP ";
		$fileSQL .= " left join HDPLNT on PLPLNT=EMPLNT ";
		$fileSQL .= " left join HDMWCM on WCPLT=EMPLNT and WCDEPT=EMMDPT and WCWC=EMWC ";
		$fileSQL .= " left join PECODE on ODCOMP=EMPECP and ODTYPE='O' and ODCODE=EMLOC ";
		if ($HDPERL>0 || $HDPRRL>0) {
			$fileSQL   .= " left join HRCOFC a on a.CFCOMP=EMCOMP and a.CFFACL=EMFACL ";
			$fileSQL   .= " left join PREXAC on EADEPT=EMDEPT ";
			$fileSQL   .= " left join HRCOFC b on b.CFCOMP=EMPECP and b.CFFACL=EMFACL ";
		} else {
			$fileSQL   .= " left join HDCFAC a on a.CFCO#=EMCOMP and a.CFFAC#=EMFACL ";
			$fileSQL   .= " left join HDCFAC b on b.CFCO#=EMPECP and b.CFFAC#=EMFACL ";
		}
	} elseif ($fromHmWk == 'W') {
		$fileSQL .= " left join HDSCHM on SMSCHD=$fromSchd and SMEFFS is null ";
		if ($fromGrp>0){
			$fileSQL .= " left join HDGRPM on BBGRP#=$fromGrp ";
		} else {
			$fileSQL .= " left join HDGRPM on BBGRP#=0 ";
		}
		$fileSQL .= " left join HDPLNT on PLPLNT=$fromPlt ";
		$fileSQL .= " left join HDMWCM on WCPLT=$fromPlt and WCDEPT='$fromDbpt' and WCWC='$fromWc' ";
		$fileSQL .= " left join PECODE on ODCOMP=EMPECP and ODTYPE='O' and ODCODE=EMLOC ";
		if ($HDPERL>0 || $HDPRRL>0) {
			$fileSQL   .= " left join HRCOFC a on a.CFCOMP=EMCOMP and a.CFFACL=EMFACL ";
			$fileSQL   .= " left join PREXAC on EADEPT=EMDEPT ";
			$fileSQL   .= " left join HRCOFC b on b.CFCOMP=EMPECP and b.CFFACL=EMFACL ";
		} else {
			$fileSQL   .= " left join HDCFAC a on a.CFCO#=EMCOMP and a.CFFAC#=EMFACL ";
			$fileSQL   .= " left join HDCFAC b on b.CFCO#=EMPECP and b.CFFAC#=EMFACL ";
		}
	}

	$selectSQL .= " (EMEMID)=($fromEmid) ";


	require 'stmtSQLSelect.php';
	require 'stmtSQLEnd.php';
	$sqlResult = db2_exec($i5Connect->getConnection (), $stmtSQL);

	//???? if ($sql_Record_Count==0) {require 'UserViewErrorInclude.php'; Exit;}

	$row = db2_fetch_assoc($sqlResult);


	$F_EMSCHD = "";
	$F_EMPLNT = "";
	$F_coFac = "";
	$F_deptWC = "";
	$F_EMDEPT = "";
	$F_EMPECP = "";
	$F_EMHGRP = "";
	$F_EMLOC = "";

	$rsDds = "";
	$rsDds .= "A";
	$rsDds .= " ";
	$wrkEMCOMP=str_pad($row[EMCOMP],2,0,STR_PAD_LEFT);
	$rsDds .= "$wrkEMCOMP";
	$wrkEMFACL=str_pad($row[EMFACL],4,0,STR_PAD_LEFT);
	$rsDds .= "$wrkEMFACL";

	$fromDateCYMD = Date_FromISO_ToCYMD($fromDate);

	if ($fromHmWk == 'H') {
		if ($row['EMSCHD']>0) {$F_EMSCHD=Format_Code($row['EMSCHD']);}
		if ($row['EMPLNT']>0) {$F_EMPLNT=Format_Code($row['EMPLNT']);}
		if ($HDPERL>0 || $HDPRRL>0) {
			if ($row['EMCOMP']>0 || $row['EMFACL']>0) {$F_coFac=Format_HRCoFac($row['EMCOMP'],$row['EMFACL'],'F');}
		} else {
			if ($row['EMCOMP']>0 || $row['EMFACL']>0) {$F_coFac=Format_CoFac($row['EMCOMP'],$row['EMFACL'],'F');}
		}
		if ($row['EMMDPT']!=="" || $row['EMWC']!=="") {$F_deptWC=Format_Code("{$row['EMMDPT']}/{$row['EMWC']}");}
		if ($row['EMDEPT']!=="") {$F_EMDEPT=Format_Code($row['EMDEPT']);}
		if ($row['EMPECP']>0) {$F_EMPECP=Format_Code($row['EMPECP']);}
		if ($row['EMHGRP']>0) {$F_EMHGRP=Format_Code($row['EMHGRP']);}
		if ($row['EMLOC']!=="") {$F_EMLOC=Format_Code($row['EMLOC']);}

		$wrkEMPLNT=str_pad($row[EMPLNT],3,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMPLNT";
		$rsDds .= "000";
		$wrkEMDEPT=str_pad($row['EMDEPT'],5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMDEPT";
		$wrkEMMDPT=str_pad($row['EMMDPT'],5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMMDPT";
		$wrkEMWC=str_pad($row['EMWC'],5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMWC";
		$wrkEMGRP=str_pad($row['EMHGRP'],5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMGRP";
		$wrkEMEMPL=str_pad($row['EMEMPL'],5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMEMPL";
		$wrkEMPECP=str_pad($row['EMPECP'],2,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMPECP";
		$wrkEMPEMP=str_pad($row['EMPEMP'],9,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMPEMP";
		$fromDateCYMD=str_pad($fromDateCYMD,7,0,STR_PAD_LEFT);
		$rsDds .= "$fromDateCYMD";
		$rsDds .= "0000000";
		$wrkEMSCHD=str_pad($row['EMSCHD'],3,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMSCHD";

	} elseif ($fromHmWk == 'W') {

		if ($fromSchd > 0) {$F_EMSCHD=Format_Code($fromSchd);}
		if ($fromPlt > 0) {$F_EMPLNT=Format_Code($fromPlt);}
		if ($HDPERL>0 || $HDPRRL>0) {
			if ($row['EMCOMP']>0 || $row['EMFACL']>0) {$F_coFac=Format_HRCoFac($row['EMCOMP'],$row['EMFACL'],'F');}
		} else {
			if ($row['EMCOMP']>0 || $row['EMFACL']>0) {$F_coFac=Format_CoFac($row['EMCOMP'],$row['EMFACL'],'F');}
		}
		if ($fromDbpt!=="" || $fromWc!=="") {$F_deptWC=Format_Code("{$fromDbpt}/{$fromWc}");}
		if ($row['EMDEPT']!=="") {$F_EMDEPT=Format_Code($row['EMDEPT']);}
		if ($row['EMPECP']>0) {$F_EMPECP=Format_Code($row['EMPECP']);}
		if ($fromGrp>0) {$F_EMHGRP=Format_Code($fromGrp);}
		if ($row['EMLOC']!=="") {$F_EMLOC=Format_Code($row['EMLOC']);}

		$wrkEMPLNT=str_pad($fromPlt,3,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMPLNT";
		$rsDds .= "000";
		$wrkEMDEPT=str_pad($fromDept,5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMDEPT";
		$wrkEMMDPT=str_pad($fromDbpt,5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMMDPT";
		$wrkEMWC=str_pad($fromWc,5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMWC";
		$wrkEMGRP=str_pad($fromGrp,5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMGRP";
		$wrkEMEMPL=str_pad($row['EMEMPL'],5,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMEMPL";
		$wrkEMPECP=str_pad($row['EMPECP'],2,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMPECP";
		$wrkEMPEMP=str_pad($row['EMPEMP'],9,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMPEMP";
		$fromDateCYMD=str_pad($fromDateCYMD,7,0,STR_PAD_LEFT);
		$rsDds .= "$fromDateCYMD";
		$rsDds .= "0000000";
		$wrkEMSCHD=str_pad($fromSchd,3,0,STR_PAD_LEFT);
		$rsDds .= "$wrkEMSCHD";

	}

	$scheduleInfo = "";

	$scheduleInfo .= "<table $quickSearchTable>";
	$scheduleInfo .= " <tr> ";
	$scheduleInfo .= "     <td class=\"dsphdr\">Schedule</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['SMDESC']} $F_EMSCHD</td> ";
	$scheduleInfo .= "     <td class=\"dsphdr\">Plant Number</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['PLNAME']} $F_EMPLNT</td> ";
	$scheduleInfo .= " </tr> ";

	$scheduleInfo .= " <tr> ";
	$scheduleInfo .= "     <td class=\"dsphdr\">Company/Facility</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['CFNAME']} $F_coFac</td> ";
	$scheduleInfo .= "     <td class=\"dsphdr\">Dept/Work Center</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['WCDESC']} $F_deptWC</td> ";
	$scheduleInfo .= " </tr> ";

	$scheduleInfo .= " <tr> ";
	$scheduleInfo .= "     <td class=\"dsphdr\">Home Department</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['EANAME']} $F_EMDEPT</td> ";
	$scheduleInfo .= "     <td class=\"dsphdr\">H/R Company</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['CONAME']} $F_EMPECP</td> ";
	$scheduleInfo .= " </tr> ";

	$scheduleInfo .= " <tr> ";
	$scheduleInfo .= "     <td class=\"dsphdr\">Group</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['BBDESC']} $F_EMHGRP</td> ";
	$scheduleInfo .= "     <td class=\"dsphdr\">Location</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['ODDESC']} $F_EMLOC</td> ";
	$scheduleInfo .= " </tr> ";

	$scheduleInfo .= " <tr> ";
	$scheduleInfo .= "     <td class=\"dsphdr\">Employee Number</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['EMEMPL']}</td> ";

	$scheduleInfo .= "     <td class=\"dsphdr\">H/R Employee</td> ";
	$scheduleInfo .= "     <td class=\"dspnmbr\">{$row['EMPEMP']}</td> ";
	$scheduleInfo .= " </tr> ";

	$F_DATE=Format_Date_ISO($fromDate, "D");
	$dateDesc =  date("l",strtotime($fromDate));

	$scheduleInfo .= " </table> ";

	if ($rsDds) {
		$pgmCall = array(
		array("Name"=>"profileHandle", "IO"=>I5_INOUT,  "Type"=>I5_TYPE_CHAR, "Length"=>"64"),
		array("Name"=>"apid", "IO"=>I5_INOUT,  "Type"=>I5_TYPE_CHAR, "Length"=>"2"),
		array("Name"=>"rsDds", "IO"=>I5_INOUT, "Type"=>I5_TYPE_CHAR, "Length"=>"851"));

		$pgm = i5_program_prepare("HETRSD_W", $pgmCall);
		if (!$pgm) {die("<br>Program prepare errno=".i5_errno()." msg=".i5_errormsg());}

		$parmIn = array(
		"profileHandle"=>$profileHandle,
		"apid"=>$apid,
		"rsDds"=>$rsDds);

		$parmOut = array(
		"profileHandle"=>"profileHandle",
		"apid"=>"apid",
		"rsDds"=>"rsDds");

		$ret = i5_program_call($pgm, $parmIn, $parmOut);
		if (function_exists('i5_output')) extract(i5_output());
		if (!$ret) {die("<br>Program call errno=".i5_errno()." msg=".i5_errormsg() . "Program:$pgm In:$parmin");}


		$excdstrt1=substr($rsDds,150,4);
		$excdstrt2=substr($rsDds,154,4);
		$excdstrt3=substr($rsDds,158,4);
		$excdstrt4=substr($rsDds,162,4);
		$excdstrt5=substr($rsDds,166,4);
		$excdstrt6=substr($rsDds,170,4);
		$excdstrt7=substr($rsDds,174,4);
		$excdstrt8=substr($rsDds,178,4);

		$excdstrt1=EditHrsMin(TimeInputFromDec(substr($excdstrt1,0,2) . "." . substr($excdstrt1,2,2)));
		$excdstrt2=EditHrsMin(TimeInputFromDec(substr($excdstrt2,0,2) . "." . substr($excdstrt2,2,2)));
		$excdstrt3=EditHrsMin(TimeInputFromDec(substr($excdstrt3,0,2) . "." . substr($excdstrt3,2,2)));
		$excdstrt4=EditHrsMin(TimeInputFromDec(substr($excdstrt4,0,2) . "." . substr($excdstrt4,2,2)));
		$excdstrt5=EditHrsMin(TimeInputFromDec(substr($excdstrt5,0,2) . "." . substr($excdstrt5,2,2)));
		$excdstrt6=EditHrsMin(TimeInputFromDec(substr($excdstrt6,0,2) . "." . substr($excdstrt6,2,2)));
		$excdstrt7=EditHrsMin(TimeInputFromDec(substr($excdstrt7,0,2) . "." . substr($excdstrt7,2,2)));
		$excdstrt8=EditHrsMin(TimeInputFromDec(substr($excdstrt8,0,2) . "." . substr($excdstrt8,2,2)));

		$excdstop1=substr($rsDds,182,4);
		$excdstop2=substr($rsDds,186,4);
		$excdstop3=substr($rsDds,190,4);
		$excdstop4=substr($rsDds,194,4);
		$excdstop5=substr($rsDds,198,4);
		$excdstop6=substr($rsDds,202,4);
		$excdstop7=substr($rsDds,206,4);
		$excdstop8=substr($rsDds,210,4);

		$excdstop1=EditHrsMin(TimeInputFromDec(substr($excdstop1,0,2) . "." . substr($excdstop1,2,2)));
		$excdstop2=EditHrsMin(TimeInputFromDec(substr($excdstop2,0,2) . "." . substr($excdstop2,2,2)));
		$excdstop3=EditHrsMin(TimeInputFromDec(substr($excdstop3,0,2) . "." . substr($excdstop3,2,2)));
		$excdstop4=EditHrsMin(TimeInputFromDec(substr($excdstop4,0,2) . "." . substr($excdstop4,2,2)));
		$excdstop5=EditHrsMin(TimeInputFromDec(substr($excdstop5,0,2) . "." . substr($excdstop5,2,2)));
		$excdstop6=EditHrsMin(TimeInputFromDec(substr($excdstop6,0,2) . "." . substr($excdstop6,2,2)));
		$excdstop7=EditHrsMin(TimeInputFromDec(substr($excdstop7,0,2) . "." . substr($excdstop7,2,2)));
		$excdstop8=EditHrsMin(TimeInputFromDec(substr($excdstop8,0,2) . "." . substr($excdstop8,2,2)));

		$excdhrs1=substr($rsDds,214,4);
		$excdhrs2=substr($rsDds,218,4);
		$excdhrs3=substr($rsDds,222,4);
		$excdhrs4=substr($rsDds,226,4);
		$excdhrs5=substr($rsDds,230,4);
		$excdhrs6=substr($rsDds,234,4);
		$excdhrs7=substr($rsDds,238,4);
		$excdhrs8=substr($rsDds,242,4);
		if ($hfmt == 'D') {
			$s1Elap1=Format_Nbr((substr($excdhrs1,0,2) . "." . substr($excdhrs1,2,2)),2,"3","N","" ,"");
			$s1Elap2=Format_Nbr((substr($excdhrs2,0,2) . "." . substr($excdhrs2,2,2)),2,"3","N","" ,"");
			$s1Elap3=Format_Nbr((substr($excdhrs3,0,2) . "." . substr($excdhrs3,2,2)),2,"3","N","" ,"");
			$s1Elap4=Format_Nbr((substr($excdhrs4,0,2) . "." . substr($excdhrs4,2,2)),2,"3","N","" ,"");
			$s1Elap5=Format_Nbr((substr($excdhrs5,0,2) . "." . substr($excdhrs5,2,2)),2,"3","N","" ,"");
			$s1Elap6=Format_Nbr((substr($excdhrs6,0,2) . "." . substr($excdhrs6,2,2)),2,"3","N","" ,"");
			$s1Elap7=Format_Nbr((substr($excdhrs7,0,2) . "." . substr($excdhrs7,2,2)),2,"3","N","" ,"");
			$s1Elap8=Format_Nbr((substr($excdhrs8,0,2) . "." . substr($excdhrs8,2,2)),2,"3","N","" ,"");
		} elseif ($hfmt == 'T') {
			$s1Elap1=EditHrsMin(TimeInputFromDec(substr($excdhrs1,0,2) . "." . substr($excdhrs1,2,2)));
			$s1Elap2=EditHrsMin(TimeInputFromDec(substr($excdhrs2,0,2) . "." . substr($excdhrs2,2,2)));
			$s1Elap3=EditHrsMin(TimeInputFromDec(substr($excdhrs3,0,2) . "." . substr($excdhrs3,2,2)));
			$s1Elap4=EditHrsMin(TimeInputFromDec(substr($excdhrs4,0,2) . "." . substr($excdhrs4,2,2)));
			$s1Elap5=EditHrsMin(TimeInputFromDec(substr($excdhrs5,0,2) . "." . substr($excdhrs5,2,2)));
			$s1Elap6=EditHrsMin(TimeInputFromDec(substr($excdhrs6,0,2) . "." . substr($excdhrs6,2,2)));
			$s1Elap7=EditHrsMin(TimeInputFromDec(substr($excdhrs7,0,2) . "." . substr($excdhrs7,2,2)));
			$s1Elap8=EditHrsMin(TimeInputFromDec(substr($excdhrs8,0,2) . "." . substr($excdhrs8,2,2)));
		}

		$expaid1=substr($rsDds,246,4);
		$expaid2=substr($rsDds,250,4);
		$expaid3=substr($rsDds,254,4);
		$expaid4=substr($rsDds,258,4);
		$expaid5=substr($rsDds,262,4);
		$expaid6=substr($rsDds,266,4);
		$expaid7=substr($rsDds,270,4);
		$expaid8=substr($rsDds,274,4);
		if ($hfmt == 'D') {
			$s1Paid1=Format_Nbr((substr($expaid1,0,2) . "." . substr($expaid1,2,2)),2,"3","N","" ,"");
			$s1Paid2=Format_Nbr((substr($expaid2,0,2) . "." . substr($expaid2,2,2)),2,"3","N","" ,"");
			$s1Paid3=Format_Nbr((substr($expaid3,0,2) . "." . substr($expaid3,2,2)),2,"3","N","" ,"");
			$s1Paid4=Format_Nbr((substr($expaid4,0,2) . "." . substr($expaid4,2,2)),2,"3","N","" ,"");
			$s1Paid5=Format_Nbr((substr($expaid5,0,2) . "." . substr($expaid5,2,2)),2,"3","N","" ,"");
			$s1Paid6=Format_Nbr((substr($expaid6,0,2) . "." . substr($expaid6,2,2)),2,"3","N","" ,"");
			$s1Paid7=Format_Nbr((substr($expaid7,0,2) . "." . substr($expaid7,2,2)),2,"3","N","" ,"");
			$s1Paid8=Format_Nbr((substr($expaid8,0,2) . "." . substr($expaid8,2,2)),2,"3","N","" ,"");
		} elseif ($hfmt == 'T') {
			$s1Paid1=EditHrsMin(TimeInputFromDec(substr($expaid1,0,2) . "." . substr($expaid1,2,2)));
			$s1Paid2=EditHrsMin(TimeInputFromDec(substr($expaid2,0,2) . "." . substr($expaid2,2,2)));
			$s1Paid3=EditHrsMin(TimeInputFromDec(substr($expaid3,0,2) . "." . substr($expaid3,2,2)));
			$s1Paid4=EditHrsMin(TimeInputFromDec(substr($expaid4,0,2) . "." . substr($expaid4,2,2)));
			$s1Paid5=EditHrsMin(TimeInputFromDec(substr($expaid5,0,2) . "." . substr($expaid5,2,2)));
			$s1Paid6=EditHrsMin(TimeInputFromDec(substr($expaid6,0,2) . "." . substr($expaid6,2,2)));
			$s1Paid7=EditHrsMin(TimeInputFromDec(substr($expaid7,0,2) . "." . substr($expaid7,2,2)));
			$s1Paid8=EditHrsMin(TimeInputFromDec(substr($expaid8,0,2) . "." . substr($expaid8,2,2)));
		}

		$exworked1=substr($rsDds,278,4);
		$exworked2=substr($rsDds,282,4);
		$exworked3=substr($rsDds,286,4);
		$exworked4=substr($rsDds,290,4);
		$exworked5=substr($rsDds,294,4);
		$exworked6=substr($rsDds,298,4);
		$exworked7=substr($rsDds,302,4);
		$exworked8=substr($rsDds,306,4);
		if ($hfmt == 'D') {
			$s1Wrkd1=Format_Nbr((substr($exworked1,0,2) . "." . substr($exworked1,2,2)),2,"3","N","" ,"");
			$s1Wrkd2=Format_Nbr((substr($exworked2,0,2) . "." . substr($exworked2,2,2)),2,"3","N","" ,"");
			$s1Wrkd3=Format_Nbr((substr($exworked3,0,2) . "." . substr($exworked3,2,2)),2,"3","N","" ,"");
			$s1Wrkd4=Format_Nbr((substr($exworked4,0,2) . "." . substr($exworked4,2,2)),2,"3","N","" ,"");
			$s1Wrkd5=Format_Nbr((substr($exworked5,0,2) . "." . substr($exworked5,2,2)),2,"3","N","" ,"");
			$s1Wrkd6=Format_Nbr((substr($exworked6,0,2) . "." . substr($exworked6,2,2)),2,"3","N","" ,"");
			$s1Wrkd7=Format_Nbr((substr($exworked7,0,2) . "." . substr($exworked7,2,2)),2,"3","N","" ,"");
			$s1Wrkd8=Format_Nbr((substr($exworked8,0,2) . "." . substr($exworked8,2,2)),2,"3","N","" ,"");
		} elseif ($hfmt == 'T') {
			$s1Wrkd1=EditHrsMin(TimeInputFromDec(substr($exworked1,0,2) . "." . substr($exworked1,2,2)));
			$s1Wrkd2=EditHrsMin(TimeInputFromDec(substr($exworked2,0,2) . "." . substr($exworked2,2,2)));
			$s1Wrkd3=EditHrsMin(TimeInputFromDec(substr($exworked3,0,2) . "." . substr($exworked3,2,2)));
			$s1Wrkd4=EditHrsMin(TimeInputFromDec(substr($exworked4,0,2) . "." . substr($exworked4,2,2)));
			$s1Wrkd5=EditHrsMin(TimeInputFromDec(substr($exworked5,0,2) . "." . substr($exworked5,2,2)));
			$s1Wrkd6=EditHrsMin(TimeInputFromDec(substr($exworked6,0,2) . "." . substr($exworked6,2,2)));
			$s1Wrkd7=EditHrsMin(TimeInputFromDec(substr($exworked7,0,2) . "." . substr($exworked7,2,2)));
			$s1Wrkd8=EditHrsMin(TimeInputFromDec(substr($exworked8,0,2) . "." . substr($exworked8,2,2)));
		}

		$excdcode1=substr($rsDds,310,2);
		$excdcode2=substr($rsDds,312,2);
		$excdcode3=substr($rsDds,314,2);
		$excdcode4=substr($rsDds,316,2);
		$excdcode5=substr($rsDds,318,2);
		$excdcode6=substr($rsDds,320,2);
		$excdcode7=substr($rsDds,322,2);
		$excdcode8=substr($rsDds,324,2);

		$exevwrkd1=substr($rsDds,326,1);
		$exevwrkd2=substr($rsDds,327,1);
		$exevwrkd3=substr($rsDds,328,1);
		$exevwrkd4=substr($rsDds,329,1);
		$exevwrkd5=substr($rsDds,330,1);
		$exevwrkd6=substr($rsDds,331,1);
		$exevwrkd7=substr($rsDds,332,1);
		$exevwrkd8=substr($rsDds,333,1);

		$exevpoer1=substr($rsDds,334,1);
		$exevpoer2=substr($rsDds,335,1);
		$exevpoer3=substr($rsDds,336,1);
		$exevpoer4=substr($rsDds,337,1);
		$exevpoer5=substr($rsDds,338,1);
		$exevpoer6=substr($rsDds,339,1);
		$exevpoer7=substr($rsDds,340,1);
		$exevpoer8=substr($rsDds,341,1);

		$exevfdpr1=substr($rsDds,342,1);
		$exevfdpr2=substr($rsDds,343,1);
		$exevfdpr3=substr($rsDds,344,1);
		$exevfdpr4=substr($rsDds,345,1);
		$exevfdpr5=substr($rsDds,346,1);
		$exevfdpr6=substr($rsDds,347,1);
		$exevfdpr7=substr($rsDds,348,1);
		$exevfdpr8=substr($rsDds,349,1);

		$excdpycd1=substr($rsDds,350,3);
		$excdpycd2=substr($rsDds,353,3);
		$excdpycd3=substr($rsDds,356,3);
		$excdpycd4=substr($rsDds,359,3);
		$excdpycd5=substr($rsDds,362,3);
		$excdpycd6=substr($rsDds,365,3);
		$excdpycd7=substr($rsDds,368,3);
		$excdpycd8=substr($rsDds,371,3);

		$excdcmnt1=substr($rsDds,374,30);
		$excdcmnt2=substr($rsDds,404,30);
		$excdcmnt3=substr($rsDds,434,30);
		$excdcmnt4=substr($rsDds,464,30);
		$excdcmnt5=substr($rsDds,494,30);
		$excdcmnt6=substr($rsDds,524,30);
		$excdcmnt7=substr($rsDds,554,30);
		$excdcmnt8=substr($rsDds,584,30);

		$extostrt1=substr($rsDds,633,4);
		$extostrt2=substr($rsDds,658,4);
		$extostrt3=substr($rsDds,683,4);
		$extostrt4=substr($rsDds,708,4);
		$extostrt5=substr($rsDds,733,4);
		$extostrt6=substr($rsDds,758,4);
		$extostrt7=substr($rsDds,783,4);
		$extostrt8=substr($rsDds,808,4);

		$extostrt1=EditHrsMin(TimeInputFromDec(substr($extostrt1,0,2) . "." . substr($extostrt1,2,2)));
		$extostrt2=EditHrsMin(TimeInputFromDec(substr($extostrt2,0,2) . "." . substr($extostrt2,2,2)));
		$extostrt3=EditHrsMin(TimeInputFromDec(substr($extostrt3,0,2) . "." . substr($extostrt3,2,2)));
		$extostrt4=EditHrsMin(TimeInputFromDec(substr($extostrt4,0,2) . "." . substr($extostrt4,2,2)));
		$extostrt5=EditHrsMin(TimeInputFromDec(substr($extostrt5,0,2) . "." . substr($extostrt5,2,2)));
		$extostrt6=EditHrsMin(TimeInputFromDec(substr($extostrt6,0,2) . "." . substr($extostrt6,2,2)));
		$extostrt7=EditHrsMin(TimeInputFromDec(substr($extostrt7,0,2) . "." . substr($extostrt7,2,2)));
		$extostrt8=EditHrsMin(TimeInputFromDec(substr($extostrt8,0,2) . "." . substr($extostrt8,2,2)));

		$extostop1=substr($rsDds,637,4);
		$extostop2=substr($rsDds,662,4);
		$extostop3=substr($rsDds,687,4);
		$extostop4=substr($rsDds,712,4);
		$extostop5=substr($rsDds,737,4);
		$extostop6=substr($rsDds,762,4);
		$extostop7=substr($rsDds,787,4);
		$extostop8=substr($rsDds,812,4);

		$extostop1=EditHrsMin(TimeInputFromDec(substr($extostop1,0,2) . "." . substr($extostop1,2,2)));
		$extostop2=EditHrsMin(TimeInputFromDec(substr($extostop2,0,2) . "." . substr($extostop2,2,2)));
		$extostop3=EditHrsMin(TimeInputFromDec(substr($extostop3,0,2) . "." . substr($extostop3,2,2)));
		$extostop4=EditHrsMin(TimeInputFromDec(substr($extostop4,0,2) . "." . substr($extostop4,2,2)));
		$extostop5=EditHrsMin(TimeInputFromDec(substr($extostop5,0,2) . "." . substr($extostop5,2,2)));
		$extostop6=EditHrsMin(TimeInputFromDec(substr($extostop6,0,2) . "." . substr($extostop6,2,2)));
		$extostop7=EditHrsMin(TimeInputFromDec(substr($extostop7,0,2) . "." . substr($extostop7,2,2)));
		$extostop8=EditHrsMin(TimeInputFromDec(substr($extostop8,0,2) . "." . substr($extostop8,2,2)));

		$extoelap1=substr($rsDds,641,4);
		$extoelap2=substr($rsDds,666,4);
		$extoelap3=substr($rsDds,691,4);
		$extoelap4=substr($rsDds,716,4);
		$extoelap5=substr($rsDds,741,4);
		$extoelap6=substr($rsDds,766,4);
		$extoelap7=substr($rsDds,791,4);
		$extoelap8=substr($rsDds,816,4);
		if ($hfmt == 'D') {
			$s2Elap1=Format_Nbr((substr($extoelap1,0,2) . "." . substr($extoelap1,2,2)),2,"3","N","" ,"");
			$s2Elap2=Format_Nbr((substr($extoelap2,0,2) . "." . substr($extoelap2,2,2)),2,"3","N","" ,"");
			$s2Elap3=Format_Nbr((substr($extoelap3,0,2) . "." . substr($extoelap3,2,2)),2,"3","N","" ,"");
			$s2Elap4=Format_Nbr((substr($extoelap4,0,2) . "." . substr($extoelap4,2,2)),2,"3","N","" ,"");
			$s2Elap5=Format_Nbr((substr($extoelap5,0,2) . "." . substr($extoelap5,2,2)),2,"3","N","" ,"");
			$s2Elap6=Format_Nbr((substr($extoelap6,0,2) . "." . substr($extoelap6,2,2)),2,"3","N","" ,"");
			$s2Elap7=Format_Nbr((substr($extoelap7,0,2) . "." . substr($extoelap7,2,2)),2,"3","N","" ,"");
			$s2Elap8=Format_Nbr((substr($extoelap8,0,2) . "." . substr($extoelap8,2,2)),2,"3","N","" ,"");
		} elseif ($hfmt == 'T') {
			$s2Elap1=EditHrsMin(TimeInputFromDec(substr($extoelap1,0,2) . "." . substr($extoelap1,2,2)));
			$s2Elap2=EditHrsMin(TimeInputFromDec(substr($extoelap2,0,2) . "." . substr($extoelap2,2,2)));
			$s2Elap3=EditHrsMin(TimeInputFromDec(substr($extoelap3,0,2) . "." . substr($extoelap3,2,2)));
			$s2Elap4=EditHrsMin(TimeInputFromDec(substr($extoelap4,0,2) . "." . substr($extoelap4,2,2)));
			$s2Elap5=EditHrsMin(TimeInputFromDec(substr($extoelap5,0,2) . "." . substr($extoelap5,2,2)));
			$s2Elap6=EditHrsMin(TimeInputFromDec(substr($extoelap6,0,2) . "." . substr($extoelap6,2,2)));
			$s2Elap7=EditHrsMin(TimeInputFromDec(substr($extoelap7,0,2) . "." . substr($extoelap7,2,2)));
			$s2Elap8=EditHrsMin(TimeInputFromDec(substr($extoelap8,0,2) . "." . substr($extoelap8,2,2)));
		}

		$extopaid1=substr($rsDds,645,1);
		$extopaid2=substr($rsDds,670,1);
		$extopaid3=substr($rsDds,695,1);
		$extopaid4=substr($rsDds,720,1);
		$extopaid5=substr($rsDds,745,1);
		$extopaid6=substr($rsDds,770,1);
		$extopaid7=substr($rsDds,795,1);
		$extopaid8=substr($rsDds,820,1);

		$extoadje1=substr($rsDds,647,1);
		$extoadje2=substr($rsDds,672,1);
		$extoadje3=substr($rsDds,697,1);
		$extoadje4=substr($rsDds,722,1);
		$extoadje5=substr($rsDds,747,1);
		$extoadje6=substr($rsDds,772,1);
		$extoadje7=substr($rsDds,797,1);
		$extoadje8=substr($rsDds,823,1);

		$extodesc1=substr($rsDds,623,10);
		$extodesc2=substr($rsDds,648,10);
		$extodesc3=substr($rsDds,673,10);
		$extodesc4=substr($rsDds,698,10);
		$extodesc5=substr($rsDds,723,10);
		$extodesc6=substr($rsDds,748,10);
		$extodesc7=substr($rsDds,773,10);
		$extodesc8=substr($rsDds,798,10);
	}

	$scheduleInfo .= " <table> ";
	$scheduleInfo .= " <tr> ";
	$scheduleInfo .= "     <td class=\"colhdr\" colspan=\"12\"> {$F_DATE}    {$dateDesc}</td> ";
	$scheduleInfo .= " </tr> ";

	$scheduleInfo .= " <tr> ";
	$scheduleInfo .= " <td class=\"colhdr\" >Start<br>Time</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Stop<br>Time</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Elapsed<br>Hours</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Worked<br>Hours</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Paid<br>Hours</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Event<br>Code</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Description</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Event<br>Worked</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >On Schedule<br>Report</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Feed<br>Payroll</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Pay<br>Code</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Comment</td>";

	$scheduleInfo .= " </tr> ";

	$x=1;
	while ($x>=1 and $x<=8) {
		if ($x==1) {
			if ($excdstrt1<>0 || $excdstop1<>0) {
				$s1Strt= $excdstrt1;
				$s1Stop= $excdstop1;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Strt</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Stop</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Elap1</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Wrkd1</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Paid1</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdcode1</td> ";
				$code1Desc = RetValue("EVTYPE='E' and EVCODE= '$excdcode1'", "HDEVNT", "EVDESC");
				$scheduleInfo .= "     <td class=\"colalph\">$code1Desc</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevwrkd1</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevpoer1</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevfdpr1</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdpycd1</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$excdcmnt1</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($x==2) {
			if ($excdstrt2<>0 || $excdstop2<>0) {
				$s1Strt2= $excdstrt2;
				$s1Stop2= $excdstop2;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Strt2</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Stop2</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Elap2</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Wrkd2</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Paid2</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdcode2</td> ";
				$code1Desc2 = RetValue("EVTYPE='E' and EVCODE= '$excdcode2'", "HDEVNT", "EVDESC");
				$scheduleInfo .= "     <td class=\"colalph\">$code1Desc2</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevwrkd2</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevpoer2</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevfdpr2</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdpycd2</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$excdcmnt2</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($x==3) {
			if ($excdstrt3<>0 || $excdstop3<>0) {
				$s1Strt3= $excdstrt3;
				$s1Stop3= $excdstop3;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Strt3</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Stop3</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Elap3</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Wrkd3</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Paid3</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdcode3</td> ";
				$code1Desc3 = RetValue("EVTYPE='E' and EVCODE= '$excdcode3'", "HDEVNT", "EVDESC");
				$scheduleInfo .= "     <td class=\"colalph\">$code1Desc3</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevwrkd3</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevpoer3</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevfdpr3</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdpycd3</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$excdcmnt3</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($x==4) {
			if ($excdstrt4<>0 || $excdstop4<>0) {
				$s1Strt4= $excdstrt4;
				$s1Stop4= $excdstop4;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Strt4</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Stop4</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Elap4</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Wrkd4</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Paid4</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdcode4</td> ";
				$code1Desc4 = RetValue("EVTYPE='E' and EVCODE= '$excdcode4'", "HDEVNT", "EVDESC");
				$scheduleInfo .= "     <td class=\"colalph\">$code1Desc4</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevwrkd4</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevpoer4</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevfdpr4</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdpycd4</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$excdcmnt4</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($x==5) {
			if ($excdstrt5<>0 || $excdstop5<>0) {
				$s1Strt5= $excdstrt5;
				$s1Stop5= $excdstop5;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Strt5</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Stop5</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Elap5</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Wrkd5</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Paid5</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdcode5</td> ";
				$code1Desc5 = RetValue("EVTYPE='E' and EVCODE= '$excdcode5'", "HDEVNT", "EVDESC");
				$scheduleInfo .= "     <td class=\"colalph\">$code1Desc5</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevwrkd5</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevpoer5</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevfdpr5</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdpycd5</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$excdcmnt5</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($x==6) {
			if ($excdstrt6<>0 || $excdstop6<>0) {
				$s1Strt6= $excdstrt6;
				$s1Stop6= $excdstop6;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Strt6</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Stop6</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Elap6</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Wrkd6</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Paid6</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdcode6</td> ";
				$code1Desc6 = RetValue("EVTYPE='E' and EVCODE= '$excdcode6'", "HDEVNT", "EVDESC");
				$scheduleInfo .= "     <td class=\"colalph\">$code1Desc6</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevwrkd6</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevpoer6</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevfdpr6</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdpycd6</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$excdcmnt6</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($x==7) {
			if ($excdstrt7<>0 || $excdstop7<>0) {
				$s1Strt7= $excdstrt7;
				$s1Stop7= $excdstop7;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Strt7</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Stop7</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Elap7</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Wrkd7</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Paid7</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdcode7</td> ";
				$code1Desc7 = RetValue("EVTYPE='E' and EVCODE= '$excdcode7'", "HDEVNT", "EVDESC");
				$scheduleInfo .= "     <td class=\"colalph\">$code1Desc7</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevwrkd7</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevpoer7</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevfdpr7</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdpycd7</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$excdcmnt7</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($x==8) {
			if ($excdstrt8<>0 || $excdstop8<>0) {
				$s1Strt8= $excdstrt8;
				$s1Stop8= $excdstop8;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Strt8</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Stop8</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Elap8</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Wrkd8</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s1Paid8</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdcode8</td> ";
				$code1Desc8 = RetValue("EVTYPE='E' and EVCODE= '$excdcode8'", "HDEVNT", "EVDESC");
				$scheduleInfo .= "     <td class=\"colalph\">$code1Desc8</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevwrkd8</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevpoer8</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$exevfdpr8</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$excdpycd8</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$excdcmnt7</td> ";
				$scheduleInfo .= " </tr> ";
			}
		}
		$x ++;
	}

	$scheduleInfo .= " </table> ";

	$scheduleInfo .= " <table> ";
	$scheduleInfo .= " <tr> ";
	$scheduleInfo .= "     <td class=\"colhdr\" colspan=\"6\">&nbsp;Time Off</td> ";
	$scheduleInfo .= " </tr> ";
	$scheduleInfo .= " <tr> ";

	$scheduleInfo .= " <td class=\"colhdr\" >Start<br>Time</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Stop<br>Time</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Elapsed<br>Hours</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Paid<br>Hours</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Adj Work</td>";
	$scheduleInfo .= " <td class=\"colhdr\" >Description</td>";
	$scheduleInfo .= " </tr> ";

	$y=1;
	while ($y>=1 and $y<=8) {
		if ($y==1) {
			if ($extostrt1<>0 || $extodstop1<>0) {
				$s2Strt= $extostrt1;
				$s2Stop= $extostop1;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Strt</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Stop</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Elap1</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extopaid1</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extoadje1</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$extodesc1</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($y==2) {
			if ($extostrt2<>0 || $extodstop2<>0) {
				$s2Strt2= $extostrt2;
				$s2Stop2= $extostop2;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Strt2</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Stop2</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Elap2</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extopaid2</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extoadje2</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$extodesc2</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($y==3) {
			if ($extostrt3<>0 || $extodstop3<>0) {
				$s2Strt3= $extostrt3;
				$s2Stop3= $extostop3;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Strt3</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Stop3</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Elap3</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extopaid3</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extoadje3</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$extodesc3</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($y==4) {
			if ($extostrt4<>0 || $extodstop4<>0) {
				$s2Strt4= $extostrt4;
				$s2Stop4= $extostop4;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Strt4</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Stop4</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Elap4</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extopaid4</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extoadje4</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$extodesc4</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($y==5) {
			if ($extostrt5>0 || $extodstop5>0) {
				$s2Strt5=$extostrt5;
				$s2Stop5=$extostop5;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Strt5</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Stop5</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Elap5</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extopaid5</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extoadje5</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$extodesc5</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($y==6) {
			if ($extostrt6>0 || $extodstop6>0) {
				$s2Strt6=$extostrt6;
				$s2Stop6=$extostop6;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Strt6</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Stop6</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Elap6</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extopaid6</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extoadje6</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$extodesc6</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($y==7) {
			if ($extostrt7>0 || $extodstop7>0) {
				$s2Strt7=$extostrt7;
				$s2Stop7=$extostop7;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Strt7</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Stop7</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Elap7</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extopaid7</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extoadje7</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$extodesc7</td> ";
				$scheduleInfo .= " </tr> ";
			}
		} elseif ($y==8) {
			if ($extostrt8>0 || $extodstop8>0) {
				$s2Strt8=$extostrt8;
				$s2Stop8=$extostop8;

				$scheduleInfo .= " <tr> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Strt8</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Stop8</td> ";
				$scheduleInfo .= "     <td class=\"colnmbr\">$s2Elap8</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extopaid8</td> ";
				$scheduleInfo .= "     <td class=\"colcode\">$extoadje8</td> ";
				$scheduleInfo .= "     <td class=\"colalph\">$extodesc8</td> ";
				$scheduleInfo .= " </tr> ";
			}
		}
		$y ++;
	}
	$scheduleInfo .= " </table> ";


	$scheduleRetInfo['semployeeName'] = Format_EmplName(trim($row['EMFNAM']),trim($row['EMLNAM']),trim($row['EMMIDI']),trim($row['EMRNAM']),trim($row['EMTRCD']),"H");
	if ($fromHmWk == 'H') {
		$scheduleRetInfo['sscheduleNum'] = $row['EMSCHD'];
	} elseif ($fromHmWk == 'W') {
		$scheduleRetInfo['sscheduleNum'] = $fromSchd;
	}
	$scheduleRetInfo['sscheduleDesc'] = $row['SMDESC'];
	$scheduleRetInfo['scheduleInfo'] = $scheduleInfo;

	return $scheduleRetInfo;

}
?>
