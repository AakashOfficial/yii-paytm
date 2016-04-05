<?php

class Paytm extends CComponent {

    public $api_live = false;
    public $merchant_key;
    public $merchant_id;
    public $website;
    public $industry_type_id;
    public $channel_id = 'WEB';
    public $refund_url;
    public $status_query_url;
    public $txn_url;

    public function init() {
        $domain = 'pguat.paytm.com';

        if ($this->api_live == true) {
            $domain = 'secure.paytm.com';
        }

        $this->refund_url = 'https://' . $domain . '/oltp/HANDLER_INTERNAL/REFUND';
        $this->status_query_url = 'https://' . $domain . '/oltp/HANDLER_INTERNAL/TXNSTATUS';
        $this->txn_url = 'https://' . $domain . '/oltp-web/processTransaction';
    }

    public function encrypt_e($input, $key) {

        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
        $input = $this->pkcs5_pad_e($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
        $iv = "@@@@&&&&####$$$$";
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    public function decrypt_e($crypt, $key) {

        $crypt = base64_decode($crypt);

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
        $iv = "@@@@&&&&####$$$$";
        mcrypt_generic_init($td, $key, $iv);
        $decrypted_data = mdecrypt_generic($td, $crypt);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $decrypted_data = $this->pkcs5_unpad_e($decrypted_data);
        $decrypted_data = rtrim($decrypted_data);
        return $decrypted_data;
    }

    public function pkcs5_pad_e($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public function pkcs5_unpad_e($text) {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        return substr($text, 0, -1 * $pad);
    }

    public function generateSalt_e($length) {
        $random = "";
        srand((double) microtime() * 1000000);

        $data = "AbcDE123IJKLMN67QRSTUVWXYZ";
        $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
        $data .= "0FGH45OP89";

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($data, (rand() % (strlen($data))), 1);
        }

        return $random;
    }

    public function checkString_e($value) {
        $myvalue = ltrim($value);
        $myvalue = rtrim($myvalue);
        if ($myvalue == 'null')
            $myvalue = '';
        return $myvalue;
    }

    public function getChecksumFromArray($arrayList, $sort = 1) {
        $key = $this->merchant_key;

        if ($sort != 0) {
            ksort($arrayList);
        }
        $str = $this->getArray2Str($arrayList);
        $salt = $this->generateSalt_e(4);
        $finalString = $str . "|" . $salt;
        $hash = hash("sha256", $finalString);
        $hashString = $hash . $salt;
        $checksum = $this->encrypt_e($hashString, $key);
        return $checksum;
    }

    public function verifychecksum_e($arrayList, $checksumvalue) {
        $key = $this->merchant_key;

        $arrayList = $this->removeCheckSumParam($arrayList);
        ksort($arrayList);
        $str = $this->getArray2Str($arrayList);
        $paytm_hash = $this->decrypt_e($checksumvalue, $key);
        $salt = substr($paytm_hash, -4);

        $finalString = $str . "|" . $salt;

        $website_hash = hash("sha256", $finalString);
        $website_hash .= $salt;

        $validFlag = false;
        if ($website_hash == $paytm_hash) {
            $validFlag = true;
        } 
        
        return $validFlag;
    }

    public function getArray2Str($arrayList) {
        $paramStr = "";
        $flag = 1;
        foreach ($arrayList as $key => $value) {
            if ($flag) {
                $paramStr .= $this->checkString_e($value);
                $flag = 0;
            } else {
                $paramStr .= "|" . $this->checkString_e($value);
            }
        }
        return $paramStr;
    }

    public function redirect2PG($paramList, $key) {
        $hashString = $this->getchecksumFromArray($paramList);
        $checksum = $this->encrypt_e($hashString, $key);
    }

    public function removeCheckSumParam($arrayList) {
        if (isset($arrayList["CHECKSUMHASH"])) {
            unset($arrayList["CHECKSUMHASH"]);
        }
        return $arrayList;
    }

    public function getTxnStatus($requestParamList) {
        return $this->callAPI($this->status_query_url, $requestParamList);
    }

    public function initiateTxnRefund($requestParamList) {
        $CHECKSUM = $this->getchecksumFromArray($requestParamList);
        $requestParamList["CHECKSUM"] = $CHECKSUM;
        return $this->callAPI($this->refund_url, $requestParamList);
    }

    public function callAPI($apiURL, $requestParamList) {
        $jsonResponse = "";
        $responseParamList = array();
        $JsonData = json_encode($requestParamList);
        $postData = 'JsonData=' . urlencode($JsonData);
        $ch = curl_init($apiURL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData))
        );
        $jsonResponse = curl_exec($ch);
        $responseParamList = json_decode($jsonResponse, true);
        return $responseParamList;
    }

}
