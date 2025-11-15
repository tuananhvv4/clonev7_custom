<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Chỉnh sửa bài viết"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `posts` WHERE `id` = '" . $id . "' "))) {
        redirect(base_url_admin("blogs"));
    }
} else {
    redirect(base_url_admin("blogs"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_blog")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["Save"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("Không được dùng chức năng này vì đây là trang web demo.") . "\")){window.history.back().location.reload();}</script>");
    }
    if($CMSNT->get_row("SELECT * FROM `posts` WHERE `title` = '" . check_string($_POST["title"]) . "' AND `id` != " . $row["id"] . " ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("Tiêu đề bài viết đã tồn tại trong hệ thống.") . "\")){window.history.back().location.reload();}</script>");
    }
    if(check_img("image")) {
        unlink($row["image"]);
        $rand = random("0123456789QWERTYUIOPASDGHJKLZXCVBNM", 4);
        $uploads_dir = "assets/storage/images/blog" . $rand . ".png";
        $tmp_name = $_FILES["image"]["tmp_name"];
        $addlogo = move_uploaded_file($tmp_name, $uploads_dir);
        if($addlogo) {
            $CMSNT->update("posts", ["image" => $uploads_dir], " `id` = '" . $row["id"] . "' ");
        }
    }
    $isInsert = $CMSNT->update("posts", ["title" => check_string($_POST["title"]), "slug" => check_string($_POST["slug"]), "content" => isset($_POST["content"]) ? base64_encode($_POST["content"]) : NULL, "status" => check_string($_POST["status"])], " `id` = '" . $row["id"] . "' ");
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Chỉnh sửa bài viết") . " (" . $row["title"] . " ID " . $row["id"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Chỉnh sửa bài viết") . " (" . $row["title"] . " ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công!\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại!\")){window.history.back().location.reload();}</script>");
}
echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Chỉnh sửa bài viết</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("blogs");
echo "\">Blogs</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Chỉnh sửa bài viết</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA BÀI VIẾT\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <div class=\"col-sm-12\">\n                                    <div class=\"mb-4\">\n                                        <label class=\"form-label\" for=\"name\">";
echo __("Tiêu đề bài viết:");
echo "</label>\n                                        <input type=\"text\" class=\"form-control\" value=\"";
echo $row["title"];
echo "\" name=\"title\"\n                                            required>\n                                    </div>\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Slug:\n                                            <span class=\"text-danger\">*</span></label>\n                                        <div class=\"col-sm-8\">\n                                            <div class=\"input-group\">\n                                                <span class=\"input-group-text\">";
echo base_url("blog/");
echo "</span>\n                                                <input type=\"text\" class=\"form-control\" value=\"";
echo $row["slug"];
echo "\" name=\"slug\" required>\n                                            </div>\n                                        </div>\n                                    </div>\n                                    <div class=\"mb-4\">\n                                        <label class=\"form-label\" for=\"code\">";
echo __("Ảnh nổi bật:");
echo "</label>\n                                        <input type=\"file\" class=\"custom-file-input mb-2\" name=\"image\">\n                                        <img src=\"";
echo base_url($row["image"]);
echo "\" width=\"200px\">\n                                    </div>\n                                    <div class=\"mb-4\">\n                                        <label class=\"form-label\"\n                                            for=\"symbol_left\">";
echo __("Nội dung bài viết:");
echo "</label>\n                                        <textarea id=\"content\"\n                                            name=\"content\">";
echo base64_decode($row["content"]);
echo "</textarea>\n                                    </div>\n                                    <div class=\"mb-4\">\n                                        <label class=\"form-label\" for=\"symbol_right\">";
echo __("Status:");
echo "</label>\n                                        <select class=\"form-control\" name=\"status\" required>\n                                            <option ";
echo $row["status"] == 1 ? "selected" : "";
echo " value=\"1\">ON</option>\n                                            <option ";
echo $row["status"] == 0 ? "selected" : "";
echo " value=\"0\">OFF</option>\n                                        </select>\n                                    </div>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger\" href=\"";
echo base_url_admin("blogs");
echo "\"><i\n                                    class=\"fa fa-fw fa-undo me-1\"></i> ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"Save\" class=\"btn btn-primary\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nCKEDITOR.replace(\"content\");\n</script>";

?>