<?php
// ============================================================
// SG Custom EIP Live Push — ALL Menus, ALL Roles
// Inserts all 5 SG portals x 6 sub-items into LIVE EIP.
// Targets SGHDSDATA (menu tables) + HDSSTDPGM (program table).
// All inserts are WHERE NOT EXISTS — safe to re-run anytime.
//
// Run EipBackupLive.php?confirm=BACKUP first, then:
//
// URL:
//   https://portal.screen-graphics.com:5601/Custom/SG/PushAllMenusLive.php?confirm=PUSH
// ============================================================

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'PUSH') {
    die('<h2>SG EIP Live Push &mdash; All Menus</h2>'
      . '<p><strong>Run '
      . '<a href="EipBackupLive.php?confirm=BACKUP">EipBackupLive.php?confirm=BACKUP</a>'
      . ' first.</strong></p>'
      . '<p>Then add <code>?confirm=PUSH</code> to this URL to execute.</p>');
}

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) {
    die('<pre>DB2 connection failed: '
      . htmlspecialchars(db2_conn_errormsg()) . '</pre>');
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
    'INVMGMT' => 'Inventory Mgmt',
    'MFG'     => 'Manufacturing',
    'OE'      => 'Order Entry',
    'PLN'     => 'Planning',
    'PUR'     => 'Purchasing',
];

$roles = [
    'ACCOUNTING','ACCOUNTMAN','COLLECTION','CONSULTANT','CUSTSRVC','CUSTSRVMGR',
    'EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','HD_CUST','HD_EMPL',
    'HD_FIX','HD_HRADMIN','HD_MOBILE','HD_SECURE','HD_SLSM','HD_VEND',
    'INSTALLER','INVENTORY','LARIANN','MCRESPO','MFGVP','PLANNING',
    'PLANPRDFUR','PRODMANAGR','PRODUCTION','PURCHASING','QC01','RECEIVING',
    'RESTRRECPT','SALES','SALESADMIN','SHIPPING','SHOPLEADER','SHOPREPORT',
    'TEMPUSER','TIFFANY','TVWILLIAMS','USERTEST','WIDGET','WIDGETS',
];
$bypassRoles = ['HD_ALL_SG'];

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

$pgms = [
    'SGPORTLND' => 'Portal Landing Page',
    'MOREQ'     => 'MO Requirements',
    'BOOKDASH'  => 'Bookings Dashboard',
    'BOOKDRLL'  => 'Bookings Drilldown',
    'SALESDASH' => 'Sales Dashboard',
    'SALESDRLL' => 'Sales Drilldown',
    'SHIPSDASH' => 'Shipments Dashboard',
    'OPENORDLC' => 'OO Line Item Cmts',
    'CSSRVINQ'  => 'CS Service Inquiry',
    'CSDATINT'  => 'CS Data Integrity',
    'INVDATINT' => 'Inv Data Integrity',
    'MODLYLBR'  => 'MO Daily Labor',
    'MOMATLCMP' => 'MO Material Comps',
];

$landingBase = '@@homeURL@@phpPathCustom/SG/sg_portal_landing.php';

$pcodes = array_keys($portals);

// ============================================================
// STEP 1: SYURLM — portal-level entries (5 rows)
// ============================================================

foreach ($portals as $pcode => $pdesc) {
    $fuid    = "$pcode/PORTAL";
    $url     = "$landingBase?portal=$pcode";
    $pdescu  = strtoupper($pdesc);
    runSql("SYURLM $fuid",
        "INSERT INTO SGHDSDATA.SYURLM
             (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,
              FUTSTP,FUTSUS,FUTSWS,FUTSPT)
         SELECT '$fuid','$pdesc','$pdesc','','$url','','','$pdescu',
                CURRENT_TIMESTAMP,'PUSHALL','','Y'
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM SGHDSDATA.SYURLM WHERE FUID='$fuid')");
}

// ============================================================
// STEP 2: SYURLM — category sub-items (30 rows)
// ============================================================

foreach ($portals as $pcode => $pdesc) {
    foreach ($cats as $ccode => $cdesc) {
        $fuid   = "{$pcode}_{$ccode}";
        $url    = "$landingBase?portal=$pcode&cat=$ccode";
        $futitl = "$pdesc - $cdesc";
        runSql("SYURLM $fuid",
            "INSERT INTO SGHDSDATA.SYURLM
                 (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,
                  FUTSTP,FUTSUS,FUTSWS,FUTSPT)
             SELECT '$fuid','$cdesc','$futitl','_blank','$url','','Y','',
                    CURRENT_TIMESTAMP,'','',''
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM SGHDSDATA.SYURLM WHERE FUID='$fuid')");
    }
}

// ============================================================
// STEP 3: SYPORT — top-level nav tabs (5 rows)
// ============================================================

foreach ($portals as $pcode => $pdesc) {
    $fpid = "$pcode/PORTAL";
    runSql("SYPORT top $pcode",
        "INSERT INTO SGHDSDATA.SYPORT
             (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,
              FPTSTP,FPTSUS,FPTSWS,FPTSPT)
         SELECT '$pcode','',1,'$fpid','','$pdesc','','',
                CURRENT_TIMESTAMP,'PUSHALL','','Y'
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM SGHDSDATA.SYPORT
             WHERE FPPORT='$pcode' AND FPPAGE='')");
}

// ============================================================
// STEP 4: SYPORT — category sub-items (30 rows)
// ============================================================

