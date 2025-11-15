<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
require_once __DIR__ . "/../../libs/database/users.php";
require_once __DIR__ . "/../../libs/toyyibpay.php";
$Mobile_Detect = new Mobile_Detect();
if($CMSNT->site("status") != 1) {
    $data = json_encode(["status" => "error", "msg" => __("Hệ thống đang bảo trì!")]);
    exit($data);
}
if(!isset($_POST["action"])) {
    $data = json_encode(["status" => "error", "msg" => __("The Request Not Found")]);
    exit($data);
}
if($CMSNT->site("status_demo") != 0) {
    exit(json_encode(["status" => "error", "msg" => __("Chức năng này không thể sử dụng trên website demo")]));
}
if($_POST["action"] == "WithdrawCommission") {
    if($CMSNT->site("status_demo") != 0) {
        exit(json_encode(["status" => "error", "msg" => __("This function cannot be used because this is a demo site")]));
    }
    if($CMSNT->site("affiliate_status") != 1) {
        exit(json_encode(["status" => "error", "msg" => __("Chức năng này đang được bảo trì")]));
    }
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    if(time() - $getUser["time_request"] < $config["max_time_load"]) {
        exit(json_encode(["status" => "error", "msg" => __("Thao tác quá nhanh, vui lòng chờ")]));
    }
    if(empty($_POST["bank"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng chọn ngân hàng cần rút")]));
    }
    if(empty($_POST["stk"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập số tài khoản cần rút")]));
    }
    if(empty($_POST["name"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập tên chủ tài khoản")]));
    }
    if(empty($_POST["amount"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập số tiền cần rút")]));
    }
    if($_POST["amount"] < $CMSNT->site("affiliate_min")) {
        exit(json_encode(["status" => "error", "msg" => __("Số tiền rút tối thiểu phải là") . " " . format_currency($CMSNT->site("affiliate_min"))]));
    }
    if($getUser["ref_price"] < $_POST["amount"]) {
        exit(json_encode(["status" => "error", "msg" => __("Số dư hoa hồng khả dụng của bạn không đủ")]));
    }
    $amount = check_string($_POST["amount"]);
    $trans_id = random("123456789QWERTYUIOPASDFGHJKLZXCVBNM", 6);
    $User = new users();
    $isTru = $User->RemoveCommission($getUser["id"], $amount, __("Withdraw commission balance") . " #" . $trans_id);
    if($isTru) {
        if(getRowRealtime("users", $getUser["id"], "ref_price") < 0) {
            $User->Banned($getUser["id"], __("Gian lận khi rút số dư hoa hồng"));
            exit(json_encode(["status" => "error", "msg" => __("Tài khoản của bạn đã bị khóa vì gian lận")]));
        }
        $isInsert = $CMSNT->insert("aff_withdraw", ["trans_id" => $trans_id, "user_id" => $getUser["id"], "bank" => check_string($_POST["bank"]), "stk" => check_string($_POST["stk"]), "name" => check_string($_POST["name"]), "amount" => check_string($_POST["amount"]), "status" => "pending", "create_gettime" => gettime(), "update_gettime" => gettime(), "reason" => NULL]);
        if($isInsert) {
            $my_text = $CMSNT->site("noti_affiliate_withdraw");
            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
            $my_text = str_replace("{username}", $getUser["username"], $my_text);
            $my_text = str_replace("{bank}", check_string($_POST["bank"]), $my_text);
            $my_text = str_replace("{account_number}", check_string($_POST["stk"]), $my_text);
            $my_text = str_replace("{account_name}", check_string($_POST["name"]), $my_text);
            $my_text = str_replace("{amount}", format_currency(check_string($_POST["amount"])), $my_text);
            $my_text = str_replace("{ip}", myip(), $my_text);
            $my_text = str_replace("{time}", gettime(), $my_text);
            sendMessTelegram($my_text, "", $CMSNT->site("affiliate_chat_id_telegram"));
            exit(json_encode(["status" => "success", "msg" => __("Yêu cầu rút tiền được tạo thành công, vui lòng đợi ADMIN xử lý")]));
        }
        exit(json_encode(["status" => "error", "msg" => "ERROR 1 - " . __("System error")]));
    }
    exit(json_encode(["status" => "error", "msg" => "ERROR 2 - " . __("System error")]));
}
if($_POST["action"] == "nap_the") {
    if($CMSNT->site("card_status") != 1) {
        exit(json_encode(["status" => "error", "msg" => __("Chức năng nạp thẻ đang được tắt")]));
    }
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    if(time() - $getUser["time_request"] < $config["max_time_load"]) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn đang thao tác quá nhanh, vui lòng chờ")]));
    }
    if(empty($_POST["telco"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng chọn nhà mạng")]));
    }
    if(empty($_POST["amount"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng chọn mệnh giá cần nạp")]));
    }
    if($_POST["amount"] <= 0) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng chọn mệnh giá cần nạp")]));
    }
    if(empty($_POST["serial"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập serial thẻ")]));
    }
    if(empty($_POST["pin"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập mã thẻ")]));
    }
    $telco = check_string($_POST["telco"]);
    $amount = check_string($_POST["amount"]);
    $serial = check_string($_POST["serial"]);
    $pin = check_string($_POST["pin"]);
    if(!checkFormatCard($telco, $serial, $pin)["status"]) {
        exit(json_encode(["status" => "error", "msg" => checkFormatCard($telco, $serial, $pin)["msg"]]));
    }
    if(5 < $CMSNT->num_rows(" SELECT * FROM `cards` WHERE `user_id` = '" . $getUser["id"] . "' AND `status` = 'pending'  ")) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng không spam!")]));
    }
    if(5 <= $CMSNT->num_rows("SELECT * FROM `cards` WHERE `status` = 'error' AND `user_id` = '" . $getUser["id"] . "' AND `create_date` >= DATE(NOW()) AND `create_date` < DATE(NOW()) + INTERVAL 1 DAY  ") - $CMSNT->num_rows("SELECT * FROM `cards` WHERE `status` = 'complted' AND `user_id` = '" . $getUser["id"] . "' AND `create_date` >= DATE(NOW()) AND `create_date` < DATE(NOW()) + INTERVAL 1 DAY  ")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn đã bị chặn sử dụng chức năng nạp thẻ trong 1 ngày")]));
    }
    $trans_id = random("QWERTYUIOPASDFGHJKLZXCVBNM", 6) . time();
    $data = card24h($telco, $amount, $serial, $pin, $trans_id);
    if($data["status"] == 99) {
        $isInsert = $CMSNT->insert("cards", ["trans_id" => $trans_id, "telco" => $telco, "amount" => $amount, "serial" => $serial, "pin" => $pin, "price" => 0, "user_id" => $getUser["id"], "status" => "pending", "reason" => "", "create_date" => gettime(), "update_date" => gettime()]);
        if($isInsert) {
            $CMSNT->update("users", ["time_request" => time()], " `id` = '" . $getUser["id"] . "' ");
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Thực hiện nạp thẻ Serial: " . $serial . " - Pin: " . $pin]);
            exit(json_encode(["status" => "success", "msg" => __("Đẩy thẻ lên thành công, vui lòng chờ xử lý thẻ trong giây lát!")]));
        }
        exit(json_encode(["status" => "error", "msg" => __("Nạp thẻ thất bại, vui lòng liên hệ Admin")]));
    }
    exit(json_encode(["status" => "error", "msg" => $data["data"]["msg"]]));
}
if($_POST["action"] == "CreateInvoiceCrypto") {
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    if(empty($_POST["amount"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập số tiền cần nạp")]));
    }
    $amount = check_string($_POST["amount"]);
    if($amount < $CMSNT->site("crypto_min")) {
        exit(json_encode(["status" => "error", "msg" => __("Số tiền gửi tối thiểu là:") . " \$" . $CMSNT->site("crypto_min")]));
    }
    if($CMSNT->site("crypto_max") < $amount) {
        exit(json_encode(["status" => "error", "msg" => __("Số tiền gửi tối đa là:") . " \$" . format_cash($CMSNT->site("crypto_max"))]));
    }
    if($CMSNT->site("crypto_status") != 1) {
        exit(json_encode(["status" => "error", "msg" => __("Chức năng này đang được bảo trì")]));
    }
    if($CMSNT->site("crypto_token") == "" || $CMSNT->site("crypto_address") == "") {
        exit(json_encode(["status" => "error", "msg" => __("Chức năng này chưa được cấu hình, vui lòng liên hệ Admin")]));
    }
    if(3 <= $CMSNT->num_rows(" SELECT * FROM `payment_crypto` WHERE `user_id` = '" . $getUser["id"] . "' AND `status` = 'waiting' AND ROUND(`amount`) = '" . $amount . "'  ")) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng không SPAM")]));
    }
    $name = "Recharge " . check_string($_SERVER["HTTP_HOST"]);
    $description = "Recharge invoice to " . $getUser["username"];
    $callback = base_url("api/callback_crypto.php");
    $return_url = base_url("client/recharge-crypto");
    $request_id = md5(time() . random("qwertyuiopasdfghjklzxcvbnm0123456789", 4));
    $arrContextOptions = ["ssl" => ["verify_peer" => false, "verify_peer_name" => false]];
    $result = file_get_contents("https://fpayment.co/api/AddInvoice.php?token_wallet=" . $CMSNT->site("crypto_token") . "&address_wallet=" . trim($CMSNT->site("crypto_address")) . "&name=" . urlencode($name) . "&description=" . urlencode($description) . "&amount=" . $amount . "&request_id=" . $request_id . "&callback=" . urlencode($callback) . "&return_url=" . urlencode($return_url), false, stream_context_create($arrContextOptions));
    $result = json_decode($result, true);
    if(!isset($result["status"])) {
        exit(json_encode(["status" => "error", "msg" => __("Không thể tạo hóa đơn do lỗi API, vui lòng thử lại sau")]));
    }
    if($result["status"] == "error") {
        exit(json_encode(["status" => "error", "msg" => __($result["msg"])]));
    }
    $trans_id = check_string($result["data"]["trans_id"]);
    $received = check_string($result["data"]["amount"]) * $CMSNT->site("crypto_rate");
    $isInsert = $CMSNT->insert("payment_crypto", ["trans_id" => $trans_id, "user_id" => $getUser["id"], "request_id" => check_string($result["data"]["request_id"]), "amount" => check_string($result["data"]["amount"]), "received" => $received, "create_gettime" => gettime(), "update_gettime" => gettime(), "status" => check_string($result["data"]["status"]), "url_payment" => check_string($result["data"]["url_payment"]), "msg" => NULL]);
    if($isInsert) {
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Generate Crypto Recharge Invoice") . " #" . $trans_id]);
        exit(json_encode(["url" => check_string($result["data"]["url_payment"]), "status" => "success", "msg" => __("Tạo hoá đơn nạp tiền thành công")]));
    }
}
if($_POST["action"] == "CreateToyyibpay") {
    if($CMSNT->site("status_demo") != 0) {
        exit(json_encode(["status" => "error", "msg" => __("You cannot use this function because this is a demo site")]));
    }
    if($CMSNT->site("status") != 1 && !isset($_SESSION["admin_login"])) {
        exit(json_encode(["status" => "error", "msg" => __("The system is maintenance")]));
    }
    if($CMSNT->site("toyyibpay_status") != 1) {
        exit(json_encode(["status" => "error", "msg" => __("This function is under maintenance")]));
    }
    if($CMSNT->site("toyyibpay_userSecretKey") == "") {
        exit(json_encode(["status" => "error", "msg" => __("This function has not been configured")]));
    }
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Please log in")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Please log in")]));
    }
    if(time() - $getUser["time_request"] < $config["max_time_load"]) {
        exit(json_encode(["status" => "error", "msg" => __("You are working too fast, please wait")]));
    }
    if(empty($_POST["amount"])) {
        exit(json_encode(["status" => "error", "msg" => __("Please enter deposit amount")]));
    }
    if($_POST["amount"] <= 0) {
        exit(json_encode(["status" => "error", "msg" => __("Deposit amount is not available")]));
    }
    if($_POST["amount"] < $CMSNT->site("toyyibpay_min")) {
        exit(json_encode(["status" => "error", "msg" => __("Minimum deposit amount is RM" . $CMSNT->site("toyyibpay_min") . "")]));
    }
    $amount = check_string($_POST["amount"]);
    $trans_id = random("QWERTYUIOPASDFGHJKLZXCVBNM", 3) . time();
    $toyyibpay = new toyyibpay($CMSNT->site("toyyibpay_userSecretKey"));
    $result = $toyyibpay->createBill(["categoryCode" => $CMSNT->site("toyyibpay_categoryCode"), "billName" => "Invoice - RM " . $amount, "billDescription" => "Recharge invoice on website " . $_SERVER["HTTP_HOST"], "billPriceSetting" => 1, "billPayorInfo" => 0, "billAmount" => check_string($_POST["amount"]) * 100, "billReturnUrl" => base_url("client/recharge-toyyibpay"), "billCallbackUrl" => base_url("api/callback_toyyibpay.php"), "billExternalReferenceNo" => $trans_id, "billTo" => $getUser["username"], "billEmail" => !empty($getUser["email"]) ? $getUser["email"] : "None", "billPhone" => !empty($getUser["phone"]) ? $getUser["phone"] : 0, "billSplitPayment" => 0, "billSplitPaymentArgs" => "", "billPaymentChannel" => 0, "billContentEmail" => "Thank you for using our system", "billChargeToCustomer" => $CMSNT->site("toyyibpay_billChargeToCustomer"), "billExpiryDate" => "", "billExpiryDays" => 3]);
    $result = json_decode($result, true);
    $BillCode = $result[0]["BillCode"];
    if(!isset($BillCode)) {
        exit(json_encode(["status" => "error", "msg" => __("Error API!")]));
    }
    $isInsert = $CMSNT->insert("payment_toyyibpay", ["user_id" => $getUser["id"], "trans_id" => $trans_id, "billName" => "Invoice - RM " . $amount, "amount" => $amount, "status" => 0, "BillCode" => $BillCode, "create_gettime" => gettime(), "update_gettime" => gettime()]);
    if($isInsert) {
        $CMSNT->update("users", ["time_request" => time()], " `id` = '" . $getUser["id"] . "' ");
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Create Recharge Bank Malaysia Invoice #") . " " . $trans_id]);
        exit(json_encode(["invoice_url" => "https://toyyibpay.com/" . $BillCode, "status" => "success", "msg" => __("Successful!")]));
    }
    exit(json_encode(["status" => "error", "msg" => __("Error!")]));
}
exit(json_encode(["status" => "error", "msg" => __("Request does not exist")]));

?>