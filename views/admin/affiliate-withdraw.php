<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Affiliate Withdraw", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_withdraw_affiliate")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back().location.reload();}</script>");
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
$user_id = "";
$reason = "";
$create_gettime = "";
$username = "";
$status = "";
$stk = "";
$trans_id = "";
if(!empty($_GET["trans_id"])) {
    $trans_id = check_string($_GET["trans_id"]);
    $where .= " AND `trans_id` = \"" . $trans_id . "\" ";
}
if(!empty($_GET["stk"])) {
    $stk = check_string($_GET["stk"]);
    $where .= " AND `stk` = \"" . $stk . "\" ";
}
if(!empty($_GET["status"])) {
    $status = check_string($_GET["status"]);
    $where .= " AND `status` = \"" . $status . "\" ";
}
if(!empty($_GET["username"])) {
    $username = check_string($_GET["username"]);
    if($idUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `username` = '" . $username . "' ")) {
        $where .= " AND `user_id` =  \"" . $idUser["id"] . "\" ";
    } else {
        $where .= " AND `user_id` =  \"\" ";
    }
}
if(!empty($_GET["user_id"])) {
    $user_id = check_string($_GET["user_id"]);
    $where .= " AND `user_id` = \"" . $user_id . "\" ";
}
if(!empty($_GET["reason"])) {
    $reason = check_string($_GET["reason"]);
    $where .= " AND `reason` LIKE \"%" . $reason . "%\" ";
}
if(!empty($_GET["create_gettime"])) {
    $create_gettime = check_string($_GET["create_gettime"]);
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `aff_withdraw` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `aff_withdraw` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("affiliate-withdraw&limit=" . $limit . "&shortByDate=" . $shortByDate . "&user_id=" . $user_id . "&reason=" . $reason . "&create_gettime=" . $create_gettime . "&username=" . $username . "&stk=" . $stk . "&status=" . $status . "&trans_id=" . $trans_id . "&"), $from, $totalDatatable, $limit);
$yesterday = date("Y-m-d", strtotime("-1 day"));
$currentWeek = date("W");
$currentMonth = date("m");
$currentYear = date("Y");
$currentDate = date("Y-m-d");
echo "<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Affiliate Withdraw</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"#\">Affiliate Program</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Affiliate Withdraw</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-3\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-center\">\n                            <div class=\"flex-fill\">\n                                <p class=\"mb-1 fs-5 fw-semibold text-default\">\n                                    ";
echo format_currency($CMSNT->get_row(" SELECT SUM(amount) FROM `aff_withdraw` WHERE `status` = 'completed' ")["SUM(amount)"]);
echo "                                </p>\n                                <p class=\"mb-0 text-muted\">Tổng số tiền đã rút</p>\n                            </div>\n                            <div class=\"ms-2\">\n                                <span class=\"avatar text-bg-danger rounded-circle fs-20\"><i\n                                        class='bx bxs-wallet-alt'></i></span>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xl-3\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-center\">\n                            <div class=\"flex-fill\">\n                                <p class=\"mb-1 fs-5 fw-semibold text-default\">\n                                    ";
echo format_currency($CMSNT->get_row("SELECT SUM(amount) FROM `aff_withdraw` WHERE `status` = 'completed' AND MONTH(create_gettime) = '" . $currentMonth . "' AND YEAR(create_gettime) = '" . $currentYear . "' ")["SUM(amount)"]);
echo "                                </p>\n                                <p class=\"mb-0 text-muted\">Tiền rút trong tháng ";
echo date("m");
echo "</p>\n                            </div>\n                            <div class=\"ms-2\">\n                                <span class=\"avatar text-bg-info rounded-circle fs-20\"><i\n                                        class='bx bxs-wallet-alt'></i></span>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xl-3\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-center\">\n                            <div class=\"flex-fill\">\n                                <p class=\"mb-1 fs-5 fw-semibold text-default\">\n                                    ";
echo format_currency($CMSNT->get_row("SELECT SUM(amount) FROM aff_withdraw WHERE  `status` = 'completed' AND YEAR(create_gettime) = " . $currentYear . " AND WEEK(create_gettime, 1) = " . $currentWeek . " ")["SUM(amount)"]);
echo "                                </p>\n                                <p class=\"mb-0 text-muted\">Tiền rút trong tuần</p>\n                            </div>\n                            <div class=\"ms-2\">\n                                <span class=\"avatar text-bg-warning rounded-circle fs-20\"><i\n                                        class='bx bxs-wallet-alt'></i></span>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xl-3\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-center\">\n                            <div class=\"flex-fill\">\n                                <p class=\"mb-1 fs-5 fw-semibold text-default\">\n                                    ";
echo format_currency($CMSNT->get_row("SELECT SUM(amount) FROM aff_withdraw WHERE  `status` = 'completed' AND `create_gettime` LIKE '%" . $currentDate . "%' ")["SUM(amount)"]);
echo "                                </p>\n                                <p class=\"mb-0 text-muted\">Tiền rút hôm nay</p>\n                            </div>\n                            <div class=\"ms-2\">\n                                <span class=\"avatar text-bg-primary rounded-circle fs-20\"><i\n                                        class='bx bxs-wallet-alt'></i></span>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            ĐƠN RÚT TIỀN HOA HỒNG\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"affiliate-withdraw\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $user_id;
echo "\" name=\"user_id\"\n                                        placeholder=\"";
echo __("ID User");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $username;
echo "\" name=\"username\"\n                                        placeholder=\"";
echo __("Username");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $trans_id;
echo "\" name=\"trans_id\"\n                                        placeholder=\"";
echo __("Mã giao dịch");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $stk;
echo "\" name=\"stk\"\n                                        placeholder=\"";
echo __("Số tài khoản");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $reason;
echo "\" name=\"reason\"\n                                        placeholder=\"";
echo __("Lý do");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <select class=\"form-control form-control-sm\" name=\"status\">\n                                        <option value=\"\">";
echo __("Trạng thái");
echo "</option>\n                                        <option ";
echo $status == "pending" ? "selected" : "";
echo " value=\"pending\">\n                                            ";
echo __("Pending");
echo "</option>\n                                        <option ";
echo $status == "cancel" ? "selected" : "";
echo " value=\"cancel\">\n                                            ";
echo __("Cancel");
echo "</option>\n                                        <option ";
echo $status == "completed" ? "selected" : "";
echo " value=\"completed\">\n                                            ";
echo __("Completed");
echo "</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
echo $create_gettime;
echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\"\n                                        href=\"";
echo base_url_admin("affiliate-withdraw");
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead class=\"table\">\n                                    <tr>\n                                        <th scope=\"col\"></th>\n                                        <th>";
echo __("Mã giao dịch");
echo "</th>\n                                        <th>";
echo __("Thành viên");
echo "</th>\n                                        <th>";
echo __("Số tiền rút");
echo "</th>\n                                        <th>";
echo __("Tài khoản nhận tiền");
echo "</th>\n                                        <th>";
echo __("Trạng thái");
echo "</th>\n                                        <th>";
echo __("Thời gian");
echo "</th>\n                                        <th>";
echo __("Lý do");
echo "</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
$i = 0;
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td>\n                                            <button type=\"button\"\n                                                onclick=\"modalEdit(`";
    echo $getUser["token"];
    echo "`, `";
    echo $row["id"];
    echo "`)\"\n                                                class=\"btn btn-icon btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Edit");
    echo "\">\n                                                <i class=\"fa fa-fw fa-edit\"></i>\n                                            </button>\n                                        </td>\n                                        <td>";
    echo $row["trans_id"];
    echo "</td>\n                                        <td><a class=\"text-primary\"\n                                                href=\"";
    echo base_url_admin("user-edit&id=" . $row["user_id"]);
    echo "\">";
    echo getRowRealtime("users", $row["user_id"], "username");
    echo "                                                [ID ";
    echo $row["user_id"];
    echo "]</a>\n                                        </td>\n                                        <td class=\"text-right\">\n                                            <span\n                                                class=\"badge bg-primary-gradient\">";
    echo format_currency($row["amount"]);
    echo "</span>\n                                        </td>\n                                        <td>";
    echo $row["bank"];
    echo " - ";
    echo $row["stk"];
    echo " - ";
    echo $row["name"];
    echo "</td>\n                                        <td class=\"text-center\">";
    echo display_withdraw($row["status"]);
    echo "</td>\n                                        <td><span class=\"badge bg-light text-dark\">";
    echo $row["create_gettime"];
    echo "</span>\n                                        </td>\n                                        <td>";
    echo $row["reason"];
    echo "</td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                            <div class=\"col-sm-12 col-md-12 mb-3\">\n                                <!--<button class=\"btn btn-danger btn-sm me-1\" type=\"button\" onclick=\"deleteConfirm()\"-->\n                                <!--    name=\"btn_delete\"><i class=\"fas fa-trash mr-1\"></i> Xóa bản ghi đã chọn</button>-->\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<div class=\"modal fade\" id=\"ModalDialog\" tabindex=\"-1\" aria-labelledby=\"modal-block-popout\" role=\"dialog\"\n    aria-hidden=\"true\">\n    <div class=\"modal-dialog modal-dialog-centered modal-xl dialog-scrollable\">\n        <div class=\"modal-content\">\n            <div id=\"modalEdit\"></div>\n        </div>\n    </div>\n</div>\n<script>\nfunction modalEdit(token, id) {\n    \$(\"#modalEdit\").html('');\n    \$.get(\"";
