<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Users"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n";
$body["footer"] = "\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_user")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["AddUser"])) {
    if(empty($_POST["username"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập username\")){window.history.back().location.reload();}</script>");
    }
    $username = check_string($_POST["username"]);
    if(!validateUsername($username)) {
        exit("<script type=\"text/javascript\">if(!alert(\"Username không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["email"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập địa chỉ Email\")){window.history.back().location.reload();}</script>");
    }
    $email = check_string($_POST["email"]);
    if(!validateEmail($email)) {
        exit("<script type=\"text/javascript\">if(!alert(\"Định dạng Email không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    if(0 < $CMSNT->num_rows("SELECT * FROM `users` WHERE `username` = '" . $username . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Tên đăng nhập đã tồn tại trong hệ thống\")){window.history.back().location.reload();}</script>");
    }
    if(0 < $CMSNT->num_rows("SELECT * FROM `users` WHERE `email` = '" . $email . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Địa chỉ email đã tồn tại trong hệ thống\")){window.history.back().location.reload();}</script>");
    }
    $google2fa = new PragmaRX\Google2FAQRCode\Google2FA();
    $isInsert = $CMSNT->insert("users", ["username" => check_string($_POST["username"]), "password" => TypePassword(check_string($_POST["username"])), "email" => check_string($_POST["email"]), "create_date" => gettime(), "update_date" => gettime(), "token" => md5(random("qwertyuiopasddfghjklzxcvbnm1234567890", 6) . time()), "money" => 0, "api_key" => md5(time() . random("QWERTYUIOPASDFGHJKL", 6)), "SecretKey_2fa" => $google2fa->generateSecretKey()]);
    if($isInsert) {
        admin_msg_success("Thêm user thành công!", "", 1000);
    }
}
$users = $CMSNT->get_list("SELECT * FROM `users` ORDER BY id DESC  ");
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
$order_by = " ORDER BY id DESC ";
$username = "";
$name = "";
$email = "";
$phone = "";
$status = "";
$role = "";
$money = "";
$discount = "";
$ip = "";
$id = "";
$shortByDate = "";
$utm_source = "";
$total_money = "";
if(!empty($_GET["utm_source"])) {
    $utm_source = check_string($_GET["utm_source"]);
    $where .= " AND `utm_source` LIKE \"%" . $utm_source . "%\" ";
}
if(!empty($_GET["id"])) {
    $id = check_string($_GET["id"]);
    $where .= " AND `id` = " . $id . " ";
}
if(!empty($_GET["username"])) {
    $username = check_string($_GET["username"]);
    $where .= " AND `username` LIKE \"%" . $username . "%\" ";
}
if(!empty($_GET["name"])) {
    $name = check_string($_GET["name"]);
    $where .= " AND `fullname` LIKE \"%" . $name . "%\" ";
}
if(!empty($_GET["email"])) {
    $email = check_string($_GET["email"]);
    $where .= " AND `email` LIKE \"%" . $email . "%\" ";
}
if(!empty($_GET["phone"])) {
    $phone = check_string($_GET["phone"]);
    $where .= " AND `phone` LIKE \"%" . $phone . "%\" ";
}
if(!empty($_GET["status"])) {
    $status = check_string($_GET["status"]);
    if($status == 1) {
        $where .= " AND `banned` = 0 ";
    } elseif($status == 2) {
        $where .= " AND `banned` = 1 ";
    }
}
if(!empty($_GET["role"])) {
    $role = check_string($_GET["role"]);
    if($role == 1) {
        $where .= " AND `ctv` = 1 ";
    } elseif($role == 2) {
        $where .= " AND `admin` != 0 ";
    }
}
if(!empty($_GET["money"])) {
    $money = check_string($_GET["money"]);
    if($money == 1) {
        $order_by = " ORDER BY `money` ASC ";
    } elseif($money == 2) {
        $order_by = " ORDER BY `money` DESC ";
    }
}
if(!empty($_GET["total_money"])) {
    $total_money = check_string($_GET["total_money"]);
    if($total_money == 1) {
        $order_by = " ORDER BY `total_money` ASC ";
    } elseif($total_money == 2) {
        $order_by = " ORDER BY `total_money` DESC ";
    }
}
if(!empty($_GET["discount"])) {
    $discount = check_string($_GET["discount"]);
    if($discount == 1) {
        $order_by = " ORDER BY `discount` ASC ";
    } elseif($discount == 2) {
        $order_by = " ORDER BY `discount` DESC ";
    }
}
if(!empty($_GET["ip"])) {
    $ip = check_string($_GET["ip"]);
    $where .= " AND `ip` LIKE \"%" . $ip . "%\" ";
}
if(isset($_GET["shortByDate"])) {
    $shortByDate = check_string($_GET["shortByDate"]);
    $yesterday = date("Y-m-d", strtotime("-1 day"));
    $currentWeek = date("W");
    $currentMonth = date("m");
    $currentYear = date("Y");
    $currentDate = date("Y-m-d");
    if($shortByDate == 1) {
        $where .= " AND `create_date` LIKE '%" . $currentDate . "%' ";
    }
    if($shortByDate == 2) {
        $where .= " AND YEAR(create_date) = " . $currentYear . " AND WEEK(create_date, 1) = " . $currentWeek . " ";
    }
    if($shortByDate == 3) {
        $where .= " AND MONTH(create_date) = '" . $currentMonth . "' AND YEAR(create_date) = '" . $currentYear . "' ";
    }
    if($shortByDate == 4) {
        $where .= " AND DATE(create_date) = '" . $yesterday . "' ";
    }
}
$listDatatable = $CMSNT->get_list("SELECT * FROM `users` WHERE " . $where . " " . $order_by . " LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `users` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("users&limit=" . $limit . "&shortByDate=" . $shortByDate . "&username=" . $username . "&name=" . $name . "&email=" . $email . "&phone=" . $phone . "&status=" . $status . "&role=" . $role . "&money=" . $money . "&ip=" . $ip . "&id=" . $id . "&utm_source=" . $utm_source . "&discount=" . $discount . "&total_money=" . $total_money . "&"), $from, $totalDatatable, $limit);
echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-users\"></i> Users</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-top\">\n                            <div class=\"me-3\">\n                                <span class=\"avatar avatar-md p-2 bg-primary\">\n                                    <svg class=\"svg-white\" xmlns=\"http://www.w3.org/2000/svg\"\n                                        enable-background=\"new 0 0 24 24\" height=\"24px\" viewBox=\"0 0 24 24\" width=\"24px\"\n                                        fill=\"#000000\">\n                                        <rect fill=\"none\" height=\"24\" width=\"24\"></rect>\n                                        <g>\n                                            <path\n                                                d=\"M4,13c1.1,0,2-0.9,2-2c0-1.1-0.9-2-2-2s-2,0.9-2,2C2,12.1,2.9,13,4,13z M5.13,14.1C4.76,14.04,4.39,14,4,14 c-0.99,0-1.93,0.21-2.78,0.58C0.48,14.9,0,15.62,0,16.43V18l4.5,0v-1.61C4.5,15.56,4.73,14.78,5.13,14.1z M20,13c1.1,0,2-0.9,2-2 c0-1.1-0.9-2-2-2s-2,0.9-2,2C18,12.1,18.9,13,20,13z M24,16.43c0-0.81-0.48-1.53-1.22-1.85C21.93,14.21,20.99,14,20,14 c-0.39,0-0.76,0.04-1.13,0.1c0.4,0.68,0.63,1.46,0.63,2.29V18l4.5,0V16.43z M16.24,13.65c-1.17-0.52-2.61-0.9-4.24-0.9 c-1.63,0-3.07,0.39-4.24,0.9C6.68,14.13,6,15.21,6,16.39V18h12v-1.61C18,15.21,17.32,14.13,16.24,13.65z M8.07,16 c0.09-0.23,0.13-0.39,0.91-0.69c0.97-0.38,1.99-0.56,3.02-0.56s2.05,0.18,3.02,0.56c0.77,0.3,0.81,0.46,0.91,0.69H8.07z M12,8 c0.55,0,1,0.45,1,1s-0.45,1-1,1s-1-0.45-1-1S11.45,8,12,8 M12,6c-1.66,0-3,1.34-3,3c0,1.66,1.34,3,3,3s3-1.34,3-3 C15,7.34,13.66,6,12,6L12,6z\">\n                                            </path>\n                                        </g>\n                                    </svg>\n                                </span>\n                            </div>\n                            <div class=\"flex-fill\">\n                                <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                    <h5 class=\"fw-semibold mb-0 lh-1\">\n                                        ";
echo format_cash($CMSNT->get_row(" SELECT COUNT(id) FROM users ")["COUNT(id)"]);
echo "                                    </h5>\n                                </div>\n                                <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">TỔNG THÀNH VIÊN</p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-top\">\n                            <div class=\"me-3\">\n                                <span class=\"avatar avatar-md p-2 bg-secondary\">\n                                    <i class=\"fa-solid fa-money-bill fs-18\"></i>\n                                </span>\n                            </div>\n                            <div class=\"flex-fill\">\n                                <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                    <h5 class=\"fw-semibold mb-0 lh-1\">\n                                        ";
echo format_currency($CMSNT->get_row(" SELECT SUM(money) FROM users ")["SUM(money)"]);
echo "                                    </h5>\n                                </div>\n                                <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">SỐ DƯ CÒN LẠI</p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-top\">\n                            <div class=\"me-3\">\n                                <span class=\"avatar avatar-md p-2 bg-warning\">\n                                    <i class=\"fa-solid fa-user-tie fs-18\"></i>\n                                </span>\n                            </div>\n                            <div class=\"flex-fill\">\n                                <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                    <h5 class=\"fw-semibold mb-0 lh-1\">\n                                        ";
echo format_cash($CMSNT->get_row(" SELECT COUNT(id) FROM users WHERE `admin` != 0 ")["COUNT(id)"]);
echo "                                    </h5>\n                                </div>\n                                <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">ADMIN</p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-top\">\n                            <div class=\"me-3\">\n                                <span class=\"avatar avatar-md p-2 bg-danger\">\n                                    <i class=\"fa-solid fa-lock fs-18\"></i>\n                                </span>\n                            </div>\n                            <div class=\"flex-fill\">\n                                <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                    <h5 class=\"fw-semibold mb-0 lh-1\">\n                                        ";
echo format_cash($CMSNT->get_row(" SELECT COUNT(id) FROM users WHERE `banned` != 0 ")["COUNT(id)"]);
echo "                                    </h5>\n                                </div>\n                                <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">Banned</p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"alert alert-solid-dark alert-dismissible fade show text-white\">\n            <p>Nếu bạn muốn tracking thành viên đăng ký, bạn có thể chèn <strong>?utm_sourc=ten_chien_dich</strong> vào\n                cuối link web để thu thập dữ liệu nơi thành viên đăng ký.</p>\n            <p>Ví dụ bạn muốn biết có bao nhiêu user đăng ký trong chiến dịch quảng cáo <strong>ABC</strong>, bạn chèn\n                link web vào quảng cáo như sau => <strong>";
echo base_url();
echo "?utm_source=camp_abc</strong></p>\n            <button type=\"button\" class=\"btn-close text-white\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"text-right\">\n                    <button type=\"button\" onclick=\"phan_tich_utm_source_users()\" class=\"btn btn-danger btn-sm mb-3\">\n                        <i class=\"fa-solid fa-chart-line\"></i> THỐNG KÊ UTM_SOURCE\n                    </button>\n                    <button type=\"button\" onclick=\"reset_tongnap()\" id=\"reset_tongnap\" class=\"btn btn-info btn-sm mb-3\">\n                        <i class=\"fa-solid fa-eraser\"></i> RESET TỔNG NẠP\n                    </button>\n                </div>\n            </div>\n            <div class=\"modal fade\" id=\"phan_tich_utm_source_users\" tabindex=\"-1\"\n                aria-labelledby=\"phan_tich_utm_source_users\" data-bs-keyboard=\"false\" aria-hidden=\"true\">\n                <!-- Scrollable modal -->\n                <div class=\"modal-dialog modal-dialog-centered modal-xl\">\n                    <div class=\"modal-content\">\n                        <div class=\"modal-header\">\n                            <h6 class=\"modal-title\" id=\"phan_tich_utm_source_users\"><i\n                                    class=\"fa-solid fa-chart-line\"></i>\n                                THỐNG KÊ UTM SOURCE\n                            </h6>\n                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n                        </div>\n                        <div class=\"modal-body\">\n                            <div id=\"hien_thi_phan_tich_utm_source_users\" class=\"mb-3\"></div>\n\n                            <p>Nếu bạn muốn tracking thành viên đăng ký, bạn có thể chèn\n                                <strong>?utm_sourc=ten_chien_dich</strong> vào\n                                cuối link web để thu thập dữ liệu nơi thành viên đăng ký.\n                            </p>\n                            <p>Ví dụ bạn muốn biết có bao nhiêu user đăng ký trong chiến dịch quảng cáo\n                                <strong>ABC</strong>, bạn chèn\n                                link web vào quảng cáo như sau =>\n                                <strong>";
echo base_url();
echo "?utm_source=camp_abc</strong>\n                            </p>\n\n                        </div>\n                        <div class=\"modal-footer\">\n                            <button type=\"button\" class=\"btn btn-light shadow-light btn-wave\"\n                                data-bs-dismiss=\"modal\">Đóng</button>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <script type=\"text/javascript\">\n            function phan_tich_utm_source_users() {\n                \$('#hien_thi_phan_tich_utm_source_users').html(\n                    '<h5 class=\"mb-3 py-4 text-center\"><i class=\"fa fa-spinner fa-spin\"></i> Đang phân tích dữ liệu, vui lòng chờ...</h5>'\n                );\n                \$('#phan_tich_utm_source_users').modal('show');\n                \$.ajax({\n                    url: \"";
echo base_url("ajaxs/admin/view.php");
echo "\",\n                    method: \"POST\",\n                    data: {\n                        action: 'phan_tich_utm_source_users',\n                        token: '";
echo $getUser["token"];
echo "'\n                    },\n                    success: function(result) {\n                        \$('#hien_thi_phan_tich_utm_source_users').html(result);\n                    },\n                    error: function() {\n                        \$('#hien_thi_phan_tich_utm_source_users').html(result);\n                    }\n                });\n            }\n            </script>\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH THÀNH VIÊN\n                        </div>\n                        <div class=\"d-flex\">\n                            <button data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                                class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                    class=\"ri-add-line fw-semibold align-middle\"></i> Thêm thành viên</button>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"users\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" type=\"number\" value=\"";
echo $id;
echo "\" name=\"id\"\n                                        placeholder=\"ID Khách hàng\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" type=\"text\" value=\"";
echo $username;
echo "\" name=\"username\"\n                                        placeholder=\"Username\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" value=\"";
echo $name;
echo "\" name=\"name\" placeholder=\"Full name\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" value=\"";
echo $email;
echo "\" name=\"email\" placeholder=\"Email\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" value=\"";
echo $phone;
echo "\" name=\"phone\" placeholder=\"Phone\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" value=\"";
echo $ip;
echo "\" name=\"ip\" placeholder=\"Địa chỉ IP\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" value=\"";
echo $utm_source;
echo "\" name=\"utm_source\"\n                                        placeholder=\"utm_source\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <select name=\"status\" class=\"form-control\">\n                                        <option value=\"\">Trạng thái\n                                        </option>\n                                        <option ";
echo $status == 2 ? "selected" : "";
echo " value=\"2\">Banned\n                                        </option>\n                                        <option ";
echo $status == 1 ? "selected" : "";
echo " value=\"1\">Active\n                                        </option>\n                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <select name=\"role\" class=\"form-control\">\n                                        <option value=\"\">Vai trò\n                                        </option>\n                                        <option ";
echo $role == 1 ? "selected" : "";
echo " value=\"1\">CTV\n                                        </option>\n                                        <option ";
echo $role == 2 ? "selected" : "";
echo " value=\"2\">Admin\n                                        </option>\n                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <select name=\"money\" class=\"form-control\">\n                                        <option value=\"\">Sắp xếp số dư\n                                        </option>\n                                        <option ";
echo $money == 1 ? "selected" : "";
echo " value=\"1\">Tăng dần\n                                        </option>\n                                        <option ";
echo $money == 2 ? "selected" : "";
echo " value=\"2\">Giảm dần\n                                        </option>\n                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <select name=\"total_money\" class=\"form-control\">\n                                        <option value=\"\">Sắp xếp tổng nạp\n                                        </option>\n                                        <option ";
echo $total_money == 1 ? "selected" : "";
echo " value=\"1\">Tăng dần\n                                        </option>\n                                        <option ";
echo $total_money == 2 ? "selected" : "";
echo " value=\"2\">Giảm dần\n                                        </option>\n                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <select name=\"discount\" class=\"form-control\">\n                                        <option value=\"\">Sắp xếp chiết khấu\n                                        </option>\n                                        <option ";
echo $discount == 1 ? "selected" : "";
echo " value=\"1\">Tăng dần\n                                        </option>\n                                        <option ";
echo $discount == 2 ? "selected" : "";
echo " value=\"2\">Giảm dần\n                                        </option>\n                                    </select>\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-danger\" href=\"";
echo base_url_admin("users");
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
echo " value=\"10000\">10.000</option>\n                                        <option ";
echo $limit == 15000 ? "selected" : "";
echo " value=\"15000\">15.000</option>\n                                        <option ";
echo $limit == 20000 ? "selected" : "";
echo " value=\"20000\">20.000</option>\n                                        <option ";
echo $limit == 30000 ? "selected" : "";
echo " value=\"30000\">30.000</option>\n                                        <option ";
echo $limit == 40000 ? "selected" : "";
echo " value=\"40000\">40.000</option>\n                                        <option ";
echo $limit == 50000 ? "selected" : "";
echo " value=\"50000\">50.000</option>\n                                    </select>\n                                </div>\n                                <div class=\"filter-short\">\n                                    <label class=\"filter-label\">";
echo __("Short by Date:");
echo "</label>\n                                    <select name=\"shortByDate\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option value=\"\">";
echo __("Tất cả");
echo "</option>\n                                        <option ";
echo $shortByDate == 1 ? "selected" : "";
echo " value=\"1\">";
echo __("Hôm nay");
echo "                                        </option>\n                                        <option ";
echo $shortByDate == 4 ? "selected" : "";
echo " value=\"4\">";
echo __("Hôm qua");
echo "                                        </option>\n                                        <option ";
echo $shortByDate == 2 ? "selected" : "";
echo " value=\"2\">";
echo __("Tuần này");
echo "                                        </option>\n                                        <option ";
echo $shortByDate == 3 ? "selected" : "";
echo " value=\"3\">\n                                            ";
echo __("Tháng này");
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive table-wrapper mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                                    id=\"check_all_checkbox_users\" value=\"option1\">\n                                            </div>\n                                        </th>\n                                        <th scope=\"col\">Username</th>\n                                        <th scope=\"col\">Email</th>\n                                        <th scope=\"col\" class=\"text-center\">Số dư khả dụng</th>\n                                        <th scope=\"col\" class=\"text-center\">Tổng nạp</th>\n                                        <th scope=\"col\" class=\"text-center\">Chiết khấu</th>\n                                        <th scope=\"col\" class=\"text-center\">Admin</th>\n                                        <th scope=\"col\" class=\"text-center\">Trạng thái</th>\n                                        <th scope=\"col\" class=\"text-center\">Hoạt động</th>\n                                        <th scope=\"col\" class=\"text-center\">utm_source</th>\n                                        <th scope=\"col\">Thời gian</th>\n                                        <th scope=\"col\" class=\"text-center\">Thao tác</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
$i = 0;
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input checkbox_users\"\n                                                    data-id=\"";
    echo $row["id"];
    echo "\" name=\"checkbox_users\"\n                                                    value=\"";
    echo $row["id"];
    echo "\" />\n                                            </div>\n                                        </td>\n                                        <td><a class=\"text-primary\"\n                                                href=\"";
    echo base_url_admin("user-edit&id=" . $row["id"]);
    echo "\">";
    echo $row["username"];
    echo "                                                [ID ";
    echo $row["id"];
    echo "]</a>\n                                        </td>\n                                        <td>\n                                            <i class=\"fa fa-envelope\" aria-hidden=\"true\"></i> ";
    echo $row["email"];
    echo "                                        </td>\n                                        <td class=\"text-right\">\n                                            <b style=\"color:blue;\">";
    echo format_currency($row["money"]);
    echo "</b>\n                                        </td>\n                                        <td class=\"text-right\">\n                                            <b style=\"color:red;\">";
    echo format_currency($row["total_money"]);
    echo "</b>\n                                        </td>\n                                        <td class=\"text-right\">\n                                            <b>";
    echo format_cash($row["discount"]);
    echo "%</b>\n                                        </td>\n                                        <td class=\"text-center\">";
    echo display_mark($row["admin"]);
    echo "</td>\n                                        <td class=\"text-center\">\n                                            ";
    echo display_banned($row["banned"]);
    echo "                                        </td>\n                                        <td class=\"text-center\">";
    echo display_online($row["time_session"]);
    echo "</td>\n                                        <td class=\"text-center\">";
    echo $row["utm_source"];
    echo "</td>\n                                        <td><span>";
    echo $row["create_date"];
    echo "</span></td>\n                                        <td class=\"text-center fs-base\">\n                                            <a href=\"";
    echo base_url_admin("user-edit&id=" . $row["id"]);
    echo "\"\n                                                class=\"btn btn-sm btn-primary shadow-primary btn-wave\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
    echo __("Edit");
    echo "\">\n                                                <i class=\"fa fa-fw fa-edit\"></i> Edit\n                                            </a>\n                                            <a type=\"button\" onclick=\"removeAccount('";
    echo $row["id"];
    echo "')\"\n                                                class=\"btn btn-sm btn-danger shadow-danger btn-wave\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
    echo __("Delete");
    echo "\">\n                                                <i class=\"fas fa-trash\"></i> Delete\n                                            </a>\n                                            ";
    if(!checkPermission($getUser["admin"], "login_user")) {
        echo "                                            <a href=\"";
        echo base_url_admin("login-user&id=" . $row["id"]);
        echo "\"\n                                                class=\"btn btn-sm btn-info shadow-info btn-wave\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
        echo __("Login");
        echo "\">\n                                                <i class=\"fa fa-fw fa-sign-in\"></i> Login\n                                            </a>\n                                            ";
    }
    echo "                                        </td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                                <tfoot>\n                                    <td colspan=\"11\">\n                                        <div class=\"btn-list\">\n                                            <button type=\"button\" onclick=\"confirmDeleteAccount()\"\n                                                class=\"btn btn-outline-danger shadow-danger btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-trash\"></i> XÓA THÀNH VIÊN</button>\n                                            <button type=\"button\" id=\"btn_edit_status_user\"\n                                                class=\"btn btn-outline-success shadow-success btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-pen-to-square\"></i> CHỈNH TRẠNG\n                                                THÁI</button>\n                                        </div>\n                                    </td>\n                                </tfoot>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<div class=\"modal fade\" id=\"modal_edit_status_user\" tabindex=\"-1\" aria-labelledby=\"modal_edit_status_user\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Cập nhật trạng thái <mark\n                        class=\"checkboxeslength\"></mark> thành viên đã chọn</h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <div class=\"modal-body\">\n                <div class=\"row mb-4\">\n                    <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Trạng thái:");
echo " <span\n                            class=\"text-danger\">*</span></label>\n                    <div class=\"col-sm-8\">\n                        <select class=\"form-control\" id=\"status\" required>\n                            <option value=\"1\">Banned</option>\n                            <option value=\"0\">Active</option>\n                        </select>\n                    </div>\n                </div>\n                <p>Khi bạn nhấn vào nút UPDATE đồng nghĩa các thành viên mà bạn đã chọn sẽ được cập nhật thành trạng\n                    thái trên.</p>\n            </div>\n            <div class=\"modal-footer\">\n                <button type=\"button\" class=\"btn btn-light\" data-bs-dismiss=\"modal\">Close</button>\n                <button type=\"button\" onclick=\"update_status_records()\" id=\"update_status_records\"\n                    class=\"btn btn-primary\"><i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "</button>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n<script type=\"text/javascript\">\nfunction update_status_records() {\n    \$('#update_status_records').html('<i class=\"fa fa-spinner fa-spin\"></i> Processing...').prop('disabled',\n        true);\n    var status = document.getElementById('status').value;\n    var checkbox = document.getElementsByName('checkbox_users');\n    // Sử dụng hàm đệ quy để thực hiện lần lượt từng postUpdate với thời gian chờ 100ms\n    function postUpdatesSequentially(index) {\n        if (index < checkbox.length) {\n            if (checkbox[index].checked === true) {\n                post_update_status_user(checkbox[index].value, status);\n            }\n            setTimeout(function() {\n                postUpdatesSequentially(index + 1);\n            }, 100);\n        } else {\n            Swal.fire({\n                title: \"Thành công!\",\n                text: \"Cập nhật trạng thái thành công\",\n                icon: \"success\"\n            });\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n            \$('#update_status_records').html('<i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "').prop(\n                'disabled',\n                false);\n        }\n    }\n    // Bắt đầu gọi hàm đệ quy từ index 0\n    postUpdatesSequentially(0);\n}\n\n\$(\"#btn_edit_status_user\").click(function() {\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_users\"]:checked');\n    if (checkboxes.length === 0) {\n        showMessage('Vui lòng chọn ít nhất một thành viên', 'error');\n        return;\n    }\n    \$(\".checkboxeslength\").html(checkboxes.length);\n    \$(\"#modal_edit_status_user\").modal('show');\n});\n\nfunction post_update_status_user(id, status) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'update_status_user',\n            id: id,\n            status: status\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, 'success');\n            } else {\n                showMessage(result.msg, 'error');\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n\n\nfunction logoutALL() {\n    cuteAlert({\n        type: \"question\",\n        title: \"";
echo __("WARNING");
echo "\",\n        message: \"";
echo __("The system will exit the login of all accounts, except for the Admin account, do you agree?");
echo "\",\n        confirmText: \"";
echo __("Agree");
echo "\",\n        cancelText: \"";
echo __("Close");
echo "\"\n    }).then((e) => {\n        if (e) {\n            \$('#logoutALL').html('<i class=\"fa fa-spinner fa-spin\"></i>').prop('disabled', true);\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    action: \"logoutALL\"\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        \$('#logoutALL').html(\n                            '<i class=\"fas fa-right-from-bracket mr-1\"></i>THOÁT TẤT CẢ').prop(\n                            'disabled', false);\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n\nfunction removeAccount(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa tài khoản\",\n        message: \"Bạn có chắc chắn muốn xóa tài khoản này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemoveAccount(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n\nfunction postRemoveAccount(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeUser',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        }\n    });\n}\n\nfunction confirmDeleteAccount() {\n    var checkbox = document.getElementsByName('checkbox_users');\n    var isAnyCheckboxChecked = false;\n    for (var i = 0; i < checkbox.length; i++) {\n        if (checkbox[i].checked === true) {\n            isAnyCheckboxChecked = true;\n            break;\n        }\n    }\n    if (!isAnyCheckboxChecked) {\n        showMessage('Vui lòng chọn ít nhất một thành viên', 'error');\n        return;\n    }\n    var result = confirm('Bạn có đồng ý xóa các thành viên đã chọn không?');\n    if (result) {\n        function postUpdatesSequentially(index) {\n            if (index < checkbox.length) {\n                if (checkbox[index].checked === true) {\n                    postRemoveAccount(checkbox[index].value);\n                }\n                setTimeout(function() {\n                    postUpdatesSequentially(index + 1);\n                }, 100);\n            } else {\n                setTimeout(function() {\n                    location.reload();\n                }, 1000);\n            }\n        }\n        postUpdatesSequentially(0);\n    }\n}\n\n\$(function() {\n    \$('#check_all_checkbox_users').on('click', function() {\n        \$('.checkbox_users').prop('checked', this.checked);\n    });\n    \$('.checkbox_users').on('click', function() {\n        \$('#check_all_checkbox_users').prop('checked', \$('.checkbox_users:checked')\n            .length === \$('.checkbox_users').length);\n    });\n});\n</script>\n\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Thêm thành viên mới</h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Username");
echo "</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"text\" class=\"form-control\" name=\"username\"\n                                placeholder=\"";
echo __("Please enter your username");
echo "\" required>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Password");
echo "</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"text\" class=\"form-control\" name=\"password\"\n                                placeholder=\"";
echo __("Please enter your password");
echo "\" required>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Email");
echo "</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"email\" class=\"form-control\" name=\"email\"\n                                placeholder=\"";
echo __("Please enter your email address");
echo "\" required>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddUser\" class=\"btn btn-primary btn-sm\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>\n                        ";
echo __("Submit");
echo "</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n\n<script>\nfunction reset_tongnap() {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận reset tổng nạp toàn bộ thành viên\",\n        message: \"Hệ thống sẽ reset tổng tiền đã nạp của toàn bộ users khi bạn nhấn Đồng ý.\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            \$('#reset_tongnap').html('<i class=\"fa fa-spinner fa-spin\"></i>').prop('disabled', true);\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    action: \"reset_total_money_users\"\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        \$('#reset_tongnap').html(\n                            '<i class=\"fa-solid fa-eraser\"></i> RESET TỔNG NẠP').prop(\n                            'disabled', false);\n                        setTimeout(function() {\n                            location.reload();\n                        }, 2000);\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n</script>";

?>