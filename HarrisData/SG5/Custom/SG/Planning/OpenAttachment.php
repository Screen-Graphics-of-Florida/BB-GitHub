<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';

$ordNum = isset($_GET['ordNum']) ? (int)$_GET['ordNum'] : 0;
if ($ordNum <= 0) {
    http_response_code(400);
    echo '<!DOCTYPE html><html><body style="font-family:Arial;padding:20px;"><p>Invalid order number.</p></body></html>';
    exit;
}

$padded   = str_pad($ordNum, 8, '0', STR_PAD_LEFT);
$fileName = $ordNum . '.xlsx';

// Attachments live under EIP regardless of which portal serves this script.
// From EIP/Custom/SG/Planning:  ../../.. = EIP root
// From SG5/Custom/SG/Planning:  ../../../.. = HarrisData root, then down to EIP
$scriptDir = str_replace('\\', '/', dirname(__FILE__));
if (strpos($scriptDir, '/EIP/') !== false) {
    $attachRoot = dirname(__FILE__) . '/../../../Attachments/SG/SalesOrder/';
} else {
    $attachRoot = dirname(__FILE__) . '/../../../../EIP/Attachments/SG/SalesOrder/';
}

$filePath = $attachRoot . $padded . '/' . $fileName;

if (!file_exists($filePath)) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body style="font-family:Arial;padding:20px;">';
    echo '<p><strong>Attachment not found</strong> for order ' . htmlspecialchars((string)$ordNum) . '.</p>';
    echo '<p style="color:#666;font-size:13px;">Expected: ' . htmlspecialchars($padded . '/' . $fileName) . '</p>';
    echo '</body></html>';
    exit;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, no-cache');
header('Pragma: no-cache');
readfile($filePath);
exit;
