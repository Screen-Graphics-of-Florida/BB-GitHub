<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/New_York');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$page_title = 'Kits Structure Report';

// ── Field map ────────────────────────────────────────────────────────────────
//
//  Confirmed 2026-08-10 against the live file layouts (DiagKitsStructure.php):
//
//  HDIMST  23,411 rows   PK IMITEM
//      IMITEM  CHAR(15)      Item Number
//      IMIMDS  CHAR(30)      Item Description
//      IMPCLS  CHAR(4)       Product Class
//      IMKIT   CHAR(1)       Kit / Featured Item / Configurable ( ,K,F,C)
//
//  HDMPSM  117,443 rows  PK PSPPLT, PSPPN, PSSEQN, PSCPN, PSREFN
//      PSPPN   CHAR(15)      Parent Item Number
//      PSCPN   CHAR(15)      Component Item Number
//      PSQPER  DECIMAL(9,5)  Quantity Per
//      PSSEQN  NUMERIC(3,0)  Routing Sequence Number
//      PSSTC   CHAR(1)       Status Code
//      PSPPLT  NUMERIC(3,0)  Parent Plant Number
//
//  PSSTC is 'A' on all 117,443 rows - verified 2026-08-10 with
//      SELECT PSSTC, COUNT(*) FROM SGHDSDATA.HDMPSM GROUP BY PSSTC
//  so there is nothing to filter and no "active only" toggle is warranted. It
//  is kept in the query and the CSV export so a code introduced later still
//  shows up, but it is not a grid column - 117,443 identical 'A's is noise.
//  Re-run that GROUP BY after any Harris upgrade before trusting this.
//
//  HDIWHS  23,720 rows   PK IWITEM, IWWHS
//      IWOHQT  DECIMAL(13,4) Quantity On Hand
//      IWQSYT  DECIMAL(13,4) Quantity Sold YTD
//      IWQIYT  DECIMAL(13,4) Quantity - Issued YTD    <- spec said IWQITY; no such column
//      IWQKYT  DECIMAL(13,4) Quantity - Kit Issued YTD
//
//  HDIPLT  23,412 rows   PK IPPLT, IPITEM
//      IPQMFG  DECIMAL(13,4) YTD Quantity Manufactured
//      IPCMTO  DECIMAL(13,4) Quantity Committed To Manufacturing
//
//  HDPCLS  93 rows       PK PCPCLS
//      PCPCLS  CHAR(4)       Product Class
//      PCPCDS  CHAR(20)      Product Class Description

define('KSR_ITEMLEN', 15);

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

// Shop floor types 94-*; DB2 LIKE wants 94-%.
function ksr_toLike($v) {
    $s = trim((string)$v);
    if ($s === '') return '%';
    return str_replace(array('*', '?'), array('%', '_'), $s);
}

function ksr_rows($conn, $sql) {
    $s = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$s) return false;
    $out = array();
    while ($r = db2_fetch_assoc($s)) $out[] = $r;
    db2_free_stmt($s);
    return $out;
}

$conn = $i5Connect->getConnection();

// ── Filters ──────────────────────────────────────────────────────────────────

$fItem    = isset($_GET['item'])  ? trim((string)$_GET['item']) : '94-*';
$fClass   = isset($_GET['class']) ? strtoupper(trim((string)$_GET['class'])) : null;
$fLevels  = (isset($_GET['levels']) && $_GET['levels'] === 'all') ? 'all' : '1';
$maxLevel = ($fLevels === 'all') ? 10 : 1;

$itemLike = ksr_q(ksr_toLike($fItem));

// Product class list: HDPCLS, falling back to the distinct IMPCLS values in use.
$classOptions = array();
$classSource  = '';

