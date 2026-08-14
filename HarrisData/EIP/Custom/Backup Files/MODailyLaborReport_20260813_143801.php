<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
date_default_timezone_set('America/Chicago');

// ── Filter params ─────────────────────────────────────────────────────────────
$filterEName = isset($_GET['fename']) ? trim($_GET['fename']) : '';
$filterOrd   = isset($_GET['ford'])   ? trim($_GET['ford'])   : '';
$filterDept  = isset($_GET['fdept'])  ? trim($_GET['fdept'])  : '';
$filterDName = isset($_GET['fdname']) ? trim($_GET['fdname']) : '';
$filterWc    = isset($_GET['fwc'])    ? trim($_GET['fwc'])    : '';

$filterDate = isset($_GET['fdate']) ? trim($_GET['fdate']) : '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDate)) {
    $filterDate = date('Y-m-d');
}

// Toggle: also include employees terminated on or after the From Date, so the
// people who actually worked during the selected window stay visible no matter
// how far back the window reaches.
$includeTerm = (isset($_GET['incterm']) && $_GET['incterm'] === '1');

// Date range: the filter date is the START; the range runs through the current
// date (or through the chosen date if a future date is picked). Plain string
// comparison is safe here — both sides are zero-padded YYYY-MM-DD.
$todayYmd  = date('Y-m-d');
$startDate = $filterDate;
$endDate   = ($filterDate > $todayYmd) ? $filterDate : $todayYmd;

// ── Auto-refresh: M–F, 7 am–5 pm Eastern ─────────────────────────────────────
$estNow      = new DateTime('now', new DateTimeZone('America/New_York'));
$estDow      = (int)$estNow->format('N');
$estHour     = (int)$estNow->format('G');
$autoRefresh = ($estDow >= 1 && $estDow <= 5 && $estHour >= 7 && $estHour < 17);
$refreshSecs = 900;
$refreshedAt = date('m/d/Y g:i:s A');

// ── Helpers ───────────────────────────────────────────────────────────────────
function molr_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function molr_esc($s) {
    return str_replace("'", "''", (string)$s);
}
function molr_dec($v, $dp = 2) {
    if ($v === null || $v === '') return '';
    return number_format((float)$v, $dp);
}
function molr_int($v) {
    if ($v === null || $v === '') return '';
    return number_format((int)$v);
}
function molr_curr($v) {
    if ($v === null || $v === '') return '';
    $n = (float)$v;
    return ($n < 0 ? '-' : '') . '$' . number_format(abs($n), 2);
}
function molr_date($v) {
    if (!$v) return '';
    $d = DateTime::createFromFormat('Y-m-d', (string)$v);
    return $d ? $d->format('m/d/Y') : (string)$v;
}
/**
 * Efficiency = earned (standard) hours / hours actually worked.
 * 1.00 std against 1.00 worked = 100%; 1.00 std against 1.25 worked = 80%.
 * Returns null when it is not meaningful — no hours worked, or a negative
 * correction row — so callers can render a dash instead of a bogus number.
 */
function molr_eff($earned, $worked) {
    $w = (float)$worked;
    if ($w <= 0) return null;
    return ((float)$earned / $w) * 100.0;
}
/** Colour band for an efficiency figure. Mirrors the variance convention. */
function molr_eff_class($pct) {
    if ($pct === null)  return '';
    if ($pct >= 100)    return ' eff-good';
    if ($pct >= 90)     return ' eff-warn';
    return ' eff-bad';
}
/** "92.4%" or an em dash. */
function molr_eff_txt($pct) {
    return ($pct === null) ? '&mdash;' : number_format($pct, 1) . '%';
}
/**
 * HREMPL.EMTRDT is NUMERIC(7,0) in CYMD form: leading century digit (1 = 20xx,
 * 0 = 19xx) then YYMMDD. 1260730 -> 07/30/2026. 0 = not terminated.
 */
function molr_cymd($v) {
    $n = (int)$v;
    if ($n <= 0) return '';
    $cent = intdiv($n, 1000000);
    $rest = $n % 1000000;
    $y    = ($cent >= 1 ? 2000 : 1900) + intdiv($rest, 10000);
    $m    = intdiv($rest % 10000, 100);
    $d    = $rest % 100;
    if ($m < 1 || $m > 12 || $d < 1 || $d > 31) return '';
    return sprintf('%02d/%02d/%04d', $m, $d, $y);
}

// ── DB connection ─────────────────────────────────────────────────────────────
$conn   = $i5Connect->getConnection();
$rows   = array();
$sqlErr = '';

// ── HREMPL column catalog ─────────────────────────────────────────────────────
// Read the real column list/types from the catalog rather than assuming them,
// so the EMTRDT test and the name expression below are built from actual state.
$hremplCols = array();
$colStmt = db2_exec($conn,
    "SELECT TRIM(COLUMN_NAME) AS COLUMN_NAME, TRIM(DATA_TYPE) AS DATA_TYPE
       FROM QSYS2.SYSCOLUMNS
      WHERE TABLE_SCHEMA = 'SGHDSDATA' AND TABLE_NAME = 'HREMPL'",
    array('cursor' => DB2_SCROLLABLE));
if ($colStmt) {
    while ($c = db2_fetch_assoc($colStmt)) {
        $hremplCols[strtoupper(trim((string)$c['COLUMN_NAME']))] =
            strtoupper(trim((string)$c['DATA_TYPE']));
    }
    db2_free_stmt($colStmt);
}

// Employee name = EMFNAM <space> EMLNAM (falls back to EMRNAM if not present)
if (isset($hremplCols['EMFNAM']) && isset($hremplCols['EMLNAM'])) {
    $nameExpr = "TRIM(TRIM(T04.EMFNAM) CONCAT ' ' CONCAT TRIM(T04.EMLNAM))";
} else {
    $nameExpr = "TRIM(T04.EMRNAM)";
}

// Terminated employees: EMTRDT holds a termination date. Keep only rows whose
// employee has EMTRDT = 0 or NULL (NULL also covers labor rows with no HREMPL
// match, which the LEFT JOIN must not drop). EMEMPL is NOT unique in HREMPL —
// 11 numbers are reused across old and new hires — so this filter is also what
// keeps the LEFT JOIN from fanning out and double-counting hours.
//
// With $includeTerm on, anyone terminated ON OR AFTER the From Date is let back
// in — they were still on the payroll for part of the selected window, so their
// hours belong in it. This tracks the range instead of the calendar, so it never
// goes stale at year end.
//
// EMTRDT is CYMD (century digit + YYMMDD), which sorts correctly as a plain
// integer: a 19xx date is 6 digits / century 0 and a 20xx date is 7 digits /
// century 1, so ">=" needs no special-casing for the four pre-2000 dates on file.
$sy = (int)substr($startDate, 0, 4);
$sm = (int)substr($startDate, 5, 2);
$sd = (int)substr($startDate, 8, 2);
$termFromCymd = ($sy >= 2000 ? 1000000 : 0) + ($sy % 100) * 10000 + $sm * 100 + $sd;

