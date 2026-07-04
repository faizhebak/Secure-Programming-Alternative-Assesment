<?php

require_once 'db_config.php'; 
 
$keyword = $_GET['keyword'] ?? '';
 

$stmt = $pdo->prepare(
    "SELECT id, name, illness_history FROM patient_records WHERE name LIKE :keyword"
);
$stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
if (count($rows) > 0) {
    foreach ($rows as $row) {
        
        echo "<div>Result found for keyword: " . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . "<br>";
        echo "Patient: " . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8')
            . " | History: " . htmlspecialchars($row['illness_history'], ENT_QUOTES, 'UTF-8')
            . "</div><hr>";
    }
} else {
    echo "No records found for: " . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
}
?>