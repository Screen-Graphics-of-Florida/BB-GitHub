<?php
// SgRequireAccess.php
// Enforce HarrisData Program Option Security (SYPGMS) on a custom SG page.
//
// WHY THIS EXISTS
//   Native HarrisData programs are checked by the EIP framework before they run.
//   Our custom PHP pages are not - they are plain files served by Apache, so anyone
//   who knows the URL can open them regardless of what Program Option Security says.
//   Verified 2026-08-24: MODLYLBR was unticked for ten users and every one of them
//   could still open MO Daily Labor. The grants were being recorded and ignored.
//
// USAGE - one line near the top of a report, after the standard header requires:
//
//     require_once 'SgRequireAccess.php';
//     sgRequireAccess('MODLYLBR');          // the SYPGMO program id for this page
//
//   Optional second argument is the SYPGMO option sequence, default 1 (our pages all
//   register as option 1 = the "View" option):
//
//     sgRequireAccess('MODLYLBR', 1);
//
// HOW IT DECIDES
//   Reads SPOP<nn> from <schema>.SYPGMS for the signed-in profile and this program id.
//   'Y' allows. Anything else - 'N', blank, or no row at all - denies. It fails closed
//   on purpose: a user with no SYPGMS row has never been granted the program.
//
// ROLLOUT SAFETY
//   Set $SG_ACCESS_AUDIT_ONLY = true below to log what WOULD be denied without
//   actually blocking anyone. Run that way first, check the Apache error log, grant
//   whoever legitimately needs the page, then switch it back to false. Turning
//   enforcement on cold will lock out anyone whose SYPGMS row was never ticked.

$SG_ACCESS_AUDIT_ONLY = false;   // true = log only, block nobody

