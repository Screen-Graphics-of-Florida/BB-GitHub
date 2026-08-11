<?php
require_once dirname(__FILE__) . '/../../GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

date_default_timezone_set('America/New_York');

function ksr_trim($v) {
    return trim((string)$v);
}

function ksr_num($v, $default = 0) {
    if ($v === null || $v === '') {
        return (float)$default;
    }
    return (float)$v;
}

function ksr_sql_like($v) {
    $s = trim((string)$v);
    if ($s === '') {
        return '%';
    }
    $s = str_replace('*', '%', $s);
    $s = str_replace('?', '_', $s);
    return $s;
}

$itemLike = isset($_GET['item']) ? ksr_trim($_GET['item']) : '94-*';
$prodClass = isset($_GET['class']) ? strtoupper(ksr_trim($_GET['class'])) : '';
$export = isset($_GET['export']) && $_GET['export'] === '1';

$itemPattern = ksr_sql_like($itemLike);

$conn = $i5Connect->getConnection();

$classOptions = array();
$selectedClassColumn = null;

$colStmt = db2_exec($conn, "SELECT COLUMN_NAME FROM QSYS2.SYSCOLUMNS WHERE TABLE_SCHEMA = 'SGHDSDATA' AND TABLE_NAME = 'HDPCLS' ORDER BY ORDINAL_POSITION");
if ($colStmt) {
    $cols = array();
    while ($c = db2_fetch_assoc($colStmt)) {
        $cols[] = strtoupper(trim((string)$c['COLUMN_NAME']));
    }
    db2_free_stmt($colStmt);

    foreach ($cols as $colName) {
        if (strpos($colName, 'PCLS') !== false || strpos($colName, 'CLASS') !== false || strpos($colName, 'CLAS') !== false || strpos($colName, 'CODE') !== false) {
            $selectedClassColumn = $colName;
            break;
        }
    }
}

if ($selectedClassColumn !== null) {
    $classSql = "SELECT DISTINCT TRIM(CAST($selectedClassColumn AS CHAR(15))) AS CLASS_CODE FROM SGHDSDATA.HDPCLS WHERE TRIM(CAST($selectedClassColumn AS CHAR(15))) <> '' ORDER BY 1";
    $classStmt = db2_exec($conn, $classSql, array('cursor' => DB2_SCROLLABLE));
    if ($classStmt) {
        while ($c = db2_fetch_assoc($classStmt)) {
            $v = strtoupper(trim((string)$c['CLASS_CODE']));
            if ($v !== '') {
                $classOptions[] = $v;
            }
        }
        db2_free_stmt($classStmt);
    }
}

if (empty($classOptions)) {
    $fallbackSql = "SELECT DISTINCT TRIM(CAST(IMPCLS AS CHAR(15))) AS CLASS_CODE FROM SGHDSDATA.HDIMST WHERE TRIM(CAST(IMPCLS AS CHAR(15))) <> '' ORDER BY 1";
    $fallbackStmt = db2_exec($conn, $fallbackSql, array('cursor' => DB2_SCROLLABLE));
    if ($fallbackStmt) {
        while ($c = db2_fetch_assoc($fallbackStmt)) {
            $v = strtoupper(trim((string)$c['CLASS_CODE']));
            if ($v !== '') {
                $classOptions[] = $v;
            }
        }
        db2_free_stmt($fallbackStmt);
    }
}

if (!empty($classOptions)) {
    $classOptions = array_values(array_unique($classOptions));
    sort($classOptions, SORT_STRING);
    if ($prodClass === '' || !in_array($prodClass, $classOptions, true)) {
        $prodClass = in_array('KITS', $classOptions, true) ? 'KITS' : $classOptions[0];
    }
} else {
    $prodClass = '';
}