if (!isset($hremplCols['EMTRDT'])) {
    $activeEmpPred = '';
    $termSelExpr   = '0';
    $empPickOrder  = "E.EMEMPL";
} elseif ($hremplCols['EMTRDT'] === 'DATE' || $hremplCols['EMTRDT'] === 'TIMESTAMP') {
    $safeStartD    = molr_esc($startDate);
    $activeEmpPred = $includeTerm
        ? "(T04.EMTRDT IS NULL OR T04.EMTRDT >= DATE('$safeStartD'))"
        : "T04.EMTRDT IS NULL";
    $termSelExpr   = "T04.EMTRDT";
    $empPickOrder  = "CASE WHEN E.EMTRDT IS NULL THEN 0 ELSE 1 END, E.EMTRDT DESC";
} else {
    $activeEmpPred = $includeTerm
        ? "(T04.EMTRDT IS NULL OR T04.EMTRDT = 0 OR T04.EMTRDT >= $termFromCymd)"
        : "(T04.EMTRDT IS NULL OR T04.EMTRDT = 0)";
    $termSelExpr   = "COALESCE(T04.EMTRDT, 0)";
    $empPickOrder  = "CASE WHEN E.EMTRDT = 0 THEN 0 ELSE 1 END, E.EMTRDT DESC";
}

// ── One HREMPL row per employee number ────────────────────────────────────────
// EMEMPL is NOT unique: 11 numbers are reused across an old and a new hire (and
// 41782 is stored twice outright), so joining HREMPL raw multiplies labor rows
// and inflates every total. Measured 2026-08-13: a From Date of 2010-01-01 gave
// 411,025 joined rows against 410,478 real ones, 169,561.81 hours instead of
// 166,194.73. Collapsing to one row per number — the active row if there is one,
// otherwise the most recent termination — makes the join 1:1 at any From Date.
$empCte = "
    EMP1 AS (
        SELECT * FROM (
            SELECT E.*,
                   ROW_NUMBER() OVER (PARTITION BY E.EMEMPL
                                      ORDER BY $empPickOrder) AS MOLR_RN
              FROM SGHDSDATA.HREMPL E
        ) Z
        WHERE MOLR_RN = 1
    )";

// ── Build WHERE ───────────────────────────────────────────────────────────────
$safeStart  = molr_esc($startDate);
$safeEnd    = molr_esc($endDate);
$whereParts = array("T01.LDDATE BETWEEN DATE('$safeStart') AND DATE('$safeEnd')");

if ($activeEmpPred !== '') {
    $whereParts[] = $activeEmpPred;
}
if ($filterEName !== '') {
    $safe = molr_esc($filterEName);
    $whereParts[] = "$nameExpr = '$safe'";
}
if ($filterOrd !== '') {
    $safe = molr_esc($filterOrd);
    $whereParts[] = "RTRIM(T01.LDORD) LIKE '%$safe%'";
}
if ($filterDept !== '') {
    $safe = molr_esc($filterDept);
    $whereParts[] = "TRIM(T03.WCDEPT) = '$safe'";
}
if ($filterDName !== '') {
    $safe = molr_esc($filterDName);
    $whereParts[] = "TRIM(T05.EANAME) = '$safe'";
}
if ($filterWc !== '') {
    $safe = molr_esc($filterWc);
    $whereParts[] = "RTRIM(T01.LDWC) = '$safe'";
}
$where = implode(' AND ', $whereParts);

// ── Main SQL (CTE) ────────────────────────────────────────────────────────────
// HDMLDM holds ~525k rows going back to 2010, so an early From Date can select
// far more than a browser can render. The detail grid is capped at MOLR_MAX_ROWS
// and the totals come from a separate GROUP BY that is never capped — so the
// TOTALS lines stay correct even when the visible detail is trimmed.
define('MOLR_MAX_ROWS', 20000);

// EARNED hours = what the standard says the job should have taken. Setup rows
// are measured against LDSUHR, everything else against the computed STDHRS.
// Variance and efficiency are both derived from it so they can never disagree:
//   VARIANCE   = EARNED - WORKED   (negative = over standard = bad)
//   EFFICIENCY = EARNED / WORKED   (100% = exactly on standard)
$earnExpr = "CASE WHEN LDLBTY='S' THEN LDSUHR ELSE STDHRS END";
$varExpr  = "(($earnExpr) - LDWHRS)";
$vcExpr   = "CASE WHEN LDLBTY='S' THEN (LDSUHR - LDWHRS) * LDSSR
                  ELSE (STDHRS - LDWHRS) * LDSLR END";

$baseCte = "
    WITH $empCte,
    BASE AS (
        SELECT
            T01.LDDATE,
            T01.LDEMP,
            $nameExpr         AS EMPNAME,
            $termSelExpr      AS EMTRDT,
            TRIM(T01.LDORD)   AS LDORD,
            TRIM(T03.WCDEPT)  AS WCDEPT,
            TRIM(T05.EANAME)  AS EANAME,
            TRIM(T01.LDWC)    AS LDWC,
            TRIM(T03.WCDESC)  AS WCDESC,
            T01.LDSEQN,
            T01.LDMPON,
            TRIM(T01.LDPN)    AS LDPN,
            T01.LDLBTY,
            CASE WHEN T01.LDLBTY='R' THEN 'Rework'
                 WHEN T01.LDLBTY='I' THEN 'Indirect'
                 WHEN T01.LDLBTY='S' THEN 'Setup'
                 ELSE 'Direct' END                                          AS LABORDEF,
            CASE WHEN T01.LDLBTY='S' AND T01.LDWHRS < 0 THEN T01.LDSUHR
                 WHEN T01.LDLBTY='D'                     THEN 0
                 WHEN T01.LDWHRS < 0                     THEN 0
                 WHEN T01.LDWHRS > 0                     THEN T01.LDSUHR
                 ELSE 0 END                                                 AS CALCSUHRS,
            CASE WHEN T01.LDLBTY='S' THEN 'H'
                 ELSE T01.LDMHRC END                                        AS MSDHRSCODE,
            CASE WHEN T01.LDLBTY='S'                        THEN 0
                 WHEN T01.LDMHRC='P' AND T01.LDQTYC = 0    THEN 0
                 WHEN T01.LDMHRC='P'
                      THEN T01.LDMHRS * T01.LDMPON * T01.LDQTYC
                 WHEN T01.LDMHRC='M' AND T01.LDQTYC = 0    THEN 0
                 WHEN T01.LDMHRC='M'
                      THEN T01.LDMHRS * T01.LDMPON * T01.LDQTYC / 1000
                 ELSE 0 END                                                 AS STDHRS,
            T01.LDWHRS,
            T01.LDSUHR,
            T01.LDSSR,
            T01.LDSLR,
            T02.OHCQTY,
            T01.LDQTYC,
            T01.LDRSCR
        FROM SGHDSDATA.HDMLDM T01
        JOIN SGHDSDATA.HDMOHM T02
            ON  T01.LDORD = T02.OHORD
            AND T01.LDPLT = T02.OHPLT
        JOIN SGHDSDATA.HDMWCM T03
            ON  T01.LDWC   = T03.WCWC
            AND T01.LDDEPT = T03.WCDEPT
            AND T01.LDPLT  = T03.WCPLT
        LEFT JOIN EMP1 T04
            ON T01.LDEMP = T04.EMEMPL
        LEFT JOIN SGHDSDATA.PREXAC T05
            ON TRIM(T03.WCDEPT) = TRIM(T05.EADEPT)
        WHERE $where
    )";

// Detail rows. $sql is uncapped and feeds the Excel export (Excel can take the
// full set); $sqlDetail is the capped copy the HTML grid uses.
$detailSelect = "
    SELECT
        LDDATE, LDEMP, EMPNAME, EMTRDT, LDORD, WCDEPT, EANAME, LDWC, WCDESC,
        LDSEQN, LDMPON, LDPN, LDLBTY, LABORDEF,
        CALCSUHRS, MSDHRSCODE, STDHRS, LDWHRS,
        $varExpr AS VARIANCE,
        $earnExpr AS EARNEDHRS,
        OHCQTY, LDQTYC, LDRSCR,
        $vcExpr AS VARCOST
    FROM BASE
    ORDER BY LDDATE ASC, LDORD ASC, LDSEQN ASC, LDEMP ASC";

