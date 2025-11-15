<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
class toyyibpay
{
    private $userSecretKey;
    public function __construct($userSecretKey)
    {
        if(empty($userSecretKey)) {
            throw new Exception("User Secret Key is not specified");
        }
        $this->userSecretKey = $userSecretKey;
    }
    public function getBillTransactions(array $some_data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, "https://toyyibpay.com/index.php/api/getBillTransactions");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        return $result;
    }
    public function createBill(array $some_data)
    {
        $some_data["userSecretKey"] = $this->userSecretKey;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, "https://toyyibpay.com/index.php/api/createBill");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        return $result;
    }
    public function __destruct()
    {
    }
}

?>