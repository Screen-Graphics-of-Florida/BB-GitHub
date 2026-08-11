<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/New_York');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$page_title = 'Kits Structure Report';

// ── Helpers ──────────────────────────────────────────────────────────────────

function ksr_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function ksr_q($s) {
    return str_replace("'", "''", (string)$s);
}

function ksr_qty($v) {
    return number_format((float)$v, 2);
}

// User types shop-floor wildcards (94-*); DB2 LIKE wants 94-%.
function ksr_toLike($v) {
    $s = trim((string)$v);
    if ($s === '') return '%';
    return str_replace(array('*', '?'), array('%', '_'), $s);
}

function ksr_val($conn, $sql) {
    $s = @db2_exec($conn, $sql);
    if (!$s) return null;
    $r = db2_fetch_row($s);
    $v = $r ? db2_result($s, 0) : null;
    db2_free_stmt($s);
    return $v;
}

function ksr_rows($conn, $sql) {
    $s = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$s) return false;
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    db2_free_stmt($s);
    return $out;
}

// ── Column discovery ─────────────────────────────────────────────────────────
//
//  HDIMST / HDIWHS / HDIPLT field names are taken from live reports that already
//  run against these files (MORequirements.php, ItemsNotCostRollReport.php):
//    HDIMST  IMITEM, IMIMDS, IMPCLS
//    HDIWHS  IWITEM, IWWHS,  IWOHQT, IWQSYT, IWQIYT
//    HDIPLT  IPITEM, IPPLT,  IPCMTO
//
//  HDMPSM and HDPCLS have no precedent anywhere in the custom tree, and the
//  "issued YTD" / "mfg YTD" field names are unconfirmed, so those are resolved
//  from QSYS2 at run time and then *proven with a data probe* rather than
//  assumed. If resolution fails the page says so and dumps the real columns.
//  DiagKitsStructure.php prints the same information in full.

function ksr_cols($conn, $file) {
    $out = array();
    $s = @db2_exec($conn,
        "SELECT COLUMN_NAME, DATA_TYPE, LENGTH, NUMERIC_SCALE
           FROM QSYS2.SYSCOLUMNS
          WHERE TABLE_SCHEMA = 'SGHDSDATA' AND TABLE_NAME = '$file'
          ORDER BY ORDINAL_POSITION",
        array('cursor' => DB2_SCROLLABLE));
    if (!$s) return $out;
    while ($r = db2_fetch_assoc($s)) {
        $out[rtrim((string)$r['COLUMN_NAME'])] = array(
            'type'  => rtrim((string)$r['DATA_TYPE']),
            'len'   => (int)$r['LENGTH'],
            'scale' => (int)$r['NUMERIC_SCALE'],
        );
    }
    db2_free_stmt($s);
    return $out;
}

function ksr_keyCols($conn, $file) {
    $out = array();
    $s = @db2_exec($conn,
        "SELECT k.COLUMN_NAME
           FROM QSYS2.SYSKEYCST k
           JOIN QSYS2.SYSCST c
             ON c.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
            AND c.CONSTRAINT_NAME   = k.CONSTRAINT_NAME
          WHERE c.TABLE_SCHEMA = 'SGHDSDATA' AND c.TABLE_NAME = '$file'
            AND c.CONSTRAINT_TYPE = 'PRIMARY KEY'
          ORDER BY k.ORDINAL_POSITION",
        array('cursor' => DB2_SCROLLABLE));
    if (!$s) return $out;
    while ($r = db2_fetch_assoc($s)) $out[] = rtrim((string)$r['COLUMN_NAME']);
    db2_free_stmt($s);
    return $out;
}

function ksr_pick($cols, $cands) {
    foreach ($cands as $c) if (isset($cols[$c])) return $c;
    return null;
}

function ksr_isChar($def) {
    return in_array($def['type'], array('CHAR', 'VARCHAR', 'GRAPHIC', 'VARG'), true);
}

function ksr_isNum($def) {
    return in_array($def['type'], array('DECIMAL', 'NUMERIC', 'INTEGER', 'SMALLINT',
                                        'BIGINT', 'DOUBLE', 'REAL'), true);
}

