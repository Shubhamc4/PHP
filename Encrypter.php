<?php

/**
 * @author Shubham Chaudhary
 */
class Encrypter
{
    function encrypt(string $payload, string $password): string
    {
        $salted = "Shubham_";
        $salt = openssl_random_pseudo_bytes(8);
        $hash1 = hex2bin(sha1($password . $salt));
        $hash2 = hex2bin(sha1($hash1 . $password . $salt));
        $key = $hash1 . $hash2;
        $iv = substr(hex2bin(sha1($hash2 . $password . $salt)), 0, 16);
        $encrypt = openssl_encrypt($payload, "AES-256-CTR", $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($salted . $salt . $encrypt);
    }

    function decrypt(string $payload, string $password): string
    {
        $payload = base64_decode($payload);
        $salt = substr($payload, 8, 8);
        $encoded = substr($payload, 16);
        $hash1 = hex2bin(sha1($password . $salt));
        $hash2 = hex2bin(sha1($hash1 . $password . $salt));
        $key = $hash1 . $hash2;
        $iv = substr(hex2bin(sha1($hash2 . $password . $salt)), 0, 16);
        return openssl_decrypt($encoded, "AES-256-CTR", $key, OPENSSL_RAW_DATA, $iv);
    }
}
