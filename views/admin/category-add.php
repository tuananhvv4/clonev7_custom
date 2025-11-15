<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Tạo chuyên mục mới"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_product")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back().location.reload();}</script>");
}
$id = 0;
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
}
if(isset($_POST["submit"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_product")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    if($CMSNT->get_row("SELECT * FROM `categories` WHERE `name` = '" . check_string($_POST["name"]) . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Chuyên mục này đã tồn tại trong hệ thống.\")){window.history.back().location.reload();}</script>");
    }
    $url_icon = NULL;
    if(check_img("icon")) {
        $rand = random("0123456789QWERTYUIOPASDGHJKLZXCVBNM", 4);
        $uploads_dir = "assets/storage/images/icon" . $rand . ".png";
        $tmp_name = $_FILES["icon"]["tmp_name"];
        $addlogo = move_uploaded_file($tmp_name, $uploads_dir);
        if($addlogo) {
            $url_icon = $uploads_dir;
        }
    }
    $isInsert = $CMSNT->insert("categories", ["stt" => check_string($_POST["stt"]), "icon" => $url_icon, "name" => check_string($_POST["name"]), "parent_id" => check_string($_POST["parent_id"]), "slug" => create_slug(check_string($_POST["name"])), "description" => check_string($_POST["description"]), "status" => check_string($_POST["status"]), "create_date" => gettime()]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add Category (" . check_string($_POST["name"]) . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Add Category (" . check_string($_POST["name"]) . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công !\")){location.href = \"\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại !\")){window.history.back().location.reload();}</script>");
}
echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><a type=\"button\"\n                    class=\"btn btn-dark btn-raised-shadow btn-wave btn-sm me-1\"\n                    href=\"";
echo base_url_admin("categories");
echo "\"><i class=\"fa-solid fa-arrow-left\"></i></a> Tạo chuyên mục\n                con</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            TẠO CHUYÊN MỤC CON\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"mb-4\">\n                                <label class=\"form-label\" for=\"stt\">";
echo __("Ưu tiên:");
echo "</label>\n                                <input type=\"text\" class=\"form-control\" value=\"0\" name=\"stt\" required>\n                                <small>Lưu ý: Ưu tiên càng cao, chuyên mục càng hiển thị trên cùng</small>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\"\n                                    for=\"example-hf-email\">";
echo __("Tên chuyên mục con:");
echo "                                    <span class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"text\" class=\"form-control\" name=\"name\"\n                                        placeholder=\"";
echo __("Nhập tên chuyên mục");
echo "\" required>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\"\n                                    for=\"example-hf-email\">";
echo __("Chuyên mục cha:");
echo "                                    <span class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control mb-2\" name=\"parent_id\" required>\n                                        ";
foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = 0 ") as $option) {
    echo "                                        <option value=\"";
    echo $option["id"];
    echo "\"\n                                            ";
    echo $id == $option["id"] ? "selected" : "";
    echo ">";
    echo $option["name"];
    echo "</option>\n                                        ";
    foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = '" . $option["id"] . "' ") as $option1) {
        echo "                                        <option disabled value=\"";
        echo $option1["id"];
        echo "\">__";
        echo $option1["name"];
        echo "</option>\n                                        ";
    }
    echo "                                        ";
}
echo "                                    </select>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Icon:");
echo " <span\n                                        class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"file\" class=\"custom-file-input\" name=\"icon\" required>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\"\n                                    for=\"example-hf-email\">";
echo __("Description SEO:");
echo "</label>\n                                <div class=\"col-sm-12\">\n                                    <textarea class=\"form-control\" rows=\"3\" name=\"description\"></textarea>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Status:");
echo " <span\n                                        class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control\" name=\"status\" required>\n                                        <option value=\"1\">ON</option>\n                                        <option value=\"0\">OFF</option>\n                                    </select>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger\" href=\"";
echo base_url_admin("categories");
echo "\"><i\n                                    class=\"fa fa-fw fa-undo me-1\"></i> ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"submit\" class=\"btn btn-primary\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Submit");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n\n    </div>\n</div>\n\n\n\n";
require_once __DIR__ . "/footer.php";

?>