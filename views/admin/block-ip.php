<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Block IP", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n \n\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_block_ip")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["SaveSettings"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_block_ip")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng edit_block_ip\")){window.history.back();}</script>");
    }
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Cấu hình block ip")]);
    foreach ($_POST as $key => $value) {
        $CMSNT->update("settings", ["value" => $value], " `name` = '" . $key . "' ");
    }
    $my_text = $CMSNT->site("noti_action");
    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
    $my_text = str_replace("{username}", $getUser["username"], $my_text);
    $my_text = str_replace("{action}", __("Cấu hình block ip"), $my_text);
    $my_text = str_replace("{ip}", myip(), $my_text);
    $my_text = str_replace("{time}", gettime(), $my_text);
    sendMessAdmin($my_text);
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Save successfully!") . "\")){window.history.back().location.reload();}</script>");
} else {
    if(isset($_POST["AddIPBlock"])) {
        if($CMSNT->site("status_demo") != 0) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used as this is a demo site.") . "\")){window.history.back().location.reload();}</script>");
        }
        if(!checkPermission($getUser["admin"], "edit_block_ip")) {
            exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
        }
        if(empty($_POST["ip"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập địa chỉ IP cần chặn\")){window.history.back().location.reload();}</script>");
        }
        $ip = check_string($_POST["ip"]);
        $isInsert = $CMSNT->insert("block_ip", ["ip" => $ip, "attempts" => 0, "banned" => 1, "reason" => !empty($_POST["reason"]) ? check_string($_POST["reason"]) : NULL, "create_gettime" => gettime()]);
        if($isInsert) {
            $Mobile_Detect = new Mobile_Detect();
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add Block IP (" . $ip . ")"]);
            $my_text = $CMSNT->site("noti_action");
            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
            $my_text = str_replace("{username}", $getUser["username"], $my_text);
            $my_text = str_replace("{action}", "Add Block IP (" . $ip . ")", $my_text);
            $my_text = str_replace("{ip}", myip(), $my_text);
            $my_text = str_replace("{time}", gettime(), $my_text);
            sendMessAdmin($my_text);
            exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công!\")){location.href = \"" . base_url_admin("block-ip") . "\";}</script>");
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
    $ip = "";
    $shortByDate = "";
    if(!empty($_GET["ip"])) {
        $ip = check_string($_GET["ip"]);
        $where .= " AND `ip` LIKE \"%" . $ip . "%\" ";
    }
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
    $listDatatable = $CMSNT->get_list(" SELECT * FROM `block_ip` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
    $totalDatatable = $CMSNT->num_rows(" SELECT * FROM `block_ip` WHERE " . $where . " ORDER BY id DESC ");
    $urlDatatable = pagination(base_url_admin("block-ip&limit=" . $limit . "&shortByDate=" . $shortByDate . "&ip=" . $ip . "&create_gettime=" . $create_gettime . "&"), $from, $totalDatatable, $limit);
    echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-ban\"></i> Block IP</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"text-right\">\n                    <button type=\"button\" id=\"open-card-config\" class=\"btn btn-primary label-btn mb-3\">\n                        <i class=\"ri-settings-4-line label-btn-icon me-2\"></i> CẤU HÌNH\n                    </button>\n                </div>\n            </div>\n            <div class=\"col-xl-12\" id=\"card-config\" style=\"display: none;\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n\n                            <div class=\"row\">\n                                <div class=\"col-lg-12 col-xl-6\">\n\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-5 col-form-label\">Số lần đăng nhập thất bại tối đa để block\n                                            IP</label>\n                                        <div class=\"col-sm-7\">\n                                            <input type=\"number\" class=\"form-control\"\n                                                value=\"";
    echo $CMSNT->site("limit_block_ip_login");
    echo "\"\n                                                name=\"limit_block_ip_login\">\n                                            <small>Nhầm ngăn chặn tấn công scan tài khoản (brute-force), CMSNT khuyên\n                                                quý khách nên để thấp hơn hoặc bằng 5</small>\n                                        </div>\n                                    </div>\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-5 col-form-label\">Chỉ có IP mua hàng mới có\n                                            thể xem được đơn hàng</label>\n                                        <div class=\"col-sm-7\">\n                                            <select class=\"form-control\" name=\"isPurchaseIpVerified\" required>\n                                                <option value=\"0\" ";
    echo $CMSNT->site("isPurchaseIpVerified") == 0 ? "selected" : "";
    echo ">";
    echo __("Thành viên tự ON/OFF theo nhu cầu");
    echo "</option>\n                                                <option value=\"1\" ";
    echo $CMSNT->site("isPurchaseIpVerified") == 1 ? "selected" : "";
    echo ">";
    echo __("Áp dụng cho toàn bộ thành viên");
    echo "</option>\n                                            </select>\n                                        </div>\n                                    </div>\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-5 col-form-label\">Chỉ có Trình Duyệt mua hàng mới có\n                                            thể xem được đơn hàng</label>\n                                        <div class=\"col-sm-7\">\n                                            <select class=\"form-control\" name=\"isPurchaseDeviceVerified\" required>\n                                                <option value=\"0\" ";
    echo $CMSNT->site("isPurchaseDeviceVerified") == 0 ? "selected" : "";
    echo ">";
    echo __("Thành viên tự ON/OFF theo nhu cầu");
    echo "</option>\n                                                <option value=\"1\" ";
    echo $CMSNT->site("isPurchaseDeviceVerified") == 1 ? "selected" : "";
    echo ">";
    echo __("Áp dụng cho toàn bộ thành viên");
    echo "</option>\n                                            </select>\n                                        </div>\n                                    </div>\n\n                                </div>\n                                <div class=\"col-lg-12 col-xl-6\">\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-5 col-form-label\">Số lần đăng nhập thất bại tối đa để\n                                            khóa tài khoản</label>\n                                        <div class=\"col-sm-7\">\n                                            <input type=\"number\" class=\"form-control\"\n                                                value=\"";
    echo $CMSNT->site("limit_block_client_login");
    echo "\"\n                                                name=\"limit_block_client_login\">\n                                            <small>Nhầm ngăn chặn tấn công scan tài khoản (brute-force), CMSNT khuyên\n                                                quý khách nên để thấp hơn hoặc bằng 10</small>\n                                        </div>\n                                    </div>\n                                    <!-- <div class=\"row mb-4\">\n                                        <label class=\"col-sm-5 col-form-label\">Số lần sai API KEY tối đa để block\n                                            IP</label>\n                                        <div class=\"col-sm-7\">\n                                            <input type=\"number\" class=\"form-control\"\n                                                value=\"";
    echo $CMSNT->site("limit_block_ip_api");
    echo "\"\n                                                name=\"limit_block_ip_api\">\n                                            <small>Nhầm ngăn chặn tấn công scan tài khoản (brute-force), CMSNT khuyên\n                                                quý khách nên để thấp hơn hoặc bằng 20</small>\n                                        </div>\n                                    </div> -->\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-5 col-form-label\">Số lần truy cập trái phép Admin\n                                            Panel</label>\n                                        <div class=\"col-sm-7\">\n                                            <input type=\"number\" class=\"form-control\"\n                                                value=\"";
    echo $CMSNT->site("limit_block_ip_admin_access");
    echo "\"\n                                                name=\"limit_block_ip_admin_access\">\n                                        </div>\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"d-grid gap-2 mb-4\">\n                                <button type=\"submit\" name=\"SaveSettings\" class=\"btn btn-primary btn-block\"><i\n                                        class=\"fa fa-fw fa-save me-1\"></i>\n                                    ";
    echo __("Save");
    echo "</button>\n                            </div>\n                        </form>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH IP BỊ CHẶN\n                        </div>\n                        <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                            class=\"btn btn-sm btn-primary shadow-primary\"><i\n                                class=\"ri-add-line fw-semibold align-middle\"></i> Thêm IP cần Block</button>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"block-ip\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
    echo $ip;
    echo "\" name=\"ip\"\n                                        placeholder=\"Tìm IP\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
    echo $create_gettime;
    echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
    echo __("Search");
    echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\" href=\"";
    echo base_url_admin("block-ip");
    echo "\"><i\n                                            class=\"fa fa-trash\"></i>\n                                        ";
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
    echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive table-wrapper mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                                    id=\"check_all_checkbox\" value=\"option1\">\n                                            </div>\n                                        </th>\n                                        <th class=\"text-center\">Địa chỉ IP</th>\n                                        <th class=\"text-center\">Attempts</th>\n                                        <th class=\"text-center\">Banned</th>\n                                        <th class=\"text-center\">Lý do</th>\n                                        <th class=\"text-center\">Thời gian</th>\n                                        <th class=\"text-center\">Thao tác</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
    foreach ($listDatatable as $row) {
        echo "                                    <tr>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input checkbox\"\n                                                    data-id=\"";
        echo $row["id"];
        echo "\" name=\"checkbox\"\n                                                    value=\"";
        echo $row["id"];
        echo "\" />\n                                            </div>\n                                        </td>\n                                        <td class=\"text-center\">";
        echo $row["ip"];
        echo "</td>\n                                        <td class=\"text-center\"><span style=\"font-size: 15px;\"\n                                                class=\"badge bg-info\">";
        echo format_cash($row["attempts"]);
        echo "</span>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            ";
        echo $row["banned"] == 1 ? "<span class=\"badge bg-danger\">Banned</span>" : "<span class=\"badge bg-success\">Live</span>";
        echo "                                        </td>\n                                        <td>";
        echo $row["reason"];
        echo "</td>\n                                        <td class=\"text-center\">";
        echo $row["create_gettime"];
        echo "</td>\n                                        <td class=\"text-center\">\n                                            <a type=\"button\" onclick=\"remove('";
        echo $row["id"];
        echo "')\"\n                                                class=\"btn btn-sm btn-danger\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
        echo __("Delete");
        echo "\">\n                                                <i class=\"fas fa-trash\"></i>\n                                            </a>\n                                        </td>\n                                    </tr>\n                                    ";
    }
    echo "                                </tbody>\n                                <tfoot>\n                                    <td colspan=\"8\">\n                                        <div class=\"btn-list\">\n                                            <button type=\"button\" id=\"btn_delete_row\"\n                                                class=\"btn btn-outline-danger shadow-danger btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-trash\"></i> XÓA IP ĐÃ CHỌN</button>\n                                        </div>\n                                    </td>\n                                </tfoot>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
    echo $limit;
    echo " of ";
    echo format_cash($totalDatatable);
    echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
    echo $limit < $totalDatatable ? $urlDatatable : "";
    echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered modal-lg dialog-scrollable\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\"><i class=\"fa-solid fa-plus\"></i> Thêm IP cần Block\n                </h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Địa chỉ IP cần Block (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group mb-3\">\n                                <input type=\"text\" class=\"form-control\" id=\"ip\" name=\"ip\"\n                                    placeholder=\"Nhập địa chỉ IP cần Block\" required>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Lý do chặn (nếu có)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group mb-3\">\n                                <textarea class=\"form-control\" name=\"reason\"\n                                    placeholder=\"Nhập lý do block ip nếu có\"></textarea>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light \" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddIPBlock\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>\n                        ";
    echo __("Submit");
    echo "</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n\n\n";
    require_once __DIR__ . "/footer.php";
    echo "\n\n<script>\n\$(function() {\n    \$('#check_all_checkbox').on('click', function() {\n        \$('.checkbox').prop('checked', this.checked);\n    });\n    \$('.checkbox').on('click', function() {\n        \$('#check_all_checkbox').prop('checked', \$('.checkbox:checked')\n            .length === \$('.checkbox').length);\n    });\n});\n</script>\n\n\n<script>\n\$(\"#btn_delete_row\").click(function() {\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox\"]:checked');\n    if (checkboxes.length === 0) {\n        return showMessage('Vui lòng chọn ít nhất một IP.', 'error');\n    }\n    Swal.fire({\n        title: \"Bạn có chắc không?\",\n        text: \"Hệ thống sẽ xóa \" + checkboxes.length +\n            \" IP bạn đã chọn khi nhấn Đồng Ý\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"Đồng ý\",\n        cancelButtonText: \"Đóng\"\n    }).then((result) => {\n        if (result.isConfirmed) {\n            delete_records();\n        }\n    });\n});\n\nfunction delete_records() {\n    var checkbox = document.getElementsByName('checkbox');\n\n    function postUpdatesSequentially(index) {\n        if (index < checkbox.length) {\n            if (checkbox[index].checked === true) {\n                postRemove(checkbox[index].value);\n            }\n            setTimeout(function() {\n                postUpdatesSequentially(index + 1);\n            }, 100);\n        } else {\n            Swal.fire({\n                title: \"Thành công!\",\n                text: \"Xóa IP thành công\",\n                icon: \"success\"\n            });\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    }\n    postUpdatesSequentially(0);\n}\n</script>\n\n<script>\nfunction postRemove(id) {\n    \$.ajax({\n        url: \"";
    echo BASE_URL("ajaxs/admin/remove.php");
    echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeBlockIP',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        }\n    });\n}\n\nfunction remove(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa địa chỉ IP\",\n        message: \"Bạn có chắc chắn muốn xóa địa chỉ IP này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemove(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n</script>\n\n<script>\ndocument.addEventListener('DOMContentLoaded', function() {\n    var button = document.getElementById('open-card-config');\n    var card = document.getElementById('card-config');\n\n    // Thêm sự kiện click cho nút button\n    button.addEventListener('click', function() {\n        // Kiểm tra nếu card đang hiển thị thì ẩn đi, ngược lại hiển thị\n        if (card.style.display === 'none' || card.style.display === '') {\n            card.style.display = 'block';\n        } else {\n            card.style.display = 'none';\n        }\n    });\n});\n</script>";
}

?>