// Rank columns by how strongly the name suggests a role, keeping file order as
// the tiebreak. Names are only a hint here - the winner still has to survive a
// probe query against real rows.
function ksr_rank($cols, $needles, $filter, $hitsOnly = false) {
    $hit = array(); $miss = array();
    foreach ($cols as $name => $def) {
        if (!$filter($def)) continue;
        $isHit = false;
        foreach ($needles as $n) {
            if (strpos($name, $n) !== false) { $isHit = true; break; }
        }
        if ($isHit) $hit[] = $name; else $miss[] = $name;
    }
    return $hitsOnly ? $hit : array_merge($hit, $miss);
}

$conn = $i5Connect->getConnection();

$resolveErr = '';
$colDump    = array();

$imstCols = ksr_cols($conn, 'HDIMST');
$whsCols  = ksr_cols($conn, 'HDIWHS');
$pltCols  = ksr_cols($conn, 'HDIPLT');
$mpsmCols = ksr_cols($conn, 'HDMPSM');
$pclsCols = ksr_cols($conn, 'HDPCLS');

$itemLen = isset($imstCols['IMITEM']) ? max(15, (int)$imstCols['IMITEM']['len']) : 30;

// Qty issued YTD: the spec says IWQITY, but MORequirements.php uses IWQIYT.
// Take whichever actually exists, preferring the spec's name.
$colIssYtd = ksr_pick($whsCols, array('IWQITY', 'IWQIYT'));
$colMfgYtd = ksr_pick($pltCols, array('IPQMFG', 'IPQMFY', 'IPQMYT'));

foreach (array('HDIMST' => array($imstCols, array('IMITEM', 'IMIMDS', 'IMPCLS')),
               'HDIWHS' => array($whsCols,  array('IWITEM', 'IWOHQT', 'IWQSYT')),
               'HDIPLT' => array($pltCols,  array('IPITEM', 'IPCMTO'))) as $f => $spec) {
    foreach ($spec[1] as $need) {
        if (!isset($spec[0][$need])) {
            $resolveErr .= "$f is missing expected column $need. ";
            $colDump[$f] = array_keys($spec[0]);
        }
    }
}
if ($colIssYtd === null) {
    $resolveErr .= 'Could not find a "qty issued YTD" column on HDIWHS (looked for IWQITY, IWQIYT). ';
    $colDump['HDIWHS'] = array_keys($whsCols);
}
if ($colMfgYtd === null) {
    $resolveErr .= 'Could not find a "qty mfg YTD" column on HDIPLT (looked for IPQMFG, IPQMFY, IPQMYT). ';
    $colDump['HDIPLT'] = array_keys($pltCols);
}

// --- HDMPSM: parent / child / qty-per --------------------------------------
$colPar = null; $colChd = null; $colQty = null;

if (!empty($mpsmCols)) {
    $itemish = array();
    foreach ($mpsmCols as $n => $d) {
        if (ksr_isChar($d) && abs($d['len'] - $itemLen) <= 5) $itemish[] = $n;
    }

    // Preferred pair: first two item-shaped columns of the primary key, which is
    // how a product-structure file is keyed (parent, then component).
    $pairs = array();
    $pk = ksr_keyCols($conn, 'HDMPSM');
    $pkItemish = array();
    foreach ($pk as $k) if (in_array($k, $itemish, true)) $pkItemish[] = $k;
    if (count($pkItemish) >= 2) $pairs[] = array($pkItemish[0], $pkItemish[1]);

    // Then name-ranked pairings, both orderings.
    $parRank = ksr_rank($mpsmCols, array('PRNT', 'PARN', 'PARENT', 'ASSY', 'ASM'), 'ksr_isChar');
    $chdRank = ksr_rank($mpsmCols, array('CHIL', 'CHLD', 'COMP', 'CPN', 'CMP'),   'ksr_isChar');
    foreach ($parRank as $p) {
        if (!in_array($p, $itemish, true)) continue;
        foreach ($chdRank as $c) {
            if ($c === $p || !in_array($c, $itemish, true)) continue;
            $pairs[] = array($p, $c);
        }
    }

    // Probe: a real (parent, child) pair joins to HDIMST on both sides.
    // Capped so a wide file can't turn page load into dozens of probe queries.
    $pairs = array_slice($pairs, 0, 12);
    foreach ($pairs as $pair) {
        list($p, $c) = $pair;
        $n = ksr_val($conn,
            "SELECT COUNT(*) FROM (
                SELECT 1 FROM SGHDSDATA.HDMPSM b
                  JOIN SGHDSDATA.HDIMST pi ON RTRIM(pi.IMITEM) = RTRIM(b.$p)
                  JOIN SGHDSDATA.HDIMST ci ON RTRIM(ci.IMITEM) = RTRIM(b.$c)
                 FETCH FIRST 25 ROWS ONLY) t");
        if ($n !== null && (int)$n > 0) { $colPar = $p; $colChd = $c; break; }
    }

    if ($colPar !== null) {
        // Name-matched only. Falling through to "first numeric column" would happily
        // pick a sequence or effectivity field and report it as a quantity.
        $qtyRank = ksr_rank($mpsmCols, array('QPER', 'QPA', 'QTY', 'QUAN'), 'ksr_isNum', true);
        if (!empty($qtyRank)) $colQty = $qtyRank[0];
    }
}

