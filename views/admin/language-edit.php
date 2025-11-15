<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Language editing"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `languages` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        redirect(base_url("admin/language-list"));
    }
} else {
    redirect(base_url("admin/language-list"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_lang")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["SaveLang"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    if(check_img("icon")) {
        $rand = check_string($_POST["lang"]);
        $uploads_dir = "assets/storage/flags/flag_" . $rand . ".png";
        $tmp_name = $_FILES["icon"]["tmp_name"];
        $addIcon = move_uploaded_file($tmp_name, $uploads_dir);
        if($addIcon) {
            $icon = "assets/storage/flags/flag_" . $rand . ".png";
            $CMSNT->update("languages", ["icon" => $icon], " `id` = '" . $row["id"] . "' ");
        }
    }
    $isInsert = $CMSNT->update("languages", ["lang" => check_string($_POST["lang"]), "status" => check_string($_POST["status"])], " `id` = '" . $row["id"] . "' ");
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Chỉnh sửa ngôn ngữ") . " (" . $row["id"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Chỉnh sửa ngôn ngữ") . " (" . $row["id"] . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("Save successfully!") . "\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Save failure!") . "\")){window.history.back().location.reload();}</script>");
}
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Edit Language</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("language-list");
echo "\">Languages</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">";
echo $row["lang"];
echo "</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA NGÔN NGỮ\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Tên ngôn ngữ</label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"text\" value=\"";
echo $row["lang"];
echo "\" class=\"form-control\" name=\"lang\"\n                                        placeholder=\"Nhập tên ngôn ngữ VD: English\" required>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Flag</label>\n                                <div class=\"col-sm-8\">\n                                    <input class=\"form-control mb-2\" type=\"file\" name=\"icon\" id=\"example-file-input\">\n                                    <img src=\"";
echo base_url($row["icon"]);
echo "\" width=\"100px\">\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Trạng thái</label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control\" name=\"status\" required>\n                                        <option ";
echo $row["status"] == 1 ? "selected" : "";
echo " value=\"1\">";
echo __("Show");
echo "                                        </option>\n                                        <option ";
echo $row["status"] == 0 ? "selected" : "";
echo " value=\"0\">";
echo __("Hide");
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger shadow-danger btn-wave\" href=\"";
echo base_url_admin("language-list");
echo "\"><i\n                                    class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"SaveLang\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n";
require_once __DIR__ . "/footer.php";

?>