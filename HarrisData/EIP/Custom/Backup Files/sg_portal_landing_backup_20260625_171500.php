<?php
require_once dirname(__FILE__) . '/../GetURLParm.php';

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
                'desc'  => 'Today\'s labor detail by MO — filter by Emp#, MO#, or Work Center; sortable; export to Excel; auto-refreshes every 10 min (M-F, 7am-5pm ET)',
                'file'  => 'Manufacturing/MODailyLaborReport.php',
            ),
            array(
                'title' => 'MO Material Components Issues',
                'desc'  => 'MO component qty variances — sortable, filter by order status, auto-refreshes every 10 min (M-F, 7am-5pm ET)',
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
                'desc'  => 'D/W/M/Y bookings by salesperson — auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/BookingsDashboard.php',
            ),
            array(
                'title' => 'Shipments Dashboard',
                'desc'  => 'Orders shipped today + D/W/M/Y invoice totals by salesperson — auto-refreshes every 15 min (M-F, 7am-6pm ET)',
                'file'  => 'Order%20Entry/ShipmentsDashboard.php',
            ),
            array(
                'title' => 'Sales Dashboard',
                'desc'  => 'D/W/M/Y sales by salesperson — auto-refreshes every 15 min (M-F, 7am-6pm ET)',
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
                'desc'   => 'Video tutorial — CS Inquiry search tips and order lookup walkthrough',
                'file'   => 'Training%20Guides/Order%20Entry/CSInqTrainingVideo.php',
                'target' => '_blank',
                'icon'   => '&#127891;',
            ),
        ),
    ),
    'SGMGMT' => array(
        '' => array(
            array(
                'title' => 'Revenue vs Goal',
                'desc'  => 'YTD revenue vs $18.3M annual goal with % completion — drill down by ship-to class code with pie chart; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/RevenueVsGoal.php',
            ),
        ),
        'OE' => array(
            array(
                'title' => 'Revenue vs Goal',
                'desc'  => 'YTD revenue vs $18.3M annual goal with % completion — drill down by ship-to class code with pie chart; auto-refreshes at 4:30 pm & 5:00 pm ET (M-F)',
                'file'  => 'Management/RevenueVsGoal.php',
            ),
        ),
    ),
    'SGRPT' => array(
        'MFG' => array(
            array(
                'title' => 'Manufacturing Order Requirements',
                'desc'  => 'Items with net shortage — auto-refreshes every 10 minutes',
                'file'  => 'Manufacturing/MORequirements.php',
            ),
        ),
        'PLN' => array(
            array(
                'title' => 'Manufacturing Order Requirements',
                'desc'  => 'Items with net shortage — auto-refreshes every 10 minutes',
                'file'  => 'Manufacturing/MORequirements.php',
            ),
            array(
                'title' => 'Open Order Line Item Comments',
                'desc'  => 'Open order lines with ACK comments — sortable, auto-refreshes every 15 min (7am–4pm CT)',
                'file'  => 'Planning/OpenOrderLineItemComments.php',
            ),
        ),
    ),
);

$portalDisplay = isset($portalNames[$portal]) ? $portalNames[$portal] : $portal;
$catDisplay    = isset($catNames[$cat])       ? $catNames[$cat]       : $cat;
$pageTitle     = $portalDisplay . ($catDisplay ? ' - ' . $catDisplay : '');

$items = isset($reportMap[$portal][$cat]) ? $reportMap[$portal][$cat] : array();
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
.content { padding: 32px 24px; }
.card {
    background: #fff;
    border: 1px solid #dce1e8;
    border-radius: 6px;
    padding: 36px;
    max-width: 600px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,.06);
}
.card .icon { font-size: 40px; margin-bottom: 14px; color: #2a5a8c; }
.card h2 { font-size: 18px; color: #2a5a8c; margin-bottom: 10px; }
.card p  { font-size: 13px; color: #555; line-height: 1.6; }
.breadcrumb { font-size: 11px; color: rgba(255,255,255,.65); margin-bottom: 6px; }
.report-list { max-width: 700px; }
.report-row {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #dce1e8;
    border-radius: 6px;
    padding: 16px 20px;
    margin-bottom: 10px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 4px rgba(0,0,0,.04);
    transition: box-shadow .15s, border-color .15s;
}
.report-row:hover { border-color: #2a5a8c; box-shadow: 0 3px 10px rgba(0,0,0,.1); }
.report-icon { font-size: 24px; margin-right: 16px; color: #2a5a8c; flex-shrink: 0; }
.report-info { flex: 1; }
.report-title { font-size: 15px; font-weight: bold; color: #2a5a8c; }
.report-desc  { font-size: 12px; color: #666; margin-top: 3px; }
.report-arrow { font-size: 22px; color: #aaa; margin-left: 12px; }
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
    border: 1px solid rgba(255,255,255,0.15);
}
.back-btn:hover { background: #3a6a9c; color: white; }
.main-content { flex: 1; padding: 24px; }
</style>
</head>
<body>
<div class="header">
  <div class="breadcrumb">HarrisData EIP &rsaquo; <?php echo htmlspecialchars($portalDisplay); ?></div>
  <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
  <div class="sub">Screen Graphics</div>
</div>
<div class="page-layout">
<div class="sidebar">
  <a class="back-btn" href="javascript:history.back()">&#8592; Back to EIP</a>
</div>
<div class="main-content">
<?php if (empty($items)): ?>
  <div class="card">
    <div class="icon">&#128193;</div>
    <h2>No Items Available Yet</h2>
    <p>
      There are no <strong><?php echo htmlspecialchars($catDisplay); ?></strong> items in
      <strong><?php echo htmlspecialchars($portalDisplay); ?></strong> at this time.<br><br>
      Items will appear here as they are added.
    </p>
  </div>
<?php else: ?>
  <div class="report-list">
    <?php foreach ($items as $item):
        $params  = array('baseVar' => $baseVar, 'eID' => $eID, 'portal' => $portal);
        $url     = htmlspecialchars($item['file'] . '?' . http_build_query($params));
        $target  = ' target="' . (!empty($item['target']) ? htmlspecialchars($item['target']) : '_blank') . '"';
        $icon    = !empty($item['icon'])   ? $item['icon'] : '&#128202;';
    ?>
    <a class="report-row" href="<?php echo $url; ?>"<?php echo $target; ?>>
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
