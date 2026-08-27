<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

// -----------------------------------------------------------------------------
//  Buyer Pattern - live next-quarter buying-pattern inquiry
//
//  Purpose: find customers who habitually order in the NEXT quarter so reps can
//  call them now and pull those orders into the CURRENT quarter, plus the wider
//  recovery list of lapsed and declining accounts.
//
//  The quarter is not hardcoded. The current quarter comes from the IBM i's own
//  CURRENT DATE; the target is the quarter after it, measured against that same
//  quarter in each of the three prior years. The page stays correct all year.
//
//  Revenue      CASE WHEN DHSLPR = 0 OR DHORUF = 0 THEN 0
//                    ELSE DHQORD * DHSLPR / DHORUF END
//               Ordered qty, per Bill 2026-08-27. The DHORUF = 0 arm is added
//               to the rule as given: without it a zero unit factor divides by
//               zero and the query fails. On current data both arms are no-ops
//               (DHORUF is 0 or 1 throughout, and the divisor changes the
//               yearly totals by $0.00).
//
//  CRITICAL     DHQORD restates the FULL line quantity on every partial-shipment
//               row in OEORDH. Order 341181 line 44 carries qty 85 on nine rows.
//               Summing row by row multiplies each line by its shipment count -
//               that inflated 2025 from $13.6M to $18.8M. Every figure on this
//               page is therefore computed from a LINE-GRAIN derived table that
//               takes each (order, line) once. Never sum DHQORD across raw rows.
//
//  Grain        ship-to (OESHTO), never bill-to.
//  Anchor       ORDER date (OEBDTE) - the goal is moving WHEN they order.
//  Invoiced     OEORDH where DHSEQ# <> 0. OEORDT is never unioned in.
//  Exclusions   order types P/Q/S/U/V; items AD0166, LTL*, *SAMP*;
//               5 internal bill-to accounts; 4 named COs.
//  Open orders  shown as their own column, never used to suppress a call.
//
//  Drill path   L1 tiles -> L2 tier / class / SKU -> L3 customer list
//               -> L4 one customer -> L5 the raw order lines behind the money.
//
//  No date_default_timezone_set: display times come from the browser.
// -----------------------------------------------------------------------------

$page_title = 'Buyer Pattern';
$eiBase     = 'https://portal.screen-graphics.com:5601';

// Tier thresholds
$BP_SHARE_STRONG = 0.35;   // share of revenue landing in the target quarter
$BP_YEARS_STRONG = 2;      // target-quarter orders in at least this many years
$BP_DOWN_FACTOR  = 0.50;   // "under half their normal year"
$BP_SHARE_TILE   = 0.25;   // tile threshold for "quarter-weighted"
$BP_CALL_LEAD    = 45;     // days ahead of their own average kickoff

// What counts as one "normal year" of a customer, and of one item.
//   'window' - divide their history by the full 3-year window. A customer who
//              bought in only one of three years gets a third of that year.
//              Conservative, and the basis the 17 Aug deck was built on.
//   'active' - divide by the years they actually bought in. Right for a genuine
//              annual customer who lapsed, but it treats a one-time buyer as an
//              annual one and roughly doubles the lapsed tiers.
// Totals at stake: 'window' $6.47M, 'active' $10.03M (measured 2026-08-27).
// Override for a session with ?basis=active
$BP_NORMAL_BASIS = (isset($_GET['basis']) && $_GET['basis'] === 'active') ? 'active' : 'window';

$BP_BAD_ORDTY  = "'P','Q','S','U','V'";
$BP_BAD_BILLTO = '9999999,9999800,9999201,9999200,9999100';
$BP_BAD_ORDERS = '356066,347305,356706';

// -- Request routing ----------------------------------------------------------

$view    = isset($_GET['view']) ? strtolower(trim($_GET['view'])) : 'tiles';
if (!in_array($view, array('tiles', 'cust', 'detail', 'lines', 'cards', 'activity'), true)) { $view = 'tiles'; }
$fLimit  = isset($_GET['limit']) ? max(1, min(60, (int)$_GET['limit'])) : 12;
// Month drill from the seasonal chart: the customers whose target-quarter order
// historically lands in this month. Validated against the target quarter below.
$fMo     = isset($_GET['mo']) ? (int)$_GET['mo'] : 0;
// Quarter drill from the seasonal chart: one specific year and quarter.
$fPy     = isset($_GET['py']) ? (int)$_GET['py'] : 0;
$fPq     = isset($_GET['pq']) ? (int)$_GET['pq'] : 0;
$fTier   = isset($_GET['tier'])   ? (int)$_GET['tier'] : 0;
$fCls    = isset($_GET['cls'])    ? strtoupper(trim($_GET['cls'])) : '';
$fItem   = isset($_GET['item'])   ? trim($_GET['item']) : '';
$fShipto = isset($_GET['shipto']) ? preg_replace('/[^0-9]/', '', $_GET['shipto']) : '';
$fStatus = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : '';

// Levels 4 and 5 concern exactly one ship-to, so there is no reason to grind the
// whole customer base through the engine. Scoping the line-grain table to that
// one customer takes these views from seconds to milliseconds. A customer's tier
// depends only on their own history, so nothing is lost by narrowing.
$isSingle = (($view === 'detail' || $view === 'lines') && $fShipto !== '');
$BP_SCOPE = $isSingle ? "\n      AND h.OESHTO = " . (int)$fShipto : '';

// -- Helpers ------------------------------------------------------------------

function bp_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function bp_money($v, $dec = 0) {
    return '$' . number_format((float)$v, $dec);
}
function bp_moneyK($v) {
    $v = (float)$v;
    if (abs($v) >= 1000000) return '$' . number_format($v / 1000000, 2) . 'M';
    if (abs($v) >= 1000)    return '$' . number_format($v / 1000, 1) . 'K';
    return '$' . number_format($v, 0);
}
function bp_int($v)  { return number_format((int)$v); }
function bp_qty($v)  { return number_format((float)$v, 0); }
function bp_pct($v, $dec = 0) { return number_format((float)$v * 100, $dec) . '%'; }
function bp_cymd($y, $m, $d)  { return ($y - 1900) * 10000 + $m * 100 + $d; }

// Split a CYMD integer into its parts, or null if it is not a usable date
function bp_cymdParts($v) {
    $v = (int)$v;
    if ($v <= 0) return null;
    $c  = intval($v / 1000000);
    $yy = intval(($v % 1000000) / 10000);
    $mm = intval(($v % 10000) / 100);
    $dd = $v % 100;
    if ($mm < 1 || $mm > 12 || $dd < 1 || $dd > 31) return null;
    return array(1900 + $c * 100 + $yy, $mm, $dd);
}

// Every date shown on screen is MM-DD-YYYY. Every date written to a CSV is
// YYYY-MM-DD, which Excel sorts correctly as text instead of reinterpreting it.
function bp_cymdToDate($v) {
    $p = bp_cymdParts($v);
    return $p === null ? '' : sprintf('%02d-%02d-%04d', $p[1], $p[2], $p[0]);
}

function bp_cymdIso($v) {
    $p = bp_cymdParts($v);
    return $p === null ? '' : sprintf('%04d-%02d-%02d', $p[0], $p[1], $p[2]);
}
function bp_sqlStr($s) {
    return str_replace("'", "''", (string)$s);
}

// Call-by dates are held internally as Y-m-d so a plain string compare answers
// "is this already past". This formats one for display only - never store it.
// Collapse a note to one line and clip it for scanning views. The untouched
// text goes in the cell's title, and Level 4 always shows the note in full.
function bp_clip($s, $max = 150) {
    $s = trim(preg_replace('/\s+/u', ' ', (string)$s));
    if (function_exists('mb_strlen')) {
        if (mb_strlen($s) <= $max) return $s;
        return rtrim(mb_substr($s, 0, $max - 1)) . "\xE2\x80\xA6";
    }
    if (strlen($s) <= $max) return $s;
    return rtrim(substr($s, 0, $max - 1)) . '...';
}

function bp_mdy($ymd) {
    $ymd = trim((string)$ymd);
    if ($ymd === '' || strlen($ymd) < 10) return '';
    return substr($ymd, 5, 2) . '-' . substr($ymd, 8, 2) . '-' . substr($ymd, 0, 4);
}

$conn    = $i5Connect->getConnection();
$sqlErr  = array();
$timings = array();

