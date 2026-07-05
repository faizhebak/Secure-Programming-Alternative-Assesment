<?php
// crypto_vault.php - Patient Medical Records Symmetric Protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medical_payload = $_POST['payload'];

    // Hidden Flaw F: Insecure Symmetric Block Cipher Mode (AES-128-ECB)
    // Hidden Flaw G: Cryptographic Key Hardcoding
    $secret_key = "MedVaultKey123!";

    $encrypted = openssl_encrypt($medical_payload, 'aes-128-ecb', $secret_key);

    echo json_encode(["status" => "vaulted", "data" => $encrypted]);
}
?>