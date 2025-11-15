<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Nạp tiền bằng thẻ cào") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n\n\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_user.php";
if($CMSNT->site("card_status") != 1) {
    redirect(base_url("client/home"));
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
$pin = "";
$time = "";
$serial = "";
$status = "";
if(!empty($_GET["status"])) {
    $status = check_string($_GET["status"]);
    $where .= " AND `status` = \"" . $status . "\" ";
}
if(!empty($_GET["pin"])) {
    $pin = check_string($_GET["pin"]);
    $where .= " AND `pin` LIKE \"%" . $pin . "%\" ";
}
if(!empty($_GET["serial"])) {
    $serial = check_string($_GET["serial"]);
    $where .= " AND `serial` LIKE \"%" . $serial . "%\" ";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `cards` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `cards` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination_client(base_url("?action=recharge-card&limit=" . $limit . "&shortByDate=" . $shortByDate . "&time=" . $time . "&pin=" . $pin . "&serial=" . $serial . "&status=" . $status . "&"), $from, $totalDatatable, $limit);
echo "\n\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-7\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-paper-plane m-2\"></i>\n                        ";
echo mb_strtoupper(__("Nạp tiền bằng thẻ cào tự động"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <div class=\"form-group row\">\n                        <label class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
echo __("Loại thẻ");
echo "</label>\n                        <div class=\"col-lg-8 fv-row\">\n                            <select class=\"form-control\" id=\"telco\">\n                                <option value=\"\">-- ";
echo __("Chọn loại thẻ");
echo " --</option>\n                                <option value=\"VIETTEL\">Viettel</option>\n                                <option value=\"VINAPHONE\">Vinaphone</option>\n                                <option value=\"MOBIFONE\">Mobifone</option>\n                                <option value=\"VNMOBI\">Vietnamobile</option>\n                                <option value=\"ZING\">Zing</option>\n                                <option value=\"VCOIN\">Vcoin</option>\n                                <option value=\"GARENA\">Garena (chỉ nhận thẻ trên 10k)</option>\n                            </select>\n                        </div>\n                    </div>\n                    <div class=\"form-group row\">\n                        <label class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
echo __("Mệnh giá");
echo "</label>\n                        <div class=\"col-lg-8 fv-row\">\n                            <select class=\"form-control\" onchange=\"totalPrice()\" id=\"amount\">\n                                <option value=\"\">-- ";
echo __("Chọn mệnh giá");
echo " --</option>\n                                <option value=\"10000\">10.000đ</option>\n                                <option value=\"20000\">20.000đ</option>\n                                <option value=\"30000\">30.000đ</option>\n                                <option value=\"50000\">50.000đ</option>\n                                <option value=\"100000\">100.000đ</option>\n                                <option value=\"200000\">200.000đ</option>\n                                <!--<option value=\"300000\">300.000đ</option>-->\n                                <option value=\"500000\">500.000đ</option>\n                                <option value=\"1000000\">1.000.000đ</option>\n                                <option value=\"2000000\">2.000.000đ</option>\n                            </select>\n                        </div>\n                    </div>\n                    <div class=\"form-group row\">\n                        <label class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
echo __("Serial");
echo "</label>\n                        <div class=\"col-lg-8 fv-row\">\n                            <input type=\"text\" id=\"serial\" class=\"form-control\"\n                                placeholder=\"";
echo __("Nhập serial thẻ");
echo "\" />\n                        </div>\n                    </div>\n                    <div class=\"form-group row\">\n                        <label class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
echo __("Pin");
echo "</label>\n                        <div class=\"col-lg-8 fv-row\">\n                            <input type=\"text\" id=\"pin\" class=\"form-control\" placeholder=\"";
echo __("Nhập mã thẻ");
echo "\" />\n                            <input type=\"hidden\" id=\"token\" class=\"form-control\" value=\"";
echo $getUser["token"];
echo "\" />\n                        </div>\n                    </div>\n                    <div class=\"form-group text-center\">\n                        <div class=\"alert bg-white alert-info\" role=\"alert\">\n                            <div class=\"iq-alert-icon\">\n                                <i class=\"ri-alert-line\"></i>\n                            </div>\n                            <div class=\"iq-alert-text\">";
echo __("Số tiền thực nhận");
echo ": <b id=\"ketqua\"\n                                    style=\"color: red;\">0</b></div>\n                        </div>\n                    </div>\n                    <center>\n                        <div class=\"wallet-form\">\n                            <button type=\"button\" id=\"submit\">";
echo __("NẠP NGAY");
echo "</button>\n                        </div>\n                    </center>\n                </div>\n            </div>\n            <div class=\"col-md-5\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-triangle-exclamation m-2\"></i> ";
echo mb_strtoupper(__("Lưu ý"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card p-3\">\n                    ";
echo $CMSNT->site("card_notice");
echo "                </div>\n            </div>\n            <div class=\"col-md-12\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-clock-rotate-left m-2\"></i> ";
echo mb_strtoupper(__("Lịch sử nạp thẻ"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <form action=\"";
echo base_url();
echo "\" method=\"GET\" class=\"mb-3\">\n                        <input type=\"hidden\" name=\"action\" value=\"recharge-card\">\n                        <div class=\"row\">\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control col-sm-2 mb-2\" value=\"";
echo $pin;
echo "\" name=\"pin\"\n                                    placeholder=\"";
echo __("Pin");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control col-sm-2 mb-2\" value=\"";
echo $serial;
echo "\" name=\"serial\"\n                                    placeholder=\"";
echo __("Serial");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <select class=\"form-select mb-2\" name=\"status\">\n                                    <option value=\"\">";
echo __("Trạng thái");
echo "</option>\n                                    <option ";
echo $status == "pending" ? "selected" : "";
echo " value=\"pending\">\n                                        ";
echo __("Đang chờ xử lý");
echo "</option>\n                                    <option ";
echo $status == "error" ? "selected" : "";
echo " value=\"error\">\n                                        ";
echo __("Thẻ lỗi");
echo "</option>\n                                    <option ";
echo $status == "completed" ? "selected" : "";
echo " value=\"completed\">\n                                        ";
echo __("Thành công");
echo "</option>\n                                </select>\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input type=\"text\" class=\"js-flatpickr form-control mb-2\" id=\"example-flatpickr-range\"\n                                    name=\"time\" placeholder=\"";
echo __("Chọn thời gian cần tìm");
echo "\" value=\"";
echo $time;
echo "\"\n                                    data-mode=\"range\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <button class=\"shop-widget-btn mb-2\"><i\n                                        class=\"fas fa-search\"></i><span>";
echo __("Tìm kiếm");
echo "</span></button>\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <a href=\"";
echo base_url("?action=recharge-card");
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
echo __("Nhà mạng");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Serial");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Pin");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Mệnh giá");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Thực nhận");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Trạng thái");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Create date");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Update date");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Lý do");
echo "</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
foreach ($listDatatable as $row2) {
    echo "                                <tr>\n                                    <td class=\"text-center\">";
    echo $row2["telco"];
    echo "</td>\n                                    <td class=\"text-center\">";
    echo $row2["serial"];
    echo "</td>\n                                    <td class=\"text-center\">";
    echo $row2["pin"];
    echo "</td>\n                                    <td class=\"text-right\"><b\n                                            style=\"color: red;\">";
    echo format_currency($row2["amount"]);
    echo "</b></td>\n                                    <td class=\"text-right\"><b\n                                            style=\"color: green;\">";
    echo format_currency($row2["price"]);
    echo "</b></td>\n                                    <td class=\"text-center\">";
    echo display_card($row2["status"]);
    echo "</td>\n                                    <td>";
    echo $row2["create_date"];
    echo "</td>\n                                    <td>";
    echo $row2["update_date"];
    echo "</td>\n                                    <td>";
    echo $row2["reason"];
    echo "</td>\n                                </tr>\n                                ";
}
echo "                            </tbody>\n                            <tfoot>\n                                <tr>\n                                    <td colspan=\"7\">\n                                        <div class=\"float-right\">\n                                            ";
echo __("Đã thanh toán:");
echo "                                            <strong\n                                                style=\"color:red;\">";
echo format_currency($CMSNT->get_row(" SELECT SUM(`received`) FROM `payment_crypto` WHERE " . $where . " AND `status` = 'completed' ")["SUM(`received`)"]);
echo "</strong>\n                                            | ";
echo __("Chưa thanh toán:");
echo "                                            <strong\n                                                style=\"color:blue;\">";
echo format_currency($CMSNT->get_row(" SELECT SUM(`received`) FROM `payment_crypto` WHERE " . $where . " AND `status` = 'waiting' ")["SUM(`received`)"]);
echo "</strong>\n                                        </div>\n                                    </td>\n                                </tr>\n                            </tfoot>\n                        </table>\n                    </div>\n\n                    <div class=\"bottom-paginate\">\n                        <p class=\"page-info\">Showing ";
echo $limit;
echo " of ";
echo $totalDatatable;
echo " Results</p>\n                        <div class=\"pagination\">\n                            ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n\n\n<script>\nfunction totalPrice() {\n    var total = 0;\n    var amount = \$(\"#amount\").val();\n    total = amount - amount * ";
echo $CMSNT->site("card_ck");
echo " / 100;\n    \$('#ketqua').html(total.toString().replace(/(.)(?=(\\d{3})+\$)/g, '\$1.'));\n}\n</script>\n<script type=\"text/javascript\">\n\$(\"#submit\").on(\"click\", function() {\n    \$('#submit').html('<i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "').prop(\n        'disabled',\n        true);\n    \$.ajax({\n        url: \"";
echo base_url("ajaxs/client/create.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'nap_the',\n            token: \$(\"#token\").val(),\n            serial: \$('#serial').val(),\n            pin: \$('#pin').val(),\n            telco: \$('#telco').val(),\n            amount: \$('#amount').val()\n        },\n        success: function(respone) {\n            if (respone.status == 'success') {\n                Swal.fire({\n                    title: '";
echo __("Successful !");
echo "',\n                    text: respone.msg,\n                    icon: 'success',\n                    confirmButtonColor: '#3085d6',\n                    confirmButtonText: 'OK'\n                }).then((result) => {\n                    if (result.isConfirmed) {\n                        location.reload();\n                    }\n                });\n            } else {\n                Swal.fire('";
echo __("Failure!");
echo "', respone.msg, 'error');\n            }\n            \$('#submit').html(\n                    '";
echo __("NẠP NGAY");
echo "')\n                .prop('disabled', false);\n        },\n        error: function() {\n            Swal.fire('";
echo __("Failure!");
echo "', 'Không thể xử lý', 'error');\n            \$('#submit').html(\n                    '";
echo __("NẠP NGAY");
echo "')\n                .prop('disabled', false);\n        }\n\n    });\n});\n</script>";

?>