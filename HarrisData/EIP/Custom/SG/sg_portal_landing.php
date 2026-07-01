<?php
require_once dirname(__FILE__) . '/../GetURLParm.php';

$proto    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'];
$backHref = $proto . '://' . $host . '/Welcome.php?baseVar=BaseConfiguration.php&eID=' . urlencode($eID) . '&portal=9999999999';

$portal = isset($_GET['portal']) ? strtoupper(trim($_GET['portal'])) : '';
$cat    = isset($_GET['cat'])    ? strtoupper(trim($_GET['cat']))    : '';

$portalNames = array(
    'SGINQ'   => 'SG Inquiries',
    'SGDASH'  => 'SG Dashboards',
    'SGDINT'  => 'SG Data Integrity',
    'SGRPT'   => 'SG Reports',
    'SGSOP'   => "SG SOP's",
    'SGTRAIN' => 'SG Training Guides',
    'SGMGMT'  => 'SG Management',
);

$catNames = array(
    'ACCT'   => 'Accounting',
    'INVMGMT'=> 'Inventory Management',
    'MFG'    => 'Manufacturing',
    'OE'     => 'Order Entry',
    'PLN'    => 'Planning',
    'PUR'    => 'Purchasing',
);

// Reports keyed by portal => cat => [ [title, desc, file], ... ]
$reportMap = array(
    'SGINQ' => array(
        'MFG' => array(
            array(
                'title' => 'MO Daily Labor Report',
                'desc'  => 'Today\'s labor detail by MO. Filter by Emp#, MO#, or Work Center; sortable; export to Excel; auto-refreshes every 10 min (M-F, 7am-5pm ET)',
                'file'  => 'Manufacturing/MODailyLaborReport.php',
            ),
            array(
                'title' => 'MO Material Components Issues',
                'desc'  => 'MO component qty variances. Sortable, filter by order status, auto-refreshes every 10 min (M-F, 7am-5pm ET)',
                'file'  => 'Manufacturing/MOMaterialComponents.php',
            ),
        ),
        'OE' => array(
            array(
                'title' => 'Customer Service Inquiry',
                'desc'  => 'Search open and closed orders by order #, invoice #, customer name, phone, P/O#, city/state, or item #/description',
                'file'  => 'Order%20Entry/CustServiceInquiry.php',
            ),
        ),
    ),
    'SGDASH' => array(
        'OE' => array(
            array(
                'title' => 'Bookings Dashboard',
                'desc'  => 'D/W/M/Y bookings by salesperson. Auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/BookingsDashboard.php',
            ),
            array(
                'title' => 'Shipments Dashboard',
                'desc'  => 'Orders shipped today + D/W/M/Y invoice totals by salesperson. Auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/ShipmentsDashboard.php',
            ),
            array(
                'title' => 'Sales Dashboard',
                'desc'  => 'D/W/M/Y sales by salesperson. Auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/SalesDashboard.php',
            ),
        ),
    ),
    'SGDINT' => array(
        'INVMGMT' => array(
            array(
                'title' => 'Inventory Data Integrity Dashboard',
                'desc'  => 'Items with incorrect inventory type codes, costing errors by product class',
                'file'  => 'Inventory%20Management/InvDataIntegrityDashboard.php',
            ),
        ),
        'OE' => array(
            array(
                'title' => 'CS Data Integrity Dashboard',
                'desc'  => 'Duplicate PO#s, open order taxes, CC fees, bad customer data, zero-cost lines, QM product class issues',
                'file'  => 'Order%20Entry/CSDataIntegrityDashboard.php',
            ),
        ),
    ),
    'SGTRAIN' => array(
        'OE' => array(
            array(
                'title'  => 'Customer Service Inquiry Training Guide',
                'desc'   => 'Video tutorial. CS Inquiry search tips and order lookup walkthrough',
                'file'   => 'Training%20Guides/Order%20Entry/CSInqTrainingVideo.php',
                'icon'   => '&#127891;',
            ),
        ),
    ),
    'SGMGMT' => array(
        '' => array(
            array(
                'title' => 'Bookings Dashboard',
                'desc'  => 'D/W/M/Y bookings by salesperson. Auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/BookingsDashboard.php',
            ),
            array(
                'title' => 'Shipments Dashboard',
                'desc'  => 'Orders shipped today + D/W/M/Y invoice totals by salesperson. Auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/ShipmentsDashboard.php',
            ),
            array(
                'title' => 'Sales Dashboard',
                'desc'  => 'D/W/M/Y sales by salesperson. Auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/SalesDashboard.php',
            ),
            array(
                'title' => 'Current Revenue vs Goal',
                'desc'  => 'YTD revenue vs $18.3M annual goal with % completion. Drill down by ship-to class code with pie chart; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/RevenueVsGoal.php',
            ),
            array(
                'title' => 'New Account Revenue vs Goal',
                'desc'  => 'YTD invoiced revenue from new accounts vs $4M goal with % completion. Click customer # to open in EIP; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/NewAccountsRevenue.php',
            ),
            array(
                'title' => 'Bottom 50% Customer Revenue Growth',
                'desc'  => 'Revenue growth of bottom-half customers. Compares same YTD period last year vs this year; sortable detail table; export to Excel; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/BottomHalfRevenue.php',
            ),
            array(
                'title' => 'Daily Sales Cust Class Past 5 Years',
                'desc'  => 'YTD invoiced sales by customer class for past 5 years side-by-side. Click class code for 5-year pie chart; export to Excel; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/CustClassSales5Yr.php',
            ),
        ),
        'OE' => array(
            array(
                'title' => 'Bookings Dashboard',
                'desc'  => 'D/W/M/Y bookings by salesperson. Auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/BookingsDashboard.php',
            ),
            array(
                'title' => 'Shipments Dashboard',
                'desc'  => 'Orders shipped today + D/W/M/Y invoice totals by salesperson. Auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/ShipmentsDashboard.php',
            ),
            array(
                'title' => 'Sales Dashboard',
                'desc'  => 'D/W/M/Y sales by salesperson. Auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/SalesDashboard.php',
            ),
            array(
                'title' => 'Current Revenue vs Goal',
                'desc'  => 'YTD revenue vs $18.3M annual goal with % completion. Drill down by ship-to class code with pie chart; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/RevenueVsGoal.php',
            ),
            array(
                'title' => 'New Account Revenue vs Goal',
                'desc'  => 'YTD invoiced revenue from new accounts vs $4M goal with % completion. Click customer # to open in EIP; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/NewAccountsRevenue.php',
            ),
            array(
                'title' => 'Bottom 50% Customer Revenue Growth',
                'desc'  => 'Revenue growth of bottom-half customers. Compares same YTD period last year vs this year; sortable detail table; export to Excel; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/BottomHalfRevenue.php',
            ),
            array(
                'title' => 'Daily Sales Cust Class Past 5 Years',
                'desc'  => 'YTD invoiced sales by customer class for past 5 years side-by-side. Click class code for 5-year pie chart; export to Excel; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/CustClassSales5Yr.php',
            ),
        ),
    ),
    'SGRPT' => array(
        'ACCT' => array(
            array(
                'title' => 'AR Aging Report',
                'desc'  => 'Open AR invoices aged by due date. Detail and summary views; sortable; filter by customer and bucket; Age As Of date picker; export to Excel',
                'file'  => 'ARAgingReport.php',
            ),
        ),
        'MFG' => array(
            array(
                'title' => 'Manufacturing Order Requirements',
                'desc'  => 'Items with net shortage. Auto-refreshes every 10 minutes',
                'file'  => 'Manufacturing/MORequirements.php',
            ),
            array(
                'title' => 'MO Receipt Report',
                'desc'  => 'Open MOs with received qty that are not fully closed. Action badges (Check & Verify / Close / Final Tag & Close); filter by Status and Action; sortable; export to Excel; auto-refreshes every 10 min',
                'file'  => 'Manufacturing/MOOpenBalanceReport.php',
            ),
        ),
        'PUR' => array(
            array(
                'title' => 'PO Requirements Report',
                'desc'  => 'Items with negative availability (Qty Available < 0). Sortable; export to Excel; auto-refreshes every 10 min',
                'file'  => 'Purchasing/PORequirementsReport.php',
            ),
        ),
        'PLN' => array(
            array(
                'title' => 'Manufacturing Order Requirements',
                'desc'  => 'Items with net shortage. Auto-refreshes every 10 minutes',
                'file'  => 'Manufacturing/MORequirements.php',
            ),
            array(
                'title'  => 'Open Order Line Item Comments',
                'desc'   => 'Open order lines with ACK comments. Sortable, auto-refreshes every 15 min (7am–4pm CT)',
                'file'   => 'Planning/OpenOrderLineItemComments.php',
                'target' => '_blank',
            ),
        ),
    ),
);

