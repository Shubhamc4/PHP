<?php

/**
 * PHP Encrypter
 *
 * @author Shubham Chaudhary
 */

/**
 * Encrypter
 *
 * This class encrypts and decrypts the given value. It uses OpenSSL extension
 * with AES-256 cipher for encryption and HMAC-SHA-256 for hash.
 * The encryption and hash can use different keys.
 */
final class Encrypter
{
    /**
     * Create a new encrypter instance
     * @throws \RuntimeException
     */
    public function __construct(
        protected string $key,
        protected string|null $authKey = null
    ) {
        if (!extension_loaded('openssl')) {
            throw new \RuntimeException('OpenSSL extension is not available.');
        }

        if (!extension_loaded('mbstring')) {
            throw new \RuntimeException('Multibyte String extension is not available.');
        }

        if (!self::isValidKey($key)) {
            throw new \RuntimeException('The encryption key length is not valid.');
        }

        if (is_null($authKey)) {
            $this->authKey = $key;
        } elseif (!self::isValidKey($authKey)) {
            throw new \RuntimeException('The authentication key length is not valid.');
        }
    }

    /**
     * Encrypt the given value
     */
    public function encrypt(mixed $value, bool $serialize = true): string
    {
        $iv = random_bytes(16);

        // Encrypt the given value
        $encrypted = openssl_encrypt(
            $serialize ? serialize($value) : $value,
            'AES-256-CBC',
            $this->key,
            0,
            $iv
        );

        if ($encrypted !== false) {
            $hmac = $this->hash($iv . $encrypted);

            return base64_encode($iv . $hmac . $encrypted);
        }
    }

    /**
     * Encrypt the given string without serialization
     */
    public function encryptString(string $value): string
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt the given value
     */
    public function decrypt(string $value, bool $unserialize = true): mixed
    {
        $value = base64_decode($value);

        $iv         = mb_substr($value, 0, 16, '8bit');
        $hmac       = mb_substr($value, 16, 32, '8bit');
        $encrypted  = mb_substr($value, 48, null, '8bit');

        $hmacNew = $this->hash($iv . $encrypted);

        if (self::hashEquals($hmac, $hmacNew)) {
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $this->key, 0, $iv);

            if ($decrypted !== false) {
                return $unserialize ? unserialize($decrypted) : $decrypted;
            }
        }
    }

    /**
     * Decrypt the given string without unserialization
     */
    public function decryptString(string $value): string
    {
        return $this->decrypt($value, false);
    }

    /**
     * Create a keyed hash for the given value
     */
    protected function hash(string $value): string
    {
        return hash_hmac('sha256', $value, $this->authKey, true);
    }

    /**
     * Compare hashes
     *
     * @param  string $original Original hash
     * @param  string $new      New hash
     * @return bool
     */
    protected static function hashEquals(string $original, string $new): bool
    {
        // PHP version >= 5.6
        if (function_exists('hash_equals')) {
            return hash_equals($original, $new);
        }

        // PHP version < 5.6
        if (!is_string($original) || !is_string($new)) {
            return false;
        }

        if ($originalLength = mb_strlen($original) !== mb_strlen($new)) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $originalLength; ++$i) {
            $result |= ord($original[$i]) ^ ord($new[$i]);
        }

        return $result === 0;
    }

    /**
     * Validate the given key
     */
    protected static function isValidKey(string $key): bool
    {
        return is_string($key) && mb_strlen($key, '8bit') === 32;
    }
}
