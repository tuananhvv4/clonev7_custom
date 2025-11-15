<?php

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
require_once __DIR__ . "/../libs/database/users.php";
$CMSNT = new DB();
$Mobile_Detect = new Mobile_Detect();
if(empty($_GET["request_id"])) {
    exit("request_id empty");
}
if(empty($_GET["token"])) {
    exit("token empty");
}
if(empty($_GET["status"])) {
    exit("status empty");
}
$request_id = isset($_GET["request_id"]) ? check_string($_GET["request_id"]) : NULL;
$token = isset($_GET["token"]) ? check_string($_GET["token"]) : NULL;
$received = isset($_GET["received"]) ? check_string($_GET["received"]) : NULL;
$status = isset($_GET["status"]) ? check_string($_GET["status"]) : NULL;
$from_address = isset($_GET["from_address"]) ? check_string($_GET["from_address"]) : NULL;
$transaction_id = isset($_GET["transaction_id"]) ? check_string($_GET["transaction_id"]) : NULL;
if($token != $CMSNT->site("crypto_token")) {
    exit("Token xác minh không chính xác");
}
if(!($row = $CMSNT->get_row(" SELECT * FROM `payment_crypto` WHERE `request_id` = '" . $request_id . "' "))) {
    exit("Hoá đơn không tồn tại");
}
$amount = $row["received"];
$received = checkPromotion($amount);
$getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $row["user_id"] . "' ");
if($row["status"] == "completed") {
    exit("Hoá đơn này đã được xử lý rồi");
}
if($status == "expired") {
    $CMSNT->update("payment_crypto", ["status" => "expired", "update_gettime" => gettime()], " `id` = '" . $row["id"] . "' ");
    exit("cập nhật trạng thái expired");
}
if($status == "completed") {
    $isUpdate = $CMSNT->update("payment_crypto", ["status" => "completed", "update_gettime" => gettime()], " `id` = '" . $row["id"] . "' ");
    if($isUpdate) {
        $User = new users();
        $isCong = $User->AddCredits($row["user_id"], $received, "Crypto Recharge #" . $row["trans_id"], "TOPUP_CRYPTO_" . $row["trans_id"]);
        if($isCong) {
            $my_text = $CMSNT->site("noti_recharge");
            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
            $my_text = str_replace("{username}", $getUser["username"], $my_text);
            $my_text = str_replace("{method}", "Crypto", $my_text);
            $my_text = str_replace("{amount}", format_currency($amount), $my_text);
            $my_text = str_replace("{price}", format_currency($received), $my_text);
            $my_text = str_replace("{time}", gettime(), $my_text);
            sendMessAdmin($my_text);
            $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => "USDT", "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
            exit("Cập nhật trạng thái completed thành công!");
        }
        exit("Hóa đơn này đã được cộng tiền rồi");
    }
}

?>