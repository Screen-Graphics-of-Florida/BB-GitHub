<?php
// -----------------------------------------------------------------------------
//  Buyer Pattern - nightly follow-up email
//
//  Sends each person who logged a contact a note listing the follow-ups they
//  promised: anything overdue, due today, or due inside the next seven days.
//  A separate summary goes to whoever is listed in the UDC table, so a manager
//  sees the whole picture without reading everyone's mail.
//
//  This is a COMMAND LINE script. It is not reachable over the web and takes no
//  input from a browser, so it needs no SYPGMS gate - the gate on a scheduled
//  job is who can schedule it.
//
//  Run it:
//    php BuyerPatternFollowupMail.php --env=TEST
//    php BuyerPatternFollowupMail.php --env=LIVE
//    php BuyerPatternFollowupMail.php --env=LIVE --dry          nothing sent
//    php BuyerPatternFollowupMail.php --env=LIVE --to=me@x.com  all mail to one
//    php BuyerPatternFollowupMail.php --env=LIVE --only=BILL    one profile
//
//  Mail transport is the IBM i's own SNDSMTPEMM. PHP's mail() is not usable
//  here: sendmail is not installed in PASE (checked 2026-08-28), and
//  SNDSMTPEMM needs no install, supports HTML and takes attachments.
//  Hard limit: NOTE accepts 5000 characters, measured - 6000 is rejected by the
//  command. The body is therefore capped and the tail becomes "and N more".
//
//  Definition of a live follow-up, identical to the page: a customer's MOST
//  RECENT note carries their follow-up. An older note with a date on it has
//  been superseded and is not chased.
// -----------------------------------------------------------------------------

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=UTF-8', true, 403);
    echo "This is a scheduled command-line job and is not available over the web.\n";
    exit(1);
}

$BP_NOTE_MAX  = 5000;    // SNDSMTPEMM NOTE ceiling, measured
$BP_LOOKAHEAD = 7;       // days ahead to warn about
$BP_MAXROWS   = 40;      // rows inline before the tail note takes over

// -- Arguments ----------------------------------------------------------------

$opt = array('env' => '', 'only' => '', 'to' => '', 'dry' => false);
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--dry')                             { $opt['dry']  = true; }
    elseif (strpos($a, '--env=')  === 0)            { $opt['env']  = strtoupper(substr($a, 6)); }
    elseif (strpos($a, '--only=') === 0)            { $opt['only'] = strtoupper(substr($a, 7)); }
    elseif (strpos($a, '--to=')   === 0)            { $opt['to']   = trim(substr($a, 5)); }
    else {
        fwrite(STDERR, "Unknown argument: $a\n");
        exit(2);
    }
}
if ($opt['env'] !== 'TEST' && $opt['env'] !== 'LIVE') {
    fwrite(STDERR, "--env=TEST or --env=LIVE is required. Nothing was sent.\n");
    exit(2);
}

// Test and Live keep their own log and their own portal; business data is
// always SGHDSDATA in both.
$LOGLIB  = ($opt['env'] === 'TEST') ? 'SG5OBJ'    : 'SGOBJ';
$SYSCHM  = ($opt['env'] === 'TEST') ? 'S5HDSDATA' : 'SGHDSDATA';
$PORTAL  = ($opt['env'] === 'TEST') ? 'https://portal.screen-graphics.com:5610'
                                    : 'https://portal.screen-graphics.com:5601';
$PAGEURL = $PORTAL . '/Custom/SG/Order%20Entry/BuyerPattern.php?view=followups';

function say($s) { echo $s . PHP_EOL; }

say(str_repeat('-', 72));
say('Buyer Pattern follow-up mail   env=' . $opt['env'] . '   log=' . $LOGLIB
    . ($opt['dry'] ? '   DRY RUN, nothing will be sent' : ''));
say('started ' . date('Y-m-d H:i:s'));

$conn = @db2_connect('*LOCAL', '', '');
if (!$conn) {
    fwrite(STDERR, 'Could not connect to DB2: ' . db2_conn_errormsg() . "\n");
    exit(3);
}

function fetchAll($conn, $sql) {
    $rows = array();
    $st = db2_exec($conn, $sql);
    if (!$st) {
        fwrite(STDERR, 'SQL failed: ' . db2_stmt_errormsg() . "\n" . $sql . "\n");
        exit(4);
    }
    while ($r = db2_fetch_assoc($st)) { $rows[] = $r; }
    db2_free_stmt($st);
    return $rows;
}

// -- The follow-up queue ------------------------------------------------------