foreach ($portals as $pcode => $pdesc) {
    $catseq = 0;
    foreach ($cats as $ccode => $cdesc) {
        $catseq++;
        $fpid   = "{$pcode}_{$ccode}";
        $fptitl = "$pdesc - $cdesc";
        runSql("SYPORT $fpid",
            "INSERT INTO SGHDSDATA.SYPORT
                 (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,
                  FPTSTP,FPTSUS,FPTSWS,FPTSPT)
             SELECT '$pcode','$pcode',$catseq,'$fpid','$cdesc','$fptitl','','',
                    CURRENT_TIMESTAMP,'PUSHALL','',''
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM SGHDSDATA.SYPORT
                 WHERE FPPORT='$pcode' AND FPID='$fpid')");
    }
}

// ============================================================
// STEP 5: SYROLD — role-to-portal assignments (42 x 5 = 210 rows)
// ============================================================

foreach ($roles as $role) {
    foreach ($pcodes as $pcode) {
        runSql("SYROLD $role/$pcode",
            "INSERT INTO SGHDSDATA.SYROLD
                 (RDROLE,RDPORT,RDSEQN,RDRESV,RDTSTP,RDTSUS,RDTSWS,RDTSPT)
             SELECT '$role','$pcode',
                    COALESCE((SELECT MAX(RDSEQN) FROM SGHDSDATA.SYROLD
                              WHERE RDROLE='$role'),0)+1,
                    '',CURRENT_TIMESTAMP,'PUSHALL','PUSHALL',''
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM SGHDSDATA.SYROLD
                 WHERE RDROLE='$role' AND RDPORT='$pcode')");
    }
}

// ============================================================
// STEP 6: SYPORR — portal role permissions
//   HD_ALL_SG excluded (bypass role — rows break all-portal access)
//   Per role+portal: 1 top-level row + 6 sub-item rows
// ============================================================

foreach ($roles as $role) {
    if (in_array($role, $bypassRoles)) continue;
    foreach ($portals as $pcode => $pdesc) {

        $prid_top = "$role/$pcode";
        runSql("SYPORR top $role/$pcode",
            "INSERT INTO SGHDSDATA.SYPORR
                 (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
             SELECT '$role','$pcode','',1,'$prid_top','Y',
                    CURRENT_TIMESTAMP,'PUSHALL',''
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM SGHDSDATA.SYPORR
                 WHERE PRROLE='$role' AND PRPORT='$pcode' AND PRPAGE='')");

        for ($i = 1; $i <= 6; $i++) {
            $seqstr   = number_format($i, 2);
            $prid_sub = "$role/$pcode/$pcode/$seqstr";
            runSql("SYPORR sub $role/$pcode/$i",
                "INSERT INTO SGHDSDATA.SYPORR
                     (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
                 SELECT '$role','$pcode','$pcode',$i,'$prid_sub','Y',
                        CURRENT_TIMESTAMP,'PUSHALL',''
                 FROM SYSIBM.SYSDUMMY1
                 WHERE NOT EXISTS (
                     SELECT 1 FROM SGHDSDATA.SYPORR
                     WHERE PRROLE='$role' AND PRPORT='$pcode'
                       AND PRPAGE='$pcode' AND PRSEQ=$i)");
        }
    }
}

// ============================================================
// STEP 7: HDSSTDPGM.SYPGMO — program registration (13 rows)
// ============================================================

foreach ($pgms as $pgmid => $pgmdesc) {
    runSql("SYPGMO $pgmid",
        "INSERT INTO HDSSTDPGM.SYPGMO (SOPGID,SOMOPT,SOMDES,SORESV)
         SELECT '$pgmid',1,'$pgmdesc',' '
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM HDSSTDPGM.SYPGMO
             WHERE SOPGID='$pgmid' AND SOMOPT=1)");
}

// ============================================================
// STEP 8: SGHDSDATA.SYPGMS — user-program security
//   49 users x 13 programs = 637 rows, Option 1 = Y
// ============================================================

foreach ($users as $user) {
    foreach ($pgms as $pgmid => $pgmdesc) {
        runSql("SYPGMS $user/$pgmid",
            "INSERT INTO SGHDSDATA.SYPGMS
                 (SPUSER,SPPGID,SPOP01,SPOP02,SPOP03,SPOP04,SPOP05,SPOP06,
                  SPOP07,SPOP08,SPOP09,SPOP10,SPOP11,SPOP12,SPOP13,SPOP14,SPOP15)
             SELECT '$user','$pgmid','Y','N','N','N','N','N',
                    'N','N','N','N','N','N','N','N','N'
             FROM SYSIBM.SYSDUMMY1
             WHERE NOT EXISTS (
                 SELECT 1 FROM SGHDSDATA.SYPGMS
                 WHERE SPUSER='$user' AND SPPGID='$pgmid')");
    }
}

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SG EIP Live Push — All Menus</title>
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
td { padding: 5px 14px; font-size: 12px;
     border-bottom: 1px solid #f0f2f5; font-family: monospace; }
tr.ok   td:first-child { color: #2e7d32; font-weight: bold; }
tr.skip td:first-child { color: #999; }
tr.fail td { color: #c62828; font-weight: bold; }
</style>
</head>
<body>
<div class="header">
  <h2>SG EIP Live Push &mdash; All Menus</h2>
  <div class="sub">SGHDSDATA &nbsp;|&nbsp; HDSSTDPGM
       &nbsp;|&nbsp; <?= date('Y-m-d H:i:s') ?></div>
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
