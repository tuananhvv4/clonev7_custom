<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
if($CMSNT->site("status_demo") != 0) {
    $data = json_encode(["status" => "error", "msg" => __("This function cannot be used because this is a demo site")]);
    exit($data);
}
if(!isset($_POST["action"])) {
    $data = json_encode(["status" => "error", "msg" => __("The Request Not Found")]);
    exit($data);
}
if($_POST["action"] == "download_order") {
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
    }
    if(empty($_POST["trans_id"])) {
        exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không hợp lệ")]));
    }
    $trans_id = check_string($_POST["trans_id"]);
    if(!($order = $CMSNT->get_row("SELECT * FROM `product_order` WHERE `trans_id` = '" . $trans_id . "' AND `buyer` = " . $getUser["id"] . " AND `trash` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không tồn tại trong hệ thống")]));
    }
    if(($order["status_view_order"] == 1 || $CMSNT->site("isPurchaseIpVerified") == 1) && $order["ip"] != myip()) {
        exit(json_encode(["status" => "error", "msg" => __("Địa chỉ IP của bạn không khớp với địa chỉ IP bạn dùng để mua hàng")]));
    }
    if($order["status_view_order"] == 1 || $CMSNT->site("isPurchaseDeviceVerified") == 1) {
        $Mobile_Detect = new Mobile_Detect();
        if($order["device"] != $Mobile_Detect->getUserAgent()) {
            exit(json_encode(["status" => "error", "msg" => __("Trình duyệt của bạn không khớp với trình duyệt lúc bạn mua hàng")]));
        }
    }
    $accounts = getRowRealtime("products", $order["product_id"], "text_txt") . PHP_EOL;
    foreach ($CMSNT->get_list(" SELECT * FROM `product_sold` WHERE `trans_id` = '" . $trans_id . "' AND `buyer` = " . $getUser["id"] . " ORDER BY id DESC ") as $account) {
        $accounts .= htmlspecialchars_decode($account["account"]) . PHP_EOL;
    }
    $file = $trans_id . ".txt";
    $data = json_encode(["status" => "success", "filename" => $file, "accounts" => $accounts, "msg" => __("Đang tải xuống đơn hàng...")]);
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Download order") . " (" . $order["trans_id"] . ")"]);
    exit($data);
} else {
    if($_POST["action"] == "loadStatusInvoice") {
        if(empty($_POST["trans_id"])) {
            exit(json_encode(["status" => "error", "msg" => __("Trans ID does not exist in the system")]));
        }
        if(!($row = $CMSNT->get_row("SELECT * FROM `invoices` WHERE `trans_id` = '" . check_string($_POST["trans_id"]) . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Trans ID does not exist in the system")]));
        }
        exit(json_encode(["data" => ["status" => $row["status"]], "status" => "success", "msg" => ""]));
    }
    if($_POST["action"] == "notication_topup") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($row = $CMSNT->get_row(" SELECT * FROM `payment_bank` WHERE `notication` = 0 AND `user_id` = '" . $getUser["id"] . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Không có lịch sử nạp tiền gần đây")]));
        }
        $CMSNT->update("payment_bank", ["notication" => 1], " `id` = '" . $row["id"] . "' ");
        exit(json_encode(["status" => "success", "msg" => __("Nạp tiền thành công") . " " . format_currency($row["received"])]));
    }
    if($_POST["action"] == "notication_topup_momo") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($row = $CMSNT->get_row(" SELECT * FROM `payment_momo` WHERE `notication` = 0 AND `user_id` = '" . $getUser["id"] . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Không có lịch sử nạp tiền gần đây")]));
        }
        $CMSNT->update("payment_momo", ["notication" => 1], " `id` = '" . $row["id"] . "' ");
        exit(json_encode(["status" => "success", "msg" => __("Nạp tiền thành công") . " " . format_currency($row["received"])]));
    }
    exit(json_encode(["status" => "error", "msg" => __("Invalid data")]));
}

?>