$sql = "
    WITH LATEST AS (
        SELECT L.CLSEQ, L.CLSHTO, L.CLUSER, L.CLOUTC, L.CLFUDT, L.CLNOTE,
               L.CLTIER, L.CLTSTP, L.CLTYPE,
               ROW_NUMBER() OVER (PARTITION BY L.CLSHTO
                                  ORDER BY L.CLTSTP DESC, L.CLSEQ DESC) AS RN
        FROM $LOGLIB.BPCALLLOG L
    )
    SELECT T.CLSHTO, TRIM(T.CLUSER) AS CLUSER, TRIM(T.CLOUTC) AS CLOUTC,
           CHAR(T.CLFUDT, ISO) AS FUDT, TRIM(T.CLNOTE) AS CLNOTE,
           T.CLTIER, CHAR(T.CLTSTP) AS LOGGED, TRIM(T.CLTYPE) AS CLTYPE,
           COALESCE(TRIM(c.CMCNA1), '(no customer record)') AS CUSTNAME,
           COALESCE(TRIM(c.CMPHON), '') AS PHONE,
           COALESCE(c.CMSLSM, 0) AS SLSM,
           DAYS(T.CLFUDT) - DAYS(CURRENT DATE) AS DUEIN
    FROM LATEST T
    LEFT JOIN SGHDSDATA.HDCUST c ON T.CLSHTO = c.CMCUST
    WHERE T.RN = 1
      AND T.CLFUDT IS NOT NULL
      AND T.CLFUDT <= CURRENT DATE + $BP_LOOKAHEAD DAYS
    ORDER BY T.CLFUDT, T.CLSHTO
";
$rows = fetchAll($conn, $sql);
say('follow-ups due or overdue: ' . count($rows));

if (empty($rows)) {
    // Silence is the correct behaviour. A nightly "nothing to do" email teaches
    // people to filter the whole thread away, and then they miss a real one.
    say('nothing to send - exiting quietly');
    say('finished ' . date('Y-m-d H:i:s'));
    exit(0);
}

// -- Group by the person who made the promise --------------------------------

$byUser = array();
foreach ($rows as $r) {
    $u = strtoupper(trim((string)$r['CLUSER']));
    if ($u === '') { $u = '(unknown)'; }
    if ($opt['only'] !== '' && $u !== $opt['only']) { continue; }
    if (!isset($byUser[$u])) {
        $byUser[$u] = array('overdue' => array(), 'today' => array(), 'soon' => array());
    }
    $d = (int)$r['DUEIN'];
    if     ($d <  0) { $byUser[$u]['overdue'][] = $r; }
    elseif ($d === 0) { $byUser[$u]['today'][]   = $r; }
    else              { $byUser[$u]['soon'][]    = $r; }
}

// -- Addresses ----------------------------------------------------------------

