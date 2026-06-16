<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>SG5 vs Live</title>
<style>
body{font-family:monospace;font-size:11px;padding:12px;}
h2{color:#00c;border-bottom:2px solid #00c;margin:16px 0 4px;}
h3{color:#800;margin:10px 0 3px;}
table{border-collapse:collapse;margin:4px 0 10px;}
td,th{border:1px solid #bbb;padding:2px 6px;white-space:nowrap;}
th{background:#ddd;}
.intest{background:#fdd;font-weight:bold;}
.inlive{background:#dfd;font-weight:bold;}
.diff{background:#ffa;font-weight:bold;}
.ok{color:green;}
</style></head><body>';

echo '<h1>SG5 (S5HDSDATA) vs Live (SGHDSDATA) — Full Comparison</h1>';
echo '<p><span style="background:#fdd;padding:2px 6px;">Red = in SG5 test ONLY (I may have inserted)</span> &nbsp;
      <span style="background:#dfd;padding:2px 6px;">Green = in Live ONLY (may have been deleted from test)</span> &nbsp;
      <span style="background:#ffa;padding:2px 6px;">Yellow = exists in both but VALUE DIFFERS</span></p>';

// -------------------------------------------------------
// 1. SYPORT rows in Live NOT in SG5 (by FPPORT+FPPAGE+FPID)
// -------------------------------------------------------
echo '<h2>1. SYPORT rows in LIVE but missing from SG5 test</h2>';
$r1 = db2_exec($conn,
    "SELECT L.FPPORT, L.FPPAGE, L.FPSEQ, L.FPID, L.FPTITL, L.FPRESV
     FROM SGHDSDATA.SYPORT L
     WHERE NOT EXISTS (
         SELECT 1 FROM S5HDSDATA.SYPORT T
         WHERE TRIM(T.FPPORT)=TRIM(L.FPPORT) AND TRIM(T.FPPAGE)=TRIM(L.FPPAGE) AND TRIM(T.FPID)=TRIM(L.FPID)
     )
     ORDER BY L.FPPORT, L.FPSEQ
     FETCH FIRST 100 ROWS ONLY");
echo '<table><tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPID</th><th>FPTITL</th><th>FPRESV</th></tr>';
$cnt1 = 0;
while ($row = db2_fetch_assoc($r1)) {
    echo '<tr class="inlive"><td>'.htmlspecialchars(trim($row['FPPORT'])).'</td><td>'.htmlspecialchars(trim($row['FPPAGE'])).'</td><td>'.htmlspecialchars(trim($row['FPSEQ'])).'</td><td>'.htmlspecialchars(trim($row['FPID'])).'</td><td>'.htmlspecialchars(trim($row['FPTITL'])).'</td><td>'.htmlspecialchars(trim($row['FPRESV'])).'</td></tr>';
    $cnt1++;
}
if ($cnt1===0) echo '<tr><td colspan="6"><em class="ok">No missing rows — SYPORT matches</em></td></tr>';
echo '</table>';

// 2. SYPORT rows in SG5 NOT in Live
echo '<h2>2. SYPORT rows in SG5 test but NOT in Live (I inserted these)</h2>';
$r2 = db2_exec($conn,
    "SELECT T.FPPORT, T.FPPAGE, T.FPSEQ, T.FPID, T.FPTITL, T.FPRESV
     FROM S5HDSDATA.SYPORT T
     WHERE NOT EXISTS (
         SELECT 1 FROM SGHDSDATA.SYPORT L
         WHERE TRIM(L.FPPORT)=TRIM(T.FPPORT) AND TRIM(L.FPPAGE)=TRIM(T.FPPAGE) AND TRIM(L.FPID)=TRIM(T.FPID)
     )
     ORDER BY T.FPPORT, T.FPSEQ
     FETCH FIRST 100 ROWS ONLY");
echo '<table><tr><th>FPPORT</th><th>FPPAGE</th><th>FPSEQ</th><th>FPID</th><th>FPTITL</th><th>FPRESV</th></tr>';
$cnt2 = 0;
while ($row = db2_fetch_assoc($r2)) {
    echo '<tr class="intest"><td>'.htmlspecialchars(trim($row['FPPORT'])).'</td><td>'.htmlspecialchars(trim($row['FPPAGE'])).'</td><td>'.htmlspecialchars(trim($row['FPSEQ'])).'</td><td>'.htmlspecialchars(trim($row['FPID'])).'</td><td>'.htmlspecialchars(trim($row['FPTITL'])).'</td><td>'.htmlspecialchars(trim($row['FPRESV'])).'</td></tr>';
    $cnt2++;
}
if ($cnt2===0) echo '<tr><td colspan="6"><em class="ok">No extra rows in SG5</em></td></tr>';
echo '</table>';

// -------------------------------------------------------
// 3. SYURLM rows in Live NOT in SG5
// -------------------------------------------------------
echo '<h2>3. SYURLM rows in LIVE but missing from SG5 test</h2>';
$r3 = db2_exec($conn,
    "SELECT L.FUID, L.FUDESC, L.FUURL, L.FURESV
     FROM SGHDSDATA.SYURLM L
     WHERE NOT EXISTS (
         SELECT 1 FROM S5HDSDATA.SYURLM T WHERE TRIM(T.FUID)=TRIM(L.FUID)
     )
     ORDER BY L.FUID
     FETCH FIRST 100 ROWS ONLY");
echo '<table><tr><th>FUID</th><th>FUDESC</th><th>FUURL</th><th>FURESV</th></tr>';
$cnt3 = 0;
while ($row = db2_fetch_assoc($r3)) {
    echo '<tr class="inlive"><td>'.htmlspecialchars(trim($row['FUID'])).'</td><td>'.htmlspecialchars(trim($row['FUDESC'])).'</td><td>'.htmlspecialchars(trim($row['FUURL'])).'</td><td>'.htmlspecialchars(trim($row['FURESV'])).'</td></tr>';
    $cnt3++;
}
if ($cnt3===0) echo '<tr><td colspan="4"><em class="ok">No missing rows — SYURLM matches</em></td></tr>';
echo '</table>';

// 4. SYURLM rows in SG5 NOT in Live
echo '<h2>4. SYURLM rows in SG5 test but NOT in Live</h2>';
$r4 = db2_exec($conn,
    "SELECT T.FUID, T.FUDESC, T.FUURL, T.FURESV
     FROM S5HDSDATA.SYURLM T
     WHERE NOT EXISTS (
         SELECT 1 FROM SGHDSDATA.SYURLM L WHERE TRIM(L.FUID)=TRIM(T.FUID)
     )
     ORDER BY T.FUID
     FETCH FIRST 100 ROWS ONLY");
echo '<table><tr><th>FUID</th><th>FUDESC</th><th>FUURL</th><th>FURESV</th></tr>';
$cnt4 = 0;
while ($row = db2_fetch_assoc($r4)) {
    echo '<tr class="intest"><td>'.htmlspecialchars(trim($row['FUID'])).'</td><td>'.htmlspecialchars(trim($row['FUDESC'])).'</td><td>'.htmlspecialchars(trim($row['FUURL'])).'</td><td>'.htmlspecialchars(trim($row['FURESV'])).'</td></tr>';
    $cnt4++;
}
if ($cnt4===0) echo '<tr><td colspan="4"><em class="ok">No extra rows in SG5</em></td></tr>';
echo '</table>';

// -------------------------------------------------------
// 5. SYPGMO rows in Live NOT in SG5 (rows I may have deleted)
// -------------------------------------------------------
echo '<h2>5. SYPGMO rows in LIVE but missing from SG5 test (DELETED?)</h2>';
$r5 = db2_exec($conn,
    "SELECT L.SOPGID, L.SOMOPT, L.SOMDES, L.SORESV
     FROM SGHDSDATA.SYPGMO L
     WHERE NOT EXISTS (
         SELECT 1 FROM S5HDSDATA.SYPGMO T
         WHERE TRIM(T.SOPGID)=TRIM(L.SOPGID) AND T.SOMOPT=L.SOMOPT
     )
     ORDER BY L.SOPGID, L.SOMOPT
     FETCH FIRST 100 ROWS ONLY");
echo '<table><tr><th>SOPGID</th><th>SOMOPT</th><th>SOMDES</th><th>SORESV</th></tr>';
$cnt5 = 0;
while ($row = db2_fetch_assoc($r5)) {
    echo '<tr class="inlive"><td>'.htmlspecialchars(trim($row['SOPGID'])).'</td><td>'.htmlspecialchars(trim($row['SOMOPT'])).'</td><td>'.htmlspecialchars(trim($row['SOMDES'])).'</td><td>'.htmlspecialchars(trim($row['SORESV'])).'</td></tr>';
    $cnt5++;
}
if ($cnt5===0) echo '<tr><td colspan="4"><em class="ok">No missing rows — SYPGMO matches</em></td></tr>';
echo '</table>';

// 6. SYPGMO in SG5 not in Live
echo '<h2>6. SYPGMO rows in SG5 test but NOT in Live</h2>';
$r6 = db2_exec($conn,
    "SELECT T.SOPGID, T.SOMOPT, T.SOMDES
     FROM S5HDSDATA.SYPGMO T
     WHERE NOT EXISTS (
         SELECT 1 FROM SGHDSDATA.SYPGMO L
         WHERE TRIM(L.SOPGID)=TRIM(T.SOPGID) AND L.SOMOPT=T.SOMOPT
     )
     ORDER BY T.SOPGID, T.SOMOPT
     FETCH FIRST 50 ROWS ONLY");
echo '<table><tr><th>SOPGID</th><th>SOMOPT</th><th>SOMDES</th></tr>';
$cnt6 = 0;
while ($row = db2_fetch_assoc($r6)) {
    echo '<tr class="intest"><td>'.htmlspecialchars(trim($row['SOPGID'])).'</td><td>'.htmlspecialchars(trim($row['SOMOPT'])).'</td><td>'.htmlspecialchars(trim($row['SOMDES'])).'</td></tr>';
    $cnt6++;
}
if ($cnt6===0) echo '<tr><td colspan="3"><em class="ok">No extra rows in SG5</em></td></tr>';
echo '</table>';

// -------------------------------------------------------
// 7. SYPORR — rows in Live for CUSTSRVC not in SG5
// -------------------------------------------------------
echo '<h2>7. SYPORR — rows in LIVE for CUSTSRVC not in SG5 test</h2>';
$r7 = db2_exec($conn,
    "SELECT L.PRROLE, L.PRPORT, L.PRPAGE, L.PRSEQ, L.PRID
     FROM SGHDSDATA.SYPORR L
     WHERE TRIM(L.PRROLE)='CUSTSRVC'
     AND NOT EXISTS (
         SELECT 1 FROM S5HDSDATA.SYPORR T
         WHERE TRIM(T.PRROLE)=TRIM(L.PRROLE) AND TRIM(T.PRPORT)=TRIM(L.PRPORT)
         AND TRIM(T.PRPAGE)=TRIM(L.PRPAGE) AND T.PRSEQ=L.PRSEQ
     )
     ORDER BY L.PRPORT, L.PRSEQ
     FETCH FIRST 100 ROWS ONLY");
echo '<table><tr><th>PRROLE</th><th>PRPORT</th><th>PRPAGE</th><th>PRSEQ</th><th>PRID</th></tr>';
$cnt7 = 0;
while ($row = db2_fetch_assoc($r7)) {
    echo '<tr class="inlive"><td>'.htmlspecialchars(trim($row['PRROLE'])).'</td><td>'.htmlspecialchars(trim($row['PRPORT'])).'</td><td>'.htmlspecialchars(trim($row['PRPAGE'])).'</td><td>'.htmlspecialchars(trim($row['PRSEQ'])).'</td><td>'.htmlspecialchars(trim($row['PRID'])).'</td></tr>';
    $cnt7++;
}
if ($cnt7===0) echo '<tr><td colspan="5"><em class="ok">SYPORR/CUSTSRVC matches live</em></td></tr>';
echo '</table>';

// -------------------------------------------------------
// 8. SYPORT — HDLIST specifically in live
// -------------------------------------------------------
echo '<h2>8. SYPORT — HDLIST rows in LIVE (S5 has none)</h2>';
$r8 = db2_exec($conn, "SELECT FPPORT,FPPAGE,FPSEQ,FPID,FPTITL,FPRESV FROM SGHDSDATA.SYPORT WHERE TRIM(FPPORT)='HDLIST' ORDER BY FPSEQ FETCH FIRST 20 ROWS ONLY");
$nc8 = db2_num_fields($r8); $c8=array();
for($i=0;$i<$nc8;$i++) $c8[]=db2_field_name($r8,$i);
echo '<table><tr>'; foreach($c8 as $c) echo '<th>'.$c.'</th>'; echo '</tr>';
$cnt8=0;
while($row=db2_fetch_assoc($r8)){
    echo '<tr class="inlive">'; foreach($c8 as $c) echo '<td>'.htmlspecialchars(trim((string)$row[$c])).'</td>'; echo '</tr>';
    $cnt8++;
}
if($cnt8===0) echo '<tr><td colspan="6"><em>No HDLIST rows in Live either</em></td></tr>';
echo '</table>';

// -------------------------------------------------------
// 9. SYURLM — value differences for shared FUIDs (spot check key portals)
// -------------------------------------------------------
echo '<h2>9. SYURLM — value differences for HDLIST/PORTAL and CONFIG entries</h2>';
$checkFuids = array('HDLIST/PORTAL','CONFIGURATION/REPORT','CONFIGURATIONHEADER/REPORT');
foreach ($checkFuids as $fuid) {
    $rL = db2_exec($conn, "SELECT FUID,FUURL,FURESV,FUTSPT FROM SGHDSDATA.SYURLM WHERE TRIM(FUID)='".$fuid."'");
    $rT = db2_exec($conn, "SELECT FUID,FUURL,FURESV,FUTSPT FROM S5HDSDATA.SYURLM WHERE TRIM(FUID)='".$fuid."'");
    $live = db2_fetch_assoc($rL);
    $test = db2_fetch_assoc($rT);
    echo '<b>'.htmlspecialchars($fuid).'</b><br>';
    if (!$live) { echo 'LIVE: (not found)<br>'; } else { echo 'LIVE: FUURL='.htmlspecialchars(trim($live['FUURL'])).' FURESV='.htmlspecialchars(trim($live['FURESV'])).'<br>'; }
    if (!$test) { echo 'SG5:  (not found)<br>'; } else { echo 'SG5:  FUURL='.htmlspecialchars(trim($test['FUURL'])).' FURESV='.htmlspecialchars(trim($test['FURESV'])).'<br>'; }
    echo '<br>';
}

// -------------------------------------------------------
// 10. SYROLD — rows in Live for CUSTSRVC not in SG5
// -------------------------------------------------------
echo '<h2>10. SYROLD — rows in LIVE for CUSTSRVC not in SG5 test</h2>';
$r10 = db2_exec($conn,
    "SELECT L.RDROLE, L.RDPORT, L.RDSEQN
     FROM SGHDSDATA.SYROLD L
     WHERE TRIM(L.RDROLE)='CUSTSRVC'
     AND NOT EXISTS (
         SELECT 1 FROM S5HDSDATA.SYROLD T
         WHERE TRIM(T.RDROLE)=TRIM(L.RDROLE) AND TRIM(T.RDPORT)=TRIM(L.RDPORT)
     )
     ORDER BY L.RDSEQN");
echo '<table><tr><th>RDROLE</th><th>RDPORT</th><th>RDSEQN</th></tr>';
$cnt10=0;
while($row=db2_fetch_assoc($r10)){
    echo '<tr class="inlive"><td>'.htmlspecialchars(trim($row['RDROLE'])).'</td><td>'.htmlspecialchars(trim($row['RDPORT'])).'</td><td>'.htmlspecialchars(trim($row['RDSEQN'])).'</td></tr>';
    $cnt10++;
}
if($cnt10===0) echo '<tr><td colspan="3"><em class="ok">SYROLD/CUSTSRVC matches live</em></td></tr>';
echo '</table>';

// 11. SYROLD rows in SG5 not in Live (for CUSTSRVC)
echo '<h2>11. SYROLD — SG5 test rows for CUSTSRVC not in Live (I inserted these)</h2>';
$r11 = db2_exec($conn,
    "SELECT T.RDROLE, T.RDPORT, T.RDSEQN
     FROM S5HDSDATA.SYROLD T
     WHERE TRIM(T.RDROLE)='CUSTSRVC'
     AND NOT EXISTS (
         SELECT 1 FROM SGHDSDATA.SYROLD L
         WHERE TRIM(L.RDROLE)=TRIM(T.RDROLE) AND TRIM(L.RDPORT)=TRIM(T.RDPORT)
     )
     ORDER BY T.RDSEQN");
echo '<table><tr><th>RDROLE</th><th>RDPORT</th><th>RDSEQN</th></tr>';
$cnt11=0;
while($row=db2_fetch_assoc($r11)){
    echo '<tr class="intest"><td>'.htmlspecialchars(trim($row['RDROLE'])).'</td><td>'.htmlspecialchars(trim($row['RDPORT'])).'</td><td>'.htmlspecialchars(trim($row['RDSEQN'])).'</td></tr>';
    $cnt11++;
}
if($cnt11===0) echo '<tr><td colspan="3"><em class="ok">No extra SYROLD rows in SG5 for CUSTSRVC</em></td></tr>';
echo '</table>';

echo '</body></html>';
?>
