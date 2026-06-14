<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>hdListInclude Compare</title>
<style>body{font-family:monospace;font-size:11px;padding:14px;}
h2{color:#00c;border-bottom:1px solid #00c;margin:14px 0 4px;}
pre{background:#f5f5f5;border:1px solid #ccc;padding:8px;font-size:10px;white-space:pre-wrap;word-break:break-all;}
.ok{color:green;font-weight:bold;}.err{color:red;font-weight:bold;}
.diff{background:#ffe0e0;}.same{background:#f5f5f5;}
table{border-collapse:collapse;width:100%;}
td{border:1px solid #bbb;padding:2px 4px;vertical-align:top;width:50%;}
th{background:#ddd;border:1px solid #bbb;padding:2px 4px;}
</style></head><body><h1>hdListInclude.php — SG5 vs Live comparison</h1>';

$sg5  = '/HarrisData/SG5/hdListInclude.php';
$live = '/HarrisData/EIP/hdListInclude.php';

// 1. File sizes and MD5
echo '<h2>1. File info</h2>';
foreach (array('SG5'=>$sg5,'Live'=>$live) as $label=>$path) {
    $ex = file_exists($path);
    echo $label.': ';
    if ($ex) {
        echo '<span class="ok">EXISTS</span> size='.filesize($path).' md5='.md5_file($path).'<br>';
    } else {
        echo '<span class="err">NOT FOUND</span> ('.$path.')<br>';
    }
}

$sg5Lines  = file_exists($sg5)  ? file($sg5)  : array();
$liveLines = file_exists($live) ? file($live) : array();

// 2. Are they identical?
echo '<h2>2. Identical?</h2>';
if (md5_file($sg5) === md5_file($live)) {
    echo '<span class="ok">Files are identical</span><br>';
} else {
    echo '<span class="err">Files DIFFER</span><br>';
}

// 3. Rtv_Default_Page function — show from both files
echo '<h2>3. Rtv_Default_Page function (from each file)</h2>';
foreach (array('SG5'=>$sg5Lines,'Live'=>$liveLines) as $label=>$lines) {
    echo '<b>'.$label.':</b><pre>';
    $inFunc = false; $depth = 0; $shown = 0;
    foreach ($lines as $n=>$line) {
        if (!$inFunc && preg_match('/function\s+Rtv_Default_Page/i', $line)) {
            $inFunc = true;
        }
        if ($inFunc) {
            echo htmlspecialchars(rtrim($line))."\n";
            $depth += substr_count($line,'{') - substr_count($line,'}');
            $shown++;
            if ($shown > 5 && $depth <= 0) break;
            if ($shown > 60) { echo '...(truncated)'; break; }
        }
    }
    if (!$inFunc) echo '(function not found)';
    echo '</pre>';
}

// 4. Lines around "Design Page" and "pagID" in both files
echo '<h2>4. Lines containing pagID, PDPGID, Design Page, Rtv_Default (context +/-2)</h2>';
foreach (array('SG5'=>$sg5Lines,'Live'=>$liveLines) as $label=>$lines) {
    echo '<b>'.$label.':</b><pre>';
    $shown = array();
    foreach ($lines as $n=>$line) {
        if (preg_match('/pagID|PDPGID|Design.Page|Rtv_Default/i', $line)) {
            for ($i=max(0,$n-2); $i<=min(count($lines)-1,$n+2); $i++) {
                if (!isset($shown[$i])) {
                    $shown[$i] = true;
                    $marker = ($i===$n) ? '>>>' : '   ';
                    echo $marker.'L'.($i+1).': '.htmlspecialchars(rtrim($lines[$i]))."\n";
                }
            }
            echo "\n";
        }
    }
    echo '</pre>';
}

// 5. Side-by-side diff of lines that differ
echo '<h2>5. Lines that differ between SG5 and Live</h2>';
$max = max(count($sg5Lines), count($liveLines));
$diffs = 0;
echo '<table><tr><th>Line</th><th>SG5</th><th>Live</th></tr>';
for ($i=0; $i<$max; $i++) {
    $a = isset($sg5Lines[$i])  ? rtrim($sg5Lines[$i])  : '(no line)';
    $b = isset($liveLines[$i]) ? rtrim($liveLines[$i]) : '(no line)';
    if ($a !== $b) {
        echo '<tr class="diff"><td>'.($i+1).'</td><td>'.htmlspecialchars($a).'</td><td>'.htmlspecialchars($b).'</td></tr>';
        $diffs++;
        if ($diffs >= 100) { echo '<tr><td colspan="3">(truncated at 100 diffs)</td></tr>'; break; }
    }
}
if ($diffs===0) echo '<tr><td colspan="3"><span class="ok">No differing lines</span></td></tr>';
echo '</table>';

// 6. Run actual Rtv_Default_Page logic for tblID=199 and show $pagID
echo '<h2>6. Live test: what does Rtv_Default_Page return for tblID=199?</h2>';
$tblID = 199;
// Get active role and user from session
$activeRole  = isset($_SESSION['hdRole'])    ? $_SESSION['hdRole']    : 'unknown';
$userProfile = isset($_SESSION['hdUser'])    ? $_SESSION['hdUser']    : 'unknown';
$altRole     = isset($_SESSION['userRole'])  ? $_SESSION['userRole']  : '';
$altUser     = isset($_SESSION['userID'])    ? $_SESSION['userID']    : '';
echo 'Session hdRole='.htmlspecialchars($activeRole).' hdUser='.htmlspecialchars($userProfile).'<br>';
echo 'Session userRole='.htmlspecialchars($altRole).' userID='.htmlspecialchars($altUser).'<br>';

// Try several possible role/user variable names
foreach (array(
    array($activeRole,$userProfile),
    array($altRole,$altUser),
    array('',''),
    array(' ',' ')
) as $pair) {
    list($role,$user) = $pair;
    $sql = "Select PDPGID From SG5STDPGM.SYDSGN Where PDTBID=$tblID";
    if ($role !== '' && $user !== '') {
        $sql .= " and (PDROLE='" . str_replace("'","''",$role) . "' or PDROLE=' ')";
        $sql .= " and (PDUSER='" . str_replace("'","''",$user) . "' or PDUSER=' ')";
    }
    $r = db2_exec($conn, $sql);
    $row = ($r) ? db2_fetch_assoc($r) : false;
    $pagID = ($row) ? trim($row['PDPGID']) : 'NULL (no row)';
    echo 'role='.htmlspecialchars($role).' user='.htmlspecialchars($user).' &rarr; PDPGID=<b>'.htmlspecialchars($pagID).'</b>';
    if (!$r) echo ' <span class="err">SQL error: '.htmlspecialchars(db2_stmt_errormsg()).'</span>';
    echo '<br>';
}

// 7. Final query test with PDPGID=0
echo '<h2>7. Final SYDSGN query with PDPGID=0</h2>';
$r = db2_exec($conn, "Select PDTBID,PDPGID,PDTYPE,PDDESC From SG5STDPGM.SYDSGN Where PDTBID=199 and PDPGID=0 and PDTYPE='L'");
if ($r) {
    $row = db2_fetch_assoc($r);
    if ($row) echo '<span class="ok">ROW FOUND</span> PDTBID='.trim($row['PDTBID']).' PDPGID='.trim($row['PDPGID']).' PDTYPE='.trim($row['PDTYPE']).' PDDESC='.trim($row['PDDESC']).'<br>';
    else echo '<span class="err">0 rows returned (PDPGID=0 row missing?)</span><br>';
} else {
    echo '<span class="err">Query failed: '.htmlspecialchars(db2_stmt_errormsg()).'</span><br>';
}

// Also try without schema prefix (library list)
$r2 = db2_exec($conn, "Select PDTBID,PDPGID,PDTYPE,PDDESC From SYDSGN Where PDTBID=199 and PDPGID=0 and PDTYPE='L'");
echo 'Without schema prefix: ';
if ($r2) {
    $row2 = db2_fetch_assoc($r2);
    echo ($row2 ? '<span class="ok">ROW FOUND</span> PDDESC='.htmlspecialchars(trim($row2['PDDESC'])) : '<span class="err">0 rows</span>').'<br>';
} else {
    echo '<span class="err">Failed: '.htmlspecialchars(db2_stmt_errormsg()).'</span><br>';
}

echo '</body></html>';
?>
