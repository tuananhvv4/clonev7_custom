<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Product Add"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_product")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["submit"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if($CMSNT->get_row("SELECT * FROM `products` WHERE `name` = '" . check_string($_POST["name"]) . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"sản phẩm này đã tồn tại trong hệ thống.\")){window.history.back().location.reload();}</script>");
    }
    if($CMSNT->get_row("SELECT * FROM `products` WHERE `slug` = '" . check_string($_POST["slug"]) . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Slug này đã tồn tại trong hệ thống.\")){window.history.back().location.reload();}</script>");
    }
    $images = "";
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
    $isInsert = $CMSNT->insert("products", ["code" => !empty($_POST["code"]) ? check_string($_POST["code"]) : uniqid(), "user_id" => $getUser["id"], "name" => !empty($_POST["name"]) ? check_string($_POST["name"]) : NULL, "slug" => !empty($_POST["slug"]) ? check_string($_POST["slug"]) : NULL, "images" => trim($images), "short_desc" => !empty($_POST["short_desc"]) ? check_string($_POST["short_desc"]) : NULL, "description" => !empty($_POST["description"]) ? base64_encode($_POST["description"]) : NULL, "flag" => !empty($_POST["flag"]) ? check_string($_POST["flag"]) : NULL, "note" => !empty($_POST["note"]) ? base64_encode($_POST["note"]) : NULL, "text_txt" => !empty($_POST["text_txt"]) ? check_string($_POST["text_txt"]) : NULL, "price" => !empty($_POST["price"]) ? check_string($_POST["price"]) : 0, "min" => !empty($_POST["min"]) ? check_string($_POST["min"]) : 1, "max" => !empty($_POST["max"]) ? check_string($_POST["max"]) : 1000000, "cost" => !empty($_POST["cost"]) ? check_string($_POST["cost"]) : 0, "discount" => !empty($_POST["discount"]) ? check_string($_POST["discount"]) : 0, "check_live" => !empty($_POST["check_live"]) ? check_string($_POST["check_live"]) : "None", "category_id" => check_string($_POST["category_id"]), "status" => check_string($_POST["status"]), "order_by" => !empty($_POST["order_by"]) ? check_string($_POST["order_by"]) : 1, "create_gettime" => gettime(), "update_gettime" => gettime()]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add Product (" . check_string($_POST["name"]) . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Add Product (" . check_string($_POST["name"]) . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công !\")){location.href = \"" . base_url_admin("products") . "\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại !\")){window.history.back().location.reload();}</script>");
}
echo "\n\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><a type=\"button\"\n                    class=\"btn btn-dark btn-raised-shadow btn-wave btn-sm me-1\"\n                    href=\"";
echo base_url_admin("products");
echo "\"><i class=\"fa-solid fa-arrow-left\"></i></a> Thêm sản phẩm mới\n            </h1>\n        </div>\n        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n            <div class=\"row\">\n                <div class=\"col-xl-8\">\n                    <div class=\"card custom-card\">\n                        <div class=\"card-header justify-content-between\">\n                            <div class=\"card-title\">\n                                THÔNG TIN SẢN PHẨM\n                            </div>\n                        </div>\n                        <div class=\"card-body\">\n                            <div class=\"row mb-5\">\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Tên sản phẩm:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"name\"\n                                        placeholder=\"";
echo __("Nhập tên sản phẩm");
echo "\" required>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Slug:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <div class=\"input-group\">\n                                        <span class=\"input-group-text\">";
echo base_url("product/");
echo "</span>\n                                        <input type=\"text\" class=\"form-control\" name=\"slug\" required>\n                                    </div>\n                                    <small>Để mặc định nếu không hiểu cách sử dụng.</small>\n                                </div>\n                                <script>\n                                function removeVietnameseTones(str) {\n                                    return str.normalize('NFD') // Tách tổ hợp ký tự và dấu\n                                        .replace(/[\\u0300-\\u036f]/g, '') // Loại bỏ dấu\n                                        .replace(/đ/g, 'd') // Chuyển đổi chữ \"đ\" thành \"d\"\n                                        .replace(/Đ/g, 'D'); // Chuyển đổi chữ \"Đ\" thành \"D\"\n                                }\n\n                                document.querySelector('input[name=\"name\"]').addEventListener('input', function() {\n                                    var productName = this.value;\n\n                                    // Chuyển tên sản phẩm thành slug\n                                    var slug = removeVietnameseTones(productName.toLowerCase())\n                                        .replace(/ /g, '-') // Thay khoảng trắng bằng dấu gạch ngang\n                                        .replace(/[^\\w-]+/g, ''); // Loại bỏ các ký tự không hợp lệ\n\n                                    // Đặt giá trị slug vào trường input slug\n                                    document.querySelector('input[name=\"slug\"]').value = slug;\n                                });\n                                </script>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Giá bán mặc định:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <div class=\"input-group\">\n                                        <input type=\"text\" class=\"form-control text-center\" id=\"example-group1-input3\"\n                                            name=\"price\" required>\n                                        <span class=\"input-group-text\">";
echo currencyDefault();
echo "</span>\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Giảm giá:");
echo "</label>\n                                    <div class=\"input-group\">\n                                        <input type=\"text\" class=\"form-control text-center\" id=\"example-group1-input3\"\n                                            name=\"discount\" value=\"0\">\n                                        <span class=\"input-group-text\">%</span>\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Giá vốn:");
echo "</label>\n                                    <div class=\"input-group\">\n                                        <input type=\"text\" class=\"form-control text-center\" id=\"example-group1-input3\"\n                                            name=\"cost\" value=\"0\">\n                                        <span class=\"input-group-text\">";
echo currencyDefault();
echo "</span>\n                                    </div>\n                                    <small>Giá vốn nhập hàng để tính toán lợi nhuận nếu có</small>\n                                </div>\n                                <div class=\"col-sm-6 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mua tối thiểu:");
echo "</label>\n                                    <div class=\"input-group\">\n                                        <input type=\"number\" class=\"form-control text-center\" id=\"example-group1-input3\"\n                                            name=\"min\" value=\"1\">\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-6 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mua tối đa:");
echo "</label>\n                                    <div class=\"input-group\">\n                                        <input type=\"number\" class=\"form-control text-center\" id=\"example-group1-input3\"\n                                            name=\"max\" value=\"1000000\">\n                                    </div>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\"\n                                        for=\"example-hf-email\">";
echo __("Check live tài khoản:");
echo "</label>\n                                    <select class=\"form-control\" name=\"check_live\">\n                                        <option value=\"None\">None</option>\n                                        <option value=\"Clone\">Clone Via Facebook</option>\n                                        <option value=\"Hotmail\">Hotmail & Outlook\n                                        </option>\n                                        <option value=\"Gmail\">Gmail (Cấu hình API Key tại Cài đặt -> Kết nối)\n                                        </option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Trạng thái:");
echo " <span\n                                            class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" name=\"status\" required>\n                                        <option value=\"1\">ON</option>\n                                        <option value=\"0\">OFF</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-4 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Quốc gia:");
echo " (nếu\n                                        có)</label>\n                                    <input type=\"text\" class=\"form-control\" name=\"flag\"\n                                        placeholder=\"Country Codes VD: Việt Nam = vn, Mỹ = us, Thái Lan = th\">\n                                    <small>Truy cập vào <a class=\"text-primary\"\n                                            href=\"https://www.nationsonline.org/oneworld/country_code_list.htm\"\n                                            target=\"_blank\">đây</a> để sao chép Code Alpha 2</small>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mô tả ngắn:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <textarea class=\"form-control\" rows=\"3\" name=\"short_desc\"\n                                        placeholder=\"Nhập mô tả ngắn cho sản phẩm\" required></textarea>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mô tả chi tiết:");
echo "</label>\n                                    <textarea class=\"description\" id=\"description\" name=\"description\"></textarea>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\"\n                                        for=\"example-hf-email\">";
echo __("Lưu ý xuất hiện khi xem đơn hàng:");
echo "</label>\n                                    <textarea class=\"note\" id=\"note\" name=\"note\"></textarea>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\"\n                                        for=\"example-hf-email\">";
echo __("Nội dung đầu tiên trong tệp .txt:");
echo "</label>\n                                    <textarea class=\"form-control\" name=\"text_txt\"></textarea>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <div class=\"mb-3\">\n                                        <label class=\"form-label\"\n                                            for=\"example-file-input-multiple\">";
echo __("Ảnh sản phẩm:");
echo "</label>\n                                        <input class=\"form-control\" type=\"file\" name=\"images[]\" multiple>\n                                        <small>Có thể chọn 1 hoặc nhiều ảnh</small>\n                                    </div>\n                                </div>\n                            </div>\n\n\n                        </div>\n                    </div>\n                </div>\n                <div class=\"col-xl-4\">\n                    <div class=\"card custom-card\">\n                        <div class=\"card-body\">\n                            <div class=\"row mb-3\">\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
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
        echo "                                        <option value=\"";
        echo $option1["id"];
        echo "\">__";
        echo $option1["name"];
        echo "</option>\n                                        ";
    }
    echo "                                        ";
}
echo "                                    </select>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Mã sản phẩm:");
echo " <span\n                                            class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"code\"\n                                        value=\"";
echo isset($_GET["code"]) ? check_string($_GET["code"]) : uniqid();
echo "\">\n                                    <small>";
echo __("Mã sản phẩm dùng để phân loại kho hàng, 2 sản phẩm giống mã sản phẩm sẽ dùng chung 1 kho hàng.");
echo "</small>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\"\n                                        for=\"example-hf-email\">";
echo __("Tài khoản nào trong kho hàng được ưu tiên bán trước?");
echo "</label>\n                                    <select class=\"form-control\" name=\"order_by\" required>\n                                        <option value=\"1\">";
echo __("Check live gần nhất");
echo "</option>\n                                        <option value=\"2\">";
echo __("Import lâu nhất");
echo "</option>\n                                        <option value=\"3\">";
echo __("Import gần nhất");
echo "</option>\n                                        <option value=\"4\">";
echo __("Ngẫu nhiên");
echo "</option>\n                                    </select>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger shadow-danger btn-wave\"\n                                href=\"";
echo base_url_admin("products");
echo "\"><i class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"submit\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                                    class=\"fa fa-fw fa-plus me-1\"></i> Submit</button>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </form>\n    </div>\n</div>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nCKEDITOR.replace(\"description\");\nCKEDITOR.replace(\"note\");\n</script>";

?>