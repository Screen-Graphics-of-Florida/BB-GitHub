<?php
/* ============================================================================
 * DieMasterDiag.php  --  READ-ONLY diagnostic for the DIELIB die master.
 *
 * Answers the questions that have to be settled before any EIP die screen
 * (inquiry or maintenance) can be trusted:
 *
 *   1. Is DIELIB reachable from the web job, and does DIEMAST2 hold live data?
 *   2. What are DIEMAST2's ACTUAL column definitions today?  The 1999 DDS
 *      declares PCWDTH as 4S 0 while every RPG program reads it with 2
 *      decimals -- so SQL may hand back 1050 where the app means 10.50.
 *   3. What is SIZDIE2's real column layout?  It has no DDS source in DIELIB,
 *      so its field names can only be discovered from the catalog.  The
 *      maintenance screen must keep it in lockstep or green-screen DIINQ1
 *      stops finding dies added from EIP.
 *   4. Does the web profile have the authority needed to write?
 *   5. Which PHP is actually serving these pages?
 *
 * This page executes SELECT statements only.  It writes nothing, anywhere.
 * ========================================================================== */

require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/Chicago');

$conn = $i5Connect->getConnection();

function dd_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* Run a SELECT and return array('rows'=>..., 'err'=>...). Never throws. */
function dd_q($conn, $sql) {
    $out = array('rows' => array(), 'err' => '');
    $st  = @db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
    if (!$st) {
        $out['err'] = db2_stmt_errormsg();
        return $out;
    }
    while ($r = db2_fetch_assoc($st)) {
        $out['rows'][] = $r;
    }
    db2_free_stmt($st);
    return $out;
}

