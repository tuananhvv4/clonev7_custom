<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
require_once __DIR__ . "/../../libs/suppliers.php";
$CMSNT = new DB();
if($CMSNT->site("time_cron_suppliers_shopclone7") < time() && time() - $CMSNT->site("time_cron_suppliers_shopclone7") < 5) {
    exit("Thao tác quá nhanh, vui lòng thử lại sau!");
}
$CMSNT->update("settings", ["value" => time()], " `name` = 'time_cron_suppliers_shopclone7' ");
foreach ($CMSNT->get_list(" SELECT * FROM `suppliers` WHERE `status` = 1 AND `type` = 'SHOPCLONE7' ") as $supplier) {
    $result = balance_API_SHOPCLONE7($supplier["domain"], $supplier["api_key"]);
    $result = json_decode($result, true);
    if(isset($result["status"]) && $result["status"] == "success") {
        $CMSNT->update("suppliers", ["price" => format_currency($result["data"]["money"])], " `id` = '" . $supplier["id"] . "' ");
    }
    $result = listProduct_API_SHOPCLONE7($supplier["domain"], $supplier["api_key"]);
    $result = json_decode($result, true);
    if($result["status"] == "success") {
        foreach ($result["categories"] as $category) {
            $category_id = 0;
            if($supplier["sync_category"] == "ON") {
                $category_name = check_string($category["name"]);
                if(!($category_api = $CMSNT->get_row(" SELECT * FROM `categories` WHERE `name` = '" . $category_name . "'  "))) {
                    $rand = "_" . random("QWERTTYUIOPASDFGHJKLZXCVBNM123456789", 6);
                    $uploads_dir = "../../assets/storage/images/category" . $rand . ".png";
                    $image = @imagecreatefrompng($category["icon"]);
                    $url_image = $CMSNT->site("favicon");
                    if($image) {
                        if(imagepng($image, $uploads_dir)) {
                            $url_image = "assets/storage/images/category" . $rand . ".png";
                        }
                        imagedestroy($image);
                    }
                    $isInsert = $CMSNT->insert("categories", ["parent_id" => 1, "id_api" => check_string($category["id"]), "supplier_id" => $supplier["id"], "status" => 1, "name" => $category_name, "slug" => create_slug($category_name), "icon" => $url_image, "create_date" => gettime()]);
                    if($isInsert) {
                        $category_id = $CMSNT->get_row(" SELECT * FROM `categories` WHERE `name` = '" . $category_name . "' AND `supplier_id` = '" . $supplier["id"] . "' ")["id"];
                        echo "<b style=\"color:red;\">CREATE</b> - Tạo category " . $category_name . " thành công !<br>";
                    }
                } else {
                    $category_id = $category_api["id"];
                }
            }
            foreach ($category["products"] as $api) {
                $api_id = check_string($api["id"]);
                $api_name = $supplier["check_string_api"] == "OFF" ? $api["name"] : check_string($api["name"]);
                $api_desc = $supplier["check_string_api"] == "OFF" ? $api["description"] : check_string($api["description"]);
                $api_stock = (int) check_string($api["amount"]);
                $api_price = (int) check_string($api["price"]);
                $ck = $api_price * $supplier["discount"] / 100;
                $price = $api["price"];
                if($supplier["update_price"] == "ON") {
                    if($supplier["roundMoney"] == "ON") {
                        $price = roundMoney($api_price + $ck);
                    } else {
                        $price = $api_price + $ck;
                    }
                }
                if(!($product = $CMSNT->get_row(" SELECT * FROM `products` WHERE `api_id` = '" . $api_id . "' AND `supplier_id` = '" . $supplier["id"] . "' "))) {
                    $CMSNT->insert("products", ["user_id" => $supplier["user_id"], "category_id" => $category_id, "supplier_id" => $supplier["id"], "name" => $api_name, "slug" => create_slug($api_name . $api_id), "short_desc" => $api_desc, "price" => $price, "status" => 0, "cost" => $api_price, "api_id" => $api_id, "api_name" => $api_name, "api_stock" => $api_stock, "api_time_update" => time(), "create_gettime" => gettime(), "update_gettime" => gettime()]);
                    if($CMSNT->site("debug_api_suppliers") == 1) {
                        echo "<b style=\"color:red;\">CREATE</b> - Tạo sản phẩm " . $api_name . " thành công !<br>";
                    }
                } else {
                    $api_name = $supplier["check_string_api"] == "OFF" ? $api["name"] : check_string($api["name"]);
                    $api_desc = $supplier["check_string_api"] == "OFF" ? $api["description"] : check_string($api["description"]);
                    $api_stock = (int) check_string($api["amount"]);
                    $api_price = (int) check_string($api["price"]);
                    $ck = $api_price * $supplier["discount"] / 100;
                    $price = $product["price"];
                    if($supplier["update_price"] == "ON") {
                        if($supplier["roundMoney"] == "ON") {
                            $price = roundMoney($api_price + $ck);
                        } else {
                            $price = $api_price + $ck;
                        }
                    }
                    $product_name = $api_name;
                    $product_desc = $api_desc;
                    if($supplier["update_name"] == "OFF") {
                        $product_name = $product["name"];
                        $product_desc = $product["short_desc"];
                    }
                    $CMSNT->update("products", ["price" => $price, "name" => $product_name, "slug" => create_slug($product_name . $api_id), "short_desc" => $product_desc, "cost" => $api_price, "api_name" => $api_name, "api_time_update" => time(), "api_stock" => $api_stock], " `id` = '" . $product["id"] . "' ");
                    if($CMSNT->site("debug_api_suppliers") == 1) {
                        echo "<b style=\"color:green;\">UPDATE</b> - sản phẩm " . $api_name . " thành công !<br>";
                    }
                }
            }
        }
        $CMSNT->remove("products", " `supplier_id` = '" . $supplier["id"] . "' AND " . time() . " - `api_time_update` >= 3600 ");
    }
}

?>