$r = ksr_rows($conn,
    "SELECT RTRIM(PCPCLS) AS CODE, RTRIM(PCPCDS) AS DESCR
       FROM SGHDSDATA.HDPCLS
      WHERE RTRIM(PCPCLS) <> ''
      ORDER BY 1");
if ($r !== false && !empty($r)) {
    foreach ($r as $row) {
        $code = strtoupper(rtrim((string)$row['CODE']));
        if ($code !== '') $classOptions[$code] = rtrim((string)$row['DESCR']);
    }
    $classSource = 'SGHDSDATA.HDPCLS';
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

// Default to KITS when that class exists. An explicit class='' is the user
// choosing All Classes, so it is only defaulted when class is absent entirely.
if ($fClass === null) {
    $fClass = isset($classOptions['KITS']) ? 'KITS' : '';
} elseif ($fClass !== '' && !isset($classOptions[$fClass])) {
    $fClass = '';
}

// ── Query ────────────────────────────────────────────────────────────────────
//
//  HDIWHS is keyed item x warehouse and HDIPLT item x plant, so joining either
//  straight onto the BOM would multiply every component row by its warehouse
//  and plant counts. Both are pre-aggregated to one row per item, making the
//  quantities all-warehouse / all-plant totals. (In practice this is nearly a
//  1:1 file - 23,720 HDIWHS and 23,412 HDIPLT rows against 23,411 items - but
//  the aggregate is correct either way.)
//
//  A parent/child pair can legitimately repeat in HDMPSM: the key carries
//  PSSEQN (routing sequence) and PSREFN (engineering reference), so a component
//  consumed at two routing steps is two rows. Those are shown separately rather
//  than collapsed - that is what the structure actually says.
//
//  The BOM leg is a recursive CTE. LVL < 1 makes the recursive branch a no-op
//  for the single-level view, so both modes share one statement, and the level
//  cap also stops a cyclic structure from running away.

$clsWhere = ($fClass !== '')
    ? "       AND RTRIM(p.IMPCLS) = '" . ksr_q($fClass) . "'\n"
    : '';

$sql = "
WITH BOM (LVL, TOP_ITEM, PARENT_ITEM, SEQN, STAT, CHILD_ITEM, QTY_PER, EXT_QTY, BOM_PATH) AS (
    SELECT 1,
           CAST(RTRIM(p.IMITEM) AS VARCHAR(" . KSR_ITEMLEN . ")),
           CAST(RTRIM(p.IMITEM) AS VARCHAR(" . KSR_ITEMLEN . ")),
           b.PSSEQN,
           CAST(b.PSSTC AS CHAR(1)),
           CAST(RTRIM(b.PSCPN) AS VARCHAR(" . KSR_ITEMLEN . ")),
           CAST(b.PSQPER AS DECIMAL(15,5)),
           CAST(b.PSQPER AS DECIMAL(15,5)),
           CAST(RTRIM(b.PSCPN) AS VARCHAR(500))
      FROM SGHDSDATA.HDIMST p
      JOIN SGHDSDATA.HDMPSM b
        ON RTRIM(b.PSPPN) = RTRIM(p.IMITEM)
     WHERE RTRIM(p.IMITEM) LIKE '$itemLike'
$clsWhere
    UNION ALL

    SELECT r.LVL + 1,
           r.TOP_ITEM,
           r.CHILD_ITEM,
           b.PSSEQN,
           CAST(b.PSSTC AS CHAR(1)),
           CAST(RTRIM(b.PSCPN) AS VARCHAR(" . KSR_ITEMLEN . ")),
           CAST(b.PSQPER AS DECIMAL(15,5)),
           CAST(r.EXT_QTY * b.PSQPER AS DECIMAL(15,5)),
           CAST(r.BOM_PATH || ' > ' || RTRIM(b.PSCPN) AS VARCHAR(500))
      FROM BOM r
      JOIN SGHDSDATA.HDMPSM b
        ON RTRIM(b.PSPPN) = r.CHILD_ITEM
     WHERE r.LVL < $maxLevel
),
WH (ITM, OHQTY, SOLDYTD, ISSYTD, KITISSYTD) AS (
    SELECT RTRIM(IWITEM),
           SUM(IWOHQT), SUM(IWQSYT), SUM(IWQIYT), SUM(IWQKYT)
      FROM SGHDSDATA.HDIWHS
     GROUP BY RTRIM(IWITEM)
),
PL (ITM, MFGYTD, CMTMO) AS (
    SELECT RTRIM(IPITEM),
           SUM(IPQMFG), SUM(IPCMTO)
      FROM SGHDSDATA.HDIPLT
     GROUP BY RTRIM(IPITEM)
)
SELECT r.LVL                    AS LVL,
       r.TOP_ITEM               AS TOP_ITEM,
       RTRIM(tp.IMIMDS)         AS TOP_DESC,
       RTRIM(tp.IMPCLS)         AS TOP_CLASS,
       RTRIM(tp.IMKIT)          AS TOP_KIT,
       r.PARENT_ITEM            AS PARENT_ITEM,
       r.SEQN                   AS SEQN,
       r.STAT                   AS STAT,
       r.CHILD_ITEM             AS CHILD_ITEM,
       RTRIM(ci.IMIMDS)         AS CHILD_DESC,
       RTRIM(ci.IMPCLS)         AS CHILD_CLASS,
       r.QTY_PER                AS QTY_PER,
       r.EXT_QTY                AS EXT_QTY,
       r.BOM_PATH               AS BOM_PATH,
       COALESCE(WH.OHQTY,     0) AS OHQTY,
       COALESCE(WH.SOLDYTD,   0) AS SOLDYTD,
       COALESCE(WH.ISSYTD,    0) AS ISSYTD,
       COALESCE(WH.KITISSYTD, 0) AS KITISSYTD,
       COALESCE(PL.MFGYTD,    0) AS MFGYTD,
       COALESCE(PL.CMTMO,     0) AS CMTMO
  FROM BOM r
  LEFT JOIN SGHDSDATA.HDIMST tp ON RTRIM(tp.IMITEM) = r.TOP_ITEM
  LEFT JOIN SGHDSDATA.HDIMST ci ON RTRIM(ci.IMITEM) = r.CHILD_ITEM
  LEFT JOIN WH ON WH.ITM = r.CHILD_ITEM
  LEFT JOIN PL ON PL.ITM = r.CHILD_ITEM
 ORDER BY r.TOP_ITEM, r.LVL, r.BOM_PATH, r.SEQN, r.CHILD_ITEM
";

$rows   = array();
$sqlErr = '';

$res = ksr_rows($conn, $sql);
if ($res === false) {
    $sqlErr = db2_stmt_errormsg();
} else {
    $rows = $res;
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
        'Level', 'Kit Item', 'Kit Description', 'Kit Class', 'Kit Flag',
        'Parent Item', 'Seq', 'Status', 'Child Item', 'Child Description', 'Child Class',
        'Qty Per', 'Ext Qty Per Kit', 'BOM Path',
        'Qty On Hand', 'Qty Sold YTD', 'Qty Issued YTD', 'Qty Kit Issued YTD',
        'Qty Mfg YTD', 'Qty Committed To MO'
    ));
    foreach ($rows as $r) {
        fputcsv($out, array(
            (int)$r['LVL'],
            rtrim((string)$r['TOP_ITEM']),
            rtrim((string)$r['TOP_DESC']),
            rtrim((string)$r['TOP_CLASS']),
            rtrim((string)$r['TOP_KIT']),
            rtrim((string)$r['PARENT_ITEM']),
            (int)$r['SEQN'],
            rtrim((string)$r['STAT']),
            rtrim((string)$r['CHILD_ITEM']),
            rtrim((string)$r['CHILD_DESC']),
            rtrim((string)$r['CHILD_CLASS']),
            number_format((float)$r['QTY_PER'], 5, '.', ''),
            number_format((float)$r['EXT_QTY'], 5, '.', ''),
            rtrim((string)$r['BOM_PATH']),
            number_format((float)$r['OHQTY'],     4, '.', ''),
            number_format((float)$r['SOLDYTD'],   4, '.', ''),
            number_format((float)$r['ISSYTD'],    4, '.', ''),
            number_format((float)$r['KITISSYTD'], 4, '.', ''),
            number_format((float)$r['MFGYTD'],    4, '.', ''),
            number_format((float)$r['CMTMO'],     4, '.', ''),
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

$nCols = ($fLevels === 'all') ? 18 : 16;

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
#ksr-grid tbody td { color:#111827 !important; font-size:12px; white-space:nowrap; }
#ksr-grid tbody tr.ksr-newkit td { border-top:2px solid #374151; }
#ksr-grid td.ksr-zero { color:#CC1F20 !important; font-weight:bold; }
.ksr-lvl { display:inline-block; min-width:16px; text-align:center; font-weight:bold;
           background:#374151; color:#fff; border-radius:3px; padding:0 4px; }
.ksr-kitflag { display:inline-block; background:#1DA032; color:#fff; font-weight:bold;
               border-radius:3px; padding:0 5px; }
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

<?php if ($sqlErr): ?>
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
      across <?php echo number_format($kitCount); ?>&nbsp;parent<?php echo $kitCount === 1 ? '' : 's'; ?>
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
  Item <b><?php echo ksr_h($fItem); ?></b> &rarr; DB2 <code>LIKE '<?php echo ksr_h(ksr_toLike($fItem)); ?>'</code>.
  Classes from <b><?php echo ksr_h($classSource !== '' ? $classSource : 'n/a'); ?></b>.
  Structure <code>HDMPSM.PSPPN &rarr; PSCPN</code>, qty <code>PSQPER</code>;
  component figures are totals across all warehouses (HDIWHS) and plants (HDIPLT).
  <code>PSSTC</code> is 'A' on all 117,443 structure rows (verified 2026-08-10), so no
  status filter is applied and none is needed. <b>Kit</b> = <code>IMKIT</code> (K/F/C).
</div>

<div style="overflow-x:auto;">
<table id="ksr-grid" <?php echo $contentTable; ?>>
  <thead>
    <tr>
      <th class="colhdr">Lvl</th>
      <th class="colhdr">Kit Item</th>
      <th class="colhdr">Kit Description</th>
      <th class="colhdr">Class</th>
      <th class="colhdr">Kit</th>
      <?php if ($fLevels === 'all'): ?>
      <th class="colhdr">Parent Item</th>
      <?php endif; ?>
      <th class="colhdr">Seq</th>
      <th class="colhdr">Child Item</th>
      <th class="colhdr">Child Description</th>
      <th class="colhdr">Child Class</th>
      <th class="colhdr">Qty Per</th>
      <?php if ($fLevels === 'all'): ?>
      <th class="colhdr">Ext Qty/Kit</th>
      <?php endif; ?>
      <th class="colhdr">Qty On Hand</th>
      <th class="colhdr">Qty Sold YTD</th>
      <th class="colhdr">Qty Issued YTD</th>
      <th class="colhdr">Qty Kit Iss YTD</th>
      <th class="colhdr">Qty Mfg YTD</th>
      <th class="colhdr">Qty Cmtd To MO</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($rows) && !$sqlErr): ?>
    <tr><td colspan="<?php echo $nCols; ?>" class="colcode" align="center" style="padding:20px;">
      No product structures found for item <?php echo ksr_h($fItem); ?><?php
        echo $fClass !== '' ? ' in class ' . ksr_h($fClass) : ''; ?>.
    </td></tr>
<?php endif; ?>
<?php
$prevTop = null;
foreach ($rows as $r):
    $top     = rtrim((string)$r['TOP_ITEM']);
    $newKit  = ($top !== $prevTop);
    $prevTop = $top;
    $oh      = (float)$r['OHQTY'];
    $kitFlag = rtrim((string)$r['TOP_KIT']);
?>
    <tr class="<?php echo $newKit ? 'ksr-newkit' : ''; ?>">
      <td class="colcode"><span class="ksr-lvl"><?php echo (int)$r['LVL']; ?></span></td>
      <td class="colcode"><?php echo $newKit ? ksr_h($top) : '&nbsp;'; ?></td>
      <td class="colcode"><?php echo $newKit ? ksr_h(rtrim((string)$r['TOP_DESC'])) : '&nbsp;'; ?></td>
      <td class="colcode"><?php echo $newKit ? ksr_h(rtrim((string)$r['TOP_CLASS'])) : '&nbsp;'; ?></td>
      <td class="colcode"><?php
          echo ($newKit && $kitFlag !== '') ? '<span class="ksr-kitflag">' . ksr_h($kitFlag) . '</span>' : '&nbsp;'; ?></td>
      <?php if ($fLevels === 'all'): ?>
      <td class="colcode"><?php echo ksr_h(rtrim((string)$r['PARENT_ITEM'])); ?></td>
      <?php endif; ?>
      <td class="colcode" align="right"><?php echo (int)$r['SEQN']; ?></td>
      <td class="colcode"><b><?php echo ksr_h(rtrim((string)$r['CHILD_ITEM'])); ?></b></td>
      <td class="colcode" style="white-space:normal;"><?php echo ksr_h(rtrim((string)$r['CHILD_DESC'])); ?></td>
      <td class="colcode"><?php echo ksr_h(rtrim((string)$r['CHILD_CLASS'])); ?></td>
      <td class="colcode" align="right"><?php echo number_format((float)$r['QTY_PER'], 5); ?></td>
      <?php if ($fLevels === 'all'): ?>
      <td class="colcode" align="right"><?php echo number_format((float)$r['EXT_QTY'], 5); ?></td>
      <?php endif; ?>
      <td class="colcode<?php echo $oh <= 0 ? ' ksr-zero' : ''; ?>" align="right"><?php echo ksr_qty($oh); ?></td>
      <td class="colcode" align="right"><?php echo ksr_qty($r['SOLDYTD']); ?></td>
      <td class="colcode" align="right"><?php echo ksr_qty($r['ISSYTD']); ?></td>
      <td class="colcode" align="right"><?php echo ksr_qty($r['KITISSYTD']); ?></td>
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