if ($colPar === null || $colChd === null) {
    $resolveErr .= 'Could not identify the parent/child item columns on HDMPSM. ';
    $colDump['HDMPSM'] = array_keys($mpsmCols);
}

// --- HDPCLS: product class code / description ------------------------------
$colPcls = null; $colPclsDesc = null;

if (!empty($pclsCols)) {
    $codeRank = ksr_rank($pclsCols, array('PCLS', 'CLAS', 'CLASS', 'CODE', 'PC'), 'ksr_isChar');
    $best = -1;
    foreach ($codeRank as $cand) {
        if ($pclsCols[$cand]['len'] > 15) continue;   // codes are short
        $n = ksr_val($conn,
            "SELECT COUNT(*) FROM (
                SELECT 1 FROM SGHDSDATA.HDPCLS x
                 WHERE RTRIM(x.$cand) <> ''
                   AND EXISTS (SELECT 1 FROM SGHDSDATA.HDIMST i
                                WHERE RTRIM(i.IMPCLS) = RTRIM(x.$cand))
                 FETCH FIRST 50 ROWS ONLY) t");
        $n = ($n === null) ? 0 : (int)$n;
        if ($n > $best) { $best = $n; $colPcls = $cand; }
        if ($n >= 50) break;
    }
    if ($best <= 0) $colPcls = null;   // nothing in HDPCLS matches IMPCLS - fall back

    if ($colPcls !== null) {
        foreach ($pclsCols as $n => $d) {
            if ($n === $colPcls) continue;
            if (ksr_isChar($d) && $d['len'] >= 10) { $colPclsDesc = $n; break; }
        }
    }
}

// ── Filters ──────────────────────────────────────────────────────────────────

$fItem    = isset($_GET['item']) ? trim((string)$_GET['item']) : '94-*';
$fClass   = isset($_GET['class']) ? strtoupper(trim((string)$_GET['class'])) : null;
$fLevels  = (isset($_GET['levels']) && $_GET['levels'] === 'all') ? 'all' : '1';
$maxLevel = ($fLevels === 'all') ? 10 : 1;

$itemLike = ksr_q(ksr_toLike($fItem));

// Class list: HDPCLS when it resolved and has data, HDIMST.IMPCLS otherwise.
$classOptions = array();
$classSource  = '';

if ($colPcls !== null) {
    $r = ksr_rows($conn,
        "SELECT DISTINCT RTRIM($colPcls) AS CODE"
      . ($colPclsDesc !== null ? ", RTRIM($colPclsDesc) AS DESCR" : ", '' AS DESCR")
      . " FROM SGHDSDATA.HDPCLS WHERE RTRIM($colPcls) <> '' ORDER BY 1");
    if ($r !== false) {
        foreach ($r as $row) {
            $code = strtoupper(rtrim((string)$row['CODE']));
            if ($code !== '') $classOptions[$code] = rtrim((string)$row['DESCR']);
        }
        if (!empty($classOptions)) $classSource = "SGHDSDATA.HDPCLS ($colPcls)";
    }
}

