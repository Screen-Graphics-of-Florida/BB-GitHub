<?php
// CS Inquiry Training Guide — video player
// Source: Customer_Service_Inquiry_Training_Guide.mp4 (same directory)

// Framework includes — provide $activeRole / $i5Connect / $eID / $portal for the EIP left nav.
require_once dirname(__FILE__) . '/../../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Customer Service Inquiry Training Guide</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    background: #000;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
}
video {
    width: 100%;
    max-width: 1280px;
    max-height: 100vh;
    outline: none;
}
</style>
</head>
<body>
<?php require_once dirname(__FILE__) . '/../../SgReportNav.php'; ?>
<video controls autoplay>
  <source src="Customer_Service_Inquiry_Training_Guide.mp4" type="video/mp4">
  Your browser does not support HTML5 video.
</video>
</body>
</html>
