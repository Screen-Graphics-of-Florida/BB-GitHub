<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
$conn = $i5Connect->getConnection();

$pList = "'SGINQ','SGDASH','SGDINT','SGRPT','SGSOP'";
$rList = "'ACCOUNTING','ACCOUNTMAN','COLLECTION','CUSTSRVC','CUSTSRVMGR','EBORRELL','ENAPOLES','HD_ALL','HD_ALL_SG','INVENTORY','LARIANN','MCRESPO','MFGVP','PLANNING','PLANPRDPUR','PRODMANAGR','PRODUCTION','PURCHASING','SALES','SALESADMIN','TIFFANY','WIDGETS','HD_SLSM'";

if (isset($_POST['revert'])) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Revert Today</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}</style></head><body>';
    echo '<h1>Full Rollback</h1>';

    // 1. Clear SYPORR for 23 roles x SG portals
    $r = db2_exec($conn,"DELETE FROM S5HDSDATA.SYPORR WHERE TRIM(PRROLE) IN (".$rList.") AND PRPORT IN (".$pList.")");
    if ($r) echo '<span class="ok">OK</span> SYPORR cleared: '.db2_num_rows($r).' rows deleted<br>';
    else    echo '<span class="err">ERR</span> SYPORR: '.htmlspecialchars(db2_stmt_errormsg()).'<br>';

    // 2. Clear S5HDSDATA.SYPORT for SG portals entirely
    $r = db2_exec($conn,"DELETE FROM S5HDSDATA.SYPORT WHERE FPPORT IN (".$pList.")");
    if ($r) echo '<span class="ok">OK</span> SYPORT cleared: '.db2_num_rows($r).' rows deleted<br>';
    else    echo '<span class="err">ERR</span> SYPORT: '.htmlspecialchars(db2_stmt_errormsg()).'<br>';

    // 3. Copy SYPORT from Live
    $rLive = db2_exec($conn,"SELECT * FROM SGHDSDATA.SYPORT WHERE FPPORT IN (".$pList.") ORDER BY FPPORT,FPSEQ");
    $ins=0; $err=0;
    if ($rLive) {
        $nc=db2_num_fields($rLive); $cols=array();
        for($i=0;$i<$nc;$i++) $cols[]=db2_field_name($rLive,$i);
        while ($row=db2_fetch_assoc($rLive)) {
            $vals=array();
            foreach($cols as $c) $vals[]="'".str_replace("'","''",(string)rtrim($row[$c]))."'";
            $ri=db2_exec($conn,"INSERT INTO S5HDSDATA.SYPORT (".implode(',',$cols).") VALUES (".implode(',',$vals).")");
            if ($ri) $ins++; else { $err++; echo '<span class="err">SYPORT INSERT ERR</span> '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }
        }
        echo '<span class="ok">OK</span> SYPORT restored from Live: '.$ins.' rows inserted, '.$err.' errors<br>';
    } else echo '<span class="err">ERR</span> Cannot read Live SYPORT: '.htmlspecialchars(db2_stmt_errormsg()).'<br>';

    // 4. Clear S5HDSDATA.SYROLD for SG portals
    $r = db2_exec($conn,"DELETE FROM S5HDSDATA.SYROLD WHERE RDPORT IN (".$pList.")");
    if ($r) echo '<span class="ok">OK</span> SYROLD cleared: '.db2_num_rows($r).' rows deleted<br>';
    else    echo '<span class="err">ERR</span> SYROLD: '.htmlspecialchars(db2_stmt_errormsg()).'<br>';

    // 5. Copy SYROLD from Live
    $rLive2 = db2_exec($conn,"SELECT * FROM SGHDSDATA.SYROLD WHERE RDPORT IN (".$pList.") ORDER BY RDPORT,RDROLE");
    $ins2=0; $err2=0;
    if ($rLive2) {
        $nc=db2_num_fields($rLive2); $cols=array();
        for($i=0;$i<$nc;$i++) $cols[]=db2_field_name($rLive2,$i);
        while ($row=db2_fetch_assoc($rLive2)) {
            $vals=array();
            foreach($cols as $c) $vals[]="'".str_replace("'","''",(string)rtrim($row[$c]))."'";
            $ri=db2_exec($conn,"INSERT INTO S5HDSDATA.SYROLD (".implode(',',$cols).") VALUES (".implode(',',$vals).")");
            if ($ri) $ins2++; else { $err2++; echo '<span class="err">SYROLD INSERT ERR</span> '.htmlspecialchars(db2_stmt_errormsg()).'<br>'; }
        }
        echo '<span class="ok">OK</span> SYROLD restored from Live: '.$ins2.' rows inserted, '.$err2.' errors<br>';
    } else echo '<span class="err">ERR</span> Cannot read Live SYROLD: '.htmlspecialchars(db2_stmt_errormsg()).'<br>';

    // 6. Revert SYDSGN PDUSER back to BILL
    $r = db2_exec($conn,"UPDATE SG5STDPGM.SYDSGN SET PDUSER='BILL' WHERE PDTBID=199 AND PDPGID=0");
    if ($r) echo '<span class="ok">OK</span> SYDSGN PDUSER reverted to BILL: '.db2_num_rows($r).' rows<br>';
    else    echo '<span class="err">ERR</span> SYDSGN: '.htmlspecialchars(db2_stmt_errormsg()).'<br>';

    echo '<br><strong>Done. Log out and back in as BBUSCH — SG portals should be visible again.</strong>';
    echo '</body></html>'; exit;
}

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Revert Today</title>
<style>body{font-family:monospace;font-size:13px;padding:20px;}
.warn{background:#fee;border:2px solid #c00;padding:14px;margin:12px 0;}
ul{margin:8px 0 8px 20px;line-height:1.8;}
</style></head><body><h1>Full Rollback — Revert All Changes Today</h1>';
echo '<div class="warn"><b>Will revert:</b><ul>
<li>SYPORR: delete all SG portal rows for 23 roles</li>
<li>SYPORT: delete all SG portal rows, restore from Live (SGHDSDATA)</li>
<li>SYROLD: delete all SG portal rows, restore from Live (SGHDSDATA)</li>
<li>SYDSGN: set PDUSER back to BILL (restores Configuration/Portal error state)</li>
</ul></div>';
echo '<form method="post"><button name="revert" value="1" style="background:#c00;color:#fff;padding:12px 32px;font-size:15px;">REVERT ALL TODAY\'S CHANGES</button></form>';
echo '</body></html>';
?>
