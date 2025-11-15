<?php

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
$CMSNT = new DB();
if(empty($_GET["api_key"])) {
    exit(json_encode(["status" => "error", "msg" => __("Thiếu api_key")]));
}
if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `api_key` = '" . check_string($_GET["api_key"]) . "' AND `banned` = 0 "))) {
    exit(json_encode(["status" => "error", "msg" => __("api_key không hợp lệ")]));
}
$data_category = [];
foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `parent_id` != 0 AND `status` = 1 ") as $category) {
    $data_product = [];
    foreach ($CMSNT->get_list(" SELECT * FROM `products` WHERE `category_id` = '" . $category["id"] . "' AND `status` = 1 ") as $product) {
        $stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
        $data_product[] = ["id" => $product["id"], "name" => $product["name"], "price" => (int) $product["price"], "amount" => (int) $stock, "description" => $product["short_desc"], "flag" => $product["flag"], "min" => $product["min"], "max" => $product["max"]];
    }
    $data_category[] = ["id" => $category["id"], "name" => $category["name"], "icon" => BASE_URL($category["icon"]), "products" => $data_product];
}
exit(json_encode(["status" => "success", "msg" => __("Lấy dữ liệu thành công!"), "categories" => $data_category], JSON_PRETTY_PRINT));

?>