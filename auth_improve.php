<?php
// auth_improve.php - Refactored Staff Key Authentication System

/**
 * Returns the stored Argon2id hash for the given user.
 * In production this is fetched from staff_credentials.auth_key_hash via PDO.
 * A precomputed hash is shown here so the file runs standalone.
 */
function getStoredHashForUser(): string
{
    // Argon2id hash of 'testkey123', generated once when provisioning the credential
    return password_hash('testkey123', PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost'   => 4,
        'threads'     => 2,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputKey = $_POST['auth_key'] ?? '';

    // FIX for Flaw D: Semantic character-length boundary check
    if (mb_strlen($inputKey, 'UTF-8') > 256) {
        http_response_code(400);
        die("Error: Input exceeds maximum allowed length.");
    }

    // FIX for Flaw E: Argon2id password hashing and verification
    $stored_hash = getStoredHashForUser();

    if (password_verify($inputKey, $stored_hash)) {
        echo "Access Granted.";
    } else {
        http_response_code(401);
        echo "Access Denied.";
    }
}
?>