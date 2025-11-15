<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Chỉnh sửa menu", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `menu` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        redirect(base_url("admin/menu-list"));
    }
} else {
    redirect(base_url("admin/menu-list"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_menu")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["SaveMenu"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    $isUpdate = $CMSNT->update("menu", ["name" => check_string($_POST["name"]), "slug" => create_slug(check_string($_POST["name"])), "href" => !empty($_POST["href"]) ? check_string($_POST["href"]) : "", "icon" => $_POST["icon"], "position" => !empty($_POST["position"]) ? check_string($_POST["position"]) : 3, "target" => !empty($_POST["target"]) ? check_string($_POST["target"]) : "", "content" => !empty($_POST["content"]) ? $_POST["content"] : "", "status" => check_string($_POST["status"])], " `id` = '" . $row["id"] . "' ");
    if($isUpdate) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Edit menu (ID " . $row["id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Edit menu (ID " . $row["id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công!\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại!\")){window.history.back().location.reload();}</script>");
}
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-sitemap\"></i> Chỉnh sửa menu '<b\n                    style=\"color:red;\">";
echo $row["name"];
echo "</b>'</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("menu-list");
echo "\">Menu</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">";
echo $row["name"];
echo "</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA MENU\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Tên menu (<span\n                                        class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"text\" class=\"form-control\" value=\"";
echo $row["name"];
echo "\" name=\"name\"\n                                        placeholder=\"Nhập tên menu cần tạo\" required>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Liên kết</label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"text\" class=\"form-control\" value=\"";
echo $row["href"];
echo "\"\n                                        placeholder=\"Nhập địa chỉ liên kết cần tới khi click vào menu này\" name=\"href\">\n                                    <small>Chỉ áp dụng khi nội dung hiển thị trống</small>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-12 col-form-label\" for=\"example-hf-email\">Nội dung hiển thị (nếu\n                                    có)</label>\n                                <div class=\"col-sm-12\">\n                                    <textarea id=\"content\" name=\"content\"\n                                        placeholder=\"Để trống nếu muốn sử dụng liên kết\">";
echo $row["content"];
echo "</textarea>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Vị trí hiển thị</label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control\" name=\"position\" required>\n                                        <option ";
echo $row["position"] == 1 ? "selected" : "";
echo " value=\"1\">Trong menu SỐ DƯ\n                                        </option>\n                                        <option ";
echo $row["position"] == 2 ? "selected" : "";
echo " value=\"2\">Trong menu NẠP\n                                            TIỀN</option>\n                                        <option ";
echo $row["position"] == 3 ? "selected" : "";
echo " value=\"3\">Trong menu KHÁC\n                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Icon menu (<span\n                                        class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"text\" class=\"form-control\"\n                                        placeholder='Ví dụ: <i class=\"fas fa-home\"></i>' name=\"icon\"\n                                        value='";
echo $row["icon"];
echo "' required>\n                                    <small>Tìm thêm icon tại <a target=\"_blank\"\n                                            href=\"https://fontawesome.com/v5.15/icons?d=gallery&p=2\">đây</a></small>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Trạng thái</label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control\" name=\"status\" required>\n                                        <option ";
echo $row["status"] == 1 ? "selected" : "";
echo " value=\"1\">Hiển thị</option>\n                                        <option ";
echo $row["status"] == 0 ? "selected" : "";
echo " value=\"0\">Ẩn</option>\n                                    </select>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <div class=\"col-sm-4\"></div>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"form-check form-check-md d-flex align-items-center mb-2\">\n                                        <input class=\"form-check-input\" type=\"checkbox\" name=\"target\"\n                                            ";
echo $row["target"] == "_blank" ? "checked" : "";
echo " value=\"_blank\"\n                                            id=\"customCheckbox2\" checked>\n                                        <label class=\"form-check-label\" for=\"customCheckbox2\">\n                                            Mở tab mới khi\n                                            click\n                                        </label>\n                                    </div>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger shadow-danger btn-wave\"\n                                href=\"";
echo base_url_admin("menu-list");
echo "\"><i class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"SaveMenu\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nCKEDITOR.replace(\"content\");\n</script>";

?>