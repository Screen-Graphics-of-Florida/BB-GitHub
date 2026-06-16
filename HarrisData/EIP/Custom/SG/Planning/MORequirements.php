<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$page_title  = 'Manufacturing Order Requirements';
$refreshSecs = 600;
$refreshedAt = date('m/d/Y g:i:s A');

// ── Helpers ──────────────────────────────────────────────────────────────────

function morr_cYmdToDate($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    $mm = intval(($v % 10000)   / 100);
    $dd = $v % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return '';
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $c * 100 + $yy);
}

function morr_int($v) {
    return ($v === null || $v === '') ? '' : number_format((int)$v);
}

function morr_dec4($v) {
    return ($v === null || $v === '') ? '' : number_format((float)$v, 4);
}

function morr_curr2($v) {
    return ($v === null || $v === '') ? '' : '$' . number_format((float)$v, 2);
}

function morr_curr3($v) {
    return ($v === null || $v === '') ? '' : '$' . number_format((float)$v, 3);
}

function morr_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// ── Query ─────────────────────────────────────────────────────────────────────
//
//  Converted from SEQUEL to standard DB2 SQL.
//  SEQUEL PARTIAL OUTER JOIN  -> LEFT JOINs anchored on HDIPLT (T01).
//  SEQUEL WDATA()             -> MAX() (non-key aggregate stand-in).
//  SEQUEL VALID_DATE/CVTDATE  -> raw CYMD integer returned; PHP formats it.
//
//  NOTE: IWQTRS (qty in warehouse transfers) is referenced from HDIWHS.
//  If this field name differs in your HDIWHS file, adjust accordingly.

$sql = "
    SELECT
        T02.IWWHS                                                   AS WH,
        T01.IPBAC                                                   AS ANALYST,
        T03.IMPCLS                                                  AS CLASS,
        T02.IWDTLS                                                  AS DTLSSOLD,
        (T01.IPQMRL + T02.IWOHQT + T02.IWQTRS)
            - (T01.IPSSQ + T02.IWRESQ + T01.IPCMTO)                AS SHORTAGE,
        T01.IPITEM                                                  AS ITEM,
        T02.IWOHQT                                                  AS OHQTY,
        T03.IMIMDS                                                  AS ITEMDESC,
        T03.IMUDA1                                                  AS DIECOLOR,
        T01.IPSSQ                                                   AS SFTYSTOCK,
        T01.IPALTS                                                  AS ACCTLOTSIZE,
        T02.IWRESQ                                                  AS CORDERS,
        T01.IPCMTO                                                  AS ALLOCMO,
        T01.IPQMRL                                                  AS MORELEASED,
        T02.IWQAYT + T02.IWQIYT + T02.IWQSYT                       AS USAGEYTD,
        SUM(CASE WHEN D.DTDPER BETWEEN 12501 AND 12512
                  AND D.DTIVTT IN ('SLOE','ISOU') THEN D.DTQTY
                 ELSE 0 END)                                        AS USAGE2025,
        SUM(CASE WHEN D.DTDPER BETWEEN 12401 AND 12412
                  AND D.DTIVTT IN ('SLOE','ISOU') THEN D.DTQTY
                 ELSE 0 END)                                        AS USAGE2024,
        SUM(CASE WHEN D.DTDPER BETWEEN 12301 AND 12312
                  AND D.DTIVTT IN ('SLOE','ISOU') THEN D.DTQTY
                 ELSE 0 END)                                        AS USAGE2023,
        SUM(CASE WHEN D.DTDPER BETWEEN 12201 AND 12212
                  AND D.DTIVTT IN ('SLOE','ISOU') THEN D.DTQTY
                 ELSE 0 END)                                        AS USAGE2022,
        SUM(CASE WHEN D.DTDPER BETWEEN 12101 AND 12112
                  AND D.DTIVTT IN ('SLOE','ISOU') THEN D.DTQTY
                 ELSE 0 END)                                        AS USAGE2021,
        MAX(CASE WHEN T02.IWQSYT > 0
                 THEN T02.IWDSYT / T02.IWQSYT
                 ELSE 0 END)                                        AS AVGPRICE,
        MAX(C.CMUCC1 + C.CMUCC2 + C.CMUCC3 + C.CMUCC4 + C.CMUCC5) AS UNITCOST
    FROM SGHDSDATA.HDIPLT T01
        LEFT JOIN SGHDSDATA.HDIWHS T02 ON T01.IPITEM = T02.IWITEM
        LEFT JOIN SGHDSDATA.HDIMST T03 ON T01.IPITEM = T03.IMITEM
        LEFT JOIN SGHDSDATA.HDMCMM C   ON T01.IPITEM = C.CMPN
                                      AND T01.IPPLT   = C.CMPLT
        LEFT JOIN SGHDSDATA.HDDTRN D   ON T01.IPITEM = D.DTITEM
    WHERE T01.IPPTYP <> 'P'
      AND (T01.IPQMRL + T02.IWOHQT + T02.IWQTRS)
          - (T01.IPSSQ + T02.IWRESQ + T01.IPCMTO) < 0
      AND C.CMCSET = 1
    GROUP BY
        T02.IWWHS, T01.IPBAC, T03.IMPCLS, T02.IWDTLS,
        T01.IPQMRL, T02.IWOHQT, T02.IWQTRS,
        T01.IPSSQ,  T02.IWRESQ, T01.IPCMTO,
        T01.IPITEM, T03.IMIMDS, T03.IMUDA1,
        T01.IPALTS, T02.IWQAYT, T02.IWQIYT, T02.IWQSYT
    ORDER BY T02.IWWHS ASC, T01.IPITEM ASC
