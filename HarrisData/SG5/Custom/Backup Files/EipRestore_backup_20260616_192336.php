<?php
// ============================================================
// SG Custom EIP Restore Script — SG5 Test Environment
// ============================================================
// Run after any S5HDSDATA database refresh or Harris upgrade.
// Step 0 clears all SG portal rows first, then rebuilds from
// scratch with field values verified against June 13 backup.
//
// URL:
//   https://portal.screen-graphics.com:5610/Custom/SG/EipRestore.php?confirm=RESTORE
// ============================================================

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'RESTORE') {
    die('<h2>SG EIP Restore</h2>'
      . '<p>Add <code>?confirm=RESTORE</code> to the URL to run.</p>');
}

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) {
    die('<pre>DB2 connection failed: '
      . htmlspecialchars(db2_conn_errormsg()) . '</pre>');
}

$cntDel = $cntOk = $cntSkip = $cntFail = 0;
$log    = [];

function runDel($label, $sql) {
    global $conn, $cntDel, $cntFail, $log;
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) {
        $cntFail++;
        $log[] = ['FAIL', $label, db2_stmt_errormsg()];
    } else {
        $n = db2_num_rows($stmt);
        $cntDel++;
        $log[] = ['DEL', $label, "$n rows removed"];
    }
}

function runSql($label, $sql) {
    global $conn, $cntOk, $cntSkip, $cntFail, $log;
    $stmt = @db2_exec($conn, $sql);
    if ($stmt === false) {
        $cntFail++;
        $log[] = ['FAIL', $label, db2_stmt_errormsg()];
    } else {
        $n = db2_num_rows($stmt);
        if ($n > 0) { $cntOk++;   $log[] = ['OK',   $label, '']; }
        else        { $cntSkip++; $log[] = ['SKIP', $label, 'already exists']; }
    }
}

// ============================================================
// Configuration
// ============================================================

$portals = [
    'SGINQ'  => 'SG Inquiries',
    'SGDASH' => 'SG Dashboards',
    'SGDINT' => 'SG Data Integrity',
    'SGRPT'  => 'SG Reports',
    'SGSOP'  => 'SG SOPs',
];

$cats = [
    'ACCT'    => 'Accounting',
    'INVMGMT' => 'Inv Management',
    'MFG'     => 'Manufacturing',
    'OE'      => 'Order Entry',
    'PLN'     => 'Planning',
    'PUR'     => 'Purchasing',
];

// Max existing RDSEQN per role before SG portals were added
$roleMaxSeq = [
    'ACCOUNTING' => 24, 'ACCOUNTMAN' => 21, 'COLLECTION' => 11,
    'CONSULTANT' => 11, 'CUSTSRVC'   => 20, 'CUSTSRVMGR' => 22,
    'EBORRELL'   => 19, 'ENAPOLES'   => 20, 'HD_ALL'     => 67,
    'HD_ALL_SG'  => 36, 'HD_CUST'    =>  2, 'HD_EMPL'    =>  3,
    'HD_FIX'     =>  3, 'HD_HRADMIN' =>  4, 'HD_MOBILE'  =>  1,
    'HD_SECURE'  =>  1, 'HD_SLSM'    =>  3, 'HD_VEND'    =>  2,
    'INSTALLER'  =>  3, 'INVENTORY'  => 15, 'LARIANN'    => 21,
    'MCRESPO'    => 18, 'MFGVP'      => 15, 'PLANNING'   => 18,
    'PLANPRDFUR' => 18, 'PRODMANAGR' => 16, 'PRODUCTION' => 19,
    'PURCHASING' => 17, 'QC01'       =>  6, 'RECEIVING'  =>  4,
    'RESTRRECPT' =>  9, 'SALES'      => 14, 'SALESADMIN' => 37,
    'SHIPPING'   => 13, 'SHOPLEADER' =>  8, 'SHOPREPORT' =>  7,
    'TEMPUSER'   =>  6, 'TIFFANY'    => 23, 'TVWILLIAMS' => 12,
    'USERTEST'   =>  5, 'WIDGET'     =>  1, 'WIDGETS'    => 33,
];

$pgms = [
    'SGPORTLND' => 'Portal Landing Page',
    'MOREQ'     => 'MO Requirements',
    'BOOKDASH'  => 'Bookings Dashboard',
    'BOOKDRLL'  => 'Bookings Drilldown',
    'SALESDASH' => 'Sales Dashboard',
    'SALESDRLL' => 'Sales Drilldown',
    'SHIPSDASH' => 'Shipments Dashboard',
    'OPENORDLC' => 'OO Line Item Cmts',
];

