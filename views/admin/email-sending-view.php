<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "View Sending Report", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `email_campaigns` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        redirect(base_url_admin("email-campaigns"));
    }
} else {
    redirect(base_url_admin("email-campaigns"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_email_campaigns")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
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
$shortByDate = "";
$where = " `camp_id` = '" . $id . "'  ";
$status = "";
$username = "";
$email = "";
$update_gettime = "";
$user_id = "";
if(!empty($_GET["status"])) {
    $status = (int) check_string($_GET["status"]);
    if($status == 1) {
        $where .= " AND `status` = 0 ";
    } elseif($status == 2) {
        $where .= " AND `status` = 1 ";
    } elseif($status == 3) {
        $where .= " AND `status` = 2 ";
    } elseif($status == 4) {
        $where .= " AND `status` = 3 ";
    }
}
if(!empty($_GET["username"])) {
    $username = check_string($_GET["username"]);
    $user_id = $CMSNT->get_row(" SELECT `id` FROM `users` WHERE `username` = '" . $username . "' ")["id"];
    $where .= " AND `user_id` = \"" . $user_id . "\" ";
}
if(!empty($_GET["email"])) {
    $email = check_string($_GET["email"]);
    $user_id = $CMSNT->get_row(" SELECT `id` FROM `users` WHERE `email` = '" . $email . "' ")["id"];
    $where .= " AND `user_id` = \"" . $user_id . "\" ";
}
if(!empty($_GET["update_gettime"])) {
    $create_date = check_string($_GET["update_gettime"]);
    $update_gettime = $create_date;
    $create_date_1 = str_replace("-", "/", $create_date);
    $create_date_1 = explode(" to ", $create_date_1);
    if($create_date_1[0] != $create_date_1[1]) {
        $create_date_1 = [$create_date_1[0] . " 00:00:00", $create_date_1[1] . " 23:59:59"];
        $where .= " AND `update_gettime` >= '" . $create_date_1[0] . "' AND `update_gettime` <= '" . $create_date_1[1] . "' ";
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
        $where .= " AND `update_gettime` LIKE '%" . $currentDate . "%' ";
    }
    if($shortByDate == 2) {
        $where .= " AND YEAR(update_gettime) = " . $currentYear . " AND WEEK(update_gettime, 1) = " . $currentWeek . " ";
    }
    if($shortByDate == 3) {
        $where .= " AND MONTH(update_gettime) = '" . $currentMonth . "' AND YEAR(update_gettime) = '" . $currentYear . "' ";
    }
}
$listDatatable = $CMSNT->get_list(" SELECT * FROM `email_sending` WHERE " . $where . " ORDER BY `id` ASC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `email_sending` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("email-sending-view&id=" . $id . "&limit=" . $limit . "&shortByDate=" . $shortByDate . "&update_gettime=" . $update_gettime . "&status=" . $status . "&email=" . $email . "&username=" . $username . "&"), $from, $totalDatatable, $limit);
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">View Sending Report</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("email-campaigns");
echo "\">Email\n                                Campaigns</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">View Sending Report</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            VIEW SENDING REPORT\n                        </div>\n                        <div class=\"d-flex\">\n                            <a type=\"button\" href=\"";
echo base_url_admin("email-campaigns");
echo "\"\n                                class=\"btn btn-sm btn-danger btn-wave waves-light waves-effect waves-light\"><i class=\"fa-solid fa-rotate-left\"></i> Quay lại</a>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"email-sending-view\">\n                                <input type=\"hidden\" name=\"id\" value=\"";
echo $id;
echo "\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $user_id;
echo "\" name=\"user_id\"\n                                        placeholder=\"";
echo __("ID User");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $username;
echo "\" name=\"username\"\n                                        placeholder=\"";
echo __("Username");
echo "\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $email;
echo "\" name=\"email\"\n                                        placeholder=\"Email\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"update_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
echo $update_gettime;
echo "\"\n                                        placeholder=\"Chọn thời gian hoàn thành\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\"\n                                        href=\"";
echo base_url_admin("email-sending-view&id=" . $id);
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th>Username</th>\n                                        <th>Email</th>\n                                        <th class=\"text-center\">Trạng thái</th>\n                                        <th>Thời gian hoàn thành</th>\n                                        <th>Response</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td><i class=\"ace-icon fa fa-user bigger-130 mr-1\"></i>\n                                            <strong>";
    echo getRowRealtime("users", $row["user_id"], "username");
    echo "</strong>\n                                        </td>\n                                        <td><i class=\"ace-icon fa fa-envelope bigger-130 mr-1\"></i>\n                                            <strong>";
    echo getRowRealtime("users", $row["user_id"], "email");
    echo "</strong>\n                                        </td>\n                                        <td class=\"text-center\">";
    echo display_camp($row["status"]);
    echo "</td>\n                                        <td><i class=\"fa-solid fa-clock mr-1\"></i> ";
    echo $row["update_gettime"];
    echo "</td>\n                                        <td>\n                                            <textarea class=\"form-control\" rows=\"1\"\n                                                readonly>";
    echo $row["response"];
    echo "</textarea>\n                                        </td>\n                                    </tr>\n                                    ";
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