if (empty($classOptions)) {
    $r = ksr_rows($conn,
        "SELECT DISTINCT RTRIM(IMPCLS) AS CODE FROM SGHDSDATA.HDIMST
          WHERE RTRIM(IMPCLS) <> '' ORDER BY 1");
    if ($r !== false) {
        foreach ($r as $row) {
            $code = strtoupper(rtrim((string)$row['CODE']));
            if ($code !== '') $classOptions[$code] = '';
        }
        if (!empty($classOptions)) $classSource = 'SGHDSDATA.HDIMST.IMPCLS (fallback)';
    }
}

// Default to KITS when it exists. '' is an explicit "All Classes" choice, so it
// is only overridden on the very first request (class not supplied at all).
if ($fClass === null) {
    $fClass = isset($classOptions['KITS']) ? 'KITS' : '';
} elseif ($fClass !== '' && !isset($classOptions[$fClass])) {
    $fClass = '';
}

// ── Query ────────────────────────────────────────────────────────────────────
//
//  HDIWHS is keyed item x warehouse and HDIPLT item x plant, so joining them
//  straight onto the BOM would multiply every component row by its warehouse
//  and plant counts. Both are pre-aggregated to one row per item instead, and
//  the figures shown are therefore all-warehouse / all-plant totals.
//
//  The BOM leg is a recursive CTE: LVL < 1 makes the recursive branch a no-op
//  for the single-level view, so both modes share one statement. The level cap
//  also stops a cyclic structure from running away.

$rows   = array();
$sqlErr = '';
$sql    = '';

if ($resolveErr === '') {

    $qtyExpr = ($colQty !== null) ? "CAST(b.$colQty AS DECIMAL(15,4))" : "CAST(0 AS DECIMAL(15,4))";
    $clsWhere = ($fClass !== '') ? "        AND RTRIM(p.IMPCLS) = '" . ksr_q($fClass) . "'\n" : '';

    $sql = "
WITH BOM (LVL, TOP_ITEM, PARENT_ITEM, CHILD_ITEM, QTY_PER, EXT_QTY, BOM_PATH) AS (
    SELECT 1,
           CAST(RTRIM(p.IMITEM) AS VARCHAR($itemLen)),
           CAST(RTRIM(p.IMITEM) AS VARCHAR($itemLen)),
           CAST(RTRIM(b.$colChd) AS VARCHAR($itemLen)),
           $qtyExpr,
           $qtyExpr,
           CAST(RTRIM(b.$colChd) AS VARCHAR(500))
      FROM SGHDSDATA.HDIMST p
      JOIN SGHDSDATA.HDMPSM b
        ON RTRIM(b.$colPar) = RTRIM(p.IMITEM)
     WHERE RTRIM(p.IMITEM) LIKE '$itemLike'
$clsWhere
    UNION ALL

    SELECT r.LVL + 1,
           r.TOP_ITEM,
           r.CHILD_ITEM,
           CAST(RTRIM(b.$colChd) AS VARCHAR($itemLen)),
           $qtyExpr,
           CAST(r.EXT_QTY * $qtyExpr AS DECIMAL(15,4)),
           CAST(r.BOM_PATH || ' > ' || RTRIM(b.$colChd) AS VARCHAR(500))
      FROM BOM r
      JOIN SGHDSDATA.HDMPSM b
        ON RTRIM(b.$colPar) = r.CHILD_ITEM
     WHERE r.LVL < $maxLevel
),
WH (ITM, OHQTY, SOLDYTD, ISSYTD) AS (
    SELECT RTRIM(IWITEM),
           SUM(CAST(IWOHQT AS DECIMAL(15,4))),
           SUM(CAST(IWQSYT AS DECIMAL(15,4))),
           SUM(CAST($colIssYtd AS DECIMAL(15,4)))
      FROM SGHDSDATA.HDIWHS
     GROUP BY RTRIM(IWITEM)
),
PL (ITM, MFGYTD, CMTMO) AS (
    SELECT RTRIM(IPITEM),
           SUM(CAST($colMfgYtd AS DECIMAL(15,4))),
           SUM(CAST(IPCMTO AS DECIMAL(15,4)))
      FROM SGHDSDATA.HDIPLT
     GROUP BY RTRIM(IPITEM)
)
SELECT r.LVL                          AS LVL,
       r.TOP_ITEM                     AS TOP_ITEM,
       RTRIM(tp.IMIMDS)               AS TOP_DESC,
       RTRIM(tp.IMPCLS)               AS TOP_CLASS,
       r.PARENT_ITEM                  AS PARENT_ITEM,
       r.CHILD_ITEM                   AS CHILD_ITEM,
       RTRIM(ci.IMIMDS)               AS CHILD_DESC,
       RTRIM(ci.IMPCLS)               AS CHILD_CLASS,
       r.QTY_PER                      AS QTY_PER,
       r.EXT_QTY                      AS EXT_QTY,
       r.BOM_PATH                     AS BOM_PATH,
       COALESCE(WH.OHQTY,   0)        AS OHQTY,
       COALESCE(WH.SOLDYTD, 0)        AS SOLDYTD,
       COALESCE(WH.ISSYTD,  0)        AS ISSYTD,
       COALESCE(PL.MFGYTD,  0)        AS MFGYTD,
       COALESCE(PL.CMTMO,   0)        AS CMTMO
  FROM BOM r
  LEFT JOIN SGHDSDATA.HDIMST tp ON RTRIM(tp.IMITEM) = r.TOP_ITEM
  LEFT JOIN SGHDSDATA.HDIMST ci ON RTRIM(ci.IMITEM) = r.CHILD_ITEM
  LEFT JOIN WH ON WH.ITM = r.CHILD_ITEM
  LEFT JOIN PL ON PL.ITM = r.CHILD_ITEM
 ORDER BY r.TOP_ITEM, r.LVL, r.BOM_PATH, r.CHILD_ITEM
";

    $res = ksr_rows($conn, $sql);
    if ($res === false) {
        $sqlErr = db2_stmt_errormsg();
    } else {
        $rows = $res;
    }
}

$rowCount = count($rows);
$kitCount = 0;
$seenTop  = array();
foreach ($rows as $r) {
    $t = rtrim((string)$r['TOP_ITEM']);
    if (!isset($seenTop[$t])) { $seenTop[$t] = true; $kitCount++; }
}

// ── CSV / Excel export (must run before any HTML) ────────────────────────────

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="KitsStructure_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array(
        'Level', 'Kit Item', 'Kit Description', 'Kit Class', 'Parent Item',
        'Child Item', 'Child Description', 'Child Class', 'Qty Per', 'Ext Qty Per Kit',
        'BOM Path', 'Qty On Hand', 'Qty Sold YTD', 'Qty Issued YTD',
        'Qty Mfg YTD', 'Qty Committed To MO'
    ));
    foreach ($rows as $r) {
        fputcsv($out, array(
            (int)$r['LVL'],
            rtrim((string)$r['TOP_ITEM']),
            rtrim((string)$r['TOP_DESC']),
            rtrim((string)$r['TOP_CLASS']),
            rtrim((string)$r['PARENT_ITEM']),
            rtrim((string)$r['CHILD_ITEM']),
            rtrim((string)$r['CHILD_DESC']),
            rtrim((string)$r['CHILD_CLASS']),
            number_format((float)$r['QTY_PER'], 4, '.', ''),
            number_format((float)$r['EXT_QTY'], 4, '.', ''),
            rtrim((string)$r['BOM_PATH']),
            number_format((float)$r['OHQTY'],   4, '.', ''),
            number_format((float)$r['SOLDYTD'], 4, '.', ''),
            number_format((float)$r['ISSYTD'],  4, '.', ''),
            number_format((float)$r['MFGYTD'],  4, '.', ''),
            number_format((float)$r['CMTMO'],   4, '.', ''),
        ));
    }
    fclose($out);
    exit;
}

