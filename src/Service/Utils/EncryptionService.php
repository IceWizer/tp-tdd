<?php

namespace App\Service\Utils;

class EncryptionService
{
    private string $encryptionKey;
    public static string $cypherAlgorithm = 'aes-256-cbc';

    public function __construct(string $encryptionKey)
    {
        $this->encryptionKey = bin2hex(base64_decode($encryptionKey));
    }

    public function encrypt(string $data, string $dynamicSalt = ""): string
    {
        $ivLength = $this->getIvLength();
        if ($ivLength === false) {
            throw new \Exception('Invalid cypher algorithm');
        }
        $iv = openssl_random_pseudo_bytes($ivLength);
        $key = bin2hex(hash('sha256', $this->encryptionKey . $dynamicSalt, true));
        $encrypted = openssl_encrypt($data, EncryptionService::$cypherAlgorithm, $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }


    public function decrypt(string $data, string $dynamicSalt = ""): string|false
    {
        if (empty($data)) {
            throw new \Exception('Data is empty');
        }

        $data = base64_decode($data, true);
        if ($data === false) {
            throw new \Exception('Data is not base64 encoded');
        }

        $ivLength = $this->getIvLength();
        if ($ivLength === false) {
            throw new \Exception('Invalid cypher algorithm');
        }

        if (strlen($data) < $ivLength) {
            throw new \Exception('Invalid data');
        }

        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        $key = bin2hex(hash('sha256', $this->encryptionKey . $dynamicSalt, true));
        return openssl_decrypt($encrypted, EncryptionService::$cypherAlgorithm, $key, 0, $iv);
    }

    public function getIvLength(): int|false
    {
        if (in_array(EncryptionService::$cypherAlgorithm, openssl_get_cipher_methods(true))) {
            return openssl_cipher_iv_length(EncryptionService::$cypherAlgorithm);
        }
        return false;
    }
}
