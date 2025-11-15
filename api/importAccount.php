<?php

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
require_once __DIR__ . "/../libs/database/users.php";
$CMSNT = new DB();
if(empty($_REQUEST["api_key"])) {
    exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập API KEY")]));
}
if(empty($_REQUEST["code"])) {
    exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập Mã Kho Hàng")]));
}
if(empty($_REQUEST["account"])) {
    exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập Tài khoản cần thêm")]));
}
$api_key = check_string($_REQUEST["api_key"]);
$code = check_string($_REQUEST["code"]);
$account = check_string($_REQUEST["account"]);
$value_add = 0;
$value_update = 0;
list($uid) = explode("|", $account);
$filter = isset($_REQUEST["filter"]) ? check_string($_REQUEST["filter"]) : 1;
if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `api_key` = '" . check_string($_REQUEST["api_key"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
    exit(json_encode(["status" => "error", "msg" => __("API KEY không chính xác")]));
}
if(!checkPermission($getUser["admin"], "edit_stock_product")) {
    exit(json_encode(["status" => "error", "msg" => __("API KEY không có quyền sử dụng tính năng này")]));
}
if($filter == 1) {
    if($CMSNT->get_row(" SELECT * FROM `product_stock` WHERE `uid` = '" . $uid . "' ")) {
        $isUpdate = $CMSNT->update("product_stock", ["product_code" => $code, "seller" => $getUser["id"], "uid" => $uid, "account" => $account, "type" => "API", "create_gettime" => gettime()], " `uid` = '" . $uid . "' ");
        if($isUpdate) {
            $value_update++;
        }
    } else {
        $isAdd = $CMSNT->insert("product_stock", ["product_code" => $code, "seller" => $getUser["id"], "uid" => $uid, "account" => $account, "type" => "API", "create_gettime" => gettime()]);
        if($isAdd) {
            $value_add++;
        }
    }
} else {
    $isAdd = $CMSNT->insert("product_stock", ["product_code" => $code, "seller" => $getUser["id"], "uid" => $uid, "account" => $account, "type" => "API", "create_gettime" => gettime()]);
    if($isAdd) {
        $value_add++;
    }
}
exit(json_encode(["status" => "success", "msg" => "Đã thêm " . $value_add . " tài khoản | Cập nhật " . $value_update . " tài khoản"]));

?>