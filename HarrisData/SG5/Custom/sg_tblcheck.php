<?php
require_once 'GetURLParm.php';
require_once 'GenericDirectCallVariables.php';
require_once 'SetLibraryList.php';

$conn = $i5Connect->getConnection();

// Find the Order History / Order list page in EIP table definitions
// Look in both S5HDSDATA (test) and SGHDSDATA (live) schemas
$queries = array(
    'SYSURLM eID L028' => "SELECT FUID, FUDESC, FUTITL, FUURL FROM S5HDSDATA.SYURLM WHERE FUID LIKE '%L028%' OR FUDESC LIKE '%order%' OR FUDESC LIKE '%Order%' FETCH FIRST 20 ROWS ONLY",
    'HDPAG (page master)' => "SELECT * FROM SGHDSDATA.HDPAG WHERE PGDESC LIKE '%order%' OR PGDESC LIKE '%Order%' OR PGDESC LIKE '%hist%' FETCH FIRST 20 ROWS ONLY",
    'HDTBL tblID 205' => "SELECT * FROM SGHDSDATA.HDTBL WHERE TBTBL# = 205 FETCH FIRST 5 ROWS ONLY",
    'HDTBL order history' => "SELECT TBTBL#, TBDESC FROM SGHDSDATA.HDTBL WHERE TBDESC LIKE '%order%' OR TBDESC LIKE '%Order%' OR TBDESC LIKE '%hist%' FETCH FIRST 20 ROWS ONLY",
);

foreach ($queries as $label => $sql) {
    echo "<h3>" . htmlspecialchars($label) . "</h3><pre>";
    $stmt = db2_exec($conn, $sql);
    if ($stmt) {
        $count = 0;
        while ($r = db2_fetch_assoc($stmt)) {
            echo htmlspecialchars(print_r($r, true));
            $count++;
        }
        if ($count === 0) echo "(no rows)\n";
        db2_free_stmt($stmt);
    } else {
        echo "Error: " . htmlspecialchars(db2_stmt_errormsg()) . "\n";
    }
    echo "</pre>";
}
