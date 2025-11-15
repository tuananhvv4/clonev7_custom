<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Chỉnh sửa Task", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `automations` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        redirect(base_url("admin/automations"));
    }
} else {
    redirect(base_url("admin/automations"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_automations")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["SaveTask"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_automations")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    if(empty($_POST["type"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng chọn loại công việc\")){window.history.back().location.reload();}</script>");
    }
    $type = check_string($_POST["type"]);
    if(empty($_POST["product_id"])) {
        $product_id = NULL;
    } else {
        $product_id = json_encode($_POST["product_id"]);
    }
    if(empty($_POST["schedule"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập thời gian\")){window.history.back().location.reload();}</script>");
    }
    $schedule = check_string($_POST["schedule"]);
    $isUpdate = $CMSNT->update("automations", ["name" => !empty($_POST["name"]) ? check_string($_POST["name"]) : NULL, "type" => $type, "product_id" => $product_id, "schedule" => $schedule, "other" => !empty($_POST["other"]) ? check_string($_POST["other"]) : NULL, "update_gettime" => gettime()], " `id` = '" . $id . "' ");
    if($isUpdate) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Edit Task Automation (" . $row["name"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Edit Task Automation (" . $row["name"] . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công !\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại !\")){window.history.back().location.reload();}</script>");
}
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-tags\"></i> Chỉnh sửa công việc '<b\n                    style=\"color:red;\">";
echo $row["name"];
echo "</b>'</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("automations");
echo "\">Automations</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Edit Task</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA CÔNG VIỆC\n                        </div>\n                    </div>\n                    <div class=\"card-body\" onchange=\"loadform()\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Tên công việc</label>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"input-group mb-3\">\n                                        <textarea class=\"form-control\" name=\"name\" placeholder=\"Nhập tên mô tả task nếu có\">";
echo $row["name"];
echo "</textarea>\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Loại công việc (<span\n                                        class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"input-group mb-3\">\n                                        <select class=\"form-control\" name=\"type\" id=\"type\">\n                                            <option> -- Chọn loại công việc --</option>\n                                            <option value=\"delete_order\" ";
echo $row["type"] == "delete_order" ? "selected" : "";
echo ">Xóa tài khoản đã bán</option>\n                                            <option value=\"delete_order_not_uid\" ";
echo $row["type"] == "delete_order_not_uid" ? "selected" : "";
echo ">Xóa tài khoản đã bán, không xóa UID</option>\n                                            <option value=\"delete_order_revenue\" ";
echo $row["type"] == "delete_order_revenue" ? "selected" : "";
echo ">Xóa đơn hàng & tài khoản đã bán</option>\n                                            <option value=\"change_warehouse\" ";
echo $row["type"] == "change_warehouse" ? "selected" : "";
echo ">Thay đổi kho hàng</option>\n                                        </select>\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\" id=\"product_id_input\" style=\"display: none;\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Sản phẩm áp dụng (<span\n                                        class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control\" name=\"product_id[]\" id=\"listProduct\" multiple>\n                                        <option value=\"\">Mặc định sẽ áp dụng cho toàn bộ sản phẩm nếu không chọn\n                                        </option>\n                                        ";
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
echo "                                    </select>\n                                </div>\n                                <script>\n                                const multipleCancelButton = new Choices(\n                                    '#listProduct', {\n                                        allowHTML: true,\n                                        removeItemButton: true,\n                                    }\n                                );\n                                </script>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Thời gian (<span\n                                        class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"input-group mb-3\">\n                                        <input class=\"form-control\" name=\"schedule\" value=\"";
echo $row["schedule"];
echo "\" id=\"schedule\" onkeyup=\"loadform()\"\n                                            value=\"604800\" placeholder=\"Nhập giây, ví dụ 1 ngày = 86400\" required>\n                                        <span class=\"input-group-text\">\n                                            Giây\n                                        </span>\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\" id=\"warehouse_input\" style=\"display: none;\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Kho hàng nhận (<span\n                                        class=\"text-danger\">*</span>)</label>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"input-group mb-3\">\n                                        <input class=\"form-control\" name=\"other\" id=\"other\" value=\"";
echo $row["other"];
echo "\" onkeyup=\"loadform()\"\n                                            placeholder=\"Mã kho hàng\">\n                                    </div>\n                                </div>\n                            </div>\n\n                            <p id=\"mota\">Vui lòng chọn loại công việc</p>\n\n                            <script>\n                            function formatTime(seconds) {\n                                var days = Math.floor(seconds / (60 * 60 * 24));\n                                var hours = Math.floor((seconds % (60 * 60 * 24)) / (60 * 60));\n                                var minutes = Math.floor((seconds % (60 * 60)) / 60);\n                                var remainingSeconds = seconds % 60;\n\n                                var result = '';\n                                if (days > 0) {\n                                    result += days + ' ngày ';\n                                }\n                                if (hours > 0) {\n                                    result += hours + ' giờ ';\n                                }\n                                if (minutes > 0) {\n                                    result += minutes + ' phút ';\n                                }\n                                if (remainingSeconds > 0) {\n                                    result += remainingSeconds + ' giây';\n                                }\n\n                                return result.trim();\n                            }\n\n                            function loadform() {\n                                var type = \$('#type').val();\n                                var schedule = \$('#schedule').val();\n                                var formattedTime = formatTime(schedule);\n\n                                \$('#warehouse_input').hide();\n                                \$('#product_id_input').hide();\n                                \n                                if (type == 'change_warehouse') {\n                                    \$('#warehouse_input').show();\n                                    \$('#product_id_input').show();\n                                    \$('#mota').html(\n                                        'Nếu bạn tạo Task này => Hệ thống sẽ thực hiện chuyển những tài khoản trong sản phẩm bạn chọn vào kho hàng <b style=\"color:blue;\">' +\n                                        \$('#other').val() + '</b> nếu quá <b style=\"color:red;\">' + formattedTime +\n                                        '</b> chưa được bán.');\n                                } else if (type == 'delete_order') {\n                                    \$('#product_id_input').show();\n                                    \$('#mota').html(\n                                        'Nếu bạn tạo Task này => Hệ thống sẽ thực hiện xóa tài khoản đã bán sau <b style=\"color:red;\">' +\n                                        formattedTime + '</b>, chỉ áp dụng các sản phẩm bạn chọn ở trên.');\n                                } else if (type == 'delete_order_not_uid') {\n                                    \$('#product_id_input').show();\n                                    \$('#mota').html(\n                                        'Nếu bạn tạo Task này => Hệ thống sẽ thực hiện xóa tài khoản đã bán, không xóa UID sau <b style=\"color:red;\">' +\n                                        formattedTime + '</b>, chỉ áp dụng các sản phẩm bạn chọn ở trên.');\n                                } else if (type == 'delete_order_revenue') {\n                                    \$('#product_id_input').show();\n                                    \$('#mota').html(\n                                        'Nếu bạn tạo Task này => Hệ thống sẽ thực hiện xóa đơn hàng và tài khoản đã bán sau <b style=\"color:red;\">' +\n                                        formattedTime + '</b>, chỉ áp dụng các sản phẩm bạn chọn ở trên.');\n                                } else {\n                                    \$('#mota').html('Vui lòng chọn loại công việc');\n                                }\n                            }\n                            // Sự kiện DOMContentLoaded\n                            document.addEventListener(\"DOMContentLoaded\", function(event) {\n                                // Gọi hàm loadform khi trang đã tải xong\n                                loadform();\n                            });\n                            </script>\n                            <a type=\"button\" class=\"btn btn-danger shadow-danger btn-wave\"\n                                href=\"";
echo base_url_admin("automations");
echo "\"><i class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"SaveTask\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n";
require_once __DIR__ . "/footer.php";

?>