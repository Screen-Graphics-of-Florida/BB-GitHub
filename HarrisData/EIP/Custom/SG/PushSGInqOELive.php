<?php
// ============================================================
// SG EIP Live Push — SG Inquiries / Order Entry ONLY
// Adds SGINQ portal + OE sub-item to LIVE EIP (SGHDSDATA).
// Registers SGPORTLND and CSSRVINQ in HDSSTDPGM.SYPGMO.
//
// Run EipBackupLive.php?confirm=BACKUP FIRST, verify backup
// file exists in Backup Files, then run this script.
//
// URL:
//   https://portal.screen-graphics.com:5601/Custom/SG/PushSGInqOELive.php?confirm=PUSH
// ============================================================

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'PUSH') {
    die('<h2>SG EIP Live Push &mdash; SG Inquiries / Order Entry</h2>'
      . '<p><strong>Step 1:</strong> Run '
      . '<a href="EipBackupLive.php?confirm=BACKUP">EipBackupLive.php?confirm=BACKUP</a>'
      . ' and verify the backup file exists in Backup Files.</p>'
      . '<p><strong>Step 2:</strong> Add <code>?confirm=PUSH</code> to this URL to execute.</p>');
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

$landingBase = '@@homeURL@@phpPathCustom/SG/sg_portal_landing.php';

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
    'CSSRVINQ'  => 'CS Service Inquiry',
];

// ============================================================
// STEP 1: SYURLM — portal top-level entry (SGINQ/PORTAL)
// ============================================================

runSql('SYURLM SGINQ/PORTAL',
    "INSERT INTO SGHDSDATA.SYURLM
         (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,
          FUTSTP,FUTSUS,FUTSWS,FUTSPT)
     SELECT 'SGINQ/PORTAL','SG Inquiries','SG Inquiries','',
            '$landingBase?portal=SGINQ','','','SG INQUIRIES',
            CURRENT_TIMESTAMP,'PUSHOE','','Y'
     FROM SYSIBM.SYSDUMMY1
     WHERE NOT EXISTS (
         SELECT 1 FROM SGHDSDATA.SYURLM WHERE FUID='SGINQ/PORTAL')");

// ============================================================
// STEP 2: SYURLM — OE sub-item (SGINQ_OE)
// ============================================================

runSql('SYURLM SGINQ_OE',
    "INSERT INTO SGHDSDATA.SYURLM
         (FUID,FUDESC,FUTITL,FUTRGT,FUURL,FUIMG,FURESV,FUDESCU,
          FUTSTP,FUTSUS,FUTSWS,FUTSPT)
     SELECT 'SGINQ_OE','Order Entry','SG Inquiries - Order Entry','_blank',
            '$landingBase?portal=SGINQ&cat=OE','','Y','',
            CURRENT_TIMESTAMP,'','',''
     FROM SYSIBM.SYSDUMMY1
     WHERE NOT EXISTS (
         SELECT 1 FROM SGHDSDATA.SYURLM WHERE FUID='SGINQ_OE')");

// ============================================================
// STEP 3: SYPORT — top-level nav tab for SGINQ
// ============================================================

runSql('SYPORT top SGINQ',
    "INSERT INTO SGHDSDATA.SYPORT
         (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,
          FPTSTP,FPTSUS,FPTSWS,FPTSPT)
     SELECT 'SGINQ','',1,'SGINQ/PORTAL','','SG Inquiries','','',
            CURRENT_TIMESTAMP,'PUSHOE','','Y'
     FROM SYSIBM.SYSDUMMY1
     WHERE NOT EXISTS (
         SELECT 1 FROM SGHDSDATA.SYPORT
         WHERE FPPORT='SGINQ' AND FPPAGE='')");

// ============================================================
// STEP 4: SYPORT — OE sub-item
//   FPSEQ=4: ACCT=1, INVMGMT=2, MFG=3, OE=4, PLN=5, PUR=6
// ============================================================

runSql('SYPORT SGINQ_OE',
    "INSERT INTO SGHDSDATA.SYPORT
         (FPPORT,FPPAGE,FPSEQ,FPID,FPDESC,FPTITL,FPRESV,FPDESCU,
          FPTSTP,FPTSUS,FPTSWS,FPTSPT)
     SELECT 'SGINQ','SGINQ',4,'SGINQ_OE','Order Entry',
            'SG Inquiries - Order Entry','','',
            CURRENT_TIMESTAMP,'PUSHOE','',''
     FROM SYSIBM.SYSDUMMY1
     WHERE NOT EXISTS (
         SELECT 1 FROM SGHDSDATA.SYPORT
         WHERE FPPORT='SGINQ' AND FPID='SGINQ_OE')");

// ============================================================
// STEP 5: SYROLD — assign all roles to SGINQ portal
// ============================================================

foreach ($roles as $role) {
    runSql("SYROLD $role/SGINQ",
        "INSERT INTO SGHDSDATA.SYROLD
             (RDROLE,RDPORT,RDSEQN,RDRESV,RDTSTP,RDTSUS,RDTSWS,RDTSPT)
         SELECT '$role','SGINQ',
                COALESCE((SELECT MAX(RDSEQN) FROM SGHDSDATA.SYROLD
                          WHERE RDROLE='$role'),0)+1,
                '',CURRENT_TIMESTAMP,'PUSHOE','PUSHOE',''
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM SGHDSDATA.SYROLD
             WHERE RDROLE='$role' AND RDPORT='SGINQ')");
}

// ============================================================
// STEP 6: SYPORR — role permissions
//   HD_ALL_SG excluded (bypass role — adding rows breaks it)
//   Top-level row + OE sub-item row (PRSEQ=4) per role
// ============================================================

foreach ($roles as $role) {
    if (in_array($role, $bypassRoles)) continue;

    $prid_top = "$role/SGINQ";
    runSql("SYPORR top $role/SGINQ",
        "INSERT INTO SGHDSDATA.SYPORR
             (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
         SELECT '$role','SGINQ','',1,'$prid_top','Y',
                CURRENT_TIMESTAMP,'PUSHOE',''
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM SGHDSDATA.SYPORR
             WHERE PRROLE='$role' AND PRPORT='SGINQ' AND PRPAGE='')");

    $prid_oe = "$role/SGINQ/SGINQ/4.00";
    runSql("SYPORR OE $role/SGINQ",
        "INSERT INTO SGHDSDATA.SYPORR
             (PRROLE,PRPORT,PRPAGE,PRSEQ,PRID,PRSEL,PRTSTP,PRTSUS,PRTSPT)
         SELECT '$role','SGINQ','SGINQ',4,'$prid_oe','Y',
                CURRENT_TIMESTAMP,'PUSHOE',''
         FROM SYSIBM.SYSDUMMY1
         WHERE NOT EXISTS (
             SELECT 1 FROM SGHDSDATA.SYPORR
             WHERE PRROLE='$role' AND PRPORT='SGINQ'
               AND PRPAGE='SGINQ' AND PRSEQ=4)");
}

// ============================================================
// STEP 7: HDSSTDPGM.SYPGMO — program registration
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
// STEP 8: SGHDSDATA.SYPGMS — user-program security (Option 1=Y)
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
<title>SG EIP Live Push — SG Inquiries / OE</title>
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
  <h2>SG EIP Live Push &mdash; SG Inquiries / Order Entry</h2>
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
