<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Chỉnh sửa API nhà cung cấp", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../libs/suppliers.php";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    if(!($supplier = $CMSNT->get_row("SELECT * FROM `suppliers` WHERE `id` = '" . $id . "' "))) {
        redirect(base_url_admin("product-api"));
    }
} else {
    redirect(base_url_admin("product-api"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
require_once __DIR__ . "/../../models/is_license.php";
if(!checkPermission($getUser["admin"], "manager_suppliers")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["save"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["type"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng chọn loại API cần kết nối\")){window.history.back().location.reload();}</script>");
    }
    $type = check_string($_POST["type"]);
    if(empty($_POST["domain"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập domain cần kết nối\")){window.history.back().location.reload();}</script>");
    }
    $domain = check_string($_POST["domain"]);
    $price = "";
    $token = !empty($_POST["token"]) ? check_string($_POST["token"]) : NULL;
    if($type == "SHOPCLONE6") {
        $checkdomain = curl_get("https://api.cmsnt.co/checkdomain.php?domain=" . $domain);
        $checkdomain = json_decode($checkdomain, true);
        if(!$checkdomain["status"]) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $checkdomain["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        $data = curl_get(check_string($_POST["domain"]) . "/api/GetBalance.php?username=" . check_string($_POST["username"]) . "&password=" . check_string($_POST["password"]));
        $price = $data;
        $data = json_decode($data, true);
        if(isset($data["status"]) && $data["status"] == "error") {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $data["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
    }
    if($type == "SHOPCLONE7") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập api_key\")){window.history.back().location.reload();}</script>");
        }
        $checkdomain = curl_get("https://api.cmsnt.co/checkdomain.php?domain=" . $domain);
        $checkdomain = json_decode($checkdomain, true);
        if(!$checkdomain["status"]) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $checkdomain["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        $response = balance_API_SHOPCLONE7(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($response, true);
        if($result["status"] == "error") {
            $price = $result["msg"];
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["data"]["money"]);
    }
    if($type == "API_1") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập api_key\")){window.history.back().location.reload();}</script>");
        }
        $response = balance_API_1(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($response, true);
        if(!$result["status"]) {
            $price = $result["msg"];
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["balance"]);
    }
    if($type == "API_4") {
        if(empty($_POST["username"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập username\")){window.history.back().location.reload();}</script>");
        }
        if(empty($_POST["password"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập password\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_4(check_string($_POST["domain"]), check_string($_POST["username"]), check_string($_POST["password"]));
        $result = json_decode($result, true);
        if(!isset($result["data"]["Data"]["userDetail"]["coin"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Thông tin kết nối không chính xác\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency(check_string($result["data"]["Data"]["userDetail"]["coin"]));
        $token = check_string($result["data"]["Data"]["accessToken"]);
    }
    if($type == "API_6") {
        $result = balance_API_6(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if(!isset($result["balance"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["balance"]);
    }
    if($type == "API_9") {
        $result = balance_API_9(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if($result["error"] != 0) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["data"]["balance"]);
    }
    if($type == "API_14") {
        $result = balance_API_14(check_string($_POST["domain"]), check_string($_POST["token"]));
        $result = json_decode($result, true);
        if(!isset($result["user"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["user"]["balance"]);
    }
    if($type == "API17") {
        if(empty($_POST["username"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập username\")){window.history.back().location.reload();}</script>");
        }
        $username = check_string($_POST["username"]);
        if(empty($_POST["password"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập password\")){window.history.back().location.reload();}</script>");
        }
        $password = check_string($_POST["password"]);
        $data = balance_API_17(check_string($_POST["domain"]), $username, $password);
        $price = $data;
        $data = json_decode($data, true);
        if(isset($data["status"]) && $data["status"] == "error") {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $data["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
    }
    if($type == "API_18") {
        $result = balance_API_18(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if(isset($result["error"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["error"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = "\$" . $result["balance"];
    }
    if($type == "API_19") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập api_key\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_19(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if(!isset($result["balance"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["balance"]);
    }
    if($type == "API_20") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập kioskToken\")){window.history.back().location.reload();}</script>");
        }
        if(empty($_POST["token"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập userToken\")){window.history.back().location.reload();}</script>");
        }
        $result = curl_get(check_string($_POST["domain"]) . "api/getStock?kioskToken=" . check_string($_POST["api_key"]) . "&userToken=" . check_string($_POST["token"]));
        $result = json_decode($result, true);
        if(!$result["success"]) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["description"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = check_string($result["name"]);
    }
    if($type == "API_21") {
        if(empty($_POST["token"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập token\")){window.history.back().location.reload();}</script>");
        }
        $price = "Không có API lấy số dư";
    }
    if($type == "API_22") {
        if(empty($_POST["token"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập token\")){window.history.back().location.reload();}</script>");
        }
        $price = "Không có API lấy số dư";
    }
    if($type == "API_23") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập kioskToken\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_23(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if($result["Code"] != 0) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["Message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = check_string("\$" . $result["Balance"]);
    }
    if($type == "API_24") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập api_key\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_24(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if(!isset($result["data"][0]["money_available"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"[SYSTEM] Thông tin kết nối không chính xác\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency(check_string($result["data"][0]["money_available"]));
    }
    $isUpdate = $CMSNT->update("suppliers", ["type" => $type, "domain" => $domain, "username" => !empty($_POST["username"]) ? check_string($_POST["username"]) : NULL, "password" => !empty($_POST["password"]) ? check_string($_POST["password"]) : NULL, "api_key" => !empty($_POST["api_key"]) ? check_string($_POST["api_key"]) : NULL, "token" => $token, "coupon" => !empty($_POST["coupon"]) ? check_string($_POST["coupon"]) : NULL, "price" => check_string($price), "check_string_api" => check_string($_POST["check_string_api"]), "discount" => check_string($_POST["discount"]), "update_name" => check_string($_POST["update_name"]), "sync_category" => !empty($_POST["sync_category"]) ? check_string($_POST["sync_category"]) : "OFF", "update_price" => check_string($_POST["update_price"]), "roundMoney" => check_string($_POST["roundMoney"]), "update_gettime" => gettime()], " `id` = '" . $supplier["id"] . "' ");
    if($isUpdate) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Edit API Supplier (" . $supplier["domain"] . " ID " . $supplier["id"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Edit API Supplier (" . $supplier["domain"] . " ID " . $supplier["id"] . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công!\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại!\")){window.history.back().location.reload();}</script>");
}
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><a type=\"button\"\n                    class=\"btn btn-dark btn-raised-shadow btn-wave btn-sm me-1\"\n                    href=\"";
echo base_url_admin("product-api");
echo "\"><i class=\"fa-solid fa-arrow-left\"></i></a> Chỉnh sửa API\n                nhà cung cấp ";
echo $supplier["domain"];
echo "            </h1>\n        </div>\n        ";
if($supplier["type"] == "SHOPCLONE6" && 120 <= time() - $CMSNT->site("time_cron_suppliers_shopclone6")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/shopclone6.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/shopclone6.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "SHOPCLONE7" && 120 <= time() - $CMSNT->site("time_cron_suppliers_shopclone7")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/shopclone7.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/shopclone7.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_1" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api1")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api1.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api1.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_6" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api6")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api6.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api6.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_14" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api14")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api14.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api14.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_17" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api17")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api17.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api17.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_18" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api18")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api18.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api18.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_19" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api19")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api19.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api19.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_4" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api4")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api4.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api4.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_20" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api20")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api20.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api20.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_9" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api9")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api9.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api9.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_21" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api21")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api21.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api21.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_22" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api22")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api22.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api22.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_23" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api23")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api23.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api23.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        ";
if($supplier["type"] == "API_24" && 120 <= time() - $CMSNT->site("time_cron_suppliers_api24")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
    echo base_url("cron/suppliers/api24.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/suppliers/api24.php");
    echo "</a> 1 phút 1 lần để hệ thống\n            tự động cập nhật dữ liệu từ API.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n            <div class=\"row\">\n                <div class=\"col-xl-8\">\n                    <div class=\"card custom-card\">\n                        <div class=\"card-header justify-content-between\">\n                            <div class=\"card-title\">\n                                CHỈNH SỬA KẾT NỐI API\n                            </div>\n                        </div>\n                        <div class=\"card-body\">\n                            <div class=\"row mb-3 gy-2\">\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Loại API:");
echo " <span\n                                            class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" id=\"api-select\" name=\"type\" required>\n                                        <option value=\"\">-- Chọn loại API --</option>\n                                        <option ";
echo $supplier["type"] == "SHOPCLONE7" ? "selected" : "";
echo "                                            value=\"SHOPCLONE7\">\n                                            SHOPCLONE7 CMSNT</option>\n                                        <option ";
echo $supplier["type"] == "SHOPCLONE6" ? "selected" : "";
echo "                                            value=\"SHOPCLONE6\">\n                                            SHOPCLONE5 & SHOPCLONE6 CMSNT</option>\n                                        <option ";
echo $supplier["type"] == "API_1" ? "selected" : "";
echo " value=\"API_1\">API\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_4" ? "selected" : "";
echo " value=\"API_4\">API\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_6" ? "selected" : "";
echo " value=\"API_6\">API\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_9" ? "selected" : "";
echo " value=\"API_9\">API\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_14" ? "selected" : "";
echo " value=\"API_14\">API\n\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_17" ? "selected" : "";
echo " value=\"API_17\">API\n\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_18" ? "selected" : "";
echo " value=\"API_18\">API\n\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_19" ? "selected" : "";
echo " value=\"API_19\">API\n\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_20" ? "selected" : "";
echo " value=\"API_20\">API\n\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_21" ? "selected" : "";
echo " value=\"API_21\">API\n\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_22" ? "selected" : "";
echo " value=\"API_22\">API\n\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_23" ? "selected" : "";
echo " value=\"API_23\">API\n\n                                        </option>\n                                        <option ";
echo $supplier["type"] == "API_24" ? "selected" : "";
echo " value=\"API_24\">API\n\n                                        </option>\n                                    </select>\n                                </div>\n                                <script>\n                                function shuffleOptions(selectElement) {\n                                    // Lấy tất cả các option\n                                    let options = Array.from(selectElement.options);\n\n                                    // Lấy các option từ vị trí thứ 4 trở đi (index = 3)\n                                    let optionsToShuffle = options.slice(3);\n\n                                    // Random thứ tự các option này\n                                    for (let i = optionsToShuffle.length - 1; i > 0; i--) {\n                                        let j = Math.floor(Math.random() * (i + 1));\n                                        [optionsToShuffle[i], optionsToShuffle[j]] = [optionsToShuffle[j],\n                                            optionsToShuffle[i]\n                                        ];\n                                    }\n\n                                    // Xóa hết các option cũ\n                                    selectElement.innerHTML = '';\n\n                                    // Thêm lại các option không bị random\n                                    selectElement.appendChild(options[0]);\n                                    selectElement.appendChild(options[1]);\n                                    selectElement.appendChild(options[2]);\n\n                                    // Thêm lại các option đã được random\n                                    optionsToShuffle.forEach(option => selectElement.appendChild(option));\n                                }\n\n                                document.addEventListener(\"DOMContentLoaded\", function() {\n                                    const selectElement = document.getElementById(\"api-select\");\n                                    shuffleOptions(selectElement);\n                                });\n                                </script>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Domain");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" placeholder=\"Link Website cần kết nối\"\n                                        name=\"domain\" value=\"";
echo $supplier["domain"];
echo "\" required>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Số dư:");
echo "</label>\n                                    <textarea class=\"form-control\" readonly>";
echo $supplier["price"];
echo "</textarea>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"username\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Username:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"username\"\n                                        value=\"";
echo $supplier["username"];
echo "\"\n                                        placeholder=\"";
echo __("Nhập tên đăng nhập website API");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"password\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Password:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"password\"\n                                        value=\"";
echo $supplier["password"];
echo "\"\n                                        placeholder=\"";
echo __("Nhập mật khẩu đăng nhập website API");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"api_key\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("API Key:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"api_key\"\n                                        value=\"";
echo $supplier["api_key"];
echo "\"\n                                        placeholder=\"";
echo __("Nhập Api Key trong website API");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"token\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Token:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"token\"\n                                        value=\"";
echo $supplier["token"];
echo "\"\n                                        placeholder=\"";
echo __("Nhập Token trong website API");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"coupon\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Coupon:");
echo "</label>\n                                    <input type=\"text\" class=\"form-control\" name=\"coupon\"\n                                        placeholder=\"";
echo __("Nhập mã giảm giá nếu có");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"sync_category\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Đồng bộ chuyên mục từ API\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" name=\"sync_category\" required>\n                                        <option ";
echo $supplier["sync_category"] == "OFF" ? "selected" : "";
echo "  value=\"OFF\">OFF</option>\n                                        <option ";
echo $supplier["sync_category"] == "ON" ? "selected" : "";
echo " value=\"ON\">ON</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Cập nhật giá bán tự động\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" data-trigger name=\"update_price\" required>\n                                        <option ";
echo $supplier["update_price"] == "ON" ? "selected" : "";
echo " value=\"ON\">ON\n                                        </option>\n                                        <option ";
echo $supplier["update_price"] == "OFF" ? "selected" : "";
echo " value=\"OFF\">\n                                            OFF</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Làm tròn giá bán\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" data-trigger name=\"roundMoney\" required>\n                                        <option ";
echo $supplier["roundMoney"] == "ON" ? "selected" : "";
echo " value=\"ON\">ON\n                                        </option>\n                                        <option ";
echo $supplier["roundMoney"] == "OFF" ? "selected" : "";
echo " value=\"OFF\">OFF\n                                        </option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Nâng giá tự động so với giá gốc\n                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" value=\"";
echo $supplier["discount"];
echo "\"\n                                        placeholder=\"Ví dụ: nhập 10 hệ thống sẽ tăng giá bán 10% so với giá gốc, nhập 0 để chỉnh giá như giá gốc\"\n                                        name=\"discount\" required>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Cập nhật tên sản phẩm, mô tả ngắn\n                                        tự động\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" data-trigger name=\"update_name\" required>\n                                        <option ";
echo $supplier["update_name"] == "ON" ? "selected" : "";
echo " value=\"ON\">ON\n                                        </option>\n                                        <option ";
echo $supplier["update_name"] == "OFF" ? "selected" : "";
echo " value=\"OFF\">\n                                            OFF</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Lọc HTML trong Tên sản phẩm & mô tả\n                                        sản phẩm trong API\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" name=\"check_string_api\" required>\n                                        <option ";
echo $supplier["check_string_api"] == "ON" ? "selected" : "";
echo "                                            value=\"ON\">ON</option>\n                                        <option ";
echo $supplier["check_string_api"] == "OFF" ? "selected" : "";
echo "                                            value=\"OFF\">OFF</option>\n                                    </select>\n                                    <small>Khi bật chức năng này hệ thống sẽ lọc Tên sản phẩm & mô tả sản phẩm của bên\n                                        API tránh việc bên API cố tình chèn bug vào website bạn.</small>\n                                </div>\n                            </div>\n                            <div class=\"d-grid gap-2 mb-3\">\n                                <button type=\"submit\" name=\"save\" class=\"btn btn-primary shadow-primary\"><i\n                                        class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"col-xl-4\">\n                    <div class=\"card custom-card\">\n                        <div class=\"card-body\">\n                            <p>Chức năng này cho phép quý khách bán lại sản phẩm của website khác trên chính website của\n                                quý khách.</p>\n                            <p>Trong trường hợp quý khách cấu hình đúng nhưng không hiện số dư của API => do máy chủ của\n                                quý khách không thể cURL đến được máy chủ của họ, trường hợp này chúng tôi sẽ không giúp\n                                ngoài việc chờ đợi hoặc làm theo hướng dẫn sau <a class=\"text-primary\" target=\"_blank\"\n                                    href=\"https://help.cmsnt.co/huong-dan/ket-noi-api-nhap-dung-thong-tin-nhung-khong-ra-so-du-thi-lam-sao/\">đây</a>.\n                            </p>\n                            <p>Kết nối API website cùng hệ sinh thái CMSNT sẽ miễn phí, tích hợp API website khác hệ\n                                sinh thái chúng tôi sẽ tính phí 200.000đ / 1 lần thêm kết nối (tính theo số lần thêm API\n                                chứ không phải tính theo web, ví dụ quý khách cần tôi thêm API websitecandau.com giá\n                                200.000đ, sau đó quý khách xóa websitecandau.com ra khỏi hệ thống, chúng tôi sẽ tiếp tục\n                                tính phí 200.000đ khi quý khách cần chúng tôi thêm lại).</p>\n                            <p>Liên hệ hỗ trợ kết nối API ngoài hệ thống quý khách nhấn vào <a\n                                    href=\"https://www.cmsnt.co/p/contact.html\" class=\"text-primary\"\n                                    target=\"_blank\">đây</a>.</p>\n                        </div>\n                    </div>\n                </div>\n        </form>\n    </div>\n</div>\n</div>\n\n\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nvar lightboxVideo = GLightbox({\n    selector: '.glightbox'\n});\n\nCKEDITOR.replace(\"description\");\nCKEDITOR.replace(\"note\");\n\nfunction removeImageProduct(id, image) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa ảnh\",\n        message: \"Bạn có chắc chắn muốn xóa ảnh \" + id + \" không ?\",\n        confirmText: \"Đồng Ý\",\n        cancelText: \"Hủy\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    id: id,\n                    image: image,\n                    action: 'removeImageProduct'\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n</script>\n\n\n<script>\ndocument.addEventListener(\"DOMContentLoaded\", function() {\n    const typeSelect = document.querySelector(\"select[name='type']\");\n    const usernameField = document.getElementById(\"username\");\n    const passwordField = document.getElementById(\"password\");\n    const apiKeyField = document.getElementById(\"api_key\");\n    const tokenField = document.getElementById(\"token\");\n    const couponField = document.getElementById(\"coupon\");\n    const sync_category = document.getElementById(\"sync_category\");\n\n    function toggleFields() {\n        const selectedType = typeSelect.value;\n        usernameField.style.display = \"none\";\n        passwordField.style.display = \"none\";\n        apiKeyField.style.display = \"none\";\n        tokenField.style.display = \"none\";\n        couponField.style.display = \"none\";\n        sync_category.style.display = \"none\";\n\n        if (selectedType === \"SHOPCLONE6\") {\n            sync_category.style.display = \"block\";\n            usernameField.style.display = \"block\";\n            passwordField.style.display = \"block\";\n        } else if (selectedType === \"SHOPCLONE7\") {\n            sync_category.style.display = \"block\";\n            apiKeyField.style.display = \"block\";\n            couponField.style.display = \"block\";\n        } else if (selectedType === \"API_4\" || selectedType === \"API_17\") {\n            usernameField.style.display = \"block\";\n            passwordField.style.display = \"block\";\n        } else if (selectedType === \"API_1\" || selectedType === \"API_6\" || selectedType === \"API_18\" ||\n            selectedType === \"API_19\" || selectedType === \"API_9\" || selectedType === \"API_23\" || selectedType === \"API_24\") {\n            apiKeyField.style.display = \"block\";\n        } else if (selectedType === \"API_14\" || selectedType === \"API_21\" || selectedType === \"API_22\") {\n            tokenField.style.display = \"block\";\n        } else if (selectedType === \"API_20\") {\n            apiKeyField.style.display = \"block\";\n            tokenField.style.display = \"block\";\n        }\n    }\n    toggleFields();\n    typeSelect.addEventListener(\"change\", toggleFields);\n});\n</script>";

?>