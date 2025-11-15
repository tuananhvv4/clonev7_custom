<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Automations", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n \n\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_automations")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["AddTask"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used as this is a demo site.") . "\")){window.history.back().location.reload();}</script>");
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
    if($type == "change_warehouse" && empty($_POST["other"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập mã kho hàng cần chuyển đến\")){window.history.back().location.reload();}</script>");
    }
    $isInsert = $CMSNT->insert("automations", ["name" => !empty($_POST["name"]) ? check_string($_POST["name"]) : NULL, "type" => $type, "product_id" => $product_id, "schedule" => $schedule, "other" => !empty($_POST["other"]) ? check_string($_POST["other"]) : NULL, "create_gettime" => gettime(), "update_gettime" => gettime()]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add Task Automation"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Add Task Automation", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công!\")){location.href = \"" . base_url_admin("automations") . "\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại!\")){window.history.back().location.reload();}</script>");
}
if(isset($_GET["limit"])) {
    $limit = (int) check_string($_GET["limit"]);
} else {
    $limit = 10;
}
if(isset($_GET["page"])) {
    $page = check_string((int) $_GET["page"]);
} else {
    $page = 1;
}
$from = ($page - 1) * $limit;
$where = " `id` > 0 ";
$create_gettime = "";
$shortByDate = "";
if(!empty($_GET["create_gettime"])) {
    $create_gettime = check_string($_GET["create_gettime"]);
    $createdate = $create_gettime;
    $create_gettime_1 = str_replace("-", "/", $create_gettime);
    $create_gettime_1 = explode(" to ", $create_gettime_1);
    if($create_gettime_1[0] != $create_gettime_1[1]) {
        $create_gettime_1 = [$create_gettime_1[0] . " 00:00:00", $create_gettime_1[1] . " 23:59:59"];
        $where .= " AND `create_gettime` >= '" . $create_gettime_1[0] . "' AND `create_gettime` <= '" . $create_gettime_1[1] . "' ";
    }
}
if(isset($_GET["shortByDate"])) {
    $shortByDate = check_string($_GET["shortByDate"]);
    $yesterday = date("Y-m-d", strtotime("-1 day"));
    $currentWeek = date("W");
    $currentMonth = date("m");
    $currentYear = date("Y");
    $currentDate = date("Y-m-d");
    if($shortByDate == 1) {
        $where .= " AND `create_gettime` LIKE '%" . $currentDate . "%' ";
    }
    if($shortByDate == 2) {
        $where .= " AND YEAR(create_gettime) = " . $currentYear . " AND WEEK(create_gettime, 1) = " . $currentWeek . " ";
    }
    if($shortByDate == 3) {
        $where .= " AND MONTH(create_gettime) = '" . $currentMonth . "' AND YEAR(create_gettime) = '" . $currentYear . "' ";
    }
}
$listDatatable = $CMSNT->get_list(" SELECT * FROM `automations` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `automations` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("automations&limit=" . $limit . "&shortByDate=" . $shortByDate . "&create_gettime=" . $create_gettime . "&"), $from, $totalDatatable, $limit);
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"bx bxs-calendar\"></i> Automations</h1>\n        </div>\n        ";
if(300 <= time() - $CMSNT->site("check_time_cron_task")) {
    echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\" href=\"";
    echo base_url("cron/task.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/task.php");
    echo "</a> 1 - 5 phút 1 lần để sử dụng được chức năng này.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH CÔNG VIỆC TỰ ĐỘNG\n                        </div>\n                        <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                            class=\"btn btn-sm btn-primary shadow-primary\"><i\n                                class=\"ri-add-line fw-semibold align-middle\"></i> THÊM TASK</button>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"automations\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
echo $create_gettime;
echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\"\n                                        href=\"";
echo base_url_admin("automations");
echo "\"><i class=\"fa fa-trash\"></i>\n                                        ";
echo __("Clear filter");
echo "                                    </a>\n                                </div>\n                            </div>\n                            <div class=\"top-filter\">\n                                <div class=\"filter-show\">\n                                    <label class=\"filter-label\">Show :</label>\n                                    <select name=\"limit\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option ";
echo $limit == 5 ? "selected" : "";
echo " value=\"5\">5</option>\n                                        <option ";
echo $limit == 10 ? "selected" : "";
echo " value=\"10\">10</option>\n                                        <option ";
echo $limit == 20 ? "selected" : "";
echo " value=\"20\">20</option>\n                                        <option ";
echo $limit == 50 ? "selected" : "";
echo " value=\"50\">50</option>\n                                        <option ";
echo $limit == 100 ? "selected" : "";
echo " value=\"100\">100</option>\n                                        <option ";
echo $limit == 500 ? "selected" : "";
echo " value=\"500\">500</option>\n                                        <option ";
echo $limit == 1000 ? "selected" : "";
echo " value=\"1000\">1000</option>\n                                    </select>\n                                </div>\n                                <div class=\"filter-short\">\n                                    <label class=\"filter-label\">";
echo __("Short by Date:");
echo "</label>\n                                    <select name=\"shortByDate\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option value=\"\">";
echo __("Tất cả");
echo "</option>\n                                        <option ";
echo $shortByDate == 1 ? "selected" : "";
echo " value=\"1\">";
echo __("Hôm nay");
echo "                                        </option>\n                                        <option ";
echo $shortByDate == 2 ? "selected" : "";
echo " value=\"2\">";
echo __("Tuần này");
echo "                                        </option>\n                                        <option ";
echo $shortByDate == 3 ? "selected" : "";
echo " value=\"3\">\n                                            ";
echo __("Tháng này");
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive table-wrapper mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                                    id=\"check_all_checkbox\" value=\"option1\">\n                                            </div>\n                                        </th>\n                                        <th class=\"text-center\">Tên công việc</th>\n                                        <th class=\"text-center\">Loại công việc</th>\n                                        <th class=\"text-center\">Chi tiết công việc</th>\n                                        <th class=\"text-center\">Thao tác</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input checkbox\"\n                                                    data-id=\"";
    echo $row["id"];
    echo "\" name=\"checkbox\"\n                                                    value=\"";
    echo $row["id"];
    echo "\" />\n                                            </div>\n                                        </td>\n                                        <td class=\"text-center\">";
    echo $row["name"];
    echo "</td>\n                                        <td class=\"text-center\">\n                                            ";
    if($row["type"] == "delete_order") {
        echo "<span style=\"font-size: 13px;\" class=\"badge bg-danger\">Xóa tài khoản đã bán</span>";
    } elseif($row["type"] == "delete_order_revenue") {
        echo "<span style=\"font-size: 13px;\" class=\"badge bg-danger\">Xóa đơn hàng & tài khoản đã bán</span>";
    } elseif($row["type"] == "change_warehouse") {
        echo "<span style=\"font-size: 13px;\" class=\"badge bg-primary\">Thay đổi kho hàng</span>";
    } elseif($row["type"] == "delete_order_not_uid") {
        echo "<span style=\"font-size: 13px;\" class=\"badge bg-primary\">Xóa tài khoản đã bán, không xóa UID</span>";
    }
    echo "                                        </td>\n                                        <td>\n                                            ";
    if($row["type"] == "delete_order") {
        echo "                                            Hệ thống sẽ thực hiện xóa tài khoản đã bán sau <b\n                                                style=\"color:red;\">";
        echo timeAgo2($row["schedule"]);
        echo "</b>, chỉ áp dụng các\n                                            sản phẩm bạn chọn.\n                                            ";
    } elseif($row["type"] == "delete_order_not_uid") {
        echo "                                            Hệ thống sẽ thực hiện xóa tài khoản đã bán, không xóa UID sau <b\n                                                style=\"color:red;\">";
        echo timeAgo2($row["schedule"]);
        echo "</b>, chỉ áp dụng các\n                                            sản phẩm bạn chọn.\n                                            ";
    } elseif($row["type"] == "change_warehouse") {
        echo "                                            Hệ thống sẽ thực hiện chuyển những tài khoản trong sản phẩm bạn chọn vào kho\n                                            hàng <b style=\"color:blue;\">";
        echo $row["other"];
        echo "</b> nếu quá <b\n                                                style=\"color:red;\">";
        echo timeAgo2($row["schedule"]);
        echo "</b> chưa được bán.\n                                            ";
    } elseif($row["type"] == "delete_order_revenue") {
        echo "                                            Hệ thống sẽ thực hiện xóa đơn hàng & tài khoản đã bán sau <b\n                                                style=\"color:red;\">";
        echo timeAgo2($row["schedule"]);
        echo "</b>, chỉ áp dụng các\n                                            sản phẩm bạn chọn.\n                                            ";
    }
    echo "\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <a type=\"button\"\n                                                href=\"";
    echo base_url_admin("automation-edit&id=" . $row["id"]);
    echo "\"\n                                                class=\"btn btn-sm btn-primary\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Edit");
    echo "\">\n                                                <i class=\"fa-solid fa-pen-to-square\"></i>\n                                            </a>\n                                            <a type=\"button\" onclick=\"remove('";
    echo $row["id"];
    echo "')\"\n                                                class=\"btn btn-sm btn-danger\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Delete");
    echo "\">\n                                                <i class=\"fas fa-trash\"></i>\n                                            </a>\n                                        </td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                                <tfoot>\n                                    <td colspan=\"8\">\n                                        <div class=\"btn-list\">\n                                            <button type=\"button\" id=\"btn_delete_row\"\n                                                class=\"btn btn-outline-danger shadow-danger btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-trash\"></i> XÓA TASK ĐÃ CHỌN</button>\n                                        </div>\n                                    </td>\n                                </tfoot>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                        <p>Hướng dẫn sử dụng chức năng xóa đơn hàng đã bán: <a class=\"text-primary\" target=\"_blank\"\n                                href=\"https://help.cmsnt.co/huong-dan/cau-hinh-tu-dong-xoa-don-hang-da-ban-trong-shopclone7/\">https://help.cmsnt.co/huong-dan/cau-hinh-tu-dong-xoa-don-hang-da-ban-trong-shopclone7/</a>\n                        </p>\n                        <p>Hướng dẫn sử dụng chức đổi kho hàng: <a class=\"text-primary\" target=\"_blank\"\n                                href=\"https://help.cmsnt.co/huong-dan/cau-hinh-chuc-nang-tu-doi-kho-hang-tai-khoan-dang-ban/\">https://help.cmsnt.co/huong-dan/cau-hinh-chuc-nang-tu-doi-kho-hang-tai-khoan-dang-ban/</a>\n                        </p>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered modal-lg dialog-scrollable\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\"><i class=\"fa-solid fa-plus\"></i> THÊM CÔNG VIỆC CẦN TỰ\n                    ĐỘNG\n                </h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\" onchange=\"loadform()\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Tên công việc</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group mb-3\">\n                                <textarea class=\"form-control\" name=\"name\"\n                                    placeholder=\"Nhập tên mô tả task nếu có\"></textarea>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Loại công việc (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group mb-3\">\n                                <select class=\"form-control\" name=\"type\" id=\"type\" required>\n                                    <option value=\"\"> -- Chọn loại công việc --</option>\n                                    <option value=\"delete_order\">Xóa tài khoản đã bán</option>\n                                    <option value=\"delete_order_not_uid\">Xóa tài khoản đã bán, không xóa UID</option>\n                                    <option value=\"delete_order_revenue\">Xóa đơn hàng & tài khoản đã bán</option>\n                                    <option value=\"change_warehouse\">Thay đổi kho hàng</option>\n                                </select>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\" id=\"product_id_input\" style=\"display: none;\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Sản phẩm áp dụng (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <select class=\"form-control\" name=\"product_id[]\" id=\"listProduct\" multiple>\n                                <option value=\"\">Mặc định sẽ áp dụng cho toàn bộ sản phẩm nếu không chọn</option>\n                                ";
