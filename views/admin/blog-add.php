<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Viết bài mới"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_blog")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back().location.reload();}</script>");
}
if(isset($_POST["submit"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if($CMSNT->get_row("SELECT * FROM `posts` WHERE `title` = '" . check_string($_POST["title"]) . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Tiêu đề bài viết đã tồn tại.\")){window.history.back().location.reload();}</script>");
    }
    $url_icon = NULL;
    if(check_img("image")) {
        $rand = random("0123456789QWERTYUIOPASDGHJKLZXCVBNM", 4);
        $uploads_dir = "assets/storage/images/image" . $rand . ".png";
        $tmp_name = $_FILES["image"]["tmp_name"];
        $addlogo = move_uploaded_file($tmp_name, $uploads_dir);
        if($addlogo) {
            $url_icon = $uploads_dir;
        }
    }
    $isInsert = $CMSNT->insert("posts", ["user_id" => $getUser["id"], "image" => $url_icon, "title" => check_string($_POST["title"]), "slug" => check_string($_POST["slug"]), "category_id" => check_string($_POST["category_id"]), "content" => isset($_POST["content"]) ? base64_encode($_POST["content"]) : NULL, "status" => check_string($_POST["status"]), "create_gettime" => gettime()]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Tạo bài viết mới") . " (" . check_string($_POST["title"]) . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Tạo bài viết mới") . " (" . check_string($_POST["title"]) . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công !\")){location.href = \"\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại !\")){window.history.back().location.reload();}</script>");
}
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Viết bài mới</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("blogs");
echo "\">Blogs</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Viết bài mới</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            THÊM BÀI VIẾT MỚI\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <div class=\"row mb-4\">\n                                    <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Tiêu đề bài viết:\n                                        <span class=\"text-danger\">*</span></label>\n                                    <div class=\"col-sm-8\">\n                                        <input name=\"title\" type=\"text\" class=\"form-control\" required>\n                                    </div>\n                                </div>\n                                <div class=\"row mb-4\">\n                                    <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Slug:\n                                        <span class=\"text-danger\">*</span></label>\n                                    <div class=\"col-sm-8\">\n                                        <div class=\"input-group\">\n                                            <span class=\"input-group-text\">";
echo base_url("blog/");
echo "</span>\n                                            <input type=\"text\" class=\"form-control\" name=\"slug\" required>\n                                        </div>\n                                    </div>\n                                </div>\n                                <script>\n                                function removeVietnameseTones(str) {\n                                    return str.normalize('NFD') // Tách tổ hợp ký tự và dấu\n                                        .replace(/[\\u0300-\\u036f]/g, '') // Loại bỏ dấu\n                                        .replace(/đ/g, 'd') // Chuyển đổi chữ \"đ\" thành \"d\"\n                                        .replace(/Đ/g, 'D'); // Chuyển đổi chữ \"Đ\" thành \"D\"\n                                }\n\n                                document.querySelector('input[name=\"title\"]').addEventListener('input', function() {\n                                    var productName = this.value;\n\n                                    // Chuyển tên sản phẩm thành slug\n                                    var slug = removeVietnameseTones(productName.toLowerCase())\n                                        .replace(/ /g, '-') // Thay khoảng trắng bằng dấu gạch ngang\n                                        .replace(/[^\\w-]+/g, ''); // Loại bỏ các ký tự không hợp lệ\n\n                                    // Đặt giá trị slug vào trường input slug\n                                    document.querySelector('input[name=\"slug\"]').value = slug;\n                                });\n                                </script>\n                                <div class=\"row mb-4\">\n                                    <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Ảnh nổi bật:\n                                        <span class=\"text-danger\">*</span></label>\n                                    <div class=\"col-sm-8\">\n                                        <input type=\"file\" class=\"custom-file-input\" name=\"image\">\n                                    </div>\n                                </div>\n                                <div class=\"row mb-4\">\n                                    <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Chuyên mục");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <div class=\"col-sm-8\">\n                                        <select class=\"form-select\" name=\"category_id\" required>\n                                            <option value=\"\">-- ";
echo __("Chọn chuyên mục");
echo " --</option>\n                                            ";
foreach ($CMSNT->get_list(" SELECT * FROM `post_category` ") as $category) {
    echo "                                            <option value=\"";
    echo $category["id"];
    echo "\">";
    echo $category["name"];
    echo "</option>\n                                            ";
}
echo "                                        </select>\n                                    </div>\n                                </div>\n                                <div class=\"row mb-4\">\n                                    <label class=\"col-sm-4 col-form-label\"\n                                        for=\"example-hf-email\">";
echo __("Nội dung chi tiết:");
echo "</label>\n                                    <div class=\"col-sm-12\">\n                                        <textarea class=\"content\" id=\"content\" name=\"content\"></textarea>\n                                    </div>\n                                </div>\n                                <div class=\"row mb-4\">\n                                    <label class=\"col-sm-4 col-form-label\"\n                                        for=\"example-hf-email\">";
echo __("Trạng thái:");
echo " <span\n                                            class=\"text-danger\">*</span></label>\n                                    <div class=\"col-sm-8\">\n                                        <select class=\"form-control\" name=\"status\" required>\n                                            <option value=\"1\">ON</option>\n                                            <option value=\"0\">OFF</option>\n                                        </select>\n                                    </div>\n                                </div>\n\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger\" href=\"";
echo base_url_admin("blogs");
echo "\"><i\n                                    class=\"fa fa-fw fa-undo me-1\"></i> ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"submit\" class=\"btn btn-primary\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Submit");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n\n    </div>\n</div>\n\n\n\n<!-- Main Container -->\n<main id=\"main-container\">\n\n    <!-- Page Content -->\n    <div class=\"content\">\n        <div\n            class=\"d-md-flex justify-content-md-between align-items-md-center py-3 pt-md-3 pb-md-0 text-center text-md-start mb-3\">\n            <div>\n                <h1 class=\"h3 mb-1\">\n                    ";
echo __("Thêm bài viết");
echo "                </h1>\n                <p class=\"fw-medium mb-0 text-muted\">\n\n                </p>\n            </div>\n            <div class=\"mt-4 mt-md-0\">\n\n            </div>\n        </div>\n\n        <div class=\"block block-rounded\">\n            <div class=\"block-content block-content-full\">\n\n            </div>\n        </div>\n        <!-- END Dynamic Table Full -->\n    </div>\n    <!-- END Page Content -->\n</main>\n<!-- END Main Container -->\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nCKEDITOR.replace(\"content\");\n</script>";

?>