$users = [
    'ADAVIS','ALOPEZ','AMEDEROS','AMEDLEY','ART','BBUSCH','BILL',
    'CHUTCH','DBROWNE','DBUSCH','DGILLESPIE','EBORRELL','EHESSLER',
    'FXAVIOR','FXAVIORSG5','GFORD','HDACCT','HDACKDOCS','HDFILES',
    'HDSTEST','JJIMENEZ','JOYCELD','JUDE','KHART','LCERVANTES',
    'LSEARLES','LTINNEY','MANNY','MCRESPO','MLOPEZ','MREID',
    'MSOMOZA','MTAKACS','NICK','PURCHASING','QC01','RBLANCHARD',
    'RECEPTION','SCANTALUPO','SCREENRIP','SGIT','SHIP','SHOP',
    'TIFFANY','TREID','TRIDDELL','USERTEST','VMINGUILLO','ZBLAKE',
];

$landingBase = '@@homeURL@@phpPathCustom/SG/sg_portal_landing.php';

// ============================================================
// STEP 0: CLEANUP — remove all SG portal rows before re-insert
// ============================================================

$pcodes = array_keys($portals);

// Build FUID list covering all variants we may have previously inserted
$delFuids = [];
foreach ($pcodes as $pcode) {
    $delFuids[] = "'$pcode/PORTAL'";
    $delFuids[] = "'$pcode/REPORT'";
    $delFuids[] = "'$pcode'";
    foreach ($cats as $ccode => $cdesc) {
        $delFuids[] = "'{$pcode}_{$ccode}'";
    }
}
$fuidList = implode(',', $delFuids);

$pcodeArr = [];
foreach ($pcodes as $pcode) { $pcodeArr[] = "'$pcode'"; }
$pcodeList = implode(',', $pcodeArr);

runDel('STEP 0: SYURLM cleanup',
    "DELETE FROM S5HDSDATA.SYURLM WHERE FUID IN ($fuidList)");
runDel('STEP 0: SYPORT cleanup',
    "DELETE FROM S5HDSDATA.SYPORT WHERE FPPORT IN ($pcodeList)");
runDel('STEP 0: SYROLD cleanup',
    "DELETE FROM S5HDSDATA.SYROLD WHERE RDPORT IN ($pcodeList)");
runDel('STEP 0: SYPORR cleanup',
    "DELETE FROM S5HDSDATA.SYPORR WHERE PRPORT IN ($pcodeList)");

// ============================================================
// STEP 1: SYURLM — portal-level entries  (5 rows)
//   FUID=PCODE/PORTAL  FUTRGT=''  FURESV=''  FUTSPT='Y'
// ============================================================

foreach ($portals as $pcode => $pdesc) {
    $fuid   = "$pcode/PORTAL";
    $url    = "$landingBase?portal=$pcode";
    $pdescu = strtoupper($pdesc);
    runSql("SYURLM $fuid",
        "INSERT INTO S5HDSDATA.SYURLM
             (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,
              FUTSTP,FUTSUS,FUTSWS,FUTSPT)
         SELECT '$fuid','$pdesc','$pdesc','','$url','','','$pdescu',
                CURRENT_TIMESTAMP,'RESTORE','','Y'
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM S5HDSDATA.SYURLM WHERE FUID='$fuid')");
}

// ============================================================
// STEP 2: SYURLM — category sub-items  (30 rows)
//   FUTRGT='_blank'  FURESV='Y'  FUDESCU=''  FUTSPT=''
// ============================================================

foreach ($portals as $pcode => $pdesc) {
    foreach ($cats as $ccode => $cdesc) {
        $fuid   = "{$pcode}_{$ccode}";
        $url    = "$landingBase?portal=$pcode&cat=$ccode";
        $futitl = "$pdesc - $cdesc";
        runSql("SYURLM $fuid",
            "INSERT INTO S5HDSDATA.SYURLM
                 (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,
                  FUTSTP,FUTSUS,FUTSWS,FUTSPT)
             SELECT '$fuid','$cdesc','$futitl','_blank','$url','','Y','',
                    CURRENT_TIMESTAMP,'','',''
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM S5HDSDATA.SYURLM WHERE FUID='$fuid')");
    }
}

