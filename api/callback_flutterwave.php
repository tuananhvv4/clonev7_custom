<?php

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
require_once __DIR__ . "/../libs/database/users.php";
$CMSNT = new DB();
$Mobile_Detect = new Mobile_Detect();
if($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    if(!empty($data)) {
        $idData = check_string($data["id"]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.flutterwave.com/v3/transactions/" . $idData . "/verify");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $CMSNT->site("flutterwave_secretKey")]);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        $id = check_string($response["data"]["id"]);
        $txRef = check_string($response["data"]["tx_ref"]);
        $currency = check_string($response["data"]["currency"]);
        $amount = check_string($response["data"]["amount"]);
        $price = $amount * $CMSNT->site("flutterwave_rate");
        if($response["data"]["status"] == "successful" && ($row = $CMSNT->get_row(" SELECT * FROM `payment_flutterwave` WHERE `tx_ref` = '" . $txRef . "' AND `currency` = '" . $currency . "' AND `status` = 'pending'  "))) {
            $user = new users();
            $isCong = $user->AddCredits($row["user_id"], $price, __("Recharge Flutterwave") . " #" . $id, "TOPUP_Flutterwave_" . $txRef);
            if($isCong) {
                $CMSNT->update("payment_flutterwave", ["status" => "success", "price" => $price, "update_gettime" => gettime(), "amount" => $amount], " `id` = '" . $row["id"] . "' AND `status` = 'pending' ");
                $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => "Flutterwave", "amount" => $price, "received" => $price, "create_time" => time(), "is_virtual" => 0]);
                $my_text = $CMSNT->site("noti_recharge");
                $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                $my_text = str_replace("{username}", getRowRealtime("users", $getUser["id"], "username"), $my_text);
                $my_text = str_replace("{method}", "Flutterwave", $my_text);
                $my_text = str_replace("{amount}", $price, $my_text);
                $my_text = str_replace("{price}", $price, $my_text);
                $my_text = str_replace("{time}", gettime(), $my_text);
                sendMessAdmin($my_text);
            }
        }
    }
}

?>