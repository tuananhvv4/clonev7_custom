<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Lịch sử ngân hàng"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n \n\n \n";
$body["footer"] = "\n \n  \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_recharge")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_GET["limit"])) {
    $limit = (int) check_string($_GET["limit"]);
} else {
    $limit = 20;
}
if(isset($_GET["page"])) {
    $page = check_string((int) $_GET["page"]);
} else {
    $page = 1;
}
$from = ($page - 1) * $limit;
$where = " `id` > 0 ";
$shortByDate = "";
$description = "";
$tid = "";
$create_gettime = "";
$type = "";
$method = "";
if(!empty($_GET["method"])) {
    $method = check_string($_GET["method"]);
    $where .= " AND `method` LIKE \"%" . $method . "%\" ";
}
if(!empty($_GET["tid"])) {
    $tid = check_string($_GET["tid"]);
    $where .= " AND `tid` = \"" . $tid . "\" ";
}
if(!empty($_GET["type"])) {
    $type = check_string($_GET["type"]);
    $where .= " AND `type` = \"" . $type . "\" ";
}
if(!empty($_GET["description"])) {
    $description = check_string($_GET["description"]);
    $description = str_replace(" ", "", $description);
    $where .= " AND `description` LIKE \"%" . $description . "%\" ";
}
if(!empty($_GET["create_gettime"])) {
    $create_gettime = check_string($_GET["create_gettime"]);
    $create_date_1 = str_replace("-", "/", $create_gettime);
    $create_date_1 = explode(" to ", $create_date_1);
    if($create_date_1[0] != $create_date_1[1]) {
        $create_date_1 = [$create_date_1[0] . " 00:00:00", $create_date_1[1] . " 23:59:59"];
        $where .= " AND `create_gettime` >= '" . $create_date_1[0] . "' AND `create_gettime` <= '" . $create_date_1[1] . "' ";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `log_bank_auto` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `log_bank_auto` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("log-auto-bank&limit=" . $limit . "&shortByDate=" . $shortByDate . "&time=" . $create_gettime . "&tid=" . $tid . "&description=" . $description . "&method=" . $method . "&type=" . $type . "&"), $from, $totalDatatable, $limit);
$yesterday = date("Y-m-d", strtotime("-1 day"));
$currentWeek = date("W");
$currentMonth = date("m");
$currentYear = date("Y");
$currentDate = date("Y-m-d");
$total_yesterday = (int) $CMSNT->get_row("SELECT SUM(amount) FROM log_bank_auto WHERE  `create_gettime` LIKE '%" . $yesterday . "%' ")["SUM(amount)"];
$total_today = $CMSNT->get_row("SELECT SUM(amount) FROM log_bank_auto WHERE  `create_gettime` LIKE '%" . $currentDate . "%' ")["SUM(amount)"];
$total_all_time = $CMSNT->get_row("SELECT SUM(amount) FROM log_bank_auto ")["SUM(amount)"];
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-building-columns\"></i> Lịch sử giao dịch Ngân Hàng</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                \n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"log-auto-bank\">\n                                <div class=\"col-md-3 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $tid;
echo "\" name=\"tid\"\n                                        placeholder=\"";
echo __("Mã giao dịch");
echo "\">\n                                </div>\n                                <div class=\"col-md-3 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $description;
echo "\"\n                                        name=\"description\" placeholder=\"";
echo __("Nội dung chuyển khoản");
echo "\">\n                                </div>\n                                <div class=\"col-md-3 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $method;
echo "\" name=\"method\"\n                                        placeholder=\"";
echo __("Ngân hàng");
echo "\">\n                                </div>\n                                <div class=\"col-md-3 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $type;
echo "\" name=\"type\"\n                                        placeholder=\"";
echo __("Type");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
echo $create_gettime;
echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-sm btn-danger\" href=\"";
echo base_url_admin("log-auto-bank");
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
echo " value=\"1000\">1000\n                                        </option>\n                                    </select>\n                                </div>\n                                <div class=\"filter-short\">\n                                    <label class=\"filter-label\">";
echo __("Short by Date:");
echo "</label>\n                                    <select name=\"shortByDate\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option value=\"\">";
echo __("Tất cả");
echo "</option>\n                                        <option ";
echo $shortByDate == 1 ? "selected" : "";
echo " value=\"1\">\n                                            ";
echo __("Hôm nay");
echo "                                        </option>\n                                        <option ";
echo $shortByDate == 2 ? "selected" : "";
echo " value=\"2\">\n                                            ";
echo __("Tuần này");
echo "                                        </option>\n                                        <option ";
echo $shortByDate == 3 ? "selected" : "";
echo " value=\"3\">\n                                            ";
echo __("Tháng này");
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive table-wrapper mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead class=\"table\">\n                                    <tr>\n                                        <th>";
echo __("Thời gian");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Ngân hàng");
echo "</th>\n                                        <th class=\"text-right\">";
echo __("Số tiền nạp");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Mã giao dịch");
echo "</th>\n                                        <th>";
echo __("Nội dung chuyển khoản");
echo "</th>\n                                        <th class=\"text-center\">Type</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
$i = 0;
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td><strong>";
    echo $row["create_gettime"];
    echo "</strong> (<i>";
    echo timeAgo(strtotime($row["create_gettime"]));
    echo "</i>)</td>\n                                        <td class=\"text-center\"><b>";
    echo $row["method"];
    echo "</b></td>\n                                        <td class=\"text-right\"><b\n                                                style=\"color: green;\">";
    echo format_currency($row["amount"]);
    echo "</b>\n                                        </td>\n                                        <td class=\"text-center\"><b style=\"color:red;\">";
    echo $row["tid"];
    echo "</b></td>\n                                        <td>";
    echo $row["description"];
    echo "</td>\n                                        <td class=\"text-center\"><b>";
    echo $row["type"];
    echo "</b></td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of\n                                    ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n    ";
require_once __DIR__ . "/footer.php";

?>