// ── HTML ─────────────────────────────────────────────────────────────────────

$preserveParams = $_GET;
unset($preserveParams['item'], $preserveParams['class'],
      $preserveParams['levels'], $preserveParams['export']);

$exportParams = $preserveParams;
$exportParams['item']   = $fItem;
$exportParams['class']  = $fClass;
$exportParams['levels'] = $fLevels;
$exportParams['export'] = 'csv';
$exportURL = '?' . http_build_query($exportParams);

print "\n<html><head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n</head>";
require_once 'Banner.php';
require_once dirname(__FILE__) . '/../SgReportNav.php';

?>
<table <?php echo $baseTable; ?>>
<tr valign="top">
<td class="content">

<style>
table[summary="banner"] { display:none !important; }
body { box-sizing:border-box !important; }
body > table { width:100% !important; max-width:none !important; table-layout:auto !important; }
td.content { width:calc(100vw - 155px) !important; max-width:none !important; box-sizing:border-box !important; }
#ksr-grid { width:100% !important; min-width:100% !important; border-collapse:collapse; }
#ksr-grid thead th { background-color:#374151 !important; color:#fff !important;
                     font-weight:bold !important; white-space:nowrap; }
#ksr-grid tbody tr:nth-child(odd)  { background:#F7F7F7; }
#ksr-grid tbody tr:nth-child(even) { background:#FFFFFF; }
#ksr-grid tbody tr:hover           { background:#EFF6FF !important; }
#ksr-grid tbody td { color:#111827 !important; font-size:12px; }
#ksr-grid tbody tr.ksr-newkit td { border-top:2px solid #374151; }
#ksr-grid td.ksr-zero { color:#CC1F20 !important; font-weight:bold; }
.ksr-lvl { display:inline-block; min-width:16px; text-align:center; font-weight:bold;
           background:#374151; color:#fff; border-radius:3px; padding:0 4px; }
</style>

<!-- Full-width title bar: escapes the 155px nav offset to span 100vw -->
<div style="position:relative; left:-155px; width:calc(100% + 155px); box-sizing:border-box;
            display:flex; align-items:center;
            padding:10px 14px 10px calc(155px + 14px);
            background:linear-gradient(to right,
                #111827 0%, #1F2937 25%, #374151 55%, #4B5563 78%, #6B7280 100%);
            border-bottom:3px solid rgba(0,0,0,0.15);
            gap:10px; margin-bottom:6px;">
  <h1 style="font-size:22px;color:#fff !important;margin:0;flex:1;font-weight:bold !important;
             text-shadow:0 1px 3px rgba(0,0,0,0.4);">Kits Structure Report</h1>
  <a href="<?php echo ksr_h($_sgnHome . '/Welcome.php?baseVar=' . rawurlencode($_sgnBv) . '&eID=' . rawurlencode($_sgnEid) . '&portal=9999999999'); ?>"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#06B6D4;
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid #0891B2;white-space:nowrap;display:inline-block;">&#8592; Back to EIP</a>
  <a href="https://screen-graphics.com/"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#CC1F20;
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid #8b1010;white-space:nowrap;display:inline-block;">Logout</a>
</div>

<?php if ($resolveErr !== ''): ?>
<div style="padding:10px 14px;background:#FFF4F4;border:1px solid #E5A0A0;margin:0 0 8px;">
  <p style="color:#CC1F20;font-weight:bold;margin:0 0 6px;">
    Field names could not be resolved &mdash; no query was run.</p>
  <p style="margin:0 0 6px;"><?php echo ksr_h($resolveErr); ?></p>
  <?php foreach ($colDump as $f => $cl): ?>
  <p style="margin:0 0 4px;font-size:12px;">
    <b>Actual SGHDSDATA.<?php echo ksr_h($f); ?> columns:</b>
    <span style="font-family:monospace;"><?php echo ksr_h(implode(', ', $cl)); ?></span></p>
  <?php endforeach; ?>
  <p style="margin:6px 0 0;font-size:12px;">Run
    <b>DiagKitsStructure.php</b> in this folder for the full layout, then hard-code
    the confirmed names in the column-discovery block at the top of this file.</p>
</div>
<?php elseif ($sqlErr): ?>
<div style="padding:10px 14px;background:#FFF4F4;border:1px solid #E5A0A0;margin:0 0 8px;">
  <p style="color:#CC1F20;font-weight:bold;margin:0 0 6px;">SQL Error: <?php echo ksr_h($sqlErr); ?></p>
  <pre style="font-size:11px;white-space:pre-wrap;"><?php echo ksr_h($sql); ?></pre>
</div>
<?php endif; ?>

<div style="display:flex;align-items:stretch;border-bottom:2px solid #D1D5DB;">
  <div style="flex:1;display:flex;align-items:center;gap:10px;padding:6px 10px;
              background:#F7F7F7;font-size:12px;flex-wrap:wrap;">
    <form method="get" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <?php foreach ($preserveParams as $pk => $pv): ?>
      <input type="hidden" name="<?php echo ksr_h($pk); ?>" value="<?php echo ksr_h($pv); ?>">
      <?php endforeach; ?>
      <label style="white-space:nowrap;font-weight:600;">Item:
        <input type="text" name="item" value="<?php echo ksr_h($fItem); ?>" size="14"
               placeholder="94-*" title="Wildcards: * = any, ? = one character"
               style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;font-size:12px;margin-left:4px;">
      </label>
      <label style="white-space:nowrap;font-weight:600;">Product Class:
        <select name="class" style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;font-size:12px;margin-left:4px;">
          <option value="" <?php echo ($fClass === '') ? 'selected' : ''; ?>>All Classes</option>
          <?php foreach ($classOptions as $code => $descr): ?>
          <option value="<?php echo ksr_h($code); ?>" <?php echo ($fClass === $code) ? 'selected' : ''; ?>>
            <?php echo ksr_h($descr !== '' ? "$code - $descr" : $code); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label style="white-space:nowrap;font-weight:600;">Levels:
        <select name="levels" style="padding:2px 4px;border:1px solid #bbb;border-radius:3px;font-size:12px;margin-left:4px;">
          <option value="1"   <?php echo ($fLevels === '1')   ? 'selected' : ''; ?>>Single level</option>
          <option value="all" <?php echo ($fLevels === 'all') ? 'selected' : ''; ?>>All levels (max 10)</option>
        </select>
      </label>
      <button type="submit"
              style="padding:2px 12px;font-size:12px;cursor:pointer;border:1px solid #1d4ed8;
                     border-radius:3px;background:#2563EB;color:#fff;font-weight:bold;">View</button>
    </form>
    <b style="margin-left:auto;white-space:nowrap;">
      <?php echo number_format($rowCount); ?>&nbsp;component line<?php echo $rowCount === 1 ? '' : 's'; ?>
      across <?php echo number_format($kitCount); ?>&nbsp;kit<?php echo $kitCount === 1 ? '' : 's'; ?>
    </b>
  </div>
  <div style="display:flex;flex-direction:column;align-items:stretch;justify-content:center;
              gap:4px;padding:6px 10px;background:#F7F7F7;border-left:2px solid #D1D5DB;">
    <button onclick="location.reload();"
            style="font-size:12px;padding:3px 14px;cursor:pointer;border:1px solid #4a0f6e;
                   border-radius:3px;background:#7B1FA2;color:#fff;font-weight:bold;
                   white-space:nowrap;text-align:center;">&#x21BB; Refresh</button>
    <a href="<?php echo ksr_h($exportURL); ?>"
       style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;
              font-weight:bold;text-decoration:none;white-space:nowrap;
              text-align:center;display:block;">&#8595; Export to Excel</a>
  </div>
</div>

<div style="padding:4px 10px;font-size:11px;color:#555;background:#FAFAFA;border-bottom:1px solid #E5E7EB;">
  Item pattern <b><?php echo ksr_h($fItem); ?></b> &rarr; DB2 <code>LIKE '<?php echo ksr_h(ksr_toLike($fItem)); ?>'</code>.
  Product classes from <b><?php echo ksr_h($classSource !== '' ? $classSource : 'n/a'); ?></b>.
  Component quantities are totals across <b>all warehouses</b> (HDIWHS) and <b>all plants</b> (HDIPLT).
  <?php if ($colPar !== null): ?>
  BOM columns in use: <code>HDMPSM.<?php echo ksr_h($colPar); ?></code> &rarr;
  <code><?php echo ksr_h($colChd); ?></code>, qty <code><?php echo ksr_h($colQty !== null ? $colQty : 'n/a'); ?></code>;
  issued YTD <code><?php echo ksr_h($colIssYtd); ?></code>, mfg YTD <code><?php echo ksr_h($colMfgYtd); ?></code>.
  <?php endif; ?>
</div>

<div style="overflow-x:auto;">
<table id="ksr-grid" <?php echo $contentTable; ?>>
  <thead>
    <tr>
      <th class="colhdr">Lvl</th>
      <th class="colhdr">Kit Item</th>
      <th class="colhdr">Kit Description</th>
      <th class="colhdr">Class</th>
      <?php if ($fLevels === 'all'): ?>
      <th class="colhdr">Parent Item</th>
      <?php endif; ?>
      <th class="colhdr">Child Item</th>
      <th class="colhdr">Child Description</th>
      <th class="colhdr">Qty Per</th>
      <?php if ($fLevels === 'all'): ?>
      <th class="colhdr">Ext Qty/Kit</th>
      <?php endif; ?>
      <th class="colhdr">Qty On Hand</th>
      <th class="colhdr">Qty Sold YTD</th>
      <th class="colhdr">Qty Issued YTD</th>
      <th class="colhdr">Qty Mfg YTD</th>
      <th class="colhdr">Qty Cmtd To MO</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($rows) && $resolveErr === '' && !$sqlErr): ?>
    <tr><td colspan="<?php echo ($fLevels === 'all') ? 14 : 12; ?>" class="colcode" align="center" style="padding:20px;">
      No product structures found for item <?php echo ksr_h($fItem); ?>
      <?php echo $fClass !== '' ? ' in class ' . ksr_h($fClass) : ''; ?>.
    </td></tr>
<?php endif; ?>
<?php
$prevTop = null;
foreach ($rows as $r):
    $top    = rtrim((string)$r['TOP_ITEM']);
    $newKit = ($top !== $prevTop);
    $prevTop = $top;
    $oh = (float)$r['OHQTY'];
?>
    <tr class="<?php echo $newKit ? 'ksr-newkit' : ''; ?>">
      <td class="colcode"><span class="ksr-lvl"><?php echo (int)$r['LVL']; ?></span></td>
      <td class="colcode"><?php echo $newKit ? ksr_h($top) : '&nbsp;'; ?></td>
      <td class="colcode"><?php echo $newKit ? ksr_h(rtrim((string)$r['TOP_DESC'])) : '&nbsp;'; ?></td>
      <td class="colcode"><?php echo $newKit ? ksr_h(rtrim((string)$r['TOP_CLASS'])) : '&nbsp;'; ?></td>
      <?php if ($fLevels === 'all'): ?>
      <td class="colcode"><?php echo ksr_h(rtrim((string)$r['PARENT_ITEM'])); ?></td>
      <?php endif; ?>
      <td class="colcode"><b><?php echo ksr_h(rtrim((string)$r['CHILD_ITEM'])); ?></b></td>
      <td class="colcode"><?php echo ksr_h(rtrim((string)$r['CHILD_DESC'])); ?></td>
      <td class="colcode" align="right"><?php echo ksr_qty($r['QTY_PER']); ?></td>
      <?php if ($fLevels === 'all'): ?>
      <td class="colcode" align="right"><?php echo ksr_qty($r['EXT_QTY']); ?></td>
      <?php endif; ?>
      <td class="colcode<?php echo $oh <= 0 ? ' ksr-zero' : ''; ?>" align="right"><?php echo ksr_qty($oh); ?></td>
      <td class="colcode" align="right"><?php echo ksr_qty($r['SOLDYTD']); ?></td>
      <td class="colcode" align="right"><?php echo ksr_qty($r['ISSYTD']); ?></td>
      <td class="colcode" align="right"><?php echo ksr_qty($r['MFGYTD']); ?></td>
      <td class="colcode" align="right"><?php echo ksr_qty($r['CMTMO']); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>

</td>
</tr>
</table>

</body>
</html>
