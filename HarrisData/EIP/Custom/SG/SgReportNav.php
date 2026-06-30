<?php
// SgReportNav.php
// Include AFTER Banner.php in every SG custom report page.
// Requires: GetURLParm.php + BaseConfiguration already loaded.
// Key framework vars provided: $activeRole, $homeURL, $phpPath, $cGIPath,
//   $helpPath, $baseVar, $eID, $portal, $i5Connect, $profileHandle

global $activeRole, $homeURL, $phpPath, $cGIPath, $helpPath,
       $baseVar, $eID, $portal, $i5Connect, $profileHandle,
       $newsLink, $browser;

$_sgnHome = isset($homeURL)       ? rtrim($homeURL, '/') : 'https://portal.screen-graphics.com:5601';
$_sgnPhp  = isset($phpPath)       ? $phpPath             : '/';
$_sgnCgi  = isset($cGIPath)       ? $cGIPath             : '/harris-CGI/';
$_sgnHelp = isset($helpPath)      ? (string)$helpPath    : '';
$_sgnBv   = isset($baseVar)       ? (string)$baseVar     : '';
$_sgnEid  = isset($eID)           ? (string)$eID         : '';
$_sgnPort = isset($portal)        ? (string)$portal      : '';
$_sgnRole = isset($activeRole)    ? trim((string)$activeRole) : '';
$_sgnPrfh = isset($profileHandle) ? (string)$profileHandle   : '';
$_sgnNews = isset($newsLink)      ? (string)$newsLink         : '';
$_sgnBrws = isset($browser)       ? (string)$browser          : '';

// Build a portal URL from FUURL template — mirrors GetMenu.php logic exactly
if (!function_exists('sgn_buildURL')) {
    function sgn_buildURL($fuurl, $fpport, $home, $php, $cgi, $help, $bv, $eid, $prfh, $news, $brws) {
        $w = trim($fuurl);
        if ($w === '') return 'javascript:void(0);';

        $phpPos = strpos(strtoupper($w), '.PHP');
        if ($phpPos !== false) {
            $w     = str_replace('@@phpPath', $php, $w);
            $bvWrk = $bv;
        } else {
            $phpPos2 = strpos(strtoupper($bv), '.PHP');
            $bvWrk   = ($phpPos2 !== false)
                       ? substr($bv, 0, $phpPos2) . '.icl'
                       : $bv;
            $w = str_replace('@@cGIPath', $cgi, $w);
        }
        $w = str_replace('@@homeURL',     $home,                                    $w);
        $w = str_replace('@@helpPath',    $help,                                    $w);
        $w = str_replace('@@prfh',        urlencode($prfh),                         $w);
        $w = str_replace('@@userProfile', urlencode((string)@$_SERVER['PHP_AUTH_USER']), $w);
        $w = str_replace('@@newsLink',    $news,                                    $w);
        $w = str_replace('@@timeStamp',   urlencode((string)@$_SERVER['REQUEST_TIME']), $w);
        $w = str_replace('@@browser',     $brws,                                    $w);

        if (strpos($fuurl, '@@homeURL') !== false) {
            $amp = (strpos($w, '?') !== false) ? '&' : '?';
            if (strpos($fuurl, '@@baseVar') === false) {
                $w  .= $amp . 'baseVar=' . urlencode($bvWrk) . '&eID=' . urlencode($eid);
                $amp = '&';
            } else {
                $w = str_replace('@@baseVar', urlencode($bvWrk), $w);
            }
            if (strpos($fuurl, '@@portal') === false) {
                $w .= '&portal=' . urlencode(trim($fpport));
            } else {
                $w = str_replace('@@portal', urlencode(trim($fpport)), $w);
            }
        }
        return $w;
    }
}

