<?php

define("IN_SITE", true);
require_once __DIR__ . "/libs/db.php";
require_once __DIR__ . "/libs/lang.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/libs/helper.php";

$CMSNT = new DB();

/* ================= HELPER SAFE FUNCTIONS ================= */

function table_exists($table) {
    global $CMSNT;
    return $CMSNT->get_row("SHOW TABLES LIKE '$table'") ? true : false;
}

function column_exists($table, $column) {
    global $CMSNT;
    return $CMSNT->get_row("SHOW COLUMNS FROM `$table` LIKE '$column'") ? true : false;
}

function index_exists($table, $indexName) {
    global $CMSNT;
    $check = $CMSNT->get_row("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'");
    return $check ? true : false;
}

function create_table($sql) {
    global $CMSNT;
    try { $CMSNT->query($sql); } catch (Exception $e) {}
}

function safe_query($sql) {
    global $CMSNT;
    try { $CMSNT->query($sql); } catch (Exception $e) {}
}

function insert_options($name, $value) {
    global $CMSNT;
    if(!$CMSNT->get_row("SELECT * FROM `settings` WHERE `name` = '$name'")) {
        $CMSNT->insert("settings", ["name" => $name, "value" => $value]);
    }
}

/* ================= CREATE TABLES SAFE ================= */

create_table("
CREATE TABLE IF NOT EXISTS `deposit_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `method` VARCHAR(255) NULL DEFAULT NULL,
    `amount` FLOAT NOT NULL DEFAULT 0,
    `received` FLOAT NOT NULL DEFAULT 0,
    `create_time` INT(11) NOT NULL,
    `is_virtual` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
)
");

create_table("
CREATE TABLE IF NOT EXISTS `order_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `buyer` INT NOT NULL,
    `product_name` VARCHAR(255) NULL DEFAULT NULL,
    `pay` FLOAT NOT NULL DEFAULT 0,
    `amount` INT NOT NULL DEFAULT 0,
    `create_time` INT(11) NOT NULL,
    PRIMARY KEY (`id`)
)
");

/* ADD COLUMNS SAFELY */
if (!column_exists("order_log", "is_virtual")) {
    safe_query("ALTER TABLE `order_log` ADD `is_virtual` INT(11) NOT NULL DEFAULT '0'");
}

if (!column_exists("product_sold", "time_check_live")) {
    safe_query("ALTER TABLE `product_sold` ADD `time_check_live` INT(11) NOT NULL DEFAULT '0'");
}

if (!column_exists("product_order", "refund")) {
    safe_query("ALTER TABLE `product_order` ADD `refund` INT(11) NOT NULL DEFAULT '0'");
}

if (!column_exists("products", "flag")) {
    safe_query("ALTER TABLE `products` ADD `flag` TEXT NULL DEFAULT NULL");
}

