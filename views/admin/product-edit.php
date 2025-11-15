<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Chỉnh sửa sản phẩm", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    if(!($product = $CMSNT->get_row("SELECT * FROM `products` WHERE `id` = '" . $id . "' "))) {
        redirect(base_url_admin("products"));
    }
} else {
    redirect(base_url_admin("products"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_product")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["save"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if($product["name"] != check_string($_POST["name"]) && $CMSNT->get_row("SELECT * FROM `products` WHERE `name` = '" . check_string($_POST["name"]) . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"sản phẩm này đã tồn tại trong hệ thống.\")){window.history.back().location.reload();}</script>");
    }
    if($product["slug"] != check_string($_POST["slug"]) && $CMSNT->get_row("SELECT * FROM `products` WHERE `slug` = '" . check_string($_POST["slug"]) . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Slug này đã tồn tại trong hệ thống.\")){window.history.back().location.reload();}</script>");
    }
    $images = $product["images"];
    if(isset($_FILES["images"]["name"]) && !empty($_FILES["images"]["name"])) {
        foreach ($_FILES["images"]["name"] as $name => $value) {
            if($value == "") {
            } else {
                $rand = random("QWERTYUIOPASDFGHJKLZXCVBNM0123456789", 8);
                $uploads_dir = "assets/storage/images/products/";
                $tmp_name = $_FILES["images"]["tmp_name"][$name];
                $name_image = $rand . ".png";
                move_uploaded_file($tmp_name, $uploads_dir . $name_image);
                $images = $images . PHP_EOL . $name_image;
            }
        }
    }
    $isInsert = $CMSNT->update("products", ["code" => !empty($_POST["code"]) ? check_string($_POST["code"]) : NULL, "name" => !empty($_POST["name"]) ? check_string($_POST["name"]) : NULL, "slug" => !empty($_POST["slug"]) ? check_string($_POST["slug"]) : NULL, "images" => trim($images), "short_desc" => !empty($_POST["short_desc"]) ? check_string($_POST["short_desc"]) : NULL, "description" => !empty($_POST["description"]) ? base64_encode($_POST["description"]) : NULL, "flag" => !empty($_POST["flag"]) ? check_string($_POST["flag"]) : NULL, "note" => !empty($_POST["note"]) ? base64_encode($_POST["note"]) : NULL, "text_txt" => !empty($_POST["text_txt"]) ? check_string($_POST["text_txt"]) : NULL, "price" => !empty($_POST["price"]) ? check_string($_POST["price"]) : 0, "min" => !empty($_POST["min"]) ? check_string($_POST["min"]) : 1, "max" => !empty($_POST["max"]) ? check_string($_POST["max"]) : 100000, "sold" => !empty($_POST["sold"]) ? check_string($_POST["sold"]) : 0, "cost" => !empty($_POST["cost"]) ? check_string($_POST["cost"]) : 0, "discount" => !empty($_POST["discount"]) ? check_string($_POST["discount"]) : 0, "check_live" => !empty($_POST["check_live"]) ? check_string($_POST["check_live"]) : "None", "category_id" => check_string($_POST["category_id"]), "status" => check_string($_POST["status"]), "order_by" => !empty($_POST["order_by"]) ? check_string($_POST["order_by"]) : 1, "update_gettime" => gettime()], " `id` = '" . $product["id"] . "' ");
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Edit Product (" . $product["name"] . " ID " . $product["id"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Edit Product (" . $product["name"] . " ID " . $product["id"] . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công!\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại!\")){window.history.back().location.reload();}</script>");
}
if(isset($_POST["AddDiscount"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    $isInsert = $CMSNT->insert("product_discount", ["product_id" => $product["id"], "min" => check_string($_POST["min"]), "discount" => check_string($_POST["discount"]), "create_gettime" => gettime()]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Thêm điều kiện giảm giá cho sản phẩm (" . $product["name"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Thêm điều kiện giảm giá cho sản phẩm (" . $product["name"] . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công!\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại !\")){window.history.back().location.reload();}</script>");
}
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><a type=\"button\"\n                    class=\"btn btn-dark btn-raised-shadow btn-wave btn-sm me-1\"\n                    href=\"";
echo base_url_admin("products");
echo "\"><i class=\"fa-solid fa-arrow-left\"></i></a> Chỉnh sửa sản\n                phẩm ";
echo $product["name"];
echo "            </h1>\n        </div>\n        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n            ";
if($product["check_live"] == "Clone" && 120 <= time() - $CMSNT->site("time_cron_checklive_clone")) {
    echo "            <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n                <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                    width=\"1.5rem\" fill=\"#000000\">\n                    <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                    <path\n                        d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n                </svg>\n                Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                    href=\"";
    echo base_url("cron/checklive/clone.php");
    echo "\"\n                    target=\"_blank\">";
    echo base_url("cron/checklive/clone.php");
    echo "</a> 1 phút 1 lần để hệ thống\n                check live tài khoản.\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                        class=\"bi bi-x\"></i></button>\n            </div>\n            ";
}
echo "            ";
if($product["check_live"] == "Hotmail" && 120 <= time() - $CMSNT->site("time_cron_checklive_hotmail")) {
    echo "            <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n                <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                    width=\"1.5rem\" fill=\"#000000\">\n                    <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                    <path\n                        d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n                </svg>\n                Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                    href=\"";
    echo base_url("cron/checklive/hotmail.php");
    echo "\"\n                    target=\"_blank\">";
    echo base_url("cron/checklive/hotmail.php");
    echo "</a> 1 phút 1 lần để hệ\n                thống\n                check live tài khoản.\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                        class=\"bi bi-x\"></i></button>\n            </div>\n            ";
}
echo "            ";
if($product["check_live"] == "Gmail" && 120 <= time() - $CMSNT->site("time_cron_checklive_gmail")) {
    echo "            <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n                <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                    width=\"1.5rem\" fill=\"#000000\">\n                    <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                    <path\n                        d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n                </svg>\n                Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                    href=\"";
    echo base_url("cron/checklive/gmail.php");
    echo "\"\n                    target=\"_blank\">";
    echo base_url("cron/checklive/gmail.php");
    echo "</a> 1 phút 1 lần để hệ\n                thống\n                check live tài khoản.\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                        class=\"bi bi-x\"></i></button>\n            </div>\n            ";
}
echo "            <div class=\"row\">\n                <div class=\"col-xl-8\">\n                    <div class=\"card custom-card\">\n                        <div class=\"card-header justify-content-between\">\n                            <div class=\"card-title\">\n                                THÔNG TIN SẢN PHẨM\n                            </div>\n                            <div class=\"d-flex\">\n                                <div class=\"btn-list\">\n                                    <button type=\"button\" data-bs-toggle=\"modal\"\n                                        data-bs-target=\"#exampleModalScrollable2\"\n                                        class=\"btn btn-sm btn-danger-gradient btn-wave shadow-danger\"><i\n                                            class=\"fa-solid fa-tag\"></i>\n                                        THÊM KHUYẾN MÃI</button>\n                                    ";
if($product["supplier_id"] == 0) {
    echo "                                    <a type=\"button\" href=\"";
    echo base_url_admin("product-stock&code=" . $product["code"]);
    echo "\"\n                                        class=\"btn btn-warning-gradient btn-wave btn-sm\" data-bs-toggle=\"tooltip\"\n                                        title=\"";
    echo __("Kho hàng");
    echo "\">\n                                        <i class=\"fa-solid fa-cart-shopping\"></i> KHO HÀNG\n                                    </a>\n                                    ";
} else {
    echo "                                    <a type=\"button\"\n                                        href=\"";
    echo base_url_admin("product-api-manager&id=" . $product["supplier_id"]);
    echo "\"\n                                        class=\"btn btn-primary-gradient btn-wave btn-sm\" data-bs-toggle=\"tooltip\"\n                                        title=\"";
    echo __("Quản lý API");
    echo "\">\n                                        <i class=\"fa-solid fa-bars-progress\"></i> QUẢN LÝ API\n                                    </a>\n                                    ";
}
echo "                                </div>\n                            </div>\n                        </div>\n                        <div class=\"card-body\">\n                            <div class=\"row mb-5\">\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Tên sản phẩm:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"name\" value=\"";
echo $product["name"];
echo "\"\n                                        placeholder=\"";
echo __("Nhập tên sản phẩm");
echo "\" required>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Slug:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <div class=\"input-group\">\n                                        <span class=\"input-group-text\">";
echo base_url("product/");
echo "</span>\n                                        <input type=\"text\" class=\"form-control\" value=\"";
echo $product["slug"];
echo "\" name=\"slug\"\n                                            required>\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Giá bán mặc định:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <div class=\"input-group\">\n                                        <input type=\"text\" value=\"";
echo $product["price"];
echo "\"\n                                            class=\"form-control text-center\" id=\"example-group1-input3\" name=\"price\"\n                                            required>\n                                        <span class=\"input-group-text\">";
echo currencyDefault();
echo "</span>\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Giảm giá:");
echo "</label>\n                                    <div class=\"input-group\">\n                                        <input type=\"text\" class=\"form-control text-center\" id=\"example-group1-input3\"\n                                            name=\"discount\" value=\"";
echo $product["discount"];
echo "\">\n                                        <span class=\"input-group-text\">%</span>\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Giá vốn:");
echo "</label>\n                                    <div class=\"input-group\">\n                                        <input type=\"text\" class=\"form-control text-center\" id=\"example-group1-input3\"\n                                            name=\"cost\" value=\"";
echo $product["cost"];
echo "\">\n                                        <span class=\"input-group-text\">";
echo currencyDefault();
echo "</span>\n                                    </div>\n                                    <small>Giá vốn nhập hàng để tính toán lợi nhuận nếu có</small>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mua tối thiểu:");
echo "</label>\n                                    <div class=\"input-group\">\n                                        <input type=\"number\" class=\"form-control text-center\" id=\"example-group1-input3\"\n                                            name=\"min\" value=\"";
echo $product["min"];
echo "\">\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mua tối đa:");
echo "</label>\n                                    <div class=\"input-group\">\n                                        <input type=\"number\" class=\"form-control text-center\" id=\"example-group1-input3\"\n                                            name=\"max\" value=\"";
echo $product["max"];
echo "\">\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\"\n                                        for=\"example-hf-email\">";
echo __("Số lượng đã bán:");
echo "</label>\n                                    <div class=\"input-group\">\n                                        <input type=\"number\" class=\"form-control text-center\" name=\"sold\"\n                                            value=\"";
echo $product["sold"];
echo "\">\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\"\n                                    style=\"";
echo $product["supplier_id"] != 0 ? "display:none;" : "";
echo "\">\n                                    <label class=\"form-label\"\n                                        for=\"example-hf-email\">";
echo __("Check live tài khoản:");
echo "</label>\n                                    <select class=\"form-control\" name=\"check_live\">\n                                        <option ";
echo $product["check_live"] == "None" ? "selected" : "";
echo " value=\"None\">\n                                            None\n                                        </option>\n                                        <option ";
echo $product["check_live"] == "Clone" ? "selected" : "";
echo " value=\"Clone\">\n                                            Clone Via Facebook</option>\n                                        <option ";
echo $product["check_live"] == "Hotmail" ? "selected" : "";
echo "                                            value=\"Hotmail\">Hotmail & Outlook\n                                        </option>\n                                        <option ";
echo $product["check_live"] == "Gmail" ? "selected" : "";
echo " value=\"Gmail\">\n                                            Gmail (Cấu hình API Key tại Cài đặt -> Kết nối)\n                                        </option>\n                                    </select>\n                                </div>\n\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Trạng thái:");
echo " <span\n                                            class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" name=\"status\" required>\n                                        <option ";
echo $product["status"] == 1 ? "selected" : "";
echo " value=\"1\">ON</option>\n                                        <option ";
echo $product["status"] == 0 ? "selected" : "";
echo " value=\"0\">OFF</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Quốc gia:");
echo " (nếu\n                                        có)</label>\n                                    <input type=\"text\" class=\"form-control\" name=\"flag\" value=\"";
echo $product["flag"];
echo "\"\n                                        placeholder=\"Country Codes VD: Việt Nam = vn, Mỹ = us, Thái Lan = th\">\n                                    <small>Truy cập vào <a class=\"text-primary\"\n                                            href=\"https://www.nationsonline.org/oneworld/country_code_list.htm\"\n                                            target=\"_blank\">đây</a> để sao chép Code Alpha 2</small>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mô tả ngắn:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <textarea class=\"form-control\" rows=\"3\" name=\"short_desc\"\n                                        placeholder=\"Nhập mô tả ngắn cho sản phẩm\"\n                                        required>";
echo $product["short_desc"];
echo "</textarea>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mô tả chi tiết:");
echo "</label>\n                                    <textarea class=\"description\" id=\"description\"\n                                        name=\"description\">";
echo base64_decode($product["description"]);
echo "</textarea>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\"\n                                        for=\"example-hf-email\">";
echo __("Lưu ý xuất hiện khi xem đơn hàng:");
echo "</label>\n                                    <textarea class=\"note\" id=\"note\"\n                                        name=\"note\">";
echo base64_decode($product["note"]);
echo "</textarea>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\"\n                                        for=\"example-hf-email\">";
echo __("Nội dung đầu tiên trong tệp .txt:");
echo "</label>\n                                    <textarea class=\"form-control\" name=\"text_txt\">";
echo $product["text_txt"];
echo "</textarea>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <div class=\"mb-3\">\n                                        <label class=\"form-label\" for=\"example-file-input-multiple\">Tải lên ảnh sản\n                                            phẩm:</label>\n                                        <input class=\"form-control\" type=\"file\" name=\"images[]\" multiple>\n                                        <small>Có thể chọn 1 hoặc nhiều ảnh</small>\n                                    </div>\n                                    <div class=\"row\">\n                                        ";
foreach (explode(PHP_EOL, $product["images"]) as $image) {
    echo "                                        ";
    if(empty($image)) {
    } else {
        echo "                                        <div class=\"col-xxl-3 col-xl-3 col-lg-3 col-md-6 col-sm-12\">\n                                            <div class=\"card\">\n                                                <span class=\"badge bg-dark text-white\">";
        echo $image;
        echo "</span>\n                                                <a href=\"";
        echo dirImageProduct($image);
        echo "\" class=\"glightbox\"\n                                                    data-gallery=\"gallery1\">\n                                                    <img src=\"";
        echo dirImageProduct($image);
        echo "\" width=\"100%\" alt=\"image\">\n                                                </a>\n                                                <button type=\"button\"\n                                                    onclick=\"removeImageProduct(`";
        echo $product["id"];
        echo "`,`";
        echo $image;
        echo "`)\"\n                                                    class=\"btn btn-danger btn-sm\">Delete</button>\n                                            </div>\n                                        </div>\n                                        ";
    }
}
echo "                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"col-xl-4\">\n                    <div class=\"card custom-card\">\n                        <div class=\"card-body\">\n                            <div class=\"row mb-3\">\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Chuyên mục:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" name=\"category_id\" required>\n                                        <option value=\"0\">";
echo __("-- Chuyên mục --");
echo "</option>\n                                        ";
foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = 0 ") as $option) {
    echo "                                        <option disabled value=\"";
    echo $option["id"];
    echo "\">";
    echo $option["name"];
    echo "</option>\n                                        ";
    foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = '" . $option["id"] . "' ") as $option1) {
        echo "                                        <option ";
        echo $product["category_id"] == $option1["id"] ? "selected" : "";
        echo "                                            value=\"";
        echo $option1["id"];
        echo "\">__";
        echo $option1["name"];
        echo "</option>\n                                        ";
    }
    echo "                                        ";
}
echo "                                    </select>\n                                </div>\n                                <div class=\"col-sm-12 mb-3\"\n                                    style=\"";
echo $product["supplier_id"] != 0 ? "display:none;" : "";
echo "\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mã sản phẩm:");
echo " <span\n                                            class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"code\" value=\"";
echo $product["code"];
echo "\">\n                                    <small>";
echo __("Mã sản phẩm dùng để phân loại kho hàng, 2 sản phẩm giống Mã sản phẩm sẽ dùng chung 1 kho hàng.");
echo "</small>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\"\n                                    style=\"";
echo $product["supplier_id"] != 0 ? "display:none;" : "";
echo "\">\n                                    <label class=\"form-label\"\n                                        for=\"example-hf-email\">";
echo __("Tài khoản nào trong kho hàng được ưu tiên bán trước?");
echo "</label>\n                                    <select class=\"form-control\" name=\"order_by\" required>\n                                        <option value=\"1\" ";
echo $product["order_by"] == 1 ? "selected" : "";
echo ">\n                                            ";
echo __("Check live gần nhất");
echo "</option>\n                                        <option value=\"2\" ";
echo $product["order_by"] == 2 ? "selected" : "";
echo ">\n                                            ";
echo __("Import lâu nhất");
echo "</option>\n                                        <option value=\"3\" ";
echo $product["order_by"] == 3 ? "selected" : "";
echo ">\n                                            ";
echo __("Import gần nhất");
echo "</option>\n                                        <option value=\"4\" ";
echo $product["order_by"] == 4 ? "selected" : "";
echo ">\n                                            ";
echo __("Ngẫu nhiên");
echo "</option>\n                                    </select>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger shadow-danger\"\n                                href=\"";
echo base_url_admin("products");
echo "\"><i class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"save\" class=\"btn btn-primary shadow-primary\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </div>\n                    </div>\n                </div>\n        </form>\n    </div>\n</div>\n</div>\n\n\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-xl modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">THÊM ĐIỀU KIỆN KHUYẾN MÃI</h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\">\n                    <div class=\"alert alert-solid-primary alert-dismissible fade show\">\n                        Điều kiện này không áp dụng cho các thành viên đã được set chiết khấu giảm giá.<br>\n                        Nếu bạn set chiết khấu giảm cho thành viên A, thành viên A sẽ không được áp dụng mức giảm này\n                        nữa.\n                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                                class=\"bi bi-x\"></i></button>\n                    </div>\n\n                    <div class=\"row mb-3\">\n                        <div class=\"col-sm-6 mb-2\">\n                            <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Số lượng mua tối thiểu:");
echo "                                <span class=\"text-danger\">*</span></label>\n                            <input type=\"text\" class=\"form-control\" name=\"min\"\n                                placeholder=\"";
echo __("Số lượng mua tối thiểu để áp dụng giảm giá");
echo "\" required>\n                        </div>\n                        <div class=\"col-sm-6 mb-2\">\n                            <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Giảm giá");
echo "                                <span class=\"text-danger\">*</span></label>\n                            <div class=\"input-group\">\n                                <input type=\"text\" class=\"form-control\" name=\"discount\"\n                                    placeholder=\"VD: nhập 10 sẽ giảm 10%\">\n                                <span class=\"input-group-text\">\n                                    %\n                                </span>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"table-responsive\">\n                        <table class=\"table table-sm text-nowrap table-striped table-hover table-bordered\">\n                            <thead>\n                                <tr>\n                                    <th class=\"text-center\">Số lượng mua tối thiểu</th>\n                                    <th class=\"text-center\">Giảm giá</th>\n                                    <th class=\"text-center\">Thao tác</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
foreach ($CMSNT->get_list(" SELECT * FROM product_discount WHERE product_id = '" . $product["id"] . "' ORDER BY min ASC ") as $product_discount) {
    echo "                                <tr>\n                                    <td class=\"text-center\"><span\n                                            class=\"badge bg-outline-info\">";
    echo format_cash($product_discount["min"]);
    echo "</span>\n                                    </td>\n                                    <td class=\"text-center\"><span\n                                            class=\"badge bg-outline-danger\">";
    echo $product_discount["discount"];
    echo "%</span>\n                                    </td>\n                                    <td class=\"text-center\">\n                                        <a type=\"button\" onclick=\"remove('";
    echo $product_discount["id"];
    echo "')\"\n                                            class=\"btn btn-sm btn-danger shadow-danger btn-wave\"\n                                            data-bs-toggle=\"tooltip\" title=\"";
    echo __("Xóa");
    echo "\">\n                                            <i class=\"fas fa-trash\"></i> Delete\n                                        </a>\n                                    </td>\n                                </tr>\n                                ";
}
echo "                            </tbody>\n                        </table>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddDiscount\" class=\"btn btn-primary\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>\n                        ";
echo __("Submit");
echo "</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nvar lightboxVideo = GLightbox({\n    selector: '.glightbox'\n});\n\nCKEDITOR.replace(\"description\");\nCKEDITOR.replace(\"note\");\n\nfunction removeImageProduct(id, image) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa ảnh\",\n        message: \"Bạn có chắc chắn muốn xóa ảnh \" + id + \" không ?\",\n        confirmText: \"Đồng Ý\",\n        cancelText: \"Hủy\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    id: id,\n                    image: image,\n                    action: 'removeImageProduct'\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n</script>\n\n<script>\nfunction remove(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác Nhận Xóa Điều Kiện Giảm Giá\",\n        message: \"Bạn có chắc chắn muốn xóa item này không ?\",\n        confirmText: \"Đồng Ý\",\n        cancelText: \"Hủy\"\n    }).then((e) => {\n        if (e) {\n            post_remove_discount(id);\n            setTimeout(function() {\n                location.reload();\n            }, 500);\n        }\n    })\n}\n\nfunction post_remove_discount(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            id: id,\n            action: 'removeProductDiscount'\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n</script>";

?>