/* Render a result set as a table, or the error / empty note. */
function dd_table($res, $emptyNote = 'No rows returned.') {
    if ($res['err'] !== '') {
        echo '<p class="bad">SQL error: ' . dd_h($res['err']) . '</p>';
        return;
    }
    if (empty($res['rows'])) {
        echo '<p class="warn">' . dd_h($emptyNote) . '</p>';
        return;
    }
    echo '<div class="scroll"><table><thead><tr>';
    foreach (array_keys($res['rows'][0]) as $c) {
        echo '<th>' . dd_h($c) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($res['rows'] as $r) {
        echo '<tr>';
        foreach ($r as $v) {
            echo '<td>' . dd_h(trim((string)$v)) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}

/* ---- 1. Reachability + row counts ------------------------------------- */

$counts = dd_q($conn, "
    SELECT 'DIEMAST2' AS FILE, COUNT(*) AS TOTAL,
           SUM(CASE WHEN DELCD = 'D' THEN 1 ELSE 0 END) AS FLAGGED_DELETED,
           SUM(CASE WHEN DELCD <> 'D' THEN 1 ELSE 0 END) AS ACTIVE
    FROM DIELIB.DIEMAST2
");

$sizCount   = dd_q($conn, "SELECT COUNT(*) AS SIZDIE2_ROWS FROM DIELIB.SIZDIE2");
$sortCount  = dd_q($conn, "SELECT COUNT(*) AS DIESORTX_ROWS FROM DIELIB.DIESORTX");

/* ---- 2. Catalog: what the files really look like today ----------------- */

$cols = dd_q($conn, "
    SELECT TRIM(COLUMN_NAME)   AS COL,
           TRIM(DATA_TYPE)     AS TYPE,
           LENGTH              AS LEN,
           NUMERIC_SCALE       AS SCALE,
           ORDINAL_POSITION    AS POS,
           TRIM(COALESCE(COLUMN_HEADING, '')) AS HEADING
    FROM QSYS2.SYSCOLUMNS
    WHERE TABLE_SCHEMA = 'DIELIB' AND TABLE_NAME = 'DIEMAST2'
    ORDER BY ORDINAL_POSITION
");

$sizCols = dd_q($conn, "
    SELECT TRIM(COLUMN_NAME)   AS COL,
           TRIM(DATA_TYPE)     AS TYPE,
           LENGTH              AS LEN,
           NUMERIC_SCALE       AS SCALE,
           ORDINAL_POSITION    AS POS
    FROM QSYS2.SYSCOLUMNS
    WHERE TABLE_SCHEMA = 'DIELIB' AND TABLE_NAME = 'SIZDIE2'
    ORDER BY ORDINAL_POSITION
");

/* ---- 3b. SIZDIE2 fallback discovery ------------------------------------ *
 *
 * SYSCOLUMNS came back empty for SIZDIE2.  Most likely cause: it is a
 * PROGRAM-DESCRIBED physical file.  The RPG F-specs give it away --
 *   FSIZDIE2 IF  F      14 13AI
 * declares record length 14 and key length 13 in the program rather than
 * referencing a record format, which is how you open a file that has no DDS.
 * Such files may expose a single implicit column (usually named after the
 * file) instead of named fields, or may not appear in SYSCOLUMNS at all.
 *
 * These three probes settle it without guessing:
 *   - does the object exist, and as what type
 *   - is it perhaps in a different library on the list (the CL uses *LIBL)
 *   - what column names does a plain SELECT * actually return
 */

$dielibObjects = dd_q($conn, "
    SELECT TRIM(TABLE_NAME)    AS NAME,
           TRIM(TABLE_TYPE)    AS TYPE,
           TRIM(FILE_TYPE)     AS FILETYPE,
           NUMBER_ROWS         AS ROWS_IN_FILE,
           TRIM(COALESCE(TABLE_TEXT, '')) AS TEXT
    FROM QSYS2.SYSTABLES
    WHERE TABLE_SCHEMA = 'DIELIB'
    ORDER BY TABLE_NAME
");

$sizAnywhere = dd_q($conn, "
    SELECT TRIM(TABLE_SCHEMA) AS SCHEMA_NAME,
           TRIM(TABLE_NAME)   AS NAME,
           TRIM(TABLE_TYPE)   AS TYPE,
           NUMBER_ROWS        AS ROWS_IN_FILE
    FROM QSYS2.SYSTABLES
    WHERE TABLE_NAME IN ('SIZDIE2', 'DIEMAST2', 'DIESORTX')
    ORDER BY TABLE_NAME, TABLE_SCHEMA
");

/* The decisive probe: whatever column names come back here ARE the ones the
 * maintenance screen has to write to. */
$sizRaw = dd_q($conn, "SELECT * FROM DIELIB.SIZDIE2 FETCH FIRST 10 ROWS ONLY");

/* ---- 3. The PCWDTH scale question ------------------------------------- */
/*
 * DIMANT enforces "piece width cannot be greater than piece length", so under
 * the CORRECT scaling almost no active row should violate width <= length.
 * Whichever of these two counts is ~0 tells us the scale:
 *
 *   VIOL_RAW    high, VIOL_SCALED ~0  ->  PCWDTH is 100x (divide by 100)
 *   VIOL_RAW    ~0                    ->  PCWDTH is already on PCLNTH's scale
 *
 * Cross-check against NUMERIC_SCALE in the catalog panel above.
 */
$scaleTest = dd_q($conn, "
    SELECT COUNT(*) AS ACTIVE_ROWS,
           SUM(CASE WHEN PCWDTH        > PCLNTH THEN 1 ELSE 0 END) AS VIOL_RAW,
           SUM(CASE WHEN PCWDTH/100.0  > PCLNTH THEN 1 ELSE 0 END) AS VIOL_SCALED
    FROM DIELIB.DIEMAST2
    WHERE DELCD <> 'D'
");

$ranges = dd_q($conn, "
    SELECT MIN(PCWDTH)  AS PCWDTH_MIN,  MAX(PCWDTH)  AS PCWDTH_MAX,
           MIN(PCLNTH)  AS PCLNTH_MIN,  MAX(PCLNTH)  AS PCLNTH_MAX,
           MIN(DIWIDTH) AS DIWIDTH_MIN, MAX(DIWIDTH) AS DIWIDTH_MAX,
           MIN(DILNTH)  AS DILNTH_MIN,  MAX(DILNTH)  AS DILNTH_MAX,
           MIN(RULE)    AS RULE_MIN,    MAX(RULE)    AS RULE_MAX,
           MIN(NOUP)    AS NOUP_MIN,    MAX(NOUP)    AS NOUP_MAX
    FROM DIELIB.DIEMAST2
    WHERE DELCD <> 'D'
");

/* Raw sample -- eyeball whether PCWDTH reads as 1050 or 10.50 */
$sample = dd_q($conn, "
    SELECT DINO, PCWDTH, PCLNTH, DIWIDTH, DILNTH, NOUP, RULE,
           SHAPE, RC, BLEED, BINNO, CUSACR, CUSTNO, ENDUSR, PARTNO, COMENT, DELCD
    FROM DIELIB.DIEMAST2
    WHERE DELCD <> 'D'
    ORDER BY DINO
    FETCH FIRST 25 ROWS ONLY
");

/* Distinct RULE heights -- .918 is the standard die rule height, so seeing it
 * confirms the 3-decimal reading of the file is right. */
$ruleVals = dd_q($conn, "
    SELECT RULE, COUNT(*) AS ROWS_WITH
    FROM DIELIB.DIEMAST2
    WHERE DELCD <> 'D'
    GROUP BY RULE
    ORDER BY COUNT(*) DESC
    FETCH FIRST 12 ROWS ONLY
");

/* ---- 4. Authority ----------------------------------------------------- */

$who = dd_q($conn, "
    SELECT CURRENT_USER AS CURUSER, SESSION_USER AS SESUSER,
           SYSTEM_USER  AS SYSUSER, CURRENT SERVER AS SRVNAME
    FROM SYSIBM.SYSDUMMY1
");

/* SELECT * deliberately: QSYS2.OBJECT_PRIVILEGES column names vary across
 * releases (DATA_ADD / DATA_UPDATE vs INSERT_AUTHORITY / UPDATE_AUTHORITY),
 * and naming them wrong would fail the panel that matters most.  The columns
 * to read are the ones covering read / add / update. */
$priv = dd_q($conn, "
    SELECT * FROM QSYS2.OBJECT_PRIVILEGES
    WHERE SYSTEM_OBJECT_SCHEMA = 'DIELIB'
      AND SYSTEM_OBJECT_NAME IN ('DIEMAST2', 'SIZDIE2')
");

/* ---- 5. Which PHP is serving this page? -------------------------------- */

$phpExts = array('pdo_sqlite', 'sqlite3', 'ibm_db2', 'pdo_ibm', 'json', 'mbstring');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Die Master Diagnostic (read-only)</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px;
       background: #edf1f7; color: #1a2233; padding: 0 0 40px; }
.topbar { background: #111827; color: #fff; padding: 10px 16px; }
.topbar h1 { font-size: 17px; }
.topbar .sub { font-size: 11px; color: #9aa7bd; margin-top: 2px; }
.wrap { padding: 14px; }
.card { background: #fff; border: 1px solid #c8d0de; border-radius: 4px;
        margin-bottom: 14px; overflow: hidden; }
.card > h2 { background: #374151; color: #fff; font-size: 12px; font-weight: 700;
             padding: 6px 12px; text-transform: uppercase; letter-spacing: .6px; }
.card > .body { padding: 10px 12px; }
.note { font-size: 12px; color: #5a6478; margin-bottom: 8px; line-height: 1.5; }
.scroll { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
th { background: #e8edf5; padding: 4px 8px; font-size: 10px; font-weight: 700;
     text-align: left; white-space: nowrap; border-bottom: 1px solid #c8d0de; }
td { padding: 3px 8px; border-bottom: 1px solid #eef; white-space: nowrap;
     font-family: 'Courier New', monospace; }
tr:nth-child(even) td { background: #f7f8fc; }
.bad  { color: #900; background: #fdd; padding: 6px 10px; border-radius: 3px;
        font-family: monospace; font-size: 12px; }
.warn { color: #7a5200; background: #fff8e1; padding: 6px 10px; border-radius: 3px; }
.ok   { color: #1a5e2a; font-weight: 700; }
.verdict { background: #eef4ff; border-left: 4px solid #2563eb;
           padding: 8px 12px; font-size: 12px; line-height: 1.6; }
code { background: #f1f3f8; padding: 1px 4px; border-radius: 2px;
       font-family: 'Courier New', monospace; }
</style>
</head>
<body>

<div class="topbar">
  <h1>Die Master Diagnostic &mdash; DIELIB</h1>
  <div class="sub">Read-only. Runs SELECT statements only &mdash; writes nothing.
    &nbsp;|&nbsp; <?php echo dd_h(date('m/d/Y g:i:s a')); ?></div>
</div>

<div class="wrap">

  <div class="card">
    <h2>1 &nbsp;Reachability &amp; row counts</h2>
    <div class="body">
      <p class="note">If these error out, DIELIB is not in reach of the web job and
        everything else on this page is moot.</p>
      <?php dd_table($counts); ?>
      <?php dd_table($sizCount); ?>
      <?php dd_table($sortCount); ?>
    </div>
  </div>

  <div class="card">
    <h2>2 &nbsp;DIEMAST2 &mdash; actual column definitions</h2>
    <div class="body">
      <p class="note">The live catalog, not the 1999 DDS. Watch
        <code>SCALE</code> on <code>PCWDTH</code>: <code>0</code> means SQL returns
        <code>1050</code> for 10.50 and every read/write must scale by 100;
        <code>2</code> means the file was redefined since and no scaling is needed.</p>
      <?php dd_table($cols, 'DIEMAST2 not found in QSYS2.SYSCOLUMNS.'); ?>
    </div>
  </div>

  <div class="card">
    <h2>3 &nbsp;SIZDIE2 &mdash; column layout (no DDS source exists)</h2>
    <div class="body">
      <p class="note">SIZDIE2 has no source member in DIELIB/DIESRC, so this catalog
        query is the only way to learn its real field names. The maintenance screen
        must insert/flag rows here in lockstep with DIEMAST2, or green-screen DIINQ1
        (inquiry by minimum cut dimension) will not see dies added from EIP.
        Expected shape: a 13-byte key (piece width + piece length + die number)
        plus a 1-byte delete flag.</p>
      <?php dd_table($sizCols, 'SIZDIE2 not found in QSYS2.SYSCOLUMNS — expected if it is a program-described file. See panel 3b.'); ?>
    </div>
  </div>

  <div class="card">
    <h2>3b &nbsp;SIZDIE2 fallback discovery</h2>
    <div class="body">
      <p class="note">SYSCOLUMNS knows nothing about SIZDIE2, which is what you would expect
        from a <b>program-described</b> physical file &mdash; one created with a record length
        and no DDS. The RPG F-spec <code>FSIZDIE2 IF F 14 13AI</code> declares the 14-byte
        record and 13-byte key inside the program rather than referencing a record format,
        which is exactly how a file with no external definition is opened. Such a file
        typically exposes one implicit column named after the file instead of named fields.</p>

      <p class="note"><b>Every object in DIELIB</b> &mdash; confirms whether SIZDIE2 exists at
        all, and as what type:</p>
      <?php dd_table($dielibObjects, 'No objects reported for schema DIELIB.'); ?>

      <p class="note"><b>Located anywhere on the system</b> &mdash; the DIELIB CL programs
        override with <code>*LIBL</code>, so SIZDIE2 could legitimately live in another
        library on the job's library list rather than in DIELIB:</p>
      <?php dd_table($sizAnywhere, 'None of SIZDIE2 / DIEMAST2 / DIESORTX found in QSYS2.SYSTABLES.'); ?>

      <p class="note"><b>The decisive probe</b> &mdash; a plain <code>SELECT *</code>. Whatever
        column name comes back is the one the maintenance screen must write to. Expect a single
        14-character column: 13 bytes of key (piece width + piece length + die number) plus the
        1-byte delete flag, which would mean writes are done by building and slicing a fixed
        13-character string exactly as DIMANT does with <code>MOVEL</code>/<code>MOVE</code>:</p>
      <?php dd_table($sizRaw, 'SELECT * returned no rows — the file exists but is empty, or is not readable via SQL.'); ?>
    </div>
  </div>

  <div class="card">
    <h2>4 &nbsp;The PCWDTH scale question</h2>
    <div class="body">
      <p class="note">DIMANT enforces <em>piece width &le; piece length</em>, so under the
        correct scaling almost no active row should violate it. Whichever count is
        near zero identifies the scale.</p>
      <?php dd_table($scaleTest); ?>
      <div class="verdict">
        <b>How to read this:</b><br>
        <code>VIOL_RAW</code> high and <code>VIOL_SCALED</code> near 0
        &rarr; PCWDTH is stored 100&times;; divide by 100 on read, multiply on write.<br>
        <code>VIOL_RAW</code> near 0
        &rarr; PCWDTH is already on the same scale as PCLNTH; no conversion.<br>
        Both near 0 &rarr; trust <code>NUMERIC_SCALE</code> from panel 2.
      </div>
    </div>
  </div>

  <div class="card">
    <h2>5 &nbsp;Value ranges (active rows)</h2>
    <div class="body">
      <p class="note">Magnitude check. If PCWDTH tops out near 9999 while PCLNTH tops out
        near 99.99, that is the 100&times; discrepancy in plain sight.</p>
      <?php dd_table($ranges); ?>
    </div>
  </div>

  <div class="card">
    <h2>6 &nbsp;Rule heights</h2>
    <div class="body">
      <p class="note">RULE is declared <code>3S 3</code>. Seeing <code>.918</code> &mdash;
        the standard die rule height &mdash; confirms the 3-decimal reading is correct.</p>
      <?php dd_table($ruleVals); ?>
    </div>
  </div>

  <div class="card">
    <h2>7 &nbsp;Raw sample &mdash; first 25 active dies by die number</h2>
    <div class="body">
      <p class="note">Values exactly as SQL returns them, unformatted. Note DINO is 5
        characters: 4 digits plus an optional letter suffix.</p>
      <?php dd_table($sample); ?>
    </div>
  </div>

  <div class="card">
    <h2>8 &nbsp;Who am I, and can I write?</h2>
    <div class="body">
      <p class="note">The maintenance screen needs INSERT and UPDATE on both DIEMAST2 and
        SIZDIE2 for the profile shown below. Every existing SG custom page is
        read-only, so this authority has never been exercised.</p>
      <?php dd_table($who); ?>
      <?php dd_table($priv, 'No rows in QSYS2.OBJECT_PRIVILEGES for these objects (the view may itself be restricted).'); ?>
    </div>
  </div>

  <div class="card">
    <h2>9 &nbsp;PHP runtime actually serving this page</h2>
    <div class="body">
      <p class="note">Settles which interpreter the EIP instance uses, rather than inferring
        it from the HTTP server config files.</p>
      <div class="scroll">
      <table>
        <thead><tr><th>Item</th><th>Value</th></tr></thead>
        <tbody>
          <tr><td>PHP_VERSION</td><td><?php echo dd_h(PHP_VERSION); ?></td></tr>
          <tr><td>PHP_BINARY</td><td><?php echo dd_h(defined('PHP_BINARY') ? PHP_BINARY : 'n/a'); ?></td></tr>
          <tr><td>php.ini loaded</td><td><?php echo dd_h(php_ini_loaded_file()); ?></td></tr>
          <tr><td>scan dir</td><td><?php echo dd_h(php_ini_scanned_files()); ?></td></tr>
          <tr><td>SAPI</td><td><?php echo dd_h(PHP_SAPI); ?></td></tr>
          <?php foreach ($phpExts as $e): ?>
          <tr><td>extension: <?php echo dd_h($e); ?></td>
              <td><?php echo extension_loaded($e)
                    ? '<span class="ok">loaded</span>' : 'NOT loaded'; ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>

</div>
</body>
</html>
