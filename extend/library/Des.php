<?php
namespace library;

class Des
{
    var $key;
    var $iv; // 偏移量

    function __construct($key="1234abcd", $iv = null)
    {
        // key长度8例如:1234abcd
        $this->key = $key;
        if ($iv == null) {
            $this->iv = $key; // 默认以$key 作为 iv
        } else {
            $this->iv = $iv;
        }
    }

    public function encrypt($input)
    {
        $data = openssl_encrypt($input, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, bin2hex($this->iv));
        $data = base64_encode($data);
        return $data;
    }

    public function decrypt($input)
    {
        $decrypted = openssl_decrypt(base64_decode($input), 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, bin2hex($this->iv));
        return $decrypted;
    }




}