";

$conn   = $i5Connect->getConnection();
$stmt   = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
$rows   = array();
$sqlErr = '';

if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $rows[] = $r;
    }
    db2_free_stmt($stmt);
} else {
    $sqlErr = db2_stmt_errormsg();
}

$rowCount = count($rows);

// ── CSV export ────────────────────────────────────────────────────────────────

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="MORequirements_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array(
        'W/H', 'Analyst', 'Class', 'Date Last Sold', 'Shortage', 'Item Number',
        'Qty On Hand', 'Item Description', 'Die/Color Info', 'Safety Stock Qty',
        'Accting Lot Size', 'Qty On COs', 'Qty Alloc To MOs', 'MO Qty Released',
        'Usage YTD', 'Usage 2025', 'Usage 2024', 'Usage 2023', 'Usage 2022', 'Usage 2021',
        'Avg Sale Price', 'Unit Cost'
    ));
    foreach ($rows as $r) {
        fputcsv($out, array(
            $r['WH'],
            trim((string)$r['ANALYST']),
            trim((string)$r['CLASS']),
            morr_cYmdToDate($r['DTLSSOLD']),
            (int)$r['SHORTAGE'],
            trim((string)$r['ITEM']),
            (float)$r['OHQTY'],
            trim((string)$r['ITEMDESC']),
            trim((string)$r['DIECOLOR']),
            (int)$r['SFTYSTOCK'],
            (int)$r['ACCTLOTSIZE'],
            (int)$r['CORDERS'],
            (int)$r['ALLOCMO'],
            (int)$r['MORELEASED'],
            (int)$r['USAGEYTD'],
            (int)$r['USAGE2025'],
            (int)$r['USAGE2024'],
            (int)$r['USAGE2023'],
            (int)$r['USAGE2022'],
            (int)$r['USAGE2021'],
            number_format((float)$r['AVGPRICE'], 2, '.', ''),
            number_format((float)$r['UNITCOST'], 3, '.', '')
        ));
    }
    fclose($out);
    exit;
}

// ── HTML output ───────────────────────────────────────────────────────────────

$exportParams         = $_GET;
$exportParams['export'] = 'csv';
$exportURL            = '?' . http_build_query($exportParams);

print "\n<html><head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n</head>";
require_once 'Banner.php';

?>
<table <?php echo $baseTable; ?>>
<tr valign="top">
<td class="content">

<table width="100%" cellpadding="2" cellspacing="0" border="0">
  <tr>
    <td><h1>&nbsp;&nbsp;Manufacturing Order Requirements</h1></td>
    <td align="right" nowrap style="padding-right:10px;font-size:11px;">
      <b><?php echo $rowCount; ?>&nbsp;item<?php echo $rowCount === 1 ? '' : 's'; ?></b>
      &nbsp;|&nbsp;Refreshed:&nbsp;<?php echo morr_h($refreshedAt); ?>
      &nbsp;|&nbsp;Next&nbsp;refresh:&nbsp;<b><span id="morr-cd"><?php echo (int)$refreshSecs; ?></span>s</b>
      &nbsp;&nbsp;<a href="<?php echo morr_h($exportURL); ?>" style="font-size:11px;">&#x21E9; Export CSV</a>
    </td>
  </tr>
  <tr>
    <td colspan="2" style="padding:0 0 4px 20px;font-size:11px;color:#555;">
      Items with Net Shortage &mdash; Auto-refreshes every 10 minutes
    </td>
  </tr>
</table>

<?php if ($sqlErr): ?>
<p style="color:red;font-weight:bold;padding:8px;"><?php echo morr_h('SQL Error: ' . $sqlErr); ?></p>
<?php endif; ?>

<style type="text/css">
#morr-grid thead th { cursor:pointer; user-select:none; white-space:nowrap; }
#morr-grid thead th:hover { opacity:0.85; }
#morr-grid thead th.morr-asc::after  { content:' \25B2'; font-size:9px; }
#morr-grid thead th.morr-desc::after { content:' \25BC'; font-size:9px; }
</style>

