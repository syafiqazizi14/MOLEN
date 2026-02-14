<?php
require 'vendor/autoload.php';

$con = new mysqli('localhost', 'root', '', 'bps');

echo "=== JUMLAH DATA PER BULAN (TAHUN 2026) ===\n";
$result = $con->query('SELECT month, COUNT(*) as total FROM placements WHERE year = 2026 GROUP BY month ORDER BY month');
while($row = $result->fetch_assoc()) {
    $monthName = date('F', mktime(0, 0, 0, $row['month'], 1));
    echo "Bulan " . $row['month'] . " ($monthName): " . $row['total'] . " records\n";
}

echo "\n=== DETAIL DATA UNTUK SETIAP BULAN (SAMPLE) ===\n";
for($m = 1; $m <= 12; $m++) {
    $result = $con->query("SELECT id, mitra_id, month, survey_1, survey_2, survey_3 FROM placements WHERE year = 2026 AND month = $m LIMIT 2");
    $count = $result->num_rows;
    $monthName = date('F', mktime(0, 0, 0, $m, 1));
    echo "\nBulan $m ($monthName): $count records\n";
    if($count > 0) {
        while($row = $result->fetch_assoc()) {
            echo "  - ID: {$row['id']}, Mitra: {$row['mitra_id']}, Surveys: {$row['survey_1']} | {$row['survey_2']} | {$row['survey_3']}\n";
        }
    }
}

$con->close();
?>