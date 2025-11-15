<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Logs", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_logs")) {
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
$user_id = "";
$content = "";
$createdate = "";
$ip = "";
$device = "";
$username = "";
$shortByDate = "";
if(!empty($_GET["username"])) {
    $username = check_string($_GET["username"]);
    if($idUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `username` = '" . $username . "' ")) {
        $where .= " AND `user_id` =  \"" . $idUser["id"] . "\" ";
    } else {
        $where .= " AND `user_id` =  \"\" ";
    }
}
if(!empty($_GET["ip"])) {
    $ip = check_string($_GET["ip"]);
    $where .= " AND `ip` LIKE \"%" . $ip . "%\" ";
}
if(!empty($_GET["device"])) {
    $device = check_string($_GET["device"]);
    $where .= " AND `device` LIKE \"%" . $device . "%\" ";
}
if(!empty($_GET["user_id"])) {
    $user_id = check_string($_GET["user_id"]);
    $where .= " AND `user_id` = \"" . $user_id . "\" ";
}
if(!empty($_GET["content"])) {
    $content = check_string($_GET["content"]);
    $where .= " AND `action` LIKE \"%" . $content . "%\" ";
}
if(!empty($_GET["createdate"])) {
    $create_date = check_string($_GET["createdate"]);
    $createdate = $create_date;
    $create_date_1 = str_replace("-", "/", $create_date);
    $create_date_1 = explode(" to ", $create_date_1);
    if($create_date_1[0] != $create_date_1[1]) {
        $create_date_1 = [$create_date_1[0] . " 00:00:00", $create_date_1[1] . " 23:59:59"];
        $where .= " AND `createdate` >= '" . $create_date_1[0] . "' AND `createdate` <= '" . $create_date_1[1] . "' ";
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
        $where .= " AND `createdate` LIKE '%" . $currentDate . "%' ";
    }
    if($shortByDate == 2) {
        $where .= " AND YEAR(createdate) = " . $currentYear . " AND WEEK(createdate, 1) = " . $currentWeek . " ";
    }
    if($shortByDate == 3) {
        $where .= " AND MONTH(createdate) = '" . $currentMonth . "' AND YEAR(createdate) = '" . $currentYear . "' ";
    }
}
$listDatatable = $CMSNT->get_list(" SELECT * FROM `logs` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `logs` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("logs&limit=" . $limit . "&shortByDate=" . $shortByDate . "&user_id=" . $user_id . "&content=" . $content . "&createdate=" . $createdate . "&ip=" . $ip . "&device=" . $device . "&username=" . $username . "&"), $from, $totalDatatable, $limit);
echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-clock-rotate-left\"></i> Nhật ký hoạt động</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            NHẬT KÝ HOẠT ĐỘNG\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"logs\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $user_id;
echo "\" name=\"user_id\"\n                                        placeholder=\"";
echo __("ID User");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $username;
echo "\" name=\"username\"\n                                        placeholder=\"";
echo __("Username");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $content;
echo "\" name=\"content\"\n                                        placeholder=\"";
echo __("Hành động");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $ip;
echo "\" name=\"ip\"\n                                        placeholder=\"";
echo __("Địa chỉ IP");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $device;
echo "\" name=\"device\"\n                                        placeholder=\"";
echo __("Thiết bị");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"createdate\" class=\"form-control form-control-sm\" id=\"daterange\"\n                                        value=\"";
echo $createdate;
echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\" href=\"";
echo base_url_admin("logs");
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
echo " value=\"1000\">1.000</option>\n                                        <option ";
echo $limit == 5000 ? "selected" : "";
echo " value=\"5000\">5.000</option>\n                                        <option ";
echo $limit == 10000 ? "selected" : "";
echo " value=\"10000\">10.000</option>\n                                    </select>\n                                </div>\n                                <div class=\"filter-short\">\n                                    <label class=\"filter-label\">";
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive table-wrapper mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th>#</th>\n                                        <th>";
echo __("Username");
echo "</th>\n                                        <th>";
echo __("Hành động");
echo "</th>\n                                        <th>";
echo __("Thời gian");
echo "</th>\n                                        <th>";
echo __("Địa chỉ IP");
echo "</th>\n                                        <th>";
echo __("Thiết bị");
echo "</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
$i = 0;
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td>";
    echo $row["id"];
    echo "</td>\n                                        <td><a class=\"text-primary\" href=\"";
    echo base_url_admin("user-edit&id=" . $row["user_id"]);
    echo "\">";
    echo getRowRealtime("users", $row["user_id"], "username");
    echo "                                                [ID ";
    echo $row["user_id"];
    echo "]</a>\n                                        </td>\n                                        <td>";
    echo $row["action"];
    echo "</td>\n                                        <td><span class=\"badge bg-light text-dark\" data-toggle=\"tooltip\" data-placement=\"bottom\"\n                                                title=\"";
    echo timeAgo(strtotime($row["createdate"]));
    echo "\">";
    echo $row["createdate"];
    echo "</span></td>\n                                        <td><span class=\"badge bg-danger-transparent\">";
    echo $row["ip"];
    echo "</span></td>\n                                        <td><small>";
    echo $row["device"];
    echo "</small></td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n";
require_once __DIR__ . "/footer.php";

?>