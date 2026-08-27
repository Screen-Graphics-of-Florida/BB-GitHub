<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

// -----------------------------------------------------------------------------
//  Q4 Buyer Pattern - live Q4 buying-pattern inquiry
//
//  Built 2026-08-27 from the 17 Aug 2026 "Q4 Buyer Pattern" deck (slide 10 spec).
//  Purpose: find customers who habitually order in Q4 so reps can call them in
//  Aug/Sep and pull those orders into Q3, plus the wider recovery list.
//
//  Grain            ship-to (OESHTO), never bill-to.
//  Period anchor    ORDER date (h.OEBDTE) - the goal is moving WHEN they order.
//  Revenue          DHQORD x DHSLPR / DHORUF  (ordered qty, not shipped)
//                   The deck said DHQORD x DHSLPR with no /DHORUF. Every other
//                   SG revenue page (SalesDashboard, SalesDrilldown,
//                   CustClassSales5Yr, BottomHalfRevenue) divides by DHORUF,
//                   the pricing unit factor. The divisor is kept here so this
//                   page ties to those; the rules-check strip reports what the
//                   divisor is actually worth so the deck can be reconciled.
//                   Override with ?rev=deck to reproduce the deck exactly.
//  Invoiced only    OEORDH where DHSEQ# <> 0. OEORDT is never unioned in.
//  Exclusions       order types F/N/P/Q/S/U/V; items AD0166, LTL*, *SAMP*;
//                   5 internal bill-to accounts; 4 named COs.
//  Open orders      shown as their own column, never used to suppress a call
//                   (decision 2026-08-27: "show both, don't filter").
//
//  No date_default_timezone_set: display times come from the browser, and every
//  date boundary is derived from the IBM i's own CURRENT DATE.
// -----------------------------------------------------------------------------

$page_title = 'Q4 Buyer Pattern';
$eiBase     = 'https://portal.screen-graphics.com:5601';

// Revenue mode: 'factored' divides by DHORUF (ties to every other SG page).
// 'deck' reproduces the 17 Aug deck exactly (no divisor). Switch via ?rev=deck.
$Q4_REV_MODE = (isset($_GET['rev']) && $_GET['rev'] === 'deck') ? 'deck' : 'factored';

// Tier thresholds - slide 5 of the deck
$Q4_SHARE_STRONG = 0.35;   // "35%+ of revenue in Q4"
$Q4_YEARS_STRONG = 2;      // "Q4 orders in 2+ years"
$Q4_DOWN_FACTOR  = 0.50;   // "under half their normal year"
$Q4_SHARE_TILE   = 0.25;   // slide 2 tile: "25% or more of their revenue into Q4"
$Q4_CALL_LEAD    = 45;     // days ahead of their own average Q4 kickoff

// Rules from the prompt files, 17 Aug 2026
$Q4_BAD_ORDTY  = "'F','N','P','Q','S','U','V'";
$Q4_BAD_BILLTO = '9999999,9999800,9999201,9999200,9999100';
$Q4_BAD_ORDERS = '356066,347305,356706';

// STILL PENDING: the deck claims "entry code N" is excluded on the invoiced
// side. ODOREC exists on OEORDT (open orders) and is applied below, but the
// OEORDH equivalent is unconfirmed. Left empty rather than guessed - the
// RULES PENDING banner warns on screen until it is wired.
$Q4_RULE_ENTRYCODE = '';   // e.g. " AND d.DHORCS <> 'N'"

$Q4_PENDING = array();
if ($Q4_RULE_ENTRYCODE === '') {
    $Q4_PENDING[] = 'Entry-code N exclusion is NOT applied on the invoiced side '
                  . '(OEORDH field name unconfirmed). Open orders do apply it via ODOREC.';
}

// -- Helpers ------------------------------------------------------------------

function q4_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function q4_money($v, $dec = 0) {
    return '$' . number_format((float)$v, $dec);
}

function q4_moneyK($v) {
    $v = (float)$v;
    if (abs($v) >= 1000000) return '$' . number_format($v / 1000000, 2) . 'M';
    if (abs($v) >= 1000)    return '$' . number_format($v / 1000, 1) . 'K';
    return '$' . number_format($v, 0);
}

function q4_int($v) {
    return number_format((int)$v);
}

function q4_pct($v, $dec = 0) {
    return number_format((float)$v * 100, $dec) . '%';
}

function q4_cymd($y, $m, $d) {
    return ($y - 1900) * 10000 + $m * 100 + $d;
}

function q4_cymdToDate($v) {
    $v = (int)$v;
    if ($v <= 0) return '';
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    $mm = intval(($v % 10000) / 100);
    $dd = $v % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return '';
    return sprintf('%02d/%02d/%04d', $mm, $dd, 1900 + $c * 100 + $yy);
}

$conn    = $i5Connect->getConnection();
$sqlErr  = array();
$timings = array();

function q4_fetchAll($conn, $sql, $label, &$sqlErr, &$timings) {
    $t0   = microtime(true);
    $rows = array();
    $stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if ($stmt) {
        while ($r = db2_fetch_assoc($stmt)) { $rows[] = $r; }
        db2_free_stmt($stmt);
    } else {
        $sqlErr[] = $label . ': ' . db2_stmt_errormsg();
    }
    $timings[$label] = round((microtime(true) - $t0) * 1000);
    return $rows;
}

// -- Date frame, taken from the IBM i itself ----------------------------------