$sql       = $baseCte . $detailSelect;
$sqlDetail = $sql . "\n    FETCH FIRST " . (MOLR_MAX_ROWS + 1) . " ROWS ONLY";

// Per-date totals over the FULL filtered set (never capped).
$sqlTotals = $baseCte . "
    SELECT
        LDDATE,
        SUM(CALCSUHRS) AS CALCSUHRS,
        SUM(STDHRS)    AS STDHRS,
        SUM(LDWHRS)    AS LDWHRS,
        SUM($varExpr)  AS VARIANCE,
        SUM($earnExpr) AS EARNEDHRS,
        SUM(OHCQTY)    AS OHCQTY,
        SUM(LDQTYC)    AS LDQTYC,
        SUM(LDRSCR)    AS LDRSCR,
        SUM($vcExpr)   AS VARCOST,
        COUNT(*)       AS NROWS
    FROM BASE
    GROUP BY LDDATE
    ORDER BY LDDATE ASC";

// ── Work-center dropdown options ──────────────────────────────────────────────
$wcOptions = array();
$wcSql     = "SELECT DISTINCT TRIM(WCWC) AS WCWC, TRIM(WCDESC) AS WCDESC
              FROM SGHDSDATA.HDMWCM
              WHERE TRIM(WCDESC) <> ''
              ORDER BY WCDESC ASC";
$wcStmt = db2_exec($conn, $wcSql, array('cursor' => DB2_SCROLLABLE));
if ($wcStmt) {
    while ($wc = db2_fetch_assoc($wcStmt)) {
        $wcOptions[] = array(
            'code' => trim((string)$wc['WCWC']),
            'desc' => trim((string)$wc['WCDESC']),
        );
    }
    db2_free_stmt($wcStmt);
}

// ── Department dropdown options (WCDEPT + PREXAC name) ────────────────────────
$deptOptions = array();
$deptSql = "SELECT DISTINCT TRIM(W.WCDEPT) AS WCDEPT, TRIM(P.EANAME) AS EANAME
            FROM SGHDSDATA.HDMWCM W
            LEFT JOIN SGHDSDATA.PREXAC P
                ON TRIM(W.WCDEPT) = TRIM(P.EADEPT)
            WHERE TRIM(W.WCDEPT) <> ''
            ORDER BY WCDEPT ASC";
$deptStmt = db2_exec($conn, $deptSql, array('cursor' => DB2_SCROLLABLE));
if ($deptStmt) {
    while ($d = db2_fetch_assoc($deptStmt)) {
        $deptOptions[] = array(
            'code' => trim((string)$d['WCDEPT']),
            'name' => trim((string)$d['EANAME']),
        );
    }
    db2_free_stmt($deptStmt);
}

// ── Employee dropdown options (number + name) ────────────────────────────────
$empOptions   = array();
// Same active/terminated rule as the grid, so the picker matches what's shown.
$empActiveSql = ($activeEmpPred !== '') ? " AND $activeEmpPred" : '';
$empSql = "WITH $empCte
           SELECT DISTINCT T01.LDEMP AS LDEMP, $nameExpr AS EMPNAME
           FROM SGHDSDATA.HDMLDM T01
           LEFT JOIN EMP1 T04
               ON T01.LDEMP = T04.EMEMPL
           WHERE T01.LDEMP > 0$empActiveSql
           ORDER BY EMPNAME ASC, LDEMP ASC";
$empStmt = db2_exec($conn, $empSql, array('cursor' => DB2_SCROLLABLE));
if ($empStmt) {
    while ($e = db2_fetch_assoc($empStmt)) {
        $empOptions[] = array(
            'num'  => (int)$e['LDEMP'],
            'name' => trim((string)$e['EMPNAME']),
        );
    }
    db2_free_stmt($empStmt);
}

// ── Totals helpers ────────────────────────────────────────────────────────────
function molr_new_tot() {
    return array(
        'CALCSUHRS' => 0.0,
        'STDHRS'    => 0.0,
        'LDWHRS'    => 0.0,
        'VARIANCE'  => 0.0,
        'EARNEDHRS' => 0.0,
        'OHCQTY'    => 0,
        'LDQTYC'    => 0,
        'LDRSCR'    => 0,
        'VARCOST'   => 0.0,
    );
}
function molr_add_tot(&$t, $r) {
    $t['CALCSUHRS'] += (float)$r['CALCSUHRS'];
    $t['STDHRS']    += (float)$r['STDHRS'];
    $t['LDWHRS']    += (float)$r['LDWHRS'];
    $t['VARIANCE']  += (float)$r['VARIANCE'];
    $t['EARNEDHRS'] += (float)$r['EARNEDHRS'];
    $t['OHCQTY']    += (int)$r['OHCQTY'];
    $t['LDQTYC']    += (int)$r['LDQTYC'];
    $t['LDRSCR']    += (int)$r['LDRSCR'];
    $t['VARCOST']   += (float)$r['VARCOST'];
}

// ── CSV / Excel export ────────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if ($stmt) {
        while ($r = db2_fetch_assoc($stmt)) { $rows[] = $r; }
        db2_free_stmt($stmt);
    }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="MODailyLaborReport_'
        . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array(
        'Date', 'Emp #', 'Emp Name', 'Term Date', 'MO #', 'Dept #', 'Dept Name', 'Work Ctr #', 'WC Description',
        'Seq', 'Std Crew Sz', 'Part #', 'Labor Typ', 'Labor Definition',
        'Std Setup Hrs', 'Hrs Ref', 'Direct Std Hrs', 'Hrs Worked',
        'Variance Hrs', 'Efficiency %', 'Curr Ord Qty', 'Qty Completed', 'Qty Scrapped',
        'Labor Var Cost',
    ));

    // Emits a TOTALS line in the same 24-column shape as the data rows.
    $csvTot = function ($out, $label, $t) {
        $pct = molr_eff($t['EARNEDHRS'], $t['LDWHRS']);
        fputcsv($out, array(
            $label, '', '', '', '', '', '', '', '', '', '', '', '', '',
            number_format((float)$t['CALCSUHRS'], 2, '.', ''),
            '',
            number_format((float)$t['STDHRS'],   2, '.', ''),
            number_format((float)$t['LDWHRS'],   2, '.', ''),
            number_format((float)$t['VARIANCE'], 2, '.', ''),
            ($pct === null ? '' : number_format($pct, 1, '.', '')),
            (int)$t['OHCQTY'],
            (int)$t['LDQTYC'],
            (int)$t['LDRSCR'],
            number_format((float)$t['VARCOST'],  2, '.', ''),
        ));
    };

    // Per-date totals are computed up front so each day's TOTALS line can lead
    // its rows, matching the on-screen layout.
    $grand   = molr_new_tot();
    $csvDayT = array();
    foreach ($rows as $r) {
        $d = trim((string)$r['LDDATE']);
        if (!isset($csvDayT[$d])) { $csvDayT[$d] = molr_new_tot(); }
        molr_add_tot($csvDayT[$d], $r);
        molr_add_tot($grand, $r);
    }
    if (!empty($rows)) { $csvTot($out, 'GRAND TOTALS', $grand); }

    $prevDate = null;
    foreach ($rows as $r) {
        $thisDate = trim((string)$r['LDDATE']);
        if ($thisDate !== $prevDate) {
            $csvTot($out, 'TOTALS ' . molr_date($thisDate), $csvDayT[$thisDate]);
            $prevDate = $thisDate;
        }
        $rowPct = molr_eff($r['EARNEDHRS'], $r['LDWHRS']);
        fputcsv($out, array(
            molr_date($r['LDDATE']),
            (int)$r['LDEMP'],
            trim((string)$r['EMPNAME']),
            molr_cymd($r['EMTRDT']),
            trim((string)$r['LDORD']),
            trim((string)$r['WCDEPT']),
            trim((string)$r['EANAME']),
            trim((string)$r['LDWC']),
            trim((string)$r['WCDESC']),
            (int)$r['LDSEQN'],
            (int)$r['LDMPON'],
            trim((string)$r['LDPN']),
            trim((string)$r['LDLBTY']),
            trim((string)$r['LABORDEF']),
            number_format((float)$r['CALCSUHRS'], 2, '.', ''),
            trim((string)$r['MSDHRSCODE']),
            number_format((float)$r['STDHRS'],    2, '.', ''),
            number_format((float)$r['LDWHRS'],    2, '.', ''),
            number_format((float)$r['VARIANCE'],  2, '.', ''),
            ($rowPct === null ? '' : number_format($rowPct, 1, '.', '')),
            (int)$r['OHCQTY'],
            (int)$r['LDQTYC'],
            (int)$r['LDRSCR'],
            number_format((float)$r['VARCOST'],   2, '.', ''),
        ));
    }
    fclose($out);
    exit;
}

