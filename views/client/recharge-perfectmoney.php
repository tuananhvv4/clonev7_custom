<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Nạp tiền bằng Perfect Money") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_user.php";
if($CMSNT->site("perfectmoney_status") != 1) {
    redirect(base_url());
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
if(0 < $CMSNT->num_rows("SELECT * FROM `payment_pm` WHERE `user_id` = '" . $getUser["id"] . "' AND `status` = 0 ")) {
    $payment_id = $CMSNT->get_row("SELECT * FROM `payment_pm` WHERE `user_id` = '" . $getUser["id"] . "' AND `status` = 0 ")["payment_id"];
} else {
    $payment_id = random("QWERTYUIOPASDFGHJKLZXCVBNM", 4) . "_" . time();
    $CMSNT->insert("payment_pm", ["user_id" => $getUser["id"], "payment_id" => $payment_id, "amount" => 0, "create_date" => gettime(), "create_time" => time(), "update_date" => gettime(), "update_time" => time(), "status" => 0]);
}
$params = ["API_URL" => "https://perfectmoney.is/api/step1.asp", "PAYMENT_ID" => $payment_id, "PAYEE_ACCOUNT" => $CMSNT->site("perfectmoney_account"), "PAYMENT_UNITS" => $CMSNT->site("perfectmoney_units"), "PAYEE_NAME" => $getUser["username"], "PAYMENT_URL" => base_url("?action=recharge-perfectmoney"), "NOPAYMENT_URL" => base_url("?action=recharge-perfectmoney"), "STATUS_URL" => base_url("api/callback-perfectmoney.php"), "SUGGESTED_MEMO" => "Payment - " . $CMSNT->site("title")];
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
$where = " `user_id` = '" . $getUser["id"] . "' AND `status` = 1  ";
$shortByDate = "";
$payment_id = "";
$time = "";
$amount = "";
$price = "";
if(!empty($_GET["payment_id"])) {
    $payment_id = check_string($_GET["payment_id"]);
    $where .= " AND `payment_id` LIKE \"%" . $payment_id . "%\" ";
}
if(!empty($_GET["amount"])) {
    $amount = check_string($_GET["amount"]);
    $where .= " AND `amount` = " . $amount . " ";
}
if(!empty($_GET["price"])) {
    $price = check_string($_GET["price"]);
    $where .= " AND `price` = " . $price . " ";
}
if(!empty($_GET["time"])) {
    $time = check_string($_GET["time"]);
    $create_date_1 = str_replace("-", "/", $time);
    $create_date_1 = explode(" to ", $create_date_1);
    if($create_date_1[0] != $create_date_1[1]) {
        $create_date_1 = [$create_date_1[0] . " 00:00:00", $create_date_1[1] . " 23:59:59"];
        $where .= " AND `create_date` >= '" . $create_date_1[0] . "' AND `create_date` <= '" . $create_date_1[1] . "' ";
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
        $where .= " AND `create_date` LIKE '%" . $currentDate . "%' ";
    }
    if($shortByDate == 2) {
        $where .= " AND YEAR(create_date) = " . $currentYear . " AND WEEK(create_date, 1) = " . $currentWeek . " ";
    }
    if($shortByDate == 3) {
        $where .= " AND MONTH(create_date) = '" . $currentMonth . "' AND YEAR(create_date) = '" . $currentYear . "' ";
    }
}
$listDatatable = $CMSNT->get_list(" SELECT * FROM `payment_pm` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `payment_pm` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url("?action=recharge-perfectmoney&limit=" . $limit . "&shortByDate=" . $shortByDate . "&time=" . $time . "&payment_id=" . $payment_id . "&amount=" . $amount . "&"), $from, $totalDatatable, $limit);
echo "\n\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-7\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-file-invoice m-2\"></i>\n                        ";
echo mb_strtoupper(__("Nạp tiền bằng Perfect Money"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <form method=\"POST\" action=\"";
echo $params["API_URL"];
echo "\" target=\"_blank\">\n                        <div class=\"text-center mb-4\">\n                            <img width=\"300px\" src=\"";
echo base_url("assets/img/PerfectMoney.png");
echo "\" />\n                        </div>\n                        <input type=\"hidden\" name=\"SUGGESTED_MEMO\" value=\"";
echo $params["SUGGESTED_MEMO"];
echo "\">\n                        <input type=\"hidden\" name=\"PAYMENT_ID\" value=\"";
echo $params["PAYMENT_ID"];
echo "\" />\n                        <input type=\"hidden\" name=\"PAYEE_ACCOUNT\" value=\"";
echo $params["PAYEE_ACCOUNT"];
echo "\" />\n                        <input type=\"hidden\" name=\"PAYMENT_UNITS\" value=\"";
echo $params["PAYMENT_UNITS"];
echo "\" />\n                        <input type=\"hidden\" name=\"PAYEE_NAME\" value=\"";
echo $params["PAYEE_NAME"];
echo "\" />\n                        <input type=\"hidden\" name=\"PAYMENT_URL\" value=\"";
echo $params["PAYMENT_URL"];
echo "\" />\n                        <input type=\"hidden\" name=\"PAYMENT_URL_METHOD\" value=\"LINK\" />\n                        <input type=\"hidden\" name=\"NOPAYMENT_URL\" value=\"";
echo $params["NOPAYMENT_URL"];
echo "\" />\n                        <input type=\"hidden\" name=\"NOPAYMENT_URL_METHOD\" value=\"LINK\" />\n                        <input type=\"hidden\" name=\"STATUS_URL\" value=\"";
echo $params["STATUS_URL"];
echo "\" />\n                        <div class=\"row mb-3\">\n                            <label class=\"col-sm-4 col-form-label\"\n                                for=\"example-hf-email\">";
echo __("Enter amount: (USD)");
echo "</label>\n                            <div class=\"col-sm-8\">\n                                <input type=\"hidden\" class=\"form-control\" id=\"token\" value=\"";
echo $getUser["token"];
echo "\">\n                                <input type=\"text\" class=\"form-control\" id=\"amount\" name=\"PAYMENT_AMOUNT\"\n                                    placeholder=\"";
echo __("Vui lòng nhập số tiền cần nạp");
echo "\" required>\n                            </div>\n                        </div>\n                        <center>\n                            <div class=\"wallet-form\">\n                                <button type=\"submit\" name=\"createPmCheckout\">";
echo __("Submit");
echo "</button>\n                            </div>\n                        </center>\n                    </form>\n                </div>\n            </div>\n            <div class=\"col-md-5\">\n                <div class=\"home-heading mb-3\">\n                    <h3>\n                        <i class=\"fa-solid fa-triangle-exclamation m-2\"></i> \n                        ";
echo mb_strtoupper(__("Lưu ý"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    ";
echo $CMSNT->site("perfectmoney_notice");
echo "                </div>\n            </div>\n            <div class=\"col-md-12\">\n                <div class=\"home-heading mb-3\">\n                    <h3>\n                        <i class=\"fa-solid fa-clock-rotate-left m-2\"></i>\n                        ";
echo mb_strtoupper(__("Lịch sử nạp Perfect Money"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <form action=\"";
echo base_url();
echo "\" method=\"GET\">\n                        <input type=\"hidden\" name=\"action\" value=\"recharge-perfectmoney\">\n                        <div class=\"row\">\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control col-sm-2 mb-1\" value=\"";
echo $payment_id;
echo "\" name=\"payment_id\"\n                                    placeholder=\"";
echo __("Mã giao dịch");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control col-sm-2 mb-1\" value=\"";
echo $amount;
echo "\" name=\"amount\"\n                                    placeholder=\"";
echo __("Số tiền gửi");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control col-sm-2 mb-1\" value=\"";
echo $price;
echo "\" name=\"price\"\n                                    placeholder=\"";
echo __("Thực nhận");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-6 col-6\">\n                                <input type=\"text\" class=\"js-flatpickr form-control mb-1\" id=\"example-flatpickr-range\"\n                                    name=\"time\" placeholder=\"";
echo __("Chọn thời gian cần tìm");
echo "\" value=\"";
echo $time;
echo "\"\n                                    data-mode=\"range\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <button class=\"shop-widget-btn mb-2\"><i\n                                        class=\"fas fa-search\"></i><span>";
echo __("Tìm kiếm");
echo "</span></button>\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <a href=\"";
echo base_url("?action=recharge-perfectmoney");
echo "\"\n                                    class=\"shop-widget-btn mb-2\"><i\n                                        class=\"far fa-trash-alt\"></i><span>";
echo __("Bỏ lọc");
echo "</span></a>\n                            </div>\n                        </div>\n                        <div class=\"top-filter\">\n                            <div class=\"filter-show\"><label class=\"filter-label\">Show :</label>\n                                <select name=\"limit\" onchange=\"this.form.submit()\" class=\"form-select filter-select\">\n                                    <option ";
echo $limit == 5 ? "selected" : "";
echo " value=\"5\">5</option>\n                                    <option ";
echo $limit == 10 ? "selected" : "";
echo " value=\"10\">10</option>\n                                    <option ";
echo $limit == 20 ? "selected" : "";
echo " value=\"20\">20</option>\n                                    <option ";
echo $limit == 50 ? "selected" : "";
echo " value=\"50\">50</option>\n                                    <option ";
echo $limit == 100 ? "selected" : "";
echo " value=\"100\">100</option>\n                                    <option ";
echo $limit == 500 ? "selected" : "";
echo " value=\"500\">500</option>\n                                    <option ";
echo $limit == 1000 ? "selected" : "";
echo " value=\"1000\">1000</option>\n                                </select>\n                            </div>\n                            <div class=\"filter-short\">\n                                <label class=\"filter-label\">";
echo __("Short by Date:");
echo "</label>\n                                <select name=\"shortByDate\" onchange=\"this.form.submit()\"\n                                    class=\"form-select filter-select\">\n                                    <option value=\"\">";
echo __("Tất cả");
echo "</option>\n                                    <option ";
echo $shortByDate == 1 ? "selected" : "";
echo " value=\"1\">";
echo __("Hôm nay");
echo "                                    </option>\n                                    <option ";
echo $shortByDate == 2 ? "selected" : "";
echo " value=\"2\">";
echo __("Tuần này");
echo "                                    </option>\n                                    <option ";
echo $shortByDate == 3 ? "selected" : "";
echo " value=\"3\">";
echo __("Tháng này");
echo "                                    </option>\n                                </select>\n                            </div>\n                        </div>\n                    </form>\n                    <div class=\"table-scroll\">\n                        <table class=\"table fs-sm mb-0\">\n                            <thead>\n                                <tr>\n                                    <th class=\"text-center\">";
echo __("Mã giao dịch");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Số tiền gửi");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Thực nhận");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Thời gian");
echo "</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
foreach ($listDatatable as $row2) {
    echo "                                <tr>\n                                    <td class=\"text-center\">\n                                        ";
    echo $row2["payment_id"];
    echo "                                    </td>\n                                    <td class=\"text-center\"><b>";
    echo $row2["amount"];
    echo " USD</b>\n                                    </td>\n                                    <td class=\"text-center\"><b\n                                            style=\"color: red;\">";
    echo format_currency($row2["price"]);
    echo "</b>\n                                    </td>\n                                    <td class=\"text-center\">";
    echo $row2["create_date"];
    echo "</td>\n                                </tr>\n                                ";
}
echo "                            </tbody>\n                            <tfoot>\n                                <tr>\n                                    <td colspan=\"7\">\n                                        <div class=\"float-right\">\n                                            ";
echo __("Đã thanh toán:");
echo "                                            <strong\n                                                style=\"color:red;\">";
echo format_currency($CMSNT->get_row(" SELECT SUM(`price`) FROM `payment_pm` WHERE " . $where . "   ")["SUM(`price`)"]);
echo "</strong>\n\n                                    </td>\n                                </tr>\n                            </tfoot>\n                        </table>\n                    </div>\n                    <div class=\"bottom-paginate\">\n                        <p class=\"page-info\">Showing ";
echo $limit;
echo " of ";
echo $totalDatatable;
echo " Results</p>\n                        <div class=\"pagination\">\n                            ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n\n<script>\nDashmix.helpersOnLoad(['js-flatpickr', 'jq-datepicker', 'jq-maxlength', 'jq-select2', 'jq-rangeslider',\n    'jq-masked-inputs', 'jq-pw-strength'\n]);\n</script>";

?>