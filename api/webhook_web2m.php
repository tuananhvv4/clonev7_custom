<?php

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
require_once __DIR__ . "/../libs/database/users.php";
$CMSNT = new DB();
$Mobile_Detect = new Mobile_Detect();
if($CMSNT->site("status") != 1 && !isset($_SESSION["admin_login"])) {
    exit("status_website_off");
}
if(!$CMSNT->site("token_webhook_web2m")) {
    exit("token_webhook_empty");
}
$accessToken = $CMSNT->site("token_webhook_web2m");
$receivedData = file_get_contents("php://input");
if(isset($_SERVER["HTTP_AUTHORIZATION"]) && strpos($_SERVER["HTTP_AUTHORIZATION"], "Bearer ") === 0) {
    $bearerToken = substr($_SERVER["HTTP_AUTHORIZATION"], 7);
    if($accessToken === $bearerToken) {
        $result = json_decode($receivedData, true);
        foreach ($result["data"] as $data) {
            $tid = check_string($data["transactionNum"]);
            $description = check_string($data["description"]);
            $amount = check_string($data["amount"]);
            $method = check_string($data["bank"]);
            $user_id = parse_order_id($description, $CMSNT->site("prefix_autobank"));
            if(empty($tid)) {
            } elseif($amount < $CMSNT->site("bank_min") || $CMSNT->site("bank_max") < $amount) {
            } else {
                if($data["type"] == "IN" && ($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $user_id . "' ")) && $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ") == 0) {
                    $received = checkPromotion($amount);
                    $insertSv2 = $CMSNT->insert("payment_bank", ["tid" => $tid, "method" => $method, "user_id" => $getUser["id"], "description" => $description, "amount" => $amount, "received" => $received, "create_gettime" => gettime(), "create_time" => time()]);
                    if($insertSv2) {
                        $user = new users();
                        $isCong = $user->AddCredits($getUser["id"], $received, "Nạp tiền tự động qua " . $method . " (#" . $tid . " - " . $description . " - " . $amount . ")", "TOPUP_" . $data["accountNumber"] . "_" . $tid);
                        if($isCong) {
                            debit_processing($getUser["id"]);
                            $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => $method, "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
                            $my_text = $CMSNT->site("noti_recharge");
                            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                            $my_text = str_replace("{username}", $getUser["username"], $my_text);
                            $my_text = str_replace("{method}", $method, $my_text);
                            $my_text = str_replace("{amount}", format_currency($amount), $my_text);
                            $my_text = str_replace("{price}", format_currency($received), $my_text);
                            $my_text = str_replace("{time}", gettime(), $my_text);
                            sendMessAdmin($my_text);
                        }
                    }
                }
                if($CMSNT->get_row(" SELECT COUNT(id) FROM `log_bank_auto` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ")["COUNT(id)"] == 0) {
                    $CMSNT->insert("log_bank_auto", ["tid" => $tid, "method" => $method, "description" => $description, "type" => $data["type"], "amount" => $amount, "create_gettime" => gettime()]);
                }
            }
        }
        $response = ["status" => true, "msg" => "OK"];
        echo json_encode($response);
    } else {
        http_response_code(401);
        echo "Chữ ký không hợp lệ.";
    }
} else {
    http_response_code(401);
    echo "Access Token không được cung cấp hoặc không hợp lệ.";
}
function writeLog($message, $logFile = "webhook.log")
{
    $timestamp = date("Y-m-d H:i:s");
    $logMessage = "[" . $timestamp . "] " . $message . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

?>