// ============================================================
// STEP 3: SYPORT — top-level nav tabs  (5 rows)
//   FPID=PCODE/PORTAL  FPDESC=''  FPRESV=''  FPDESCU=''  FPTSPT='Y'
// ============================================================

foreach ($portals as $pcode => $pdesc) {
    $fpid = "$pcode/PORTAL";
    runSql("SYPORT top $pcode",
        "INSERT INTO S5HDSDATA.SYPORT
             (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,
              FPTSTP,FPTSUS,FPTSWS,FPTSPT)
         SELECT '$pcode','',1,'$fpid','','$pdesc','','',
                CURRENT_TIMESTAMP,'RESTORE','','Y'
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM S5HDSDATA.SYPORT WHERE FPPORT='$pcode' AND FPPAGE='')");
}

// ============================================================
// STEP 4: SYPORT — category sub-items  (30 rows)
//   FPSEQ=1-6  FPDESC=cdesc  FPRESV=''  FPDESCU=''  FPTSPT=''
// ============================================================

foreach ($portals as $pcode => $pdesc) {
    $catseq = 0;
    foreach ($cats as $ccode => $cdesc) {
        $catseq++;
        $fpid   = "{$pcode}_{$ccode}";
        $fptitl = "$pdesc - $cdesc";
        runSql("SYPORT $fpid",
            "INSERT INTO S5HDSDATA.SYPORT
                 (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,
                  FPTSTP,FPTSUS,FPTSWS,FPTSPT)
             SELECT '$pcode','$pcode',$catseq,'$fpid','$cdesc','$fptitl','','',
                    CURRENT_TIMESTAMP,'RESTORE','',''
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM S5HDSDATA.SYPORT WHERE FPPORT='$pcode' AND FPID='$fpid')");
    }
}

// ============================================================
// STEP 5: SYROLD — role-to-portal assignments  (42 x 5 = 210 rows)
// ============================================================

$plist = array_keys($portals);
foreach ($roleMaxSeq as $role => $maxseq) {
    foreach ($plist as $i => $pcode) {
        $seq = $maxseq + 1 + $i;
        runSql("SYROLD $role/$pcode",
            "INSERT INTO S5HDSDATA.SYROLD
                 (RDROLE,RDPORT,RDSEQN,RDRESV,RDTSTP,RDTSUS,RDTSWS,RDTSPT)
             SELECT '$role','$pcode',$seq,'',CURRENT_TIMESTAMP,'RESTORE','RESTORE',''
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM S5HDSDATA.SYROLD
                 WHERE RDROLE='$role' AND RDPORT='$pcode')");
    }
}

// ============================================================
// STEP 6: SYPGMO — program registration  (SG5STDPGM, 8 rows)
// SG5STDPGM is not wiped by S5HDSDATA refresh — likely present.
// ============================================================

foreach ($pgms as $pgmid => $pgmdesc) {
    runSql("SYPGMO $pgmid",
        "INSERT INTO SG5STDPGM.SYPGMO (SOPGID,SOMOPT,SOMDES,SORESV)
         SELECT '$pgmid',1,'$pgmdesc',' '
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM SG5STDPGM.SYPGMO WHERE SOPGID='$pgmid' AND SOMOPT=1)");
}

// ============================================================
// STEP 7: SYPGMS — program option security  (49 x 8 = 392 rows)
// ============================================================

foreach ($users as $user) {
    foreach ($pgms as $pgmid => $pgmdesc) {
        runSql("SYPGMS $user/$pgmid",
            "INSERT INTO S5HDSDATA.SYPGMS
                 (SPUSER,SPPGID,SPOP01,SPOP02,SPOP03,SPOP04,SPOP05,SPOP06,
                  SPOP07,SPOP08,SPOP09,SPOP10,SPOP11,SPOP12,SPOP13,SPOP14,SPOP15)
             SELECT '$user','$pgmid','Y','N','N','N','N','N',
                    'N','N','N','N','N','N','N','N','N'
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM S5HDSDATA.SYPGMS
                 WHERE SPUSER='$user' AND SPPGID='$pgmid')");
    }
}

// ============================================================
// STEP 8: SYPORR — portal role permissions  (41 x 5 x 7 = 1,435 rows)
//   Per role+portal: 1 top-level row (PRPAGE='') +
//                    6 sub-item rows (PRPAGE=pcode, PRSEQ=1-6)
//   HD_ALL_SG is excluded: it uses a bypass and must have 0 SYPORR rows
//   to retain all-portal access. Adding rows switches it to filtered mode.
// ============================================================

