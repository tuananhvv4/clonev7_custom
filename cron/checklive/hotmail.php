<?php
define("IN_SITE", true);

require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";

function getIfExistsResult($username) {
    $ch = curl_init('https://login.live.com/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['username' => $username]),
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    if ($html === false) {
        return 'ERROR';
    }

    if (preg_match('/var ServerData = (.*?);<\/script>/s', $html, $matches)) {
        if (preg_match('/"IfExistsResult"\s*:\s*([0-9]+)/', $matches[1], $result)) {
            return $result[1] == 0 ? 'LIVE' : 'DIE';
        }
    }
    return 'ERROR';
}


$CMSNT = new DB();


$lastCronTime = $CMSNT->site("time_cron_checklive_hotmail");
$currentTime = time();
if ($lastCronTime >= $currentTime || ($currentTime - $lastCronTime < 1)) {
    exit("Thao tác quá nhanh, vui lòng thử lại sau!");
}
$CMSNT->update("settings", ["value" => $currentTime], " `name` = 'time_cron_checklive_hotmail' ");
$products_list = $CMSNT->get_list("SELECT * FROM `products` WHERE `check_live` = 'Hotmail'");
if (empty($products_list)) {
    exit("Không có sản phẩm nào bật check live");
}

$product_codes = array_column($products_list, "code");
$product_codes_str = implode("','", array_map("addslashes", $product_codes));
$where_is_checklive = " AND `product_code` IN ('" . $product_codes_str . "')";

$products = $CMSNT->get_list("SELECT * FROM `product_stock` WHERE `id` > 0 " . $where_is_checklive . " ORDER BY `time_check_live` ASC LIMIT 50");
if (empty($products)) {
    exit("Không có tài khoản nào cần kiểm tra");
}
$products_info = [];
$uids = [];

foreach ($products as $product) {
    $account = check_string($product["account"]);
    if (!in_array($account, $uids)) {
        $uids[] = $account;
    }
    $products_info[$account] = $product;
}
foreach ($uids as $uid) {
    $username = explode('|', $uid)[0];
    $result = getIfExistsResult($username);

    if ($result === 'DIE') {
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
            echo "UID: " . substr($uid, 0, 6) . "*******, Result: DIE<br>";
        }
    } elseif ($result === 'LIVE') {
        $CMSNT->update("product_stock", ["time_check_live" => $currentTime], " `id` = '" . $products_info[$uid]["id"] . "' ");
        echo "UID: " . substr($uid, 0, 6) . "*******, Result: LIVE<br>";
    } else {
        $CMSNT->update("product_stock", ["time_check_live" => $currentTime], " `id` = '" . $products_info[$uid]["id"] . "' ");
        echo "UID: " . substr($uid, 0, 6) . "*******, Result: ERROR<br>";
    }
}