$todayRow = q4_fetchAll($conn,
    "SELECT YEAR(CURRENT DATE) AS Y, MONTH(CURRENT DATE) AS M, DAY(CURRENT DATE) AS D
       FROM SYSIBM.SYSDUMMY1", 'currentDate', $sqlErr, $timings);
$curY = !empty($todayRow) ? (int)$todayRow[0]['Y'] : 2026;
$curM = !empty($todayRow) ? (int)$todayRow[0]['M'] : 1;
$curD = !empty($todayRow) ? (int)$todayRow[0]['D'] : 1;
$todayCymd = q4_cymd($curY, $curM, $curD);

// Three history years then the current, partial year - self-advancing
$hy       = array($curY - 3, $curY - 2, $curY - 1);
$yrs      = array($hy[0], $hy[1], $hy[2], $curY);
$winStart = q4_cymd($hy[0], 1, 1);

$yb = array();
foreach ($yrs as $y) {
    $yb[$y] = array(
        'start' => q4_cymd($y, 1, 1),
        'end'   => q4_cymd($y, 12, 31),
        'q4s'   => q4_cymd($y, 10, 1),
        'q4e'   => q4_cymd($y, 12, 31),
    );
}

// -- Shared SQL fragments -----------------------------------------------------

$REV = ($Q4_REV_MODE === 'deck')
     ? 'd.DHQORD * d.DHSLPR'
     : 'CASE WHEN d.DHORUF <> 0 THEN d.DHQORD * d.DHSLPR / d.DHORUF ELSE d.DHQORD * d.DHSLPR END';

$INVOICED_FROM = "
    FROM SGHDSDATA.OEORDH d
    JOIN SGHDSDATA.OEORHD h      ON d.\"DHORD#\" = h.\"OEORD#\"
    LEFT JOIN SGHDSDATA.HDCUST c ON h.OESHTO     = c.CMCUST
";

$INVOICED_WHERE = "
    WHERE d.\"DHSEQ#\" <> 0
      AND d.DHQORD    <> 0
      AND h.OEBDTE    >= $winStart
      AND h.OEBDTE    <= $todayCymd
      AND h.OEORTY NOT IN ($Q4_BAD_ORDTY)
      AND TRIM(d.DHITEM) <> 'AD0166'
      AND TRIM(d.DHITEM) NOT LIKE 'LTL%'
      AND TRIM(d.DHITEM) NOT LIKE '%SAMP%'
      AND COALESCE(c.CMBLTO, 0) NOT IN ($Q4_BAD_BILLTO)
      AND h.\"OEORD#\" NOT IN ($Q4_BAD_ORDERS)
      $Q4_RULE_ENTRYCODE
";

// Per-year revenue / Q4 revenue / Q4 order-count / Q4 first-order columns
$selYear = '';
foreach ($yrs as $y) {
    $s  = $yb[$y]['start']; $e  = $yb[$y]['end'];
    $qs = $yb[$y]['q4s'];   $qe = $yb[$y]['q4e'];
    $selYear .= "
        SUM(CASE WHEN h.OEBDTE BETWEEN $s  AND $e  THEN $REV ELSE 0 END) AS REV$y,
        SUM(CASE WHEN h.OEBDTE BETWEEN $qs AND $qe THEN $REV ELSE 0 END) AS Q4R$y,
        COUNT(DISTINCT CASE WHEN h.OEBDTE BETWEEN $qs AND $qe THEN h.\"OEORD#\" END) AS Q4O$y,
        MIN(CASE WHEN h.OEBDTE BETWEEN $qs AND $qe THEN h.OEBDTE END) AS Q4F$y,";
}

// -- Query 1: one row per ship-to ---------------------------------------------

$sqlCust = "
    SELECT
        h.OESHTO                            AS SHIPTO,
        MAX(TRIM(c.CMCNA1))                 AS CUSTNAME,
        MAX(COALESCE(TRIM(c.CMCCLS), '??')) AS CLSCODE,
        MAX(TRIM(c.CMCCTY))                 AS CITY,
        MAX(TRIM(c.CMST))                   AS STATE,
        MAX(TRIM(c.CMPHON))                 AS PHONE,
        MAX(c.CMSLSM)                       AS SLSM,
        MAX(c.CMBLTO)                       AS BILLTO,
        $selYear
        MAX(h.OEBDTE)                       AS LASTORD,
        COUNT(DISTINCT h.\"OEORD#\")        AS ORDCNT
    $INVOICED_FROM
    $INVOICED_WHERE
    GROUP BY h.OESHTO
";
$custRows = q4_fetchAll($conn, $sqlCust, 'customers', $sqlErr, $timings);

// -- Query 2: one row per ship-to x item --------------------------------------

$selItemYear = '';
foreach ($yrs as $y) {
    $s = $yb[$y]['start']; $e = $yb[$y]['end'];
    $selItemYear .= "
        SUM(CASE WHEN h.OEBDTE BETWEEN $s AND $e THEN $REV     ELSE 0 END) AS R$y,
        SUM(CASE WHEN h.OEBDTE BETWEEN $s AND $e THEN d.DHQORD ELSE 0 END) AS Q$y,
        MAX(CASE WHEN h.OEBDTE BETWEEN $s AND $e THEN 1 ELSE 0 END)        AS F$y,";
}
$histS = $yb[$hy[0]]['start'];
$histE = $yb[$hy[2]]['end'];

$sqlItem = "
    SELECT
        h.OESHTO            AS SHIPTO,
        TRIM(d.DHITEM)      AS ITEM,
        MAX(TRIM(d.DHIMDS)) AS ITEMDESC,
        $selItemYear
        COUNT(DISTINCT CASE WHEN h.OEBDTE BETWEEN $histS AND $histE
                            THEN h.\"OEORD#\" END) AS HISTORDS,
        MAX(h.OEBDTE)       AS LASTORD
    $INVOICED_FROM
    $INVOICED_WHERE
    GROUP BY h.OESHTO, TRIM(d.DHITEM)
";
$itemRows = q4_fetchAll($conn, $sqlItem, 'items', $sqlErr, $timings);

// -- Query 3: open unshipped orders (shown, never used to suppress) -----------

$sqlOpen = "
    SELECT
        h.OESHTO                     AS SHIPTO,
        COUNT(DISTINCT h.\"OEORD#\") AS OPENORDS,
        SUM(d.ODQORD * d.ODSLPR)     AS OPENAMT
    FROM SGHDSDATA.OEORHD h
    JOIN SGHDSDATA.OEORDT d      ON h.\"OEORD#\" = d.\"ODORD#\"
    LEFT JOIN SGHDSDATA.HDCUST c ON h.OESHTO     = c.CMCUST
    WHERE h.OEORTY NOT IN ($Q4_BAD_ORDTY)
      AND d.ODOREC <> 'N'
      AND TRIM(d.ODITEM) <> 'AD0166'
      AND TRIM(d.ODITEM) NOT LIKE 'LTL%'
      AND TRIM(d.ODITEM) NOT LIKE '%SAMP%'
      AND COALESCE(c.CMBLTO, 0) NOT IN ($Q4_BAD_BILLTO)
      AND h.\"OEORD#\" NOT IN ($Q4_BAD_ORDERS)
    GROUP BY h.OESHTO
";
$openRows = q4_fetchAll($conn, $sqlOpen, 'openOrders', $sqlErr, $timings);

// -- Query 4: class descriptions ----------------------------------------------

$clsRows = q4_fetchAll($conn,
    "SELECT TRIM(CCCCLS) AS CODE, TRIM(CCCCDS) AS DESCR FROM SGHDSDATA.HDCCLS",
    'classes', $sqlErr, $timings);
$clsName = array();
foreach ($clsRows as $r) { $clsName[$r['CODE']] = $r['DESCR']; }

// -- Query 5: salesperson names -----------------------------------------------

$slsRows = q4_fetchAll($conn,
    "SELECT SMSLSM AS CODE, TRIM(SMSNA1) AS NAME FROM SGHDSDATA.HDSLSM",
    'salespeople', $sqlErr, $timings);
$slsName = array();
foreach ($slsRows as $r) { $slsName[(int)$r['CODE']] = $r['NAME']; }

// -- Query 6: rules check - what the DHORUF divisor is actually worth ---------

$sqlCheck = "
    SELECT
        COUNT(*)                                                AS LINES_ALL,
        SUM(CASE WHEN d.DHORUF NOT IN (0, 1) THEN 1 ELSE 0 END) AS LINES_FACTORED,
        SUM(CASE WHEN d.DHORUF = 0 THEN 1 ELSE 0 END)           AS LINES_ZEROUF,
        SUM(d.DHQORD * d.DHSLPR)                                AS REV_NODIV,
        SUM(CASE WHEN d.DHORUF <> 0 THEN d.DHQORD * d.DHSLPR / d.DHORUF
                 ELSE d.DHQORD * d.DHSLPR END)                  AS REV_DIV,
        SUM(CASE WHEN d.DHORUF <> 0 THEN d.DHQSTC * d.DHSLPR / d.DHORUF
                 ELSE d.DHQSTC * d.DHSLPR END)                  AS REV_SHIPPED
    $INVOICED_FROM
    $INVOICED_WHERE
";
$checkRows = q4_fetchAll($conn, $sqlCheck, 'rulesCheck', $sqlErr, $timings);
$chk = !empty($checkRows) ? $checkRows[0] : array();

// -- Build the per-customer model ---------------------------------------------

$openBy = array();
foreach ($openRows as $r) {
    $openBy[trim((string)$r['SHIPTO'])] = array(
        'ords' => (int)$r['OPENORDS'],
        'amt'  => (float)$r['OPENAMT'],
    );
}

// Item roll-up: stopped / reduced dollars per ship-to, and per-SKU totals
$itemAgg = array();
$skuAgg  = array();

foreach ($itemRows as $r) {
    $sh   = trim((string)$r['SHIPTO']);
    $item = trim((string)$r['ITEM']);

    $histRev = 0.0; $histQty = 0.0; $yrsWith = 0;
    foreach ($hy as $y) {
        $histRev += (float)$r['R' . $y];
        $histQty += (float)$r['Q' . $y];
        $yrsWith += (int)$r['F' . $y];
    }
    $curRev   = (float)$r['R' . $curY];
    $histOrds = (int)$r['HISTORDS'];

    // A genuine repeat purchase: 2+ years, or 3+ separate orders. One-time
    // custom prints are deliberately not counted as lost reorders.
    $isRepeat = ($yrsWith >= 2) || ($histOrds >= 3);
    if (!$isRepeat || $histRev <= 0 || $yrsWith === 0) { continue; }

    $normal    = $histRev / $yrsWith;
    $normalQty = $histQty / $yrsWith;

    if (!isset($itemAgg[$sh])) {
        $itemAgg[$sh] = array('stopAmt' => 0.0, 'redAmt' => 0.0, 'stopN' => 0, 'redN' => 0);
    }

    if ($curRev <= 0) {
        $itemAgg[$sh]['stopAmt'] += $normal;
        $itemAgg[$sh]['stopN']++;
        if (!isset($skuAgg[$item])) {
            $skuAgg[$item] = array('desc' => trim((string)$r['ITEMDESC']),
                                   'custs' => 0, 'amt' => 0.0, 'qty' => 0.0);
        }
        $skuAgg[$item]['custs']++;
        $skuAgg[$item]['amt'] += $normal;
        $skuAgg[$item]['qty'] += $normalQty;
    } elseif ($curRev < $normal * $Q4_DOWN_FACTOR) {
        $itemAgg[$sh]['redAmt'] += ($normal - $curRev);
        $itemAgg[$sh]['redN']++;
    }
}

$custs = array();
foreach ($custRows as $r) {
    $sh = trim((string)$r['SHIPTO']);

    $histRev = 0.0; $q4Rev = 0.0; $q4Years = 0; $yrsWith = 0;
    $byYear = array(); $q4ByYear = array(); $q4Ord = array(); $q4First = array();
    foreach ($hy as $y) {
        $rv = (float)$r['REV' . $y];
        $q4 = (float)$r['Q4R' . $y];
        $qo = (int)$r['Q4O' . $y];
        $byYear[$y]   = $rv;
        $q4ByYear[$y] = $q4;
        $q4Ord[$y]    = $qo;
        $q4First[$y]  = (int)$r['Q4F' . $y];
        $histRev += $rv;
        $q4Rev   += $q4;
        if ($rv > 0) { $yrsWith++; }
        if ($qo > 0) { $q4Years++; }
    }
    $curRev          = (float)$r['REV' . $curY];
    $byYear[$curY]   = $curRev;
    $q4ByYear[$curY] = (float)$r['Q4R' . $curY];
    $q4Ord[$curY]    = (int)$r['Q4O' . $curY];

    if ($histRev <= 0 || $yrsWith === 0) { continue; }

    $normal   = $histRev / $yrsWith;
    $q4Share  = ($histRev > 0) ? ($q4Rev / $histRev) : 0.0;
    $strongQ4 = ($q4Share >= $Q4_SHARE_STRONG && $q4Years >= $Q4_YEARS_STRONG);
    $silent   = ($curRev <= 0);
    $downHalf = (!$silent && $curRev < $normal * $Q4_DOWN_FACTOR);

    // Tier assignment - slide 5
    $tier = 0;
    if     ($strongQ4 && $silent)   { $tier = 1; }
    elseif ($strongQ4 && $downHalf) { $tier = 2; }
    elseif ($strongQ4)              { $tier = 3; }
    elseif ($silent)                { $tier = 4; }
    elseif ($downHalf)              { $tier = 5; }

    // Call-by date: $Q4_CALL_LEAD days ahead of their own average Q4 kickoff
    $kickDays = array();
    foreach ($hy as $y) {
        $f = $q4First[$y];
        if ($f > 0) {
            $mm = intval(($f % 10000) / 100);
            $dd = $f % 100;
            $kickDays[] = ($mm - 10) * 31 + $dd;
        }
    }
    $callBy = '';
    if (!empty($kickDays)) {
        $avgInto = array_sum($kickDays) / count($kickDays);
        $kickTs  = mktime(0, 0, 0, 10, 1, $curY) + (int)round($avgInto - 1) * 86400;
        $callBy  = date('Y-m-d', $kickTs - $Q4_CALL_LEAD * 86400);
    }

    $ia  = isset($itemAgg[$sh]) ? $itemAgg[$sh] : array('stopAmt'=>0.0,'redAmt'=>0.0,'stopN'=>0,'redN'=>0);
    $op  = isset($openBy[$sh])  ? $openBy[$sh]  : array('ords'=>0,'amt'=>0.0);
    $cls = trim((string)$r['CLSCODE']);

    $custs[] = array(
        'shipto'   => $sh,
        'name'     => trim((string)$r['CUSTNAME']),
        'cls'      => $cls,
        'clsdesc'  => isset($clsName[$cls]) ? $clsName[$cls] : $cls,
        'city'     => trim((string)$r['CITY']),
        'state'    => trim((string)$r['STATE']),
        'phone'    => trim((string)$r['PHONE']),
        'slsm'     => (int)$r['SLSM'],
        'slsmname' => isset($slsName[(int)$r['SLSM']]) ? $slsName[(int)$r['SLSM']] : '',
        'byYear'   => $byYear,
        'q4ByYear' => $q4ByYear,
        'q4Ord'    => $q4Ord,
        'histRev'  => $histRev,
        'curRev'   => $curRev,
        'normal'   => $normal,
        'q4Rev'    => $q4Rev,
        'q4Share'  => $q4Share,
        'q4Years'  => $q4Years,
        'tier'     => $tier,
        'silent'   => $silent,
        'callBy'   => $callBy,
        'stopAmt'  => $ia['stopAmt'],
        'redAmt'   => $ia['redAmt'],
        'stopN'    => $ia['stopN'],
        'redN'     => $ia['redN'],
        'openOrds' => $op['ords'],
        'openAmt'  => $op['amt'],
        'lastOrd'  => (int)$r['LASTORD'],
    );
}

// -- Tile figures -------------------------------------------------------------

$tileHistory   = count($custs);
$tileSilent    = 0;   $tileSilentRev  = 0.0;
$tileEveryQ4   = 0;   $tileEveryQ4Rev = 0.0;
$tileQ4Wtd     = 0;
$tileStopAmt   = 0.0; $tileStopItems  = 0;
$tileRedAmt    = 0.0;
$tileAtStake   = 0.0;
$tileOpenOrds  = 0;   $tileOpenAmt    = 0.0;
$tierCount = array(1=>0,2=>0,3=>0,4=>0,5=>0);
$tierStake = array(1=>0.0,2=>0.0,3=>0.0,4=>0.0,5=>0.0);
$tierStop  = array(1=>0.0,2=>0.0,3=>0.0,4=>0.0,5=>0.0);
$tierRed   = array(1=>0.0,2=>0.0,3=>0.0,4=>0.0,5=>0.0);
$tierItems = array(1=>0,2=>0,3=>0,4=>0,5=>0);
$tierOpen  = array(1=>0.0,2=>0.0,3=>0.0,4=>0.0,5=>0.0);
$byClass   = array();

foreach ($custs as $c) {
    if ($c['silent'])       { $tileSilent++;  $tileSilentRev  += $c['histRev']; }
    if ($c['q4Years'] >= 3) { $tileEveryQ4++; $tileEveryQ4Rev += $c['q4Rev']; }
    if ($c['q4Share'] >= $Q4_SHARE_TILE) { $tileQ4Wtd++; }
    $tileStopAmt   += $c['stopAmt'];
    $tileRedAmt    += $c['redAmt'];
    $tileStopItems += $c['stopN'];

    $t = $c['tier'];
    if ($t > 0) {
        $tierCount[$t]++;
        $tierStake[$t] += $c['normal'];
        $tierStop[$t]  += $c['stopAmt'];
        $tierRed[$t]   += $c['redAmt'];
        $tierItems[$t] += $c['stopN'];
        $tierOpen[$t]  += $c['openAmt'];
        $tileAtStake   += $c['normal'];
        $tileOpenOrds  += $c['openOrds'];
        $tileOpenAmt   += $c['openAmt'];
    }

    $cl = $c['cls'];
    if (!isset($byClass[$cl])) {
        $byClass[$cl] = array('desc' => $c['clsdesc'], 'custs' => 0, 'hist' => 0.0,
                              'q4' => 0.0, 'stake' => 0.0, 'stop' => 0.0,
                              't' => array(1=>0,2=>0,3=>0,4=>0,5=>0));
    }
    $byClass[$cl]['custs']++;
    $byClass[$cl]['hist'] += $c['histRev'];
    $byClass[$cl]['q4']   += $c['q4Rev'];
    $byClass[$cl]['stop'] += $c['stopAmt'];
    if ($t > 0) { $byClass[$cl]['stake'] += $c['normal']; $byClass[$cl]['t'][$t]++; }
}

$tierTotal = array_sum($tierCount);

// Top stopped SKUs - slide 6
$skuList = array();
foreach ($skuAgg as $item => $a) {
    $skuList[] = array('item' => $item, 'desc' => $a['desc'],
                       'custs' => $a['custs'], 'amt' => $a['amt'], 'qty' => $a['qty']);
}
usort($skuList, function ($a, $b) {
    if ($a['amt'] == $b['amt']) return 0;
    return ($a['amt'] < $b['amt']) ? 1 : -1;
});

$classList = array();
foreach ($byClass as $code => $a) { $a['code'] = $code; $classList[] = $a; }
usort($classList, function ($a, $b) {
    if ($a['stake'] == $b['stake']) return 0;
    return ($a['stake'] < $b['stake']) ? 1 : -1;
});

$tierLabel = array(
    1 => 'Q4 buyer, ordered nothing in ' . $curY,
    2 => 'Q4 buyer, ' . $curY . ' under half normal',
    3 => 'Q4 buyer, active - pull order forward',
    4 => 'Lapsed: bought ' . $hy[0] . '-' . $hy[2] . ', nothing ' . $curY,
    5 => $curY . ' under half normal, no Q4 skew',
);
$tierRule = array(
    1 => '35%+ of revenue in Q4, Q4 orders in 2+ years, zero in ' . $curY,
    2 => 'same Q4 habit, ' . $curY . ' under half their normal year',
    3 => 'Q4 habit and buying normally in ' . $curY,
    4 => 'bought ' . $hy[0] . '-' . $hy[2] . ', nothing in ' . $curY . ', no strong Q4 skew',
    5 => $curY . ' under half their normal year, no strong Q4 skew',
);

$totalMs = array_sum($timings);

// -- CSV export ---------------------------------------------------------------

if (isset($_GET['export'])) {
    $what = $_GET['export'];
    $stamp = date('Ymd_His');
    header('Content-Type: text/csv');

    if ($what === 'cust') {
        header('Content-Disposition: attachment; filename="Q4BuyerPattern_Customers_' . $stamp . '.csv"');
        $out = fopen('php://output', 'w');
        $hdr = array('Tier', 'Tier Rule', 'Ship-To #', 'Customer Name', 'City', 'State',
                     'Phone', 'Class', 'Class Description', 'Salesperson #', 'Salesperson',
                     'Q4 Share %', 'Q4 Years', 'Call By');
        foreach ($yrs as $y) { $hdr[] = $y . ' Sales'; }
        foreach ($yrs as $y) { $hdr[] = $y . ' Q4 Sales'; }
        $hdr = array_merge($hdr, array('Normal Year / At Stake', 'Stopped $/yr', 'Reduced $/yr',
                                       'Items Stopped', 'Items Reduced',
                                       'Open Orders', 'Open Order $', 'Last Order Date'));
        fputcsv($out, $hdr);
        foreach ($custs as $c) {
            if ($c['tier'] === 0) { continue; }
            $row = array($c['tier'], $tierRule[$c['tier']], $c['shipto'], $c['name'],
                         $c['city'], $c['state'], $c['phone'], $c['cls'], $c['clsdesc'],
                         $c['slsm'], $c['slsmname'],
                         number_format($c['q4Share'] * 100, 1, '.', ''), $c['q4Years'], $c['callBy']);
            foreach ($yrs as $y) { $row[] = number_format($c['byYear'][$y], 2, '.', ''); }
            foreach ($yrs as $y) { $row[] = number_format($c['q4ByYear'][$y], 2, '.', ''); }
            $row = array_merge($row, array(
                number_format($c['normal'],  2, '.', ''),
                number_format($c['stopAmt'], 2, '.', ''),
                number_format($c['redAmt'],  2, '.', ''),
                $c['stopN'], $c['redN'], $c['openOrds'],
                number_format($c['openAmt'], 2, '.', ''),
                q4_cymdToDate($c['lastOrd'])));
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    if ($what === 'sku') {
        header('Content-Disposition: attachment; filename="Q4BuyerPattern_StoppedSKUs_' . $stamp . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, array('SKU', 'Description', 'Customers Stopped', 'Annual $ Stopped', 'Annual Qty'));
        foreach ($skuList as $s) {
            fputcsv($out, array($s['item'], $s['desc'], $s['custs'],
                                number_format($s['amt'], 2, '.', ''),
                                number_format($s['qty'], 0, '.', '')));
        }
        fclose($out);
        exit;
    }

    if ($what === 'class') {
        header('Content-Disposition: attachment; filename="Q4BuyerPattern_Classes_' . $stamp . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, array('Class', 'Description', 'Customers', 'Q4 Share %', '$ At Stake',
                            'Stopped $/yr', 'T1', 'T2', 'T3', 'T4', 'T5'));
        foreach ($classList as $a) {
            fputcsv($out, array($a['code'], $a['desc'], $a['custs'],
                number_format(($a['hist'] > 0 ? $a['q4'] / $a['hist'] : 0) * 100, 1, '.', ''),
                number_format($a['stake'], 2, '.', ''),
                number_format($a['stop'],  2, '.', ''),
                $a['t'][1], $a['t'][2], $a['t'][3], $a['t'][4], $a['t'][5]));
        }
        fclose($out);
        exit;
    }

    header('Content-Disposition: attachment; filename="Q4BuyerPattern_Tiers_' . $stamp . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array('Tier', 'Who They Are', 'Customers', '$ At Stake',
                        'Stopped $/yr', 'Reduced $/yr', 'Items Stopped', 'Open Order $'));
    for ($t = 1; $t <= 5; $t++) {
        fputcsv($out, array('Tier ' . $t, $tierRule[$t], $tierCount[$t],
            number_format($tierStake[$t], 2, '.', ''),
            number_format($tierStop[$t],  2, '.', ''),
            number_format($tierRed[$t],   2, '.', ''),
            $tierItems[$t],
            number_format($tierOpen[$t],  2, '.', '')));
    }
    fclose($out);
    exit;
}

// -- HTML output --------------------------------------------------------------

print "\n<html><head>";
require_once ($headInclude);
require_once ($genericHead);
print "\n</head>";
require_once 'Banner.php';
require_once dirname(__FILE__) . '/../SgReportNav.php';

$revNoDiv   = isset($chk['REV_NODIV'])   ? (float)$chk['REV_NODIV']   : 0.0;
$revDiv     = isset($chk['REV_DIV'])     ? (float)$chk['REV_DIV']     : 0.0;
$revShipped = isset($chk['REV_SHIPPED']) ? (float)$chk['REV_SHIPPED'] : 0.0;
$linesAll   = isset($chk['LINES_ALL'])   ? (int)$chk['LINES_ALL']     : 0;
$linesFact  = isset($chk['LINES_FACTORED']) ? (int)$chk['LINES_FACTORED'] : 0;
$linesZero  = isset($chk['LINES_ZEROUF'])   ? (int)$chk['LINES_ZEROUF']   : 0;
$factPct    = ($linesAll > 0) ? ($linesFact / $linesAll) : 0.0;
$divDelta   = ($revDiv > 0) ? (($revNoDiv - $revDiv) / $revDiv) : 0.0;
?>
<table <?php echo $baseTable; ?>>
<tr valign="top">
<td class="content">

<style>
table[summary="banner"] { display:none !important; }
body { box-sizing:border-box !important; }
body > table { width:100% !important; max-width:none !important; table-layout:auto !important; }
td.content { width:calc(100vw - 155px) !important; max-width:none !important; box-sizing:border-box !important; }
.q4-grid { width:100% !important; min-width:100% !important; border-collapse:collapse; }
.q4-grid thead th { background-color:#374151 !important; color:#fff !important;
                    font-weight:bold !important; cursor:pointer; user-select:none;
                    white-space:nowrap; padding:5px 7px; font-size:12px; text-align:left; }
.q4-grid thead th:hover { opacity:0.85; }
.q4-grid thead th.q4-asc::after  { content:' \25B2'; font-size:9px; }
.q4-grid thead th.q4-desc::after { content:' \25BC'; font-size:9px; }
.q4-grid tbody tr:nth-child(odd)  { background:#F7F7F7; }
.q4-grid tbody tr:nth-child(even) { background:#FFFFFF; }
.q4-grid tbody tr:hover           { background:#EFF6FF !important; }
.q4-grid tbody td { color:#111827 !important; padding:4px 7px; font-size:12px;
                    border-bottom:1px solid #E5E7EB; }
.q4-grid tbody td a { color:#2563EB !important; text-decoration:none !important;
                      font-weight:bold !important; }
.q4-grid tbody td a:hover { text-decoration:underline !important; }
.q4-grid tfoot td { background:#E5E7EB; font-weight:bold; padding:5px 7px; font-size:12px;
                    border-top:2px solid #9CA3AF; }
.q4-r { text-align:right; }
.q4-sec { margin:18px 0 6px; font-size:15px; font-weight:bold; color:#111827;
          border-left:4px solid #2563EB; padding-left:8px; }
.q4-sec span { font-weight:normal; font-size:12px; color:#6B7280; margin-left:8px; }
.q4-tiles { display:flex; flex-wrap:wrap; gap:1px; background:#D1D5DB;
            border:1px solid #D1D5DB; margin:8px 0 4px; }
.q4-tile { background:#fff; flex:1 1 190px; padding:12px 14px; min-width:190px; }
.q4-tile a { text-decoration:none !important; color:inherit !important; display:block; }
.q4-tile:hover { background:#EFF6FF; }
.q4-tk { font-size:10.5px; letter-spacing:0.09em; text-transform:uppercase;
         color:#6B7280; font-weight:bold; margin-bottom:5px; }
.q4-tv { font-size:27px; font-weight:bold; color:#111827; line-height:1.05; }
.q4-tn { font-size:11px; color:#4B5563; margin-top:5px; line-height:1.35; }
.q4-badge { display:inline-block; padding:1px 6px; border-radius:3px; font-size:11px;
            font-weight:bold; color:#fff; }
.q4-t1 { background:#CC1F20; } .q4-t2 { background:#EA580C; } .q4-t3 { background:#1DA032; }
.q4-t4 { background:#7B1FA2; } .q4-t5 { background:#0891B2; }
.q4-warn { background:#FEF3C7; border:1px solid #F0C060; color:#78350F;
           padding:8px 12px; margin:8px 0; font-size:12px; border-radius:3px; }
.q4-warn b { color:#78350F; }
.q4-chk { background:#EFF6FF; border:1px solid #93C5FD; color:#1E3A8A;
          padding:8px 12px; margin:8px 0; font-size:12px; border-radius:3px; }
.q4-chk table { border-collapse:collapse; margin-top:6px; font-size:12px; }
.q4-chk td { padding:2px 12px 2px 0; }
.q4-err { color:#CC1F20; font-weight:bold; padding:8px; font-size:12px; }
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
              text-shadow:0 1px 3px rgba(0,0,0,0.4);">
    Q4 Buyer Pattern
  </h1>
  <a href="<?php echo q4_h($_sgnHome . '/Welcome.php?baseVar=' . rawurlencode($_sgnBv) . '&eID=' . rawurlencode($_sgnEid) . '&portal=9999999999'); ?>"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#06B6D4;
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid #0891B2;white-space:nowrap;display:inline-block;">&#8592; Back to EIP</a>
  <a href="https://screen-graphics.com/"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#CC1F20;
            color:#fff !important;text-decoration:none !important;border-radius:4px;
            border:1px solid #8b1010;white-space:nowrap;display:inline-block;">Logout</a>
</div>

<?php if (!empty($sqlErr)): ?>
  <?php foreach ($sqlErr as $e): ?>
  <p class="q4-err"><?php echo q4_h('SQL Error - ' . $e); ?></p>
  <?php endforeach; ?>
<?php endif; ?>

<!-- Status + action bar -->
<div style="display:flex;align-items:stretch;border-bottom:2px solid #D1D5DB;">
  <div style="flex:1;display:flex;flex-direction:column;">
    <div style="background:#2563EB;border-bottom:1px solid #1d4ed8;padding:4px 14px;
                display:flex;align-items:center;gap:14px;font-size:11px;color:#fff;flex:1;
                flex-wrap:wrap;">
      <span style="font-weight:bold;">Live SGHDSDATA</span>
      <span>OEORDH + OEORHD &middot; order date <?php echo $hy[0]; ?>-01-01 to
            <?php echo q4_cymdToDate($todayCymd); ?></span>
      <span style="background:#fff;border-radius:12px;padding:2px 10px;color:#2563EB !important;
                   font-weight:700;">Revenue: <?php echo $Q4_REV_MODE === 'deck'
            ? 'DHQORD x DHSLPR (deck)' : 'DHQORD x DHSLPR / DHORUF'; ?></span>
      <span style="background:#fff;border-radius:12px;padding:2px 10px;color:#2563EB !important;
                   font-weight:700;"><?php echo q4_int($totalMs); ?> ms
            (<?php echo count($timings); ?> queries)</span>
      <span id="q4-asof" style="background:#fff3cd;border:1px solid #f0c060;border-radius:12px;
                   padding:2px 10px;color:#856404 !important;font-weight:700;"></span>
    </div>
    <div style="display:flex;align-items:center;gap:14px;padding:6px 10px;
                background:#F7F7F7;font-size:12px;flex:1;flex-wrap:wrap;">
      <b><?php echo q4_int($tileHistory); ?></b> ship-tos with history
      <span style="color:#9CA3AF;">|</span>
      <b><?php echo q4_int($tierTotal); ?></b> on the call lists
      <span style="color:#9CA3AF;">|</span>
      <b><?php echo q4_money($tileAtStake); ?></b> at stake
      <span style="color:#9CA3AF;">|</span>
      <b><?php echo q4_money($tileStopAmt); ?></b>/yr stopped
      <span style="margin-left:auto;color:#6B7280;">
        Levels 3 and 4 (customer list, one-customer product detail) land in the next build.
      </span>
    </div>
  </div>
  <div style="display:flex;flex-direction:column;align-items:stretch;justify-content:center;
              gap:4px;padding:6px 10px;background:#F7F7F7;border-left:2px solid #D1D5DB;">
    <button onclick="location.reload();"
            style="font-size:12px;padding:3px 14px;cursor:pointer;border:1px solid #4a0f6e;
                   border-radius:3px;background:#7B1FA2;color:#fff;font-weight:bold;
                   white-space:nowrap;text-align:center;">&#x21BB; Refresh</button>
    <a href="?export=cust"
       style="background:#1DA032;color:#fff !important;padding:3px 14px;border-radius:3px;
              font-size:12px;font-weight:bold;text-decoration:none !important;white-space:nowrap;
              text-align:center;display:block;">&#8595; Export Call List</a>
  </div>
</div>

<?php if (!empty($Q4_PENDING)): ?>
<div class="q4-warn">
  <b>RULES PENDING - do not quote these numbers as final.</b>
  <ul style="margin:5px 0 0 18px;padding:0;">
    <?php foreach ($Q4_PENDING as $p): ?>
    <li><?php echo q4_h($p); ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<!-- Rules check: settles the DHORUF question against live data -->
<div class="q4-chk">
  <b>Rules check - what the DHORUF divisor is worth</b>
  <table>
    <tr>
      <td>Invoiced lines in window</td>
      <td class="q4-r"><b><?php echo q4_int($linesAll); ?></b></td>
      <td>with DHORUF not 0 or 1</td>
      <td class="q4-r"><b><?php echo q4_int($linesFact); ?></b>
          (<?php echo q4_pct($factPct, 1); ?>)</td>
      <td>with DHORUF = 0</td>
      <td class="q4-r"><b><?php echo q4_int($linesZero); ?></b></td>
    </tr>
    <tr>
      <td>Ordered qty, divisor applied<br><span style="color:#6B7280;">this page</span></td>
      <td class="q4-r"><b><?php echo q4_money($revDiv); ?></b></td>
      <td>Ordered qty, no divisor<br><span style="color:#6B7280;">the 17 Aug deck</span></td>
      <td class="q4-r"><b><?php echo q4_money($revNoDiv); ?></b></td>
      <td>Deck overstates by</td>
      <td class="q4-r"><b style="color:<?php echo abs($divDelta) > 0.01 ? '#CC1F20' : '#1DA032'; ?>;">
          <?php echo q4_pct($divDelta, 1); ?></b></td>
    </tr>
    <tr>
      <td>Shipped qty, divisor applied<br><span style="color:#6B7280;">SalesDashboard basis</span></td>
      <td class="q4-r"><b><?php echo q4_money($revShipped); ?></b></td>
      <td colspan="4" style="color:#6B7280;">
        If "deck overstates by" is near 0%, the deck's dollars stand as published. If it is
        material, every figure in the deck and the tier workbooks needs restating and this
        page is the correct basis.
      </td>
    </tr>
  </table>
</div>

<!-- LEVEL 1 -->
<div class="q4-sec">Level 1 &middot; The headline<span>Every figure queried live at page load</span></div>
<div class="q4-tiles">
  <div class="q4-tile">
    <a href="#q4-classes">
      <div class="q4-tk">Ship-tos with history</div>
      <div class="q4-tv"><?php echo q4_int($tileHistory); ?></div>
      <div class="q4-tn">Bought at least once <?php echo $hy[0]; ?>-<?php echo $hy[2]; ?>.</div>
    </a>
  </div>
  <div class="q4-tile">
    <a href="#q4-tiers">
      <div class="q4-tk">Silent so far in <?php echo $curY; ?></div>
      <div class="q4-tv"><?php echo q4_int($tileSilent); ?></div>
      <div class="q4-tn">Nothing at all in <?php echo $curY; ?>. They carried
          <?php echo q4_money($tileSilentRev); ?>.</div>
    </a>
  </div>
  <div class="q4-tile">
    <a href="#q4-tiers">
      <div class="q4-tk">Order every single Q4</div>
      <div class="q4-tv"><?php echo q4_int($tileEveryQ4); ?></div>
      <div class="q4-tn">Q4 orders in <?php echo $hy[0]; ?> and <?php echo $hy[1]; ?> and
          <?php echo $hy[2]; ?> - <?php echo q4_money($tileEveryQ4Rev); ?> of Q4 business.</div>
    </a>
  </div>
  <div class="q4-tile">
    <a href="#q4-tiers">
      <div class="q4-tk">Q4-weighted customers</div>
      <div class="q4-tv"><?php echo q4_int($tileQ4Wtd); ?></div>
      <div class="q4-tn">Put <?php echo q4_pct($Q4_SHARE_TILE); ?> or more of their revenue
          into Q4.</div>
    </a>
  </div>
  <div class="q4-tile">
    <a href="#q4-skus">
      <div class="q4-tk">Products stopped</div>
      <div class="q4-tv"><?php echo q4_moneyK($tileStopAmt); ?>/yr</div>
      <div class="q4-tn"><?php echo q4_int($tileStopItems); ?> repeat items dropped, plus
          <?php echo q4_moneyK($tileRedAmt); ?>/yr reduced.</div>
    </a>
  </div>
  <div class="q4-tile">
    <a href="#q4-tiers">
      <div class="q4-tk">Total at stake</div>
      <div class="q4-tv"><?php echo q4_moneyK($tileAtStake); ?></div>
      <div class="q4-tn"><?php echo q4_int($tierTotal); ?> customers on the lists. One
          recovered normal year each - the size of the prize, not a forecast.</div>
    </a>
  </div>
</div>

<!-- LEVEL 2a -->
<div class="q4-sec" id="q4-tiers">Level 2 &middot; The call lists
  <span>Tiers 1-3 are the Q4-pattern customers; tiers 4-5 are the wider recovery list</span></div>
<div style="overflow-x:auto;">
<table class="q4-grid" id="q4-tiergrid">
  <thead>
    <tr>
      <th>Tier</th><th>Who they are</th>
      <th class="q4-r">Customers</th><th class="q4-r">$ at stake</th>
      <th class="q4-r">Stopped $/yr</th><th class="q4-r">Reduced $/yr</th>
      <th class="q4-r">Items stopped</th><th class="q4-r">Open order $</th>
    </tr>
  </thead>
  <tbody>
<?php for ($t = 1; $t <= 5; $t++): ?>
    <tr>
      <td><span class="q4-badge q4-t<?php echo $t; ?>">Tier <?php echo $t; ?></span></td>
      <td><?php echo q4_h($tierRule[$t]); ?></td>
      <td class="q4-r"><?php echo q4_int($tierCount[$t]); ?></td>
      <td class="q4-r"><?php echo q4_money($tierStake[$t]); ?></td>
      <td class="q4-r"><?php echo q4_money($tierStop[$t]); ?></td>
      <td class="q4-r"><?php echo ($t === 1 || $t === 4) ? '&mdash;' : q4_money($tierRed[$t]); ?></td>
      <td class="q4-r"><?php echo q4_int($tierItems[$t]); ?></td>
      <td class="q4-r"><?php echo $tierOpen[$t] > 0 ? q4_money($tierOpen[$t]) : '&mdash;'; ?></td>
    </tr>
<?php endfor; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="2">TOTAL</td>
      <td class="q4-r"><?php echo q4_int($tierTotal); ?></td>
      <td class="q4-r"><?php echo q4_money($tileAtStake); ?></td>
      <td class="q4-r"><?php echo q4_money(array_sum($tierStop)); ?></td>
      <td class="q4-r"><?php echo q4_money(array_sum($tierRed)); ?></td>
      <td class="q4-r"><?php echo q4_int(array_sum($tierItems)); ?></td>
      <td class="q4-r"><?php echo q4_money($tileOpenAmt); ?></td>
    </tr>
  </tfoot>
</table>
</div>
<div style="font-size:11px;color:#6B7280;padding:5px 2px;">
  Tiers 1 and 4 have no "reduced" figure - those customers bought nothing at all in
  <?php echo $curY; ?>. Open order $ is shown for information and never suppresses a call:
  <?php echo q4_int($tileOpenOrds); ?> open orders worth <?php echo q4_money($tileOpenAmt); ?>
  sit against customers on these lists.
  <a href="?export=tier" style="color:#1DA032;font-weight:bold;">Export tiers</a>
</div>

<!-- LEVEL 2b -->
<div class="q4-sec" id="q4-classes">Level 2 &middot; Opportunity by customer class
  <span>Click a column header to sort</span></div>
<div style="overflow-x:auto;">
<table class="q4-grid" id="q4-clsgrid">
  <thead>
    <tr>
      <th>Class</th><th>Customer class</th>
      <th class="q4-r">Customers</th><th class="q4-r">Q4 share</th>
      <th class="q4-r">$ at stake</th><th class="q4-r">Stopped $/yr</th>
      <th class="q4-r">T1</th><th class="q4-r">T2</th><th class="q4-r">T3</th>
      <th class="q4-r">T4</th><th class="q4-r">T5</th>
    </tr>
  </thead>
  <tbody>
<?php foreach ($classList as $a):
    if ($a['stake'] <= 0 && $a['stop'] <= 0) { continue; }
    $shr = ($a['hist'] > 0) ? $a['q4'] / $a['hist'] : 0.0;
?>
    <tr>
      <td><b><?php echo q4_h($a['code']); ?></b></td>
      <td><?php echo q4_h($a['desc']); ?></td>
      <td class="q4-r"><?php echo q4_int($a['custs']); ?></td>
      <td class="q4-r" data-val="<?php echo number_format($shr, 4, '.', ''); ?>">
          <?php echo q4_pct($shr); ?></td>
      <td class="q4-r"><?php echo q4_money($a['stake']); ?></td>
      <td class="q4-r"><?php echo q4_money($a['stop']); ?></td>
      <td class="q4-r"><?php echo $a['t'][1] ? q4_int($a['t'][1]) : '&mdash;'; ?></td>
      <td class="q4-r"><?php echo $a['t'][2] ? q4_int($a['t'][2]) : '&mdash;'; ?></td>
      <td class="q4-r"><?php echo $a['t'][3] ? q4_int($a['t'][3]) : '&mdash;'; ?></td>
      <td class="q4-r"><?php echo $a['t'][4] ? q4_int($a['t'][4]) : '&mdash;'; ?></td>
      <td class="q4-r"><?php echo $a['t'][5] ? q4_int($a['t'][5]) : '&mdash;'; ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<div style="font-size:11px;color:#6B7280;padding:5px 2px;">
  Q4 share = that class's Q4 revenue as a percent of its <?php echo $hy[0]; ?>-<?php echo $hy[2]; ?>
  revenue. T1-T5 are counts of customers in that tier.
  <a href="?export=class" style="color:#1DA032;font-weight:bold;">Export classes</a>
</div>

<!-- LEVEL 2c -->
<div class="q4-sec" id="q4-skus">Level 2 &middot; What they stopped ordering
  <span>Top 25 of <?php echo q4_int(count($skuList)); ?> stopped repeat SKUs</span></div>
<div style="overflow-x:auto;">
<table class="q4-grid" id="q4-skugrid">
  <thead>
    <tr>
      <th>SKU</th><th>Description</th>
      <th class="q4-r">Customers stopped</th>
      <th class="q4-r">Annual $ stopped</th>
      <th class="q4-r">Annual qty</th>
    </tr>
  </thead>
  <tbody>
<?php $shown = 0; foreach ($skuList as $s): if ($shown++ >= 25) break; ?>
    <tr>
      <td><b><?php echo q4_h($s['item']); ?></b></td>
      <td><?php echo q4_h($s['desc']); ?></td>
      <td class="q4-r"><?php echo q4_int($s['custs']); ?></td>
      <td class="q4-r"><?php echo q4_money($s['amt']); ?></td>
      <td class="q4-r"><?php echo q4_int($s['qty']); ?></td>
    </tr>
<?php endforeach; ?>
<?php if (empty($skuList)): ?>
    <tr><td colspan="5" style="text-align:center;padding:20px;">No stopped repeat items found.</td></tr>
<?php endif; ?>
  </tbody>
</table>
</div>
<div style="font-size:11px;color:#6B7280;padding:5px 2px;">
  A stopped item is a genuine repeat purchase - bought in 2+ years or across 3+ separate
  orders - with zero purchases in <?php echo $curY; ?>. One-time custom prints are excluded.
  A high customer count is a slipping product line, not one lost account.
  <a href="?export=sku" style="color:#1DA032;font-weight:bold;">Export all
  <?php echo q4_int(count($skuList)); ?> SKUs</a>
</div>

<!-- Basis note -->
<div style="margin:16px 0 8px;padding:10px 12px;background:#F9FAFB;border:1px solid #E5E7EB;
            font-size:11px;color:#4B5563;line-height:1.6;border-radius:3px;">
  <b style="color:#111827;">Rules baked in, so every screen agrees</b><br>
  Revenue = ordered qty x selling price
  <?php echo $Q4_REV_MODE === 'deck' ? '(no unit-factor divisor - deck mode)'
                                     : 'divided by the DHORUF unit factor'; ?>;
  overages are not counted &middot;
  customer grain is the ship-to (OESHTO), never the bill-to &middot;
  invoiced history only, OEORDH where DHSEQ# &lt;&gt; 0, OEORDT is never unioned in &middot;
  excluded order types F/N/P/Q/S/U/V &middot;
  excluded items AD0166, LTL*, *SAMP* &middot;
  excluded <?php echo count(explode(',', $Q4_BAD_BILLTO)); ?> internal bill-to accounts and
  <?php echo count(explode(',', $Q4_BAD_ORDERS)); ?> named COs &middot;
  <?php echo $curY; ?> is a partial year compared against full prior years with no proration,
  so a customer who only ever buys in Q4 reads as silent - that is the intended signal &middot;
  order history lags roughly two weeks because lines reach OEORDH as they ship, which is why
  open orders are shown as their own column.
</div>

</td>
</tr>
</table>

<script type="text/javascript">
// As-of stamp in the viewer's own timezone, never a server assumption
(function () {
    var el = document.getElementById('q4-asof');
    if (!el) return;
    var now = new Date();
    var tz  = now.toLocaleTimeString('en-US', {timeZoneName:'short'}).split(' ').pop();
    el.textContent = 'Loaded ' + now.toLocaleDateString('en-US',
        {weekday:'short', month:'short', day:'numeric', year:'numeric'}) +
        ' ' + now.toLocaleTimeString('en-US',
        {hour:'numeric', minute:'2-digit', second:'2-digit'}) + ' ' + tz;
}());

// Column sorting, shared by every grid on the page
(function () {
    var grids = document.querySelectorAll('table.q4-grid');
    for (var g = 0; g < grids.length; g++) { wire(grids[g]); }

    function cellVal(td) {
        if (!td) return null;
        if (td.hasAttribute('data-val')) { return parseFloat(td.getAttribute('data-val')) || 0; }
        var t = td.textContent.replace(/[,$%]/g, '').trim();
        if (t === '' || t === '—') return null;
        var n = parseFloat(t);
        return isNaN(n) ? t.toLowerCase() : n;
    }

    function wire(tbl) {
        var tbody = tbl.querySelector('tbody');
        var ths   = tbl.querySelectorAll('thead th');
        if (!tbody || !ths.length) return;
        var state = { col: -1, dir: 1 };

        function sortBy(col) {
            state.dir = (state.col === col) ? -state.dir : 1;
            state.col = col;
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            rows.sort(function (a, b) {
                var va = cellVal(a.cells[col]), vb = cellVal(b.cells[col]);
                if (va === null && vb === null) return 0;
                if (va === null) return 1;
                if (vb === null) return -1;
                if (va < vb) return -state.dir;
                if (va > vb) return  state.dir;
                return 0;
            });
            rows.forEach(function (r) { tbody.appendChild(r); });
            for (var i = 0; i < ths.length; i++) {
                ths[i].className = ths[i].className.replace(/\s*q4-(asc|desc)/g, '');
            }
            ths[col].className += (state.dir === 1 ? ' q4-asc' : ' q4-desc');
        }

        for (var i = 0; i < ths.length; i++) {
            (function (col) {
                ths[col].addEventListener('click', function () { sortBy(col); });
            }(i));
        }
    }
}());
</script>

</body>
</html>