$bypassRoles = ['HD_ALL_SG'];

foreach ($roleMaxSeq as $role => $maxseq) {
    if (in_array($role, $bypassRoles)) continue;
    foreach ($portals as $pcode => $pdesc) {

        // Top-level row
        $prid_top = "$role/$pcode";
        runSql("SYPORR top $role/$pcode",
            "INSERT INTO S5HDSDATA.SYPORR
                 (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
             SELECT '$role','$pcode','',1,'$prid_top','Y',
                    CURRENT_TIMESTAMP,'RESTORE',''
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM S5HDSDATA.SYPORR
                 WHERE PRROLE='$role' AND PRPORT='$pcode' AND PRPAGE='')");

        // Sub-item rows 1-6
        for ($i = 1; $i <= 6; $i++) {
            $seqstr   = number_format($i, 2);
            $prid_sub = "$role/$pcode/$pcode/$seqstr";
            runSql("SYPORR sub $role/$pcode/$i",
                "INSERT INTO S5HDSDATA.SYPORR
                     (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
                 SELECT '$role','$pcode','$pcode',$i,'$prid_sub','Y',
                        CURRENT_TIMESTAMP,'RESTORE',''
                 FROM SYSIBM.SYSDUMMY1
                 WHERE NOT EXISTS (
                     SELECT 1 FROM S5HDSDATA.SYPORR
                     WHERE PRROLE='$role' AND PRPORT='$pcode'
                       AND PRPAGE='$pcode' AND PRSEQ=$i)");
        }
    }
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG EIP Restore</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 24px; }
.header {
    background: linear-gradient(135deg, #2a5a8c 0%, #1a3d5c 100%);
    color: #fff; padding: 14px 24px; border-radius: 6px;
    border-bottom: 3px solid #f90; margin-bottom: 20px;
}
.header h2 { font-size: 20px; }
.header .sub { font-size: 12px; opacity: .75; margin-top: 3px; }
.summary { display: flex; gap: 16px; margin-bottom: 20px; }
.card { background: #fff; border-radius: 6px; padding: 14px 24px;
        border-left: 4px solid #ccc; min-width: 110px; text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,.06); }
.card.del  { border-color: #1565c0; }
.card.ok   { border-color: #2e7d32; }
.card.skip { border-color: #e65100; }
.card.fail { border-color: #c62828; }
.card .num { font-size: 32px; font-weight: bold; color: #333; }
.card .lbl { font-size: 11px; color: #666; margin-top: 4px; }
table { width: 100%; border-collapse: collapse; background: #fff;
        border-radius: 6px; overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,.06); }
th { background: #2a5a8c; color: #fff; padding: 8px 14px;
     text-align: left; font-size: 12px; }
td { padding: 5px 14px; font-size: 12px;
     border-bottom: 1px solid #f0f2f5; font-family: monospace; }
tr.del  td:first-child { color: #1565c0; font-weight: bold; }
tr.ok   td:first-child { color: #2e7d32; font-weight: bold; }
tr.skip td:first-child { color: #999; }
tr.fail td { color: #c62828; font-weight: bold; }
</style>
</head>
<body>
<div class="header">
  <h2>SG Custom EIP Restore</h2>
  <div class="sub">S5HDSDATA &nbsp;|&nbsp; SG5STDPGM
       &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>
<div class="summary">
  <div class="card del">
    <div class="num"><?= $cntDel ?></div>
    <div class="lbl">Cleaned Up</div>
  </div>
  <div class="card ok">
    <div class="num"><?= $cntOk ?></div>
    <div class="lbl">Inserted</div>
  </div>
  <div class="card skip">
    <div class="num"><?= $cntSkip ?></div>
    <div class="lbl">Already Existed</div>
  </div>
  <div class="card fail">
    <div class="num"><?= $cntFail ?></div>
    <div class="lbl">Failed</div>
  </div>
</div>
<table>
  <tr><th>Status</th><th>Item</th><th>Note</th></tr>
  <?php foreach ($log as list($status, $label, $note)): ?>
  <tr class="<?= strtolower($status) ?>">
    <td><?= $status ?></td>
    <td><?= htmlspecialchars($label) ?></td>
    <td><?= htmlspecialchars($note) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
</body>
</html>
