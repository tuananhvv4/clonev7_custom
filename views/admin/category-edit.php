<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Category editing"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `categories` WHERE `id` = '" . $id . "' "))) {
        redirect(base_url_admin("categories"));
    }
} else {
    redirect(base_url_admin("categories"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_product")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["SaveCategory"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if($CMSNT->get_row("SELECT * FROM `categories` WHERE `name` = '" . check_string($_POST["name"]) . "' AND `id` != " . $row["id"] . " ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Tên chuyên mục đã tồn tại trong hệ thống.\")){window.history.back().location.reload();}</script>");
    }
    if(check_img("icon")) {
        unlink($row["icon"]);
        $rand = random("0123456789QWERTYUIOPASDGHJKLZXCVBNM", 4);
        $uploads_dir = "assets/storage/images/category" . $rand . ".png";
        $tmp_name = $_FILES["icon"]["tmp_name"];
        $addlogo = move_uploaded_file($tmp_name, $uploads_dir);
        if($addlogo) {
            $CMSNT->update("categories", ["icon" => $uploads_dir], " `id` = '" . $row["id"] . "' ");
        }
    }
    $isInsert = $CMSNT->update("categories", ["stt" => check_string($_POST["stt"]), "parent_id" => check_string($_POST["parent_id"]), "name" => check_string($_POST["name"]), "slug" => create_slug(check_string($_POST["name"])), "description" => check_string($_POST["description"]), "status" => check_string($_POST["status"])], " `id` = '" . $row["id"] . "' ");
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Edit Category (" . $row["name"] . " ID " . $row["id"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Edit Category (" . $row["name"] . " ID " . $row["id"] . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công!\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại!\")){window.history.back().location.reload();}</script>");
}
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><a type=\"button\" class=\"btn btn-dark btn-raised-shadow btn-wave btn-sm me-1\"\n                    href=\"";
echo base_url_admin("categories");
echo "\"><i class=\"fa-solid fa-arrow-left\"></i></a> Chỉnh sửa chuyên mục ";
echo $row["name"];
echo "</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA CHUYÊN MỤC SẢN PHẨM\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <div class=\"col-sm-12\">\n                                    <div class=\"mb-4\">\n                                        <label class=\"form-label\" for=\"stt\">";
echo __("Ưu tiên:");
echo "</label>\n                                        <input type=\"text\" class=\"form-control\" value=\"";
echo $row["stt"];
echo "\" name=\"stt\" required>\n                                        <small>Lưu ý: Ưu tiên càng cao, chuyên mục càng hiển thị trên cùng</small>\n                                    </div>\n                                    <div class=\"mb-4\">\n                                        <label class=\"form-label\" for=\"name\">";
echo __("Tên chuyên mục:");
echo "</label>\n                                        <input type=\"text\" class=\"form-control\" value=\"";
echo $row["name"];
echo "\" name=\"name\"\n                                            placeholder=\"Nhập tên chuyên mục\" required>\n                                    </div>\n                                    <div class=\"row mb-4\" ";
echo $row["parent_id"] == 0 ? "style=\"display:none;\"" : "";
echo ">\n                                        <label class=\"col-sm-4 col-form-label\"\n                                            for=\"example-hf-email\">";
echo __("Chuyên mục cha:");
echo "                                            <span class=\"text-danger\">*</span></label>\n                                        <div class=\"col-sm-8\">\n                                            <select class=\"form-control mb-2\" name=\"parent_id\" required>\n                                                <option value=\"0\">";
echo __("-- Chuyên mục cha --");
echo "</option>\n                                                ";
foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = 0 ") as $option) {
    echo "                                                <option ";
    echo $option["id"] == $row["parent_id"] ? "selected" : "";
    echo "                                                    value=\"";
    echo $option["id"];
    echo "\">";
    echo $option["name"];
    echo "</option>\n                                                ";
    foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = '" . $option["id"] . "' ") as $option1) {
        echo "                                                <option disabled value=\"";
        echo $option1["id"];
        echo "\">__";
        echo $option1["name"];
        echo "                                                </option>\n                                                ";
    }
    echo "                                                ";
}
echo "                                            </select>\n                                            <i>";
echo __("Chỉ định một chuyên mục cha để tạo đa cấp. Chẳng hạn, chuyên mục Facebook sẽ là chuyên mục cha của Clone Facebook và VIA Facebook.");
echo "</i>\n                                        </div>\n                                    </div>\n                                    <div class=\"mb-4\">\n                                        <label class=\"form-label\" for=\"code\">";
echo __("Icon:");
echo "</label>\n                                        <input type=\"file\" class=\"custom-file-input mb-2\" name=\"icon\">\n                                        <img src=\"";
echo base_url($row["icon"]);
echo "\" width=\"50px\">\n                                    </div>\n                                    <div class=\"mb-4\">\n                                        <label class=\"form-label\" for=\"symbol_left\">";
echo __("Description SEO:");
echo "</label>\n                                        <textarea class=\"form-control\" rows=\"3\"\n                                            name=\"description\">";
echo $row["description"];
echo "</textarea>\n                                    </div>\n                                    <div class=\"mb-4\">\n                                        <label class=\"form-label\" for=\"symbol_right\">";
echo __("Status:");
echo "</label>\n                                        <select class=\"form-control\" name=\"status\" required>\n                                            <option ";
echo $row["status"] == 1 ? "selected" : "";
echo " value=\"1\">ON</option>\n                                            <option ";
echo $row["status"] == 0 ? "selected" : "";
echo " value=\"0\">OFF</option>\n                                        </select>\n                                    </div>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger\" href=\"";
echo base_url_admin("categories");
echo "\"><i\n                                    class=\"fa fa-fw fa-undo me-1\"></i> ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"SaveCategory\" class=\"btn btn-primary\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n ";

?>