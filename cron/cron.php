<?php

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
$CMSNT = new DB();
if($CMSNT->site("check_time_cron_cron") < time() && time() - $CMSNT->site("check_time_cron_cron") < 3) {
    exit("Thao tác quá nhanh, vui lòng thử lại sau!");
}
$CMSNT->update("settings", ["value" => time()], " `name` = 'check_time_cron_cron' ");
if($CMSNT->site("status_tao_gd_ao") == 1) {
    $int_rand = rand(0, $CMSNT->site("toc_do_gd_nap_ao"));
    if($int_rand == $CMSNT->site("toc_do_gd_nap_ao")) {
        $array_amount = explode(PHP_EOL, $CMSNT->site("menh_gia_nap_ao_ngau_nhien"));
        $array_method = explode(PHP_EOL, $CMSNT->site("method_nap_ao"));
        $amount = $array_amount[rand(0, count($array_amount) - 1)];
        $amount = $amount != 0 ? $amount : 10000;
        $method = $array_method[rand(0, count($array_method) - 1)];
        $CMSNT->insert("deposit_log", ["user_id" => $CMSNT->get_row("SELECT * FROM `users` ORDER BY RAND() ")["id"], "method" => $method, "amount" => $amount, "received" => $amount, "create_time" => time(), "is_virtual" => 1]);
    }
    $int_rand = rand(0, $CMSNT->site("toc_do_gd_mua_ao"));
    if($int_rand == $CMSNT->site("toc_do_gd_mua_ao")) {
        $amount = rand($CMSNT->site("sl_mua_toi_thieu_gd_ao"), $CMSNT->site("sl_mua_toi_da_gd_ao"));
        $trans_id = random("QWERTYUPASDFGHJKZXCVBNM123456789", 4);
        foreach ($CMSNT->get_list("SELECT * FROM `products` WHERE `status` = 1 ORDER BY RAND() ") as $product) {
            if($CMSNT->site("tao_gd_ao_sp_het_hang") == 1) {
                $stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
                if($stock == 0) {
                }
            }
            $CMSNT->insert("order_log", ["buyer" => $CMSNT->get_row("SELECT * FROM `users` ORDER BY RAND() ")["id"], "product_name" => $product["name"], "pay" => $amount * $product["price"], "amount" => $amount, "create_time" => time(), "is_virtual" => 1]);
        }
    }
}
$CMSNT->remove("deposit_log", " " . time() . " - `create_time` >= 604800 ");
$CMSNT->remove("order_log", " " . time() . " - `create_time` >= 604800 ");
$urls = [];
$urls[] = base_url("tool/check-live-facebook");
$urls[] = base_url("tool/get-2fa");
$urls[] = base_url("tool/icon-facebook");
$urls[] = base_url("tool/random-face");
foreach ($CMSNT->get_list(" SELECT * FROM categories WHERE `status` = 1 ") as $category) {
    $urls[] = base_url("category/" . $category["slug"]);
}
foreach ($CMSNT->get_list(" SELECT * FROM products WHERE `status` = 1 ") as $product) {
    $urls[] = base_url("product/" . $product["slug"]);
}
foreach ($CMSNT->get_list(" SELECT * FROM posts WHERE `status` = 1 ") as $blog) {
    $urls[] = base_url("blog/" . $blog["slug"]);
}
$xml = new DOMDocument("1.0", "UTF-8");
$xml->formatOutput = true;
$urlset = $xml->createElement("urlset");
$urlset->setAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");
foreach ($urls as $url) {
    $urlElement = $xml->createElement("url");
    $locElement = $xml->createElement("loc", htmlspecialchars($url));
    $urlElement->appendChild($locElement);
    $urlset->appendChild($urlElement);
}
$xml->appendChild($urlset);
$xml->save("../sitemap.xml");

?>