// ── Normal page load ──────────────────────────────────────────────────────────
$stmt = db2_exec($conn, $sqlDetail, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) { $rows[] = $r; }
    db2_free_stmt($stmt);
} else {
    $sqlErr = db2_stmt_errormsg();
}

// One row over the cap means the grid is trimmed; drop it and flag it.
$truncated = (count($rows) > MOLR_MAX_ROWS);
if ($truncated) { $rows = array_slice($rows, 0, MOLR_MAX_ROWS); }
$rowCount = count($rows);

// ── Grand totals + per-date subtotals (from the uncapped GROUP BY) ────────────
$tot         = molr_new_tot();
$dateTots    = array();   // 'YYYY-MM-DD' => totals array, in date order
$dateRowCnt  = array();   // 'YYYY-MM-DD' => rows behind that date's totals
$totalRowCnt = 0;
$totStmt = db2_exec($conn, $sqlTotals, array('cursor' => DB2_SCROLLABLE));
if ($totStmt) {
    while ($t = db2_fetch_assoc($totStmt)) {
        $d = trim((string)$t['LDDATE']);
        $dateTots[$d]   = molr_new_tot();
        molr_add_tot($dateTots[$d], $t);
        $dateRowCnt[$d] = (int)$t['NROWS'];
        $totalRowCnt   += (int)$t['NROWS'];
        molr_add_tot($tot, $t);
    }
    db2_free_stmt($totStmt);
} elseif (!$sqlErr) {
    $sqlErr = db2_stmt_errormsg();
}

$eiBase = 'https://portal.screen-graphics.com:5601';

$exportParams           = $_GET;
$exportParams['export'] = 'csv';
$exportURL              = '?' . http_build_query($exportParams);

// Toggle link for the "include terminated this year" button — preserves every
// other filter currently in the URL.
$termParams = $_GET;
unset($termParams['export']);
$termParams['incterm'] = $includeTerm ? '0' : '1';
$termToggleURL         = '?' . http_build_query($termParams);

$jsRows = array();
foreach ($rows as $r) {
    $jsRows[] = array('moNum' => trim((string)$r['LDORD']));
}

$displayDate    = molr_date($startDate);
$displayEndDate = molr_date($endDate);
$displayRange   = ($startDate === $endDate)
    ? $displayDate
    : $displayDate . ' &ndash; ' . $displayEndDate;

/**
 * Renders one 23-column TOTALS row.
 * Sign convention: VARIANCE = earned - worked, so a NEGATIVE number means they
 * went over standard and is shown red; positive (under standard) is green.
 * $cls   extra class on the <tr> ('' = grand totals, 'date-totals' = per-date)
 * $label text for the left-hand label cell
 */
