<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Chỉnh sửa mã giảm giá", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `coupons` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        redirect(base_url("admin/coupons"));
    }
} else {
    redirect(base_url("admin/coupons"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_coupon")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["SaveCoupon"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["amount"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Số lượng không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    $amount = check_string($_POST["amount"]);
    if($amount <= 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Số lượng không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["discount"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập chiết khấu giảm giá\")){window.history.back().location.reload();}</script>");
    }
    $discount = check_string($_POST["discount"]);
    $product_id = json_encode($_POST["product_id"]);
    if(empty($_POST["product_id"])) {
        $product_id = NULL;
    }
    if(empty($_POST["min"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập giá trị đơn hàng tối thiểu\")){window.history.back().location.reload();}</script>");
    }
    $min = check_string($_POST["min"]);
    if($min <= 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Giá trị đơn hàng tối thiểu không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["max"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập giá trị đơn hàng tối đa\")){window.history.back().location.reload();}</script>");
    }
    $max = check_string($_POST["max"]);
    if($max <= 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Giá trị đơn hàng tối đa không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    $isUpdate = $CMSNT->update("coupons", ["product_id" => $product_id, "amount" => $amount, "discount" => $discount, "update_gettime" => gettime(), "min" => $min, "max" => $max], " `id` = '" . $id . "' ");
    if($isUpdate) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Edit Coupon (" . $row["code"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Edit Coupon (" . $row["code"] . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công !\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại !\")){window.history.back().location.reload();}</script>");
}
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-tags\"></i> Chỉnh sửa mã giảm giá '<b\n                    style=\"color:red;\">";
echo $row["code"];
echo "</b>'</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("coupons");
echo "\">Coupons</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Edit Coupon</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA MÃ GIẢM GIÁ\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Số lượng mã giảm giá\n                                    (<span class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"input-group mb-3\">\n                                        <button class=\"btn btn-primary shadow-primary\" type=\"button\"\n                                            id=\"button-minus-amount\"><i class=\"fa-solid fa-minus\"></i></button>\n                                        <input type=\"number\" class=\"form-control text-center\" placeholder=\"\"\n                                            value=\"";
echo $row["amount"];
echo "\" name=\"amount\" required>\n                                        <button class=\"btn btn-primary shadow-primary\" type=\"button\"\n                                            id=\"button-plus-amount\"><i class=\"fa-solid fa-plus\"></i></button>\n                                    </div>\n                                    <script>\n                                    document.getElementById('button-plus-amount').addEventListener('click', function() {\n                                        incrementValue();\n                                    });\n                                    document.getElementById('button-minus-amount').addEventListener('click',\n                                        function() {\n                                            decrementValue();\n                                        });\n\n                                    function incrementValue() {\n                                        var inputElement = document.getElementsByName('amount')[0];\n                                        var currentValue = parseInt(inputElement.value, 10);\n                                        inputElement.value = currentValue + 1;\n                                    }\n\n                                    function decrementValue() {\n                                        var inputElement = document.getElementsByName('amount')[0];\n                                        var currentValue = parseInt(inputElement.value, 10);\n                                        if (currentValue > 1) {\n                                            inputElement.value = currentValue - 1;\n                                        }\n                                    }\n                                    </script>\n                                    <small>Nếu bạn chọn 10, sẽ có 10 lượt sử dụng mã giảm giá cho 10 user khác\n                                        nhau.</small>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Chiết khấu giảm (<span\n                                        class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"input-group\">\n                                        <input type=\"text\" class=\"form-control\" name=\"discount\"\n                                            value=\"";
echo $row["discount"];
echo "\" required>\n                                        <span class=\"input-group-text\">\n                                            <i class=\"fa-solid fa-percent\"></i>\n                                        </span>\n                                    </div>\n                                    <small>Nhập 10 tức giảm 10% cho đơn hàng áp dụng mã giảm giá này.</small>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Sản phẩm áp dụng (<span\n                                        class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control\" name=\"product_id[]\" id=\"listProduct\" multiple>\n                                        <option value=\"\">Mặc định sẽ áp dụng cho toàn bộ sản phẩm nếu không chọn\n                                        </option>\n                                        ";
foreach ($CMSNT->get_list(" SELECT * FROM `categories` ") as $category) {
    echo "                                        <optgroup label=\"__";
    echo $category["name"];
    echo "__\">\n                                            ";
    foreach ($CMSNT->get_list(" SELECT * FROM `products` WHERE `category_id` = '" . $category["id"] . "' ") as $product) {
        echo "                                            <option\n                                                ";
        echo in_array($product["id"], json_decode($row["product_id"] ?? "[]", true) ?? [], true) ? "selected" : "";
        echo "                                                value=\"";
        echo $product["id"];
        echo "\">";
        echo $product["name"];
        echo "</option>\n\n                                            ";
    }
    echo "                                        </optgroup>\n                                        ";
}
echo "                                    </select>\n                                </div>\n                                <script>\n                                const multipleCancelButton = new Choices(\n                                    '#listProduct', {\n                                        allowHTML: true,\n                                        removeItemButton: true,\n                                    }\n                                );\n                                </script>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Giá trị đơn hàng tối thiểu\n                                    (<span class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"input-group\">\n                                        <input type=\"text\" class=\"form-control\" value=\"";
echo $row["min"];
echo "\" name=\"min\"\n                                            required>\n                                        <span class=\"input-group-text\">\n                                            ";
echo currencyDefault();
echo "                                        </span>\n                                    </div>\n                                    <small>Giá trị đơn hàng tối thiểu để áp dụng mã giảm giá</small>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Giá trị đơn hàng tối đa\n                                    (<span class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"input-group\">\n                                        <input type=\"text\" class=\"form-control\" value=\"";
echo $row["max"];
echo "\" name=\"max\"\n                                            required>\n                                        <span class=\"input-group-text\">\n                                            ";
echo currencyDefault();
echo "                                        </span>\n                                    </div>\n                                    <small>Giá trị đơn hàng tối đa để áp dụng mã giảm giá</small>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger shadow-danger btn-wave\"\n                                href=\"";
echo base_url_admin("coupons");
echo "\"><i class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"SaveCoupon\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n";
require_once __DIR__ . "/footer.php";

?>