create_table("
CREATE TABLE IF NOT EXISTS `automations` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` TEXT NULL,
    `type` VARCHAR(55) NULL,
    `product_id` LONGTEXT NULL,
    `schedule` INT(11) NOT NULL DEFAULT '0',
    `other` TEXT NULL,
    `create_gettime` DATETIME NOT NULL,
    `update_gettime` DATETIME NOT NULL,
    PRIMARY KEY (`id`)
)
");

safe_query("ALTER TABLE `payment_momo` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT");

if (!column_exists("suppliers", "check_string_api")) {
    safe_query("ALTER TABLE `suppliers` ADD `check_string_api` VARCHAR(55) NOT NULL DEFAULT 'ON'");
}

if (!column_exists("product_order", "note")) {
    safe_query("ALTER TABLE `product_order` ADD `note` TEXT NULL DEFAULT NULL");
}

if (!column_exists('products', 'text_txt')) {
    safe_query("ALTER TABLE `products` ADD `text_txt` TEXT NULL DEFAULT NULL");
}

if (!index_exists('product_die', 'uid')) {
    safe_query("ALTER TABLE product_die ADD UNIQUE (uid)");
}

if (!column_exists("products", "order_by")) {
    safe_query("ALTER TABLE `products` ADD `order_by` INT(11) NOT NULL DEFAULT '1'");
}

if (!column_exists("product_sold", "type")) {
    safe_query("ALTER TABLE `product_sold` ADD `type` VARCHAR(55) NULL DEFAULT 'WEB'");
}

create_table("
CREATE TABLE IF NOT EXISTS `payment_flutterwave` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL DEFAULT '0',
    `tx_ref` VARCHAR(55) NULL,
    `amount` FLOAT NOT NULL DEFAULT '0',
    `currency` TEXT NULL,
    `create_gettime` DATETIME NOT NULL,
    `update_gettime` DATETIME NOT NULL,
    `status` VARCHAR(55) NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`id`)
)
");

if (!column_exists("payment_flutterwave", "price")) {
    safe_query("ALTER TABLE `payment_flutterwave` ADD `price` FLOAT NOT NULL DEFAULT '0'");
}

create_table("
CREATE TABLE IF NOT EXISTS `failed_attempts` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `ip_address` VARCHAR(45) NULL,
    `attempts` INT(11) NOT NULL DEFAULT '0',
    `create_gettime` DATETIME NOT NULL,
    `type` VARCHAR(55) NULL,
    PRIMARY KEY (`id`)
)
");

create_table("
CREATE TABLE IF NOT EXISTS `payment_manual` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `icon` TEXT NULL,
    `title` TEXT NULL,
    `description` TEXT NULL,
    `content` LONGTEXT NULL,
    `display` INT(11) NOT NULL DEFAULT '0',
    `create_gettime` DATETIME NOT NULL,
    `update_gettime` DATETIME NOT NULL,
    PRIMARY KEY (`id`)
)
");

if (!column_exists("payment_manual", "slug")) {
    safe_query("ALTER TABLE `payment_manual` ADD `slug` TEXT NULL");
}

safe_query("ALTER TABLE `log_ref` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT");
safe_query("ALTER TABLE `log_ref` ADD PRIMARY KEY (`id`)");

if (!column_exists("menu", "parent_id")) {
    safe_query("ALTER TABLE `menu` ADD `parent_id` INT(11) NOT NULL DEFAULT '0'");
}

if (!column_exists("suppliers", "sync_category")) {
    safe_query("ALTER TABLE `suppliers` ADD `sync_category` VARCHAR(55) NOT NULL DEFAULT 'OFF'");
}

if (!column_exists("categories", "supplier_id")) {
    safe_query("ALTER TABLE `categories` ADD `supplier_id` INT(11) NOT NULL DEFAULT '0'");
}

/* ================= INSERT OPTIONS SAFE ================= */

insert_options("status_giao_dich_gan_day", 1);
insert_options("content_gd_mua_gan_day", "<b style=\"color: green;\">...{username}</b> mua <b style=\"color: red;\">{amount}</b> <b>{product_name}</b> với giá <b style=\"color:blue;\">{price}</b>");
insert_options("content_gd_nap_tien_gan_day", "<b style=\"color: green;\">...{username}</b> thực hiện nạp <b style=\"color:blue;\">{amount}</b> bằng <b style=\"color:red;\">{method}</b> thực nhận <b style=\"color:blue;\">{received}</b>");
insert_options("status_tao_gd_ao", 0);
insert_options("sl_mua_toi_thieu_gd_ao", 1);
insert_options("sl_mua_toi_da_gd_ao", 10);
insert_options("toc_do_gd_mua_ao", 5);
insert_options("menh_gia_nap_ao_ngau_nhien", "10000\n20000\n40000\n50000\n60000\n70000\n100000\n200000\n300000\n500000\n400000\n40000\n15000\n25000\n35000\n45000\n55000\n65000\n45000\n100000\n1500000\n200000");
insert_options("toc_do_gd_nap_ao", 5);
insert_options("method_nap_ao", "ACB\nMB\nUSDT\nPayPal");
insert_options("tao_gd_ao_sp_het_hang", 1);
insert_options("check_time_cron_cron", 0);
insert_options("blog_status", 1);
insert_options("cong_tien_nguoi_ban", 0);
insert_options("noti_buy_product", "[{time}] <b>{username}</b> vừa mua {amount} tài khoản {product} với giá {pay} - #{trans_id}");
insert_options("time_cron_suppliers_api20", 0);
insert_options("debug_auto_bank", 0);
insert_options("time_cron_suppliers_api9", 0);
insert_options("debug_api_suppliers", 1);
insert_options("order_by_product_home", 1);
insert_options("token_webhook_web2m", "");
insert_options("time_cron_suppliers_api21", 0);
insert_options("time_cron_suppliers_api17", 0);
insert_options("api_check_live_gmail", "");
insert_options("api_key_check_live_gmail", "");
insert_options("time_cron_checklive_gmail", 0);
insert_options("time_limit_check_live_gmail", 1800);
insert_options("widget_zalo1_status", 0);
insert_options("widget_zalo1_sdt", "");
insert_options("widget_phone1_status", 0);
insert_options("widget_phone1_sdt", "");
insert_options("flutterwave_status", 0);
insert_options("flutterwave_rate", 16);
insert_options("flutterwave_currency_code", "NGN");
insert_options("flutterwave_publicKey", "");
insert_options("flutterwave_secretKey", "");
insert_options("flutterwave_notice", "");
insert_options("limit_block_ip_login", 5);
insert_options("limit_block_client_login", 10);
insert_options("limit_block_ip_api", 20);
insert_options("limit_block_ip_admin_access", 5);
insert_options("isPurchaseIpVerified", 0);
insert_options("isPurchaseDeviceVerified", 0);
insert_options("time_cron_suppliers_api22", 0);
insert_options("time_cron_suppliers_api23", 0);
insert_options("show_btn_category_home", 0);
insert_options("time_cron_suppliers_api24", 0);

?>
