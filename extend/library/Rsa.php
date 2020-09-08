<?php
namespace library;
class Rsa {

	private $pubkey = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDLhP8NHtmoB4FTRsaxi3u/VxwS
Wf46u0b9owctCCVxIFT5qktNVgsbnqm9BIRMsiy74Z+aZm2Ix0tGr7Nmls5rbrEx
1ffILzvvj465iaXZXntwW1usMpN8W2nFj4h94+wdUhKNnvzF94ppFaqoLMcDQ+wm
6DaTCK74x3q1OcwdkwIDAQAB
-----END PUBLIC KEY-----";
	
	
	private $privkey = "-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDLhP8NHtmoB4FTRsaxi3u/VxwSWf46u0b9owctCCVxIFT5qktN
Vgsbnqm9BIRMsiy74Z+aZm2Ix0tGr7Nmls5rbrEx1ffILzvvj465iaXZXntwW1us
MpN8W2nFj4h94+wdUhKNnvzF94ppFaqoLMcDQ+wm6DaTCK74x3q1OcwdkwIDAQAB
AoGANFXmFKSrShdr/VNFAyEgWGr5MAruYoIIVm8XCFCA+F9dGrrdFG0c/CRH9/By
aRqkgEgb9+OBJVxCklHaa3Y6bFYXPF8K4e3xfeXYK6BrtmQHTjQ0AFrnr7P5R04a
JOLoapBZCDk0QWi5UDxGFqIS36H1Rbiyv8/Lyb4HAcH06lECQQD8LMnGshArfUGD
a6zJh3JSvhlz/x9B/CJpBsJ7izVrzK6qBXx2Uvu72E3uTOPnWg/HONMxLHUU3kcm
3nmbTNLbAkEAzptGnzIEf/LQoV04lJA5sSKEhv3wCrudwsvVzjqydylEsIiGKkgE
RnB8MeNdnRCwwl00HrCrZR/ysJBTufsxqQJBAN7cukpWzcGRhheeTmsgwCFuDdKc
8sP/D24gYjqLEeA+WVK7XH654e3mQSFMZNHunykjCEpaDvMtalZoobJlV6cCQG21
9LV6iPsshG77opz5TTlru/Y3CG2anTagqQNfFf/C8U/Q33W6UlLZTonrU32hLEu5
1IBrjkgfw+7QZTWAVykCQBt9bW/CQGG7rfqbe0IQYuVVLbZ2Xors4hM4PXrm3C5R
j6fYb5D3z+MOV/bvLV8CFy+OzAlRz887tNAhXl0Eog4=
-----END RSA PRIVATE KEY-----";

	//前端需要用到(给到前端)
    private $_js_openssl_modulus = "CB84FF0D1ED9A807815346C6B18B7BBF571C1259FE3ABB46FDA3072D0825712054F9AA4B4D560B1B9EA9BD04844CB22CBBE19F9A666D88C74B46AFB36696CE6B6EB131D5F7C82F3BEF8F8EB989A5D95E7B705B5BAC32937C5B69C58F887DE3EC1D52128D9EFCC5F78A6915AAA82CC70343EC26E8369308AEF8C77AB539CC1D93";

	function __construct($myPrivateKey = '', $myPublicKey = '') {

		if(isset($myPrivateKey) && !empty($myPrivateKey)){
			$this->privkey = $myPrivateKey;
		}

		if(isset($myPublicKey) && !empty($myPublicKey)){
			$this->pubkey = $myPublicKey;
		}
		 
	}
	
	/**
	 * Des: RSA加密
	 */
	public function encrypt($data) {
		if (openssl_public_encrypt($data, $encrypted, $this->pubkey))
			$data = base64_encode($encrypted);
		else
			$data = '';

		return $data;
	}
	
	/**
	 * DES: RSA解密
	 */
	public function decrypt($data) {
		if (openssl_private_decrypt(base64_decode($data), $decrypted, $this->privkey))
			$data = $decrypted;
		else
			$data = '';

		return trim($data);
	}
	
	/**
	 * Des: Ios RSA加密
	 */
	public function iosEncrypt($data) {

		if (openssl_public_encrypt($data, $encrypted, $this->pubkey))
			$data = base64_encode($encrypted);
		else
			$data = '';

		return $data;
	}
	
	/**
	 * DES: Ios RSA解密
	 */
	public function iosDecrypt($data) {
		if (openssl_private_decrypt(base64_decode($data), $decrypted, $this->privkey,OPENSSL_PKCS1_PADDING))
			$data = $decrypted;
		else
			$data = '';

		return trim($data);
	}

    /**
     * @param $str
     * @return string
     * @desc:RSA不定长的问题
     */
    public function strPad($str){
        $str = str_pad($str,128);
        return $str;
    }

    //js rsa 解密(16进制)
    public function jsDeCrypt($hex_encrypt_data) {
        $encrypt_data = pack("H*", $hex_encrypt_data);//对十六进制数据进行转换
        openssl_private_decrypt($encrypt_data, $decrypt_data, $this->privkey);
        return $decrypt_data;
    }
	
}