$mailOf = array();
foreach (fetchAll($conn,
        "SELECT UPPER(TRIM(USUSER)) AS U, TRIM(USEMAL) AS E
           FROM $SYSCHM.SYUSER
          WHERE TRIM(COALESCE(USEMAL,'')) <> ''") as $r) {
    $mailOf[$r['U']] = $r['E'];
}

// Extra recipients for the roll-up, from the same UDC table that drives
// everything else on this page: system BUYPATTERN, code MAILTO, one address per
// UDCDESC field. Adding the COO is a UDC row, not a code change.
$udcSel = array();
for ($i = 1; $i <= 15; $i++) { $udcSel[] = "TRIM(UDCDESC$i) AS D$i"; }
$summaryTo = array();
foreach (fetchAll($conn,
        'SELECT ' . implode(', ', $udcSel) . "
           FROM PROITRG.UDCDETAIL
          WHERE UPPER(TRIM(UDCSYSTEMD)) = 'BUYPATTERN'
            AND UPPER(TRIM(UDCCODED))   = 'MAILTO'") as $r) {
    for ($i = 1; $i <= 15; $i++) {
        $v = trim((string)$r['D' . $i]);
        if ($v !== '' && strpos($v, '@') !== false) { $summaryTo[$v] = true; }
    }
}
$summaryTo = array_keys($summaryTo);

// -- Helpers ------------------------------------------------------------------

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// A CL character literal doubles its own quotes. A customer called BOB'S SIGNS
// would otherwise end the string early and the command would fail.
function clq($s) {
    return str_replace("'", "''", (string)$s);
}

function mdy($iso) {
    $iso = trim((string)$iso);
    if (strlen($iso) < 10) { return $iso; }
    return substr($iso, 5, 2) . '-' . substr($iso, 8, 2) . '-' . substr($iso, 0, 4);
}

// Sends one message. Returns true, or false with the reason in $err.
function sendMail($to, $subject, $html, $dry, &$err) {
    $err = '';
    if (strlen($html) > 5000) { $html = substr($html, 0, 4990) . '</body></html>'; }
    $cl = "SNDSMTPEMM RCP(('" . clq($to) . "')) "
        . "SUBJECT('" . clq($subject) . "') "
        . "NOTE('" . clq($html) . "') "
        . "CONTENT(*HTML)";
    if ($dry) {
        say('    DRY: would send to ' . $to . '  (' . strlen($html) . ' chars of HTML)');
        return true;
    }
    $out = array(); $rc = 0;
    // The CL is passed as a single argument; shell-quote it once for PASE.
    $shell = '/QOpenSys/usr/bin/system "' . str_replace('"', '\\"', $cl) . '"';
    exec($shell . ' 2>&1', $out, $rc);
    if ($rc !== 0) {
        $err = 'rc=' . $rc . ' ' . implode(' | ', array_slice($out, 0, 4));
        return false;
    }
    return true;
}

// -- Build and send one message per person -----------------------------------

$rowHtml = function ($r, $flag) {
    $tier = ((int)$r['CLTIER'] > 0) ? 'T' . (int)$r['CLTIER'] : '-';
    return '<tr>'
         . '<td>' . h(mdy($r['FUDT'])) . $flag . '</td>'
         . '<td>' . h(trim((string)$r['CLSHTO'])) . '</td>'
         . '<td>' . h($r['CUSTNAME']) . '</td>'
         . '<td>' . h($r['PHONE']) . '</td>'
         . '<td>' . $tier . '</td>'
         . '<td>' . h($r['CLOUTC']) . '</td>'
         . '</tr>';
};

$sent = 0; $failed = 0; $skipped = array();
$tally = array();

foreach ($byUser as $u => $b) {
    $nOver = count($b['overdue']);
    $nTod  = count($b['today']);
    $nSoon = count($b['soon']);
    $nAll  = $nOver + $nTod + $nSoon;
    $tally[$u] = array('overdue' => $nOver, 'today' => $nTod, 'soon' => $nSoon);

    $to = ($opt['to'] !== '') ? $opt['to']
        : (isset($mailOf[$u]) ? $mailOf[$u] : '');
    if ($to === '') {
        $skipped[] = $u . ' (no address on ' . $SYSCHM . '.SYUSER)';
        say('  ' . $u . ': ' . $nAll . ' follow-ups but NO EMAIL ADDRESS - skipped');
        continue;
    }

    $subject = ($nOver > 0)
             ? 'Buyer Pattern: ' . $nOver . ' follow-up' . ($nOver === 1 ? '' : 's') . ' overdue'
             : 'Buyer Pattern: ' . $nAll . ' follow-up' . ($nAll === 1 ? '' : 's') . ' coming up';

    $html = '<html><body style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#111827;">'
          . '<p style="font-size:15px;font-weight:bold;margin:0 0 8px 0;">'
          . 'Your Buyer Pattern follow-ups</p>'
          . '<p style="margin:0 0 10px 0;">'
          . ($nOver > 0
              ? '<span style="color:#CC1F20;font-weight:bold;">' . $nOver . ' overdue</span>'
              : 'None overdue')
          . ($nTod  > 0 ? ' &middot; <b>' . $nTod . '</b> due today'      : '')
          . ($nSoon > 0 ? ' &middot; ' . $nSoon . ' within ' . $BP_LOOKAHEAD . ' days' : '')
          . '.</p>'
          . '<table cellpadding="4" cellspacing="0" border="0" style="border-collapse:collapse;font-size:12px;">'
          . '<tr style="background:#374151;color:#ffffff;font-weight:bold;">'
          . '<td>Follow-up</td><td>Ship-to</td><td>Customer</td><td>Phone</td>'
          . '<td>Tier</td><td>Last outcome</td></tr>';

    $shown = 0; $more = 0;
    foreach (array(array('overdue', ' <b style="color:#CC1F20;">overdue</b>'),
                   array('today',   ' <b>today</b>'),
                   array('soon',    '')) as $grp) {
        foreach ($b[$grp[0]] as $r) {
            if ($shown >= $BP_MAXROWS) { $more++; continue; }
            $piece = $rowHtml($r, $grp[1]);
            // Leave room for the closing markup and the tail sentence
            if (strlen($html) + strlen($piece) > ($BP_NOTE_MAX - 400)) { $more++; continue; }
            $html .= $piece;
            $shown++;
        }
    }
    $html .= '</table>';
    if ($more > 0) {
        $html .= '<p style="margin:10px 0 0 0;"><b>' . $more . ' more</b> not listed here - '
               . 'the full queue is on the page.</p>';
    }
    $html .= '<p style="margin:12px 0 0 0;">'
           . '<a href="' . h($PAGEURL) . '" style="color:#2563EB;font-weight:bold;">'
           . 'Open the follow-up queue</a></p>'
           . '<p style="margin:12px 0 0 0;font-size:11px;color:#6B7280;">'
           . 'A follow-up clears when you log the next contact on that customer. '
           . 'Only the most recent note on a customer is chased - earlier ones are superseded. '
           . 'Sent by the nightly job on the IBM i (' . $opt['env'] . ').</p>'
           . '</body></html>';

    $err = '';
    if (sendMail($to, $subject, $html, $opt['dry'], $err)) {
        $sent++;
        say('  ' . $u . ' -> ' . $to . '  (' . $nOver . ' overdue, ' . $nTod . ' today, '
            . $nSoon . ' soon' . ($more > 0 ? ', ' . $more . ' not listed' : '') . ')');
    } else {
        $failed++;
        say('  ' . $u . ' -> ' . $to . '  FAILED: ' . $err);
    }
}

// -- Roll-up for the managers -------------------------------------------------

if (!empty($summaryTo) && !empty($tally)) {
    $tOver = 0; $tTod = 0; $tSoon = 0;
    foreach ($tally as $t) { $tOver += $t['overdue']; $tTod += $t['today']; $tSoon += $t['soon']; }

    $html = '<html><body style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#111827;">'
          . '<p style="font-size:15px;font-weight:bold;margin:0 0 8px 0;">'
          . 'Buyer Pattern follow-ups - all salespeople</p>'
          . '<p style="margin:0 0 10px 0;">'
          . ($tOver > 0 ? '<span style="color:#CC1F20;font-weight:bold;">' . $tOver
                          . ' overdue</span>' : 'None overdue')
          . ' &middot; ' . $tTod . ' due today &middot; ' . $tSoon . ' within '
          . $BP_LOOKAHEAD . ' days.</p>'
          . '<table cellpadding="4" cellspacing="0" border="0" style="border-collapse:collapse;font-size:12px;">'
          . '<tr style="background:#374151;color:#ffffff;font-weight:bold;">'
          . '<td>Who</td><td>Overdue</td><td>Today</td><td>Within ' . $BP_LOOKAHEAD
          . ' days</td><td>Address</td></tr>';
    foreach ($tally as $u => $t) {
        $addr = isset($mailOf[$u]) ? $mailOf[$u] : 'no address on file';
        $html .= '<tr><td><b>' . h($u) . '</b></td>'
               . '<td' . ($t['overdue'] > 0 ? ' style="color:#CC1F20;font-weight:bold;"' : '') . '>'
               . $t['overdue'] . '</td>'
               . '<td>' . $t['today'] . '</td><td>' . $t['soon'] . '</td>'
               . '<td style="font-size:11px;color:#6B7280;">' . h($addr) . '</td></tr>';
    }
    $html .= '</table>'
           . '<p style="margin:12px 0 0 0;"><a href="' . h($PAGEURL)
           . '" style="color:#2563EB;font-weight:bold;">Open the follow-up queue</a></p>'
           . '<p style="margin:12px 0 0 0;font-size:11px;color:#6B7280;">'
           . 'Recipients of this summary are maintained in PROITRG.UDCDETAIL, system BUYPATTERN, '
           . 'code MAILTO. Sent by the nightly job on the IBM i (' . $opt['env'] . ').</p>'
           . '</body></html>';

    foreach ($summaryTo as $addr) {
        $target = ($opt['to'] !== '') ? $opt['to'] : $addr;
        $err = '';
        if (sendMail($target, 'Buyer Pattern follow-ups - all salespeople', $html, $opt['dry'], $err)) {
            $sent++;
            say('  summary -> ' . $target);
        } else {
            $failed++;
            say('  summary -> ' . $target . '  FAILED: ' . $err);
        }
    }
} elseif (empty($summaryTo)) {
    say('no summary recipients: add rows to PROITRG.UDCDETAIL '
        . "(UDCSYSTEMD='BUYPATTERN', UDCCODED='MAILTO')");
}

// -- Result -------------------------------------------------------------------

say(str_repeat('-', 72));
say('sent: ' . $sent . '   failed: ' . $failed . '   skipped: ' . count($skipped));
foreach ($skipped as $s) { say('  skipped: ' . $s); }
say('finished ' . date('Y-m-d H:i:s'));

// A non-zero exit makes the scheduler show the job as failed, which is what
// should happen if mail did not go out.
exit($failed > 0 ? 5 : 0);
