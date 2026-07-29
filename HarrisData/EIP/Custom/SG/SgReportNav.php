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
// Portal nav always navigates via BaseConfiguration.php, same as the portal's own nav
$_sgnNavBv = 'BaseConfiguration.php';
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
        if ($w === '') {
            return $home . '/Welcome.php?baseVar=' . urlencode($bv)
                 . '&eID=' . urlencode($eid)
                 . '&portal=' . urlencode(trim($fpport));
        }

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
            if (strpos($fuurl, '@@baseVar') !== false) {
                $w = str_replace('@@baseVar', urlencode($bvWrk), $w);
            } elseif (strpos($w, 'baseVar=') === false) {
                $w  .= $amp . 'baseVar=' . urlencode($bvWrk);
                $amp = '&';
            }
            if (strpos($w, 'eID=') === false) {
                $amp = (strpos($w, '?') !== false) ? '&' : '?';
                $w  .= $amp . 'eID=' . urlencode($eid);
            }
            $amp = (strpos($w, '?') !== false) ? '&' : '?';
            if (strpos($fuurl, '@@portal') !== false) {
                $w = str_replace('@@portal', urlencode(trim($fpport)), $w);
            } elseif (strpos($w, 'portal=') === false) {
                $w .= $amp . 'portal=' . urlencode(trim($fpport));
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

    // TYPE 1 = top-level portal entry (FPPAGE blank)
    // TYPE 2 = second-level item belonging to that portal (FPPAGE = FPPORT)
    // Mirrors the hierarchy GetMenu.php uses for the native Harris flyouts.
    $sql  = "SELECT RTRIM(FPPORT) AS FPPORT, RTRIM(FPPAGE) AS FPPAGE, "
          . "       RTRIM(FPDESC) AS FPDESC, RTRIM(FUDESC) AS FUDESC, "
          . "       RTRIM(FUURL)  AS FUURL,  RTRIM(FUTRGT) AS FUTRGT, "
          . "       CASE WHEN RTRIM(FPPAGE)='' THEN 1 ELSE 2 END AS LVL "
          . "FROM SGHDSDATA.SYROLD "
          . "INNER JOIN SGHDSDATA.SYPORT ON FPPORT=RDPORT "
          . "INNER JOIN SGHDSDATA.SYURLM ON FUID=FPID ";
    if ($_sgnPorr > 0) {
        $sql .= "INNER JOIN SGHDSDATA.SYPORR "
              . "ON RDROLE=PRROLE AND FPPORT=PRPORT AND FPPAGE=PRPAGE AND FPSEQ=PRSEQ ";
    }
    $sql .= "WHERE RDROLE='$role_safe' "
          . "AND (RTRIM(FPPAGE)='' OR RTRIM(FPPAGE)=RTRIM(FPPORT)) ";
    if ($_sgnPorr > 0) {
        $sql .= "AND PRSEL='Y' ";
    }
    $sql .= "ORDER BY RDSEQN, RDPORT, LVL, FPSEQ";

    $stmt = @db2_exec($_sgnConn, $sql);
    if ($stmt) {
        $_sgnKids  = array();   // FPPORT => list of second-level items
        $_sgnOrder = array();   // top-level items, in role sequence
        while ($row = db2_fetch_assoc($stmt)) {
            $label = (rtrim($row['FPDESC']) !== '') ? rtrim($row['FPDESC']) : rtrim($row['FUDESC']);
            $port  = rtrim($row['FPPORT']);
            $entry = array(
                'port'  => $port,
                'label' => $label,
                'href'  => sgn_buildURL(
                    rtrim($row['FUURL']), $port,
                    $_sgnHome, $_sgnPhp, $_sgnCgi, $_sgnHelp,
                    $_sgnNavBv, $_sgnEid, $_sgnPrfh, $_sgnNews, $_sgnBrws
                ),
                'new_session' => (stripos($label, 'new session') !== false),
                'target'      => rtrim($row['FUTRGT']),
            );
            if ((int)$row['LVL'] === 1) {
                $_sgnOrder[] = $entry;
            } else {
                $_sgnKids[$port][] = $entry;
            }
        }
        // Attach children to their parent portal
        foreach ($_sgnOrder as $_top) {
            $_top['kids'] = isset($_sgnKids[$_top['port']]) ? $_sgnKids[$_top['port']] : array();
            $_sgnItems[]  = $_top;
        }
    }
}
?>
<style type="text/css">
#sgn-left-nav {
    position:fixed; left:0; top:0; width:155px; height:100vh;
    background:linear-gradient(to bottom,
        #111827 0%,
        #1F2937 25%,
        #374151 55%,
        #4B5563 78%,
        #6B7280 100%
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

/* ── Hover flyout submenus ──────────────────────────────────────────────
   The flyout is position:fixed so it escapes #sgn-left-nav's overflow-y:auto
   clipping (overflow does not clip fixed descendants). Its top is set by JS
   on hover to line up with the parent row. */
.sgn-node { position:relative; }
.sgn-caret {
    float:right; opacity:0.75; font-size:11px; line-height:1;
    margin-left:4px; font-weight:700;
}
.sgn-fly {
    display:none; position:fixed; left:155px; min-width:190px; max-width:280px;
    background:#374151; border-left:2px solid #f90;
    box-shadow:3px 3px 10px rgba(0,0,0,0.45);
    z-index:100000; padding:3px 0;
    max-height:100vh; overflow-y:auto;
}
/* Opened by JS, not :hover — the nav's scrollbar leaves a dead strip between the
   row and the panel, and bare :hover closes the moment the pointer crosses it. */
.sgn-fly.sgn-open { display:block; }
.sgn-sub {
    display:block !important; padding:5px 12px;
    font-size:11px; color:#fff !important; font-weight:600 !important;
    text-decoration:none !important; cursor:pointer !important;
    white-space:nowrap; border-bottom:1px solid rgba(255,255,255,0.06);
}
.sgn-sub:last-child { border-bottom:none; }
.sgn-sub:hover { background:rgba(255,255,255,0.20) !important; color:#fff !important; text-decoration:underline !important; }
@media print { #sgn-left-nav { display:none !important; } }
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
  <?php else: foreach ($_sgnItems as $_ni):
        $_hasKids = !empty($_ni['kids']); ?>
    <div class="sgn-node">
      <a class="sgn-item<?php echo ($_ni['port'] === $_sgnPort) ? ' sgn-active' : ''; ?>"
         href="<?php echo htmlspecialchars($_ni['href'], ENT_QUOTES); ?>"
         target="<?php echo $_ni['new_session'] ? '_blank' : '_top'; ?>"
         title="<?php echo htmlspecialchars($_ni['label'], ENT_QUOTES); ?>">
        <?php if ($_hasKids): ?><span class="sgn-caret">&#8250;</span><?php endif; ?>
        <?php echo htmlspecialchars($_ni['label']); ?>
      </a>
      <?php if ($_hasKids): ?>
      <div class="sgn-fly">
        <?php foreach ($_ni['kids'] as $_sk): ?>
        <a class="sgn-sub"
           href="<?php echo htmlspecialchars($_sk['href'], ENT_QUOTES); ?>"
           target="<?php echo $_sk['new_session'] ? '_blank' : '_top'; ?>"
           title="<?php echo htmlspecialchars($_sk['label'], ENT_QUOTES); ?>">
          <?php echo htmlspecialchars($_sk['label']); ?>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
</div>

<script type="text/javascript">
(function () {
    var w = 155;
    document.body.style.paddingLeft = w + 'px';
    // keep td.content width in sync via CSS var
    document.documentElement.style.setProperty('--sgn-nav-w', w + 'px');

    // Flyout open/close is driven from JS rather than :hover for two reasons:
    //   1. The panel is position:fixed (to escape this bar's overflow clipping), so its
    //      top has to be measured from the parent row.
    //   2. The bar's scrollbar leaves a ~15px dead strip between the row and the panel.
    //      With bare :hover the panel vanishes as the pointer crosses it, so closing is
    //      deferred by CLOSE_DELAY and cancelled if the pointer lands on the panel.
    var CLOSE_DELAY = 400;   // ms of grace to travel from the row to the panel
    var openFly = null, closeTimer = null;

    function setOpen(fly, on) {
        var cls = (' ' + fly.className + ' ').replace(/ sgn-open /g, ' ');
        fly.className = (on ? cls + 'sgn-open' : cls).replace(/^\s+|\s+$/g, '');
    }
    function cancelClose() {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
    }
    function scheduleClose() {
        cancelClose();
        closeTimer = setTimeout(function () {
            if (openFly) { setOpen(openFly, false); openFly = null; }
            closeTimer = null;
        }, CLOSE_DELAY);
    }
    function openFlyout(node, fly) {
        cancelClose();
        if (openFly && openFly !== fly) { setOpen(openFly, false); }
        // Show hidden first so offsetHeight can be measured without a visible jump
        fly.style.top = '0px';
        fly.style.visibility = 'hidden';
        setOpen(fly, true);
        var r   = node.getBoundingClientRect();
        var h   = fly.offsetHeight;
        var top = r.top;
        // keep the panel on screen if the row sits near the bottom
        if (top + h > window.innerHeight - 4) {
            top = Math.max(2, window.innerHeight - h - 4);
        }
        fly.style.top = top + 'px';
        fly.style.visibility = '';
        openFly = fly;
    }

    var nodes = document.querySelectorAll('#sgn-left-nav .sgn-node');
    for (var i = 0; i < nodes.length; i++) {
        (function (node) {
            var fly = node.querySelector('.sgn-fly');   // one panel per node, no nesting
            if (!fly) return;
            node.onmouseenter = function () { openFlyout(node, fly); };
            node.onmouseleave = scheduleClose;
            // Pointer reached the panel — keep it up. Needed because crossing the
            // scrollbar strip fires mouseleave on the row first.
            fly.onmouseenter  = cancelClose;
            fly.onmouseleave  = scheduleClose;
        }(nodes[i]));
    }
}());
</script>
