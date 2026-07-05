<?php
// CryptoVaultTest.php - Standalone test runner for the refactored crypto vault
// Run with: php CryptoVaultTest.php

require_once 'crypto_vault_improve.php';

$passed = 0;
$failed = 0;

function report(string $label, bool $ok): void
{
    global $passed, $failed;
    if ($ok) {
        echo "PASS: $label\n";
        $passed++;
    } else {
        echo "FAIL: $label\n";
        $failed++;
    }
}

$testKey = random_bytes(32);

// Test 1: untampered encrypt and decrypt round trip
$plaintext1 = 'DIAGNOSIS: Stage-2 Carcinoma. TREATMENT: Chemotherapy cycle 1.';
$packed1 = encryptVaultPayload($plaintext1, $testKey);
$recovered1 = decryptVaultPayload($packed1, $testKey);
report('Encrypt/decrypt round trip succeeds', $recovered1 === $plaintext1);

// Test 2: tampered ciphertext throws a RuntimeException
$plaintext2 = 'DIAGNOSIS: Acute Type-2 Diabetes. TREATMENT: Insulin regimen.';
$iv = random_bytes(12);
$tag = '';
$ciphertext = openssl_encrypt($plaintext2, 'aes-256-gcm', $testKey, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
$tampered = $ciphertext;
$tampered[0] = chr(ord($tampered[0]) ^ 0xFF);
$packed2 = base64_encode($iv . $tag . $tampered);

$threw = false;
try {
    decryptVaultPayload($packed2, $testKey);
} catch (RuntimeException $e) {
    $threw = true;
}
report('Tampered ciphertext throws RuntimeException', $threw);

// Test 3: credential hash integrity with Argon2id
$plaintextKey = 'testkey123';
$hash = password_hash($plaintextKey, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost'   => 4,
    'threads'     => 2,
]);
report('Password hash integrity matches', password_verify($plaintextKey, $hash) && !password_verify('wrongkey', $hash));

echo "\n$passed passed, $failed failed\n";
?>