$portalDisplay = isset($portalNames[$portal]) ? $portalNames[$portal] : $portal;
$catDisplay    = isset($catNames[$cat])       ? $catNames[$cat]       : $cat;
$pageTitle     = $portalDisplay . ($catDisplay ? ' - ' . $catDisplay : '');

$items = isset($reportMap[$portal][$cat]) ? $reportMap[$portal][$cat] : array();

// Build list of categories that have items for this portal
$availCats = array();
if (isset($reportMap[$portal])) {
    foreach ($reportMap[$portal] as $ck => $cv) {
        if ($ck !== '' && !empty($cv)) {
            $availCats[$ck] = isset($catNames[$ck]) ? $catNames[$ck] : $ck;
        }
    }
}
$showCatSelect = ($cat === '' && empty($items) && !empty($availCats));

$catIcons = array(
    'ACCT'   => '&#128176;',
    'INVMGMT'=> '&#128230;',
    'MFG'    => '&#9881;',
    'OE'     => '&#128666;',
    'PLN'    => '&#128197;',
    'PUR'    => '&#128722;',
);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($pageTitle); ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f0f2f5; }
.header {
    background: linear-gradient(135deg, #2a5a8c 0%, #1a3d5c 100%);
    color: #fff;
    padding: 14px 24px;
    border-bottom: 3px solid #f90;
}
.header h1 { font-size: 20px; font-weight: bold; }
.header .sub { font-size: 12px; opacity: 0.75; margin-top: 3px; }
.breadcrumb { font-size: 11px; color: rgba(255,255,255,.65); margin-bottom: 6px; }
.breadcrumb a { color: rgba(255,255,255,.75); text-decoration: none; }
.breadcrumb a:hover { text-decoration: underline; }
.report-list { max-width: 700px; }
.report-row {
    display: flex; align-items: center; background: #fff;
    border: 1px solid #dce1e8; border-radius: 6px; padding: 16px 20px;
    margin-bottom: 10px; text-decoration: none; color: inherit;
    box-shadow: 0 2px 4px rgba(0,0,0,.04); transition: box-shadow .15s, border-color .15s;
    cursor: pointer;
}
.report-row:hover { border-color: #2a5a8c; box-shadow: 0 3px 10px rgba(0,0,0,.12); }
.report-icon { font-size: 24px; margin-right: 16px; color: #2a5a8c; flex-shrink: 0; }
.report-info { flex: 1; }
.report-title { font-size: 15px; font-weight: bold; color: #2a5a8c; }
.report-desc  { font-size: 12px; color: #666; margin-top: 3px; line-height: 1.4; }
.report-arrow { font-size: 22px; color: #aaa; margin-left: 12px; }
.cat-grid { display: flex; flex-wrap: wrap; gap: 16px; max-width: 780px; }
.cat-tile {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    background: #fff; border: 1px solid #dce1e8; border-radius: 8px;
    padding: 28px 24px; width: 180px; text-decoration: none;
    box-shadow: 0 2px 6px rgba(0,0,0,.05); transition: box-shadow .15s, border-color .15s;
    cursor: pointer;
}
.cat-tile:hover { border-color: #2a5a8c; box-shadow: 0 4px 14px rgba(0,0,0,.12); }
.cat-tile .tile-icon { font-size: 36px; margin-bottom: 12px; }
.cat-tile .tile-name { font-size: 14px; font-weight: bold; color: #2a5a8c; text-align: center; }
.cat-tile .tile-count { font-size: 11px; color: #888; margin-top: 4px; }
.page-layout { display: flex; align-items: flex-start; }
.sidebar {
    width: 160px; flex-shrink: 0;
    background: #1a3d5c; min-height: calc(100vh - 80px);
    padding: 14px 10px;
}
.back-btn {
    display: block; background: #2a5a8c; color: #cde0ff;
    text-decoration: none; font-size: 12px; font-weight: 700;
    padding: 8px 12px; border-radius: 4px; text-align: center;
    border: 1px solid rgba(255,255,255,0.15); margin-bottom: 8px;
}
.back-btn:hover { background: #3a6a9c; color: white; }
.sidebar-link {
    display: block; color: #cde0ff; text-decoration: none;
    font-size: 11px; padding: 5px 8px; border-radius: 3px; margin-bottom: 2px;
}
.sidebar-link:hover { background: rgba(255,255,255,0.12); color: white; }
.sidebar-link.active { background: rgba(255,255,255,0.18); color: white; font-weight: bold; }
.sidebar-hdr { color: #7aafd4; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin: 14px 0 4px; padding: 0 4px; }
.main-content { flex: 1; padding: 24px; }
.section-title { font-size: 16px; font-weight: bold; color: #1a3d5c; margin-bottom: 18px; }
</style>
</head>
<body>
<div class="header">
  <div class="breadcrumb">
    <a href="<?php echo htmlspecialchars($backHref); ?>">EIP Home</a>
    &rsaquo; <?php echo htmlspecialchars($portalDisplay); ?>
    <?php if ($catDisplay): ?> &rsaquo; <?php echo htmlspecialchars($catDisplay); ?><?php endif; ?>
  </div>
  <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
  <div class="sub">Screen Graphics</div>
</div>
<div class="page-layout">
<div class="sidebar">
  <a class="back-btn" href="<?php echo htmlspecialchars($backHref); ?>">&#8592; Back to EIP</a>
  <?php if (!empty($availCats)): ?>
    <div class="sidebar-hdr">Categories</div>
    <?php
    $portalRoot = '?portal=' . urlencode($portal) . '&baseVar=' . urlencode($baseVar) . '&eID=' . urlencode($eID);
    foreach ($availCats as $ck => $cn):
        $catLink = $portalRoot . '&cat=' . urlencode($ck);
        $isActive = ($cat === $ck);
    ?>
    <a class="sidebar-link<?php echo $isActive ? ' active' : ''; ?>"
       href="<?php echo htmlspecialchars($catLink); ?>">
      <?php echo htmlspecialchars($cn); ?>
    </a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<div class="main-content">
<?php if ($showCatSelect): ?>
  <div class="section-title">Select a category:</div>
  <div class="cat-grid">
    <?php foreach ($availCats as $ck => $cn):
        $catLink  = '?portal=' . urlencode($portal) . '&cat=' . urlencode($ck)
                  . '&baseVar=' . urlencode($baseVar) . '&eID=' . urlencode($eID);
        $icon     = isset($catIcons[$ck]) ? $catIcons[$ck] : '&#128193;';
        $cnt      = count($reportMap[$portal][$ck]);
    ?>
    <a class="cat-tile" href="<?php echo htmlspecialchars($catLink); ?>">
      <div class="tile-icon"><?php echo $icon; ?></div>
      <div class="tile-name"><?php echo htmlspecialchars($cn); ?></div>
      <div class="tile-count"><?php echo $cnt; ?> report<?php echo $cnt !== 1 ? 's' : ''; ?></div>
    </a>
    <?php endforeach; ?>
  </div>
<?php elseif (empty($items)): ?>
  <p style="font-size:13px;color:#555;">No reports available<?php echo $catDisplay ? ' for ' . htmlspecialchars($catDisplay) : ''; ?> at this time.</p>
<?php else: ?>
  <?php if ($catDisplay): ?>
    <div class="section-title"><?php echo htmlspecialchars($catDisplay); ?></div>
  <?php endif; ?>
  <div class="report-list">
    <?php foreach ($items as $item):
        $params = array('baseVar' => $baseVar, 'eID' => $eID, 'portal' => $portal);
        $url    = htmlspecialchars($item['file'] . '?' . http_build_query($params));
        $tgt    = ' target="' . (!empty($item['target']) ? htmlspecialchars($item['target']) : '_self') . '"';
        $icon   = !empty($item['icon']) ? $item['icon'] : '&#128202;';
    ?>
    <a class="report-row" href="<?php echo $url; ?>"<?php echo $tgt; ?>>
      <div class="report-icon"><?php echo $icon; ?></div>
      <div class="report-info">
        <div class="report-title"><?php echo htmlspecialchars($item['title']); ?></div>
        <?php if (!empty($item['desc'])): ?>
        <div class="report-desc"><?php echo htmlspecialchars($item['desc']); ?></div>
        <?php endif; ?>
      </div>
      <div class="report-arrow">&#8250;</div>
    </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</div><!-- /main-content -->
</div><!-- /page-layout -->
</body>
</html>
