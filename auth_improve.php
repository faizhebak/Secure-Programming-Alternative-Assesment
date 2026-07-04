<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputKey = $_POST['auth_key'] ?? '';
 

    if (mb_strlen($inputKey, 'UTF-8') > 256) {
        http_response_code(400);
        die("Error: Input exceeds maximum allowed length.");
    }
 

    $stored_hash = getStoredHashForUser(); 
    if (password_verify($inputKey, $stored_hash)) {
        echo "Access Granted.";
    } else {
        http_response_code(401);
        echo "Access Denied.";
    }
}
?>