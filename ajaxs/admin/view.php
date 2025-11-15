<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
require_once __DIR__ . "/../../models/is_admin.php";
if(!isset($_POST["action"])) {
    $data = json_encode(["status" => "error", "msg" => __("The Request Not Found")]);
    exit($data);
}
if($_POST["action"] == "download_product_die") {
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
    }
    if(!checkPermission($getUser["admin"], "edit_stock_product")) {
        exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
    }
    $accounts = "";
    $current_product_code = "";
    foreach ($CMSNT->get_list(" SELECT * FROM `product_die` ORDER BY product_code ") as $row) {
        if($row["product_code"] != $current_product_code) {
            if($current_product_code != "") {
                $accounts .= PHP_EOL;
            }
            $current_product_code = $row["product_code"];
            $accounts .= PHP_EOL . PHP_EOL . PHP_EOL . "============== " . $CMSNT->get_row(" SELECT * FROM `products` WHERE `code` = '" . $current_product_code . "' ")["name"] . " | Kho Hàng: " . $current_product_code . " ==============" . PHP_EOL;
        }
        $accounts .= $row["account"] . PHP_EOL;
    }
    $data = json_encode(["status" => "success", "filename" => "all_list_die_" . gettime(), "accounts" => $accounts, "msg" => __("Xuất dữ liệu thành công")]);
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Tải toàn bộ tài khoản DIE về máy")]);
    exit($data);
} else {
    if($_POST["action"] == "view_chart_thong_ke_don_hang_api_thang") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_statistical")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        $month = date("m");
        $year = date("Y");
        $numOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $labels = [];
        $revenues = [];
        $profits = [];
        for ($day = 1; $day <= $numOfDays; $day++) {
            $date = $year . "-" . $month . "-" . $day;
            $query = "SELECT SUM(pay) AS total_pay, SUM(cost) AS total_cost FROM product_order WHERE `refund` = 0 AND `supplier_id` != 0 AND DATE(create_gettime) = '" . $date . "'";
            $result = $CMSNT->get_row($query);
            $labels[] = $day . "/" . $month . "/" . $year;
            $revenues[] = $result["total_pay"] ?? 0;
            $profits[] = ($result["total_pay"] ?? 0) - ($result["total_cost"] ?? 0);
        }
        exit(json_encode(["labels" => $labels, "revenues" => $revenues, "profits" => $profits]));
    }
    if($_POST["action"] == "view_chart_thong_ke_nap_tien_thang") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_statistical")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        $month = date("m");
        $year = date("Y");
        $numOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $labels = [];
        $data = [];
        for ($day = 1; $day <= $numOfDays; $day++) {
            $date = $year . "-" . $month . "-" . $day;
            $total_topup_bank = $CMSNT->get_row("SELECT SUM(amount) AS total FROM payment_bank WHERE DATE(create_gettime) = '" . $date . "'")["total"] ?? 0;
            $total_topup_card = $CMSNT->get_row("SELECT SUM(amount) AS total FROM cards WHERE `status` = 'completed' AND DATE(create_date) = '" . $date . "'")["total"] ?? 0;
            $total_topup_crypto = $CMSNT->get_row("SELECT SUM(received) AS total FROM payment_crypto WHERE `status` = 'completed' AND DATE(create_gettime) = '" . $date . "'")["total"] ?? 0;
            $total_topup_momo = $CMSNT->get_row("SELECT SUM(amount) AS total FROM payment_momo WHERE DATE(create_gettime) = '" . $date . "'")["total"] ?? 0;
            $total_topup_paypal = $CMSNT->get_row("SELECT SUM(price) AS total FROM payment_paypal WHERE DATE(create_date) = '" . $date . "'")["total"] ?? 0;
            $total_topup_pm = $CMSNT->get_row("SELECT SUM(price) AS total FROM payment_pm WHERE `status` = 1 AND DATE(create_date) = '" . $date . "'")["total"] ?? 0;
            $total_topup_squadco = $CMSNT->get_row("SELECT SUM(amount) AS total FROM payment_squadco WHERE DATE(create_gettime) = '" . $date . "'")["total"] ?? 0;
            $total_topup_toyyibpay = $CMSNT->get_row("SELECT SUM(amount) AS total FROM payment_toyyibpay WHERE `status` = 1 AND DATE(create_gettime) = '" . $date . "'")["total"] ?? 0;
            $total_topup = $total_topup_bank + $total_topup_card + $total_topup_crypto + $total_topup_momo + $total_topup_paypal + $total_topup_pm + $total_topup_squadco + $total_topup_toyyibpay;
            $labels[] = $day . "/" . $month . "/" . $year;
            $data[] = $total_topup;
        }
        exit(json_encode(["labels" => $labels, "data" => $data]));
    }
    if($_POST["action"] == "view_chart_thong_ke_don_hang_thang") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_statistical")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        $month = date("m");
        $year = date("Y");
        $numOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $labels = [];
        $revenues = [];
        $profits = [];
        for ($day = 1; $day <= $numOfDays; $day++) {
            $date = $year . "-" . $month . "-" . $day;
            $query = "SELECT SUM(pay) AS total_pay, SUM(cost) AS total_cost FROM product_order WHERE `refund` = 0 AND DATE(create_gettime) = '" . $date . "'";
            $result = $CMSNT->get_row($query);
            $labels[] = $day . "/" . $month . "/" . $year;
            $revenues[] = $result["total_pay"] ?? 0;
            $profits[] = ($result["total_pay"] ?? 0) - ($result["total_cost"] ?? 0);
        }
        exit(json_encode(["labels" => $labels, "revenues" => $revenues, "profits" => $profits]));
    }
    if($_POST["action"] == "show_thong_ke_dashboard") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_statistical")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        $currentDate = date("Y-m-d");
        $currentYear = date("Y");
        $currentMonth = date("m");
        $query1 = "SELECT \n                COUNT(id) AS total_orders_today, \n                SUM(pay) AS total_pay_today, \n                SUM(cost) AS total_cost_today \n              FROM `product_order` \n              WHERE `refund` = 0 \n              AND `create_gettime` LIKE '%" . $currentDate . "%'";
        $result1 = $CMSNT->get_row($query1);
        $total_orders_today = $result1["total_orders_today"];
        $total_pay_today = $result1["total_pay_today"];
        $total_cost_today = $result1["total_cost_today"];
        $profit_today = $total_pay_today - $total_cost_today;
        $new_users_today = $CMSNT->get_row("SELECT COUNT(id) AS total_users_today FROM `users` WHERE `create_date` LIKE '%" . $currentDate . "%'")["total_users_today"];
        $query2 = "SELECT \n                COUNT(id) AS total_orders_month, \n                SUM(pay) AS total_pay_month, \n                SUM(cost) AS total_cost_month \n              FROM `product_order` \n              WHERE `refund` = 0 \n              AND YEAR(create_gettime) = " . $currentYear . " \n              AND MONTH(create_gettime) = " . $currentMonth;
        $result2 = $CMSNT->get_row($query2);
        $total_orders_month = $result2["total_orders_month"];
        $total_pay_month = $result2["total_pay_month"];
        $total_cost_month = $result2["total_cost_month"];
        $profit_month = $total_pay_month - $total_cost_month;
        $new_users_month = $CMSNT->get_row("SELECT COUNT(id) AS total_users_month FROM `users` WHERE YEAR(create_date) = " . $currentYear . " AND MONTH(create_date) = " . $currentMonth)["total_users_month"];
        $query3 = "SELECT \n                COUNT(id) AS total_orders_all, \n                SUM(pay) AS total_pay_all, \n                SUM(cost) AS total_cost_all \n              FROM `product_order` \n              WHERE `refund` = 0";
        $result3 = $CMSNT->get_row($query3);
        $total_orders_all = $result3["total_orders_all"];
        $total_pay_all = $result3["total_pay_all"];
        $total_cost_all = $result3["total_cost_all"];
        $profit_all = $total_pay_all - $total_cost_all;
        $total_users_all = $CMSNT->get_row("SELECT COUNT(id) AS total_users_all FROM `users`")["total_users_all"];
        $data = ["total_orders_today" => format_cash($total_orders_today), "total_pay_today" => format_currency($total_pay_today), "total_cost_today" => format_currency($total_cost_today), "profit_today" => format_currency($profit_today), "new_users_today" => format_cash($new_users_today), "total_orders_month" => format_cash($total_orders_month), "total_pay_month" => format_currency($total_pay_month), "total_cost_month" => format_currency($total_cost_month), "profit_month" => format_currency($profit_month), "new_users_month" => format_cash($new_users_month), "total_orders_all" => format_cash($total_orders_all), "total_pay_all" => format_currency($total_pay_all), "total_cost_all" => format_currency($total_cost_all), "profit_all" => format_currency($profit_all), "total_users_all" => format_cash($total_users_all)];
        exit(json_encode($data));
    }
    if($_POST["action"] == "phan_tich_utm_source_users") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_user")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        $html = "<ul class=\"nav nav-tabs mb-5 nav-justified nav-style-1 d-sm-flex d-block\" id=\"myTab\" role=\"tablist\">";
        $html .= "<li class=\"nav-item\">";
        $html .= "<a class=\"nav-link active\" id=\"table-tab\" data-toggle=\"tab\" href=\"#table-content\" role=\"tab\" aria-controls=\"table-content\" aria-selected=\"true\">Table</a>";
        $html .= "</li>";
        $html .= "<li class=\"nav-item\">";
        $html .= "<a class=\"nav-link\" id=\"chart-tab\" data-toggle=\"tab\" href=\"#chart-content\" role=\"tab\" aria-controls=\"chart-content\" aria-selected=\"false\">Pie Chart</a>";
        $html .= "</li>";
        $html .= "</ul>";
        $html .= "<div class=\"tab-content\" id=\"myTabContent\">";
        $html .= "<div class=\"tab-pane fade show active\" id=\"table-content\" role=\"tabpanel\" aria-labelledby=\"table-tab\">";
        $html .= "<div class=\"table-responsive table-wrapper\" style=\"max-height: 500px;overflow-y: auto;\">";
        $html .= "<table class=\"table text-nowrap table-striped table-hover table-bordered\">\n            <thead>\n                <tr>\n                    <th class=\"text-center\">Xếp hạng</th>\n                    <th class=\"text-center\">utm_source</th>\n                    <th class=\"text-center\">Số thành viên đăng ký</th>\n                </tr>\n            </thead>\n            <tbody>";
        $i = 1;
        $data_labels = [];
        $data_user_counts = [];
        foreach ($CMSNT->get_list("SELECT \n    utm_source, \n    COUNT(*) AS total_users\nFROM users \nGROUP BY utm_source \nORDER BY total_users DESC ") as $row) {
            $data_labels[] = $row["utm_source"];
            $data_user_counts[] = $row["total_users"];
            $html .= "<tr>\n    <td class='text-center' style='font-size:15px;'>" . $i++ . "</td>\n    <td class='text-center'>" . $row["utm_source"] . "</td>\n    <td class='text-center'><b>" . format_cash($row["total_users"]) . "</b></td>\n  </tr>";
        }
        $html .= "</tbody>\n        </table>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "<div class=\"tab-pane fade\" id=\"chart-content\" role=\"tabpanel\" aria-labelledby=\"chart-tab\">";
        $html .= "<canvas id=\"myChart\" width=\"500\" height=\"300\"></canvas>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "<script>\n            \$(document).ready(function(){\n                \$(\"#table-tab\").click(function(){\n                    \$(\"#chart-content\").removeClass(\"show active\");\n                    \$(\"#chart-tab\").removeClass(\"active\");\n                    \$(\"#table-content\").addClass(\"show active\");\n                    \$(\"#table-tab\").addClass(\"active\");\n                });\n                \$(\"#chart-tab\").click(function(){\n                    \$(\"#table-content\").removeClass(\"show active\");\n                    \$(\"#table-tab\").removeClass(\"active\");\n                    \$(\"#chart-content\").addClass(\"show active\");\n                    \$(\"#chart-tab\").addClass(\"active\");\n                    // Thêm kịch bản JavaScript để vẽ biểu đồ Pie Chart\n                    var ctx = document.getElementById(\"myChart\").getContext(\"2d\");\n                    var myChart = new Chart(ctx, {\n                        type: \"pie\",\n                        data: {\n                            labels: " . json_encode($data_labels) . ",\n                            datasets: [{\n                                label: \"Số lượng người dùng\",\n                                data: " . json_encode($data_user_counts) . ",\n                                backgroundColor: [\n                                    \"rgba(255, 99, 132, 0.6)\",\n                                    \"rgba(54, 162, 235, 0.6)\",\n                                    \"rgba(255, 206, 86, 0.6)\",\n                                    \"rgba(75, 192, 192, 0.6)\",\n                                    \"rgba(153, 102, 255, 0.6)\",\n                                    \"rgba(255, 159, 64, 0.6)\"\n                                ],\n                                borderColor: [\n                                    \"rgba(255, 99, 132, 1)\",\n                                    \"rgba(54, 162, 235, 1)\",\n                                    \"rgba(255, 206, 86, 1)\",\n                                    \"rgba(75, 192, 192, 1)\",\n                                    \"rgba(153, 102, 255, 1)\",\n                                    \"rgba(255, 159, 64, 1)\"\n                                ],\n                                borderWidth: 1\n                            }]\n                        },\n                        options: {\n                            responsive: true,\n                            maintainAspectRatio: false,\n                            legend: {\n                                position: \"right\",\n                                labels: {\n                                    fontColor: \"black\",\n                                    fontSize: 12\n                                }\n                            }\n                        }\n                    });\n                });\n            });\n        </script>";
        exit($html);
    } elseif($_POST["action"] == "view_nap_tien_gan_day") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_recent_transactions")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        $deposits = $CMSNT->get_list("SELECT * FROM `deposit_log` WHERE `is_virtual` = 0 ORDER BY id DESC limit 100");
        $html = "";
        foreach ($deposits as $deposit) {
            $html .= "<li>\n        <div class=\"timeline-time text-end\">\n            <span class=\"date\">" . timeAgo($deposit["create_time"]) . "</span>\n        </div>\n        <div class=\"timeline-icon\">\n            <a href=\"javascript:void(0);\"></a>\n        </div>\n        <div class=\"timeline-body\">\n            <div class=\"d-flex align-items-top timeline-main-content flex-wrap mt-0\">\n                <div class=\"flex-fill\">\n                    <div class=\"d-flex align-items-center\">\n                        <div class=\"mt-sm-0 mt-2\">\n                            <p class=\"mb-0 text-muted\"><b style=\"color: green;\">" . getRowRealtime("users", $deposit["user_id"], "username") . "</b>\n                                thực hiện nạp <b style=\"color: blue;\">" . format_currency($deposit["amount"]) . "</b>\n                                bằng <b style=\"color:red\">" . $deposit["method"] . "</b> thực nhận <b style=\"color:blue;\">" . format_currency($deposit["received"]) . "</b>\n                            </p>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </li>";
        }
        exit($html);
    } elseif($_POST["action"] == "view_don_hang_gan_day") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_recent_transactions")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        $orders = $CMSNT->get_list("SELECT * FROM `order_log` WHERE `is_virtual` = 0 ORDER BY id DESC limit 100");
        $html = "";
        foreach ($orders as $order) {
            $html .= "<li>\n            <div class=\"timeline-time text-end\">\n                <span class=\"date\">" . timeAgo($order["create_time"]) . "</span>\n            </div>\n            <div class=\"timeline-icon\">\n                <a href=\"javascript:void(0);\"></a>\n            </div>\n            <div class=\"timeline-body\">\n                <div class=\"d-flex align-items-top timeline-main-content flex-wrap mt-0\">\n                    <div class=\"flex-fill\">\n                        <div class=\"d-flex align-items-center\">\n                            <div class=\"mt-sm-0 mt-2\">\n                                <p class=\"mb-0 text-muted\"><b style=\"color: green;\">" . getRowRealtime("users", $order["buyer"], "username") . "</b>\n                                    mua <b style=\"color: red;\">" . format_cash($order["amount"]) . "</b>\n                                    <b>" . $order["product_name"] . "</b> với giá <b style=\"color:blue;\">" . format_currency($order["pay"]) . "</b>\n                                </p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </li>";
        }
        exit($html);
    } elseif($_POST["action"] == "top_san_pham_ban_chay") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_order_product")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        $html = "<ul class=\"nav nav-tabs mb-5 nav-justified nav-style-1 d-sm-flex d-block\" id=\"myTab\" role=\"tablist\">";
        $html .= "<li class=\"nav-item\">";
        $html .= "<a class=\"nav-link active\" id=\"table-tab\" data-toggle=\"tab\" href=\"#table-content\" role=\"tab\" aria-controls=\"table-content\" aria-selected=\"true\">Table</a>";
        $html .= "</li>";
        $html .= "<li class=\"nav-item\">";
        $html .= "<a class=\"nav-link\" id=\"chart-tab\" data-toggle=\"tab\" href=\"#chart-content\" role=\"tab\" aria-controls=\"chart-content\" aria-selected=\"false\">Pie Chart</a>";
        $html .= "</li>";
        $html .= "</ul>";
        $html .= "<div class=\"tab-content\" id=\"myTabContent\">";
        $html .= "<div class=\"tab-pane fade show active\" id=\"table-content\" role=\"tabpanel\" aria-labelledby=\"table-tab\">";
        $html .= "<div class=\"table-responsive table-wrapper\" style=\"max-height: 500px;overflow-y: auto;\">";
        $html .= "<table class=\"table text-nowrap table-striped table-hover table-bordered\">\n            <thead>\n                <tr>\n                    <th scope=\"col\">Xếp hạng</th>\n                    <th scope=\"col\">Sản phẩm</th>\n                    <th scope=\"col\">Đơn hàng đã bán</th>\n                    <th scope=\"col\">Tài khoản đã bán</th>\n                    <th scope=\"col\">Doanh thu</th>\n                    <th scope=\"col\">Lợi nhuận</th>\n                </tr>\n            </thead>\n            <tbody>";
        $i = 1;
        $data_labels = [];
        $data_revenue = [];
        foreach ($CMSNT->get_list("SELECT \n    product_id, \n    product_name, \n    COUNT(*) AS total_orders, \n    SUM(amount) AS total_quantity, \n    SUM(pay) AS total_revenue,\n    SUM(cost) AS total_cost\nFROM product_order \nWHERE refund != 1 \nGROUP BY product_id, product_name \nORDER BY total_quantity DESC, total_orders DESC ") as $row) {
            $data_labels[] = $row["product_name"];
            $data_revenue[] = $row["total_revenue"];
            $profit = $row["total_revenue"] - $row["total_cost"];
            $html .= "<tr>\n    <td class='text-center' style='font-size:15px;'>" . $i++ . "</td>\n    <td><a class='text-primary' href='" . base_url_admin("product-edit&id=" . $row["product_id"]) . "'>" . $row["product_name"] . "</a></td>\n    <td class='text-right'><b>" . format_cash($row["total_orders"]) . "</b></td>\n    <td class='text-right'><b style='color:blue;'>" . format_cash($row["total_quantity"]) . "</b></td>\n    <td class='text-right'><b style='color:red;'>" . format_currency($row["total_revenue"]) . "</b></td>\n    <td class='text-right'><b style='color:green;'>" . format_currency($profit) . "</b></td>\n  </tr>";
        }
        $html .= "</tbody>\n        </table>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "<div class=\"tab-pane fade\" id=\"chart-content\" role=\"tabpanel\" aria-labelledby=\"chart-tab\">";
        $html .= "<canvas id=\"myChart\" width=\"500\" height=\"300\"></canvas>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "<script>\n            \$(document).ready(function(){\n                \$(\"#table-tab\").click(function(){\n                    \$(\"#chart-content\").removeClass(\"show active\");\n                    \$(\"#chart-tab\").removeClass(\"active\");\n                    \$(\"#table-content\").addClass(\"show active\");\n                    \$(\"#table-tab\").addClass(\"active\");\n                });\n                \$(\"#chart-tab\").click(function(){\n                    \$(\"#table-content\").removeClass(\"show active\");\n                    \$(\"#table-tab\").removeClass(\"active\");\n                    \$(\"#chart-content\").addClass(\"show active\");\n                    \$(\"#chart-tab\").addClass(\"active\");\n                    // Thêm kịch bản JavaScript để vẽ biểu đồ Pie Chart\n                    var ctx = document.getElementById(\"myChart\").getContext(\"2d\");\n                    var myChart = new Chart(ctx, {\n                        type: \"pie\",\n                        data: {\n                            labels: " . json_encode($data_labels) . ",\n                            datasets: [{\n                                label: \"Doanh Thu\",\n                                data: " . json_encode($data_revenue) . ",\n                                backgroundColor: [\n                                    \"rgba(255, 99, 132, 0.6)\",\n                                    \"rgba(54, 162, 235, 0.6)\",\n                                    \"rgba(255, 206, 86, 0.6)\",\n                                    \"rgba(75, 192, 192, 0.6)\",\n                                    \"rgba(153, 102, 255, 0.6)\",\n                                    \"rgba(255, 159, 64, 0.6)\"\n                                ],\n                                borderColor: [\n                                    \"rgba(255, 99, 132, 1)\",\n                                    \"rgba(54, 162, 235, 1)\",\n                                    \"rgba(255, 206, 86, 1)\",\n                                    \"rgba(75, 192, 192, 1)\",\n                                    \"rgba(153, 102, 255, 1)\",\n                                    \"rgba(255, 159, 64, 1)\"\n                                ],\n                                borderWidth: 1\n                            }]\n                        },\n                        options: {\n                            responsive: true,\n                            maintainAspectRatio: false,\n                            legend: {\n                                position: \"right\",\n                                labels: {\n                                    fontColor: \"black\",\n                                    fontSize: 12\n                                }\n                            }\n                        }\n                    });\n                });\n            });\n        </script>";
        exit($html);
    } elseif($_POST["action"] == "view_product_live") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "edit_stock_product")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        if(empty($_POST["code"])) {
            exit(json_encode(["status" => "error", "msg" => __("Mã kho hàng không hợp lệ")]));
        }
        $code = check_string($_POST["code"]);
        if(!($product_die = $CMSNT->get_row("SELECT * FROM `product_stock` WHERE `product_code` = '" . $code . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Mã kho hàng không tồn tại trong hệ thống")]));
        }
        $accounts = "";
        foreach ($CMSNT->get_list(" SELECT * FROM `product_stock` WHERE `product_code` = '" . $code . "' ORDER BY id DESC ") as $account) {
            $accounts .= htmlspecialchars_decode($account["account"]) . PHP_EOL;
        }
        $data = json_encode(["status" => "success", "accounts" => $accounts, "msg" => __("Xuất dữ liệu thành công")]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xem danh sách tài khoản LIVE của kho hàng") . " (" . $code . ")"]);
        exit($data);
    } elseif($_POST["action"] == "view_product_die") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "edit_stock_product")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        if(empty($_POST["code"])) {
            exit(json_encode(["status" => "error", "msg" => __("Mã kho hàng không hợp lệ")]));
        }
        $code = check_string($_POST["code"]);
        if(!($product_die = $CMSNT->get_row("SELECT * FROM `product_die` WHERE `product_code` = '" . $code . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Mã kho hàng không tồn tại trong hệ thống")]));
        }
        $accounts = "";
        foreach ($CMSNT->get_list(" SELECT * FROM `product_die` WHERE `product_code` = '" . $code . "' ORDER BY id DESC ") as $account) {
            $accounts .= htmlspecialchars_decode($account["account"]) . PHP_EOL;
        }
        $data = json_encode(["status" => "success", "accounts" => $accounts, "msg" => __("Xuất dữ liệu thành công")]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Xem danh sách tài khoản DIE của kho hàng") . " (" . $code . ")"]);
        exit($data);
    } elseif($_POST["action"] == "view_order") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_order_product")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        if(empty($_POST["trans_id"])) {
            exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không hợp lệ")]));
        }
        $trans_id = check_string($_POST["trans_id"]);
        if(!($order = $CMSNT->get_row("SELECT * FROM `product_order` WHERE `trans_id` = '" . $trans_id . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không tồn tại trong hệ thống")]));
        }
        $accounts = "";
        foreach ($CMSNT->get_list(" SELECT * FROM `product_sold` WHERE `trans_id` = '" . $trans_id . "' ORDER BY id DESC ") as $account) {
            $accounts .= htmlspecialchars_decode($account["account"]) . PHP_EOL;
        }
        $data = json_encode(["status" => "success", "accounts" => $accounts, "msg" => __("Lấy thành công chi tiết đơn hàng")]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("View order") . " (" . $order["trans_id"] . ")"]);
        exit($data);
    } elseif($_POST["action"] == "download_order") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!checkPermission($getUser["admin"], "view_order_product")) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn không có quyền sử dụng tính năng này")]));
        }
        if(empty($_POST["trans_id"])) {
            exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không hợp lệ")]));
        }
        $trans_id = check_string($_POST["trans_id"]);
        if(!($order = $CMSNT->get_row("SELECT * FROM `product_order` WHERE `trans_id` = '" . $trans_id . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không tồn tại trong hệ thống")]));
        }
        $accounts = "";
        foreach ($CMSNT->get_list(" SELECT * FROM `product_sold` WHERE `trans_id` = '" . $trans_id . "' ORDER BY id DESC ") as $account) {
            $accounts .= preg_replace("/\\s+/", "", $account["account"]) . PHP_EOL;
        }
        $file = $trans_id . ".txt";
        $data = json_encode(["status" => "success", "filename" => $file, "accounts" => $accounts, "msg" => __("Đang tải xuống đơn hàng...")]);
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Download order") . " (" . $order["trans_id"] . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Download order") . " (" . $order["trans_id"] . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit($data);
    }
}

?>