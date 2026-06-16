<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

// Search SYURLM for order-related entries
$sql  = "SELECT FUID, FUDESC, FUTITL, FUURL FROM S5HDSDATA.SYURLM WHERE UPPER(FUDESC) LIKE '%ORDER%' OR UPPER(FUTITL) LIKE '%ORDER%' OR UPPER(FUURL) LIKE '%ORDER%' ORDER BY FUID FETCH FIRST 50 ROWS ONLY";
$stmt = db2_exec($conn, $sql);
$rows = array();
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) $rows[] = $r;
    db2_free_stmt($stmt);
}
$err = $stmt ? '' : db2_stmt_errormsg();
?>
<!DOCTYPE html><html><head><meta charset="utf-8">
<title>HDTBL — All Table Definitions</title>
<style>
body{font-family:Arial,sans-serif;font-size:13px;padding:16px;background:#f5f5f5;}
h2{margin-bottom:8px;}
input{padding:4px 8px;font-size:13px;width:300px;margin-bottom:10px;border:1px solid #ccc;border-radius:3px;}
table{border-collapse:collapse;background:#fff;width:500px;}
th{background:#003087;color:#fff;padding:5px 12px;text-align:left;}
td{padding:4px 12px;border-bottom:1px solid #ddd;}
tr:nth-child(even){background:#f0f0f0;}
tr.hi td{background:#fffacd;font-weight:bold;}
.err{background:#fdd;color:#900;padding:8px;margin-bottom:12px;}
</style>
</head><body>
<h2>SYURLM — Order-related entries (<?php echo count($rows); ?> rows)</h2>
<?php if ($err): ?>
<div class="err">Query error: <?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>
<input id="f" placeholder="Filter..." oninput="filterRows(this.value)" style="margin-bottom:10px">
<table>
<thead><tr><th>FUID</th><th>FUDESC</th><th>FUTITL</th><th>FUURL</th></tr></thead>
<tbody id="tb">
<?php foreach ($rows as $r): ?>
<tr data-desc="<?php echo strtolower(htmlspecialchars($r['FUDESC'].'|'.$r['FUTITL'].'|'.$r['FUURL'])); ?>">
  <td><?php echo htmlspecialchars($r['FUID']); ?></td>
  <td><?php echo htmlspecialchars($r['FUDESC']); ?></td>
  <td><?php echo htmlspecialchars($r['FUTITL']); ?></td>
  <td><?php echo htmlspecialchars($r['FUURL']); ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($rows) && !$err): ?><tr><td colspan="4">(no rows)</td></tr><?php endif; ?>
</tbody>
</table>
<script>
function filterRows(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#tb tr').forEach(function(tr) {
    tr.style.display = (!q || tr.dataset.desc.indexOf(q) >= 0) ? '' : 'none';
    tr.classList.toggle('hi', q.length > 1 && tr.dataset.desc.indexOf(q) >= 0);
  });
}
</script>
</body></html>