$sql = "
    SELECT
        TRIM(CAST(p.IMITEM AS CHAR(30))) AS PARENT_ITEM,
        TRIM(CAST(p.IMDSC1 AS CHAR(60))) AS PARENT_DESC,
        TRIM(CAST(p.IMPCLS AS CHAR(15))) AS PARENT_CLASS,
        TRIM(CAST(b.HDPRNT AS CHAR(30))) AS BOM_PARENT,
        TRIM(CAST(b.HDCHIL AS CHAR(30))) AS CHILD_ITEM,
        TRIM(CAST(c.IMDSC1 AS CHAR(60))) AS CHILD_DESC,
        COALESCE(CAST(b.HDQTY AS DECIMAL(15,4)), 0) AS BOM_QTY,
        COALESCE(CAST(w.IWOHQT AS DECIMAL(15,4)), 0) AS ON_HAND_QTY,
        COALESCE(CAST(w.IWQSYT AS DECIMAL(15,4)), 0) AS SOLD_YTD_QTY,
        COALESCE(CAST(w.IWQITY AS DECIMAL(15,4)), 0) AS ISSUED_YTD_QTY,
        COALESCE(CAST(pl.IPQMFG AS DECIMAL(15,4)), 0) AS MFG_YTD_QTY,
        COALESCE(CAST(pl.IPCMTO AS DECIMAL(15,4)), 0) AS COMMITTED_TO_MO_QTY
    FROM SGHDSDATA.HDIMST p
    INNER JOIN SGHDSDATA.HDMPSM b
        ON TRIM(CAST(b.HDPRNT AS CHAR(30))) = TRIM(CAST(p.IMITEM AS CHAR(30)))
    LEFT JOIN SGHDSDATA.HDIMST c
        ON TRIM(CAST(c.IMITEM AS CHAR(30))) = TRIM(CAST(b.HDCHIL AS CHAR(30)))
    LEFT JOIN SGHDSDATA.HDIWHS w
        ON TRIM(CAST(w.IWITEM AS CHAR(30))) = TRIM(CAST(b.HDCHIL AS CHAR(30)))
    LEFT JOIN SGHDSDATA.HDIPLT pl
        ON TRIM(CAST(pl.IPITEM AS CHAR(30))) = TRIM(CAST(b.HDCHIL AS CHAR(30)))
    WHERE TRIM(CAST(p.IMITEM AS CHAR(30))) LIKE '$itemPattern'
";

if ($prodClass !== '') {
    $sql .= "      AND TRIM(CAST(p.IMPCLS AS CHAR(15))) = '$prodClass'\n";
}

$sql .= "    ORDER BY TRIM(CAST(p.IMITEM AS CHAR(30))), TRIM(CAST(b.HDCHIL AS CHAR(30)))\n";

$rows = array();
$stmt = db2_exec($conn, $sql, array('cursor' => DB2_SCROLLABLE));
if ($stmt) {
    while ($r = db2_fetch_assoc($stmt)) {
        $rows[] = array(
            'PARENT_ITEM' => ksr_trim($r['PARENT_ITEM']),
            'PARENT_DESC' => ksr_trim($r['PARENT_DESC']),
            'PARENT_CLASS' => ksr_trim($r['PARENT_CLASS']),
            'BOM_PARENT' => ksr_trim($r['BOM_PARENT']),
            'CHILD_ITEM' => ksr_trim($r['CHILD_ITEM']),
            'CHILD_DESC' => ksr_trim($r['CHILD_DESC']),
            'BOM_QTY' => ksr_num($r['BOM_QTY']),
            'ON_HAND_QTY' => ksr_num($r['ON_HAND_QTY']),
            'SOLD_YTD_QTY' => ksr_num($r['SOLD_YTD_QTY']),
            'ISSUED_YTD_QTY' => ksr_num($r['ISSUED_YTD_QTY']),
            'MFG_YTD_QTY' => ksr_num($r['MFG_YTD_QTY']),
            'COMMITTED_TO_MO_QTY' => ksr_num($r['COMMITTED_TO_MO_QTY'])
        );
    }
    db2_free_stmt($stmt);
} else {
    $error = db2_stmt_errormsg();
}

