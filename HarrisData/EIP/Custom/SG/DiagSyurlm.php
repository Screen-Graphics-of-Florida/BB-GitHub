<?php
// Query ALL SYURLM entries — shows FUID, FUURL, FUTSPT, FUTSUS
// Used to find URL pattern for native EIP portals
// URL: https://portal.screen-graphics.com:5601/Custom/SG/DiagSyurlm.php

$conn = db2_connect('*LOCAL', '', '');
if (!$conn) die('DB error: ' . htmlspecialchars(db2_conn_errormsg()));

$filter = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($filter !== '') {
    $safe = str_replace("'", "''", $filter);
    $sql = "SELECT RTRIM(FUID) AS FUID, RTRIM(FUTSPT) AS FUTSPT, "
         . "       RTRIM(FUTSUS) AS FUTSUS, RTRIM(FUTSWS) AS FUTSWS, "
         . "       RTRIM(FUURL) AS FUURL "
         . "FROM SGHDSDATA.SYURLM "
         . "WHERE FUID LIKE '%$safe%' "
         . "ORDER BY FUID";
} else {
    $sql = "SELECT RTRIM(FUID) AS FUID, RTRIM(FUTSPT) AS FUTSPT, "
         . "       RTRIM(FUTSUS) AS FUTSUS, RTRIM(FUTSWS) AS FUTSWS, "
         . "       RTRIM(FUURL) AS FUURL "
         . "FROM SGHDSDATA.SYURLM "
         . "ORDER BY FUID";
}

$rows = array();
$s = @db2_exec($conn, $sql);
if ($s) while ($r = db2_fetch_assoc($s)) $rows[] = $r;

db2_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SYURLM Entries</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font:12px Arial,sans-serif; background:#f0f2f5; padding:16px; }
h2 { background:#2a5a8c; color:#fff; padding:7px 14px; border-radius:4px;
     font-size:14px; border-bottom:3px solid #f90; margin-bottom:10px; }
form { margin-bottom:12px; display:flex; gap:8px; align-items:center; }
input  { padding:5px 9px; font-size:13px; border:1px solid #ccc;
         border-radius:4px; width:280px; }
button { padding:5px 13px; font-size:13px; background:#2a5a8c; color:#fff;
         border:none; border-radius:4px; cursor:pointer; }
table { border-collapse:collapse; width:100%; background:#fff;
        border-radius:4px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,.08); }
th { background:#2a5a8c; color:#fff; padding:5px 10px;
     text-align:left; font-size:11px; }
td { padding:4px 10px; font-size:11px; font-family:monospace;
     border-bottom:1px solid #f0f0f0; }
.y { color:#2e7d32; font-weight:bold; }
.n { color:#c62828; font-weight:bold; }
</style>
</head>
<body>

<form>
  <input name="q" value="<?= htmlspecialchars($filter) ?>"
         placeholder="filter by FUID (blank = show all)">
  <button>Filter</button>
  <a href="?" style="font-size:12px">Clear</a>
</form>

<h2>SYURLM — <?= count($rows) ?> rows<?= $filter?" (filter: $filter)":'' ?></h2>
<table>
  <tr>
    <th>FUID</th>
    <th>FUTSPT</th>
    <th>FUTSUS</th>
    <th>FUTSWS</th>
    <th>FUURL</th>
  </tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= htmlspecialchars($r['FUID']) ?></td>
    <td class="<?= $r['FUTSPT']==='Y'?'y':'n' ?>">
      <?= htmlspecialchars($r['FUTSPT']===''?'(blank)':$r['FUTSPT']) ?>
    </td>
    <td><?= htmlspecialchars($r['FUTSUS']) ?></td>
    <td><?= htmlspecialchars($r['FUTSWS']) ?></td>
    <td style="max-width:500px;word-break:break-all">
      <?= htmlspecialchars($r['FUURL']) ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

</body>
</html>