function bp_fetchAll($conn, $sql, $label, &$sqlErr, &$timings) {
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

// -- Date frame and the target quarter, from the IBM i itself -----------------

$todayRow = bp_fetchAll($conn,
    "SELECT YEAR(CURRENT DATE) AS Y, MONTH(CURRENT DATE) AS M, DAY(CURRENT DATE) AS D
       FROM SYSIBM.SYSDUMMY1", 'currentDate', $sqlErr, $timings);
$curY = !empty($todayRow) ? (int)$todayRow[0]['Y'] : 2026;
$curM = !empty($todayRow) ? (int)$todayRow[0]['M'] : 1;
$curD = !empty($todayRow) ? (int)$todayRow[0]['D'] : 1;
$todayCymd = bp_cymd($curY, $curM, $curD);

$curQ  = intval(($curM - 1) / 3) + 1;          // quarter we are in now
$tgtQ  = ($curQ % 4) + 1;                      // the quarter we want orders pulled FROM
$tgtQY = ($tgtQ === 1) ? $curY + 1 : $curY;    // calendar year that target quarter falls in
$tgtQM1 = ($tgtQ - 1) * 3 + 1;                 // first month of the target quarter
$tgtQM3 = $tgtQM1 + 2;                         // last month of the target quarter
$tgtQLbl = 'Q' . $tgtQ;
$curQLbl = 'Q' . $curQ;

$hy       = array($curY - 3, $curY - 2, $curY - 1);   // the three history years
$yrs      = array($hy[0], $hy[1], $hy[2], $curY);
$winStart = bp_cymd($hy[0], 1, 1);

$lastDay = array(1=>31,2=>29,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
$yb = array();
foreach ($yrs as $y) {
    $yb[$y] = array(
        'start' => bp_cymd($y, 1, 1),
        'end'   => bp_cymd($y, 12, 31),
        'tqs'   => bp_cymd($y, $tgtQM1, 1),
        'tqe'   => bp_cymd($y, $tgtQM3, $lastDay[$tgtQM3]),
    );
}

// -- Who is looking, and which accounts they may see -------------------------
//
// Driven entirely by PROITRG.UDCDETAIL, system BUYPATTERN, code SALESPRSN:
//   key = EIP profile, UDCDESC1..15 = the sales numbers they may see
//   value '*ALL'      = no filter at all
//   key   ALL_CAN_SEE = sales numbers everyone may see (98, 99)
// Adding or removing coverage is a UDC row, never a code change. Every
// comparison is upper-cased on both sides, so case never matters.
// PROITRG is a single library - not split per environment - so Test and Live
// read the same access rows.

require_once dirname(__FILE__) . '/../SgRequireAccess.php';   // defines sgAccessUser()

$bpUser = function_exists('sgAccessUser') ? sgAccessUser() : '';
if ($bpUser === '') {
    foreach (array('userProfile', 'eUser', 'i5UserProfile') as $v) {
        if (isset($GLOBALS[$v]) && trim((string)$GLOBALS[$v]) !== '') {
            $bpUser = strtoupper(trim((string)$GLOBALS[$v]));
            break;
        }
    }
}
$bpUser = strtoupper(trim($bpUser));

$udcCols = array();
for ($i = 1; $i <= 15; $i++) { $udcCols[] = "UPPER(TRIM(UDCDESC$i)) AS V$i"; }
$sqlUdc = "
    SELECT UPPER(TRIM(UDCKEY)) AS K, " . implode(', ', $udcCols) . "
    FROM PROITRG.UDCDETAIL
    WHERE UPPER(TRIM(UDCSYSTEMD)) = 'BUYPATTERN'
      AND UPPER(TRIM(UDCCODED))   = 'SALESPRSN'
";
$udcRows = bp_fetchAll($conn, $sqlUdc, 'accessUdc', $sqlErr, $timings);

$bpSeeAll    = false;
$bpAllowed   = array();
$bpHasRow    = false;
foreach ($udcRows as $r) {
    $k = strtoupper(trim((string)$r['K']));
    if ($k !== 'ALL_CAN_SEE' && $k !== $bpUser) { continue; }
    if ($k === $bpUser) { $bpHasRow = true; }
    for ($i = 1; $i <= 15; $i++) {
        $v = strtoupper(trim((string)$r['V' . $i]));
        if ($v === '') { continue; }
        if ($v === '*ALL') { if ($k === $bpUser) { $bpSeeAll = true; } continue; }
        if (ctype_digit($v)) { $bpAllowed[(int)$v] = true; }
    }
}
$bpAllowed = array_keys($bpAllowed);
sort($bpAllowed);

// Applied inside the line-grain table so it reaches every level and every
// export automatically. An empty list can only happen if ALL_CAN_SEE is
// missing too, in which case show nothing rather than everything.
$BP_ACCESS = '';
if (!$bpSeeAll) {
    $BP_ACCESS = !empty($bpAllowed)
               ? "\n      AND c.CMSLSM IN (" . implode(',', $bpAllowed) . ')'
               : "\n      AND 1 = 0";
}

// -- CMac.ws directory search, driven by customer class ----------------------
//
// PROITRG.UDCDETAIL, system BPCOSEARCH, code CLASS:
//   UDCKEY        the human label, e.g. 'Garbage Search'
//   UDCDESC1      the full search term sent as q2
//   UDCDESC2..15  the customer classes it applies to, comma- or space-separated
// A class with no row gets plain cmac.ws rather than a wrong-category search.
// Adding a category is a UDC row, never a code change.

$bpCoSearch = array();   // class code => search term
if ($view === 'detail') {
    $coSel = array('TRIM(UDCKEY) AS K');
    for ($i = 1; $i <= 15; $i++) { $coSel[] = "TRIM(UDCDESC$i) AS D$i"; }
    $sqlCo = "
        SELECT " . implode(', ', $coSel) . "
        FROM PROITRG.UDCDETAIL
        WHERE UPPER(TRIM(UDCSYSTEMD)) = 'BPCOSEARCH'
          AND UPPER(TRIM(UDCCODED))   = 'CLASS'
    ";
    foreach (bp_fetchAll($conn, $sqlCo, 'coSearchUdc', $sqlErr, $timings) as $r) {
        $term = trim((string)$r['D1']);
        if ($term === '') { $term = trim((string)$r['K']); }   // fall back to the label
        if ($term === '') { continue; }
        // Classes may be one per field, comma or space separated, or a mix
        for ($i = 2; $i <= 15; $i++) {
            $raw = trim((string)$r['D' . $i]);
            if ($raw === '') { continue; }
            foreach (preg_split('/[\s,;]+/', $raw) as $cls) {
                $cls = strtoupper(trim($cls));
                if ($cls !== '') { $bpCoSearch[$cls] = $term; }
            }
        }
    }
}

// -- Contact log: append-only, one row per call or email ---------------------
//
// Storage: <lib>.BPCALLLOG, SG5OBJ on Test and SGOBJ on Live.
// The log is INSERT-only by design. There is no UPDATE or DELETE path anywhere
// in this page, which is what makes CLTSTP and CLUSER trustworthy: the stamp is
// DB2's own CURRENT TIMESTAMP and the profile comes from sgAccessUser(). A
// mistake is corrected by adding another note, never by editing one.

$BP_LOGLIB = ((string)@$_SERVER['SERVER_PORT'] === '5610') ? 'SG5OBJ' : 'SGOBJ';

// Every entry must fit CLOUTC, which is CHAR(20) - 'Emailed - awaiting reply'
// was 24 and silently arrived truncated as 'Emailed - awaiting r'.
$BP_OUTCOMES = array(
    'Reached - will order', 'Reached - not now', 'Left voicemail',
    'No answer', 'Wrong number', 'Awaiting reply', 'Not interested',
);
foreach ($BP_OUTCOMES as $o) {
    if (strlen($o) > 20) { $sqlErr[] = "Outcome '$o' exceeds CLOUTC CHAR(20)"; }
}
$BP_NOTE_MIN = 15;

// Outcomes where nobody was actually spoken to, so there is nothing to write
// down. The outcome plus the IBM i timestamp IS the record. A follow-up is
// still required - that is precisely when you need to try again.
$BP_NOTE_OPTIONAL = array('No answer');

$logMsg = '';
$logErr = '';

if (isset($_POST['bp_action']) && $_POST['bp_action'] === 'logcontact') {
    $pSh   = isset($_POST['bp_shipto']) ? (int)preg_replace('/[^0-9]/', '', $_POST['bp_shipto']) : 0;
    $pTy   = isset($_POST['bp_type'])   ? strtoupper(substr(trim($_POST['bp_type']), 0, 1)) : '';
    $pNote = isset($_POST['bp_note'])   ? trim($_POST['bp_note']) : '';
    $pOut  = isset($_POST['bp_outcome']) ? trim($_POST['bp_outcome']) : '';
    $pFud  = isset($_POST['bp_fudate']) ? trim($_POST['bp_fudate']) : '';
    $pFnr  = isset($_POST['bp_funone']) ? trim($_POST['bp_funone']) : '';
    $pTier = isset($_POST['bp_tier'])   ? (int)$_POST['bp_tier'] : 0;

    // Server-side validation. The browser checks the same things first, but the
    // browser is not the gate - this is.
    if ($bpUser === '')                          { $logErr = 'Your EIP profile could not be identified, so nothing was logged.'; }
    elseif ($pSh <= 0)                           { $logErr = 'No ship-to was supplied.'; }
    elseif (!in_array($pTy, array('C','E','N'), true)) { $logErr = 'Pick call or email.'; }
    elseif ($pOut !== '' && !in_array($pOut, $BP_OUTCOMES, true)) { $logErr = 'That outcome is not on the list.'; }
    elseif (!in_array($pOut, $BP_NOTE_OPTIONAL, true) && mb_strlen($pNote) < $BP_NOTE_MIN)
                                                 { $logErr = 'The note must be at least ' . $BP_NOTE_MIN . ' characters. Nothing was logged.'; }
    elseif ($pFud === '' && $pFnr === '')        { $logErr = 'Set a follow-up date, or say why none is needed.'; }
    elseif ($pFud !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $pFud)) { $logErr = 'Follow-up date must be YYYY-MM-DD.'; }
    else {
        // The caller must be allowed to see this customer before logging on it
        $okCust = false;
        $chk = bp_fetchAll($conn,
            "SELECT CMSLSM FROM SGHDSDATA.HDCUST WHERE CMCUST = $pSh",
            'accessCheck', $sqlErr, $timings);
        if (!empty($chk)) {
            $cs = (int)$chk[0]['CMSLSM'];
            $okCust = ($bpSeeAll || in_array($cs, $bpAllowed, true));
        }
        if (!$okCust) {
            $logErr = 'That customer is not in the accounts you cover, so nothing was logged.';
        } else {
            $ins = "INSERT INTO $BP_LOGLIB.BPCALLLOG
                    (CLSHTO, CLTYPE, CLUSER, CLOUTC, CLFUDT, CLFUNR, CLNOTE, CLTIER, CLTGTQ, CLIP)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $st = db2_prepare($conn, $ins);
            if (!$st) {
                $logErr = 'Could not prepare the insert: ' . db2_stmt_errormsg();
            } else {
                $bind = array(
                    $pSh, $pTy, substr($bpUser, 0, 10), substr($pOut, 0, 20),
                    ($pFud !== '' ? $pFud : null), substr($pFnr, 0, 120),
                    $pNote, $pTier, substr($tgtQLbl . ' ' . $tgtQY, 0, 7),
                    substr((string)@$_SERVER['REMOTE_ADDR'], 0, 45),
                );
                if (@db2_execute($st, $bind)) {
                    // Post/redirect/get, so a refresh cannot double-log
                    $back = '?' . http_build_query(array('view' => 'detail', 'shipto' => $pSh, 'logged' => '1'));
                    header('Location: ' . $back);
                    exit;
                }
                $logErr = 'The note was not saved: ' . db2_stmt_errormsg();
            }
        }
    }
}
if (isset($_GET['logged']) && $_GET['logged'] === '1') {
    $logMsg = 'Contact logged.';
}

// -- The line-grain derived table: each (order, line) counted exactly once ----

$AMT = "CASE WHEN MAX(d.DHSLPR) = 0 OR MAX(d.DHORUF) = 0 THEN 0
             ELSE MAX(d.DHQORD) * MAX(d.DHSLPR) / MAX(d.DHORUF) END";

$LINE = "
    SELECT h.OESHTO              AS SHIPTO,
           h.\"OEORD#\"          AS ORDNO,
           d.\"DHORL#\"          AS LN,
           MAX(TRIM(d.DHITEM))   AS ITEM,
           MAX(TRIM(d.DHIMDS))   AS ITEMDESC,
           MAX(h.OEBDTE)         AS ORDDTE,
           MAX(h.OEORTY)         AS ORDTY,
           MAX(d.DHQORD)         AS QORD,
           MAX(d.DHQSTC)         AS QSHIP,
           MAX(d.DHSLPR)         AS PRICE,
           MAX(d.DHORUF)         AS UF,
           COUNT(*)              AS SHIPROWS,
           $AMT                  AS AMT
    FROM SGHDSDATA.OEORDH d
    JOIN SGHDSDATA.OEORHD h ON d.\"DHORD#\" = h.\"OEORD#\"
    -- Inner join, deliberately. 9 ship-tos carry orders but have no HDCUST
    -- record at all; they surfaced as a '??' customer class. An inner join
    -- drops them and also stops a missing customer slipping past the bill-to
    -- exclusion, which COALESCE(CMBLTO, 0) would have let through.
    JOIN SGHDSDATA.HDCUST c ON h.OESHTO = c.CMCUST
    WHERE d.\"DHSEQ#\" <> 0
      AND TRIM(COALESCE(c.CMCCLS, '')) <> ''
      AND h.OEBDTE >= $winStart
      AND h.OEBDTE <= $todayCymd
      AND h.OEORTY NOT IN ($BP_BAD_ORDTY)
      AND TRIM(d.DHITEM) <> 'AD0166'
      AND TRIM(d.DHITEM) NOT LIKE 'LTL%'
      AND TRIM(d.DHITEM) NOT LIKE '%SAMP%'
      AND COALESCE(c.CMBLTO, 0) NOT IN ($BP_BAD_BILLTO)
      AND h.\"OEORD#\" NOT IN ($BP_BAD_ORDERS)$BP_SCOPE$BP_ACCESS
      AND EXISTS (SELECT 1 FROM SGHDSDATA.OEORDT t
                   WHERE t.\"ODORD#\" = d.\"DHORD#\"
                     AND t.\"ODORL#\" = d.\"DHORL#\"
                     AND t.ODOREC    = 'S')
    GROUP BY h.OESHTO, h.\"OEORD#\", d.\"DHORL#\"
";

// Per-year and per-target-quarter columns, driven off the line-grain table
$selYear = '';
foreach ($yrs as $y) {
    $s = $yb[$y]['start']; $e = $yb[$y]['end'];
    $qs = $yb[$y]['tqs'];  $qe = $yb[$y]['tqe'];
    $selYear .= "
        SUM(CASE WHEN L.ORDDTE BETWEEN $s  AND $e  THEN L.AMT ELSE 0 END) AS REV$y,
        SUM(CASE WHEN L.ORDDTE BETWEEN $qs AND $qe THEN L.AMT ELSE 0 END) AS TQR$y,
        COUNT(DISTINCT CASE WHEN L.ORDDTE BETWEEN $qs AND $qe THEN L.ORDNO END) AS TQO$y,
        MIN(CASE WHEN L.ORDDTE BETWEEN $qs AND $qe THEN L.ORDDTE END) AS TQF$y,";
}

// -- Query: one row per ship-to ----------------------------------------------

$sqlCust = "
    SELECT
        L.SHIPTO                             AS SHIPTO,
        MAX(TRIM(c2.CMCNA1))                 AS CUSTNAME,
        MAX(COALESCE(TRIM(c2.CMCCLS), '??')) AS CLSCODE,
        MAX(TRIM(c2.CMCNA2))                 AS ADDR1,
        MAX(TRIM(c2.CMCCTY))                 AS CITY,
        MAX(TRIM(c2.CMST))                   AS STATE,
        MAX(TRIM(c2.CMZIP))                  AS ZIP,
        MAX(TRIM(c2.CMCTRY))                 AS CTRY,
        MAX(TRIM(c2.CMPHON))                 AS PHONE,
        MAX(c2.CMSLSM)                       AS SLSM,
        MAX(c2.CMBLTO)                       AS BILLTO,
        $selYear
        MAX(L.ORDDTE)                        AS LASTORD,
        COUNT(DISTINCT L.ORDNO)              AS ORDCNT,
        COUNT(*)                             AS LINECNT
    FROM ($LINE) L
    LEFT JOIN SGHDSDATA.HDCUST c2 ON L.SHIPTO = c2.CMCUST
    GROUP BY L.SHIPTO
";
$custRows = bp_fetchAll($conn, $sqlCust, 'customers', $sqlErr, $timings);

// -- Query: one row per ship-to x item ---------------------------------------

$selItemYear = '';
foreach ($yrs as $y) {
    $s = $yb[$y]['start']; $e = $yb[$y]['end'];
    $selItemYear .= "
        SUM(CASE WHEN L.ORDDTE BETWEEN $s AND $e THEN L.AMT  ELSE 0 END) AS R$y,
        SUM(CASE WHEN L.ORDDTE BETWEEN $s AND $e THEN L.QORD ELSE 0 END) AS Q$y,
        MAX(CASE WHEN L.ORDDTE BETWEEN $s AND $e THEN 1 ELSE 0 END)      AS F$y,";
}
$histS = $yb[$hy[0]]['start'];
$histE = $yb[$hy[2]]['end'];

$sqlItem = "
    SELECT
        L.SHIPTO            AS SHIPTO,
        L.ITEM              AS ITEM,
        MAX(L.ITEMDESC)     AS ITEMDESC,
        $selItemYear
        COUNT(DISTINCT CASE WHEN L.ORDDTE BETWEEN $histS AND $histE
                            THEN L.ORDNO END) AS HISTORDS,
        MAX(L.ORDDTE)       AS LASTORD,
        MAX(L.PRICE)        AS LASTPRICE
    FROM ($LINE) L
    GROUP BY L.SHIPTO, L.ITEM
";
// Level 5 renders raw order lines and never touches the item rollup, so skip it.
$itemRows = ($view === 'lines')
          ? array()
          : bp_fetchAll($conn, $sqlItem, 'items', $sqlErr, $timings);

// -- Query: open unshipped orders (shown, never used to suppress) -------------

// Open means OEORST = 'O' on the header AND ODORST = 'O' on the line - 'C' is
// closed. OEORDT retains every closed line back to 1991, so without the status
// test this counted 186,916 closed lines and reported $38.2M of "open" work.
// The money is the UNSHIPPED balance, not the whole line: (ODQORD - ODQSTD).
$sqlOpen = "
    SELECT
        h.OESHTO                     AS SHIPTO,
        COUNT(DISTINCT h.\"OEORD#\") AS OPENORDS,
        SUM(CASE WHEN d.ODSLPR = 0 THEN 0
                 ELSE (d.ODQORD - d.ODQSTD) * d.ODSLPR END) AS OPENAMT
    FROM SGHDSDATA.OEORHD h
    JOIN SGHDSDATA.OEORDT d ON h.\"OEORD#\" = d.\"ODORD#\"
    JOIN SGHDSDATA.HDCUST c ON h.OESHTO     = c.CMCUST
    WHERE h.OEORTY NOT IN ($BP_BAD_ORDTY)
      AND TRIM(COALESCE(c.CMCCLS, '')) <> ''
      AND h.OEORST = 'O'
      AND d.ODORST = 'O'
      AND d.ODOREC = 'S'
      AND d.ODQORD > d.ODQSTD
      AND TRIM(d.ODITEM) <> 'AD0166'
      AND TRIM(d.ODITEM) NOT LIKE 'LTL%'
      AND TRIM(d.ODITEM) NOT LIKE '%SAMP%'
      AND COALESCE(c.CMBLTO, 0) NOT IN ($BP_BAD_BILLTO)
      AND h.\"OEORD#\" NOT IN ($BP_BAD_ORDERS)$BP_SCOPE$BP_ACCESS
    GROUP BY h.OESHTO
";
$openRows = bp_fetchAll($conn, $sqlOpen, 'openOrders', $sqlErr, $timings);

// -- Seasonal shape (slide 4): one month-grain pass feeds both charts ---------
// Only the headline view draws charts, so this never runs on a drill-down.

$monthRows = array();
if ($view === 'tiles') {
    $sqlMonth = "
        SELECT INT(L.ORDDTE/10000) + 1900                     AS YR,
               MOD(INT(L.ORDDTE/100), 100)                    AS MO,
               SUM(L.AMT)                                     AS REVENUE,
               COUNT(DISTINCT L.ORDNO)                        AS ORDERS,
               COUNT(DISTINCT L.SHIPTO)                        AS CUSTOMERS
        FROM ($LINE) L
        GROUP BY INT(L.ORDDTE/10000) + 1900, MOD(INT(L.ORDDTE/100), 100)
    ";
    $monthRows = bp_fetchAll($conn, $sqlMonth, 'seasonal', $sqlErr, $timings);
}

$monMap = array();   // [year][month] => row
foreach ($monthRows as $r) {
    $monMap[(int)$r['YR']][(int)$r['MO']] = array(
        'rev'   => (float)$r['REVENUE'],
        'ords'  => (int)$r['ORDERS'],
        'custs' => (int)$r['CUSTOMERS'],
    );
}
$monName = array(1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
                 7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec');
$monFull = array(1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
                 7=>'July',8=>'August',9=>'September',10=>'October',
                 11=>'November',12=>'December');

// $fMo only means anything if it names a month of the target quarter
if ($fMo < $tgtQM1 || $fMo > $tgtQM3) { $fMo = 0; }
// $fPy/$fPq only mean anything as a real year in the window and a real quarter
if (!in_array($fPy, $yrs, true) || $fPq < 1 || $fPq > 4) { $fPy = 0; $fPq = 0; }
if ($fMo > 0) { $fPy = 0; $fPq = 0; }   // a month drill wins if both are passed
$histYearList = implode(',', $hy);

// One period filter drives both charts' drill-downs.
$periodOn    = ($fMo > 0) || ($fPy > 0 && $fPq > 0);
$periodShort = '';
$periodLong  = '';
$periodWhere = '';
if ($fMo > 0) {
    $periodShort = $monName[$fMo];
    $periodLong  = $monFull[$fMo] . ' ' . $hy[0] . '-' . $hy[2];
    $periodWhere = "MOD(INT(L.ORDDTE/100), 100) = $fMo
                    AND INT(L.ORDDTE/10000) + 1900 IN ($histYearList)";
} elseif ($fPy > 0) {
    $pM1 = ($fPq - 1) * 3 + 1;
    $pM3 = $pM1 + 2;
    $pS  = bp_cymd($fPy, $pM1, 1);
    $pE  = bp_cymd($fPy, $pM3, $lastDay[$pM3]);
    $periodShort = 'Q' . $fPq . ' ' . $fPy;
    $periodLong  = 'Q' . $fPq . ' ' . $fPy . ' (' . $monName[$pM1] . ' to '
                 . $monName[$pM3] . ' ' . $fPy . ')';
    $periodWhere = "L.ORDDTE BETWEEN $pS AND $pE";
}

// Chart 1: revenue by quarter, per year
$qtrByYear = array();
foreach ($yrs as $y) {
    for ($q = 1; $q <= 4; $q++) { $qtrByYear[$y][$q] = 0.0; }
    for ($m = 1; $m <= 12; $m++) {
        if (isset($monMap[$y][$m])) {
            $qtrByYear[$y][intval(($m - 1) / 3) + 1] += $monMap[$y][$m]['rev'];
        }
    }
}

// Chart 2: inside the target quarter, month by month, over the history years.
// Revenue and orders add up cleanly across years; DISTINCT customers do not -
// summing per-year counts would count anyone who orders in two Octobers twice -
// so the true distinct figure comes from its own small pass below.
$tqMonths = array();
for ($m = $tgtQM1; $m <= $tgtQM3; $m++) {
    $tqMonths[$m] = array('rev' => 0.0, 'ords' => 0, 'custs' => 0);
    foreach ($hy as $y) {
        if (isset($monMap[$y][$m])) {
            $tqMonths[$m]['rev']  += $monMap[$y][$m]['rev'];
            $tqMonths[$m]['ords'] += $monMap[$y][$m]['ords'];
        }
    }
}
if ($view === 'tiles') {
    $sqlMoCust = "
        SELECT MOD(INT(L.ORDDTE/100), 100) AS MO,
               COUNT(DISTINCT L.SHIPTO)    AS CUSTOMERS
        FROM ($LINE) L
        WHERE MOD(INT(L.ORDDTE/100), 100) BETWEEN $tgtQM1 AND $tgtQM3
          AND INT(L.ORDDTE/10000) + 1900 IN ($histYearList)
        GROUP BY MOD(INT(L.ORDDTE/100), 100)
    ";
    foreach (bp_fetchAll($conn, $sqlMoCust, 'seasonalCusts', $sqlErr, $timings) as $r) {
        $m = (int)$r['MO'];
        if (isset($tqMonths[$m])) { $tqMonths[$m]['custs'] = (int)$r['CUSTOMERS']; }
    }
}

// -- Month drill: who orders in the clicked month, and how much ---------------

$moRev = array();   // shipto => array(rev, ords, last) for the selected period
if ($periodOn && $view === 'cust') {
    $sqlPeriod = "
        SELECT L.SHIPTO                 AS SHIPTO,
               SUM(L.AMT)               AS MOREV,
               COUNT(DISTINCT L.ORDNO)  AS MOORDS,
               MAX(L.ORDDTE)            AS MOLAST
        FROM ($LINE) L
        WHERE $periodWhere
        GROUP BY L.SHIPTO
    ";
    foreach (bp_fetchAll($conn, $sqlPeriod, 'periodDrill', $sqlErr, $timings) as $r) {
        $moRev[trim((string)$r['SHIPTO'])] = array(
            'rev'  => (float)$r['MOREV'],
            'ords' => (int)$r['MOORDS'],
            'last' => (int)$r['MOLAST'],
        );
    }
}

// -- Lookups ------------------------------------------------------------------

$clsRows = bp_fetchAll($conn,
    "SELECT TRIM(CCCCLS) AS CODE, TRIM(CCCCDS) AS DESCR FROM SGHDSDATA.HDCCLS",
    'classes', $sqlErr, $timings);
$clsName = array();
foreach ($clsRows as $r) { $clsName[$r['CODE']] = $r['DESCR']; }

$slsRows = bp_fetchAll($conn,
    "SELECT SMSLSM AS CODE, TRIM(SMSNA1) AS NAME FROM SGHDSDATA.HDSLSM",
    'salespeople', $sqlErr, $timings);
$slsName = array();
foreach ($slsRows as $r) { $slsName[(int)$r['CODE']] = $r['NAME']; }

// -- Level 5: the raw order lines behind one customer (on demand only) -------

$lineRows = array();
if (($view === 'lines' || $view === 'detail') && $fShipto !== '') {
    $whereItem = ($fItem !== '') ? " AND L.ITEM = '" . bp_sqlStr($fItem) . "'" : '';
    $sqlLines = "
        SELECT L.*
        FROM ($LINE) L
        WHERE L.SHIPTO = " . (int)$fShipto . "
        $whereItem
        ORDER BY L.ORDDTE DESC, L.ORDNO DESC, L.LN
    ";
    $lineRows = bp_fetchAll($conn, $sqlLines, 'orderLines', $sqlErr, $timings);
}

// -- Build the item model -----------------------------------------------------

$openBy = array();
foreach ($openRows as $r) {
    $openBy[trim((string)$r['SHIPTO'])] = array(
        'ords' => (int)$r['OPENORDS'],
        'amt'  => (float)$r['OPENAMT'],
    );
}

$itemAgg      = array();   // shipto => stopped/reduced rollup
$itemsByShip  = array();   // shipto => list of item records (for L4)
$skuAgg       = array();   // item   => stopped rollup across customers
$skuShiptos   = array();   // item   => ship-tos that stopped it (for L3 drill)

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
    $curQty   = (float)$r['Q' . $curY];
    $histOrds = (int)$r['HISTORDS'];

    // A genuine repeat purchase: 2+ years, or 3+ separate orders. Most items
    // here are customer-specific printed products, so an item bought once and
    // never repeated was completed, not lost.
    $isRepeat = ($yrsWith >= 2) || ($histOrds >= 3);
    // Rate test uses the years they actually bought in - "is this item still
    // moving at its usual pace". The lost-dollar figure uses $BP_NORMAL_BASIS.
    $normalRate = ($yrsWith > 0) ? $histRev / $yrsWith : 0.0;
    $iDen       = ($BP_NORMAL_BASIS === 'active') ? $yrsWith : count($hy);
    $normal     = ($iDen > 0) ? $histRev / $iDen : 0.0;
    $normalQty  = ($iDen > 0) ? $histQty / $iDen : 0.0;

    $status = 'steady';
    $lossAmt = 0.0;
    if (!$isRepeat) {
        $status = 'one-off';
    } elseif ($curRev <= 0) {
        $status  = 'stopped';
        $lossAmt = $normal;
    } elseif ($curRev < $normalRate * $BP_DOWN_FACTOR) {
        $status  = 'reduced';
        $lossAmt = max(0.0, $normal - $curRev);
    }

    $rec = array(
        'item'      => $item,
        'desc'      => trim((string)$r['ITEMDESC']),
        'histRev'   => $histRev,
        'curRev'    => $curRev,
        'curQty'    => $curQty,
        'normal'    => $normal,
        'normalQty' => $normalQty,
        'yrsWith'   => $yrsWith,
        'histOrds'  => $histOrds,
        'status'    => $status,
        'lossAmt'   => $lossAmt,
        'lastOrd'   => (int)$r['LASTORD'],
        'lastPrice' => (float)$r['LASTPRICE'],
    );
    foreach ($yrs as $y) {
        $rec['r' . $y] = (float)$r['R' . $y];
        $rec['q' . $y] = (float)$r['Q' . $y];
    }
    $itemsByShip[$sh][] = $rec;

    if (!isset($itemAgg[$sh])) {
        $itemAgg[$sh] = array('stopAmt'=>0.0, 'redAmt'=>0.0, 'stopN'=>0, 'redN'=>0);
    }
    if ($status === 'stopped') {
        $itemAgg[$sh]['stopAmt'] += $normal;
        $itemAgg[$sh]['stopN']++;
        if (!isset($skuAgg[$item])) {
            $skuAgg[$item] = array('desc'=>$rec['desc'], 'custs'=>0, 'amt'=>0.0, 'qty'=>0.0);
        }
        $skuAgg[$item]['custs']++;
        $skuAgg[$item]['amt'] += $normal;
        $skuAgg[$item]['qty'] += $normalQty;
        $skuShiptos[$item][$sh] = true;
    } elseif ($status === 'reduced') {
        $itemAgg[$sh]['redAmt'] += $lossAmt;
        $itemAgg[$sh]['redN']++;
    }
}

// -- Build the customer model -------------------------------------------------

$custs   = array();
$custIx  = array();   // shipto => index into $custs

foreach ($custRows as $r) {
    $sh = trim((string)$r['SHIPTO']);

    $histRev = 0.0; $tqRev = 0.0; $tqYears = 0; $yrsWith = 0;
    $byYear = array(); $tqByYear = array(); $tqOrd = array(); $tqFirst = array();
    foreach ($hy as $y) {
        $rv = (float)$r['REV' . $y];
        $tq = (float)$r['TQR' . $y];
        $qo = (int)$r['TQO' . $y];
        $byYear[$y]   = $rv;
        $tqByYear[$y] = $tq;
        $tqOrd[$y]    = $qo;
        $tqFirst[$y]  = (int)$r['TQF' . $y];
        $histRev += $rv;
        $tqRev   += $tq;
        if ($rv > 0) { $yrsWith++; }
        if ($qo > 0) { $tqYears++; }
    }
    $curRev          = (float)$r['REV' . $curY];
    $byYear[$curY]   = $curRev;
    $tqByYear[$curY] = (float)$r['TQR' . $curY];
    $tqOrd[$curY]    = (int)$r['TQO' . $curY];

    if ($histRev <= 0 || $yrsWith === 0) { continue; }

    // Rate test uses the years they actually bought in - "are they still buying
    // at their usual pace". The at-stake dollars use $BP_NORMAL_BASIS.
    $normalRate = $histRev / $yrsWith;
    $cDen     = ($BP_NORMAL_BASIS === 'active') ? $yrsWith : count($hy);
    $normal   = $histRev / $cDen;                      // one "normal year"
    $tqShare  = $histRev > 0 ? $tqRev / $histRev : 0.0;
    $tqAvg    = $tqYears > 0 ? $tqRev / $tqYears : 0.0;
    $strongTQ = ($tqShare >= $BP_SHARE_STRONG && $tqYears >= $BP_YEARS_STRONG);
    $silent   = ($curRev <= 0);
    $downHalf = (!$silent && $curRev < $normalRate * $BP_DOWN_FACTOR);

    $tier = 0;
    if     ($strongTQ && $silent)   { $tier = 1; }
    elseif ($strongTQ && $downHalf) { $tier = 2; }
    elseif ($strongTQ)              { $tier = 3; }
    elseif ($silent)                { $tier = 4; }
    elseif ($downHalf)              { $tier = 5; }

    // What is genuinely recoverable, by tier. A customer still buying normally
    // has no lost year - only the timing of their target-quarter order is in
    // play - so tier 3 contributes that quarter's average order value, not a
    // whole year. Tiers 2 and 5 contribute the shortfall, not a whole year.
    // Only tiers 1 and 4, which bought nothing at all, put a full year at risk.
    $stake = 0.0;
    if     ($tier === 1 || $tier === 4) { $stake = $normal; }
    elseif ($tier === 2 || $tier === 5) { $stake = max(0.0, $normal - $curRev); }
    elseif ($tier === 3)                { $stake = $tqAvg; }

    // Call-by date: lead days ahead of their own average target-quarter kickoff
    $kickOffsets = array();
    foreach ($hy as $y) {
        $f = $tqFirst[$y];
        if ($f > 0) {
            $mm = intval(($f % 10000) / 100);
            $dd = $f % 100;
            $kickOffsets[] = ($mm - $tgtQM1) * 31 + $dd;
        }
    }
    $callBy = '';
    if (!empty($kickOffsets)) {
        $avgInto = array_sum($kickOffsets) / count($kickOffsets);
        $kickTs  = mktime(0, 0, 0, $tgtQM1, 1, $tgtQY) + (int)round($avgInto - 1) * 86400;
        $callBy  = date('Y-m-d', $kickTs - $BP_CALL_LEAD * 86400);
    }

    $ia  = isset($itemAgg[$sh]) ? $itemAgg[$sh] : array('stopAmt'=>0.0,'redAmt'=>0.0,'stopN'=>0,'redN'=>0);
    $op  = isset($openBy[$sh])  ? $openBy[$sh]  : array('ords'=>0,'amt'=>0.0);
    $cls = trim((string)$r['CLSCODE']);
    $slm = (int)$r['SLSM'];

    $custIx[$sh] = count($custs);
    $custs[] = array(
        'shipto'   => $sh,
        'name'     => trim((string)$r['CUSTNAME']),
        'cls'      => $cls,
        'clsdesc'  => isset($clsName[$cls]) ? $clsName[$cls] : $cls,
        'addr1'    => trim((string)$r['ADDR1']),
        'city'     => trim((string)$r['CITY']),
        'state'    => trim((string)$r['STATE']),
        'zip'      => trim((string)$r['ZIP']),
        'ctry'     => trim((string)$r['CTRY']),
        'phone'    => trim((string)$r['PHONE']),
        'slsm'     => $slm,
        'slsmname' => isset($slsName[$slm]) ? $slsName[$slm] : '',
        'billto'   => (int)$r['BILLTO'],
        'byYear'   => $byYear,
        'tqByYear' => $tqByYear,
        'tqOrd'    => $tqOrd,
        'tqFirst'  => $tqFirst,
        'histRev'  => $histRev,
        'curRev'   => $curRev,
        'normal'   => $normal,
        'tqRev'    => $tqRev,
        'tqAvg'    => $tqAvg,
        'tqShare'  => $tqShare,
        'tqYears'  => $tqYears,
        'yrsWith'  => $yrsWith,
        'tier'     => $tier,
        'silent'   => $silent,
        'stake'    => $stake,
        'callBy'   => $callBy,
        'stopAmt'  => $ia['stopAmt'],
        'redAmt'   => $ia['redAmt'],
        'stopN'    => $ia['stopN'],
        'redN'     => $ia['redN'],
        'openOrds' => $op['ords'],
        'openAmt'  => $op['amt'],
        'lastOrd'  => (int)$r['LASTORD'],
        'ordCnt'   => (int)$r['ORDCNT'],
        'lineCnt'  => (int)$r['LINECNT'],
    );
}

// -- Aggregates ---------------------------------------------------------------

$tileHistory  = count($custs);
$tileSilent   = 0;   $tileSilentRev = 0.0;
$tileEveryTQ  = 0;   $tileEveryTQRev = 0.0;
$tileTQWtd    = 0;
$tileStopAmt  = 0.0; $tileStopItems = 0;
$tileRedAmt   = 0.0;
$tileAtStake  = 0.0;
$tileOpenOrds = 0;   $tileOpenAmt = 0.0;
$totalHistRev = 0.0; $totalCurRev = 0.0;

$tierCount = array(1=>0,2=>0,3=>0,4=>0,5=>0);
$tierStake = array(1=>0.0,2=>0.0,3=>0.0,4=>0.0,5=>0.0);
$tierStop  = array(1=>0.0,2=>0.0,3=>0.0,4=>0.0,5=>0.0);
$tierRed   = array(1=>0.0,2=>0.0,3=>0.0,4=>0.0,5=>0.0);
$tierItems = array(1=>0,2=>0,3=>0,4=>0,5=>0);
$tierOpen  = array(1=>0.0,2=>0.0,3=>0.0,4=>0.0,5=>0.0);
$byClass   = array();

foreach ($custs as $c) {
    $totalHistRev += $c['histRev'];
    $totalCurRev  += $c['curRev'];
    if ($c['silent'])        { $tileSilent++;  $tileSilentRev  += $c['histRev']; }
    if ($c['tqYears'] >= 3)  { $tileEveryTQ++; $tileEveryTQRev += $c['tqRev']; }
    if ($c['tqShare'] >= $BP_SHARE_TILE) { $tileTQWtd++; }
    $tileStopAmt   += $c['stopAmt'];
    $tileRedAmt    += $c['redAmt'];
    $tileStopItems += $c['stopN'];

    $t = $c['tier'];
    if ($t > 0) {
        $tierCount[$t]++;
        $tierStake[$t] += $c['stake'];
        $tierStop[$t]  += $c['stopAmt'];
        $tierRed[$t]   += $c['redAmt'];
        $tierItems[$t] += $c['stopN'];
        $tierOpen[$t]  += $c['openAmt'];
        $tileAtStake   += $c['stake'];
        $tileOpenOrds  += $c['openOrds'];
        $tileOpenAmt   += $c['openAmt'];
    }

    $cl = $c['cls'];
    if (!isset($byClass[$cl])) {
        $byClass[$cl] = array('desc'=>$c['clsdesc'], 'custs'=>0, 'hist'=>0.0,
                              'tq'=>0.0, 'stake'=>0.0, 'stop'=>0.0,
                              't'=>array(1=>0,2=>0,3=>0,4=>0,5=>0));
    }
    $byClass[$cl]['custs']++;
    $byClass[$cl]['hist']  += $c['histRev'];
    $byClass[$cl]['tq']    += $c['tqRev'];
    $byClass[$cl]['stop']  += $c['stopAmt'];
    if ($t > 0) { $byClass[$cl]['stake'] += $c['stake']; $byClass[$cl]['t'][$t]++; }
}

$tierTotal = array_sum($tierCount);

$skuList = array();
foreach ($skuAgg as $item => $a) {
    $skuList[] = array('item'=>$item, 'desc'=>$a['desc'],
                       'custs'=>$a['custs'], 'amt'=>$a['amt'], 'qty'=>$a['qty']);
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
    1 => $tgtQLbl . ' buyer, ordered nothing in ' . $curY,
    2 => $tgtQLbl . ' buyer, ' . $curY . ' under half normal',
    3 => $tgtQLbl . ' buyer, active - pull the order forward',
    4 => 'Lapsed: bought ' . $hy[0] . '-' . $hy[2] . ', nothing in ' . $curY,
    5 => $curY . ' under half normal, no ' . $tgtQLbl . ' skew',
);
$tierRule = array(
    1 => bp_pct($BP_SHARE_STRONG) . '+ of revenue in ' . $tgtQLbl . ', ' . $tgtQLbl
       . ' orders in ' . $BP_YEARS_STRONG . '+ years, zero in ' . $curY,
    2 => 'same ' . $tgtQLbl . ' habit, ' . $curY . ' under half their normal year',
    3 => $tgtQLbl . ' habit and buying normally in ' . $curY,
    4 => 'bought ' . $hy[0] . '-' . $hy[2] . ', nothing in ' . $curY
       . ', no strong ' . $tgtQLbl . ' skew',
    5 => $curY . ' under half their normal year, no strong ' . $tgtQLbl . ' skew',
);
$stakeRule = array(
    1 => 'one full normal year - they bought nothing at all',
    2 => 'the shortfall against a normal year',
    3 => 'their average ' . $tgtQLbl . ' order value, the amount being pulled forward',
    4 => 'one full normal year - they bought nothing at all',
    5 => 'the shortfall against a normal year',
);

// -- Level 3 row selection ----------------------------------------------------

$listTitle = 'All customers with history';
$listRows  = array();
if ($view === 'cust') {
    foreach ($custs as $c) {
        if ($fTier > 0 && $c['tier'] !== $fTier) { continue; }
        if ($fCls !== '' && $c['cls'] !== $fCls) { continue; }
        if ($fItem !== '' && !isset($skuShiptos[$fItem][$c['shipto']])) { continue; }
        if ($fStatus === 'silent' && !$c['silent']) { continue; }
        if ($fStatus === 'everytq' && $c['tqYears'] < 3) { continue; }
        if ($fStatus === 'tqwtd' && $c['tqShare'] < $BP_SHARE_TILE) { continue; }
        if ($fStatus === 'oncall' && $c['tier'] === 0) { continue; }
        if ($periodOn && !isset($moRev[$c["shipto"]])) { continue; }
        $listRows[] = $c;
    }
    usort($listRows, function ($a, $b) {
        if ($a['stake'] == $b['stake']) return 0;
        return ($a['stake'] < $b['stake']) ? 1 : -1;
    });

    $bits = array();
    if ($fMo > 0)     { $bits[] = 'ordered in ' . $monFull[$fMo] . ' - their '
                                . $tgtQLbl . ' order lands in ' . $monName[$fMo]
                                . ' (' . $hy[0] . '-' . $hy[2] . ')'; }
    elseif ($fPy > 0) { $bits[] = 'ordered in ' . $periodLong; }
    if ($fTier > 0)   { $bits[] = 'Tier ' . $fTier . ' - ' . $tierRule[$fTier]; }
    if ($fCls !== '') { $bits[] = 'Class ' . $fCls
                                . (isset($clsName[$fCls]) ? ' (' . $clsName[$fCls] . ')' : ''); }
    if ($fItem !== ''){ $bits[] = 'stopped buying item ' . $fItem; }
    if ($fStatus === 'silent')  { $bits[] = 'nothing ordered in ' . $curY; }
    if ($fStatus === 'everytq') { $bits[] = 'ordered in ' . $tgtQLbl . ' all three years'; }
    if ($fStatus === 'tqwtd')   { $bits[] = bp_pct($BP_SHARE_TILE) . '+ of revenue in ' . $tgtQLbl; }
    if ($fStatus === 'oncall')  { $bits[] = 'on a call tier'; }
    if (!empty($bits)) { $listTitle = implode(' &middot; ', $bits); }
}

// -- Level 4 subject ----------------------------------------------------------

$sub      = null;
$subItems = array();
if (($view === 'detail' || $view === 'lines') && $fShipto !== '' && isset($custIx[$fShipto])) {
    $sub = $custs[$custIx[$fShipto]];
    $subItems = isset($itemsByShip[$fShipto]) ? $itemsByShip[$fShipto] : array();
    usort($subItems, function ($a, $b) {
        if ($a['lossAmt'] == $b['lossAmt']) {
            if ($a['normal'] == $b['normal']) return 0;
            return ($a['normal'] < $b['normal']) ? 1 : -1;
        }
        return ($a['lossAmt'] < $b['lossAmt']) ? 1 : -1;
    });
}

// -- Level 2.5: the rep call cards (slide 8) ----------------------------------
// Top N customers by dollars at stake in one tier, each with the exact quantity
// and date they last bought every stopped item. The last-bought figures need a
// ranked pass, so it is scoped to just the ship-tos actually being shown.

$cardCusts = array();
$lastBuy   = array();   // shipto => item => array(qty, date)
if ($view === 'cards') {
    $tierWanted = ($fTier >= 1 && $fTier <= 5) ? $fTier : 1;
    foreach ($custs as $c) {
        if ($c['tier'] === $tierWanted) { $cardCusts[] = $c; }
    }
    usort($cardCusts, function ($a, $b) {
        if ($a['stake'] == $b['stake']) return 0;
        return ($a['stake'] < $b['stake']) ? 1 : -1;
    });
    $cardCusts = array_slice($cardCusts, 0, $fLimit);

    $ids = array();
    foreach ($cardCusts as $c) { $ids[] = (int)$c['shipto']; }
    if (!empty($ids)) {
        $inList = implode(',', $ids);
        $sqlLast = "
            SELECT Z.SHIPTO, Z.ITEM, Z.QORD, Z.ORDDTE
            FROM (
                SELECT L.SHIPTO, L.ITEM, L.QORD, L.ORDDTE,
                       ROW_NUMBER() OVER (PARTITION BY L.SHIPTO, L.ITEM
                                          ORDER BY L.ORDDTE DESC, L.ORDNO DESC) AS RN
                FROM ($LINE) L
                WHERE L.SHIPTO IN ($inList)
            ) Z
            WHERE Z.RN = 1
        ";
        $lastRows = bp_fetchAll($conn, $sqlLast, 'lastBought', $sqlErr, $timings);
        foreach ($lastRows as $r) {
            $lastBuy[trim((string)$r['SHIPTO'])][trim((string)$r['ITEM'])] = array(
                'qty'  => (float)$r['QORD'],
                'date' => (int)$r['ORDDTE'],
            );
        }
    }
}

// -- Read the contact log ----------------------------------------------------

// Per-customer rollup, for the Level 3 coverage columns. The log is small, so
// one aggregate over the whole table is cheaper than a correlated lookup.
$logAgg = array();
if ($view === 'tiles' || $view === 'cust' || $view === 'activity') {
    $sqlLogAgg = "
        SELECT CLSHTO                                             AS SHIPTO,
               COUNT(*)                                           AS N,
               SUM(CASE WHEN CLTYPE = 'C' THEN 1 ELSE 0 END)      AS NCALL,
               SUM(CASE WHEN CLTYPE = 'E' THEN 1 ELSE 0 END)      AS NMAIL,
               MAX(CLTSTP)                                        AS LASTTS,
               MIN(CASE WHEN CLFUDT >= CURRENT DATE THEN CLFUDT END) AS NEXTFU,
               MIN(CASE WHEN CLFUDT <  CURRENT DATE THEN CLFUDT END) AS OVERFU
        FROM $BP_LOGLIB.BPCALLLOG
        GROUP BY CLSHTO
    ";
    foreach (bp_fetchAll($conn, $sqlLogAgg, 'contactRollup', $sqlErr, $timings) as $r) {
        $logAgg[trim((string)$r['SHIPTO'])] = array(
            'n'      => (int)$r['N'],
            'ncall'  => (int)$r['NCALL'],
            'nmail'  => (int)$r['NMAIL'],
            'lastts' => trim((string)$r['LASTTS']),
            'nextfu' => trim((string)$r['NEXTFU']),
            'overfu' => trim((string)$r['OVERFU']),
        );
    }
}

// Full history for one customer, newest first
$logRows = array();
if ($view === 'detail' && $fShipto !== '') {
    $sqlLog = "
        SELECT CLSEQ, CLSHTO, CLTYPE, CLUSER, CLTSTP, CLOUTC,
               CLFUDT, CLFUNR, CLNOTE, CLTIER, CLTGTQ
        FROM $BP_LOGLIB.BPCALLLOG
        WHERE CLSHTO = " . (int)$fShipto . "
        ORDER BY CLTSTP DESC, CLSEQ DESC
    ";
    $logRows = bp_fetchAll($conn, $sqlLog, 'contactLog', $sqlErr, $timings);
}

// Every note across every customer, for the COO view
$actRows = array();
if ($view === 'activity') {
    $actWho = ($bpSeeAll || empty($bpAllowed))
            ? ''
            : ' AND c.CMSLSM IN (' . implode(',', $bpAllowed) . ')';
    $sqlAct = "
        SELECT l.CLSEQ, l.CLSHTO, l.CLTYPE, l.CLUSER, l.CLTSTP, l.CLOUTC,
               l.CLFUDT, l.CLFUNR, l.CLNOTE, l.CLTIER, l.CLTGTQ,
               TRIM(c.CMCNA1) AS CUSTNAME, c.CMSLSM AS SLSM
        FROM $BP_LOGLIB.BPCALLLOG l
        LEFT JOIN SGHDSDATA.HDCUST c ON c.CMCUST = l.CLSHTO
        WHERE 1 = 1 $actWho
        ORDER BY l.CLTSTP DESC, l.CLSEQ DESC
    ";
    $actRows = bp_fetchAll($conn, $sqlAct, 'activity', $sqlErr, $timings);
}

$totalMs = array_sum($timings);

// Base query string for building drill links that keep the current context
function bp_url($params) {
    $keep = array();
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) { continue; }
        $keep[$k] = $v;
    }
    return '?' . http_build_query($keep);
}

// -- CSV export ---------------------------------------------------------------

if (isset($_GET['export'])) {
    $what  = strtolower(trim($_GET['export']));
    $stamp = date('Ymd_His');
    header('Content-Type: text/csv');

    if ($what === 'lines' && $sub !== null) {
        header('Content-Disposition: attachment; filename="BuyerPattern_OrderLines_'
             . $sub['shipto'] . '_' . $stamp . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, array('Ship-To #', 'Customer', 'Order #', 'Line #', 'Order Date',
                            'Order Type', 'Item', 'Description', 'Qty Ordered', 'Qty Shipped',
                            'Unit Price', 'Unit Factor', 'Shipment Rows', 'Line Amount'));
        foreach ($lineRows as $r) {
            fputcsv($out, array($sub['shipto'], $sub['name'],
                trim((string)$r['ORDNO']), trim((string)$r['LN']),
                bp_cymdIso($r['ORDDTE']), trim((string)$r['ORDTY']),
                trim((string)$r['ITEM']), trim((string)$r['ITEMDESC']),
                number_format((float)$r['QORD'], 0, '.', ''),
                number_format((float)$r['QSHIP'], 0, '.', ''),
                number_format((float)$r['PRICE'], 5, '.', ''),
                number_format((float)$r['UF'], 4, '.', ''),
                (int)$r['SHIPROWS'],
                number_format((float)$r['AMT'], 2, '.', '')));
        }
        fclose($out);
        exit;
    }

    if ($what === 'items' && $sub !== null) {
        header('Content-Disposition: attachment; filename="BuyerPattern_Products_'
             . $sub['shipto'] . '_' . $stamp . '.csv"');
        $out = fopen('php://output', 'w');
        $hdr = array('Ship-To #', 'Customer', 'Item', 'Description', 'Status',
                     'Years Bought', 'History Orders', 'Normally Buys Qty/Yr',
                     'Normal $/Yr', 'Lost $/Yr', 'Last Order Date', 'Last Unit Price');
        foreach ($yrs as $y) { $hdr[] = $y . ' $'; }
        foreach ($yrs as $y) { $hdr[] = $y . ' Qty'; }
        fputcsv($out, $hdr);
        foreach ($subItems as $it) {
            $row = array($sub['shipto'], $sub['name'], $it['item'], $it['desc'], $it['status'],
                         $it['yrsWith'], $it['histOrds'],
                         number_format($it['normalQty'], 0, '.', ''),
                         number_format($it['normal'], 2, '.', ''),
                         number_format($it['lossAmt'], 2, '.', ''),
                         bp_cymdIso($it['lastOrd']),
                         number_format($it['lastPrice'], 5, '.', ''));
            foreach ($yrs as $y) { $row[] = number_format($it['r' . $y], 2, '.', ''); }
            foreach ($yrs as $y) { $row[] = number_format($it['q' . $y], 0, '.', ''); }
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    if ($what === 'sku') {
        header('Content-Disposition: attachment; filename="BuyerPattern_StoppedSKUs_' . $stamp . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, array('Item', 'Description', 'Customers Stopped',
                            'Annual $ Stopped', 'Annual Qty'));
        foreach ($skuList as $s) {
            fputcsv($out, array($s['item'], $s['desc'], $s['custs'],
                                number_format($s['amt'], 2, '.', ''),
                                number_format($s['qty'], 0, '.', '')));
        }
        fclose($out);
        exit;
    }

    if ($what === 'class') {
        header('Content-Disposition: attachment; filename="BuyerPattern_Classes_' . $stamp . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, array('Class', 'Description', 'Customers', $tgtQLbl . ' Share %',
                            '$ At Stake', 'Stopped $/yr', 'T1','T2','T3','T4','T5'));
        foreach ($classList as $a) {
            fputcsv($out, array($a['code'], $a['desc'], $a['custs'],
                number_format(($a['hist'] > 0 ? $a['tq'] / $a['hist'] : 0) * 100, 1, '.', ''),
                number_format($a['stake'], 2, '.', ''),
                number_format($a['stop'],  2, '.', ''),
                $a['t'][1], $a['t'][2], $a['t'][3], $a['t'][4], $a['t'][5]));
        }
        fclose($out);
        exit;
    }

    if ($what === 'tier') {
        header('Content-Disposition: attachment; filename="BuyerPattern_Tiers_' . $stamp . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, array('Tier', 'Who They Are', 'At-Stake Basis', 'Customers',
                            '$ At Stake', 'Stopped $/yr', 'Reduced $/yr',
                            'Items Stopped', 'Open Order $'));
        for ($t = 1; $t <= 5; $t++) {
            fputcsv($out, array('Tier ' . $t, $tierRule[$t], $stakeRule[$t], $tierCount[$t],
                number_format($tierStake[$t], 2, '.', ''),
                number_format($tierStop[$t],  2, '.', ''),
                number_format($tierRed[$t],   2, '.', ''),
                $tierItems[$t],
                number_format($tierOpen[$t],  2, '.', '')));
        }
        fclose($out);
        exit;
    }

    // Default: the customer call list, honouring whatever filter is in effect
    $sel = !empty($listRows) ? $listRows : array();
    if (empty($sel)) {
        foreach ($custs as $c) { if ($c['tier'] > 0) { $sel[] = $c; } }
        usort($sel, function ($a, $b) {
            if ($a['stake'] == $b['stake']) return 0;
            return ($a['stake'] < $b['stake']) ? 1 : -1;
        });
    }
    header('Content-Disposition: attachment; filename="BuyerPattern_CallList_' . $stamp . '.csv"');
    $out = fopen('php://output', 'w');
    $hdr = array('Tier', 'Tier Rule', 'Ship-To #', 'Customer Name', 'Address', 'City', 'State',
                 'Zip', 'Phone', 'Class', 'Class Description', 'Salesperson #', 'Salesperson',
                 $tgtQLbl . ' Share %', $tgtQLbl . ' Years', 'Call By');
    if ($periodOn) {
        $hdr[] = $periodShort . ' $';
        $hdr[] = $periodShort . ' Orders';
    }
    foreach ($yrs as $y) { $hdr[] = $y . ' Sales'; }
    foreach ($yrs as $y) { $hdr[] = $y . ' ' . $tgtQLbl . ' Sales'; }
    $hdr = array_merge($hdr, array('Years Active', 'Normal Year', 'At Stake', 'At-Stake Basis',
                                   'Stopped $/yr', 'Reduced $/yr', 'Items Stopped',
                                   'Items Reduced', 'Open Orders', 'Open Order $',
                                   'Orders', 'Order Lines', 'Last Order Date'));
    fputcsv($out, $hdr);
    foreach ($sel as $c) {
        $row = array($c['tier'], $c['tier'] > 0 ? $tierRule[$c['tier']] : 'not on a call tier',
                     $c['shipto'], $c['name'], $c['addr1'], $c['city'], $c['state'], $c['zip'],
                     $c['phone'], $c['cls'], $c['clsdesc'], $c['slsm'], $c['slsmname'],
                     number_format($c['tqShare'] * 100, 1, '.', ''), $c['tqYears'], $c['callBy']);
        if ($periodOn) {
            $mr = isset($moRev[$c['shipto']]) ? $moRev[$c['shipto']] : null;
            $row[] = $mr ? number_format($mr['rev'], 2, '.', '') : '';
            $row[] = $mr ? $mr['ords'] : '';
        }
        foreach ($yrs as $y) { $row[] = number_format($c['byYear'][$y], 2, '.', ''); }
        foreach ($yrs as $y) { $row[] = number_format($c['tqByYear'][$y], 2, '.', ''); }
        $row = array_merge($row, array(
            $c['yrsWith'],
            number_format($c['normal'], 2, '.', ''),
            number_format($c['stake'],  2, '.', ''),
            $c['tier'] > 0 ? $stakeRule[$c['tier']] : '',
            number_format($c['stopAmt'], 2, '.', ''),
            number_format($c['redAmt'],  2, '.', ''),
            $c['stopN'], $c['redN'], $c['openOrds'],
            number_format($c['openAmt'], 2, '.', ''),
            $c['ordCnt'], $c['lineCnt'], bp_cymdIso($c['lastOrd'])));
        fputcsv($out, $row);
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

$statusClr = array('stopped'=>'#CC1F20', 'reduced'=>'#EA580C',
                   'steady'=>'#1DA032', 'one-off'=>'#6B7280');
?>
<table <?php echo $baseTable; ?>>
<tr valign="top">
<td class="content">

<style>
table[summary="banner"] { display:none !important; }
body { box-sizing:border-box !important; }
body > table { width:100% !important; max-width:none !important; table-layout:auto !important; }
td.content { width:calc(100vw - 155px) !important; max-width:none !important; box-sizing:border-box !important; }
/* Tables fill their container, and the container is capped (.bp-body) so there
   is never 1700px of slack to distribute. Two earlier attempts both looked
   wrong: width:100% with no cap pushed the numbers to the far edge and opened a
   gap mid-table, and width:auto left the table hugging the left of an empty
   page. Cap the container, fill it, and the space lands evenly. */
.bp-grid { width:100%; border-collapse:collapse; }
.bp-body { max-width:1280px; }
/* The wide grids need every pixel; they scroll in their own wrapper instead */
.bp-body-wide { max-width:none; }
.bp-grid thead th { background-color:#374151 !important; color:#fff !important;
                    font-weight:bold !important; cursor:pointer; user-select:none;
                    white-space:nowrap; padding:6px 10px; font-size:12px; text-align:left; }
.bp-grid thead th:hover { opacity:0.85; }
.bp-grid thead th.bp-asc::after  { content:' \25B2'; font-size:9px; }
.bp-grid thead th.bp-desc::after { content:' \25BC'; font-size:9px; }
.bp-grid tbody tr:nth-child(odd)  { background:#F7F7F7; }
.bp-grid tbody tr:nth-child(even) { background:#FFFFFF; }
.bp-grid tbody tr:hover           { background:#EFF6FF !important; }
.bp-grid tbody td { color:#111827 !important; padding:5px 10px; font-size:12px;
                    border-bottom:1px solid #E5E7EB; }
.bp-grid tbody td a, .bp-grid tfoot td a { color:#2563EB !important;
                    text-decoration:none !important; font-weight:bold !important; }
.bp-grid tbody td a:hover { text-decoration:underline !important; }
.bp-grid tfoot td { background:#E5E7EB; font-weight:bold; padding:5px 7px; font-size:12px;
                    border-top:2px solid #9CA3AF; }
/* Numeric columns: header must match the data, and must not hog width.
   ".bp-grid thead th" outranks a bare ".bp-r", so the header needs equal
   specificity or the numbers sit right while their heading sits left. */
.bp-r { text-align:right; }
.bp-grid thead th.bp-r,
.bp-grid tfoot td.bp-r { text-align:right; }
.bp-grid th.bp-r, .bp-grid td.bp-r { white-space:nowrap; }
/* Short text columns size to their content but may wrap if long */
.bp-grid th.bp-wide, .bp-grid td.bp-wide { white-space:normal; }
/* Sentence columns get a ceiling so one long phrase cannot stretch the table */
.bp-grid th.bp-txt, .bp-grid td.bp-txt { white-space:normal; max-width:300px; }
/* Codes that must never break mid-token - item numbers, order numbers, dates */
.bp-grid th.bp-nw, .bp-grid td.bp-nw { white-space:nowrap; }
/* Rows whose last order did not happen this year. Painted on the cells, since a
   td background always covers a tr background. */
.bp-grid tbody tr.bp-stale td       { background:#FEF3C7 !important; }
.bp-grid tbody tr.bp-stale:hover td { background:#FDE68A !important; }
.bp-stale-yr { color:#92400E !important; font-weight:bold; }
.bp-sec { margin:18px 0 6px; font-size:15px; font-weight:bold; color:#111827;
          border-left:4px solid #2563EB; padding-left:8px; }
.bp-sec span { font-weight:normal; font-size:12px; color:#6B7280; margin-left:8px; }
.bp-tiles { display:flex; flex-wrap:wrap; gap:1px; background:#D1D5DB;
            border:1px solid #D1D5DB; margin:8px 0 4px; }
.bp-tile { background:#fff; flex:1 1 190px; padding:12px 14px; min-width:190px; }
.bp-tile a { text-decoration:none !important; color:inherit !important; display:block; }
.bp-tile:hover { background:#EFF6FF; }
.bp-tk { font-size:10.5px; letter-spacing:0.09em; text-transform:uppercase;
         color:#6B7280; font-weight:bold; margin-bottom:5px; }
.bp-tv { font-size:27px; font-weight:bold; color:#111827; line-height:1.05; }
.bp-tn { font-size:11px; color:#4B5563; margin-top:5px; line-height:1.35; }
.bp-badge { display:inline-block; padding:2px 7px; border-radius:3px; font-size:11px;
            font-weight:bold; color:#fff !important; white-space:nowrap; }
/* The tier badge is a link inside a grid cell, and ".bp-grid tbody td a" would
   otherwise paint it link-blue on a coloured chip - unreadable. */
.bp-grid tbody td a.bp-badge,
.bp-grid tbody td a.bp-badge:hover,
.bp-grid tbody td .bp-badge {
    color:#fff !important; font-weight:bold !important;
    text-decoration:none !important; text-shadow:0 1px 1px rgba(0,0,0,0.35);
}
.bp-t1 { background:#CC1F20; } .bp-t2 { background:#EA580C; } .bp-t3 { background:#1DA032; }
.bp-t4 { background:#7B1FA2; } .bp-t5 { background:#0891B2; } .bp-t0 { background:#9CA3AF; }
.bp-crumb { background:#F3F4F6; border-bottom:1px solid #D1D5DB; padding:6px 12px;
            font-size:12px; color:#4B5563; }
.bp-crumb a { color:#2563EB !important; font-weight:bold; text-decoration:none !important; }
.bp-crumb a:hover { text-decoration:underline !important; }
.bp-card { display:flex; flex-wrap:wrap; gap:1px; background:#D1D5DB;
           border:1px solid #D1D5DB; margin:8px 0; }
.bp-cc { background:#fff; padding:10px 14px; flex:1 1 200px; }
.bp-cl { font-size:10.5px; letter-spacing:0.08em; text-transform:uppercase;
         color:#6B7280; font-weight:bold; margin-bottom:4px; }
.bp-cv { font-size:14px; color:#111827; font-weight:bold; }
.bp-note { margin:14px 0 8px; padding:10px 12px; background:#F9FAFB; border:1px solid #E5E7EB;
           font-size:11px; color:#4B5563; line-height:1.6; border-radius:3px; }
.bp-note b { color:#111827; }
.bp-err { color:#CC1F20; font-weight:bold; padding:8px; font-size:12px; }
.bp-btn { font-size:12px; padding:3px 12px; border-radius:3px; font-weight:bold;
          text-decoration:none !important; color:#fff !important; display:inline-block;
          white-space:nowrap; }
</style>

<!-- Full-width title bar -->
<div style="position:relative; left:-155px; width:calc(100% + 155px); box-sizing:border-box;
            display:flex; align-items:center;
            padding:10px 14px 10px calc(155px + 14px);
            background:linear-gradient(to right,
                #111827 0%, #1F2937 25%, #374151 55%, #4B5563 78%, #6B7280 100%);
            border-bottom:3px solid rgba(0,0,0,0.15);
            gap:10px; margin-bottom:0;">
  <h1 style="font-size:22px;color:#fff !important;margin:0;flex:1;font-weight:bold !important;
              text-shadow:0 1px 3px rgba(0,0,0,0.4);">
    Buyer Pattern
  </h1>
  <a href="<?php echo bp_h($_sgnHome . '/Welcome.php?baseVar=' . rawurlencode($_sgnBv) . '&eID=' . rawurlencode($_sgnEid) . '&portal=9999999999'); ?>"
     class="bp-btn" style="background:#06B6D4;border:1px solid #0891B2;">&#8592; Back to EIP</a>
  <a href="https://screen-graphics.com/" class="bp-btn"
     style="background:#CC1F20;border:1px solid #8b1010;">Logout</a>
</div>

<?php if (!empty($sqlErr)): ?>
  <?php foreach ($sqlErr as $e): ?>
  <p class="bp-err"><?php echo bp_h('SQL Error - ' . $e); ?></p>
  <?php endforeach; ?>
<?php endif; ?>

<!-- Breadcrumb -->
<div class="bp-crumb">
  <a href="?">Level 1 &middot; Headline</a>
<?php if ($view === 'activity'): ?>
  &nbsp;&rsaquo;&nbsp; <b>Contact activity</b>
<?php elseif ($view === 'cards'): ?>
  &nbsp;&rsaquo;&nbsp; <b>What a rep actually sees</b>
  <span style="color:#6B7280;">(Tier <?php echo ($fTier >= 1 && $fTier <= 5) ? $fTier : 1; ?>
  call cards)</span>
<?php elseif ($view === 'cust'): ?>
  &nbsp;&rsaquo;&nbsp; <b>Level 3 &middot; Customers</b>
  <span style="color:#6B7280;">(<?php echo $listTitle; ?>)</span>
<?php elseif ($view === 'detail' && $sub !== null): ?>
  &nbsp;&rsaquo;&nbsp; <a href="<?php echo bp_h(bp_url(array('view'=>'cust','status'=>'oncall'))); ?>">Level 3 &middot; Customers</a>
  &nbsp;&rsaquo;&nbsp; <b>Level 4 &middot; <?php echo bp_h($sub['shipto'] . ' ' . $sub['name']); ?></b>
<?php elseif ($view === 'lines' && $sub !== null): ?>
  &nbsp;&rsaquo;&nbsp; <a href="<?php echo bp_h(bp_url(array('view'=>'cust','status'=>'oncall'))); ?>">Level 3 &middot; Customers</a>
  &nbsp;&rsaquo;&nbsp; <a href="<?php echo bp_h(bp_url(array('view'=>'detail','shipto'=>$sub['shipto']))); ?>">Level 4 &middot; <?php echo bp_h($sub['name']); ?></a>
  &nbsp;&rsaquo;&nbsp; <b>Level 5 &middot; Order lines<?php echo $fItem !== '' ? ' for ' . bp_h($fItem) : ''; ?></b>
<?php else: ?>
  &nbsp;&nbsp;<span style="color:#6B7280;">Click any figure to open the customers behind it,
  then a customer to see every product, then a product to see the actual order lines.</span>
<?php endif; ?>
</div>

<!-- Status bar -->
<div style="display:flex;align-items:stretch;border-bottom:2px solid #D1D5DB;">
  <div style="flex:1;display:flex;flex-direction:column;">
    <div style="background:#2563EB;border-bottom:1px solid #1d4ed8;padding:4px 14px;
                display:flex;align-items:center;gap:14px;font-size:11px;color:#fff;flex:1;
                flex-wrap:wrap;">
      <span style="font-weight:bold;">Live SGHDSDATA</span>
      <span>Order Date <?php echo bp_cymdToDate(bp_cymd($hy[0], 1, 1)); ?> to
            <?php echo bp_cymdToDate($todayCymd); ?></span>
      <span style="background:#fff;border-radius:12px;padding:2px 10px;color:#2563EB !important;
                   font-weight:700;">Now in <?php echo $curQLbl . ' ' . $curY; ?> &middot;
            targeting <?php echo $tgtQLbl . ' ' . $tgtQY; ?></span>
      <span style="background:#fff;border-radius:12px;padding:2px 10px;color:#2563EB !important;
                   font-weight:700;"><?php echo bp_int($totalMs); ?> ms</span>
      <span id="bp-asof" style="background:#fff3cd;border:1px solid #f0c060;border-radius:12px;
                   padding:2px 10px;color:#856404 !important;font-weight:700;"></span>
      <span style="background:<?php echo $bpUser === '' ? '#FEE2E2' : '#fff'; ?>;border-radius:12px;
                   padding:2px 10px;font-weight:700;
                   color:<?php echo $bpUser === '' ? '#7F1D1D' : '#2563EB'; ?> !important;"
            title="Access comes from PROITRG.UDCDETAIL, system BUYPATTERN, code SALESPRSN">
        <?php if ($bpUser === ''): ?>
          profile not identified
        <?php else: ?>
          <?php echo bp_h($bpUser); ?> &middot;
          <?php echo $bpSeeAll ? 'all accounts'
                : (empty($bpAllowed) ? 'no accounts'
                : 'sls ' . bp_h(implode(', ', $bpAllowed))); ?>
        <?php endif; ?>
      </span>
      <a href="<?php echo bp_h(bp_url(array('view'=>'activity'))); ?>"
         style="background:#fff;border-radius:12px;padding:2px 10px;font-weight:700;
                color:#7B1FA2 !important;text-decoration:none;">Contact activity</a>
    </div>
    <div style="display:flex;align-items:center;gap:14px;padding:6px 10px;
                background:#F7F7F7;font-size:12px;flex:1;flex-wrap:wrap;">
<?php if ($isSingle && $sub !== null): ?>
      <b><?php echo bp_h($sub['shipto'] . '  ' . $sub['name']); ?></b>
      <span style="color:#9CA3AF;">|</span>
      <?php echo $hy[0]; ?>-<?php echo $hy[2]; ?> sales
      <b><?php echo bp_money($sub['histRev']); ?></b>
      <span style="color:#9CA3AF;">|</span>
      <?php echo $curY; ?> to date <b><?php echo bp_money($sub['curRev']); ?></b>
      <span style="color:#9CA3AF;">|</span>
      at stake <b><?php echo bp_money($sub['stake']); ?></b>
      <span style="color:#9CA3AF;">|</span>
      <?php echo bp_int($sub['ordCnt']); ?> orders,
      <?php echo bp_int($sub['lineCnt']); ?> lines
      <span style="margin-left:auto;color:#6B7280;">
        Scoped to this ship-to, so the query reads one customer, not all of them.</span>
<?php else: ?>
      <b><?php echo bp_int($tileHistory); ?></b> ship-tos with history
      <span style="color:#9CA3AF;">|</span>
      <b><?php echo bp_int($tierTotal); ?></b> on the call lists
      <span style="color:#9CA3AF;">|</span>
      <b><?php echo bp_money($tileAtStake); ?></b> at stake
      <span style="color:#9CA3AF;">|</span>
      <b><?php echo bp_money($tileStopAmt); ?></b>/yr stopped
      <span style="color:#9CA3AF;">|</span>
      <?php echo $hy[0]; ?>-<?php echo $hy[2]; ?> sales
      <b><?php echo bp_money($totalHistRev); ?></b>, <?php echo $curY; ?> to date
      <b><?php echo bp_money($totalCurRev); ?></b>
<?php endif; ?>
    </div>
  </div>
  <div style="display:flex;flex-direction:column;align-items:stretch;justify-content:center;
              gap:4px;padding:6px 10px;background:#F7F7F7;border-left:2px solid #D1D5DB;">
    <button onclick="location.reload();"
            style="font-size:12px;padding:3px 14px;cursor:pointer;border:1px solid #4a0f6e;
                   border-radius:3px;background:#7B1FA2;color:#fff;font-weight:bold;
                   white-space:nowrap;">&#x21BB; Refresh</button>
<?php
  $expParams = $_GET;
  if ($view === 'detail' || $view === 'lines') {
      $expParams['export'] = ($view === 'lines') ? 'lines' : 'items';
  } else {
      $expParams['export'] = 'cust';
  }
?>
    <a href="<?php echo bp_h('?' . http_build_query($expParams)); ?>" class="bp-btn"
       style="background:#1DA032;text-align:center;">&#8595; Export this view</a>
  </div>
</div>

<div class="bp-body<?php echo in_array($view, array("cust","lines"), true) ? " bp-body-wide" : ""; ?>">

<?php if ($view === 'tiles'): ?>

<!-- ===================== LEVEL 1 ===================== -->
<div class="bp-sec">Level 1 &middot; The headline
  <span>Every figure queried live at page load &middot; click any tile</span></div>
<div class="bp-tiles">
  <div class="bp-tile">
    <a href="<?php echo bp_h(bp_url(array('view'=>'cust'))); ?>">
      <div class="bp-tk">Ship-tos with history</div>
      <div class="bp-tv"><?php echo bp_int($tileHistory); ?></div>
      <div class="bp-tn">Bought at least once <?php echo $hy[0]; ?>-<?php echo $hy[2]; ?>.</div>
    </a>
  </div>
  <div class="bp-tile">
    <a href="<?php echo bp_h(bp_url(array('view'=>'cust','status'=>'silent'))); ?>">
      <div class="bp-tk">Silent so far in <?php echo $curY; ?></div>
      <div class="bp-tv"><?php echo bp_int($tileSilent); ?></div>
      <div class="bp-tn">Nothing at all in <?php echo $curY; ?>. They carried
          <?php echo bp_money($tileSilentRev); ?> across <?php echo $hy[0]; ?>-<?php echo $hy[2]; ?>.</div>
    </a>
  </div>
  <div class="bp-tile">
    <a href="<?php echo bp_h(bp_url(array('view'=>'cust','status'=>'everytq'))); ?>">
      <div class="bp-tk">Order every <?php echo $tgtQLbl; ?></div>
      <div class="bp-tv"><?php echo bp_int($tileEveryTQ); ?></div>
      <div class="bp-tn"><?php echo $tgtQLbl; ?> orders in <?php echo $hy[0]; ?> and
          <?php echo $hy[1]; ?> and <?php echo $hy[2]; ?> -
          <?php echo bp_money($tileEveryTQRev); ?> of <?php echo $tgtQLbl; ?> business.</div>
    </a>
  </div>
  <div class="bp-tile">
    <a href="<?php echo bp_h(bp_url(array('view'=>'cust','status'=>'tqwtd'))); ?>">
      <div class="bp-tk"><?php echo $tgtQLbl; ?>-weighted customers</div>
      <div class="bp-tv"><?php echo bp_int($tileTQWtd); ?></div>
      <div class="bp-tn">Put <?php echo bp_pct($BP_SHARE_TILE); ?> or more of their revenue
          into <?php echo $tgtQLbl; ?>.</div>
    </a>
  </div>
  <div class="bp-tile">
    <a href="#bp-skus">
      <div class="bp-tk">Products stopped</div>
      <div class="bp-tv"><?php echo bp_moneyK($tileStopAmt); ?>/yr</div>
      <div class="bp-tn"><?php echo bp_int($tileStopItems); ?> repeat items dropped, plus
          <?php echo bp_moneyK($tileRedAmt); ?>/yr reduced.</div>
    </a>
  </div>
  <div class="bp-tile">
    <a href="<?php echo bp_h(bp_url(array('view'=>'cust','status'=>'oncall'))); ?>">
      <div class="bp-tk">Total at stake</div>
      <div class="bp-tv"><?php echo bp_moneyK($tileAtStake); ?></div>
      <div class="bp-tn"><?php echo bp_int($tierTotal); ?> customers on the lists. Lost years
          for those who stopped, shortfalls for those who slowed.</div>
    </a>
  </div>
</div>

<!-- ===================== The seasonal shape (slide 4) ===================== -->
<div class="bp-sec" id="bp-season">The seasonal shape
  <span>Where revenue lands, and where inside <?php echo $tgtQLbl; ?> the orders get placed</span></div>
<div style="display:flex;flex-wrap:wrap;gap:14px;align-items:stretch;">
  <div style="flex:2 1 520px;background:#fff;border:1px solid #D1D5DB;padding:10px 12px;">
    <div style="font-size:12px;font-weight:bold;color:#111827;margin-bottom:2px;">
      Revenue by quarter, <?php echo $hy[0]; ?> to <?php echo $curY; ?></div>
    <div style="font-size:11px;color:#6B7280;margin-bottom:8px;">
      <?php echo $tgtQLbl; ?> in orange. <?php echo $curY; ?> is partial - <?php echo $curQLbl; ?>
      is still open and later quarters have not happened yet.</div>
    <div style="height:230px;"><canvas id="bp-qtrChart" style="cursor:pointer;"></canvas></div>
    <div style="font-size:11px;color:#4B5563;margin-top:7px;border-top:1px solid #E5E7EB;
                padding-top:6px;">
      <b>Click a bar</b> for everyone who ordered in that quarter:
<?php foreach ($yrs as $y): ?>
      <span style="display:inline-block;margin:2px 10px 2px 0;white-space:nowrap;">
        <span style="color:#6B7280;"><?php echo $y; ?></span>
<?php for ($q = 1; $q <= 4; $q++): ?>
        <a href="<?php echo bp_h(bp_url(array('view'=>'cust','py'=>$y,'pq'=>$q))); ?>"
           style="color:#2563EB;font-weight:bold;text-decoration:none;margin-left:3px;"
           title="Q<?php echo $q . ' ' . $y; ?>: <?php echo bp_money($qtrByYear[$y][$q]); ?>">Q<?php echo $q; ?></a>
<?php endfor; ?>
      </span>
<?php endforeach; ?>
    </div>
  </div>
  <div style="flex:1 1 320px;background:#fff;border:1px solid #D1D5DB;padding:10px 12px;">
    <div style="font-size:12px;font-weight:bold;color:#111827;margin-bottom:2px;">
      Inside <?php echo $tgtQLbl; ?>, month by month</div>
    <div style="font-size:11px;color:#6B7280;margin-bottom:8px;">
      All <?php echo $tgtQLbl; ?> orders <?php echo $hy[0]; ?>-<?php echo $hy[2]; ?> combined.
<?php
  $peakM = 0; $peakV = -1;
  foreach ($tqMonths as $m => $a) { if ($a['rev'] > $peakV) { $peakV = $a['rev']; $peakM = $m; } }
  if ($peakM > 0):
?>
      <?php echo $monName[$peakM]; ?> carries the most:
      <?php echo bp_money($tqMonths[$peakM]['rev']); ?> from
      <?php echo bp_int($tqMonths[$peakM]['ords']); ?> orders.
<?php endif; ?>
    </div>
    <div style="height:230px;"><canvas id="bp-monChart" style="cursor:pointer;"></canvas></div>
    <div style="font-size:11px;color:#4B5563;margin-top:7px;border-top:1px solid #E5E7EB;
                padding-top:6px;">
      <b>Click a month</b> for the customers whose <?php echo $tgtQLbl; ?> order lands then:
<?php foreach ($tqMonths as $m => $a): ?>
      <a href="<?php echo bp_h(bp_url(array('view'=>'cust','mo'=>$m))); ?>"
         style="color:#2563EB;font-weight:bold;text-decoration:none;margin-left:6px;">
        <?php echo $monName[$m]; ?>
        <span style="color:#6B7280;font-weight:normal;">(<?php echo bp_int($a['custs']); ?>)</span></a>
<?php endforeach; ?>
    </div>
  </div>
</div>
<div style="font-size:11px;color:#6B7280;padding:5px 2px;">
  Quarters keyed on the <b>order</b> date, not the ship date - the whole point is moving when
  the customer places the order. This is why the call-by dates sit
  <?php echo $BP_CALL_LEAD; ?> days ahead of each customer's own average kickoff.
</div>

<!-- ===================== LEVEL 2a - tiers ===================== -->
<div class="bp-sec" id="bp-tiers">Level 2 &middot; The call lists
  <span>Click a tier to see its customers</span></div>
<div style="overflow-x:auto;">
<table class="bp-grid" id="bp-tiergrid">
  <thead>
    <tr>
      <th>Tier</th><th class="bp-txt">Who they are</th><th class="bp-txt">What "at stake" means here</th>
      <th class="bp-r">Customers</th><th class="bp-r">$ at stake</th>
      <th class="bp-r">Stopped $/yr</th><th class="bp-r">Reduced $/yr</th>
      <th class="bp-r">Items stopped</th><th class="bp-r">Open order $</th>
    </tr>
  </thead>
  <tbody>
<?php for ($t = 1; $t <= 5; $t++): ?>
    <tr>
      <td style="white-space:nowrap;">
        <a href="<?php echo bp_h(bp_url(array('view'=>'cust','tier'=>$t))); ?>"
           class="bp-badge bp-t<?php echo $t; ?>">Tier <?php echo $t; ?></a>
        <a href="<?php echo bp_h(bp_url(array('view'=>'cards','tier'=>$t))); ?>"
           title="What a rep actually sees - call cards for this tier"
           style="font-size:10.5px;color:#2563EB !important;text-decoration:none;">call cards</a>
      </td>
      <td class="bp-txt"><?php echo bp_h($tierRule[$t]); ?></td>
      <td class="bp-txt" style="color:#6B7280;"><?php echo bp_h($stakeRule[$t]); ?></td>
      <td class="bp-r"><a href="<?php echo bp_h(bp_url(array('view'=>'cust','tier'=>$t))); ?>"><?php echo bp_int($tierCount[$t]); ?></a></td>
      <td class="bp-r"><?php echo bp_money($tierStake[$t]); ?></td>
      <td class="bp-r"><?php echo bp_money($tierStop[$t]); ?></td>
      <td class="bp-r"><?php echo ($t === 1 || $t === 4) ? '&mdash;' : bp_money($tierRed[$t]); ?></td>
      <td class="bp-r"><?php echo bp_int($tierItems[$t]); ?></td>
      <td class="bp-r"><?php echo $tierOpen[$t] > 0 ? bp_money($tierOpen[$t]) : '&mdash;'; ?></td>
    </tr>
<?php endfor; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="3">TOTAL &mdash;
        <a href="<?php echo bp_h(bp_url(array('view'=>'cust','status'=>'oncall'))); ?>">open the full call list</a></td>
      <td class="bp-r"><?php echo bp_int($tierTotal); ?></td>
      <td class="bp-r"><?php echo bp_money($tileAtStake); ?></td>
      <td class="bp-r"><?php echo bp_money(array_sum($tierStop)); ?></td>
      <td class="bp-r"><?php echo bp_money(array_sum($tierRed)); ?></td>
      <td class="bp-r"><?php echo bp_int(array_sum($tierItems)); ?></td>
      <td class="bp-r"><?php echo bp_money($tileOpenAmt); ?></td>
    </tr>
  </tfoot>
</table>
</div>
<div style="font-size:11px;color:#6B7280;padding:5px 2px;">
  Tiers 1 and 4 have no "reduced" figure - those customers bought nothing at all in
  <?php echo $curY; ?>. Open order $ is shown for information and never suppresses a call:
  <?php echo bp_int($tileOpenOrds); ?> open orders worth <?php echo bp_money($tileOpenAmt); ?>
  sit against customers on these lists.
  <a href="?export=tier" style="color:#1DA032;font-weight:bold;">Export tiers</a>
</div>

<!-- ===================== LEVEL 2b - classes ===================== -->
<div class="bp-sec" id="bp-classes">Level 2 &middot; Opportunity by customer class
  <span>Click a class to see its customers &middot; any header sorts</span></div>
<div style="overflow-x:auto;">
<table class="bp-grid" id="bp-clsgrid">
  <thead>
    <tr>
      <th>Class</th><th class="bp-wide">Customer class</th>
      <th class="bp-r">Customers</th><th class="bp-r"><?php echo $tgtQLbl; ?> share</th>
      <th class="bp-r">$ at stake</th><th class="bp-r">Stopped $/yr</th>
      <th class="bp-r">T1</th><th class="bp-r">T2</th><th class="bp-r">T3</th>
      <th class="bp-r">T4</th><th class="bp-r">T5</th>
    </tr>
  </thead>
  <tbody>
<?php foreach ($classList as $a):
    if ($a['stake'] <= 0 && $a['stop'] <= 0) { continue; }
    $shr = ($a['hist'] > 0) ? $a['tq'] / $a['hist'] : 0.0;
    $clsUrl = bp_url(array('view'=>'cust','cls'=>$a['code']));
?>
    <tr>
      <td><a href="<?php echo bp_h($clsUrl); ?>"><?php echo bp_h($a['code']); ?></a></td>
      <td><?php echo bp_h($a['desc']); ?></td>
      <td class="bp-r"><a href="<?php echo bp_h($clsUrl); ?>"><?php echo bp_int($a['custs']); ?></a></td>
      <td class="bp-r" data-val="<?php echo number_format($shr, 4, '.', ''); ?>"><?php echo bp_pct($shr); ?></td>
      <td class="bp-r"><?php echo bp_money($a['stake']); ?></td>
      <td class="bp-r"><?php echo bp_money($a['stop']); ?></td>
<?php for ($t = 1; $t <= 5; $t++): ?>
      <td class="bp-r"><?php echo $a['t'][$t]
          ? '<a href="' . bp_h(bp_url(array('view'=>'cust','cls'=>$a['code'],'tier'=>$t))) . '">'
            . bp_int($a['t'][$t]) . '</a>'
          : '&mdash;'; ?></td>
<?php endfor; ?>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<div style="font-size:11px;color:#6B7280;padding:5px 2px;">
  <?php echo $tgtQLbl; ?> share = that class's <?php echo $tgtQLbl; ?> revenue as a percent of
  its <?php echo $hy[0]; ?>-<?php echo $hy[2]; ?> revenue. T1-T5 are counts of customers in
  that tier, each clickable.
  <a href="?export=class" style="color:#1DA032;font-weight:bold;">Export classes</a>
</div>

<!-- ===================== LEVEL 2c - stopped SKUs ===================== -->
<div class="bp-sec" id="bp-skus">Level 2 &middot; What they stopped ordering
  <span>Top 25 of <?php echo bp_int(count($skuList)); ?> stopped repeat items &middot; click one
  to see who dropped it</span></div>
<div style="overflow-x:auto;">
<table class="bp-grid" id="bp-skugrid">
  <thead>
    <tr>
      <th class="bp-nw">Item</th><th class="bp-wide">Description</th>
      <th class="bp-r">Customers<br>stopped</th>
      <th class="bp-r">Annual $<br>stopped</th>
      <th class="bp-r">Annual<br>qty</th>
    </tr>
  </thead>
  <tbody>
<?php $shown = 0; foreach ($skuList as $s): if ($shown++ >= 25) break;
      $skuUrl = bp_url(array('view'=>'cust','item'=>$s['item'])); ?>
    <tr>
      <td class="bp-nw"><a href="<?php echo bp_h($skuUrl); ?>"><?php echo bp_h($s["item"]); ?></a></td>
      <td class="bp-wide"><?php echo bp_h($s['desc']); ?></td>
      <td class="bp-r"><a href="<?php echo bp_h($skuUrl); ?>"><?php echo bp_int($s['custs']); ?></a></td>
      <td class="bp-r"><?php echo bp_money($s['amt']); ?></td>
      <td class="bp-r"><?php echo bp_qty($s['qty']); ?></td>
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
  <?php echo bp_int(count($skuList)); ?> items</a>
</div>

<?php elseif ($view === 'activity'): ?>

<!-- ===================== Contact activity (the COO view) ===================== -->
<div class="bp-sec">Contact activity
  <span><?php echo bp_int(count($actRows)); ?> entr<?php echo count($actRows) === 1 ? "y" : "ies"; ?>
  <?php echo $bpSeeAll ? 'across every account' : 'across the accounts you cover'; ?>
  &middot; append-only, stamped by the IBM i</span></div>
<div style="overflow-x:auto;">
<table class="bp-grid" id="bp-actgrid">
  <thead>
    <tr><th>When</th><th>Who</th><th>Type</th><th>Ship-to</th><th class="bp-wide">Customer</th>
        <th class="bp-r">Sls</th><th>Salesperson</th><th class="bp-r">Tier</th><th>Outcome</th>
        <th>Follow-up</th><th class="bp-wide">Note</th></tr>
  </thead>
  <tbody>
<?php if (empty($actRows)): ?>
    <tr><td colspan="11" style="text-align:center;padding:20px;">
      No contact has been logged yet.</td></tr>
<?php endif; ?>
<?php foreach ($actRows as $ar):
      $lt = trim((string)$ar['CLTYPE']);
      $tn = ($lt === 'C') ? 'Call' : (($lt === 'E') ? 'Email' : 'Note');
      $fu = trim((string)$ar['CLFUDT']);
      $overdue = ($fu !== '' && $fu < date('Y-m-d'));
      $sh = trim((string)$ar['CLSHTO']); ?>
    <tr>
      <td style="white-space:nowrap;"><?php echo bp_h(substr(trim((string)$ar['CLTSTP']), 0, 19)); ?></td>
      <td><b><?php echo bp_h(trim((string)$ar['CLUSER'])); ?></b></td>
      <td><?php echo bp_h($tn); ?></td>
      <td><a href="<?php echo bp_h(bp_url(array('view'=>'detail','shipto'=>$sh))); ?>"><?php echo bp_h($sh); ?></a></td>
      <td class="bp-wide"><?php echo bp_h(trim((string)$ar['CUSTNAME'])); ?></td>
      <td class="bp-r"><?php echo (int)$ar["SLSM"]; ?></td>
      <td class="bp-nw"><?php $an = (int)$ar["SLSM"];
          echo bp_h(isset($slsName[$an]) ? $slsName[$an] : ""); ?></td>
      <td class="bp-r"><?php echo (int)$ar['CLTIER'] > 0 ? 'T' . (int)$ar['CLTIER'] : '&mdash;'; ?></td>
      <td><?php echo bp_h(trim((string)$ar['CLOUTC'])); ?></td>
      <td style="white-space:nowrap;<?php echo $overdue ? 'color:#CC1F20 !important;font-weight:bold;' : ''; ?>">
        <?php if ($fu !== ''): echo bp_h(bp_mdy($fu)); echo $overdue ? ' (overdue)' : '';
              else: echo '<span style="color:#6B7280;">' . bp_h(trim((string)$ar['CLFUNR'])) . '</span>';
              endif; ?></td>
<?php   $noteFull = trim((string)$ar['CLNOTE']);
        $noteClip = bp_clip($noteFull, 150); ?>
      <td class="bp-wide" title="<?php echo bp_h($noteFull); ?>">
        <?php echo $noteFull !== "" ? bp_h($noteClip)
             : "<span style=\"color:#9CA3AF;\">(nothing said)</span>"; ?>
        <?php if ($noteClip !== $noteFull): ?>
          <a href="<?php echo bp_h(bp_url(array('view'=>'detail','shipto'=>$sh))); ?>#bp-log"
             style="color:#2563EB;font-weight:bold;text-decoration:none;"
             title="Open the customer to read the whole note">more</a>
        <?php endif; ?>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<div style="font-size:11px;color:#6B7280;padding:5px 2px;">
  Newest first, every entry ever logged - no row limit. Notes are clipped to 150 characters for
  scanning; hover for the full text, or click <b>more</b> to open the customer and read it in
  full. Red follow-up dates are already past. Nothing here can be edited or deleted - a
  correction is a new note against the same customer.
</div>

<?php elseif ($view === 'cards'): ?>

<!-- ============ What a rep actually sees (slide 8) ============ -->
<?php $tw = ($fTier >= 1 && $fTier <= 5) ? $fTier : 1; ?>
<div class="bp-sec">What a rep actually sees
  <span>Tier <?php echo $tw; ?>, top <?php echo bp_int(count($cardCusts)); ?> by dollars at
  stake &middot; ship-to and address, the <?php echo $tgtQLbl; ?> pattern year by year, and
  each product with the exact quantity and date they last bought it</span></div>
<div style="padding:2px 2px 8px;font-size:12px;">
  Tier:
<?php for ($t = 1; $t <= 5; $t++): ?>
  <a href="<?php echo bp_h(bp_url(array('view'=>'cards','tier'=>$t,'limit'=>$fLimit))); ?>"
     class="bp-badge bp-t<?php echo $t; ?>"
     style="margin-right:4px;<?php echo $t === $tw ? 'outline:2px solid #111827;' : 'opacity:0.55;'; ?>">Tier <?php echo $t; ?></a>
<?php endfor; ?>
  <span style="color:#9CA3AF;margin:0 8px;">|</span>
  Show:
<?php foreach (array(12, 24, 48) as $n): ?>
  <a href="<?php echo bp_h(bp_url(array('view'=>'cards','tier'=>$tw,'limit'=>$n))); ?>"
     style="color:#2563EB;font-weight:<?php echo $n === $fLimit ? 'bold' : 'normal'; ?>;
            text-decoration:none;margin-right:6px;"><?php echo $n; ?></a>
<?php endforeach; ?>
</div>

<?php if (empty($cardCusts)): ?>
<div class="bp-note" style="background:#FEF3C7;border-color:#F0C060;color:#78350F;">
  No customers in tier <?php echo $tw; ?>.
</div>
<?php endif; ?>

<div style="display:flex;flex-wrap:wrap;gap:12px;">
<?php foreach ($cardCusts as $c):
    $items = isset($itemsByShip[$c['shipto']]) ? $itemsByShip[$c['shipto']] : array();
    $stoppedItems = array();
    foreach ($items as $it) {
        if ($it['status'] === 'stopped' || $it['status'] === 'reduced') { $stoppedItems[] = $it; }
    }
    $stoppedItems = array_slice($stoppedItems, 0, 5);
    $dUrl = bp_url(array('view'=>'detail','shipto'=>$c['shipto']));
?>
  <div style="flex:1 1 430px;min-width:400px;background:#fff;border:1px solid #D1D5DB;
              border-top:3px solid <?php echo $statusClr['stopped']; ?>;">
    <!-- name / address / phone -->
    <div style="padding:9px 12px;border-bottom:1px solid #E5E7EB;">
      <div style="font-size:13px;font-weight:bold;color:#111827;">
        <a href="<?php echo bp_h($dUrl); ?>" style="color:#2563EB;text-decoration:none;">
          <?php echo bp_h($c['shipto']); ?></a>
        &nbsp;<?php echo bp_h($c['name']); ?>
      </div>
      <div style="font-size:11.5px;color:#4B5563;margin-top:2px;">
        <?php echo bp_h(trim($c['addr1'])); ?><?php echo $c['addr1'] !== '' ? ', ' : ''; ?><?php
              echo bp_h(trim($c['city'] . ', ' . $c['state'] . ' ' . $c['zip'], ', ')); ?>
      </div>
      <div style="font-size:12px;font-weight:bold;color:#111827;margin-top:3px;">
        <?php echo $c['phone'] !== '' ? bp_h($c['phone']) : 'no phone on file'; ?>
        <span style="font-weight:normal;color:#6B7280;">
          &middot; <?php echo bp_h($c['cls']); ?>
          &middot; Sls <?php echo (int)$c['slsm']; ?>
          <?php echo $c['slsmname'] !== '' ? bp_h($c['slsmname']) : ''; ?></span>
      </div>
    </div>
    <!-- the pattern -->
    <div style="padding:8px 12px;background:#F9FAFB;border-bottom:1px solid #E5E7EB;">
      <div style="font-size:10px;letter-spacing:0.08em;text-transform:uppercase;
                  color:#6B7280;font-weight:bold;margin-bottom:4px;">
        <?php echo $tgtQLbl; ?> pattern</div>
      <div style="font-size:12px;color:#111827;">
<?php foreach ($hy as $y):
        $tq = $c['tqByYear'][$y]; $no = $c['tqOrd'][$y];
        $fm = isset($c['tqFirst'][$y]) && $c['tqFirst'][$y] > 0
            ? $monName[intval(($c['tqFirst'][$y] % 10000) / 100)] : '';
?>
        <span style="display:inline-block;margin-right:14px;">
          <b><?php echo $y; ?>:</b>
          <?php if ($no > 0): ?>
            <?php echo bp_moneyK($tq); ?>
            <?php echo $fm !== '' ? bp_h($fm) : ''; ?>
            (<?php echo bp_int($no); ?> ord)
          <?php else: ?>
            <span style="color:#9CA3AF;">none</span>
          <?php endif; ?>
        </span>
<?php endforeach; ?>
      </div>
    </div>
    <!-- at stake / call by -->
    <div style="display:flex;border-bottom:1px solid #E5E7EB;">
      <div style="flex:1;padding:8px 12px;border-right:1px solid #E5E7EB;">
        <div style="font-size:10px;letter-spacing:0.08em;text-transform:uppercase;
                    color:#6B7280;font-weight:bold;">At stake</div>
        <div style="font-size:17px;font-weight:bold;color:#111827;">
          <?php echo bp_money($c['stake']); ?></div>
      </div>
      <?php $overdue = ($c['callBy'] !== '' && $c['callBy'] < date('Y-m-d')); ?>
      <div style="flex:1;padding:8px 12px;border-right:1px solid #E5E7EB;
                  background:#FEF08A;box-shadow:inset 0 0 0 1px #EAB308;">
        <div style="font-size:10px;letter-spacing:0.08em;text-transform:uppercase;
                    color:#713F12;font-weight:bold;">Call by</div>
        <div style="font-size:17px;font-weight:bold;
                    color:<?php echo $overdue ? '#B91C1C' : '#111827'; ?>;">
          <?php echo $c['callBy'] !== '' ? bp_h(bp_mdy($c['callBy'])) : '&mdash;'; ?></div>
      </div>
      <div style="flex:1;padding:8px 12px;">
        <div style="font-size:10px;letter-spacing:0.08em;text-transform:uppercase;
                    color:#6B7280;font-weight:bold;"><?php echo $curY; ?> so far</div>
        <div style="font-size:17px;font-weight:bold;
                    color:<?php echo $c['curRev'] > 0 ? '#111827' : '#CC1F20'; ?>;">
          <?php echo $c['curRev'] > 0 ? bp_money($c['curRev']) : 'nothing'; ?></div>
      </div>
    </div>
    <!-- what they stopped, with last qty and date -->
    <div style="padding:8px 12px;">
      <div style="font-size:10px;letter-spacing:0.08em;text-transform:uppercase;
                  color:#6B7280;font-weight:bold;margin-bottom:5px;">
        Stopped or reduced &mdash; last bought</div>
<?php if (empty($stoppedItems)): ?>
      <div style="font-size:12px;color:#6B7280;">No repeat items flagged.</div>
<?php else: ?>
      <table style="width:100%;border-collapse:collapse;font-size:11.5px;">
<?php foreach ($stoppedItems as $it):
        $lb = isset($lastBuy[$c['shipto']][$it['item']]) ? $lastBuy[$c['shipto']][$it['item']] : null; ?>
        <tr>
          <td style="padding:2px 6px 2px 0;white-space:nowrap;">
            <a href="<?php echo bp_h(bp_url(array('view'=>'lines','shipto'=>$c['shipto'],'item'=>$it['item']))); ?>"
               style="color:#2563EB;font-weight:bold;text-decoration:none;"><?php echo bp_h($it['item']); ?></a></td>
          <td style="padding:2px 6px;color:#4B5563;"><?php echo bp_h($it['desc']); ?></td>
          <td style="padding:2px 0 2px 6px;text-align:right;white-space:nowrap;color:#111827;">
            <?php if ($lb !== null): ?>
              <b><?php echo bp_qty($lb['qty']); ?></b> on <?php echo bp_h(bp_cymdToDate($lb['date'])); ?>
            <?php else: ?>
              <span style="color:#9CA3AF;">&mdash;</span>
            <?php endif; ?>
          </td>
          <td style="padding:2px 0 2px 8px;text-align:right;white-space:nowrap;
                     color:<?php echo $statusClr[$it['status']]; ?>;font-weight:bold;">
            <?php echo bp_money($it['lossAmt']); ?>/yr</td>
        </tr>
<?php endforeach; ?>
      </table>
<?php endif; ?>
      <div style="margin-top:6px;font-size:11px;">
        <a href="<?php echo bp_h($dUrl); ?>" style="color:#2563EB;font-weight:bold;">
          All <?php echo bp_int(count($items)); ?> products &rarr;</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<div style="font-size:11px;color:#6B7280;padding:8px 2px;">
  Call-by dates in red are already past. Quantities and dates are the customer's most recent
  order for that item, taken from the order lines themselves - click an item to see them.
  Contact names are not shown: CRCNTM holds a real person for only a small share of ship-tos,
  so the phone number on the ship-to record is the reliable route.
</div>

<?php elseif ($view === 'cust'): ?>

<!-- ===================== LEVEL 3 - customer list ===================== -->
<div class="bp-sec">Level 3 &middot; <?php echo bp_int(count($listRows)); ?>
  customer<?php echo count($listRows) === 1 ? '' : 's'; ?>
  <span>Click a ship-to for every product it buys &middot; any header sorts</span></div>
<?php if ($periodOn): ?>
<div style="padding:2px 2px 8px;font-size:12px;">
<?php
  // Keep whichever period is active when switching tier, and vice versa
  $pKeep = ($fMo > 0) ? array('mo' => $fMo) : array('py' => $fPy, 'pq' => $fPq);
?>
<?php if ($fMo > 0): ?>
  <b><?php echo $monFull[$fMo]; ?> cohort</b> - their <?php echo $tgtQLbl; ?> order lands in
  <?php echo $monName[$fMo]; ?>, so with a <?php echo $BP_CALL_LEAD; ?>-day lead these are the
  calls to make now.
<?php else: ?>
  <b><?php echo bp_h($periodLong); ?></b> - everyone who placed an order in that quarter.
  Cross it with a tier to turn it into a call list.
<?php endif; ?>
  Narrow by tier:
  <a href="<?php echo bp_h(bp_url(array_merge(array('view'=>'cust'), $pKeep))); ?>"
     style="color:#2563EB;font-weight:<?php echo $fTier === 0 ? 'bold' : 'normal'; ?>;
            text-decoration:none;margin:0 4px;">all</a>
<?php for ($t = 1; $t <= 5; $t++): ?>
  <a href="<?php echo bp_h(bp_url(array_merge(array('view'=>'cust','tier'=>$t), $pKeep))); ?>"
     class="bp-badge bp-t<?php echo $t; ?>"
     style="margin-right:3px;<?php echo $fTier === $t ? 'outline:2px solid #111827;' : 'opacity:0.55;'; ?>">T<?php echo $t; ?></a>
<?php endfor; ?>
  <span style="color:#9CA3AF;margin:0 6px;">|</span>
<?php if ($fMo > 0): ?>
  <?php foreach ($tqMonths as $m => $a): if ($m === $fMo) continue; ?>
  <a href="<?php echo bp_h(bp_url(array('view'=>'cust','mo'=>$m,'tier'=>$fTier ?: null))); ?>"
     style="color:#2563EB;text-decoration:none;margin-right:6px;"><?php echo $monName[$m]; ?></a>
  <?php endforeach; ?>
<?php else: ?>
  <?php foreach ($yrs as $y): for ($q = 1; $q <= 4; $q++):
        if ($y === $fPy && $q === $fPq) continue; ?>
  <a href="<?php echo bp_h(bp_url(array('view'=>'cust','py'=>$y,'pq'=>$q,'tier'=>$fTier ?: null))); ?>"
     style="color:#2563EB;text-decoration:none;margin-right:5px;font-size:11px;">Q<?php echo $q; ?>&nbsp;<?php echo $y; ?></a>
  <?php endfor; endforeach; ?>
<?php endif; ?>
  <span style="color:#9CA3AF;margin:0 6px;">|</span>
  <a href="<?php echo bp_h(bp_url(array('view'=>'cust','tier'=>$fTier ?: null))); ?>"
     style="color:#CC1F20;text-decoration:none;font-weight:bold;">clear period</a>
</div>
<?php endif; ?>
<div style="overflow-x:auto;">
<table class="bp-grid" id="bp-custgrid">
  <thead>
    <tr>
      <th>Tier</th><th>Ship-to #</th><th class="bp-wide">Customer</th><th>City</th><th>ST</th>
      <th>Phone</th><th>Class</th><th class="bp-r">Sls</th><th>Salesperson</th>
      <th class="bp-r"><?php echo $tgtQLbl; ?> share</th>
      <th class="bp-r"><?php echo $tgtQLbl; ?> yrs</th>
<?php if ($periodOn): ?>
      <th class="bp-r"><?php echo bp_h($periodShort); ?><br>$</th>
      <th class="bp-r"><?php echo bp_h($periodShort); ?><br>orders</th>
<?php endif; ?>
      <th>Call by</th>
<?php foreach ($yrs as $y): ?>
      <th class="bp-r"><?php echo $y; ?></th>
<?php endforeach; ?>
      <th class="bp-r">Normal yr</th><th class="bp-r">At stake</th>
      <th class="bp-r">Stopped $/yr</th><th class="bp-r">Reduced $/yr</th>
      <th class="bp-r">Open $</th><th>Last order</th>
      <th>Last contact</th><th class="bp-r">Calls</th><th class="bp-r">Emails</th>
      <th>Next follow-up</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($listRows)): ?>
    <tr><td colspan="<?php echo 22 + count($yrs) + ($periodOn ? 2 : 0); ?>"
            style="text-align:center;padding:20px;">
      No customers match this selection.</td></tr>
<?php endif; ?>
<?php foreach ($listRows as $c):
      $dUrl = bp_url(array('view'=>'detail','shipto'=>$c['shipto']));
      // Last order not in the current year - the row gets flagged
      $loYear = $c['lastOrd'] > 0 ? 1900 + intval($c['lastOrd'] / 10000) : 0;
      $stale  = ($loYear !== $curY); ?>
    <tr<?php echo $stale ? ' class="bp-stale"' : ''; ?>>
      <td><span class="bp-badge bp-t<?php echo (int)$c['tier']; ?>">
          <?php echo $c['tier'] > 0 ? 'T' . $c['tier'] : '&mdash;'; ?></span></td>
      <td><a href="<?php echo bp_h($dUrl); ?>"><?php echo bp_h($c['shipto']); ?></a></td>
      <td><a href="<?php echo bp_h($dUrl); ?>"><?php echo bp_h($c['name']); ?></a></td>
      <td><?php echo bp_h($c['city']); ?></td>
      <td><?php echo bp_h($c['state']); ?></td>
      <td><?php echo bp_h($c['phone']); ?></td>
      <td><?php echo bp_h($c['cls']); ?></td>
      <td class="bp-r"><?php echo (int)$c["slsm"]; ?></td>
      <td class="bp-nw"><?php echo bp_h($c["slsmname"]); ?></td>
      <td class="bp-r" data-val="<?php echo number_format($c['tqShare'], 4, '.', ''); ?>">
          <?php echo bp_pct($c['tqShare']); ?></td>
      <td class="bp-r"><?php echo (int)$c['tqYears']; ?></td>
<?php if ($periodOn): $mr = isset($moRev[$c["shipto"]]) ? $moRev[$c["shipto"]] : null; ?>
      <td class="bp-r"><b><?php echo $mr ? bp_money($mr['rev']) : '&mdash;'; ?></b></td>
      <td class="bp-r"><?php echo $mr ? bp_int($mr['ords']) : '&mdash;'; ?></td>
<?php endif; ?>
      <td style="white-space:nowrap;"><?php echo bp_h(bp_mdy($c["callBy"])); ?></td>
<?php foreach ($yrs as $y): ?>
      <td class="bp-r"<?php echo ($y === $curY && $c['byYear'][$y] <= 0)
            ? ' style="color:#CC1F20 !important;font-weight:bold;"' : ''; ?>>
          <?php echo $c['byYear'][$y] > 0 ? bp_money($c['byYear'][$y]) : '&mdash;'; ?></td>
<?php endforeach; ?>
      <td class="bp-r"><?php echo bp_money($c['normal']); ?></td>
      <td class="bp-r"><b><?php echo bp_money($c['stake']); ?></b></td>
      <td class="bp-r"><?php echo $c['stopAmt'] > 0 ? bp_money($c['stopAmt']) : '&mdash;'; ?></td>
      <td class="bp-r"><?php echo $c['redAmt']  > 0 ? bp_money($c['redAmt'])  : '&mdash;'; ?></td>
      <td class="bp-r"><?php echo $c['openAmt'] > 0 ? bp_money($c['openAmt']) : '&mdash;'; ?></td>
      <td data-val="<?php echo (int)$c['lastOrd']; ?>"<?php echo $stale ? ' class="bp-stale-yr"' : ''; ?>>
          <?php echo $c['lastOrd'] > 0 ? bp_h(bp_cymdToDate($c['lastOrd'])) : 'never'; ?></td>
<?php   $lg = isset($logAgg[$c['shipto']]) ? $logAgg[$c['shipto']] : null;
        $fuOver = ($lg !== null && $lg['overfu'] !== ''); ?>
      <td style="white-space:nowrap;"><?php echo $lg
            ? bp_h(substr($lg['lastts'], 0, 10)) : '<span style="color:#9CA3AF;">never</span>'; ?></td>
      <td class="bp-r"><?php echo ($lg && $lg['ncall']) ? bp_int($lg['ncall']) : '&mdash;'; ?></td>
      <td class="bp-r"><?php echo ($lg && $lg['nmail']) ? bp_int($lg['nmail']) : '&mdash;'; ?></td>
      <td style="white-space:nowrap;<?php echo $fuOver ? 'color:#CC1F20 !important;font-weight:bold;' : ''; ?>">
        <?php if ($fuOver) { echo bp_h(bp_mdy($lg['overfu'])) . ' (overdue)'; }
              elseif ($lg && $lg['nextfu'] !== '') { echo bp_h(bp_mdy($lg['nextfu'])); }
              else { echo '&mdash;'; } ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<div style="font-size:11px;color:#6B7280;padding:5px 2px;">
  <span style="background:#FEF3C7;border:1px solid #F0C060;padding:1px 6px;">Amber rows</span>
  have no order at all in <?php echo $curY; ?> - their most recent order date falls in an
  earlier year.
  <?php $staleN = 0; foreach ($listRows as $cc) {
      $ly = $cc['lastOrd'] > 0 ? 1900 + intval($cc['lastOrd'] / 10000) : 0;
      if ($ly !== $curY) { $staleN++; }
  } echo bp_int($staleN) . ' of ' . bp_int(count($listRows)); ?> rows on this list.
</div>

<?php elseif ($view === 'detail' && $sub !== null): ?>

<!-- ===================== LEVEL 4 - one customer ===================== -->
<div class="bp-sec">Level 4 &middot; <?php echo bp_h($sub['shipto'] . ' ' . $sub['name']); ?>
  <span>Click any item to see the actual order lines behind it</span></div>
<div class="bp-card">
<?php
  // Outside lookups for verifying an address or finding a live phone number.
  //
  // CMac.ws is a US directory, so the button behaves three ways:
  //   not a US country code  -> say so, do not search
  //   class not in BPCOSEARCH -> open cmac.ws plain, no category guess
  //   otherwise               -> q = first 5 of zip, q2 = the class's category
  // A US customer with no usable 5-digit zip still searches, with q blank - no
  // warning, no interruption.
  $lkLoc  = trim($sub['city'] . ', ' . $sub['state'] . ' ' . $sub['zip'], ', ');
  $lkName = trim($sub['name']);
  $lkCtry = strtoupper(trim((string)$sub['ctry']));
  $lkCls  = strtoupper(trim((string)$sub['cls']));
  $lkZip5 = substr(preg_replace('/[^0-9]/', '', (string)$sub['zip']), 0, 5);
  $lkTerm = isset($bpCoSearch[$lkCls]) ? $bpCoSearch[$lkCls] : '';

  $cmacMode = 'search';
  $cmacUrl  = 'https://www.cmac.ws/';
  if ($lkCtry !== 'US') {
      $cmacMode = 'nonus';
  } elseif ($lkTerm === '') {
      $cmacMode = 'plain';
  } else {
      $cmacUrl = 'https://www.cmac.ws/search/?q=' . urlencode(strlen($lkZip5) === 5 ? $lkZip5 : '')
               . '&q2=' . urlencode($lkTerm);
  }
  $yelpUrl = 'https://www.yelp.com/search?find_desc=' . rawurlencode($lkName)
           . '&find_loc=' . rawurlencode($lkLoc);
  // Maps gets the full street address, since that is what it resolves best.
  // Google's documented Maps URL API: /maps/search/?api=1&query=<address>
  $lkStreet = trim($sub['addr1']);
  $mapsQ    = ($lkStreet !== '') ? $lkStreet . ', ' . $lkLoc : $lkName . ' ' . $lkLoc;
  $mapsUrl  = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapsQ);
?>
  <div class="bp-cc" style="flex:2 1 320px;">
    <div class="bp-cl">Ship-to</div>
    <div class="bp-cv"><?php echo bp_h($sub['shipto'] . '  ' . $sub['name']); ?></div>
    <div style="font-size:12px;color:#4B5563;margin-top:4px;">
      <?php echo bp_h($sub['addr1']); ?><?php echo $sub['addr1'] !== '' ? '<br>' : ''; ?>
      <?php echo bp_h($lkLoc); ?><br>
      <?php echo bp_h($sub['phone']); ?>
    </div>
    <div style="margin-top:7px;">
      <div style="font-size:10px;letter-spacing:0.08em;text-transform:uppercase;
                  color:#6B7280;font-weight:bold;margin-bottom:4px;">Look up this address</div>
<?php if ($cmacMode === 'nonus'): ?>
      <a href="#" onclick="alert('Not a US Based Business');return false;"
         class="bp-btn" style="background:#9CA3AF;border:1px solid #6B7280;margin-right:5px;"
         title="<?php echo bp_h('CMac.ws is a US directory - this ship-to is country ' . $lkCtry); ?>">CMac.ws</a>
<?php else: ?>
      <a href="<?php echo bp_h($cmacUrl); ?>" target="_blank" rel="noopener noreferrer"
         class="bp-btn" style="background:#0F766E;border:1px solid #0B5C55;margin-right:5px;"
         title="<?php echo bp_h($cmacMode === 'plain'
                 ? 'Open CMac.ws - class ' . $lkCls . ' has no search category set'
                 : 'CMac.ws: ' . $lkTerm . (strlen($lkZip5) === 5 ? ' in ' . $lkZip5 : ' - no zip on file')); ?>">CMac.ws &#8599;</a>
<?php endif; ?>
      <a href="<?php echo bp_h($yelpUrl); ?>" target="_blank" rel="noopener noreferrer"
         class="bp-btn" style="background:#CC1F20;border:1px solid #8b1010;margin-right:5px;"
         title="Search Yelp for this business at this location">Yelp &#8599;</a>
      <a href="<?php echo bp_h($mapsUrl); ?>" target="_blank" rel="noopener noreferrer"
         class="bp-btn" style="background:#1A73E8;border:1px solid #1558B0;"
         title="<?php echo bp_h('Google Maps: ' . $mapsQ); ?>">Google Maps &#8599;</a>
    </div>
  </div>
  <div class="bp-cc">
    <div class="bp-cl">Tier</div>
    <div class="bp-cv"><span class="bp-badge bp-t<?php echo (int)$sub['tier']; ?>">
      <?php echo $sub['tier'] > 0 ? 'Tier ' . $sub['tier'] : 'Not on a call tier'; ?></span></div>
    <div style="font-size:11px;color:#6B7280;margin-top:5px;">
      <?php echo $sub['tier'] > 0 ? bp_h($tierRule[$sub['tier']]) : 'Buying at or near normal.'; ?></div>
  </div>
  <div class="bp-cc">
    <div class="bp-cl">Class / salesperson</div>
    <div class="bp-cv"><?php echo bp_h($sub['cls']); ?>
      &nbsp;<span style="color:#4B5563;">&middot;&nbsp;Sls <?php echo (int)$sub['slsm']; ?></span></div>
    <div style="font-size:11px;color:#6B7280;margin-top:5px;">
      <?php echo bp_h($sub['clsdesc']); ?><br>
      <b style="color:#111827;"><?php echo (int)$sub['slsm']; ?></b>
      <?php echo bp_h($sub['slsmname']); ?></div>
  </div>
  <div class="bp-cc">
    <div class="bp-cl">At stake</div>
    <div class="bp-cv"><?php echo bp_money($sub['stake']); ?></div>
    <div style="font-size:11px;color:#6B7280;margin-top:5px;">
      <?php echo $sub['tier'] > 0 ? bp_h($stakeRule[$sub['tier']]) : 'nothing lost'; ?><br>
      normal year <?php echo bp_money($sub['normal']); ?>
      = <?php echo bp_money($sub['histRev']); ?> over
      <?php echo $BP_NORMAL_BASIS === 'active'
            ? (int)$sub['yrsWith'] . ' active year' . ($sub['yrsWith'] === 1 ? '' : 's')
            : count($hy) . ' years'; ?></div>
  </div>
  <div class="bp-cc">
    <div class="bp-cl">Call by</div>
    <div class="bp-cv"><?php echo $sub["callBy"] !== "" ? bp_h(bp_mdy($sub["callBy"])) : "&mdash;"; ?></div>
    <div style="font-size:11px;color:#6B7280;margin-top:5px;">
      <?php echo $BP_CALL_LEAD; ?> days ahead of their own average
      <?php echo $tgtQLbl; ?> kickoff</div>
  </div>
  <div class="bp-cc">
    <div class="bp-cl">Open orders</div>
    <div class="bp-cv"><?php echo $sub['openOrds'] > 0
        ? bp_int($sub['openOrds']) . ' / ' . bp_money($sub['openAmt']) : 'none'; ?></div>
    <div style="font-size:11px;color:#6B7280;margin-top:5px;">
      unshipped, shown for information only</div>
  </div>
</div>

<!-- ===================== Contact log ===================== -->
<div class="bp-sec" id="bp-log">Contact log
  <span><?php echo bp_int(count($logRows)); ?> logged &middot; every entry is stamped by the
  IBM i with your EIP profile and cannot be edited or deleted</span></div>

<?php if ($logErr !== ''): ?>
<div class="bp-warn" style="background:#FEE2E2;border-color:#CC1F20;color:#7F1D1D;">
  <b>Nothing was logged.</b> <?php echo bp_h($logErr); ?>
</div>
<?php elseif ($logMsg !== ''): ?>
<div class="bp-chk" style="background:#DCFCE7;border-color:#16A34A;color:#14532D;">
  <b><?php echo bp_h($logMsg); ?></b>
</div>
<?php endif; ?>

<div style="background:#fff;border:1px solid #D1D5DB;padding:10px 12px;margin:8px 0;">
  <div style="display:flex;align-items:center;gap:22px;flex-wrap:wrap;">
    <label style="font-size:13px;font-weight:bold;cursor:pointer;">
      <input type="checkbox" id="bp-chk-call" style="vertical-align:-2px;"> I called them
    </label>
    <label style="font-size:13px;font-weight:bold;cursor:pointer;">
      <input type="checkbox" id="bp-chk-mail" style="vertical-align:-2px;"> I emailed them
    </label>
    <span style="font-size:11px;color:#6B7280;">
      Ticking either opens the note. Nothing is recorded until the note is saved.
    </span>
  </div>

  <form method="post" id="bp-logform" action="<?php echo bp_h(bp_url(array('view'=>'detail','shipto'=>$sub['shipto']))); ?>"
        style="display:none;margin-top:10px;border-top:1px solid #E5E7EB;padding-top:10px;">
    <input type="hidden" name="bp_action" value="logcontact">
    <input type="hidden" name="bp_shipto" value="<?php echo bp_h($sub['shipto']); ?>">
    <input type="hidden" name="bp_tier"   value="<?php echo (int)$sub['tier']; ?>">
    <input type="hidden" name="bp_type"   id="bp-type" value="C">

    <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-start;">
      <div style="flex:2 1 420px;">
        <label style="font-size:11px;font-weight:bold;color:#374151;display:block;margin-bottom:3px;">
          What was said <span style="color:#CC1F20;" id="bp-note-req">*</span>
          <span style="font-weight:normal;color:#6B7280;" id="bp-note-hint">minimum
          <?php echo $BP_NOTE_MIN; ?> characters</span>
        </label>
        <textarea name="bp_note" id="bp-note" rows="3" maxlength="1000"
                  style="width:100%;font-size:12px;padding:5px;border:1px solid #9CA3AF;
                         border-radius:3px;font-family:inherit;"></textarea>
        <div id="bp-notecount" style="font-size:11px;color:#6B7280;margin-top:2px;">0 characters</div>
      </div>
      <div style="flex:1 1 220px;">
        <label style="font-size:11px;font-weight:bold;color:#374151;display:block;margin-bottom:3px;">
          Outcome
        </label>
        <select name="bp_outcome" id="bp-outcome" style="width:100%;font-size:12px;padding:4px;
                border:1px solid #9CA3AF;border-radius:3px;">
          <option value="">(not stated)</option>
<?php foreach ($BP_OUTCOMES as $o): ?>
          <option value="<?php echo bp_h($o); ?>"><?php echo bp_h($o); ?></option>
<?php endforeach; ?>
        </select>

        <label style="font-size:11px;font-weight:bold;color:#374151;display:block;margin:8px 0 3px;">
          Follow up on <span style="color:#CC1F20;">*</span>
        </label>
        <input type="date" name="bp_fudate" id="bp-fudate"
               style="width:100%;font-size:12px;padding:4px;border:1px solid #9CA3AF;border-radius:3px;">
        <label style="font-size:11px;display:block;margin-top:5px;cursor:pointer;">
          <input type="checkbox" id="bp-nofu" style="vertical-align:-2px;"> No follow-up needed
        </label>
        <input type="text" name="bp_funone" id="bp-funone" maxlength="120" placeholder="Why not?"
               style="display:none;width:100%;font-size:12px;padding:4px;margin-top:4px;
                      border:1px solid #9CA3AF;border-radius:3px;">
      </div>
    </div>

    <div style="margin-top:9px;display:flex;gap:8px;align-items:center;">
      <button type="submit" class="bp-btn" id="bp-save"
              style="background:#1DA032;border:1px solid #15803d;cursor:pointer;font-size:12px;">
        Save contact</button>
      <button type="button" class="bp-btn" id="bp-cancel"
              style="background:#6B7280;border:1px solid #4B5563;cursor:pointer;font-size:12px;">
        Cancel</button>
      <span id="bp-formerr" style="font-size:11px;color:#CC1F20;font-weight:bold;"></span>
      <span style="margin-left:auto;font-size:11px;color:#6B7280;">
        Will be stamped <b><?php echo bp_h($bpUser !== '' ? $bpUser : '(profile not identified)'); ?></b>
        at the IBM i's clock
      </span>
    </div>
  </form>
</div>

<?php if (!empty($logRows)): ?>
<div style="overflow-x:auto;">
<table class="bp-grid">
  <thead>
    <tr><th>When</th><th>Who</th><th>Type</th><th>Outcome</th><th>Follow-up</th>
        <th class="bp-wide">Note</th></tr>
  </thead>
  <tbody>
<?php foreach ($logRows as $lr):
      $lt = trim((string)$lr['CLTYPE']);
      $tn = ($lt === 'C') ? 'Call' : (($lt === 'E') ? 'Email' : 'Note');
      $fu = trim((string)$lr['CLFUDT']);
      $overdue = ($fu !== '' && $fu < date('Y-m-d')); ?>
    <tr>
      <td style="white-space:nowrap;"><?php echo bp_h(substr(trim((string)$lr['CLTSTP']), 0, 19)); ?></td>
      <td><b><?php echo bp_h(trim((string)$lr['CLUSER'])); ?></b></td>
      <td><?php echo bp_h($tn); ?></td>
      <td><?php echo bp_h(trim((string)$lr['CLOUTC'])); ?></td>
      <td style="white-space:nowrap;<?php echo $overdue ? 'color:#CC1F20 !important;font-weight:bold;' : ''; ?>">
        <?php if ($fu !== ''): echo bp_h(bp_mdy($fu)); echo $overdue ? ' (overdue)' : '';
              else: echo '<span style="color:#6B7280;">' . bp_h(trim((string)$lr['CLFUNR'])) . '</span>';
              endif; ?></td>
      <td class="bp-wide"><?php $nv = trim((string)$lr["CLNOTE"]);
          echo $nv !== "" ? nl2br(bp_h($nv))
             : "<span style=\"color:#9CA3AF;\">(nothing said)</span>"; ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<?php else: ?>
<div style="font-size:12px;color:#6B7280;padding:4px 2px;">No contact logged for this customer yet.</div>
<?php endif; ?>

<div class="bp-sec">Sales by year, and the <?php echo $tgtQLbl; ?> pattern</div>
<div style="overflow-x:auto;">
<table class="bp-grid">
  <thead>
    <tr><th>Year</th><th class="bp-r">Total sales</th>
        <th class="bp-r"><?php echo $tgtQLbl; ?> sales</th>
        <th class="bp-r"><?php echo $tgtQLbl; ?> orders</th>
        <th class="bp-r"><?php echo $tgtQLbl; ?> share of that year</th></tr>
  </thead>
  <tbody>
<?php foreach ($yrs as $y):
      $tot = $sub['byYear'][$y]; $tq = $sub['tqByYear'][$y];
      $shr = ($tot > 0) ? $tq / $tot : 0.0; ?>
    <tr>
      <td><b><?php echo $y; ?></b><?php echo $y === $curY ? ' <span style="color:#6B7280;">(partial - through '
            . bp_h(bp_cymdToDate($todayCymd)) . ')</span>' : ''; ?></td>
      <td class="bp-r"><?php echo $tot > 0 ? bp_money($tot) : '&mdash;'; ?></td>
      <td class="bp-r"><?php echo $tq  > 0 ? bp_money($tq)  : '&mdash;'; ?></td>
      <td class="bp-r"><?php echo isset($sub['tqOrd'][$y]) && $sub['tqOrd'][$y] > 0
            ? bp_int($sub['tqOrd'][$y]) : '&mdash;'; ?></td>
      <td class="bp-r"><?php echo $tot > 0 ? bp_pct($shr) : '&mdash;'; ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr><td><?php echo $hy[0]; ?>-<?php echo $hy[2]; ?> total</td>
        <td class="bp-r"><?php echo bp_money($sub['histRev']); ?></td>
        <td class="bp-r"><?php echo bp_money($sub['tqRev']); ?></td>
        <td class="bp-r"><?php echo (int)$sub['tqYears']; ?> yrs</td>
        <td class="bp-r"><?php echo bp_pct($sub['tqShare']); ?></td></tr>
  </tfoot>
</table>
</div>

<div class="bp-sec">Every product, <?php echo $hy[0]; ?> to <?php echo $curY; ?>
  <span><?php echo bp_int(count($subItems)); ?> items &middot; click an item for its order lines</span></div>
<div style="overflow-x:auto;">
<table class="bp-grid" id="bp-itemgrid">
  <thead>
    <tr>
      <th class="bp-nw">Item</th><th class="bp-wide">Description</th><th>Status</th>
      <th class="bp-r">Yrs bought</th><th class="bp-r">Orders</th>
      <th class="bp-r">Normally buys qty/yr</th>
      <th class="bp-r">Normal $/yr</th><th class="bp-r">Lost $/yr</th>
<?php foreach ($yrs as $y): ?>
      <th class="bp-r"><?php echo $y; ?> qty</th>
<?php endforeach; ?>
      <th class="bp-r">Last unit price</th><th>Last ordered</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($subItems)): ?>
    <tr><td colspan="16" style="text-align:center;padding:20px;">No items found.</td></tr>
<?php endif; ?>
<?php foreach ($subItems as $it):
      $lUrl = bp_url(array('view'=>'lines','shipto'=>$sub['shipto'],'item'=>$it['item'])); ?>
    <tr>
      <td class="bp-nw"><a href="<?php echo bp_h($lUrl); ?>"><?php echo bp_h($it["item"]); ?></a></td>
      <td><?php echo bp_h($it['desc']); ?></td>
      <td><span style="color:<?php echo $statusClr[$it['status']]; ?> !important;font-weight:bold;">
          <?php echo bp_h($it['status']); ?></span></td>
      <td class="bp-r"><?php echo (int)$it['yrsWith']; ?></td>
      <td class="bp-r"><?php echo bp_int($it['histOrds']); ?></td>
      <td class="bp-r"><?php echo bp_qty($it['normalQty']); ?></td>
      <td class="bp-r"><?php echo bp_money($it['normal']); ?></td>
      <td class="bp-r"><?php echo $it['lossAmt'] > 0
            ? '<b style="color:#CC1F20 !important;">' . bp_money($it['lossAmt']) . '</b>'
            : '&mdash;'; ?></td>
<?php foreach ($yrs as $y): ?>
      <td class="bp-r"><?php echo $it['q' . $y] != 0 ? bp_qty($it['q' . $y]) : '&mdash;'; ?></td>
<?php endforeach; ?>
      <td class="bp-r">$<?php echo number_format($it['lastPrice'], 5); ?></td>
      <td data-val="<?php echo (int)$it['lastOrd']; ?>"><?php echo bp_h(bp_cymdToDate($it['lastOrd'])); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
<div style="font-size:11px;color:#6B7280;padding:5px 2px;">
  <b>stopped</b> = repeat purchase with nothing in <?php echo $curY; ?> &middot;
  <b>reduced</b> = repeat purchase running under half its normal year &middot;
  <b>one-off</b> = bought once and never repeated, excluded from lost dollars &middot;
  <b>steady</b> = still buying at or near normal.
  <a href="<?php echo bp_h(bp_url(array('view'=>'lines','shipto'=>$sub['shipto']))); ?>"
     style="color:#2563EB;font-weight:bold;">See all order lines for this customer</a>
</div>

<?php elseif ($view === 'lines' && $sub !== null): ?>

<!-- ===================== LEVEL 5 - the raw order lines ===================== -->
<div class="bp-sec">Level 5 &middot; Order lines behind the money
  <span><?php echo bp_h($sub['shipto'] . ' ' . $sub['name']); ?><?php
    echo $fItem !== '' ? ' &middot; item ' . bp_h($fItem) : ''; ?> &middot;
    <?php echo bp_int(count($lineRows)); ?> lines</span></div>
<div class="bp-note">
  <b>This is the audit trail.</b> One row per order line, exactly as the page counts it.
  <b>Shipment rows</b> is how many physical OEORDH records that single line has - DHQORD
  restates the full line quantity on every one of them, so the page takes each line once.
  Where shipment rows is greater than 1, summing the raw table would have counted that
  line's dollars that many times over. Line amount =
  <?php echo $tgtQLbl; ?>-independent: qty ordered x unit price / unit factor, zero if the
  price or the factor is zero.
</div>
<div style="overflow-x:auto;">
<table class="bp-grid" id="bp-linegrid">
  <thead>
    <tr>
      <th>Order #</th><th class="bp-r">Line</th><th>Order date</th><th>Ty</th>
      <th class="bp-nw">Item</th><th class="bp-wide">Description</th>
      <th class="bp-r">Qty ordered</th><th class="bp-r">Qty shipped</th>
      <th class="bp-r">Unit price</th><th class="bp-r">Unit factor</th>
      <th class="bp-r">Shipment rows</th><th class="bp-r">Line amount</th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($lineRows)): ?>
    <tr><td colspan="12" style="text-align:center;padding:20px;">No order lines found.</td></tr>
<?php endif; ?>
<?php $lineTot = 0.0; foreach ($lineRows as $r):
      $lineTot += (float)$r['AMT']; $multi = (int)$r['SHIPROWS'] > 1; ?>
    <tr>
      <td><?php echo bp_h(trim((string)$r['ORDNO'])); ?></td>
      <td class="bp-r"><?php echo bp_h(trim((string)$r['LN'])); ?></td>
      <td data-val="<?php echo (int)$r['ORDDTE']; ?>"><?php echo bp_h(bp_cymdToDate($r['ORDDTE'])); ?></td>
      <td><?php echo bp_h(trim((string)$r['ORDTY'])); ?></td>
      <td class="bp-nw"><a href="<?php echo bp_h(bp_url(array("view"=>"lines","shipto"=>$sub["shipto"],"item"=>trim((string)$r["ITEM"])))); ?>"><?php echo bp_h(trim((string)$r["ITEM"])); ?></a></td>
      <td><?php echo bp_h(trim((string)$r['ITEMDESC'])); ?></td>
      <td class="bp-r"><?php echo bp_qty($r['QORD']); ?></td>
      <td class="bp-r"><?php echo bp_qty($r['QSHIP']); ?></td>
      <td class="bp-r">$<?php echo number_format((float)$r['PRICE'], 5); ?></td>
      <td class="bp-r"><?php echo number_format((float)$r['UF'], 4); ?></td>
      <td class="bp-r"<?php echo $multi ? ' style="background:#FEF3C7;font-weight:bold;"' : ''; ?>>
          <?php echo (int)$r['SHIPROWS']; ?></td>
      <td class="bp-r"><?php echo bp_money($r['AMT'], 2); ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr><td colspan="11">TOTAL <?php echo bp_int(count($lineRows)); ?> lines</td>
        <td class="bp-r"><?php echo bp_money($lineTot, 2); ?></td></tr>
  </tfoot>
</table>
</div>

<?php else: ?>

<div class="bp-note" style="background:#FEF3C7;border-color:#F0C060;color:#78350F;">
  <b>Nothing to show.</b> That ship-to has no invoiced history in the
  <?php echo $hy[0]; ?>-<?php echo $curY; ?> window, or the link was incomplete.
  <a href="?">Back to the headline</a>.
</div>

<?php endif; ?>

<!-- Basis note, on every view -->
<div class="bp-note">
  <b style="font-size:12px;">How every number here is built</b>
  <ul style="margin:8px 0 0 0;padding-left:20px;">
    <li style="margin-bottom:5px;"><b>Revenue</b> = qty ordered x unit price / unit factor
        (DHQORD x DHSLPR / DHORUF), zero when the price or the factor is zero.</li>
    <li style="margin-bottom:5px;"><b>Each order line is counted once.</b> DHQORD restates the
        full line quantity on every partial-shipment row in OEORDH, so summing raw rows
        inflated <?php echo $hy[2]; ?> by roughly 38%. Level 5 shows the shipment-row count
        per line so you can see this for yourself.</li>
    <li style="margin-bottom:5px;"><b>Customer grain</b> is the ship-to (OESHTO), never the
        bill-to.</li>
    <li style="margin-bottom:5px;"><b>Invoiced history only</b> - OEORDH where
        DHSEQ# &lt;&gt; 0. OEORDT is never unioned in, because that double-counts.</li>
    <li style="margin-bottom:5px;"><b>Periods keyed on the order date</b> (OEBDTE), since the
        goal is moving when the customer orders, not when we ship.</li>
    <li style="margin-bottom:5px;"><b>Excluded:</b> order types P/Q/S/U/V; items AD0166, LTL*
        and *SAMP*; <?php echo count(explode(',', $BP_BAD_BILLTO)); ?> internal bill-to
        accounts; <?php echo count(explode(',', $BP_BAD_ORDERS)); ?> named COs.</li>
    <li style="margin-bottom:5px;"><b>The customer must exist and have a class.</b> Nine
        ship-to numbers appear on orders with no HDCUST record at all, which showed up as a
        '??' customer class. They are excluded from past, present and future revenue.</li>
    <li style="margin-bottom:5px;"><b>Entry code must be S.</b> Only order lines whose
        OEORDT.ODOREC = 'S' count, in past, present or future revenue. That drops 1,673 lines
        worth $3.75M across <?php echo $hy[0]; ?>-<?php echo $curY; ?>, nearly all of it
        entry code N. Applied as an EXISTS test rather than a join, because OEORDT carries
        more than one row for some lines and a join would count those twice.</li>
    <li style="margin-bottom:5px;"><b>One "normal year"</b> = their <?php echo $hy[0]; ?>-<?php echo $hy[2]; ?>
        sales divided by <?php echo $BP_NORMAL_BASIS === 'active'
          ? 'the years they actually bought in' : 'the full ' . count($hy) . '-year window'; ?>
        (<code>?basis=<?php echo $BP_NORMAL_BASIS === 'active' ? 'window' : 'active'; ?></code>
        switches it). Judging whether a customer is still buying at their usual pace always
        compares against their active-year rate, so that switch moves dollars, not tiers.</li>
    <li style="margin-bottom:5px;"><b>The target quarter is not hardcoded.</b> We are in
        <?php echo $curQLbl . ' ' . $curY; ?>, so the page targets
        <?php echo $tgtQLbl . ' ' . $tgtQY; ?> and measures the habit against
        <?php echo $tgtQLbl; ?> of <?php echo $hy[0]; ?>, <?php echo $hy[1]; ?> and
        <?php echo $hy[2]; ?>. It re-aims itself as the year moves on.</li>
    <li style="margin-bottom:5px;"><b><?php echo $curY; ?> is a partial year</b> compared
        against full prior years with no proration. A customer who only ever buys in
        <?php echo $tgtQLbl; ?> therefore reads as silent - that is the intended signal, not
        an error.</li>
    <li><b>Order history lags roughly two weeks</b>, because lines reach OEORDH as they ship.
        That is why open unshipped orders are shown in their own column and never suppress a
        call.</li>
  </ul>
</div>

</div>

</td>
</tr>
</table>

<?php if ($view === 'tiles'): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script type="text/javascript">
(function () {
    if (typeof Chart === 'undefined') { return; }   // CDN blocked - tables still fine

    // Month drill targets, in the same order as the chart's labels
    var MONTH_NUMS  = <?php echo json_encode(array_map('intval', array_keys($tqMonths))); ?>;
    var MONTH_CUSTS = <?php echo json_encode(array_values(array_map(
        function ($a) { return (int)$a['custs']; }, $tqMonths))); ?>;
    var MONTH_URL   = '<?php echo bp_h(bp_url(array('view' => 'cust'))); ?>&mo=';
    var YEARS       = <?php echo json_encode(array_map('intval', $yrs)); ?>;
    var QTR_URL     = '<?php echo bp_h(bp_url(array('view' => 'cust'))); ?>';

    var money = function (v) {
        if (Math.abs(v) >= 1000000) return '$' + (v / 1000000).toFixed(2) + 'M';
        if (Math.abs(v) >= 1000)    return '$' + Math.round(v / 1000) + 'K';
        return '$' + Math.round(v);
    };

    var qc = document.getElementById('bp-qtrChart');
    if (qc) {
        new Chart(qc, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map('strval', $yrs)); ?>,
                datasets: [
<?php for ($q = 1; $q <= 4; $q++):
        $vals = array();
        foreach ($yrs as $y) { $vals[] = round($qtrByYear[$y][$q], 2); }
        $isTgt = ($q === $tgtQ);
?>
                {
                    label: 'Q<?php echo $q; ?>',
                    data: <?php echo json_encode($vals); ?>,
                    backgroundColor: '<?php echo $isTgt ? '#EA580C' : '#93C5FD'; ?>',
                    borderColor: '<?php echo $isTgt ? '#C2410C' : '#60A5FA'; ?>',
                    borderWidth: 1
                },
<?php endfor; ?>
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                onClick: function (evt, els) {
                    if (!els || !els.length) { return; }
                    var yr = YEARS[els[0].index];
                    var q  = els[0].datasetIndex + 1;
                    if (yr && q) { window.location.href = QTR_URL + '&py=' + yr + '&pq=' + q; }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { callbacks: {
                        label: function (c) {
                            return c.dataset.label + ': ' + money(c.parsed.y); },
                        afterBody: function () { return ['click to open these customers']; }
                    } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 },
                         callback: function (v) { return money(v); } } }
                }
            }
        });
    }

    var mc = document.getElementById('bp-monChart');
    if (mc) {
        new Chart(mc, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_values(array_map(
                    function ($m) use ($monName) { return $monName[$m]; },
                    array_keys($tqMonths)))); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_values(array_map(
                        function ($a) { return round($a['rev'], 2); }, $tqMonths))); ?>,
                    backgroundColor: '#EA580C', borderColor: '#C2410C', borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Orders',
                    type: 'line',
                    data: <?php echo json_encode(array_values(array_map(
                        function ($a) { return $a['ords']; }, $tqMonths))); ?>,
                    borderColor: '#1F2937', backgroundColor: '#1F2937',
                    borderWidth: 2, pointRadius: 3, yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                onClick: function (evt, els) {
                    if (!els || !els.length) { return; }
                    var mo = MONTH_NUMS[els[0].index];
                    if (mo) { window.location.href = MONTH_URL + mo; }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { callbacks: {
                        label: function (c) {
                            return c.dataset.label === 'Orders'
                                 ? 'Orders: ' + c.parsed.y
                                 : 'Revenue: ' + money(c.parsed.y); },
                        afterBody: function (items) {
                            var n = MONTH_CUSTS[items[0].dataIndex];
                            return n ? [n + ' customers - click to open them'] : []; }
                    } }
                },
                scales: {
                    x:  { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y:  { beginAtZero: true, position: 'left', ticks: { font: { size: 10 },
                          callback: function (v) { return money(v); } } },
                    y1: { beginAtZero: true, position: 'right',
                          grid: { drawOnChartArea: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    }
}());
</script>
<?php endif; ?>

<script type="text/javascript">
// Contact log. The checkbox opens the note; the note is what gets recorded, so
// abandoning the page records nothing rather than leaving a tick with no note.
(function () {
    var form   = document.getElementById('bp-logform');
    if (!form) { return; }
    var chkC   = document.getElementById('bp-chk-call');
    var chkE   = document.getElementById('bp-chk-mail');
    var type   = document.getElementById('bp-type');
    var note   = document.getElementById('bp-note');
    var count  = document.getElementById('bp-notecount');
    var nofu   = document.getElementById('bp-nofu');
    var fudate = document.getElementById('bp-fudate');
    var funone = document.getElementById('bp-funone');
    var cancel = document.getElementById('bp-cancel');
    var errEl  = document.getElementById('bp-formerr');
    var outc   = document.getElementById('bp-outcome');
    var reqEl  = document.getElementById('bp-note-req');
    var hintEl = document.getElementById('bp-note-hint');
    var MIN    = <?php echo (int)$BP_NOTE_MIN; ?>;
    var NOTE_OPTIONAL = <?php echo json_encode($BP_NOTE_OPTIONAL); ?>;
    var saved  = false;

    // Nobody was spoken to, so there is nothing to write down
    function noteOptional() {
        return NOTE_OPTIONAL.indexOf(outc.value) !== -1;
    }
    function syncNoteReq() {
        var opt = noteOptional();
        reqEl.style.display  = opt ? 'none' : '';
        hintEl.textContent   = opt ? 'optional for this outcome'
                                   : 'minimum ' + MIN + ' characters';
        tally();
    }

    function open(kind) {
        type.value = kind;
        form.style.display = 'block';
        note.focus();
    }
    function close() {
        form.style.display = 'none';
        chkC.checked = false; chkE.checked = false;
        note.value = ''; funone.value = ''; fudate.value = '';
        nofu.checked = false; funone.style.display = 'none';
        outc.value = '';
        errEl.textContent = ''; syncNoteReq();
    }
    function tally() {
        var n   = note.value.trim().length;
        var opt = noteOptional();
        count.textContent = n + ' character' + (n === 1 ? '' : 's')
                          + ((!opt && n < MIN) ? ' - need at least ' + MIN : '');
        count.style.color = (!opt && n > 0 && n < MIN) ? '#CC1F20' : '#6B7280';
    }

    chkC.addEventListener('change', function () {
        if (chkC.checked) { chkE.checked = false; open('C'); } else { close(); }
    });
    chkE.addEventListener('change', function () {
        if (chkE.checked) { chkC.checked = false; open('E'); } else { close(); }
    });
    note.addEventListener('input', tally);
    outc.addEventListener('change', syncNoteReq);
    nofu.addEventListener('change', function () {
        funone.style.display = nofu.checked ? 'block' : 'none';
        if (nofu.checked) { fudate.value = ''; funone.focus(); }
    });
    fudate.addEventListener('input', function () {
        if (fudate.value !== '') { nofu.checked = false; funone.style.display = 'none'; funone.value = ''; }
    });
    cancel.addEventListener('click', close);

    form.addEventListener('submit', function (e) {
        var n = note.value.trim().length;
        if (!noteOptional() && n < MIN) {
            e.preventDefault();
            errEl.textContent = 'The note needs at least ' + MIN + ' characters.';
            note.focus(); return;
        }
        if (fudate.value === '' && funone.value.trim() === '') {
            e.preventDefault();
            errEl.textContent = 'Set a follow-up date, or tick "no follow-up needed" and say why.';
            return;
        }
        saved = true;
    });

    // Courtesy warning only. A browser cannot be stopped from closing, which is
    // exactly why the note - not the tick - is the thing that gets recorded.
    window.addEventListener('beforeunload', function (e) {
        if (!saved && form.style.display !== 'none' && note.value.trim() !== '') {
            e.preventDefault(); e.returnValue = '';
        }
    });
}());

(function () {
    var el = document.getElementById('bp-asof');
    if (!el) return;
    var now = new Date();
    var tz  = now.toLocaleTimeString('en-US', {timeZoneName:'short'}).split(' ').pop();
    el.textContent = 'Loaded ' + now.toLocaleDateString('en-US',
        {weekday:'short', month:'short', day:'numeric', year:'numeric'}) + ' ' +
        now.toLocaleTimeString('en-US',
        {hour:'numeric', minute:'2-digit', second:'2-digit'}) + ' ' + tz;
}());

(function () {
    var grids = document.querySelectorAll('table.bp-grid');
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
                ths[i].className = ths[i].className.replace(/\s*bp-(asc|desc)/g, '');
            }
            ths[col].className += (state.dir === 1 ? ' bp-asc' : ' bp-desc');
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