<table id="morr-grid" <?php echo $contentTable; ?> style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th class="colhdr">W/H</th>
      <th class="colhdr">Analyst</th>
      <th class="colhdr">Class</th>
      <th class="colhdr">Date Last Sold</th>
      <th class="colhdr">Shortage</th>
      <th class="colhdr">Item Number</th>
      <th class="colhdr">Qty On Hand</th>
      <th class="colhdr">Item Description</th>
      <th class="colhdr">Die/Color Info</th>
      <th class="colhdr">Safety Stock Qty</th>
      <th class="colhdr">Accting Lot Size</th>
      <th class="colhdr">Qty On CO's</th>
      <th class="colhdr">Qty Alloc To MO's</th>
      <th class="colhdr">MO Qty Released</th>
      <th class="colhdr">Usage YTD</th>
      <th class="colhdr">Usage 2025</th>
      <th class="colhdr">Usage 2024</th>
      <th class="colhdr">Usage 2023</th>
      <th class="colhdr">Usage 2022</th>
      <th class="colhdr">Usage 2021</th>
      <th class="colhdr">Avg Sale Price</th>
      <th class="colhdr">Unit Cost</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($rows) && !$sqlErr): ?>
    <tr>
      <td colspan="22" class="colcode" align="center" style="padding:20px;">
        No shortage items found.
      </td>
    </tr>
<?php endif; ?>
<?php foreach ($rows as $r):
    $shortage = (float)(isset($r['SHORTAGE']) ? $r['SHORTAGE'] : 0);
    $rowStyle = $shortage < 0 ? ' style="background-color:#ffcccc;"' : '';
?>
    <tr<?php echo $rowStyle; ?>>
      <td class="colcode" align="right"><?php echo morr_h($r['WH']); ?></td>
      <td class="colcode"><?php echo morr_h(trim((string)$r['ANALYST'])); ?></td>
      <td class="colcode"><?php echo morr_h(trim((string)$r['CLASS'])); ?></td>
      <td class="colcode" data-val="<?php echo (int)$r['DTLSSOLD']; ?>"><?php echo morr_h(morr_cYmdToDate($r['DTLSSOLD'])); ?></td>
      <td class="colcode" align="right"<?php echo $shortage < 0 ? ' style="color:#cc0000;font-weight:bold;"' : ''; ?>>
        <?php echo morr_int($r['SHORTAGE']); ?></td>
      <td class="colcode"><?php echo morr_h(trim((string)$r['ITEM'])); ?></td>
      <td class="colcode" align="right"><?php echo morr_dec4($r['OHQTY']); ?></td>
      <td class="colcode"><?php echo morr_h(trim((string)$r['ITEMDESC'])); ?></td>
      <td class="colcode"><?php echo morr_h(trim((string)$r['DIECOLOR'])); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['SFTYSTOCK']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['ACCTLOTSIZE']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['CORDERS']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['ALLOCMO']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['MORELEASED']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['USAGEYTD']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['USAGE2025']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['USAGE2024']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['USAGE2023']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['USAGE2022']); ?></td>
      <td class="colcode" align="right"><?php echo morr_int($r['USAGE2021']); ?></td>
      <td class="colcode" align="right"><?php echo morr_curr2($r['AVGPRICE']); ?></td>
      <td class="colcode" align="right"><?php echo morr_curr3($r['UNITCOST']); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>

</td>
</tr>
</table>

<script type="text/javascript">
(function () {
    var secs = <?php echo (int)$refreshSecs; ?>;
    var el   = document.getElementById('morr-cd');
    function tick() {
        if (!el) return;
        if (secs <= 0) { location.reload(); return; }
        el.innerHTML = secs;
        secs--;
        setTimeout(tick, 1000);
    }
    tick();
}());

(function () {
    var tbl   = document.getElementById('morr-grid');
    if (!tbl) return;
    var tbody = tbl.querySelector('tbody');
    var ths   = tbl.querySelectorAll('thead th');
    var state = { col: -1, dir: 1 };

    function cellVal(td) {
        if (td.hasAttribute('data-val')) {
            return parseFloat(td.getAttribute('data-val')) || 0;
        }
        var t = td.textContent.replace(/[\$,]/g, '').trim();
        if (t === '') return null;
        var n = parseFloat(t);
        return isNaN(n) ? t.toLowerCase() : n;
    }

    function sortBy(col) {
        state.dir = (state.col === col) ? -state.dir : 1;
        state.col = col;

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        rows.sort(function (a, b) {
            var va = cellVal(a.cells[col]);
            var vb = cellVal(b.cells[col]);
            // nulls/empty always last
            if (va === null && vb === null) return 0;
            if (va === null) return 1;
            if (vb === null) return -1;
            if (va < vb) return -state.dir;
            if (va > vb) return  state.dir;
            return 0;
        });
        rows.forEach(function (r) { tbody.appendChild(r); });

        for (var i = 0; i < ths.length; i++) {
            ths[i].className = ths[i].className
                .replace(/\s*morr-(asc|desc)/g, '');
        }
        ths[col].className += (state.dir === 1 ? ' morr-asc' : ' morr-desc');
    }

    for (var i = 0; i < ths.length; i++) {
        (function (col) {
            ths[col].addEventListener('click', function () { sortBy(col); });
        }(i));
    }
}());
</script>

</body>
</html>
