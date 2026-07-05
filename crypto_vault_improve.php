<?php
// crypto_vault_improve.php - Refactored Patient Medical Records Symmetric Protection
// Runs standalone with plain PHP. The key is read from the environment,
// with a documented fallback for local testing.

/**
 * Loads the 32-byte AES-256 key from the VAULT_KEY environment variable.
 * VAULT_KEY must be a base64-encoded 32-byte value.
 * A local development fallback is provided so the file runs standalone.
 */
function getVaultKey(): string
{
    $b64 = getenv('VAULT_KEY');

    if ($b64 === false || $b64 === '') {
        // Local development fallback only. In production the key comes from the environment.
        $key = hash('sha256', 'local-development-key-do-not-use-in-production', true);
        return $key;
    }

    $key = base64_decode($b64, true);
    if ($key === false || strlen($key) !== 32) {
        http_response_code(500);
        die("Configuration error, the encryption key is missing or invalid.");
    }
    return $key;
}

/**
 * Encrypts a payload with AES-256-GCM and packs the IV, tag, and ciphertext
 * into a single base64 string.
 */
function encryptVaultPayload(string $plaintext, string $secret_key): string
{
    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt(
        $plaintext,
        'aes-256-gcm',
        $secret_key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($ciphertext === false) {
        throw new RuntimeException('Encryption failed.');
    }

    return base64_encode($iv . $tag . $ciphertext);
}

/**
 * Reverses encryptVaultPayload. Verifies the authentication tag before
 * returning plaintext, and throws a typed exception on any tamper or malformed input.
 */
function decryptVaultPayload(string $packedBase64, string $secret_key): string
{
    $raw = base64_decode($packedBase64, true);
    if ($raw === false || strlen($raw) < 28) {
        throw new InvalidArgumentException('Malformed vault payload.');
    }

    $iv = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $ciphertext = substr($raw, 28);

    $plaintext = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $secret_key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($plaintext === false) {
        throw new RuntimeException('Authentication tag verification failed, the payload may have been tampered with.');
    }

    return $plaintext;
}

// Request handling block only runs when this file is called directly via POST.
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $medical_payload = $_POST['payload'] ?? '';
    $secret_key = getVaultKey();

    try {
        $packed = encryptVaultPayload($medical_payload, $secret_key);
        echo json_encode(["status" => "vaulted", "data" => $packed]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Encryption operation failed."]);
    }
}
?>