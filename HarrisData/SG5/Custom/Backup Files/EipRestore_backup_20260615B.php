<?php
// ============================================================
// SG Custom EIP Restore Script — SG5 Test Environment
// ============================================================
// Run after any S5HDSDATA database refresh or Harris upgrade.
// All INSERTs are guarded by NOT EXISTS — safe to re-run.
//
// URL:
//   https://portal.screen-graphics.com:5610/Custom/SG/EipRestore.php?confirm=RESTORE
// ============================================================

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'RESTORE') {
    die('<h2>SG EIP Restore</h2><p>Add <code>?confirm=RESTORE</code> to the URL to run.</p>');
}

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) {
    die('<pre>DB2 connection failed: ' . htmlspecialchars(db2_conn_errormsg()) . '</pre>');
}

$cntOk = $cntSkip = $cntFail = 0;
$log   = [];

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

// Max existing RDSEQN per role — SG portals inserted after these
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

// All IBM i user profiles that need program option security grants
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
// STEP 1: SYURLM — URL Master  (35 rows)
// ============================================================

// 5 portal-level landing pages
foreach ($portals as $pcode => $pdesc) {
    $url    = $landingBase . '?portal=' . $pcode;
    $pdescu = strtoupper($pdesc);
    runSql("SYURLM $pcode",
        "INSERT INTO S5HDSDATA.SYURLM
             (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,FUTSTP,FUTSUS,FUTSWS,FUTSPT)
         SELECT '$pcode','$pdesc','$pdesc','_blank','$url','',' ','$pdescu',
                CURRENT_TIMESTAMP,'RESTORE','RESTORE',' '
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (SELECT 1 FROM S5HDSDATA.SYURLM WHERE FUID='$pcode')");
}

// 30 category-level landing pages
foreach ($portals as $pcode => $pdesc) {
    foreach ($cats as $ccode => $cdesc) {
        $fuid   = $pcode . '_' . $ccode;
        $url    = $landingBase . '?portal=' . $pcode . '&cat=' . $ccode;
        $futitl = $pdesc . ' - ' . $cdesc;
        $cdescu = strtoupper($cdesc);
        runSql("SYURLM $fuid",
            "INSERT INTO S5HDSDATA.SYURLM
                 (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,FUTSTP,FUTSUS,FUTSWS,FUTSPT)
             SELECT '$fuid','$cdesc','$futitl','_blank','$url','',' ','$cdescu',
                    CURRENT_TIMESTAMP,'RESTORE','RESTORE',' '
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (SELECT 1 FROM S5HDSDATA.SYURLM WHERE FUID='$fuid')");
    }
}

// ============================================================
// STEP 2: SYPORT — Portal Menu Entries  (35 rows)
// ============================================================

// 5 top-level nav tabs (FPPAGE = '')
foreach ($portals as $pcode => $pdesc) {
    $pdescu = strtoupper($pdesc);
    runSql("SYPORT top $pcode",
        "INSERT INTO S5HDSDATA.SYPORT
             (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,FPTSTP,FPTSUS,FPTSWS,FPTSPT)
         SELECT '$pcode',''  ,1,'$pcode','$pdesc','$pdesc',' ','$pdescu',
                CURRENT_TIMESTAMP,'RESTORE','RESTORE',' '
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM S5HDSDATA.SYPORT WHERE FPPORT='$pcode' AND FPPAGE='' AND FPID='$pcode')");
}

// 30 sub-items (FPPAGE = parent portal code, FPSEQ 10-60)
foreach ($portals as $pcode => $pdesc) {
    $catseq = 0;
    foreach ($cats as $ccode => $cdesc) {
        $catseq += 10;
        $fpid   = $pcode . '_' . $ccode;
        $fptitl = $pdesc . ' - ' . $cdesc;
        $cdescu = strtoupper($cdesc);
        runSql("SYPORT $fpid",
            "INSERT INTO S5HDSDATA.SYPORT
                 (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,FPTSTP,FPTSUS,FPTSWS,FPTSPT)
             SELECT '$pcode','$pcode',$catseq,'$fpid','$cdesc','$fptitl',' ','$cdescu',
                    CURRENT_TIMESTAMP,'RESTORE','RESTORE',' '
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM S5HDSDATA.SYPORT WHERE FPPORT='$pcode' AND FPID='$fpid')");
    }
}

// ============================================================
// STEP 3: SYROLD — Role-to-Portal Assignments  (42 × 5 = 210 rows)
// Each role gets all 5 SG portals sequenced after its existing max
// ============================================================

$plist = array_keys($portals);
foreach ($roleMaxSeq as $role => $maxseq) {
    foreach ($plist as $i => $pcode) {
        $seq = $maxseq + 1 + $i;
        runSql("SYROLD $role/$pcode",
            "INSERT INTO S5HDSDATA.SYROLD
                 (RDROLE,RDPORT,RDSEQN,RDRESV,RDTSTP,RDTSUS,RDTSWS,RDTSPT)
             SELECT '$role','$pcode',$seq,' ',CURRENT_TIMESTAMP,'RESTORE','RESTORE',' '
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM S5HDSDATA.SYROLD WHERE RDROLE='$role' AND RDPORT='$pcode')");
    }
}

// ============================================================
// STEP 4: SYPGMO — Program Registration  (SG5STDPGM, 8 rows)
// Note: SG5STDPGM was NOT wiped by the S5HDSDATA refresh —
// these are likely already present. Safe to re-run regardless.
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
// STEP 5: SYPGMS — Program Option Security  (49 users × 8 pgms = 392 rows)
// Grants View (SPOP01=Y) for all SG programs to every user profile.
// HD_ALL_SG bypasses this check; all other users require an explicit row.
// ============================================================

foreach ($users as $user) {
    foreach ($pgms as $pgmid => $pgmdesc) {
        runSql("SYPGMS $user/$pgmid",
            "INSERT INTO S5HDSDATA.SYPGMS
                 (SPUSER,SPPGID,SPOP01,SPOP02,SPOP03,SPOP04,SPOP05,SPOP06,
                  SPOP07,SPOP08,SPOP09,SPOP10,SPOP11,SPOP12,SPOP13,SPOP14,SPOP15)
             SELECT '$user','$pgmid','Y','N','N','N','N','N','N','N','N','N','N','N','N','N','N'
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM S5HDSDATA.SYPGMS WHERE SPUSER='$user' AND SPPGID='$pgmid')");
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
td { padding: 5px 14px; font-size: 12px; border-bottom: 1px solid #f0f2f5; font-family: monospace; }
tr.ok   td:first-child { color: #2e7d32; font-weight: bold; }
tr.skip td:first-child { color: #999; }
tr.fail td { color: #c62828; font-weight: bold; }
</style>
</head>
<body>
<div class="header">
  <h2>SG Custom EIP Restore</h2>
  <div class="sub">S5HDSDATA &nbsp;|&nbsp; SG5STDPGM &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
</div>
<div class="summary">
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
