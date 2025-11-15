<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Quản lý API nhà cung cấp", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css\">\n";
$body["footer"] = "\n<!-- Datatables Cdn -->\n<script src=\"https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js\"></script>\n<script src=\"https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js\"></script>\n<script src=\"https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js\"></script>\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js\"></script>\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js\"></script>\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js\"></script>\n";
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
    if($type == "SHOPCLONE6") {
        $data = curl_get(check_string($_POST["domain"]) . "/api/GetBalance.php?username=" . check_string($_POST["username"]) . "&password=" . check_string($_POST["password"]));
        $price = $data;
        $data = json_decode($data, true);
        if(isset($data["status"]) && $data["status"] == "error") {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $data["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
    }
    if($type == "API_1") {
        $curl = curl_init();
        curl_setopt_array($curl, [CURLOPT_URL => $domain . "api/v1/balance", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["api_key" => check_string($_POST["token"])]]);
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true);
        $price = $data["balance"];
        if(!$data["status"]) {
            $price = $data["msg"];
            exit("<script type=\"text/javascript\">if(!alert(\"" . $data["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
    }
    if($type == "API_4") {
        $data = curl_get(check_string($_POST["domain"]) . "/api.php?apikey=" . check_string($_POST["api_key"]) . "&action=get-balance");
        $data = json_decode($data, true);
        $price = $data["balance"];
        if(!isset($data["balance"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $data["message"] . "\")){window.history.back().location.reload();}</script>");
        }
    }
    $isUpdate = $CMSNT->update("suppliers", ["type" => $type, "domain" => $domain, "username" => !empty($_POST["username"]) ? check_string($_POST["username"]) : NULL, "password" => !empty($_POST["password"]) ? check_string($_POST["password"]) : NULL, "api_key" => !empty($_POST["api_key"]) ? check_string($_POST["api_key"]) : NULL, "token" => !empty($_POST["token"]) ? check_string($_POST["token"]) : NULL, "price" => check_string($price), "discount" => check_string($_POST["discount"]), "update_name" => check_string($_POST["update_name"]), "update_price" => check_string($_POST["update_price"]), "update_gettime" => gettime()], " `id` = '" . $supplier["id"] . "' ");
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
if(isset($_GET["limit"])) {
    $limit = (int) check_string($_GET["limit"]);
} else {
    $limit = 10;
}
if(isset($_GET["page"])) {
    $page = check_string((int) $_GET["page"]);
} else {
    $page = 1;
}
$from = ($page - 1) * $limit;
$where = " `supplier_id` = '" . $id . "' ";
$user_id = "";
$name = "";
$create_gettime = "";
$username = "";
$shortByDate = "";
$category_id = "";
$status = "";
if(!empty($_GET["status"])) {
    $status = check_string($_GET["status"]);
    if($status == 2) {
        $where .= " AND `status` = 0 ";
    } elseif($status == 1) {
        $where .= " AND `status` = 1 ";
    }
}
if(!empty($_GET["category_id"])) {
    $category_id = check_string($_GET["category_id"]);
    $where .= " AND `category_id` = \"" . $category_id . "\" ";
}
if(!empty($_GET["username"])) {
    $username = check_string($_GET["username"]);
    if($idUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `username` = '" . $username . "' ")) {
        $where .= " AND `user_id` =  \"" . $idUser["id"] . "\" ";
    } else {
        $where .= " AND `user_id` =  \"\" ";
    }
}
if(!empty($_GET["user_id"])) {
    $user_id = check_string($_GET["user_id"]);
    $where .= " AND `user_id` = \"" . $user_id . "\" ";
}
if(!empty($_GET["name"])) {
    $name = check_string($_GET["name"]);
    $where .= " AND `name` LIKE \"%" . $name . "%\" ";
}
if(!empty($_GET["create_gettime"])) {
    $create_gettime = check_string($_GET["create_gettime"]);
    $createdate = $create_gettime;
    $create_gettime_1 = str_replace("-", "/", $create_gettime);
    $create_gettime_1 = explode(" to ", $create_gettime_1);
    if($create_gettime_1[0] != $create_gettime_1[1]) {
        $create_gettime_1 = [$create_gettime_1[0] . " 00:00:00", $create_gettime_1[1] . " 23:59:59"];
        $where .= " AND `create_gettime` >= '" . $create_gettime_1[0] . "' AND `create_gettime` <= '" . $create_gettime_1[1] . "' ";
    }
}
if(isset($_GET["shortByDate"])) {
    $shortByDate = check_string($_GET["shortByDate"]);
    $yesterday = date("Y-m-d", strtotime("-1 day"));
    $currentWeek = date("W");
    $currentMonth = date("m");
    $currentYear = date("Y");
    $currentDate = date("Y-m-d");
    if($shortByDate == 1) {
        $where .= " AND `create_gettime` LIKE '%" . $currentDate . "%' ";
    }
    if($shortByDate == 2) {
        $where .= " AND YEAR(create_gettime) = " . $currentYear . " AND WEEK(create_gettime, 1) = " . $currentWeek . " ";
    }
    if($shortByDate == 3) {
        $where .= " AND MONTH(create_gettime) = '" . $currentMonth . "' AND YEAR(create_gettime) = '" . $currentYear . "' ";
    }
}
$listDatatable = $CMSNT->get_list(" SELECT * FROM `products` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `products` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("product-api-manager&limit=" . $limit . "&shortByDate=" . $shortByDate . "&user_id=" . $user_id . "&name=" . $name . "&create_gettime=" . $create_gettime . "&username=" . $username . "&category_id=" . $category_id . "&id=" . $id . "&"), $from, $totalDatatable, $limit);
$yesterday = date("Y-m-d", strtotime("-1 day"));
$currentWeek = date("W");
$currentMonth = date("m");
$currentYear = date("Y");
$currentDate = date("Y-m-d");
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><a type=\"button\"\n                    class=\"btn btn-dark btn-raised-shadow btn-wave btn-sm me-1\"\n                    href=\"";
echo base_url_admin("product-api");
echo "\"><i class=\"fa-solid fa-arrow-left\"></i></a> Quản lý API\n                ";
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
echo "        <ul class=\"nav nav-tabs tab-style-1\" role=\"tablist\">\n            <li class=\"nav-item\">\n                <a class=\"nav-link\" data-bs-toggle=\"tab\" data-bs-target=\"#tab1\" aria-current=\"page\" href=\"#tab1\"><i\n                        class=\"fa-solid fa-chart-pie\"></i> Thống kê</a>\n            </li>\n            <li class=\"nav-item\">\n                <a class=\"nav-link\" data-bs-toggle=\"tab\" data-bs-target=\"#tab_category\" href=\"#tab_category\"><i\n                        class=\"fa-solid fa-list-ul\"></i> Chuyên mục</a>\n            </li>\n            <li class=\"nav-item\">\n                <a class=\"nav-link\" data-bs-toggle=\"tab\" data-bs-target=\"#tab2\" href=\"#tab2\"><i\n                        class=\"fa-solid fa-list\"></i> Sản phẩm</a>\n            </li>\n            <li class=\"nav-item\">\n                <a class=\"nav-link\" href=\"";
echo base_url_admin("product-orders&supplier_id=" . $supplier["id"]);
echo "\"><i\n                        class=\"fa-solid fa-cart-shopping\"></i> Đơn hàng</a>\n            </li>\n            <li class=\"nav-item\">\n                <a class=\"nav-link\" href=\"";
echo base_url_admin("product-api-edit&id=" . $id);
echo "\"><i\n                        class=\"fa-solid fa-gear\"></i> Chỉnh sửa kết nối</a>\n            </li>\n        </ul>\n        <div class=\"tab-content\">\n            <div class=\"tab-pane show text-muted\" id=\"tab1\" role=\"tabpanel\">\n                ";
$doanh_thu = $CMSNT->get_row(" SELECT SUM(pay) FROM product_order WHERE `refund` = 0 AND supplier_id = '" . $id . "' ")["SUM(pay)"];
$tien_von = $CMSNT->get_row(" SELECT SUM(cost) FROM product_order WHERE `refund` = 0 AND supplier_id = '" . $id . "' ")["SUM(cost)"];
$loi_nhuan = $doanh_thu - $tien_von;
echo "                <div class=\"row\">\n                    <div class=\"col\">\n                        <div class=\"card custom-card\">\n                            <div class=\"card-body\">\n                                <div class=\"d-flex align-items-top\">\n                                    <div class=\"me-3\">\n                                        <span class=\"avatar avatar-md p-2 bg-primary\">\n                                            <i class=\"fa-solid fa-cart-shopping fs-16\"></i>\n                                        </span>\n                                    </div>\n                                    <div class=\"flex-fill\">\n                                        <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                            <h5 class=\"fw-semibold mb-0 lh-1\">\n                                                ";
echo format_cash($CMSNT->get_row(" SELECT COUNT(id) FROM product_order WHERE refund = 0 AND supplier_id = '" . $id . "' ")["COUNT(id)"]);
echo "                                            </h5>\n                                        </div>\n                                        <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">ĐƠN HÀNG</p>\n                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"col\">\n                        <div class=\"card custom-card\">\n                            <div class=\"card-body\">\n                                <div class=\"d-flex align-items-top\">\n                                    <div class=\"me-3\">\n                                        <span class=\"avatar avatar-md p-2 bg-info\">\n                                            <i class=\"fa-solid fa-money-bill-1 fs-16\"></i>\n                                        </span>\n                                    </div>\n                                    <div class=\"flex-fill\">\n                                        <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                            <h5 class=\"fw-semibold mb-0 lh-1\">\n                                                ";
echo format_currency($doanh_thu);
echo "                                            </h5>\n                                        </div>\n                                        <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">DOANH THU ĐƠN HÀNG</p>\n                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"col\">\n                        <div class=\"card custom-card\">\n                            <div class=\"card-body\">\n                                <div class=\"d-flex align-items-top\">\n                                    <div class=\"me-3\">\n                                        <span class=\"avatar avatar-md p-2 bg-warning\">\n                                            <i class=\"fa-solid fa-money-bill-1 fs-16\"></i>\n                                        </span>\n                                    </div>\n                                    <div class=\"flex-fill\">\n                                        <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                            <h5 class=\"fw-semibold mb-0 lh-1\">\n                                                ";
echo format_currency($tien_von);
echo "                                            </h5>\n                                        </div>\n                                        <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">GIÁ VỐN</p>\n                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"col\">\n                        <div class=\"card custom-card\">\n                            <div class=\"card-body\">\n                                <div class=\"d-flex align-items-top\">\n                                    <div class=\"me-3\">\n                                        <span class=\"avatar avatar-md p-2 bg-success\">\n                                            <i class=\"fa-solid fa-money-bill-1 fs-16\"></i>\n                                        </span>\n                                    </div>\n                                    <div class=\"flex-fill\">\n                                        <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                            <h5 class=\"fw-semibold mb-0 lh-1\">\n                                                ";
echo format_currency($loi_nhuan);
echo "                                            </h5>\n                                        </div>\n                                        <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">LỢI NHUẬN</p>\n                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"row\">\n                    ";
$month = date("m");
$year = date("Y");
$numOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$labels = [];
$revenues = [];
$profits = [];
for ($day = 1; $day <= $numOfDays; $day++) {
    $date = $year . "-" . $month . "-" . $day;
    $query = "SELECT SUM(pay), SUM(cost) FROM product_order WHERE `supplier_id` = " . $id . " AND `refund` = 0 AND DATE(create_gettime) = '" . $date . "'";
    $result = $CMSNT->get_row($query);
    $labels[] = $day . "/" . $month . "/" . $year;
    $revenues[] = $result["SUM(pay)"];
    $profits[] = $result["SUM(pay)"] - $result["SUM(cost)"];
}
echo "                    <div class=\"col-xl-12\">\n                        <div class=\"card custom-card\">\n                            <div class=\"card-header\">\n                                <div class=\"card-title\">THỐNG KÊ ĐƠN HÀNG THÁNG ";
echo date("m");
echo "</div>\n                            </div>\n                            <div class=\"card-body\">\n                                <canvas id=\"chartjs-line\" class=\"chartjs-chart\"></canvas>\n                                <script>\n                                (function() {\n                                    Chart.defaults.borderColor = \"rgba(142, 156, 173,0.1)\";\n                                    Chart.defaults.color = \"#8c9097\";\n\n                                    const labels = ";
echo json_encode($labels);
echo ";\n                                    const revenues = ";
echo json_encode($revenues);
echo ";\n                                    const profits = ";
echo json_encode($profits);
echo ";\n\n                                    const data = {\n                                        labels: labels,\n                                        datasets: [{\n                                                label: 'Doanh thu',\n                                                backgroundColor: 'rgb(132, 90, 223)',\n                                                borderColor: 'rgb(132, 90, 223)',\n                                                data: revenues,\n                                            },\n                                            {\n                                                label: 'Lợi nhuận',\n                                                backgroundColor: 'rgb(73,182,245)',\n                                                borderColor: 'rgb(73,182,245)',\n                                                data: profits,\n                                            }\n                                        ]\n                                    };\n\n                                    const config = {\n                                        type: 'bar',\n                                        data: data,\n                                        options: {}\n                                    };\n\n                                    const myChart = new Chart(\n                                        document.getElementById('chartjs-line'),\n                                        config\n                                    );\n                                })();\n                                </script>\n                            </div>\n                        </div>\n                    </div>\n\n                </div>\n            </div>\n            <div class=\"tab-pane text-muted\" id=\"tab_category\" role=\"tabpanel\">\n                <div class=\"row\">\n                    <div class=\"col-xl-12\">\n                        <div class=\"card custom-card\">\n                            <div class=\"card-header justify-content-between\">\n                                <div class=\"card-title\">\n                                    DANH SÁCH CHUYÊN MỤC API\n                                </div>\n                            </div>\n                            <div class=\"card-body\">\n                                <div class=\"table-responsive table-wrapper\">\n                                    <table id=\"datatable-basic\" class=\"table table-bordered text-nowrap w-100\">\n                                        <thead>\n                                            <tr>\n                                                <th class=\"text-center\">\n                                                    <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                        <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                                            id=\"check_all_checkbox_category_api\" value=\"option1\">\n                                                    </div>\n                                                </th>\n                                                <th>Tên chuyên mục con</th>\n                                                <th>Chuyên mục cha</th>\n                                                <th>Thống kê</th>\n                                                <th>Ảnh</th>\n                                                <th>Trạng thái</th>\n                                                <th>Thao tác</th>\n                                            </tr>\n                                        </thead>\n                                        <tbody>\n                                            ";
$i = 0;
foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `supplier_id` = '" . $id . "' ") as $cate) {
    echo "                                            <tr>\n                                                <td class=\"text-center\">\n                                                    <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                        <input type=\"checkbox\"\n                                                            class=\"form-check-input checkbox_category_api\"\n                                                            data-id=\"";
    echo $cate["id"];
    echo "\" name=\"checkbox_category_api\"\n                                                            value=\"";
    echo $cate["id"];
    echo "\" />\n                                                    </div>\n                                                </td>\n                                                <td>";
    echo $cate["name"];
    echo "</td>\n                                                <td>\n                                                    ";
    if(1 < $cate["parent_id"]) {
        echo "                                                    <a class=\"text-primary\"\n                                                        href=\"";
        echo base_url_admin("category-edit&id=" . $cate["parent_id"]);
        echo "\"><i\n                                                            class=\"fa-solid fa-pen-to-square\"></i>\n                                                        ";
        echo getRowRealtime("categories", $cate["parent_id"], "name");
        echo "</a>\n                                                    ";
    }
    echo "                                                </td>\n                                                <td>\n                                                    <span class=\"badge bg-outline-primary\">Sản phẩm:\n                                                        ";
    echo format_cash($CMSNT->num_rows(" SELECT * FROM `products` WHERE `category_id` = '" . $cate["id"] . "' "));
    echo "</span><br>\n                                                </td>\n                                                <td><img src=\"";
    echo base_url($cate["icon"]);
    echo "\" width=\"40px\"></td>\n                                                <td class=\"text-center\">\n                                                    <div class=\"form-check form-switch form-check-lg\">\n                                                        <input class=\"form-check-input\" type=\"checkbox\"\n                                                            id=\"list_category_status";
    echo $cate["id"];
    echo "\" value=\"1\"\n                                                            ";
    echo $cate["status"] == 1 ? "checked=\"\"" : "";
    echo ">\n                                                    </div>\n                                                </td>\n                                                <td>\n                                                    <a type=\"button\"\n                                                        href=\"";
    echo base_url_admin("category-edit&id=" . $cate["id"]);
    echo "\"\n                                                        class=\"btn btn-info shadow-info btn-wave btn-sm\"\n                                                        data-bs-toggle=\"tooltip\" title=\"";
    echo __("Edit");
    echo "\">\n                                                        <i class=\"fa-solid fa-pen-to-square\"></i> Edit\n                                                    </a>\n                                                    <a type=\"button\" onclick=\"deleteCategory('";
    echo $cate["id"];
    echo "')\"\n                                                        id=\"btnDeleteCategory";
    echo $cate["id"];
    echo "\"\n                                                        class=\"btn btn-danger shadow-danger btn-wave btn-sm\"\n                                                        data-bs-toggle=\"tooltip\" title=\"";
    echo __("Delete");
    echo "\">\n                                                        <i class=\"fas fa-trash\"></i> Delete\n                                                    </a>\n                                                </td>\n                                            </tr>\n                                            ";
}
echo "                                        </tbody>\n                                        <tfoot>\n                                            <td colspan=\"9\">\n                                                <div class=\"btn-list\">\n                                                    <button type=\"button\" id=\"btn_edit_category_category\"\n                                                        class=\"btn btn-outline-secondary shadow-secondary btn-wave btn-sm\"><i\n                                                            class=\"fa-solid fa-pen-to-square\"></i> CHỈNH CHUYÊN\n                                                        MỤC CHA</button>\n                                                    <button type=\"button\" id=\"btn_edit_status_category\"\n                                                        class=\"btn btn-outline-success shadow-success btn-wave btn-sm\"><i\n                                                            class=\"fa-solid fa-pen-to-square\"></i> CHỈNH TRẠNG\n                                                        THÁI</button>\n                                                    <button type=\"button\" id=\"btn_delete_category\"\n                                                        class=\"btn btn-outline-danger shadow-danger btn-wave btn-sm\"><i\n                                                            class=\"fa-solid fa-trash\"></i> XÓA CHUYÊN MỤC</button>\n                                                </div>\n                                            </td>\n                                        </tfoot>\n                                    </table>\n                                </div>\n\n                                <div class=\"modal fade\" id=\"modal_edit_category_category\" tabindex=\"-1\"\n                                    aria-labelledby=\"Cập nhật chuyên mục cha\" data-bs-keyboard=\"false\"\n                                    aria-hidden=\"true\">\n                                    <!-- Scrollable modal -->\n                                    <div class=\"modal-dialog modal-dialog-centered\">\n                                        <div class=\"modal-content\">\n                                            <div class=\"modal-header\">\n                                                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Cập nhật chuyên mục\n                                                    cha cho <mark class=\"checkboxeslength\"></mark> chuyên mục đã chọn\n                                                </h6>\n                                                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\"\n                                                    aria-label=\"Close\"></button>\n                                            </div>\n                                            <div class=\"modal-body\">\n                                                <div class=\"row mb-4\">\n                                                    <label class=\"col-sm-4 col-form-label\"\n                                                        for=\"example-hf-email\">";
echo __("Chuyên mục cha:");
echo " <span\n                                                            class=\"text-danger\">*</span></label>\n                                                    <div class=\"col-sm-8\">\n                                                        <select class=\"form-control\" id=\"category_category_id\" required>\n                                                            <option value=\"0\">";
echo __("-- Chọn chuyên mục cha --");
echo "                                                            </option>\n                                                            ";
foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = 0 ") as $option) {
    echo "                                                            <option value=\"";
    echo $option["id"];
    echo "\">\n                                                                ";
    echo $option["name"];
    echo "</option>\n                                                            ";
}
echo "                                                        </select>\n                                                    </div>\n                                                </div>\n                                                <p>Khi bạn nhấn vào nút UPDATE đồng nghĩa các chuyên mục mà bạn đã chọn\n                                                    sẽ\n                                                    được cập nhật chuyên\n                                                    mục cha trên.</p>\n\n                                            </div>\n                                            <div class=\"modal-footer\">\n                                                <button type=\"button\" class=\"btn btn-light\"\n                                                    data-bs-dismiss=\"modal\">Close</button>\n                                                <button type=\"button\" onclick=\"update_category_category_records()\"\n                                                    id=\"update_category_category_records\" class=\"btn btn-primary\"><i\n                                                        class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "</button>\n                                            </div>\n                                        </div>\n                                    </div>\n                                </div>\n\n                                <div class=\"modal fade\" id=\"modal_edit_status_category\" tabindex=\"-1\"\n                                    aria-labelledby=\"modal_edit_category_product\" data-bs-keyboard=\"false\"\n                                    aria-hidden=\"true\">\n                                    <!-- Scrollable modal -->\n                                    <div class=\"modal-dialog modal-dialog-centered\">\n                                        <div class=\"modal-content\">\n                                            <div class=\"modal-header\">\n                                                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Cập nhật trạng thái\n                                                    <mark class=\"checkboxeslength\"></mark> sản phẩm đã chọn\n                                                </h6>\n                                                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\"\n                                                    aria-label=\"Close\"></button>\n                                            </div>\n                                            <div class=\"modal-body\">\n                                                <div class=\"row mb-4\">\n                                                    <label class=\"col-sm-4 col-form-label\"\n                                                        for=\"example-hf-email\">";
echo __("Trạng thái:");
echo " <span\n                                                            class=\"text-danger\">*</span></label>\n                                                    <div class=\"col-sm-8\">\n                                                        <select class=\"form-control\" id=\"category_status\" required>\n                                                            <option value=\"1\">ON</option>\n                                                            <option value=\"0\">OFF</option>\n                                                        </select>\n                                                    </div>\n                                                </div>\n                                                <p>Khi bạn nhấn vào nút UPDATE đồng nghĩa các chuyên mục mà bạn đã chọn sẽ\n                                                    được cập nhật thành trạng\n                                                    thái trên.</p>\n                                            </div>\n                                            <div class=\"modal-footer\">\n                                                <button type=\"button\" class=\"btn btn-light\"\n                                                    data-bs-dismiss=\"modal\">Close</button>\n                                                <button type=\"button\" onclick=\"update_status_category_records()\"\n                                                    id=\"update_status_category_records\" class=\"btn btn-primary\"><i\n                                                        class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "</button>\n                                            </div>\n                                        </div>\n                                    </div>\n                                </div>\n\n                                <script>\n                                \$(function() {\n                                    \$('#check_all_checkbox_category_api').on('click', function() {\n                                        \$('.checkbox_category_api').prop('checked', this.checked);\n                                    });\n                                    \$('.checkbox_category_api').on('click', function() {\n                                        \$('#check_all_checkbox_category_api').prop('checked', \$(\n                                                '.checkbox_category_api:checked')\n                                            .length === \$('.checkbox_category_api').length);\n                                    });\n                                });\n\n                                \$(\"#btn_edit_category_category\").click(function() {\n                                    var checkboxes = document.querySelectorAll(\n                                        'input[name=\"checkbox_category_api\"]:checked');\n                                    if (checkboxes.length === 0) {\n                                        showMessage('Vui lòng chọn ít nhất một chuyên mục.', 'error');\n                                        return;\n                                    }\n                                    \$(\".checkboxeslength\").html(checkboxes.length);\n                                    \$(\"#modal_edit_category_category\").modal('show');\n                                });\n\n                                \$(\"#btn_edit_status_category\").click(function() {\n                                    var checkboxes = document.querySelectorAll(\n                                        'input[name=\"checkbox_category_api\"]:checked');\n                                    if (checkboxes.length === 0) {\n                                        showMessage('Vui lòng chọn ít nhất một chuyên mục.', 'error');\n                                        return;\n                                    }\n                                    \$(\".checkboxeslength\").html(checkboxes.length);\n                                    \$(\"#modal_edit_status_category\").modal('show');\n                                });\n\n                                \$(\"#btn_delete_category\").click(function() {\n                                    var checkboxes = document.querySelectorAll(\n                                        'input[name=\"checkbox_category_api\"]:checked');\n                                    if (checkboxes.length === 0) {\n                                        showMessage('";
echo __("Vui lòng tích vào chuyên mục cần xóa.");
echo "',\n                                            'error');\n                                        return;\n                                    }\n                                    Swal.fire({\n                                        title: \"";
echo __("Bạn có chắc không?");
echo "\",\n                                        text: \"";
echo __("Hệ thống sẽ XÓA");
echo " \" + checkboxes.length +\n                                            \" ";
echo __("chuyên mục bạn chọn khi nhấn Đồng Ý");
echo "\",\n                                        icon: \"warning\",\n                                        showCancelButton: true,\n                                        confirmButtonColor: \"#3085d6\",\n                                        cancelButtonColor: \"#d33\",\n                                        confirmButtonText: \"";
echo __("Đồng ý");
echo "\",\n                                        cancelButtonText: \"";
echo __("Đóng");
echo "\"\n                                    }).then((result) => {\n                                        if (result.isConfirmed) {\n                                            delete_category_records();\n                                        }\n                                    });\n                                });\n\n                                function update_category_category_records() {\n                                    \$('#update_category_category_records').html('<i class=\"fa fa-spinner fa-spin\"></i>')\n                                        .prop('disabled',\n                                            true);\n                                    var category_id = document.getElementById('category_category_id').value;\n                                    var checkbox = document.getElementsByName('checkbox_category_api');\n                                    // Sử dụng hàm đệ quy để thực hiện lần lượt từng postUpdate với thời gian chờ 100ms\n                                    function postUpdatesSequentially(index) {\n                                        if (index < checkbox.length) {\n                                            if (checkbox[index].checked === true) {\n                                                post_update_category_category(checkbox[index].value, category_id);\n                                            }\n                                            setTimeout(function() {\n                                                postUpdatesSequentially(index + 1);\n                                            }, 100);\n                                        } else {\n                                            Swal.fire({\n                                                title: \"Thành công!\",\n                                                text: \"Cập nhật chuyên mục thành công\",\n                                                icon: \"success\"\n                                            });\n                                            setTimeout(function() {\n                                                location.reload();\n                                            }, 1000);\n                                            \$('#update_category_records').html(\n                                                '<i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "').prop(\n                                                'disabled',\n                                                false);\n                                        }\n                                    }\n                                    // Bắt đầu gọi hàm đệ quy từ index 0\n                                    postUpdatesSequentially(0);\n                                }\n\n                                function update_status_category_records() {\n                                    \$('#update_status_category_records').html('<i class=\"fa fa-spinner fa-spin\"></i>')\n                                        .prop('disabled',\n                                            true);\n                                    var status = document.getElementById('category_status').value;\n                                    var checkbox = document.getElementsByName('checkbox_category_api');\n                                    // Sử dụng hàm đệ quy để thực hiện lần lượt từng postUpdate với thời gian chờ 100ms\n                                    function postUpdatesSequentially(index) {\n                                        if (index < checkbox.length) {\n                                            if (checkbox[index].checked === true) {\n                                                post_update_status_category(checkbox[index].value, status);\n                                            }\n                                            setTimeout(function() {\n                                                postUpdatesSequentially(index + 1);\n                                            }, 100);\n                                        } else {\n                                            Swal.fire({\n                                                title: \"Thành công!\",\n                                                text: \"Cập nhật trạng thái thành công\",\n                                                icon: \"success\"\n                                            });\n                                            \$('#update_status_category_records').html(\n                                                '<i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "').prop(\n                                                'disabled',\n                                                false);\n                                        }\n                                    }\n                                    // Bắt đầu gọi hàm đệ quy từ index 0\n                                    postUpdatesSequentially(0);\n                                }\n\n\n                                function delete_category_records() {\n                                    var checkbox = document.getElementsByName('checkbox_category_api');\n\n                                    function postUpdatesSequentially(index) {\n                                        if (index < checkbox.length) {\n                                            if (checkbox[index].checked === true) {\n                                                post_delete_category(checkbox[index].value);\n                                            }\n                                            setTimeout(function() {\n                                                postUpdatesSequentially(index + 1);\n                                            }, 100);\n                                        } else {\n                                            Swal.fire({\n                                                title: \"";
echo __("Thành công!");
echo "\",\n                                                text: \"";
echo __("Xóa thành công");
echo "\",\n                                                icon: \"success\"\n                                            });\n                                            setTimeout(function() {\n                                                location.reload();\n                                            }, 1000);\n                                        }\n                                    }\n                                    postUpdatesSequentially(0);\n                                }\n\n\n                                function post_update_category_category(id, category_id) {\n                                    \$.ajax({\n                                        url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n                                        method: \"POST\",\n                                        dataType: \"JSON\",\n                                        data: {\n                                            action: 'update_category_category',\n                                            id: id,\n                                            category_id: category_id\n                                        },\n                                        success: function(result) {\n                                            if (result.status == 'success') {\n                                                showMessage(result.msg, result.status);\n                                            } else {\n                                                showMessage(result.msg, result.status);\n                                            }\n                                        },\n                                        error: function() {\n                                            alert(html(result));\n                                            location.reload();\n                                        }\n                                    });\n                                }\n\n                                function post_update_status_category(id, status) {\n                                    \$.ajax({\n                                        url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n                                        method: \"POST\",\n                                        dataType: \"JSON\",\n                                        data: {\n                                            action: 'update_status_category',\n                                            id: id,\n                                            status: status\n                                        },\n                                        success: function(result) {\n                                            if (result.status == 'success') {\n                                                if (status == 1) {\n                                                    document.getElementById('list_category_status' + id).checked = true;\n                                                } else {\n                                                    document.getElementById('list_category_status' + id).checked = false;\n                                                }\n                                                showMessage(result.msg, result.status);\n                                            } else {\n                                                showMessage(result.msg, result.status);\n                                            }\n                                        },\n                                        error: function() {\n                                            alert(html(result));\n                                            location.reload();\n                                        }\n                                    });\n                                }\n\n                                function post_delete_category(id) {\n                                    \$.ajax({\n                                        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n                                        method: \"POST\",\n                                        dataType: \"JSON\",\n                                        data: {\n                                            id: id,\n                                            token: '";
echo $getUser["token"];
echo "',\n                                            action: 'removeCategory'\n                                        },\n                                        success: function(result) {\n                                            if (result.status == 'success') {\n                                                showMessage(result.msg, result.status);\n                                            } else {\n                                                showMessage(result.msg, result.status);\n                                            }\n                                        },\n                                        error: function() {\n                                            alert(html(response));\n                                            location.reload();\n                                        }\n                                    });\n                                }\n\n                                function deleteCategory(id) {\n                                    const originalContent = \$('#btnDeleteCategory' + id)\n                                        .html(); // Save the original button content\n                                    \$('#btnDeleteCategory' + id).html(\n                                            '<span><i class=\"fa fa-spinner fa-spin\"></i></span>')\n                                        .prop('disabled', true);\n\n                                    Swal.fire({\n                                        title: \"";
echo __("Bạn có chắc không?");
echo "\",\n                                        text: \"";
echo __("Hệ thống sẽ xóa chuyên mục này nếu bạn nhấn Đồng ý");
echo "\",\n                                        icon: \"warning\",\n                                        showCancelButton: true,\n                                        confirmButtonColor: \"#3085d6\",\n                                        cancelButtonColor: \"#d33\",\n                                        confirmButtonText: \"";
echo __("Đồng ý");
echo "\",\n                                        cancelButtonText: \"";
echo __("Đóng");
echo "\"\n                                    }).then((result) => {\n                                        if (result.isConfirmed) {\n                                            post_delete_category(id);\n                                            setTimeout(() => {\n                                                location.reload();\n                                            }, 500);\n                                        }\n                                    }).finally(() => {\n                                        \$('#btnDeleteCategory' + id).html(originalContent)\n                                            .prop('disabled', false);\n                                    });\n                                }\n                                </script>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"tab-pane text-muted\" id=\"tab2\" role=\"tabpanel\">\n                <div class=\"row\">\n                    <div class=\"col-xl-12\">\n                        <div class=\"card custom-card\">\n                            <div class=\"card-header justify-content-between\">\n                                <div class=\"card-title\">\n                                    DANH SÁCH SẢN PHẨM API\n                                </div>\n                            </div>\n                            <div class=\"card-body\">\n                                <form action=\"";
echo base_url_admin();
echo "\" class=\"align-items-center mb-3\" name=\"formSearch\"\n                                    method=\"GET\">\n                                    <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                        <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                        <input type=\"hidden\" name=\"id\" value=\"";
echo $id;
echo "\">\n                                        <input type=\"hidden\" name=\"action\" value=\"product-api-manager\">\n                                        <div class=\"col-lg col-md-4 col-6\">\n                                            <input class=\"form-control form-control-sm\" value=\"";
echo $user_id;
echo "\"\n                                                name=\"user_id\" placeholder=\"ID User\">\n                                        </div>\n                                        <div class=\"col-lg col-md-4 col-6\">\n                                            <input class=\"form-control form-control-sm\" value=\"";
echo $username;
echo "\"\n                                                name=\"username\" placeholder=\"Username\">\n                                        </div>\n                                        <div class=\"col-lg col-md-4 col-6\">\n                                            <input class=\"form-control form-control-sm\" value=\"";
echo $name;
echo "\" name=\"name\"\n                                                placeholder=\"Tên sản phẩm\">\n                                        </div>\n                                        <div class=\"col-lg col-md-4 col-6\">\n                                            <select class=\"form-control\" name=\"status\">\n                                                <option value=\"\">";
echo __("-- Trạng thái --");
echo "</option>\n                                                <option ";
echo $status == 1 ? "selected" : "";
echo " value=\"1\">Hiển Thị</option>\n                                                <option ";
echo $status == 2 ? "selected" : "";
echo " value=\"2\">Ẩn</option>\n                                            </select>\n                                        </div>\n                                        <div class=\"col-md-3 col-6\">\n                                            <select class=\"form-control form-control-sm mb-1\" name=\"category_id\">\n                                                <option value=\"\">";
echo __("-- Chuyên mục --");
echo "</option>\n                                                ";
foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = 0 ") as $option) {
    echo "                                                <option disabled value=\"";
    echo $option["id"];
    echo "\">";
    echo $option["name"];
    echo "                                                </option>\n                                                ";
    foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = '" . $option["id"] . "' ") as $option1) {
        echo "                                                <option value=\"";
        echo $option1["id"];
        echo "\">__";
        echo $option1["name"];
        echo "</option>\n                                                ";
    }
    echo "                                                ";
}
echo "                                            </select>\n                                        </div>\n\n                                        <div class=\"col-lg col-md-4 col-6\">\n                                            <input type=\"text\" name=\"create_gettime\"\n                                                class=\"form-control form-control-sm\" id=\"daterange\"\n                                                value=\"";
echo $create_gettime;
echo "\" placeholder=\"Chọn thời gian\">\n                                        </div>\n                                        <div class=\"col-12\">\n                                            <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                                ";
echo __("Search");
echo "                                            </button>\n                                            <a class=\"btn btn-hero btn-sm btn-danger\"\n                                                href=\"";
echo base_url_admin("product-api-manager&id=" . $id);
echo "\"><i\n                                                    class=\"fa fa-trash\"></i>\n                                                ";
echo __("Clear filter");
echo "                                            </a>\n                                        </div>\n                                    </div>\n                                    <div class=\"top-filter\">\n                                        <div class=\"filter-show\">\n                                            <label class=\"filter-label\">Show :</label>\n                                            <select name=\"limit\" onchange=\"this.form.submit()\"\n                                                class=\"form-select filter-select\">\n                                                <option ";
echo $limit == 5 ? "selected" : "";
echo " value=\"5\">5</option>\n                                                <option ";
echo $limit == 10 ? "selected" : "";
echo " value=\"10\">10</option>\n                                                <option ";
echo $limit == 20 ? "selected" : "";
echo " value=\"20\">20</option>\n                                                <option ";
echo $limit == 50 ? "selected" : "";
echo " value=\"50\">50</option>\n                                                <option ";
echo $limit == 100 ? "selected" : "";
echo " value=\"100\">100</option>\n                                                <option ";
echo $limit == 500 ? "selected" : "";
echo " value=\"500\">500</option>\n                                                <option ";
echo $limit == 1000 ? "selected" : "";
echo " value=\"1000\">1000\n                                                </option>\n                                            </select>\n                                        </div>\n                                        <div class=\"filter-short\">\n                                            <label class=\"filter-label\">";
echo __("Short by Date:");
echo "</label>\n                                            <select name=\"shortByDate\" onchange=\"this.form.submit()\"\n                                                class=\"form-select filter-select\">\n                                                <option value=\"\">";
echo __("Tất cả");
echo "</option>\n                                                <option ";
echo $shortByDate == 1 ? "selected" : "";
echo " value=\"1\">\n                                                    ";
echo __("Hôm nay");
echo "                                                </option>\n                                                <option ";
echo $shortByDate == 2 ? "selected" : "";
echo " value=\"2\">\n                                                    ";
echo __("Tuần này");
echo "                                                </option>\n                                                <option ";
echo $shortByDate == 3 ? "selected" : "";
echo " value=\"3\">\n                                                    ";
echo __("Tháng này");
echo "                                                </option>\n                                            </select>\n                                        </div>\n                                    </div>\n                                </form>\n                                <div class=\"table-responsive table-wrapper mb-3\">\n                                    <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                        <thead>\n                                            <tr>\n                                                <th class=\"text-center\">\n                                                    <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                        <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                                            id=\"check_all_checkbox_product_api\" value=\"option1\">\n                                                    </div>\n                                                </th>\n                                                <th>";
echo __("Sản phẩm");
echo "</th>\n                                                <th class=\"text-center\">";
echo __("Chuyên mục");
echo "</th>\n                                                <th class=\"text-center\">";
echo __("Trạng thái");
echo "</th>\n                                                <th class=\"text-center\">";
echo __("Giá bán");
echo "</th>\n                                                <th class=\"text-center\">";
echo __("Chi tiết");
echo "</th>\n                                                <th class=\"text-center\">";
echo __("Thời gian");
echo "</th>\n                                                <th class=\"text-center\">";
echo __("Thao tác");
echo "</th>\n                                            </tr>\n                                        </thead>\n                                        <tbody>\n                                            ";
foreach ($listDatatable as $product) {
    echo "                                            <tr>\n                                                <td class=\"text-center\">\n                                                    <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                        <input type=\"checkbox\"\n                                                            class=\"form-check-input checkbox_product_api\"\n                                                            data-id=\"";
    echo $product["id"];
    echo "\" name=\"checkbox_product_api\"\n                                                            value=\"";
    echo $product["id"];
    echo "\" />\n                                                    </div>\n                                                </td>\n                                                <td>\n                                                    Tên sản phẩm hệ thống: <b>";
    echo $product["name"];
    echo "</b><br>\n                                                    Tên sản phẩm API: <b>";
    echo $product["api_name"];
    echo "</b>\n                                                </td>\n                                                <td class=\"text-center\">\n                                                    ";
    if($product["category_id"] != 0) {
        echo "                                                    <span\n                                                        class=\"badge bg-primary\">";
        echo getRowRealtime("categories", $product["category_id"], "name");
        echo "</span>\n                                                    ";
    }
    echo "                                                </td>\n                                                <td class=\"text-center\">\n                                                    <div class=\"form-check form-switch form-check-lg\"\n                                                        onchange=\"post_update_status_table_product(`";
    echo $product["id"];
    echo "`)\">\n                                                        <input class=\"form-check-input\" type=\"checkbox\"\n                                                            id=\"status";
    echo $product["id"];
    echo "\" value=\"1\"\n                                                            ";
    echo $product["status"] == 1 ? "checked=\"\"" : "";
    echo ">\n                                                    </div>\n                                                </td>\n                                                <td>\n                                                    Giá bán: <b\n                                                        style=\"color:red;\">";
    echo format_currency($product["price"]);
    echo "</b><br>\n                                                    Giá vốn: <b\n                                                        style=\"color:blue;\">";
    echo format_currency($product["cost"]);
    echo "</b>\n                                                </td>\n                                                <td>\n                                                    Đang bán: <b>";
    echo format_cash($product["api_stock"]);
    echo "</b><br>\n                                                    Đã bán: <b>";
    echo format_cash($product["sold"]);
    echo "</b>\n                                                </td>\n                                                <td><small>";
    echo $product["create_gettime"];
    echo "</small></td>\n                                                <td>\n                                                    <a type=\"button\"\n                                                        href=\"";
    echo base_url_admin("product-edit&id=" . $product["id"]);
    echo "\"\n                                                        class=\"btn btn-sm btn-secondary shadow-secondary btn-wave\"\n                                                        data-bs-toggle=\"tooltip\" title=\"";
    echo __("Chỉnh sửa");
    echo "\">\n                                                        <i class=\"fa-solid fa-pen-to-square\"></i> Edit\n                                                    </a>\n                                                    <a type=\"button\" onclick=\"removeProduct('";
    echo $product["id"];
    echo "')\"\n                                                        id=\"btnDeleteProduct";
    echo $product["id"];
    echo "\"\n                                                        class=\"btn btn-sm btn-danger shadow-danger btn-wave\"\n                                                        data-bs-toggle=\"tooltip\" title=\"";
    echo __("Xóa");
    echo "\">\n                                                        <i class=\"fas fa-trash\"></i> Delete\n                                                    </a>\n                                                </td>\n                                            </tr>\n                                            ";
}
echo "                                        </tbody>\n                                        <tfoot>\n                                            <td colspan=\"9\">\n                                                <div class=\"btn-list\">\n                                                    <button type=\"button\" id=\"btn_edit_category_product\"\n                                                        class=\"btn btn-outline-secondary shadow-secondary btn-wave btn-sm\"><i\n                                                            class=\"fa-solid fa-pen-to-square\"></i> CHỈNH CHUYÊN\n                                                        MỤC</button>\n                                                    <button type=\"button\" id=\"btn_edit_status_product\"\n                                                        class=\"btn btn-outline-success shadow-success btn-wave btn-sm\"><i\n                                                            class=\"fa-solid fa-pen-to-square\"></i> CHỈNH TRẠNG\n                                                        THÁI</button>\n                                                    <button type=\"button\" id=\"btn_delete_product\"\n                                                        class=\"btn btn-outline-danger shadow-danger btn-wave btn-sm\"><i\n                                                            class=\"fa-solid fa-trash\"></i> XÓA SẢN PHẨM</button>\n                                                </div>\n                                            </td>\n                                        </tfoot>\n                                    </table>\n                                </div>\n                                <div class=\"row\">\n                                    <div class=\"col-sm-12 col-md-5\">\n                                        <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of\n                                            ";
echo format_cash($totalDatatable);
echo "                                            Results</p>\n                                    </div>\n                                    <div class=\"col-sm-12 col-md-7 mb-3\">\n                                        ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n\n\n    <script>\n    document.addEventListener('DOMContentLoaded', function() {\n        // Xác định tab được lưu trong localStorage\n        let activeTab = localStorage.getItem('activeTab');\n\n        // Nếu không có tab được lưu trong localStorage, hoặc không tồn tại tab được lưu\n        if (!activeTab || !document.querySelector(activeTab)) {\n            // Đặt tab \"Thống kê\" làm active mặc định\n            let defaultTab = document.querySelector('a[data-bs-target=\"#tab1\"]');\n            if (defaultTab) {\n                defaultTab.classList.add('active');\n                defaultTab.classList.add('show');\n                let defaultTabContent = document.querySelector('#tab1');\n                if (defaultTabContent) {\n                    defaultTabContent.classList.add('active');\n                    defaultTabContent.classList.add('show');\n                }\n            }\n        } else {\n            // Kích hoạt tab được lưu\n            let tabLink = document.querySelector(`a[data-bs-target=\"\${activeTab}\"]`);\n            if (tabLink) {\n                tabLink.classList.add('active');\n                tabLink.classList.add('show');\n                let tabContent = document.querySelector(activeTab);\n                if (tabContent) {\n                    tabContent.classList.add('active');\n                    tabContent.classList.add('show');\n                }\n            }\n        }\n\n        // Lắng nghe sự kiện khi click vào tab\n        let tabs = document.querySelectorAll('.nav-link');\n        tabs.forEach(function(tab) {\n            tab.addEventListener('click', function() {\n                // Lưu tab được click vào localStorage\n                let targetTab = tab.getAttribute('data-bs-target');\n                localStorage.setItem('activeTab', targetTab);\n            });\n        });\n    });\n    </script>\n\n    <script>\n    \$(function() {\n        \$('#check_all_checkbox_product_api').on('click', function() {\n            \$('.checkbox_product_api').prop('checked', this.checked);\n        });\n        \$('.checkbox_product_api').on('click', function() {\n            \$('#check_all_checkbox_product_api').prop('checked', \$('.checkbox_product_api:checked')\n                .length === \$('.checkbox_product_api').length);\n        });\n    });\n    </script>\n\n\n    <script>\n    function post_update_category_product(id, category_id) {\n        \$.ajax({\n            url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n            method: \"POST\",\n            dataType: \"JSON\",\n            data: {\n                action: 'update_category_product',\n                id: id,\n                category_id: category_id\n            },\n            success: function(result) {\n                if (result.status == 'success') {\n                    showMessage(result.msg, result.status);\n                } else {\n                    showMessage(result.msg, result.status);\n                }\n            },\n            error: function() {\n                alert(html(result));\n                location.reload();\n            }\n        });\n    }\n\n    function post_update_status_product(id, status) {\n        \$.ajax({\n            url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n            method: \"POST\",\n            dataType: \"JSON\",\n            data: {\n                action: 'update_status_product',\n                id: id,\n                status: status\n            },\n            success: function(result) {\n                if (result.status == 'success') {\n                    if (status == 1) {\n                        document.getElementById('status' + id).checked = true;\n                    } else {\n                        document.getElementById('status' + id).checked = false;\n                    }\n                    showMessage(result.msg, result.status);\n                } else {\n                    showMessage(result.msg, result.status);\n                }\n            },\n            error: function() {\n                alert(html(result));\n                location.reload();\n            }\n        });\n    }\n\n    function post_update_status_table_product(id) {\n        \$.ajax({\n            url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n            method: \"POST\",\n            dataType: \"JSON\",\n            data: {\n                action: 'update_status_product',\n                id: id,\n                status: \$('#status' + id + ':checked').val()\n            },\n            success: function(result) {\n                if (result.status == 'success') {\n                    showMessage(result.msg, result.status);\n                } else {\n                    showMessage(result.msg, result.status);\n                }\n            },\n            error: function() {\n                alert(html(result));\n                location.reload();\n            }\n        });\n    }\n\n    function post_remove_product(id) {\n        \$.ajax({\n            url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n            method: \"POST\",\n            dataType: \"JSON\",\n            data: {\n                id: id,\n                action: 'removeProduct'\n            },\n            success: function(result) {\n                if (result.status == 'success') {\n                    showMessage(result.msg, result.status);\n                } else {\n                    showMessage(result.msg, result.status);\n                }\n            },\n            error: function() {\n                alert(html(response));\n                location.reload();\n            }\n        });\n    }\n\n    function removeProduct(id) {\n        const originalContent = \$('#btnDeleteProduct' + id).html(); // Save the original button content\n        \$('#btnDeleteProduct' + id).html('<span><i class=\"fa fa-spinner fa-spin\"></i></span>')\n            .prop('disabled', true);\n        Swal.fire({\n            title: \"";
echo __("Bạn có chắc không?");
echo "\",\n            text: \"";
echo __("Hệ thống sẽ sản phẩm này nếu bạn nhấn Đồng ý");
echo "\",\n            icon: \"warning\",\n            showCancelButton: true,\n            confirmButtonColor: \"#3085d6\",\n            cancelButtonColor: \"#d33\",\n            confirmButtonText: \"";
echo __("Đồng ý");
echo "\",\n            cancelButtonText: \"";
echo __("Đóng");
echo "\"\n        }).then((result) => {\n            if (result.isConfirmed) {\n                post_remove_product(id);\n                setTimeout(() => {\n                    location.reload();\n                }, 500);\n            }\n        }).finally(() => {\n            // Restore the button content and enable it when Swal closes\n            \$('#btnDeleteProduct' + id).html(originalContent)\n                .prop('disabled', false);\n        });\n    }\n    </script>\n\n\n\n    <script>\n    \$(document).ready(function() {\n\n        \$(\"#btn_edit_category_product\").click(function() {\n            var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_api\"]:checked');\n            if (checkboxes.length === 0) {\n                showMessage('Vui lòng chọn ít nhất một sản phẩm.', 'error');\n                return;\n            }\n            \$(\".checkboxeslength\").html(checkboxes.length);\n            \$(\"#modal_edit_category_product\").modal('show');\n        });\n\n        \$(\"#btn_edit_status_product\").click(function() {\n            var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_api\"]:checked');\n            if (checkboxes.length === 0) {\n                showMessage('Vui lòng chọn ít nhất một sản phẩm.', 'error');\n                return;\n            }\n            \$(\".checkboxeslength\").html(checkboxes.length);\n            \$(\"#modal_edit_status_product\").modal('show');\n        });\n\n        \$(\"#btn_delete_product\").click(function() {\n            var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_api\"]:checked');\n            if (checkboxes.length === 0) {\n                showMessage('Vui lòng chọn ít nhất một sản phẩm.', 'error');\n                return;\n            }\n            Swal.fire({\n                title: \"Bạn có chắc không?\",\n                text: \"Hệ thống sẽ xóa \" + checkboxes.length +\n                    \" sản phẩm bạn chọn khi nhấn Đồng Ý\",\n                icon: \"warning\",\n                showCancelButton: true,\n                confirmButtonColor: \"#3085d6\",\n                cancelButtonColor: \"#d33\",\n                confirmButtonText: \"Đồng ý\",\n                cancelButtonText: \"Đóng\"\n            }).then((result) => {\n                if (result.isConfirmed) {\n                    delete_records();\n                }\n            });\n        });\n\n    });\n    </script>\n\n    <script>\n    function update_category_records() {\n        \$('#update_category_records').html('<i class=\"fa fa-spinner fa-spin\"></i> Processing...').prop('disabled',\n            true);\n        var category_id = document.getElementById('category_id').value;\n        var checkbox = document.getElementsByName('checkbox_product_api');\n        // Sử dụng hàm đệ quy để thực hiện lần lượt từng postUpdate với thời gian chờ 100ms\n        function postUpdatesSequentially(index) {\n            if (index < checkbox.length) {\n                if (checkbox[index].checked === true) {\n                    post_update_category_product(checkbox[index].value, category_id);\n                }\n                setTimeout(function() {\n                    postUpdatesSequentially(index + 1);\n                }, 100);\n            } else {\n                Swal.fire({\n                    title: \"Thành công!\",\n                    text: \"Cập nhật chuyên mục thành công\",\n                    icon: \"success\"\n                });\n                setTimeout(function() {\n                    location.reload();\n                }, 1000);\n                \$('#update_category_records').html('<i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "').prop(\n                    'disabled',\n                    false);\n            }\n        }\n        // Bắt đầu gọi hàm đệ quy từ index 0\n        postUpdatesSequentially(0);\n    }\n\n    function update_status_records() {\n        \$('#update_status_records').html('<i class=\"fa fa-spinner fa-spin\"></i> Processing...').prop('disabled',\n            true);\n        var status = document.getElementById('status').value;\n        var checkbox = document.getElementsByName('checkbox_product_api');\n        // Sử dụng hàm đệ quy để thực hiện lần lượt từng postUpdate với thời gian chờ 100ms\n        function postUpdatesSequentially(index) {\n            if (index < checkbox.length) {\n                if (checkbox[index].checked === true) {\n                    post_update_status_product(checkbox[index].value, status);\n                }\n                setTimeout(function() {\n                    postUpdatesSequentially(index + 1);\n                }, 100);\n            } else {\n                Swal.fire({\n                    title: \"Thành công!\",\n                    text: \"Cập nhật trạng thái thành công\",\n                    icon: \"success\"\n                });\n                \$('#update_status_records').html('<i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "').prop(\n                    'disabled',\n                    false);\n            }\n        }\n        // Bắt đầu gọi hàm đệ quy từ index 0\n        postUpdatesSequentially(0);\n    }\n\n    function delete_records() {\n        var checkbox = document.getElementsByName('checkbox_product_api');\n\n        function postUpdatesSequentially(index) {\n            if (index < checkbox.length) {\n                if (checkbox[index].checked === true) {\n                    post_remove_product(checkbox[index].value);\n                }\n                setTimeout(function() {\n                    postUpdatesSequentially(index + 1);\n                }, 100);\n            } else {\n                Swal.fire({\n                    title: \"Thành công!\",\n                    text: \"Xóa sản phẩm thành công\",\n                    icon: \"success\"\n                });\n                setTimeout(function() {\n                    location.reload();\n                }, 1000);\n            }\n        }\n        postUpdatesSequentially(0);\n    }\n    </script>\n    <div class=\"modal fade\" id=\"modal_edit_category_product\" tabindex=\"-1\"\n        aria-labelledby=\"Cập nhật chuyên mục sản phẩm\" data-bs-keyboard=\"false\" aria-hidden=\"true\">\n        <!-- Scrollable modal -->\n        <div class=\"modal-dialog modal-dialog-centered\">\n            <div class=\"modal-content\">\n                <div class=\"modal-header\">\n                    <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Cập nhật chuyên mục <mark\n                            class=\"checkboxeslength\"></mark> sản phẩm đã chọn</h6>\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n                </div>\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Chuyên mục:");
echo " <span\n                                class=\"text-danger\">*</span></label>\n                        <div class=\"col-sm-8\">\n                            <select class=\"form-control\" id=\"category_id\" required>\n                                <option value=\"0\">";
echo __("-- Chọn chuyên mục --");
echo "</option>\n                                ";
foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = 0 ") as $option) {
    echo "                                <option disabled value=\"";
    echo $option["id"];
    echo "\">";
    echo $option["name"];
    echo "</option>\n                                ";
    foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = '" . $option["id"] . "' ") as $option1) {
        echo "                                <option value=\"";
        echo $option1["id"];
        echo "\">__";
        echo $option1["name"];
        echo "</option>\n                                ";
    }
    echo "                                ";
}
echo "                            </select>\n                        </div>\n                    </div>\n                    <p>Khi bạn nhấn vào nút UPDATE đồng nghĩa các sản phẩm mà bạn đã chọn sẽ được cập nhật thành chuyên\n                        mục trên.</p>\n\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"button\" onclick=\"update_category_records()\" id=\"update_category_records\"\n                        class=\"btn btn-primary\"><i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "</button>\n                </div>\n            </div>\n        </div>\n    </div>\n\n    <div class=\"modal fade\" id=\"modal_edit_status_product\" tabindex=\"-1\" aria-labelledby=\"Cập nhật trạng thái sản phẩm\"\n        data-bs-keyboard=\"false\" aria-hidden=\"true\">\n        <!-- Scrollable modal -->\n        <div class=\"modal-dialog modal-dialog-centered\">\n            <div class=\"modal-content\">\n                <div class=\"modal-header\">\n                    <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Cập nhật trạng thái <mark\n                            class=\"checkboxeslength\"></mark> sản phẩm đã chọn</h6>\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n                </div>\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Trạng thái:");
echo " <span\n                                class=\"text-danger\">*</span></label>\n                        <div class=\"col-sm-8\">\n                            <select class=\"form-control\" id=\"status\" required>\n                                <option value=\"1\">ON</option>\n                                <option value=\"0\">OFF</option>\n                            </select>\n                        </div>\n                    </div>\n                    <p>Khi bạn nhấn vào nút UPDATE đồng nghĩa các sản phẩm mà bạn đã chọn sẽ được cập nhật thành trạng\n                        thái trên.</p>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"button\" onclick=\"update_status_records()\" id=\"update_status_records\"\n                        class=\"btn btn-primary\"><i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "</button>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\n// basic datatable\n\$('#datatable-basic').DataTable({\n    language: {\n        searchPlaceholder: 'Tìm kiếm...',\n        sSearch: '',\n    },\n    \"pageLength\": 100,\n    // scrollX: true\n});\n// basic datatable\n</script>";

?>