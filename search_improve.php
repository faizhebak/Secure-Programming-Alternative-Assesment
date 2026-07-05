<?php
// search_improve.php - Refactored Patient & Medical Record Search Proxy
require_once 'db_config.php'; // provides a PDO connection named $pdo, using a least-privilege DB user

$keyword = $_GET['keyword'] ?? '';

// FIX for Flaw A: Parameterized query via PDO prepared statement
$stmt = $pdo->prepare(
    "SELECT id, name, illness_history FROM patient_records WHERE name LIKE :keyword"
);
$stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) > 0) {
    foreach ($rows as $row) {
        // FIX for Flaws B & C: Context-aware output encoding
        echo "<div>Result found for keyword: " . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . "<br>";
        echo "Patient: " . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8')
            . " | History: " . htmlspecialchars($row['illness_history'], ENT_QUOTES, 'UTF-8')
            . "</div><hr>";
    }
} else {
    echo "No records found for: " . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
}
?>