foreach ($CMSNT->get_list(" SELECT * FROM `categories` ") as $category) {
    echo "                                <optgroup label=\"__";
    echo $category["name"];
    echo "__\">\n                                    ";
    foreach ($CMSNT->get_list(" SELECT * FROM `products` WHERE `category_id` = '" . $category["id"] . "' ") as $product) {
        echo "                                    <option value=\"";
        echo $product["id"];
        echo "\">";
        echo $product["name"];
        echo "</option>\n                                    ";
    }
    echo "                                </optgroup>\n                                ";
}
echo "                            </select>\n                        </div>\n                        <script>\n                        const multipleCancelButton = new Choices(\n                            '#listProduct', {\n                                allowHTML: true,\n                                removeItemButton: true,\n                            }\n                        );\n                        </script>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Thời gian (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group mb-3\">\n                                <input class=\"form-control\" name=\"schedule\" id=\"schedule\" onkeyup=\"loadform()\"\n                                    value=\"604800\" placeholder=\"Nhập giây, ví dụ 1 ngày = 86400\" required>\n                                <span class=\"input-group-text\">\n                                    Giây\n                                </span>\n                            </div>\n                            <div class=\"btn-group\" role=\"group\" aria-label=\"Time buttons\">\n                                <button type=\"button\" class=\"btn btn-outline-primary btn-wave btn-sm\" onclick=\"setTime(1)\">1 ngày</button>\n                                <button type=\"button\" class=\"btn btn-outline-primary btn-wave btn-sm\" onclick=\"setTime(3)\">3 ngày</button>\n                                <button type=\"button\" class=\"btn btn-outline-primary btn-wave btn-sm\" onclick=\"setTime(7)\" active>7 ngày</button>\n                                <button type=\"button\" class=\"btn btn-outline-primary btn-wave btn-sm\" onclick=\"setTime(30)\">30 ngày</button>\n                            </div>\n                        </div>\n                    </div>\n                    <script>\n                    function setTime(days) {\n                        const seconds = days * 86400; // 1 ngày = 86400 giây\n                        document.getElementById('schedule').value = seconds;\n                        loadform(); // Gọi hàm loadform nếu cần cập nhật gì thêm\n                    }\n                    </script>\n                    <div class=\"row mb-4\" id=\"warehouse_input\" style=\"display: none;\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Kho hàng nhận (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group mb-3\">\n                                <input class=\"form-control\" name=\"other\" id=\"other\" onkeyup=\"loadform()\"\n                                    placeholder=\"Mã kho hàng\">\n                            </div>\n                        </div>\n                    </div>\n\n                    <p id=\"mota\">Vui lòng chọn loại công việc</p>\n\n                    <script>\n                    function formatTime(seconds) {\n                        var days = Math.floor(seconds / (60 * 60 * 24));\n                        var hours = Math.floor((seconds % (60 * 60 * 24)) / (60 * 60));\n                        var minutes = Math.floor((seconds % (60 * 60)) / 60);\n                        var remainingSeconds = seconds % 60;\n\n                        var result = '';\n                        if (days > 0) {\n                            result += days + ' ngày ';\n                        }\n                        if (hours > 0) {\n                            result += hours + ' giờ ';\n                        }\n                        if (minutes > 0) {\n                            result += minutes + ' phút ';\n                        }\n                        if (remainingSeconds > 0) {\n                            result += remainingSeconds + ' giây';\n                        }\n\n                        return result.trim();\n                    }\n\n                    function loadform() {\n                        var type = \$('#type').val();\n                        var schedule = \$('#schedule').val();\n                        var formattedTime = formatTime(schedule);\n\n                        \$('#warehouse_input').hide();\n                        \$('#product_id_input').hide();\n\n                        if (type == 'change_warehouse') {\n                            \$('#warehouse_input').show();\n                            \$('#product_id_input').show();\n                            \$('#mota').html(\n                                'Nếu bạn tạo Task này => Hệ thống sẽ thực hiện chuyển những tài khoản trong sản phẩm bạn chọn vào kho hàng <b style=\"color:blue;\">' +\n                                \$('#other').val() + '</b> nếu quá <b style=\"color:red;\">' + formattedTime +\n                                '</b> chưa được bán.');\n                        } else if (type == 'delete_order') {\n                            \$('#product_id_input').show();\n                            \$('#mota').html(\n                                'Nếu bạn tạo Task này => Hệ thống sẽ thực hiện xóa tài khoản đã bán sau <b style=\"color:red;\">' +\n                                formattedTime + '</b>, chỉ áp dụng các sản phẩm bạn chọn ở trên.');\n                        } else if (type == 'delete_order_not_uid') {\n                            \$('#product_id_input').show();\n                            \$('#mota').html(\n                                'Nếu bạn tạo Task này => Hệ thống sẽ thực hiện xóa tài khoản đã bán, không xóa UID sau <b style=\"color:red;\">' +\n                                formattedTime + '</b>, chỉ áp dụng các sản phẩm bạn chọn ở trên.');\n                        } else if (type == 'delete_order_revenue') {\n                            \$('#product_id_input').show();\n                            \$('#mota').html(\n                                'Nếu bạn tạo Task này => Hệ thống sẽ thực hiện xóa đơn hàng và tài khoản đã bán sau <b style=\"color:red;\">' +\n                                formattedTime + '</b>, chỉ áp dụng các sản phẩm bạn chọn ở trên.');\n                        } else {\n                            \$('#mota').html('Vui lòng chọn loại công việc');\n                        }\n                    }\n                    </script>\n\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light \" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddTask\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>\n                        ";
echo __("Submit");
echo "</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n\n<script>\n\$(function() {\n    \$('#check_all_checkbox').on('click', function() {\n        \$('.checkbox').prop('checked', this.checked);\n    });\n    \$('.checkbox').on('click', function() {\n        \$('#check_all_checkbox').prop('checked', \$('.checkbox:checked')\n            .length === \$('.checkbox').length);\n    });\n});\n</script>\n\n\n<script>\n\$(\"#btn_delete_row\").click(function() {\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox\"]:checked');\n    if (checkboxes.length === 0) {\n        return showMessage('Lỗi: Vui lòng chọn ít nhất một dữ liệu.', 'error');\n    }\n    Swal.fire({\n        title: \"Bạn có chắc không?\",\n        text: \"Hệ thống sẽ xóa \" + checkboxes.length +\n            \" Task bạn đã chọn khi nhấn Đồng Ý\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"Đồng ý\",\n        cancelButtonText: \"Đóng\"\n    }).then((result) => {\n        if (result.isConfirmed) {\n            delete_records();\n        }\n    });\n});\n\nfunction delete_records() {\n    var checkbox = document.getElementsByName('checkbox');\n\n    function postUpdatesSequentially(index) {\n        if (index < checkbox.length) {\n            if (checkbox[index].checked === true) {\n                postRemove(checkbox[index].value);\n            }\n            setTimeout(function() {\n                postUpdatesSequentially(index + 1);\n            }, 100);\n        } else {\n            Swal.fire({\n                title: \"Thành công!\",\n                text: \"Xóa dữ liệu thành công\",\n                icon: \"success\"\n            });\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    }\n    postUpdatesSequentially(0);\n}\n</script>\n\n<script>\nfunction postRemove(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeTaskAutomation',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        }\n    });\n}\n\nfunction remove(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa Task\",\n        message: \"Bạn có chắc chắn muốn xóa Task này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemove(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n</script>";

?>