if ($export) {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="KitsStructureReport_' . date('Ymd_His') . '.csv"');
    echo "Parent Item,Parent Description,Parent Class,Child Item,Child Description,BOM Qty,Qty On Hand,Qty Sold YTD,Qty Issued YTD,Qty Mfg YTD,Qty Committed To MO\r\n";
    foreach ($rows as $r) {
        echo sprintf(
            "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\r\n",
            str_replace('"', '""', $r['PARENT_ITEM']),
            str_replace('"', '""', $r['PARENT_DESC']),
            str_replace('"', '""', $r['PARENT_CLASS']),
            str_replace('"', '""', $r['CHILD_ITEM']),
            str_replace('"', '""', $r['CHILD_DESC']),
            number_format($r['BOM_QTY'], 4, '.', ''),
            number_format($r['ON_HAND_QTY'], 4, '.', ''),
            number_format($r['SOLD_YTD_QTY'], 4, '.', ''),
            number_format($r['ISSUED_YTD_QTY'], 4, '.', ''),
            number_format($r['MFG_YTD_QTY'], 4, '.', ''),
            number_format($r['COMMITTED_TO_MO_QTY'], 4, '.', '')
        );
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kits Structure Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f5f7fb; color: #1a1a1a; }
        .wrap { max-width: 1500px; margin: 0 auto; }
        .toolbar { background: #fff; border: 1px solid #d6dbe4; border-radius: 8px; padding: 16px; margin-bottom: 18px; }
        .toolbar h1 { margin: 0 0 14px; font-size: 22px; }
        form { display: flex; flex-wrap: wrap; gap: 12px; align-items: end; }
        label { display: flex; flex-direction: column; font-size: 12px; font-weight: 700; color: #445; }
        input, button { padding: 8px 10px; font-size: 13px; }
        input { min-width: 180px; }
        button { background: #0d5fd3; border: 0; color: #fff; border-radius: 6px; cursor: pointer; }
        .meta { margin-top: 8px; font-size: 12px; color: #5b6472; }
        .err { background: #fff1f0; border: 1px solid #f3b1aa; color: #8a1f1f; padding: 12px; border-radius: 6px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #d6dbe4; }
        th, td { border-bottom: 1px solid #e5e7ec; padding: 7px 8px; font-size: 12px; text-align: left; vertical-align: top; }
        th { background: #eef3ff; position: sticky; top: 0; }
        tr:nth-child(even) td { background: #fafcff; }
        .muted { color: #5b6472; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="toolbar">
            <h1>Kits Structure Report</h1>
            <form method="get">
                <label>
                    Item pattern
                    <input type="text" name="item" value="<?php echo htmlspecialchars($itemLike, ENT_QUOTES, 'UTF-8'); ?>" placeholder="94-*">
                </label>
                <label>
                    Product class
                    <select name="class">
                        <option value="">All Classes</option>
                        <?php foreach ($classOptions as $classValue): ?>
                            <option value="<?php echo htmlspecialchars($classValue, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($prodClass === $classValue) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($classValue, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit">Run report</button>
                <button type="button" onclick="location.href='?item=<?php echo rawurlencode($itemLike); ?>&class=<?php echo rawurlencode($prodClass); ?>&export=1';">Export to Excel</button>
            </form>
            <div class="meta">Product classes are loaded from SGHDSDATA.HDPCLS when available, with HDIMST used as a fallback for live data.</div>
        </div>

        <?php if (isset($error)): ?>
            <div class="err">
                <strong>Query error:</strong> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?><br>
                <span class="muted">If the table/field names differ in your environment, update the aliases in this report to match the live IBM i file definitions.</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($rows)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Parent Item</th>
                        <th>Parent Description</th>
                        <th>Parent Class</th>
                        <th>Child Item</th>
                        <th>Child Description</th>
                        <th>BOM Qty</th>
                        <th>Qty On Hand</th>
                        <th>Qty Sold YTD</th>
                        <th>Qty Issued YTD</th>
                        <th>Qty Mfg YTD</th>
                        <th>Qty Committed To MO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['PARENT_ITEM'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($r['PARENT_DESC'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($r['PARENT_CLASS'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($r['CHILD_ITEM'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($r['CHILD_DESC'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo number_format($r['BOM_QTY'], 4, '.', ','); ?></td>
                            <td><?php echo number_format($r['ON_HAND_QTY'], 4, '.', ','); ?></td>
                            <td><?php echo number_format($r['SOLD_YTD_QTY'], 4, '.', ','); ?></td>
                            <td><?php echo number_format($r['ISSUED_YTD_QTY'], 4, '.', ','); ?></td>
                            <td><?php echo number_format($r['MFG_YTD_QTY'], 4, '.', ','); ?></td>
                            <td><?php echo number_format($r['COMMITTED_TO_MO_QTY'], 4, '.', ','); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="meta">No matching kit records were found for this item/class filter.</div>
        <?php endif; ?>
    </div>
</body>
</html>
