<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
date_default_timezone_set('America/Chicago');

// --- Search parameters ---
$srchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$searched = ($srchTerm !== '');

$rows = array(); $err = ''; $rowCount = 0;

function escSql($v) { return str_replace("'", "''", $v); }

if ($searched) {
    $conn = $i5Connect->getConnection();

    $v = escSql($srchTerm);
    $extraWhere = "
      AND (  UPPER(TRIM(u.OUFLDV))       LIKE UPPER('%{$v}%')
          OR UPPER(TRIM(CHAR(u.OUFLDR))) LIKE UPPER('%{$v}%')
          OR UPPER(TRIM(d.ODIMDS))       LIKE UPPER('%{$v}%')
          OR UPPER(TRIM(d.ODITEM))       LIKE UPPER('%{$v}%')
          OR UPPER(TRIM(h.OEORRF))       LIKE UPPER('%{$v}%')
          OR UPPER(TRIM(c.CMCNA1))       LIKE UPPER('%{$v}%'))";

    $sql = "
        SELECT
            h.\"OEORD#\"                                                AS ORDNUM,
            h.OEORST                                                    AS HORST,
            h.OEORTY                                                    AS ORTY,
            h.OEBDTE                                                    AS BDTE,
            TRIM(CHAR(h.OESHTO))                                        AS SHIPTO,
            TRIM(CHAR(h.OEBLTO))                                        AS BILLTO,
            h.OESLSM                                                    AS SLSNUM,
            TRIM(h.OEORRF)                                              AS PONBR,
            TRIM(h.OEUDF1)                                              AS CSREP,
            CASE WHEN s.SMREGN <> 'INACT' THEN TRIM(s.SMSNA1)
                 ELSE 'Ex-Sales' END                                    AS SLSNAME,
            COALESCE(TRIM(c.CMCNA1), '')                                AS CUSTNAME,
            COALESCE(TRIM(c.CMCCTY), '')                                AS CUSTCITY,
            COALESCE(TRIM(c.CMST),   '')                                AS CUSTST,
            d.\"ODORL#\"                                                AS LINENUM,
            d.ODORST                                                    AS DORST,
            COALESCE(NULLIF(NULLIF(TRIM(d.ODMORD), ''), '0'),
                (SELECT MIN(TRIM(oh.OHORD))
                 FROM SGHDSDATA.HDMOHM oh
                 WHERE oh.\"OHORD#\" = d.\"ODORD#\"
                 -- AND   oh.\"OHORL#\" = d.\"ODORL#\"
                 AND   TRIM(oh.OHPN) = TRIM(d.ODITEM)))                 AS MFORD,
            TRIM(d.ODITEM)                                              AS ITEM,
            TRIM(d.ODIMDS)                                              AS ITEMDESC,
            CASE WHEN d.ODITEM LIKE '93-%' THEN 'CStock'
                 WHEN d.ODITEM LIKE '94-%' THEN 'CStock'
                 WHEN d.ODITEM LIKE '95-%' THEN 'CStock'
                 WHEN d.ODITEM LIKE '96-%' THEN 'CStock'
                 WHEN d.ODITEM LIKE '97-%' THEN 'Stock'
                 WHEN d.ODITEM LIKE '99-%' THEN 'Stock'
                 WHEN d.ODITEM LIKE '3M-%' THEN 'Stock'
                 WHEN d.ODITEM LIKE '3M0%' THEN 'Stock'
                 ELSE 'Custom' END                                      AS ITEMTYP,
            d.ODQORD                                                    AS QORD,
            d.ODQSTD                                                    AS QSTD,
            CASE WHEN d.ODORST = 'C' THEN 0
                 WHEN d.ODQORD - d.ODQSTD < 0 THEN 0
                 ELSE d.ODQORD - d.ODQSTD END                          AS QTYDUE,
            d.ODSLPR                                                    AS SLPR,
            d.ODQORD * d.ODSLPR                                         AS TTLSALE,
            d.ODRQDT                                                    AS RQDT,
            (SELECT MIN(iw.IWWHS) FROM SGHDSDATA.HDIWHS iw
             WHERE iw.IWITEM = d.ODITEM)                                AS WHSE,
            TRIM(CHAR(u.OUFLDR))                                        AS OUFLDR,
            TRIM(u.OUFLDV)                                              AS OUFLDV
        FROM SGHDSDATA.OEORHD h
        JOIN SGHDSDATA.OEORDT d   ON h.\"OEORD#\" = d.\"ODORD#\"
        LEFT JOIN SGHDSDATA.OEOUDT u ON u.OUORD  = d.\"ODORD#\"
                                  AND u.OULINE = d.\"ODORL#\"
        LEFT JOIN SGHDSDATA.HDCUST c  ON h.OESHTO = c.CMCUST
        LEFT JOIN SGHDSDATA.HDSLSM s  ON h.OESLSM = s.SMSLSM
        WHERE h.\"OEORD#\" <> 0
          AND h.OEORTY NOT IN ('Q', 'U')$extraWhere
        ORDER BY h.OEBDTE DESC, h.\"OEORD#\" DESC, d.\"ODORL#\" ASC
        FETCH FIRST 500 ROWS ONLY
    ";

    $stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if ($stmt) {
        while ($r = db2_fetch_assoc($stmt)) {
            $rows[] = $r;
        }
        $rowCount = count($rows);
        db2_free_stmt($stmt);
    } else {
        $err = db2_stmt_errormsg();
    }
}

