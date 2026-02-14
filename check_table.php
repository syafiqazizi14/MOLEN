<?php
require 'vendor/autoload.php';

$con = new mysqli('localhost', 'root', '', 'bps');

echo "=== PLACEMENTS TABLE STRUCTURE ===\n";
$result = $con->query('DESCRIBE placements');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' => ' . $row['Type'] . "\n";
}

echo "\n=== SAMPLE DATA (2026) ===\n";
$result = $con->query('SELECT id, mitra_id, year, month, survey_1 FROM placements WHERE CAST(year AS UNSIGNED) = 2026 LIMIT 5');
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== SAMPLE DATA (2025) ===\n";
$result = $con->query('SELECT id, mitra_id, year, month, survey_1 FROM placements WHERE CAST(year AS UNSIGNED) = 2025 LIMIT 5');
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== ALL DISTINCT YEARS ===\n";
$result = $con->query('SELECT DISTINCT year FROM placements ORDER BY year');
while($row = $result->fetch_assoc()) {
    echo "Year: " . json_encode($row['year']) . "\n";
}

$con->close();
?>