// Build the full EIP portal menu for this user's role
$_sgnItems = array();
if (!empty($_sgnRole) && isset($i5Connect)) {
    $_sgnConn    = $i5Connect->getConnection();
    $role_safe   = str_replace("'", "''", $_sgnRole);

    // Whitelist vs bypass mode
    $_sgnPorr = 0;
    $_sc = @db2_exec($_sgnConn,
        "SELECT COUNT(*) FROM SGHDSDATA.SYPORR WHERE PRROLE='$role_safe'");
    if ($_sc) { $_r = db2_fetch_row($_sc); if ($_r) $_sgnPorr = (int)db2_result($_sc, 0); }

    $sql  = "SELECT RTRIM(FPPORT) AS FPPORT, "
          . "       RTRIM(FPDESC) AS FPDESC, RTRIM(FUDESC) AS FUDESC, "
          . "       RTRIM(FUURL)  AS FUURL "
          . "FROM SGHDSDATA.SYROLD "
          . "INNER JOIN SGHDSDATA.SYPORT ON FPPORT=RDPORT "
          . "INNER JOIN SGHDSDATA.SYURLM ON FUID=FPID ";
    if ($_sgnPorr > 0) {
        $sql .= "INNER JOIN SGHDSDATA.SYPORR "
              . "ON RDROLE=PRROLE AND FPPORT=PRPORT AND FPPAGE=PRPAGE AND FPSEQ=PRSEQ ";
    }
    $sql .= "WHERE RDROLE='$role_safe' AND RTRIM(FPPAGE)='' ";
    if ($_sgnPorr > 0) {
        $sql .= "AND PRSEL='Y' ";
    }
    $sql .= "ORDER BY RDSEQN, FPSEQ";

    $stmt = @db2_exec($_sgnConn, $sql);
    if ($stmt) {
        while ($row = db2_fetch_assoc($stmt)) {
            $label = (rtrim($row['FPDESC']) !== '') ? rtrim($row['FPDESC']) : rtrim($row['FUDESC']);
            $_sgnItems[] = array(
                'port'  => rtrim($row['FPPORT']),
                'label' => $label,
                'href'  => sgn_buildURL(
                    rtrim($row['FUURL']), rtrim($row['FPPORT']),
                    $_sgnHome, $_sgnPhp, $_sgnCgi, $_sgnHelp,
                    $_sgnBv, $_sgnEid, $_sgnPrfh, $_sgnNews, $_sgnBrws
                ),
            );
        }
    }
}
?>
<style type="text/css">
#sgn-left-nav {
    position:fixed; left:0; top:0; width:155px; height:100vh;
    background:linear-gradient(to bottom,
        #1DA032 0%,   /* green  */
        #1840A8 22%,  /* blue   */
        #7B1FA2 44%,  /* purple */
        #CC1F20 63%,  /* red    */
        #E86200 82%,  /* orange */
        #FFD000 100%  /* yellow */
    );
    overflow-y:auto; z-index:99999;
    box-shadow:2px 0 6px rgba(0,0,0,0.35);
    font-family:Arial,sans-serif;
    pointer-events:auto !important;
}
.sgn-hdr {
    background:rgba(0,0,0,0.35); color:#fff !important;
    padding:9px 10px 7px; font-size:10px; font-weight:700 !important;
    letter-spacing:1px; text-transform:uppercase;
    border-bottom:1px solid rgba(255,255,255,0.25);
    position:sticky; top:0; z-index:1;
}
.sgn-item {
    display:block !important; padding:4px 8px 4px 10px;
    font-size:11px; color:#fff !important; font-weight:700 !important;
    text-decoration:none !important; cursor:pointer !important;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    border-bottom:1px solid rgba(255,255,255,0.04);
    pointer-events:auto !important;
}
.sgn-item:hover  { background:rgba(255,255,255,0.18) !important; color:#fff !important; text-decoration:underline !important; }
.sgn-item.sgn-active { background:rgba(255,255,255,0.22) !important; color:#fff !important; font-weight:700 !important; }
.sgn-empty {
    padding:12px; font-size:10px; color:#fff !important; font-style:italic;
}
</style>

<div id="sgn-left-nav">
  <div class="sgn-hdr">EIP Navigation</div>
  <?php if (empty($_sgnItems)): ?>
    <div class="sgn-empty">
      <?php if (empty($_sgnRole)): ?>
        No role found
      <?php else: ?>
        No items for role:<br><?php echo htmlspecialchars($_sgnRole); ?>
      <?php endif; ?>
    </div>
  <?php else: foreach ($_sgnItems as $_ni): ?>
    <a class="sgn-item<?php echo ($_ni['port'] === $_sgnPort) ? ' sgn-active' : ''; ?>"
       href="<?php echo htmlspecialchars($_ni['href'], ENT_QUOTES); ?>"
       title="<?php echo htmlspecialchars($_ni['label'], ENT_QUOTES); ?>">
      <?php echo htmlspecialchars($_ni['label']); ?>
    </a>
  <?php endforeach; endif; ?>
</div>

<script type="text/javascript">
(function () {
    var w = 155;
    document.body.style.paddingLeft = w + 'px';
    // keep td.content width in sync via CSS var
    document.documentElement.style.setProperty('--sgn-nav-w', w + 'px');
}());
</script>
