<?php
/**
 * Created by yang
 * User: bonzaphp@gmail.com
 * Date: 2023/7/1
 * Time: 10:19
 */
namespace Bonza\Hfpay\Kernel\Encryption;

use function Bonza\Hfpay\str_random;

class Encryptor
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $aesKey;

    /**
     * @var int
     */
    protected $blockSize = 32;

    /**
     * Encryptor Constructor.
     *
     * @param  string  $key
     * @param  string  $token
     * @param  string  $aesKey
     */
    public function __construct($key, $token, $aesKey)
    {
        $this->key = $key;
        $this->token = $token;
        $this->aesKey = base64_decode($aesKey.'=', true);
    }

    /**
     * Encrypt the data.
     *
     * @param  string  $data
     * @param  string|null  $nonce
     * @param  int|null  $timestamp
     *
     * @return string
     * @throws \JsonException
     * @throws \Exception
     */
    public function encrypt($data, string $nonce = null, int $timestamp = null): string
    {
        $string = str_random().pack('N', strlen($data)).$data.$this->key;
        $encryptMethod = 'AES-256-CBC';
        $ivLength = openssl_cipher_iv_length($encryptMethod);
        $iv = random_bytes($ivLength);
        if (false === $ivLength) {
            die('IV generate failed');
        }
        $result = base64_encode(
//            openssl_encrypt($this->pkcs7Pad($string), $encryptMethod, $this->aesKey, OPENSSL_NO_PADDING, substr($this->aesKey, 0, 16))
            openssl_encrypt($this->pkcs7Pad($string), $encryptMethod, $this->aesKey, OPENSSL_NO_PADDING, $iv)
        );
        !is_null($nonce) || $nonce = uniqid('',false);
        !is_null($timestamp) || $timestamp = time();
        return json_encode([
            'msg_signature' => $this->signature($this->token, $nonce, $timestamp, $result),
            'timeStamp'     => $timestamp,
            'nonce'         => $nonce,
            'encrypt'       => $result,
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Decrypt the data.
     *
     * @param  string  $data
     * @param  string  $signature
     * @param  string  $nonce
     * @param  int  $timestamp
     *
     * @return string
     */
    public function decrypt(string $data, string $signature, string $nonce, $timestamp): string
    {
        if ($signature !== $this->signature($this->token, $nonce, $timestamp, $data)) {
            throw new \RuntimeException('Invalid Signature.');
        }
        $decrypted = openssl_decrypt(
            base64_decode($data, true), 'AES-256-CBC', $this->aesKey, OPENSSL_NO_PADDING, substr($this->aesKey, 0, 16)
        );
        $result = $this->pkcs7Unpad($decrypted);
        $data = substr($result, 16, strlen($result));
        $contentLen = unpack('N', substr($data, 0, 4))[1];
        if (substr($data, $contentLen + 4) !== $this->key) {
            throw new \RuntimeException('Invalid CorpId.');
        }
        return substr($data, 4, $contentLen);
    }

    /**
     * Get SHA1.
     *
     * @return string
     */
    public function signature(): string
    {
        $array = func_get_args();
        sort($array, SORT_STRING);
        return sha1(implode($array));
    }

    /**
     * PKCS#7 pad.
     *
     * @param  string  $text
     *
     * @return string
     */
    public function pkcs7Pad(string $text): string
    {
        $padding = $this->blockSize - (strlen($text) % $this->blockSize);
        $pattern = chr($padding);
        return $text.str_repeat($pattern, $padding);
    }

    /**
     * PKCS#7 unpad.
     *
     * @param  string  $text
     *
     * @return string
     */
    public function pkcs7Unpad(string $text): string
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > $this->blockSize) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}
