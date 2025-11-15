<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Khuyến mãi Nạp Tiền", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n \n\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "view_promotion")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["AddPromotion"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used as this is a demo site.") . "\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_promotion")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    if(empty($_POST["min"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập số tiền tối thiểu\")){window.history.back().location.reload();}</script>");
    }
    $min = check_string($_POST["min"]);
    if($min <= 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Số tiền nạp tối thiểu không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["discount"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập phần trăm khuyến mãi\")){window.history.back().location.reload();}</script>");
    }
    $discount = check_string($_POST["discount"]);
    $isInsert = $CMSNT->insert("promotions", ["discount" => $discount, "create_gettime" => gettime(), "min" => $min]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add Promotion (" . format_currency($min) . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Add Promotion (" . format_currency($min) . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công!\")){location.href = \"" . base_url_admin("promotions") . "\";}</script>");
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
$shortByDate = "";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `promotions` WHERE " . $where . " ORDER BY `min` ASC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `promotions` WHERE " . $where . " ORDER BY `min` ASC ");
$urlDatatable = pagination(base_url_admin("promotions&limit=" . $limit . "&shortByDate=" . $shortByDate . "&"), $from, $totalDatatable, $limit);
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-tags\"></i> Khuyến mãi Nạp Tiền</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH MỐC NẠP TIỀN\n                        </div>\n                        <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                            class=\"btn btn-sm btn-primary shadow-primary\"><i\n                                class=\"ri-add-line fw-semibold align-middle\"></i> Tạo mốc nạp mới</button>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"promotions\">\n\n                            </div>\n                            <div class=\"top-filter\">\n                                <div class=\"filter-show\">\n                                    <label class=\"filter-label\">Show :</label>\n                                    <select name=\"limit\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option ";
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                                    id=\"check_all_checkbox\" value=\"option1\">\n                                            </div>\n                                        </th>\n                                        <th class=\"text-center\">Số tiền nạp tổi thiểu</th>\n                                        <th class=\"text-center\">Khuyến mãi thêm</th>\n                                        <th class=\"text-center\">Thời gian thêm</th>\n                                        <th class=\"text-center\">Thao tác</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
foreach ($listDatatable as $promotion) {
    echo "                                    <tr>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input checkbox\"\n                                                    data-id=\"";
    echo $promotion["id"];
    echo "\" name=\"checkbox\"\n                                                    value=\"";
    echo $promotion["id"];
    echo "\" />\n                                            </div>\n                                        </td>\n                                        <td class=\"text-center\"><b style=\"font-size:15px;\">>=\n                                                ";
    echo format_currency($promotion["min"]);
    echo "</b></td>\n                                        <td class=\"text-center\"><span style=\"font-size: 15px;\"\n                                                class=\"badge bg-primary\">";
    echo $promotion["discount"];
    echo "%</span></td>\n                                        <td class=\"text-center\">";
    echo $promotion["create_gettime"];
    echo "</td>\n                                        <td class=\"text-center\">\n                                            <a type=\"button\" onclick=\"remove('";
    echo $promotion["id"];
    echo "')\"\n                                                class=\"btn btn-sm btn-danger\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Delete");
    echo "\">\n                                                <i class=\"fas fa-trash\"></i> Delete\n                                            </a>\n                                        </td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                                <tfoot>\n                                    <td colspan=\"6\">\n                                        <div class=\"btn-list\">\n                                            <button type=\"button\" id=\"btn_delete_product\"\n                                                class=\"btn btn-outline-danger shadow-danger btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-trash\"></i> XÓA DỮ LIỆU ĐÃ CHỌN</button>\n                                        </div>\n                                    </td>\n                                </tfoot>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered modal-lg dialog-scrollable\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\"><i class=\"fa-solid fa-plus\"></i> Tạo mốc nạp mới\n                </h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Nạp tối thiểu (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group\">\n                                <input type=\"text\" class=\"form-control\" name=\"min\" required>\n                                <span class=\"input-group-text\">\n                                    ";
echo currencyDefault();
echo "                                </span>\n                            </div>\n                            <small>Số tiền nạp tối thiểu để được nhận khuyến mãi</small>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Khuyến mãi (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group\">\n                                <input type=\"text\" class=\"form-control\" name=\"discount\" required>\n                                <span class=\"input-group-text\">\n                                    <i class=\"fa-solid fa-percent\"></i>\n                                </span>\n                            </div>\n                            <small>Nhập chiết khấu khuyến mãi VD: 10 (tức khuyến mãi 10% khi nhập nạp tiền đủ\n                                mốc)</small>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light \" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddPromotion\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>\n                        ";
echo __("Submit");
echo "</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\n\$(function() {\n    \$('#check_all_checkbox').on('click', function() {\n        \$('.checkbox').prop('checked', this.checked);\n    });\n    \$('.checkbox').on('click', function() {\n        \$('#check_all_checkbox').prop('checked', \$('.checkbox:checked')\n            .length === \$('.checkbox').length);\n    });\n});\n</script>\n\n<script>\n\$(\"#btn_delete_product\").click(function() {\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox\"]:checked');\n    if (checkboxes.length === 0) {\n        showMessage('Vui lòng chọn ít nhất một bản ghi', 'error');\n        return;\n    }\n    Swal.fire({\n        title: \"Bạn có chắc không?\",\n        text: \"Hệ thống sẽ xóa \" + checkboxes.length +\n            \" dữ liệu bạn đã chọn khi nhấn Đồng Ý\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"Đồng ý\",\n        cancelButtonText: \"Đóng\"\n    }).then((result) => {\n        if (result.isConfirmed) {\n            delete_records();\n        }\n    });\n});\n\nfunction delete_records() {\n    var checkbox = document.getElementsByName('checkbox');\n\n    function postUpdatesSequentially(index) {\n        if (index < checkbox.length) {\n            if (checkbox[index].checked === true) {\n                postRemove(checkbox[index].value);\n            }\n            setTimeout(function() {\n                postUpdatesSequentially(index + 1);\n            }, 100);\n        } else {\n            Swal.fire({\n                title: \"Thành công!\",\n                text: \"Xóa dữ liệu thành công\",\n                icon: \"success\"\n            });\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    }\n    postUpdatesSequentially(0);\n}\n</script>\n\n<script>\nfunction postRemove(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removePromotion',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        }\n    });\n}\n\nfunction remove(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa Promotion\",\n        message: \"Bạn có chắc chắn muốn xóa Promotion này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemove(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n</script>";

?>