<?php

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
$CMSNT = new DB();
if($CMSNT->site("check_time_cron_task") < time() && time() - $CMSNT->site("check_time_cron_task") < 3) {
    exit("Thao tác quá nhanh, vui lòng thử lại sau!");
}
$CMSNT->update("settings", ["value" => time()], " `name` = 'check_time_cron_task' ");
foreach ($CMSNT->get_list(" SELECT * FROM `automations` ") as $task) {
    if($task["type"] == "change_warehouse") {
        if($task["product_id"] == "") {
            foreach ($CMSNT->get_list(" SELECT * FROM `product_stock` WHERE " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " ") as $product_stock) {
                $isUpdate = $CMSNT->update("product_stock", ["product_code" => $task["other"], "create_gettime" => gettime()], " `id` = '" . $product_stock["id"] . "' ");
            }
        } else {
            foreach (json_decode($task["product_id"], true) as $product) {
                if($product_code = $CMSNT->get_row(" SELECT * FROM `products` WHERE `id` = '" . $product . "' ")["code"]) {
                    $CMSNT->update("product_stock", ["product_code" => $task["other"], "create_gettime" => gettime()], " `product_code` = '" . $product_code . "' AND " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " ");
                }
            }
        }
    }
    if($task["type"] == "delete_order") {
        if($task["product_id"] == "") {
            $CMSNT->update("product_order", ["trash" => 1], " " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " ");
            $CMSNT->remove("product_sold", " " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " ");
        } else {
            foreach (json_decode($task["product_id"], true) as $product) {
                foreach ($CMSNT->get_list(" SELECT * FROM `product_order` WHERE `product_id` = '" . $product . "' AND " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " AND `trash` = 0 ") as $product_order) {
                    $CMSNT->update("product_order", ["trash" => 1], " `trans_id` = '" . $product_order["trans_id"] . "' ");
                    $CMSNT->remove("product_sold", " `trans_id` = '" . $product_order["trans_id"] . "' ");
                }
            }
        }
    }
    if($task["type"] == "delete_order_not_uid") {
        if($task["product_id"] == "") {
            $CMSNT->update("product_order", ["trash" => 1], " " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " ");
            $CMSNT->update("product_sold", ["account" => __("Tài khoản đã được xóa tự động")], " " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " ");
        } else {
            foreach (json_decode($task["product_id"], true) as $product) {
                foreach ($CMSNT->get_list(" SELECT * FROM `product_order` WHERE `product_id` = '" . $product . "' AND " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " AND `trash` = 0 ") as $product_order) {
                    $CMSNT->update("product_order", ["trash" => 1], " `trans_id` = '" . $product_order["trans_id"] . "' ");
                    $CMSNT->update("product_sold", ["account" => __("Tài khoản đã được xóa tự động")], " `trans_id` = '" . $product_order["trans_id"] . "' ");
                }
            }
        }
    }
    if($task["type"] == "delete_order_revenue") {
        if($task["product_id"] == "") {
            $CMSNT->remove("product_order", " " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " ");
            $CMSNT->remove("product_sold", " " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " ");
        } else {
            foreach (json_decode($task["product_id"], true) as $product) {
                foreach ($CMSNT->get_list(" SELECT * FROM `product_order` WHERE `product_id` = '" . $product . "' AND " . time() . " - UNIX_TIMESTAMP(create_gettime) >= " . $task["schedule"] . " AND `trash` = 0 ") as $product_order) {
                    $CMSNT->remove("product_order", " `trans_id` = '" . $product_order["trans_id"] . "' ");
                    $CMSNT->remove("product_sold", " `trans_id` = '" . $product_order["trans_id"] . "' ");
                }
            }
        }
    }
}

?>