function molr_totals_row($label, $t, $cls = '', $dgroup = '') {
    $varClass = $t['VARIANCE'] < 0 ? ' unfav' : ($t['VARIANCE'] > 0 ? ' fav' : '');
    $vcClass  = $t['VARCOST']  < 0 ? ' unfav' : ($t['VARCOST']  > 0 ? ' fav' : '');
    $pct      = molr_eff($t['EARNEDHRS'], $t['LDWHRS']);

    // Headline efficiency: bar + number, so the day reads at a glance.
    $effCell = '<td class="R' . molr_eff_class($pct) . '">';
    if ($pct === null) {
        $effCell .= '&mdash;';
    } else {
        $w = max(0, min(100, $pct));   // bar caps at 100% so it stays readable
        $effCell .= '<div class="effbar"><span style="width:' . number_format($w, 1) . '%"></span></div>'
                 .  '<b>' . number_format($pct, 1) . '%</b>';
    }
    $effCell .= '</td>';

    echo '<tr class="totals-row' . ($cls !== '' ? ' ' . $cls : '') . '"'
       . ($cls === '' ? ' data-totals="1"' : ' data-subtotal="1"')
       . ($dgroup !== '' ? ' data-dgroup="' . molr_h($dgroup) . '"' : '') . '>'
       . '<td class="C" colspan="3">' . $label . '</td>'
       . str_repeat('<td></td>', 10)
       . '<td class="R">' . molr_dec($t['CALCSUHRS']) . '</td>'
       . '<td></td>'
       . '<td class="R">' . molr_dec($t['STDHRS'])   . '</td>'
       . '<td class="R">' . molr_dec($t['LDWHRS'])   . '</td>'
       . '<td class="R' . $varClass . '">' . molr_dec($t['VARIANCE']) . '</td>'
       . $effCell
       . '<td class="R">' . molr_int($t['OHCQTY'])   . '</td>'
       . '<td class="R">' . molr_int($t['LDQTYC'])   . '</td>'
       . '<td class="R">' . molr_int($t['LDRSCR'])   . '</td>'
       . '<td class="R' . $vcClass . '">' . molr_curr($t['VARCOST']) . '</td>'
       . '</tr>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MO Daily Labor Report</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px;
       background: #edf1f7; color: #1a2233; }

.topbar { background: #003087; color: #fff; padding: 8px 16px;
          display: flex; align-items: center;
          justify-content: space-between; }
.topbar h1 { font-size: 15px; font-weight: 700; }
.topbar-right { display: flex; align-items: center; gap: 10px;
                font-size: 11px; color: #b8cfee; flex-shrink: 0; }
.btn-refresh { background: #1a5276; color: #fff; border: 1px solid #2980b9;
               padding: 5px 14px; border-radius: 3px; font-size: 12px;
               cursor: pointer; white-space: nowrap; }
.btn-refresh:hover { background: #21618c; }
.btn-refresh-now { background: #7B1FA2; color: #fff; border: none;
                   padding: 4px 12px; border-radius: 3px; font-size: 11px;
                   font-weight: 700; cursor: pointer; white-space: nowrap;
                   text-decoration: none; margin-left: auto; }
.btn-refresh-now:hover { background: #6a1690; color: #fff; }

.filter-bar { background: #fff; border-bottom: 1px solid #c8d0de;
              padding: 8px 16px; display: flex; align-items: center;
              gap: 12px; flex-wrap: wrap; }
.filter-lbl { font-size: 11px; font-weight: 700; color: #5a6478;
              text-transform: uppercase; letter-spacing: .5px;
              white-space: nowrap; }
.filter-group { display: flex; align-items: center; gap: 5px; }
.filter-group label { font-size: 11px; font-weight: 700; color: #5a6478;
                      white-space: nowrap; }
.filter-group input[type=text],
.filter-group input[type=date],
.filter-group select {
    border: 1px solid #b0bac8; border-radius: 3px;
    padding: 4px 7px; font-size: 12px;
    background: #fff; color: #1a2233; }
.filter-group input[type=text]  { width: 100px; }
.filter-group input[type=date]  { width: 132px; }
.filter-group select            { width: 150px; }
.filter-group select#molr-fdept  { width: 68px; }
.filter-group select#molr-fename { width: 170px; }
.filter-group select#molr-fdname { width: 160px; }
.filter-group select#molr-fwc    { width: 180px; }
.filter-group input[type=text]:focus,
.filter-group input[type=date]:focus,
.filter-group select:focus { outline: none; border-color: #2980b9; }
.btn-apply  { background: #003087; color: #fff; border: none;
              padding: 5px 14px; border-radius: 3px; font-size: 12px;
              font-weight: 700; cursor: pointer; }
.btn-apply:hover  { background: #002060; }
/* ── Include-terminated toggle ── */
.btn-term   { background: #fff; color: #7a5c00; border: 1px solid #d4a017;
              padding: 4px 11px; border-radius: 3px; font-size: 12px;
              font-weight: 700; cursor: pointer; text-decoration: none;
              display: inline-block; white-space: nowrap; }
.btn-term:hover    { background: #fdf3d8; color: #7a5c00; }
.btn-term.on       { background: #d4a017; color: #fff; border-color: #a67c00; }
.btn-term.on:hover { background: #b98d12; color: #fff; }

.btn-clear  { background: #6c7a8d; color: #fff; border: none;
              padding: 5px 10px; border-radius: 3px; font-size: 12px;
              cursor: pointer; text-decoration: none; display: inline-block; }
.btn-clear:hover  { background: #55636f; color: #fff; }
.btn-export { background: #1a7a1a; color: #fff; border: none;
              padding: 5px 12px; border-radius: 3px; font-size: 12px;
              cursor: pointer; text-decoration: none;
              display: inline-block; white-space: nowrap; }
.btn-export:hover { background: #155a15; color: #fff; }
.meta-info  { display: flex; gap: 10px; align-items: center;
              font-size: 11px; color: #5a6478; margin-left: auto;
              flex-wrap: wrap; }
.meta-info b { color: #003087; }
.cd-lbl { color: #b06000; }

.content  { padding: 8px 12px; }
.tbl-wrap { overflow-x: auto; overflow-y: auto;
            max-height: calc(100vh - 128px); }

/* ── Table ── */
table { border-collapse: collapse; min-width: 1650px; width: 100%; }

/* Column widths via colgroup */
col.c-date  { width: 76px; }
col.c-emp   { width: 50px; }
col.c-ename { width: 118px; }
col.c-mo    { width: 74px; }
col.c-dept  { width: 48px; }
col.c-dname { width: 120px; }
col.c-wc    { width: 56px; }
col.c-wdesc { width: 108px; }
col.c-seq   { width: 36px; }
col.c-crew  { width: 44px; }
col.c-part  { width: 88px; }
col.c-ltyp  { width: 46px; }
col.c-ldef  { width: 64px; }
col.c-ssu   { width: 62px; }
col.c-href  { width: 42px; }
col.c-dsh   { width: 62px; }
col.c-hw    { width: 62px; }
col.c-var   { width: 64px; }
col.c-eff   { width: 62px; }
col.c-coq   { width: 66px; }
col.c-qtyc  { width: 66px; }
col.c-qtys  { width: 60px; }
col.c-vc    { width: 80px; }

/* ── Header ── */
thead th {
    background: #003087; color: #fff; padding: 4px 5px;
    font-size: 10px; font-weight: 700; line-height: 1.3;
    cursor: pointer; user-select: none;
    position: sticky; top: 0; z-index: 2;
    vertical-align: bottom; white-space: normal;
    word-break: break-word; }
thead th:hover { background: #002060; }
thead th.sort-asc::after  { content: ' \25B2'; font-size: 8px; }
thead th.sort-desc::after { content: ' \25BC'; font-size: 8px; }

/* ── Totals row ── */
tr.totals-row td {
    background: #d4e6f7; font-weight: 700; font-size: 11px;
    border-top: 2px solid #2471a3; border-bottom: 2px solid #2471a3;
    padding: 4px 5px; white-space: nowrap; }

/* ── Per-date subtotal row (light blue) ── */
tr.totals-row.date-totals td {
    background: #cfe6fa;
    border-top: 2px solid #6aa8d8; border-bottom: 2px solid #6aa8d8; }

/* ── Data rows ── */
th.L, td.L { text-align: left; }
th.R, td.R { text-align: right; }
th.C, td.C { text-align: center; }
td { padding: 3px 5px; border-bottom: 1px solid #e4e8ef;
     white-space: nowrap; vertical-align: middle; font-size: 11px; }
tr:nth-child(even):not(.totals-row) td { background: #f4f7fc; }
tr:hover:not(.totals-row) td { background: #eaf0fb; }

.mo-link { color: #003087; text-decoration: none; font-weight: 700; }
.mo-link:hover { text-decoration: underline; color: #0050c0; }
/* Over standard (negative variance) = unfavourable = red. Under = green. */
.unfav { color: #cc0000; font-weight: 700; }
.fav   { color: #177a17; }

/* ── Efficiency ── */
.eff-good { color: #177a17; font-weight: 700; }
.eff-warn { color: #b06000; font-weight: 700; }
.eff-bad  { color: #cc0000; font-weight: 700; }
.effbar { display: block; height: 4px; width: 100%; background: #ccd6e4;
          border-radius: 2px; overflow: hidden; margin: 0 0 2px; }
.effbar span { display: block; height: 100%; border-radius: 2px;
               background: currentColor; }

/* Employee terminated this year (only visible with the toggle on) */
td.term-emp { font-style: italic; color: #7a5c00; }
.term-tag { background: #fdf0cf; border: 1px solid #e0be6a; color: #7a5c00;
            border-radius: 3px; padding: 0 3px; font-size: 9px;
            font-style: normal; font-weight: 700; white-space: nowrap; }
.err   { background: #fdd; color: #900; padding: 8px 12px;
         border-radius: 4px; margin-bottom: 8px;
         font-family: monospace; font-size: 12px; }
.warn  { background: #fff3cd; color: #856404; border: 1px solid #f0c060;
         padding: 8px 12px; border-radius: 4px; margin-bottom: 8px;
         font-size: 12px; line-height: 1.5; }
.empty { text-align: center; padding: 40px; color: #888; font-size: 14px; }

/* ── Standard refresh bar (matches BookingsDashboard.php) ── */
.refresh-bar { background: #e8f0fb; border-bottom: 1px solid #bdd0ee; padding: 4px 14px; display: flex; align-items: center; gap: 14px; font-size: 11px; color: #5a6478; flex-shrink: 0; }
.refresh-dot { width: 8px; height: 8px; border-radius: 50%; background: #1a7a3c; animation: pulse 2s infinite; flex-shrink: 0; }
.refresh-dot--off { background: #94a3b8; animation: none; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
.refresh-progress { flex: 1; max-width: 160px; height: 4px; background: #d0dced; border-radius: 2px; overflow: hidden; }
.refresh-fill { height: 100%; background: #0055b3; border-radius: 2px; transition: width 1s linear; }
.refresh-pill { background: #fff; border: 1px solid #c8d0de; border-radius: 12px; padding: 2px 10px; font-size: 11px; font-weight: 600; white-space: nowrap; }
</style>
</head>
<body>
<?php require_once dirname(__FILE__) . '/../SgReportNav.php'; ?>

<!-- ── Standard SG title bar (gray gradient + Back to EIP + Logout) ── -->
<div style="display:flex;align-items:center;gap:10px;padding:10px 14px;
            background:linear-gradient(to right,
                #111827 0%, #1F2937 25%, #374151 55%, #4B5563 78%, #6B7280 100%);
            border-bottom:3px solid rgba(0,0,0,0.15);">
  <h1 style="font-size:22px;color:#fff;margin:0;flex:1;font-weight:bold;
              text-shadow:0 1px 3px rgba(0,0,0,0.4);">MO Daily Labor Report</h1>
  <a href="<?php echo molr_h($eiBase . '/Welcome.php?baseVar=BaseConfiguration.icl&eID='
              . rawurlencode($eID) . '&portal=9999999999'); ?>"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#06B6D4;
            color:#fff;text-decoration:none;border-radius:4px;border:1px solid #0891B2;
            white-space:nowrap;display:inline-block;">&#8592; Back to EIP</a>
  <a href="https://screen-graphics.com/"
     style="padding:4px 14px;font-size:12px;font-weight:700;background:#CC1F20;
            color:#fff;text-decoration:none;border-radius:4px;border:1px solid #8b1010;
            white-space:nowrap;display:inline-block;">Logout</a>
</div>
<!-- ── Standard toolbar: refresh + filter bars (left) | Refresh + Export (right) ── -->
<form method="get" action="">
<div style="display:flex;align-items:stretch;border-bottom:2px solid #D1D5DB;">
  <div style="flex:1;display:flex;flex-direction:column;min-width:0;">
    <?php if ($autoRefresh): ?>
    <div style="background:#2563EB;border-bottom:1px solid #1d4ed8;padding:4px 14px;display:flex;align-items:center;gap:14px;font-size:11px;color:#fff;">
      <div class="refresh-dot" style="background:#16A34A;"></div>
      <span>Live &ndash; auto-refreshes every 15 min (M&ndash;F, 7:00am&ndash;5:00pm ET)</span>
      <div class="refresh-progress" style="background:rgba(255,255,255,0.18);"><div class="refresh-fill" id="molr-prog" style="width:100%;background:#3B82F6;"></div></div>
      <span>Next refresh in: <strong id="molr-cd">15:00</strong></span>
      <span class="refresh-pill" style="color:#2563EB;">Last refresh: <strong><?php echo date('g:i:s A'); ?></strong></span>
      <span class="refresh-pill" style="background:#fff3cd;border-color:#f0c060;color:#856404;">Showing: <?php echo $displayRange; ?><?php if ($includeTerm): ?> &middot; incl. terminated since <?php echo molr_h($displayDate); ?><?php endif; ?></span>
    </div>
    <?php else: ?>
    <div style="background:#2563EB;border-bottom:1px solid #1d4ed8;padding:4px 14px;display:flex;align-items:center;gap:14px;font-size:11px;color:#fff;">
      <div class="refresh-dot refresh-dot--off" style="background:#94a3b8;"></div>
      <span>Auto-refresh paused &ndash; outside M&ndash;F 7:00am&ndash;5:00pm ET. Use Refresh.</span>
      <span style="flex:1"></span>
      <span class="refresh-pill" style="color:#2563EB;">Last refresh: <strong><?php echo date('g:i:s A'); ?></strong></span>
      <span class="refresh-pill" style="background:#fff3cd;border-color:#f0c060;color:#856404;">Showing: <?php echo $displayRange; ?><?php if ($includeTerm): ?> &middot; incl. terminated since <?php echo molr_h($displayDate); ?><?php endif; ?></span>
    </div>
    <?php endif; ?>
    <div class="filter-bar" style="background:#F7F7F7;border-bottom:none;">
  <span class="filter-lbl">Filter:</span>
  <div class="filter-group">
    <label for="molr-fdate">From Date</label>
    <input type="date" id="molr-fdate" name="fdate"
           value="<?php echo molr_h($filterDate); ?>"
           title="Shows all labor from this date through the current date">
    <span style="font-size:11px;color:#5a6478;white-space:nowrap;">
      thru <b style="color:#003087;"><?php echo molr_h($displayEndDate); ?></b>
    </span>
  </div>
  <div class="filter-group">
    <label for="molr-fename">Emp Name</label>
    <select id="molr-fename" name="fename">
      <option value="">(All)</option>
      <?php
        $seenNames = array();
        foreach ($empOptions as $emp):
          $nm = trim((string)$emp['name']);
          if ($nm === '' || isset($seenNames[$nm])) continue;
          $seenNames[$nm] = true;
      ?>
      <option value="<?php echo molr_h($nm); ?>"
        <?php echo ($filterEName === $nm) ? 'selected' : ''; ?>>
        <?php echo molr_h($nm); ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="filter-group">
    <label for="molr-ford">MO #</label>
    <input type="text" id="molr-ford" name="ford"
           value="<?php echo molr_h($filterOrd); ?>"
           placeholder="Order #">
  </div>
  <div class="filter-group">
    <label for="molr-fdept">Dept #</label>
    <select id="molr-fdept" name="fdept">
      <option value="">(All Depts)</option>
      <?php foreach ($deptOptions as $dp): ?>
      <option value="<?php echo molr_h($dp['code']); ?>"
        <?php echo ($filterDept === $dp['code']) ? 'selected' : ''; ?>>
        <?php echo molr_h($dp['code']); ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="filter-group">
    <label for="molr-fdname">Dept Name</label>
    <select id="molr-fdname" name="fdname">
      <option value="">(All Depts)</option>
      <?php
        $seenDeptNames = array();
        foreach ($deptOptions as $dp):
          $dn = trim((string)$dp['name']);
          if ($dn === '' || isset($seenDeptNames[$dn])) continue;
          $seenDeptNames[$dn] = true;
      ?>
      <option value="<?php echo molr_h($dn); ?>"
        <?php echo ($filterDName === $dn) ? 'selected' : ''; ?>>
        <?php echo molr_h($dn); ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="filter-group">
    <label for="molr-fwc">Work Ctr</label>
    <select id="molr-fwc" name="fwc">
      <option value="">(All Work Centers)</option>
      <?php foreach ($wcOptions as $wc): ?>
      <option value="<?php echo molr_h($wc['code']); ?>"
        <?php echo ($filterWc === $wc['code']) ? 'selected' : ''; ?>>
        <?php echo molr_h($wc['desc']); ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <!-- Keeps the toggle alive when one of the other filters auto-submits -->
  <input type="hidden" name="incterm" value="<?php echo $includeTerm ? '1' : '0'; ?>">
  <a class="btn-term<?php echo $includeTerm ? ' on' : ''; ?>"
     href="<?php echo molr_h($termToggleURL); ?>"
     title="<?php echo $includeTerm
        ? 'Currently including employees terminated on or after ' . $displayDate
          . ' — click to show active employees only'
        : 'Click to also include employees terminated on or after ' . $displayDate; ?>">
    <?php echo $includeTerm ? '&#10003;' : '&#43;'; ?>
    Incl. Terminated Since <?php echo molr_h($displayDate); ?>
  </a>
  <a class="btn-clear" href="?">Clear</a>
      <b style="margin-left:auto;white-space:nowrap;font-size:12px;color:#111827;">
        <?php if ($truncated): ?>
          <?php echo number_format($rowCount); ?>&nbsp;of&nbsp;<?php echo number_format($totalRowCnt); ?>&nbsp;rows
        <?php else: ?>
          <?php echo number_format($rowCount); ?>&nbsp;row<?php echo $rowCount !== 1 ? 's' : ''; ?>
        <?php endif; ?>
      </b>
    </div><!-- /filter-bar -->
  </div><!-- /left column -->

  <!-- Right column: Refresh directly above Export -->
  <div style="display:flex;flex-direction:column;align-items:stretch;justify-content:center;gap:4px;padding:6px 10px;background:#F7F7F7;border-left:2px solid #D1D5DB;">
    <button type="button" onclick="location.reload();"
            style="font-size:12px;padding:3px 14px;cursor:pointer;border:1px solid #4a0f6e;border-radius:3px;background:#7B1FA2;color:#fff;font-weight:bold;white-space:nowrap;text-align:center;">&#x21BB; Refresh</button>
    <a href="<?php echo molr_h($exportURL); ?>"
       style="background:#1DA032;color:#fff;padding:3px 14px;border-radius:3px;font-size:12px;font-weight:bold;text-decoration:none;white-space:nowrap;text-align:center;display:block;">&#8595; Export to Excel</a>
  </div>

</div><!-- /toolbar flex -->
</form>
<script>
// Auto-filter: submit the filter form as soon as any control changes
(function () {
    var bar = document.querySelector('.filter-bar');
    if (!bar) return;
    var ctrls = bar.querySelectorAll('select, input');
    for (var i = 0; i < ctrls.length; i++) {
        ctrls[i].addEventListener('change', function () {
            if (this.form) this.form.submit();
        });
    }
}());
</script>

<div class="content">
<?php if ($sqlErr): ?>
<div class="err">Query error: <?php echo molr_h($sqlErr); ?></div>
<?php endif; ?>
<?php if ($truncated): ?>
<div class="warn">
  Showing the first <b><?php echo number_format(MOLR_MAX_ROWS); ?></b> of
  <b><?php echo number_format($totalRowCnt); ?></b> labor rows for
  <?php echo $displayRange; ?>.
  <b>The TOTALS lines below cover all <?php echo number_format($totalRowCnt); ?> rows</b>,
  not just the ones displayed. Narrow the date range or add a filter to see every
  detail row on screen &mdash; or use <b>Export to Excel</b>, which is never trimmed.
</div>
<?php endif; ?>
<div class="tbl-wrap">
<table id="molr-grid">
  <colgroup>
    <col class="c-date"><col class="c-emp"><col class="c-ename">
    <col class="c-mo"><col class="c-dept"><col class="c-dname"><col class="c-wc"><col class="c-wdesc">
    <col class="c-seq"><col class="c-crew"><col class="c-part">
    <col class="c-ltyp"><col class="c-ldef">
    <col class="c-ssu"><col class="c-href"><col class="c-dsh">
    <col class="c-hw"><col class="c-var"><col class="c-eff">
    <col class="c-coq"><col class="c-qtyc"><col class="c-qtys">
    <col class="c-vc">
  </colgroup>
  <thead>
    <tr>
      <th class="C">Date</th>
      <th class="R">Emp #</th>
      <th class="L">Emp<br>Name</th>
      <th class="L">MO #</th>
      <th class="L">DEPT</th>
      <th class="L">Dept<br>Name</th>
      <th class="L">Work<br>Ctr #</th>
      <th class="L">WC<br>Desc</th>
      <th class="R">Seq</th>
      <th class="R">Crew<br>Sz</th>
      <th class="L">Part #</th>
      <th class="C">Lbr<br>Typ</th>
      <th class="L">Labor<br>Defn</th>
      <th class="R">Std<br>Setup<br>Hrs</th>
      <th class="C">Hrs<br>Ref</th>
      <th class="R">Direct<br>Std Hrs</th>
      <th class="R">Hrs<br>Worked</th>
      <th class="R" title="Earned (standard) hours minus hours worked. Negative = over standard.">Variance<br>Hrs</th>
      <th class="R" title="Earned (standard) hours &divide; hours worked. 100% = exactly on standard.">Effic<br>%</th>
      <th class="R">Curr<br>Ord Qty</th>
      <th class="R">Qty<br>Complt</th>
      <th class="R">Qty<br>Scrpd</th>
      <th class="R">Labor<br>Var Cost</th>
    </tr>
  </thead>
  <tbody>

<?php if (!empty($rows)): ?>
  <!-- Grand totals row (pinned at the top) -->
  <?php molr_totals_row('TOTALS &mdash; ALL DATES', $tot); ?>
<?php endif; ?>

<?php if (empty($rows) && !$sqlErr): ?>
  <tr>
    <td colspan="23" class="empty">
      No labor records found for <?php echo $displayRange; ?>
      <?php if ($filterEName !== '' || $filterOrd !== '' || $filterDept !== '' || $filterDName !== '' || $filterWc !== ''): ?>
        with the current filter
      <?php endif; ?>.
    </td>
  </tr>
<?php endif; ?>

<?php
$prevDate = null;
foreach ($rows as $idx => $r):
    $dtRaw    = trim((string)$r['LDDATE']);
    $variance = (float)$r['VARIANCE'];
    $varcost  = (float)$r['VARCOST'];
    // Negative variance = over standard = unfavourable = red.
    $varClass = $variance < 0 ? ' unfav' : ($variance > 0 ? ' fav' : '');
    $vcClass  = $varcost  < 0 ? ' unfav' : ($varcost  > 0 ? ' fav' : '');
    $rowPct   = molr_eff($r['EARNEDHRS'], $r['LDWHRS']);

    // Each day opens with its light-blue TOTALS line
    if ($dtRaw !== $prevDate) {
        molr_totals_row('TOTALS &mdash; ' . molr_h(molr_date($dtRaw)),
                        $dateTots[$dtRaw], 'date-totals', $dtRaw);
        $prevDate = $dtRaw;
    }
?>
  <tr data-dgroup="<?php echo molr_h($dtRaw); ?>">
    <td class="C" data-val="<?php echo molr_h($dtRaw); ?>">
      <?php echo molr_h(molr_date($dtRaw)); ?>
    </td>
    <td class="R" data-val="<?php echo (int)$r['LDEMP']; ?>">
      <?php echo (int)$r['LDEMP']; ?>
    </td>
    <?php $termOn = molr_cymd($r['EMTRDT']); ?>
    <td class="L<?php echo $termOn !== '' ? ' term-emp' : ''; ?>"
        <?php if ($termOn !== ''): ?>title="Terminated <?php echo molr_h($termOn); ?>"<?php endif; ?>>
      <?php echo molr_h(trim((string)$r['EMPNAME'])); ?><?php
        if ($termOn !== '') { echo ' <span class="term-tag">T ' . molr_h($termOn) . '</span>'; }
      ?>
    </td>
    <td class="L">
      <a class="mo-link" href="javascript:molrOpenMO(<?php echo $idx; ?>)">
        <?php echo molr_h(trim((string)$r['LDORD'])); ?>
      </a>
    </td>
    <td class="L"><?php echo molr_h(trim((string)$r['WCDEPT'])); ?></td>
    <td class="L"><?php echo molr_h(trim((string)$r['EANAME'])); ?></td>
    <td class="L"><?php echo molr_h(trim((string)$r['LDWC'])); ?></td>
    <td class="L"><?php echo molr_h(trim((string)$r['WCDESC'])); ?></td>
    <td class="R" data-val="<?php echo (int)$r['LDSEQN']; ?>">
      <?php echo (int)$r['LDSEQN']; ?>
    </td>
    <td class="R" data-val="<?php echo (int)$r['LDMPON']; ?>">
      <?php echo (int)$r['LDMPON']; ?>
    </td>
    <?php $partNum = trim((string)$r['LDPN']); ?>
    <td class="L"><?php if ($partNum !== ''): ?><a class="mo-link" target="_blank"
      href="<?php echo molr_h($eiBase
        . '/harris-CGI/ItemSelect.d2w/REPORT'
        . '?baseVar=BaseConfiguration.icl&portal=ITEM'
        . '&eID='        . urlencode($eID)
        . '&itemNumber=' . urlencode($partNum)); ?>"
    ><?php echo molr_h($partNum); ?></a><?php else: ?><?php endif; ?></td>
    <td class="C"><?php echo molr_h(trim((string)$r['LDLBTY'])); ?></td>
    <td class="L"><?php echo molr_h(trim((string)$r['LABORDEF'])); ?></td>
    <td class="R" data-val="<?php echo (float)$r['CALCSUHRS']; ?>">
      <?php echo molr_dec($r['CALCSUHRS']); ?>
    </td>
    <td class="C"><?php echo molr_h(trim((string)$r['MSDHRSCODE'])); ?></td>
    <td class="R" data-val="<?php echo (float)$r['STDHRS']; ?>">
      <?php echo molr_dec($r['STDHRS']); ?>
    </td>
    <td class="R" data-val="<?php echo (float)$r['LDWHRS']; ?>">
      <?php echo molr_dec($r['LDWHRS']); ?>
    </td>
    <td class="R<?php echo $varClass; ?>" data-val="<?php echo $variance; ?>">
      <?php echo molr_dec($variance); ?>
    </td>
    <?php // No data-val when blank, so the em dash sorts last instead of as zero ?>
    <td class="R<?php echo molr_eff_class($rowPct); ?>"
        <?php if ($rowPct !== null): ?>data-val="<?php echo round($rowPct, 1); ?>"<?php endif; ?>>
      <?php echo molr_eff_txt($rowPct); ?>
    </td>
    <td class="R" data-val="<?php echo (int)$r['OHCQTY']; ?>">
      <?php echo molr_int($r['OHCQTY']); ?>
    </td>
    <td class="R" data-val="<?php echo (int)$r['LDQTYC']; ?>">
      <?php echo molr_int($r['LDQTYC']); ?>
    </td>
    <td class="R" data-val="<?php echo (int)$r['LDRSCR']; ?>">
      <?php echo molr_int($r['LDRSCR']); ?>
    </td>
    <td class="R<?php echo $vcClass; ?>" data-val="<?php echo $varcost; ?>">
      <?php echo molr_curr($varcost); ?>
    </td>
  </tr>
<?php endforeach; ?>
  </tbody>
</table>
</div>
</div>

<script>
var MOLR_BASE = <?php echo json_encode($eiBase); ?>;
var MOLR_EID  = <?php echo json_encode($eID); ?>;
var MOLR_ROWS = <?php echo json_encode(array_values($jsRows)); ?>;

function molrOpenMO(idx) {
    var r = MOLR_ROWS[idx];
    if (!r || !r.moNum) return;
    window.open(
        MOLR_BASE + '/harris-CGI/SelectMfgOrder.d2w/REPORT'
        + '?baseVar=BaseConfiguration.icl&portal=MFGMGMT'
        + '&eID='       + MOLR_EID
        + '&mfgOrder='  + encodeURIComponent(r.moNum)
        + '&plantNumber=1',
        '_blank'
    );
}

// ── Sortable columns (totals row stays pinned at top) ─────────────────────────
(function () {
    var tbl    = document.getElementById('molr-grid');
    if (!tbl) return;
    var tbody  = tbl.querySelector('tbody');
    var ths    = tbl.querySelectorAll('thead th');
    var state  = { col: 3, dir: 1 };

    function cellVal(td) {
        if (td.hasAttribute('data-val')) {
            var raw = td.getAttribute('data-val');
            var n   = parseFloat(raw);
            return isNaN(n) ? raw.toLowerCase() : n;
        }
        var t = td.textContent.replace(/[\$,]/g, '').trim();
        if (t === '' || t === '—') return null;
        var n = parseFloat(t);
        return isNaN(n) ? t.toLowerCase() : n;
    }

    function cmp(a, b, col) {
        var va = cellVal(a.cells[col]);
        var vb = cellVal(b.cells[col]);
        if (va === null && vb === null) return 0;
        if (va === null) return  1;
        if (vb === null) return -1;
        if (va < vb) return -state.dir;
        if (va > vb) return  state.dir;
        return 0;
    }

    // Rows are grouped by date, each group led by its own TOTALS line.
    // Sorting re-orders rows *within* each date group so the subtotals stay
    // correct; sorting on the Date column re-orders the groups themselves.
    function sortBy(col) {
        state.dir = (state.col === col) ? -state.dir : 1;
        state.col = col;

        var all    = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        var totRow = null;
        var groups = [];        // [{key, rows:[], sub:tr}]
        var byKey  = {};
        all.forEach(function (tr) {
            if (tr.getAttribute('data-totals') === '1') { totRow = tr; return; }
            var key = tr.getAttribute('data-dgroup');
            if (key === null) return;               // e.g. the "no rows" message
            if (!byKey[key]) { byKey[key] = { key: key, rows: [], sub: null }; groups.push(byKey[key]); }
            if (tr.getAttribute('data-subtotal') === '1') { byKey[key].sub = tr; }
            else { byKey[key].rows.push(tr); }
        });

        if (col === 0) {
            // Date column: order the groups, leave each group's rows alone
            groups.sort(function (a, b) {
                if (a.key < b.key) return -state.dir;
                if (a.key > b.key) return  state.dir;
                return 0;
            });
        } else {
            groups.forEach(function (g) {
                g.rows.sort(function (a, b) { return cmp(a, b, col); });
            });
        }

        if (totRow) { tbody.appendChild(totRow); }
        groups.forEach(function (g) {
            if (g.sub) { tbody.appendChild(g.sub); }   // day TOTALS leads its rows
            g.rows.forEach(function (tr) { tbody.appendChild(tr); });
        });
        // Keep the grand totals pinned at the top
        if (totRow) { tbody.insertBefore(totRow, tbody.firstChild); }

        for (var i = 0; i < ths.length; i++) {
            ths[i].className = ths[i].className
                .replace(/\s*sort-(asc|desc)/g, '');
        }
        ths[col].className += (state.dir === 1 ? ' sort-asc' : ' sort-desc');
    }

    for (var i = 0; i < ths.length; i++) {
        (function (col) {
            ths[col].addEventListener('click', function () { sortBy(col); });
        }(i));
    }
    ths[3].className += ' sort-asc';
}());

// ── Auto-refresh countdown ────────────────────────────────────────────────────
<?php if ($autoRefresh): ?>
(function () {
    var total = <?php echo (int)$refreshSecs; ?>;
    var secs  = total;
    var cd    = document.getElementById('molr-cd');
    var prog  = document.getElementById('molr-prog');
    function fmt(s) {
        var tot = Math.max(0, s);
        var d = Math.floor(tot / 86400);
        var h = Math.floor((tot % 86400) / 3600);
        var m = Math.floor((tot % 3600) / 60);
        var r = tot % 60;
        var mm = (m < 10 ? '0' : '') + m;
        var ss = (r < 10 ? '0' : '') + r;
        if (d > 0) return d + (d === 1 ? ' day ' : ' days ') + (h < 10 ? '0' : '') + h + ':' + mm + ':' + ss;
        if (h > 0) return h + ':' + mm + ':' + ss;
        return m + ':' + ss;
    }
    function tick() {
        if (secs <= 0) { location.reload(); return; }
        if (cd)   cd.textContent   = fmt(secs);
        if (prog) prog.style.width = (secs / total * 100).toFixed(1) + '%';
        secs--;
        setTimeout(tick, 1000);
    }
    tick();
}());
<?php endif; ?>
</script>

</body>
</html>
