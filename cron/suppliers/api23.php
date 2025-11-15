<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
require_once __DIR__ . "/../../libs/suppliers.php";
$CMSNT = new DB();
if($CMSNT->site("time_cron_suppliers_api23") < time() && time() - $CMSNT->site("time_cron_suppliers_api23") < 2) {
    exit("Thao tác quá nhanh, vui lòng thử lại sau!");
}
$CMSNT->update("settings", ["value" => time()], " `name` = 'time_cron_suppliers_api23' ");
foreach ($CMSNT->get_list(" SELECT * FROM `suppliers` WHERE `status` = 1 AND `type` = 'API_23' ") as $supplier) {
    $result = balance_API_23($supplier["domain"], $supplier["api_key"]);
    $result = json_decode($result, true);
    if($result["Code"] == 0) {
        $CMSNT->update("suppliers", ["price" => "\$" . $result["Balance"]], " `id` = '" . $supplier["id"] . "' ");
    }
    $result = listProduct_API_23($supplier["domain"]);
    $result = json_decode($result, true);
    if(isset($result["Data"])) {
        foreach ($result["Data"] as $api) {
            $api_id = check_string($api["AccountCode"]);
            $api_name = check_string($api["Name"]);
            $api_stock = (int) check_string($api["Instock"]);
            $api_desc = NULL;
            $api_price = (int) check_string($api["Price"] * 25000);
            $ck = $api_price * $supplier["discount"] / 100;
            $price = (int) check_string($api["Price"] * 25000);
            if($supplier["update_price"] == "ON") {
                if($supplier["roundMoney"] == "ON") {
                    $price = roundMoney($api_price + $ck);
                } else {
                    $price = $api_price + $ck;
                }
            }
            if(!($product = $CMSNT->get_row(" SELECT * FROM `products` WHERE `api_id` = '" . $api_id . "' AND `supplier_id` = '" . $supplier["id"] . "' "))) {
                $CMSNT->insert("products", ["user_id" => $supplier["user_id"], "category_id" => 0, "supplier_id" => $supplier["id"], "name" => $api_name, "slug" => create_slug($api_name . " " . $api_id), "short_desc" => $api_desc, "price" => $price, "status" => 0, "cost" => $api_price, "api_id" => $api_id, "api_name" => $api_name, "api_stock" => $api_stock, "api_time_update" => time(), "create_gettime" => gettime(), "update_gettime" => gettime()]);
                if($CMSNT->site("debug_api_suppliers") == 1) {
                    echo "<b style=\"color:red;\">CREATE</b> - Tạo sản phẩm " . $api_name . " thành công !<br>";
                }
            } else {
                $api_name = check_string($api["Name"]);
                $api_stock = (int) check_string($api["Instock"]);
                $api_desc = NULL;
                $api_price = (int) check_string($api["Price"] * 25000);
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
                $CMSNT->update("products", ["price" => $price, "name" => $product_name, "slug" => create_slug($product_name . " " . $api_id), "short_desc" => $product_desc, "cost" => $api_price, "api_name" => $api_name, "api_time_update" => time(), "api_stock" => $api_stock], " `id` = '" . $product["id"] . "' ");
                if($CMSNT->site("debug_api_suppliers") == 1) {
                    echo "<b style=\"color:green;\">UPDATE</b> - sản phẩm " . $api_name . " thành công !<br>";
                }
            }
        }
        $CMSNT->remove("products", " `supplier_id` = '" . $supplier["id"] . "' AND " . time() . " - `api_time_update` >= 3600 ");
    }
}

?>