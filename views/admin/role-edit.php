<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Edit Role", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    if(!($row = $CMSNT->get_row("SELECT * FROM `admin_role` WHERE `id` = '" . $id . "' "))) {
        redirect(base_url_admin("roles"));
    }
} else {
    redirect(base_url_admin("roles"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_role")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["Save"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("Không được dùng chức năng này vì đây là trang web demo.") . "\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["name"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập tên vai trò\")){window.history.back().location.reload();}</script>");
    }
    $name = check_string($_POST["name"]);
    if(empty($_POST["role"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng chọn quyền cho role\")){window.history.back().location.reload();}</script>");
    }
    $role = json_encode($_POST["role"]);
    $isInsert = $CMSNT->update("admin_role", ["name" => $name, "role" => $role, "update_gettime" => gettime()], " `id` = '" . $row["id"] . "' ");
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Edit Role (" . $name . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Edit Role (" . $name . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công!\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại!\")){window.history.back().location.reload();}</script>");
}
echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-shield-halved\"></i> Chỉnh sửa vai trò '<b style=\"color:red;\">";
echo $row["name"];
echo "</b>'</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("roles");
echo "\">Roles</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Chỉnh sửa</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA ROLE\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Tên vai trò (<span\n                                        class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"text\" class=\"form-control\" name=\"name\" value=\"";
echo $row["name"];
echo "\" placeholder=\"VD: Super Admin\"\n                                        required>\n                                </div>\n                            </div>\n                            <div class=\"form-check form-check-md d-flex align-items-center mb-2\">\n                                <input class=\"form-check-input\" type=\"checkbox\" value=\"\" id=\"selectAll\"\n                                    onclick=\"toggleAllCheckboxes()\">\n                                <label class=\"form-check-label\" for=\"selectAll\">\n                                    Chọn tất cả các quyền\n                                </label>\n                            </div>\n                            <div class=\"row mb-4\">\n                                ";
foreach ($admin_roles as $category => $roles) {
    echo "                                <hr>\n                                <div class=\"col-4\">\n                                    <div class=\"form-check form-check-md d-flex align-items-center\">\n                                        <input class=\"form-check-input\" type=\"checkbox\" value=\"\"\n                                            id=\"";
    echo strtolower(str_replace(" ", "_", $category));
    echo "\"\n                                            onclick=\"toggleCategory('";
    echo strtolower(str_replace(" ", "_", $category));
    echo "')\">\n                                        <label class=\"form-check-label\"\n                                            for=\"";
    echo strtolower(str_replace(" ", "_", $category));
    echo "\">\n                                            ";
    echo $category;
    echo "                                        </label>\n                                    </div>\n                                </div>\n                                <div class=\"col-8\">\n                                    ";
    foreach ($roles as $key => $label) {
        echo "                                    <div class=\"form-check\">\n                                        <input class=\"form-check-input\" type=\"checkbox\" value=\"";
        echo $key;
        echo "\"\n                                            name=\"role[]\" id=\"";
        echo $key;
        echo "\" ";
        echo in_array($key, json_decode($row["role"]), true) ? "checked" : "";
        echo "                                            data-category=\"";
        echo strtolower(str_replace(" ", "_", $category));
        echo "\">\n                                        <label class=\"form-check-label\" for=\"";
        echo $key;
        echo "\">\n                                            ";
        echo $label;
        echo " <span class=\"badge bg-primary-transparent\">";
        echo $key;
        echo "</span>\n                                        </label>\n                                    </div>\n                                    ";
    }
    echo "                                </div>\n                                ";
}
echo "                            </div>\n\n                            <script>\n                            function toggleAllCheckboxes() {\n                                var checkboxes = document.querySelectorAll('[name=\"role[]\"]');\n                                var selectAllCheckbox = document.getElementById('selectAll');\n\n                                checkboxes.forEach(function(checkbox) {\n                                    checkbox.checked = selectAllCheckbox.checked;\n                                });\n                            }\n\n                            function toggleCategory(categoryId) {\n                                var checkboxes = document.querySelectorAll('[data-category=\"' + categoryId + '\"]');\n                                var categoryCheckbox = document.getElementById(categoryId);\n                                var selectAllCheckbox = document.getElementById('selectAll');\n\n                                checkboxes.forEach(function(checkbox) {\n                                    checkbox.checked = categoryCheckbox.checked;\n                                });\n\n                                // Kiểm tra xem tất cả ô checkbox trong danh mục đã được chọn hay không\n                                selectAllCheckbox.checked = checkboxes.length === document.querySelectorAll(\n                                    '[data-category=\"' +\n                                    categoryId + '\"]:checked').length;\n                            }\n                            </script>\n                            <a type=\"button\" class=\"btn btn-danger shadow-danger\" href=\"";
echo base_url_admin("roles");
echo "\"><i\n                                    class=\"fa fa-fw fa-undo me-1\"></i> ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"Save\" class=\"btn btn-primary shadow-primary\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo " ";

?>