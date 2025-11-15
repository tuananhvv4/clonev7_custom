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
    $data = json_encode(["status" => "error", "msg" => "The Request Not Found"]);
    exit($data);
}
if(!isset($_POST["id"])) {
    $data = json_encode(["status" => "error", "msg" => __("The ID to delete does not exist")]);
    exit($data);
}
if($CMSNT->site("status_demo") != 0) {
    exit(json_encode(["status" => "error", "msg" => __("Chức năng này không thể sử dụng trên website demo")]));
}
if($_POST["action"] == "removeOrder") {
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Please login")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Please login")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `product_order` WHERE `id` = '" . $id . "' AND `buyer` = '" . $getUser["id"] . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không tồn tại trong hệ thống")]));
    }
    $isRemove = $CMSNT->update("product_order", ["trash" => 1], " `id` = '" . $row["id"] . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Delete order") . " (" . $row["trans_id"] . ")"]);
        exit(json_encode(["status" => "success", "msg" => __("Xóa đơn hàng thành công!")]));
    }
}
if($_POST["action"] == "removeFavorite") {
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Please login")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Please login")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `favorites` WHERE `id` = '" . $id . "' AND `user_id` = '" . $getUser["id"] . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("Xóa dữ liệu thất bại")]));
    }
    $isRemove = $CMSNT->remove("favorites", " `id` = '" . $row["id"] . "' ");
    if($isRemove) {
        exit(json_encode(["status" => "success", "msg" => __("Xóa dữ liệu thành công")]));
    }
}
exit(json_encode(["status" => "error", "msg" => __("Invalid data")]));

?>