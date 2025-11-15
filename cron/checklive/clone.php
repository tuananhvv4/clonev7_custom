<?php
define("IN_SITE", true);

require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";

$CMSNT = new DB();

// Kiểm tra thời gian chạy cron
$currentTime = time();
$lastCronTime = $CMSNT->site("time_cron_checklive_clone");
if ($lastCronTime >= $currentTime || ($currentTime - $lastCronTime < 1)) {
    exit("Thao tác quá nhanh, vui lòng thử lại sau!");
}

// Cập nhật thời gian chạy cron
$CMSNT->update("settings", ["value" => $currentTime], " `name` = 'time_cron_checklive_clone' ");

// Lấy danh sách sản phẩm có bật `check_live = 'Clone'`
$products_list = $CMSNT->get_list("SELECT * FROM `products` WHERE `check_live` = 'Clone'");

if (empty($products_list)) {
    exit("Không có sản phẩm nào bật check live");
}

// Lấy danh sách mã sản phẩm
$product_codes = array_column($products_list, "code");
$product_codes_str = implode("','", array_map("addslashes", $product_codes));
$where_is_checklive = " AND `product_code` IN ('" . $product_codes_str . "')";

// Lấy danh sách UID cần kiểm tra
$products = $CMSNT->get_list("
    SELECT * 
    FROM `product_stock` 
    WHERE `id` > 0 {$where_is_checklive} 
    ORDER BY `time_check_live` ASC 
    LIMIT 200
");

if (empty($products)) {
    exit("Không có sản phẩm nào cần kiểm tra");
}

$uids = [];
$products_info = [];

// Chuẩn bị dữ liệu kiểm tra
foreach ($products as $product) {
    $uid = check_string($product["uid"]);
    if (!in_array($uid, $uids)) {
        $uids[] = $uid;
    }
    $products_info[$uid] = $product;
}

// Cấu hình cURL đa luồng
$mh = curl_multi_init();
$curl_handles = [];

foreach ($uids as $uid) {
    $ch = curl_init();
    $url = "https://graph2.facebook.com/v3.3/{$uid}/picture?redirect=0";
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    curl_multi_add_handle($mh, $ch);
    $curl_handles[$uid] = $ch;
}

// Thực thi cURL đa luồng
$running = null;
do {
    curl_multi_exec($mh, $running);
} while ($running > 0);

// Xử lý kết quả từ cURL
foreach ($curl_handles as $uid => $ch) {
    $result = curl_multi_getcontent($ch);
    $info = curl_getinfo($ch);

    if ($info["http_code"] == 200) {
        $result_array = json_decode($result, true);
        if (isset($result_array["data"]) && (!empty($result_array["data"]["height"]) || !empty($result_array["data"]["width"]))) {
            // Cập nhật thời gian kiểm tra
            $CMSNT->update("product_stock", ["time_check_live" => $currentTime], " `id` = '" . $products_info[$uid]["id"] . "' ");
            echo "UID: " . substr($uid, 0, 8) . "*******, Result: LIVE<br>";
        } else {
            // Chuyển vào bảng `product_die`
            $isInserted = $CMSNT->insert("product_die", [
                "product_code" => $products_info[$uid]["product_code"],
                "seller" => $products_info[$uid]["seller"],
                "uid" => $products_info[$uid]["uid"],
                "account" => $products_info[$uid]["account"],
                "create_gettime" => $products_info[$uid]["create_gettime"],
                "type" => $products_info[$uid]["type"]
            ]);

            if ($isInserted) {
                $CMSNT->remove("product_stock", " `id` = '" . $products_info[$uid]["id"] . "' ");
                echo "UID: " . substr($uid, 0, 8) . "*******, Result: DIE<br>";
            }
        }
    } else {
        // Cập nhật trạng thái khi lỗi
        $CMSNT->update("product_stock", ["time_check_live" => $currentTime], " `id` = '" . $products_info[$uid]["id"] . "' ");
        echo "UID: " . substr($uid, 0, 8) . "*******, Result: ERROR, HTTP Code: " . $info["http_code"] . "<br>";
    }

    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

curl_multi_close($mh);
