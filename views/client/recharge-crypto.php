<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Nạp tiền bằng Crypto") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n\n\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_user.php";
if($CMSNT->site("crypto_status") != 1) {
    redirect(base_url("client/recharge"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
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
$where = " `user_id` = '" . $getUser["id"] . "'  ";
$shortByDate = "";
$trans_id = "";
$time = "";
$amount = "";
$status = "";
if(!empty($_GET["status"])) {
    $status = check_string($_GET["status"]);
    $where .= " AND `status` = \"" . $status . "\" ";
}
if(!empty($_GET["trans_id"])) {
    $trans_id = check_string($_GET["trans_id"]);
    $where .= " AND `trans_id` LIKE \"%" . $trans_id . "%\" ";
}
if(!empty($_GET["amount"])) {
    $amount = check_string($_GET["amount"]);
    $where .= " AND `amount` = " . $amount . " ";
}
if(!empty($_GET["time"])) {
    $time = check_string($_GET["time"]);
    $create_date_1 = str_replace("-", "/", $time);
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `payment_crypto` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `payment_crypto` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination_client(base_url("?action=recharge-crypto&limit=" . $limit . "&shortByDate=" . $shortByDate . "&time=" . $time . "&trans_id=" . $trans_id . "&amount=" . $amount . "&status=" . $status . "&"), $from, $totalDatatable, $limit);
echo "\n\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n        ";
if($CMSNT->num_rows(" SELECT * FROM `promotions` ") != 0) {
    echo "            <div class=\"col-lg-12\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-percent m-2\"></i> ";
    echo mb_strtoupper(__("Khuyến mãi"));
    echo "                    </h3>\n                </div>\n                <div class=\"account-card p-0\">\n                    <table class=\"table fs-sm mb-0\">\n                        <thead>\n                            <tr>\n                                <th scope=\"col\">";
    echo __("Số tiền nạp lớn hơn hoặc bằng");
    echo "</th>\n                                <th scope=\"col\">";
    echo __("Khuyến mãi thêm");
    echo "</th>\n                            </tr>\n                        </thead>\n                        <tbody>\n                            ";
    $i = 1;
    foreach ($CMSNT->get_list(" SELECT * FROM `promotions` ORDER BY `min` DESC ") as $promotion) {
        echo "                            <tr>\n                                <td><b style=\"color: blue;\">";
        echo format_currency($promotion["min"]);
        echo "</b></td>\n                                <td><b style=\"color: red;\">";
        echo $promotion["discount"];
        echo "%</b></td>\n                            </tr>\n                            ";
    }
    echo "                        </tbody>\n                    </table>\n                </div>\n            </div>\n            ";
}
echo "            <div class=\"col-md-7\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-receipt m-2\"></i>\n                        ";
echo mb_strtoupper(__("Nạp tiền bằng Crypto"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <div class=\"text-center mb-4\">\n                        <img width=\"200px\" src=\"";
echo base_url("assets/img/usdttrc20.png");
echo "\" />\n                    </div>\n                    <div class=\"row mb-3\">\n                        <label class=\"col-sm-4 col-form-label\"\n                            for=\"example-hf-email\">";
echo __("Enter amount: (USDT)");
echo "</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"hidden\" class=\"form-control\" id=\"token\" value=\"";
echo $getUser["token"];
echo "\">\n                            <input type=\"text\" class=\"form-control\" id=\"amount\"\n                                placeholder=\"";
echo __("Vui lòng nhập số tiền cần nạp");
echo "\">\n                        </div>\n                    </div>\n                    <center>\n                        <div class=\"wallet-form\">\n                            <button type=\"button\" id=\"CreateInvoiceCrypto\">";
echo __("Submit");
echo "</button>\n                        </div>\n                    </center>\n                </div>\n            </div>\n            <div class=\"col-md-5\">\n                <div class=\"home-heading mb-3\">\n                    <h3>\n                        <i class=\"fa-solid fa-triangle-exclamation m-2\"></i> \n                        ";
echo mb_strtoupper(__("Lưu ý"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    ";
echo $CMSNT->site("crypto_note");
echo "                </div>\n            </div>\n            <div class=\"col-md-12\">\n                <div class=\"home-heading mb-3\">\n                    <h3>\n                        <i class=\"fa-solid fa-clock-rotate-left m-2\"></i>\n                        ";
echo mb_strtoupper(__("Lịch sử nạp Crypto"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <form action=\"\" method=\"GET\">\n                        <input type=\"hidden\" name=\"action\" value=\"recharge-crypto\">\n                        <div class=\"row\">\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control col-sm-2 mb-2\" value=\"";
echo $trans_id;
echo "\" name=\"trans_id\"\n                                    placeholder=\"";
echo __("Mã giao dịch");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control col-sm-2 mb-2\" value=\"";
echo $amount;
echo "\" name=\"amount\"\n                                    placeholder=\"";
echo __("Số lượng");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <select class=\"form-select mb-2\" name=\"status\">\n                                    <option value=\"\">";
echo __("Trạng thái");
echo "</option>\n                                    <option ";
echo $status == "waiting" ? "selected" : "";
echo " value=\"waiting\">\n                                        ";
echo __("Waiting");
echo "</option>\n                                    <option ";
echo $status == "expired" ? "selected" : "";
echo " value=\"expired\">\n                                        ";
echo __("Expired");
echo "</option>\n                                    <option ";
echo $status == "completed" ? "selected" : "";
echo " value=\"completed\">\n                                        ";
echo __("Completed");
echo "</option>\n                                </select>\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input type=\"text\" class=\"js-flatpickr form-control mb-2\" id=\"example-flatpickr-range\"\n                                    name=\"time\" placeholder=\"";
echo __("Chọn thời gian cần tìm");
echo "\" value=\"";
echo $time;
echo "\"\n                                    data-mode=\"range\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <button class=\"shop-widget-btn mb-2\"><i\n                                        class=\"fas fa-search\"></i><span>";
echo __("Tìm kiếm");
echo "</span></button>\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <a href=\"";
echo base_url("?action=recharge-crypto");
echo "\" class=\"shop-widget-btn mb-2\"><i\n                                        class=\"far fa-trash-alt\"></i><span>";
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
echo __("Số lượng");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Thực nhận");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Trạng thái");
echo "</th>\n                                    <th>";
echo __("Thời gian tạo");
echo "</th>\n                                    <th>";
echo __("Cập nhật");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Thao tác");
echo "</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
foreach ($listDatatable as $row2) {
    echo "                                <tr>\n                                    <td class=\"text-center\"><small><a target=\"_blank\"\n                                                href=\"";
    echo $row2["url_payment"];
    echo "\">";
    echo $row2["trans_id"];
    echo "</a></small>\n                                    </td>\n                                    <td style=\"text-align: right;\"><b>";
    echo $row2["amount"];
    echo "</b>\n                                        <b style=\"color:green;\">USDT</b>\n                                    </td>\n                                    <td style=\"text-align: right;\"><b\n                                            style=\"color: red;\">";
    echo format_currency($row2["received"]);
    echo "</b>\n                                    </td>\n                                    <td class=\"text-center\">";
    echo display_invoice($row2["status"]);
    echo "</td>\n                                    <td>";
    echo $row2["create_gettime"];
    echo "</td>\n                                    <td>";
    echo $row2["update_gettime"];
    echo "</td>\n                                    <td class=\"text-center fs-base\">\n                                        <a type=\"button\" target=\"_blank\" href=\"";
    echo $row2["url_payment"];
    echo "\">\n                                            ";
    echo __("Xem thêm");
    echo "                                        </a>\n                                    </td>\n                                </tr>\n                                ";
}
echo "                            </tbody>\n                            <tfoot>\n                                <tr>\n                                    <td colspan=\"7\">\n                                        <div class=\"float-right\">\n                                            ";
echo __("Đã thanh toán:");
echo "                                            <strong\n                                                style=\"color:red;\">";
echo format_currency($CMSNT->get_row(" SELECT SUM(`received`) FROM `payment_crypto` WHERE " . $where . " AND `status` = 'completed' ")["SUM(`received`)"]);
echo "</strong>\n                                            | ";
echo __("Chưa thanh toán:");
echo "                                            <strong\n                                                style=\"color:blue;\">";
echo format_currency($CMSNT->get_row(" SELECT SUM(`received`) FROM `payment_crypto` WHERE " . $where . " AND `status` = 'waiting' ")["SUM(`received`)"]);
echo "</strong>\n                                        </div>\n                                    </td>\n                                </tr>\n                            </tfoot>\n                        </table>\n                    </div>\n                    <div class=\"bottom-paginate\">\n                        <p class=\"page-info\">Showing ";
echo $limit;
echo " of ";
echo $totalDatatable;
echo " Results</p>\n                        <div class=\"pagination\">\n                            ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n\n<script type=\"text/javascript\">\n\$(\"#CreateInvoiceCrypto\").on(\"click\", function() {\n    \$('#CreateInvoiceCrypto').html('<i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "').prop(\n        'disabled',\n        true);\n    \$.ajax({\n        url: \"";
echo base_url("ajaxs/client/create.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'CreateInvoiceCrypto',\n            token: \$(\"#token\").val(),\n            amount: \$(\"#amount\").val()\n        },\n        success: function(respone) {\n            if (respone.status == 'success') {\n                Swal.fire({\n                    title: '";
echo __("Successful !");
echo "',\n                    text: respone.msg,\n                    icon: 'success',\n                    confirmButtonColor: '#3085d6',\n                    confirmButtonText: 'OK'\n                }).then((result) => {\n                    if (result.isConfirmed) {\n                        location.reload();\n                    }\n                });\n                setTimeout(window.open(respone.url), 2000);\n            } else {\n                Swal.fire('";
echo __("Failure!");
echo "', respone.msg, 'error');\n            }\n            \$('#CreateInvoiceCrypto').html(\n                    '";
echo __("Submit");
echo "')\n                .prop('disabled', false);\n        },\n        error: function() {\n            Swal.fire('";
echo __("Failure!");
echo "', 'Không thể xử lý', 'error');\n            \$('#CreateInvoiceCrypto').html(\n                    '";
echo __("Submit");
echo "')\n                .prop('disabled', false);\n        }\n\n    });\n});\n</script>";

?>