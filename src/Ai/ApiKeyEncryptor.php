<?php

namespace Sovic\Cms\Ai;

use SodiumException;
use Sovic\Cms\Ai\Exception\ApiKeyDecryptException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Symmetric encryption of per-user API keys, the key is derived from kernel.secret.
 * Rotating kernel.secret makes stored keys unreadable, users have to enter them again.
 */
final class ApiKeyEncryptor
{
    private string $key;

    public function __construct(
        #[Autowire('%kernel.secret%')] string $secret,
    ) {
        $this->key = sodium_crypto_generichash($secret, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    }

    public function encrypt(string $plain): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($plain, $nonce, $this->key);

        return base64_encode($nonce . $cipher);
    }

    public function decrypt(string $encoded): string
    {
        $decoded = base64_decode($encoded, true);
        if ($decoded === false || strlen($decoded) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new ApiKeyDecryptException('Invalid encrypted API key.');
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        try {
            $plain = sodium_crypto_secretbox_open($cipher, $nonce, $this->key);
        } catch (SodiumException $e) {
            throw new ApiKeyDecryptException('Unable to decrypt API key.', 0, $e);
        }
        if ($plain === false) {
            throw new ApiKeyDecryptException('Unable to decrypt API key.');
        }

        return $plain;
    }

    /**
     * Last 4 characters of the key, safe to display.
     */
    public static function hint(string $plain): string
    {
        return mb_substr($plain, -4);
    }
}
