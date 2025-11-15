<?php

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
require_once __DIR__ . "/../libs/database/users.php";
$User = new users();
$CMSNT = new DB();
$Mobile_Detect = new Mobile_Detect();
if($CMSNT->site("status") != 1 && !isset($_SESSION["admin_login"])) {
    exit("status_website_off");
}
if($CMSNT->site("card_status") != 1) {
    exit("status_card_off");
}
if(isset($_GET["request_id"]) && isset($_GET["callback_sign"])) {
    $status = check_string($_GET["status"]);
    $message = check_string($_GET["message"]);
    $request_id = check_string($_GET["request_id"]);
    $declared_value = check_string($_GET["declared_value"]);
    $value = check_string($_GET["value"]);
    $amount = check_string($_GET["amount"]);
    $code = check_string($_GET["code"]);
    $serial = check_string($_GET["serial"]);
    $telco = check_string($_GET["telco"]);
    $trans_id = check_string($_GET["trans_id"]);
    $callback_sign = check_string($_GET["callback_sign"]);
    if($callback_sign != md5($CMSNT->site("card_partner_key") . $code . $serial)) {
        exit("callback_sign_error");
    }
    if(!($row = $CMSNT->get_row(" SELECT * FROM `cards` WHERE `trans_id` = '" . $request_id . "' AND `status` = 'pending' "))) {
        exit("request_id_error");
    }
    if(!($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $row["user_id"] . "' AND `banned` = 0 "))) {
        exit("user không hợp lệ");
    }
    if($status == 1) {
        if($CMSNT->site("card_ck") == 0) {
            $price = $amount;
        } else {
            $price = $value - $value * $CMSNT->site("card_ck") / 100;
        }
        $CMSNT->update("cards", ["status" => "completed", "price" => $price, "update_date" => gettime()], " `id` = '" . $row["id"] . "' ");
        $isCong = $User->AddCredits($row["user_id"], $price, "Nạp thẻ cào Seri " . $row["serial"] . " - Pin " . $row["pin"], "TOPUP_CARD_" . $row["pin"]);
        if($isCong) {
            $my_text = $CMSNT->site("noti_recharge");
            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
            $my_text = str_replace("{username}", $getUser["username"], $my_text);
            $my_text = str_replace("{method}", $telco, $my_text);
            $my_text = str_replace("{amount}", format_currency($amount), $my_text);
            $my_text = str_replace("{price}", format_currency($price), $my_text);
            $my_text = str_replace("{time}", gettime(), $my_text);
            sendMessAdmin($my_text);
            $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => "Thẻ cào", "amount" => $value, "received" => $price, "create_time" => time(), "is_virtual" => 0]);
            exit("payment.success");
        }
        exit("thẻ này đã được cộng tiền rồi");
    }
    $CMSNT->update("cards", ["status" => "error", "price" => 0, "update_date" => gettime(), "reason" => "Thẻ cào không hợp lệ hoặc đã được sử dụng"], " `id` = '" . $row["id"] . "' ");
    exit("payment.error");
}

?>