echo BASE_URL("ajaxs/admin/modal/withdraw-edit.php?id=");
echo "\" + id + '&token=' + token, function(data) {\n        \$(\"#modalEdit\").html(data);\n    });\n    \$('#ModalDialog').modal('show')\n}\n\nfunction postRemove(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeWithdraw',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        }\n    });\n}\n\nfunction deleteConfirm() {\n    var result = confirm(\"";
echo __("Bạn có thực sự muốn xóa các bản ghi đã chọn không?");
echo "\");\n    if (result) {\n        var checkbox = document.getElementsByName('checkbox');\n        for (var i = 0; i < checkbox.length; i++) {\n            if (checkbox[i].checked === true) {\n                postRemove(checkbox[i].value);\n            }\n        }\n    }\n    setTimeout(function() {\n        location.reload();\n    }, 1000);\n}\n\$(document).ready(function() {\n    \$('#check_all').on('click', function() {\n        if (this.checked) {\n            \$('.checkbox').each(function() {\n                this.checked = true;\n            });\n        } else {\n            \$('.checkbox').each(function() {\n                this.checked = false;\n            });\n        }\n    });\n    \$('.checkbox').on('click', function() {\n        if (\$('.checkbox:checked').length == \$('.checkbox').length) {\n            \$('#check_all').prop('checked', true);\n        } else {\n            \$('#check_all').prop('checked', false);\n        }\n    });\n});\n</script>\n<script type=\"text/javascript\">\nfunction RemoveRow(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"";
echo __("Warning");
echo "\",\n        message: \"";
echo __("Bạn có chắc chắn muốn xóa item id ");
echo " \" + id + \" không ?\",\n        confirmText: \"";
echo __("Đồng ý");
echo "\",\n        cancelText: \"";
echo __("Huỷ");
echo "\"\n    }).then((e) => {\n        if (e) {\n            postRemove(id);\n            location.reload();\n        }\n    })\n}\n</script>";

?>