if (!function_exists('sgRequireAccess')) {

function sgAccessSchema() {
    $port = (string)@$_SERVER['SERVER_PORT'];
    return ($port === '5610') ? 'S5HDSDATA' : 'SGHDSDATA';
}

function sgAccessUser() {
    // Confirmed available inside custom pages 2026-08-24 via sg_pgmsec_diag.php:
    //   userProfile=BBUSCH   eUser=BBUSCH   activeRole=HD_ALL_SG
    foreach (array('userProfile', 'eUser', 'i5UserProfile') as $v) {
        if (isset($GLOBALS[$v])) {
            $u = strtoupper(trim((string)$GLOBALS[$v]));
            if ($u !== '') return $u;
        }
    }
    return '';
}

function sgAccessConn() {
    // Reuse the framework connection when the page already opened one.
    if (isset($GLOBALS['i5Connect']) && is_object($GLOBALS['i5Connect'])
        && method_exists($GLOBALS['i5Connect'], 'getConnection')) {
        $c = @$GLOBALS['i5Connect']->getConnection();
        if ($c) return $c;
    }
    return @db2_connect('*LOCAL', '', '');
}

function sgAccessLog($conn, $mode, $user, $pgmid, $opt, $why) {
    // error_log() does not reach error_log.Q1YYMMDD00 on this box (verified
    // 2026-08-25 - a confirmed denial produced no log line), so denials go to a
    // table instead. Queryable, and it survives log rotation.
    if (!$conn) $conn = sgAccessConn();
    if (!$conn) return;
    @db2_exec($conn,
        "CREATE TABLE SGOBJ.SGACCLOG ("
      . " ALSTMP TIMESTAMP, ALUSER CHAR(10), ALPGM CHAR(10), ALOPT SMALLINT,"
      . " ALMODE CHAR(6), ALREASN CHAR(60), ALURL VARCHAR(512), ALIP CHAR(45))");
    $u = str_replace("'", "''", substr($user,  0, 10));
    $p = str_replace("'", "''", substr($pgmid, 0, 10));
    $r = str_replace("'", "''", substr($why,   0, 60));
    $m = str_replace("'", "''", substr($mode,  0, 6));
    $url = isset($_SERVER['REQUEST_URI']) ? substr($_SERVER['REQUEST_URI'], 0, 512) : '';
    $url = str_replace("'", "''", $url);
    $ip  = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : '';
    $ip  = str_replace("'", "''", $ip);
    @db2_exec($conn,
        "INSERT INTO SGOBJ.SGACCLOG "
      . "(ALSTMP,ALUSER,ALPGM,ALOPT,ALMODE,ALREASN,ALURL,ALIP) VALUES "
      . "(CURRENT_TIMESTAMP,'$u','$p'," . (int)$opt . ",'$m','$r','$url','$ip')");
}

function sgAccessHome() {
    // Same source the report template's "Back to EIP" button uses (SgReportNav.php):
    // $homeURL / $baseVar / $eID, set by the framework header includes.
    $home = isset($GLOBALS['homeURL']) ? rtrim((string)$GLOBALS['homeURL'], '/') : '';
    if ($home === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'portal.screen-graphics.com';
        $home   = $scheme . '://' . $host;
    }
    $bv  = isset($GLOBALS['baseVar']) ? (string)$GLOBALS['baseVar'] : '';
    $eid = isset($GLOBALS['eID'])     ? (string)$GLOBALS['eID']     : '';
    // No session context (typical when the URL was pasted straight in) - send them to
    // the portal root, which lands on login. Never javascript:history.back(): from a
    // directly-entered URL that walks into the logout path and ejects them from EIP.
    if ($bv === '' || $eid === '') return $home . '/';
    return $home . '/Welcome.php?baseVar=' . rawurlencode($bv)
         . '&eID=' . rawurlencode($eid) . '&portal=9999999999';
}

function sgAccessDeny($pgmid, $user, $why) {
    $p = htmlspecialchars($pgmid);
    $u = htmlspecialchars($user === '' ? '(not identified)' : $user);
    header('HTTP/1.1 403 Forbidden');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Not Authorised</title>'
       . '<style>body{font-family:Arial,sans-serif;background:#f0f2f5;padding:40px;}'
       . '.box{max-width:620px;margin:0 auto;background:#fff;border-radius:6px;'
       . 'box-shadow:0 2px 6px rgba(0,0,0,.08);overflow:hidden;}'
       . '.hd{background:linear-gradient(135deg,#111827,#6B7280);color:#fff;padding:16px 24px;}'
       . '.hd h1{font-size:20px;margin:0;} .bd{padding:20px 24px;font-size:13px;line-height:1.7;}'
       . 'code{font-family:monospace;background:#f0f2f5;padding:1px 5px;border-radius:3px;}'
       . '.btn{display:inline-block;margin-top:14px;padding:9px 20px;background:#06B6D4;'
       . 'color:#fff;text-decoration:none;border-radius:4px;}</style></head><body>'
       . '<div class="box"><div class="hd"><h1>Not Authorised</h1></div><div class="bd">'
       . 'Your user profile does not have access to this program.<br><br>'
       . 'Program: <code>' . $p . '</code><br>'
       . 'User profile: <code>' . $u . '</code><br><br>'
       . 'Access is granted in HarrisData under <strong>Program Option Security</strong> '
       . 'for this program. Ask whoever administers EIP security to tick your profile '
       . 'if you need it.'
       . '<br><a class="btn" href="' . htmlspecialchars(sgAccessHome(), ENT_QUOTES)
       . '">&#8592; Back to EIP</a>'
       . '</div></div></body></html>';
    error_log("SgRequireAccess: DENIED user=$u pgm=$p reason=$why");
    exit;
}

function sgRequireAccess($pgmid, $opt = 1) {
    global $SG_ACCESS_AUDIT_ONLY;

    $pgmid = strtoupper(trim((string)$pgmid));
    $opt   = (int)$opt;
    if ($opt < 1 || $opt > 15) $opt = 1;
    $col    = sprintf('SPOP%02d', $opt);
    $schema = sgAccessSchema();
    $user   = sgAccessUser();

    if ($pgmid === '') return;   // nothing to check against - misuse, not a denial

    $allowed = false;
    $why     = 'no row';

    if ($user === '') {
        $why = 'user profile not identified';
    } else {
        $conn = sgAccessConn();
        if (!$conn) {
            // Cannot verify. Fail closed rather than wave everyone through.
            $why = 'no database connection';
        } else {
            $u = str_replace("'", "''", $user);
            $p = str_replace("'", "''", $pgmid);
            $stmt = @db2_exec($conn,
                "SELECT $col FROM $schema.SYPGMS "
              . "WHERE RTRIM(SPUSER)='$u' AND RTRIM(SPPGID)='$p'");
            if ($stmt === false) {
                $why = 'SYPGMS query failed: ' . db2_stmt_errormsg();
            } elseif ($row = db2_fetch_row($stmt)) {
                $v = strtoupper(trim((string)db2_result($stmt, 0)));
                if ($v === 'Y') { $allowed = true; }
                else { $why = "$col=" . ($v === '' ? '(blank)' : $v); }
            }
        }
    }

    if ($allowed) return;

    if (!empty($SG_ACCESS_AUDIT_ONLY)) {
        sgAccessLog(null, 'AUDIT', $user, $pgmid, $opt, $why);
        return;
    }
    sgAccessLog(null, 'DENY', $user, $pgmid, $opt, $why);
    sgAccessDeny($pgmid, $user, $why);
}

}