// Group by order number
$orders = array();
foreach ($rows as $r) {
    $ord = $r['ORDNUM'];
    if (!isset($orders[$ord])) {
        $orders[$ord] = array(
            'ordnum'   => $ord,
            'horst'    => trim((string)$r['HORST']),
            'bdte'     => $r['BDTE'],
            'shipto'   => $r['SHIPTO'],
            'slsname'  => $r['SLSNAME'],
            'custname' => $r['CUSTNAME'],
            'custcity' => $r['CUSTCITY'],
            'custst'   => $r['CUSTST'],
            'ponbr'    => $r['PONBR'],
            'csrep'    => $r['CSREP'],
            'lines'    => array(),
        );
    }
    $orders[$ord]['lines'][] = $r;
}

$eiBase = 'https://portal.screen-graphics.com:5601';

function cymdToDate($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $yy = intval($v / 10000);
    $mm = intval(($v % 10000) / 100);
    $dd = $v % 100;
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $yy);
}
function fmt($n)    { return '$' . number_format((float)$n, 2); }
function fmtQty($n) { return number_format((float)$n, 0); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Customer Service Inquiry</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px;
       background: #edf1f7; color: #1a2233; }

.topbar { background: #003087; color: #fff; padding: 8px 16px;
          display: flex; align-items: center; justify-content: space-between; }
.topbar h1 { font-size: 15px; font-weight: 700; }
.topbar .meta { font-size: 11px; color: #b8cfee; }

.content { padding: 12px 14px; }

/* Search form */
.search-card { background: #fff; border: 1px solid #c8d0de;
               border-radius: 4px; padding: 14px 16px; margin-bottom: 12px; }
.search-card h2 { font-size: 11px; font-weight: 700; color: #003087;
                  text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
.search-row { display: flex; gap: 10px; align-items: flex-end; }
.search-row .fg { flex: 0 0 500px; max-width: 500px; }
.fg label { display: block; font-size: 10px; font-weight: 700; color: #5a6478;
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
.fg input { width: 100%; padding: 6px 8px; border: 1px solid #b0bac8;
            border-radius: 3px; font-size: 13px; }
.fg input:focus { outline: none; border-color: #003087;
                  box-shadow: 0 0 0 2px rgba(0,48,135,.12); }
.search-actions { margin-top: 12px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.searched-lbl { font-size: 12px; color: #5a6478; }
.searched-lbl b { color: #003087; }
.btn-primary   { background: #003087; color: #fff; border: none;
                 padding: 7px 22px; border-radius: 3px; font-size: 13px;
                 font-weight: 700; cursor: pointer; }
.btn-primary:hover { background: #002060; }
.btn-secondary { background: #fff; color: #003087; border: 1px solid #003087;
                 padding: 7px 18px; border-radius: 3px; font-size: 13px;
                 cursor: pointer; }
.btn-secondary:hover { background: #e8edf8; }
.search-hint { margin-top: 8px; font-size: 11px; color: #888; }

/* KPI bar */
.result-bar { display: flex; gap: 12px; margin-bottom: 10px;
              flex-wrap: wrap; align-items: center; }
.kpi { background: #fff; border: 1px solid #d0d7e2;
       border-radius: 4px; padding: 6px 14px; }
.kpi .val { font-size: 20px; font-weight: 700; color: #003087; }
.kpi .lbl { font-size: 10px; color: #5a6478; text-transform: uppercase;
            letter-spacing: 1px; margin-top: 1px; }
.result-warn { font-size: 11px; color: #b06000; background: #fff8e1;
               border: 1px solid #f0c040; border-radius: 3px; padding: 4px 10px; }

/* Order blocks */
.order-block { background: #fff; border: 1px solid #c8d0de;
               border-radius: 4px; margin-bottom: 8px; overflow: hidden; }
.order-hdr { background: #002060; color: #fff; padding: 6px 12px;
             display: flex; align-items: center; gap: 12px;
             flex-wrap: wrap; font-size: 12px; }
.ord-num  { font-size: 14px; font-weight: 700; }
.cust-lbl { color: #a8c4f0; }
.sts { font-size: 10px; font-weight: 700; padding: 1px 7px;
       border-radius: 10px; white-space: nowrap; }
.sts-open   { background: #2a7a2a; color: #fff; }
.sts-closed { background: #666;    color: #eee; }
.order-meta { padding: 4px 12px; background: #1a3a6b; color: #c8d8f0;
              font-size: 11px; display: flex; gap: 16px; flex-wrap: wrap; }
.order-meta b { color: #fff; }

/* Lines table */
.tbl-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 900px; }
th { background: #e8edf5; color: #1a2233; padding: 4px 8px;
     font-size: 10px; font-weight: 700; white-space: nowrap;
     text-align: right; border-bottom: 1px solid #c8d0de; }
th.L, td.L { text-align: left; }
td { padding: 4px 8px; text-align: right; border-bottom: 1px solid #eef;
     white-space: nowrap; vertical-align: middle; }
tr:nth-child(even) td { background: #f7f8fc; }
.item-cell { font-weight: 700; color: #003087; }
.typ-custom { color: #8b0000; font-size: 10px; font-weight: 700; }
.typ-stock  { color: #2a7a2a; font-size: 10px; font-weight: 700; }
.typ-cstock { color: #b06000; font-size: 10px; font-weight: 700; }
.mo-link  { color: #003087; text-decoration: none; font-weight: 700; }
.mo-link:hover { text-decoration: underline; }
.item-link { color: #003087; text-decoration: none; font-weight: 700; }
.item-link:hover { text-decoration: underline; }
.cust-num-link { color: #a8c4f0; text-decoration: none; }
.cust-num-link:hover { text-decoration: underline; color: #c8d8ff; }
a.ord-link { color: #6db3ff; text-decoration: none; }
a.ord-link:hover { text-decoration: underline; color: #99ccff; }

.err   { background: #fdd; color: #900; padding: 8px 12px; border-radius: 4px;
         margin-bottom: 10px; font-family: monospace; font-size: 12px; }
.empty { text-align: center; padding: 30px; color: #888; font-size: 14px; }
</style>
</head>
<body>

<div class="topbar">
  <h1>Customer Service Inquiry</h1>
  <div class="meta"><?php echo htmlspecialchars(date('m/d/Y g:i a')); ?></div>
</div>

<div class="content">

  <!-- Search Form -->
  <div class="search-card">
    <h2>Search Customer Orders</h2>
    <form method="get" action="">
      <div class="search-row">
        <div class="fg">
          <label>Search (phone, name, DOT, asset, item description, P/O, customer &mdash; enter partial info)</label>
          <input type="text" name="q" value="" placeholder="enter any partial value to search across all fields..."
            autofocus>
        </div>
      </div>
      <div class="search-actions">
        <button type="submit" class="btn-primary">Search</button>
        <button type="button" class="btn-secondary"
          onclick="window.location='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>'">
          Clear
        </button>
        <?php if ($searched): ?>
        <span class="searched-lbl">Searched for: <b><?php echo htmlspecialchars($srchTerm); ?></b></span>
        <?php endif; ?>
      </div>
    </form>
    <p class="search-hint">Contains search &mdash; results limited to 500 lines.</p>
  </div>

<?php if ($searched): ?>

  <?php if ($err): ?>
    <div class="err">Query error: <?php echo htmlspecialchars($err); ?></div>
  <?php elseif (empty($orders)): ?>
    <div class="empty">No orders found matching your search criteria.</div>
  <?php else: ?>

  <?php
  $jsRows = array();
  $ordIdx = 0;
  foreach ($orders as &$ord) {
      $ord['jsIdx'] = $ordIdx;
      $jsRows[] = array(
          'ordNum'   => $ord['ordnum'],
          'custName' => $ord['custname'],
          'shipTo'   => $ord['shipto'],
          'status'   => $ord['horst'],
      );
      $ordIdx++;
  }
  unset($ord);
  ?>

  <div class="result-bar">
    <div class="kpi">
      <div class="val"><?php echo count($orders); ?></div>
      <div class="lbl">Orders</div>
    </div>
    <div class="kpi">
      <div class="val"><?php echo $rowCount; ?></div>
      <div class="lbl">Lines</div>
    </div>
    <?php if ($rowCount >= 500): ?>
    <div class="result-warn">
      &#9888; Results capped at 500 lines &mdash; narrow your search for complete results.
    </div>
    <?php endif; ?>
  </div>

  <script>
  var EI_BASE  = <?php echo json_encode($eiBase); ?>;
  var EI_EID   = <?php echo json_encode($eID); ?>;
  var CSI_ROWS = <?php echo json_encode(array_values($jsRows)); ?>;

  function openOrder(idx) {
      var r = CSI_ROWS[idx];
      var url;
      if (r.status === 'C') {
          url = EI_BASE + '/harris-CGI/SelectOrderHistory.d2w/REPORT'
              + '?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
              + '&eID='            + EI_EID
              + '&customerName='   + encodeURIComponent(r.custName)
              + '&customerNumber=' + encodeURIComponent(r.shipTo)
              + '&orderNumber='    + encodeURIComponent(r.ordNum)
              + '&orderSequence=0';
      } else {
          url = EI_BASE + '/harris-CGI/SelectOrder.d2w/REPORT'
              + '?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
              + '&eID='            + EI_EID
              + '&customerName='   + encodeURIComponent(r.custName)
              + '&customerNumber=' + encodeURIComponent(r.shipTo)
              + '&orderNumber='    + encodeURIComponent(r.ordNum);
      }
      window.open(url, '_blank');
  }

  function openCustomer(idx) {
      var r = CSI_ROWS[idx];
      window.open(EI_BASE + '/harris-CGI/CustomerSelect.d2w/REPORT'
          + '?baseVar=BaseConfiguration.icl&portal=CUSTOMER'
          + '&eID='            + EI_EID
          + '&customerName='   + encodeURIComponent(r.custName)
          + '&customerNumber=' + encodeURIComponent(r.shipTo), '_blank');
  }

  function openItem(item, desc, whse) {
      window.open(EI_BASE + '/harris-CGI/ItemSelect.d2w/REPORT'
          + '?baseVar=BaseConfiguration.icl&portal=ITEM'
          + '&eID='             + EI_EID
          + '&itemDescription=' + encodeURIComponent(desc)
          + '&itemNumber='      + encodeURIComponent(item), '_blank');
  }

  function openMO(mo) {
      if (!mo) return;
      window.open(EI_BASE + '/harris-CGI/SelectMfgOrder.d2w/REPORT'
          + '?baseVar=BaseConfiguration.icl&portal=MFGMGMT'
          + '&eID=' + EI_EID
          + '&mfgOrder='    + encodeURIComponent(mo)
          + '&plantNumber=1', '_blank');
  }
  </script>

  <?php foreach ($orders as $ord): ?>
  <div class="order-block">

    <div class="order-hdr">
      <a class="ord-link" href="javascript:openOrder(<?php echo $ord['jsIdx']; ?>)">
        <span class="ord-num">Order #<?php echo htmlspecialchars($ord['ordnum']); ?></span>
      </a>
      <span class="sts <?php echo $ord['horst'] === 'C' ? 'sts-closed' : 'sts-open'; ?>">
        <?php echo $ord['horst'] === 'C' ? 'CLOSED' : 'OPEN'; ?>
      </span>
      <span class="cust-lbl">
        <?php echo htmlspecialchars($ord['custname']); ?>
        (<a class="cust-num-link"
            href="javascript:openCustomer(<?php echo $ord['jsIdx']; ?>)"
            ><?php echo htmlspecialchars($ord['shipto']); ?></a>)
      </span>
      <?php if (trim($ord['ponbr']) !== ''): ?>
      <span>P/O: <?php echo htmlspecialchars(trim($ord['ponbr'])); ?></span>
      <?php endif; ?>
    </div>

    <div class="order-meta">
      <span>Booked: <b><?php echo cymdToDate($ord['bdte']); ?></b></span>
      <span>Salesperson: <b><?php echo htmlspecialchars($ord['slsname']); ?></b></span>
      <?php if (trim($ord['csrep']) !== ''): ?>
      <span>CS Rep: <b><?php echo htmlspecialchars(trim($ord['csrep'])); ?></b></span>
      <?php endif; ?>
      <?php
      $cityLine = trim(trim($ord['custcity']) . ', ' . trim($ord['custst']));
      $cityLine = rtrim($cityLine, ', ');
      if ($cityLine !== ''): ?>
      <span>City/St: <b><?php echo htmlspecialchars($cityLine); ?></b></span>
      <?php endif; ?>
    </div>

    <div class="tbl-wrap">
    <table>
      <thead><tr>
        <th class="L">Ln</th>
        <th class="L">Item</th>
        <th class="L">Type</th>
        <th class="L">Description</th>
        <th>Req Date</th>
        <th>Qty Ord</th>
        <th>Qty Due</th>
        <th>Unit Price</th>
        <th>Total Sale</th>
        <th class="L">MO #</th>
        <th class="L">Phone</th>
        <th class="L">Name / DOT / Asset / Other</th>
      </tr></thead>
      <tbody>
      <?php foreach ($ord['lines'] as $ln): ?>
      <?php
      $typ    = $ln['ITEMTYP'];
      $typCls = $typ === 'Custom' ? 'typ-custom'
              : ($typ === 'Stock' ? 'typ-stock' : 'typ-cstock');
      $mo  = trim($ln['MFORD']);
      $due = (float)$ln['QTYDUE'];
      ?>
      <tr>
        <td class="L"><?php echo (int)$ln['LINENUM']; ?></td>
        <td class="L item-cell"><a class="item-link"
            href="javascript:openItem(<?php
              echo htmlspecialchars(json_encode($ln['ITEM']));
            ?>,<?php
              echo htmlspecialchars(json_encode($ln['ITEMDESC']));
            ?>,<?php echo (int)$ln['WHSE']; ?>)"
            ><?php echo htmlspecialchars($ln['ITEM']); ?></a></td>
        <td class="L"><span class="<?php echo $typCls; ?>"><?php echo $typ; ?></span></td>
        <td class="L"><?php echo htmlspecialchars($ln['ITEMDESC']); ?></td>
        <td><?php echo cymdToDate($ln['RQDT']); ?></td>
        <td><?php echo fmtQty($ln['QORD']); ?></td>
        <td><?php echo $due > 0 ? fmtQty($due) : '&mdash;'; ?></td>
        <td><?php echo '$' . number_format((float)$ln['SLPR'], 3); ?></td>
        <td><?php echo fmt($ln['TTLSALE']); ?></td>
        <td class="L">
          <?php if ($mo !== '' && $mo !== '0'): ?>
          <a class="mo-link"
             href="javascript:openMO(<?php echo htmlspecialchars(json_encode($mo)); ?>)">
            <?php echo htmlspecialchars($mo); ?>
          </a>
          <?php else: echo '&mdash;'; endif; ?>
        </td>
        <td class="L"><?php
          $oufldr = trim((string)$ln['OUFLDR']);
          // Strip .000000 via string ops — avoids 32-bit overflow on 10-digit phone numbers
          if (strpos($oufldr, '.') !== false)
              $oufldr = rtrim(rtrim($oufldr, '0'), '.');
          if ($oufldr === '' || $oufldr === '0') {
              echo '&mdash;';
          } elseif (ctype_digit($oufldr) && strlen($oufldr) === 10) {
              echo '(' . substr($oufldr,0,3) . ') '
                 . substr($oufldr,3,3) . '-' . substr($oufldr,6,4);
          } else {
              echo htmlspecialchars($oufldr);
          }
        ?></td>
        <td class="L"><?php echo htmlspecialchars($ln['OUFLDV']); ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
  <?php endforeach; ?>

  <?php endif; ?>
<?php endif; ?>

</div><!-- .content -->
</body>
</html>
