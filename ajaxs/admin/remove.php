<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
require_once __DIR__ . "/../../models/is_admin.php";
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
if($_POST["action"] == "remove_payment_manual") {
    if(!checkPermission($getUser["admin"], "edit_recharge")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `payment_manual` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("Item does not exist in the system")]));
    }
    $isRemove = $CMSNT->remove("payment_manual", " `id` = '" . $id . "' ");
    if($isRemove) {
        unlink("../../" . $row["icon"]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá trang nạp tiền thủ công") . " (" . $row["title"] . " ID " . $row["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá trang nạp tiền thủ công") . " (" . $row["title"] . " ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => "Xóa thành công"]);
        exit($data);
    }
}
if($_POST["action"] == "empty_all_list_die") {
    if(!checkPermission($getUser["admin"], "edit_stock_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $isRemove = $CMSNT->remove("product_die", " `id` > 0 ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xóa toàn bộ tài khoản DIE")]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xóa toàn bộ tài khoản DIE"), $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa dữ liệu thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removeTaskAutomation") {
    if(!checkPermission($getUser["admin"], "edit_automations")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `automations` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Dữ liệu không tồn tại trong hệ thống"]));
    }
    $isRemove = $CMSNT->remove("automations", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Delete Task (" . $row["name"] . ")"]);
        $data = json_encode(["status" => "success", "msg" => __("Xóa dữ liệu thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removeAccountSold") {
    if(!checkPermission($getUser["admin"], "edit_stock_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `product_sold` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Tài khoản không tồn tại trong hệ thống"]));
    }
    $isRemove = $CMSNT->remove("product_sold", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Delete Account Sold (" . $row["uid"] . ")"]);
        $data = json_encode(["status" => "success", "msg" => "Xóa tài khoản " . $row["uid"] . " thành công"]);
        exit($data);
    }
}
if($_POST["action"] == "empty_list_account_stock") {
    if(!checkPermission($getUser["admin"], "edit_stock_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    if(empty($_POST["confirm_empty_list_account"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập nội dung xác minh")]));
    }
    $confirm_empty_list_account = check_string($_POST["confirm_empty_list_account"]);
    if($confirm_empty_list_account != "toi dong y") {
        exit(json_encode(["status" => "error", "msg" => __("Nội dung xác minh không chính xác")]));
    }
    $product_code = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `product_stock` WHERE `product_code` = '" . $product_code . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("Kho hàng đang trống")]));
    }
    $isRemove = $CMSNT->remove("product_stock", " `product_code` = '" . $product_code . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xóa toàn bộ tài khoản đang bán của kho hàng") . " (" . $product_code . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xóa toàn bộ tài khoản đang bán của kho hàng") . " (" . $product_code . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa dữ liệu thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "empty_list_die") {
    if(!checkPermission($getUser["admin"], "edit_stock_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $product_code = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `product_die` WHERE `product_code` = '" . $product_code . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Không có dữ liệu cần xóa"]));
    }
    $isRemove = $CMSNT->remove("product_die", " `product_code` = '" . $product_code . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xóa toàn bộ tài khoản DIE của kho hàng") . " (" . $product_code . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xóa toàn bộ tài khoản DIE của kho hàng") . " (" . $product_code . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa dữ liệu thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removeProductDiscount") {
    if(!checkPermission($getUser["admin"], "edit_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `product_discount` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Dữ liệu không tồn tại"]));
    }
    $isRemove = $CMSNT->remove("product_discount", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Xóa điều kiện giảm giá sản phẩm (" . getRowRealtime("products", $row["product_id"], "name") . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Xóa điều kiện giảm giá sản phẩm (" . getRowRealtime("products", $row["product_id"], "name") . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa dữ liệu thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removeBlockIP") {
    if(!checkPermission($getUser["admin"], "edit_block_ip")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `block_ip` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Dữ liệu không tồn tại"]));
    }
    $isRemove = $CMSNT->remove("block_ip", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Remove Block IP (" . $row["ip"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Remove Block IP (" . $row["ip"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa IP thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removePromotion") {
    if(!checkPermission($getUser["admin"], "edit_promotion")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `promotions` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Dữ liệu không tồn tại"]));
    }
    $isRemove = $CMSNT->remove("promotions", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Delete Promotion (" . format_currency($row["min"]) . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Delete Promotion (" . format_currency($row["min"]) . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa promotion thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removeSupplier") {
    if(!checkPermission($getUser["admin"], "manager_suppliers")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($supplier = $CMSNT->get_row("SELECT * FROM `suppliers` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "ID API không tồn tại trong hệ thống"]));
    }
    $isRemove = $CMSNT->remove("suppliers", " `id` = '" . $id . "' ");
    if($isRemove) {
        sleep(3);
        $CMSNT->remove("products", " `supplier_id` = '" . $supplier["id"] . "' ");
        foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `supplier_id` = '" . $id . "' ") as $category) {
            $imagePath = "../../" . $category["icon"];
            if(file_exists($imagePath)) {
                unlink($imagePath);
            }
            $CMSNT->remove("categories", " `id` = '" . $category["id"] . "' ");
        }
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Remove API Supplier") . " (" . $supplier["domain"] . " ID " . $supplier["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Remove API Supplier") . " (" . $supplier["domain"] . " ID " . $supplier["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit(json_encode(["status" => "success", "msg" => __("Xóa API thành công!")]));
    }
}
if($_POST["action"] == "removeOrder") {
    if(!checkPermission($getUser["admin"], "delete_order_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($product_order = $CMSNT->get_row("SELECT * FROM `product_order` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Đơn hàng không tồn tại trong hệ thống"]));
    }
    $isRemove = $CMSNT->remove("product_order", " `id` = '" . $id . "' ");
    if($isRemove) {
        $CMSNT->remove("product_sold", " `trans_id` = '" . $product_order["trans_id"] . "' ");
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Delete Order (" . $product_order["trans_id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Delete Order (" . $product_order["trans_id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa đơn hàng thành công!")]);
        exit($data);
    }
}
if($_POST["action"] == "removeMenu") {
    if(!checkPermission($getUser["admin"], "edit_menu")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `menu` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Dữ liệu không tồn tại"]));
    }
    $isRemove = $CMSNT->remove("menu", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Delete Menu (" . $row["name"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Delete Menu (" . $row["name"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => "Xóa menu thành công !"]);
        exit($data);
    }
}
if($_POST["action"] == "removeRole") {
    if(!checkPermission($getUser["admin"], "edit_role")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `admin_role` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Dữ liệu không tồn tại"]));
    }
    $isRemove = $CMSNT->remove("admin_role", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Delete Role (" . $row["name"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Delete Role (" . $row["name"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => "Xóa Role thành công !"]);
        exit($data);
    }
}
if($_POST["action"] == "removeCounpon") {
    if(!checkPermission($getUser["admin"], "edit_coupon")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `coupons` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Dữ liệu không tồn tại"]));
    }
    $isRemove = $CMSNT->remove("coupons", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Delete Coupon (" . $row["code"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Delete Coupon (" . $row["code"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa Counpon thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removeAccountStock") {
    if(!checkPermission($getUser["admin"], "edit_stock_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `product_stock` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "Tài khoản không tồn tại trong hệ thống"]));
    }
    $isRemove = $CMSNT->remove("product_stock", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Delete Account Stock (" . $row["uid"] . ")"]);
        $data = json_encode(["status" => "success", "msg" => "Xóa tài khoản " . $row["uid"] . " thành công"]);
        exit($data);
    }
}
if($_POST["action"] == "removeImageProduct") {
    if(!checkPermission($getUser["admin"], "edit_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `products` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "ID sản phẩm không tồn tại trong hệ thống"]));
    }
    $image = check_string($_POST["image"]);
    unlink("../../" . dirImageProduct($image));
    $images = str_replace($image, "", $row["images"]);
    $images = preg_replace("/^\\h*\\v+/m", "", $images);
    $CMSNT->update("products", ["images" => $images], " `id` = '" . $row["id"] . "' ");
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Delete Image Product (" . $row["name"] . " ID " . $row["id"] . ")"]);
    $my_text = $CMSNT->site("noti_action");
    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
    $my_text = str_replace("{username}", $getUser["username"], $my_text);
    $my_text = str_replace("{action}", "Delete Image Product (" . $row["name"] . " ID " . $row["id"] . ")", $my_text);
    $my_text = str_replace("{ip}", myip(), $my_text);
    $my_text = str_replace("{time}", gettime(), $my_text);
    sendMessAdmin($my_text);
    exit(json_encode(["status" => "success", "msg" => __("Xóa sản phẩm thành công")]));
}
if($_POST["action"] == "removeProduct") {
    if(!checkPermission($getUser["admin"], "edit_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `products` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "ID sản phẩm không tồn tại trong hệ thống"]));
    }
    $isRemove = $CMSNT->remove("products", " `id` = '" . $id . "' ");
    if($isRemove) {
        if(!empty($row["images"])) {
            foreach (explode(PHP_EOL, $row["images"]) as $image) {
                $imagePath = "../../" . dirImageProduct($image);
                if(file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá sản phẩm") . " (" . $row["name"] . " ID " . $row["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá sản phẩm") . " (" . $row["name"] . " ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit(json_encode(["status" => "success", "msg" => __("Xóa sản phẩm thành công")]));
    }
}
if($_POST["action"] == "removeCategory") {
    if(!checkPermission($getUser["admin"], "edit_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `categories` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => "ID chuyên mục không tồn tại trong hệ thống"]));
    }
    if($row["parent_id"] == 0 && $CMSNT->num_rows(" SELECT * FROM `categories` WHERE `parent_id` = '" . $row["id"] . "' ") != 0) {
        exit(json_encode(["status" => "error", "msg" => "Bạn cần xóa hết chuyên mục con của chuyên mục này trước khi xóa chuyên mục cha"]));
    }
    $isRemove = $CMSNT->remove("categories", " `id` = '" . $id . "' ");
    if($isRemove) {
        unlink("../../" . $row["icon"]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá chuyên mục") . " (" . $row["name"] . " ID " . $row["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá chuyên mục") . " (" . $row["name"] . " ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => "Xóa chuyên mục thành công"]);
        exit($data);
    }
}
if($_POST["action"] == "removeCategoryBlog") {
    if(!checkPermission($getUser["admin"], "edit_blog")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `post_category` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("ID chuyên mục không tồn tại trong hệ thống")]));
    }
    $isRemove = $CMSNT->remove("post_category", " `id` = '" . $id . "' ");
    if($isRemove) {
        unlink("../../" . $row["icon"]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá chuyên mục bài viết") . " (" . $row["name"] . " ID " . $row["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá chuyên mục bài viết") . " (" . $row["name"] . " ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa chuyên mục thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removePost") {
    if(!checkPermission($getUser["admin"], "edit_blog")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `posts` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("Bài viết không tồn tại trong hệ thống")]));
    }
    $isRemove = $CMSNT->remove("posts", " `id` = '" . $id . "' ");
    if($isRemove) {
        unlink("../../" . $row["image"]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá bài viết") . " (" . $row["title"] . " ID " . $row["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá bài viết") . " (" . $row["title"] . " ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa bài viết thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removeBank") {
    if(!checkPermission($getUser["admin"], "edit_recharge")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `banks` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("Item does not exist in the system")]));
    }
    $isRemove = $CMSNT->remove("banks", " `id` = '" . $id . "' ");
    if($isRemove) {
        unlink("../../" . $row["image"]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá ngân hàng") . " (" . $row["short_name"] . " ID " . $row["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá ngân hàng") . " (" . $row["short_name"] . " ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => "Xóa ngân hàng thành công"]);
        exit($data);
    }
}
if($_POST["action"] == "removeLanguage") {
    if(!checkPermission($getUser["admin"], "edit_lang")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `languages` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        $data = json_encode(["status" => "error", "msg" => __("The ID to delete does not exist")]);
        exit($data);
    }
    if($row["lang_default"] == 1) {
        $data = json_encode(["status" => "error", "msg" => __("You cannot delete the system default language")]);
        exit($data);
    }
    $CMSNT->remove("translate", " `lang_id` = '" . $row["id"] . "' ");
    $isRemove = $CMSNT->remove("languages", " `id` = '" . $id . "' ");
    if($isRemove) {
        unlink("../../" . $row["image"]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá ngôn ngữ") . " (" . $row["lang"] . " ID " . $row["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá ngôn ngữ") . " (" . $row["lang"] . " ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Successful language removal")]);
        exit($data);
    }
}
if($_POST["action"] == "removeCurrency") {
    if(!checkPermission($getUser["admin"], "edit_currency")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `currencies` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("Item does not exist in the system")]));
    }
    $isRemove = $CMSNT->remove("currencies", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá tiền tệ") . " (" . $row["name"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá tiền tệ") . " (" . $row["name"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => "Xóa item thành công"]);
        exit($data);
    }
}
if($_POST["action"] == "removeWithdraw") {
    if(!checkPermission($getUser["admin"], "edit_withdraw_affiliate")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    if(empty($_POST["id"])) {
        exit(json_encode(["status" => "error", "msg" => __("ID không được để trống")]));
    }
    $id = check_string($_POST["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `aff_withdraw` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        $data = json_encode(["status" => "error", "msg" => __("ID item không tồn tại trong hệ thống")]);
        exit($data);
    }
    $isRemove = $CMSNT->remove("aff_withdraw", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá yêu cầu rút tiền hoa hồng") . " #" . $row["trans_id"] . " - " . format_currency($row["amount"]) . " - " . $row["status"]]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá yêu cầu rút tiền hoa hồng") . " #" . $row["trans_id"] . " - " . format_currency($row["amount"]) . " - " . $row["status"], $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xoá thành công")]);
        exit($data);
    }
}
if($_POST["action"] == "removeUser") {
    if(!checkPermission($getUser["admin"], "edit_user")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    if(empty($_POST["id"])) {
        exit(json_encode(["status" => "error", "msg" => __("ID không được để trống")]));
    }
    $id = check_string($_POST["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `users` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        $data = json_encode(["status" => "error", "msg" => "ID user không tồn tại trong hệ thống"]);
        exit($data);
    }
    if($getUser["admin"] != 99999 && $row["admin"] == 99999) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $isRemove = $CMSNT->remove("users", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá tài khoản") . " (" . $row["username"] . " ID " . $row["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá tài khoản") . " (" . $row["username"] . " ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => "Xóa người dùng thành công"]);
        exit($data);
    }
}
if($_POST["action"] == "removeTranslate") {
    if(!checkPermission($getUser["admin"], "edit_lang")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `translate` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        $data = json_encode(["status" => "error", "msg" => __("The ID to delete does not exist")]);
        exit($data);
    }
    $isRemove = $CMSNT->remove("translate", " `id` = '" . $id . "' ");
    if($isRemove) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá nội dung ngôn ngữ") . " (" . $row["value"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá nội dung ngôn ngữ") . " (" . $row["value"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Language removal successful")]);
        exit($data);
    }
}
if($_POST["action"] == "email_campaigns") {
    if(!checkPermission($getUser["admin"], "edit_email_campaigns")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `email_campaigns` WHERE `id` = '" . $id . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("Item không tồn tại trong hệ thống")]));
    }
    $isRemove = $CMSNT->remove("email_campaigns", " `id` = '" . $id . "' ");
    if($isRemove) {
        $CMSNT->remove("email_sending", " `camp_id` = '" . $row["id"] . "' ");
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xoá chiến dịch Email Marketing") . " (" . $row["name"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Xoá chiến dịch Email Marketing") . " (" . $row["name"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        $data = json_encode(["status" => "success", "msg" => __("Xóa item thành công")]);
        exit($data);
    }
}
exit(json_encode(["status" => "error", "msg" => "Dữ liệu không hợp lệ"]));

?>