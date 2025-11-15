<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Recharge Flutterwave") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_user.php";
if($CMSNT->site("flutterwave_status") != 1) {
    redirect(base_url());
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
if(0 < $CMSNT->num_rows("SELECT * FROM `payment_flutterwave` WHERE `user_id` = '" . $getUser["id"] . "' AND `status` = 'pending' ")) {
    $tx_ref = $CMSNT->get_row("SELECT * FROM `payment_flutterwave` WHERE `user_id` = '" . $getUser["id"] . "' AND `status` = 'pending' ")["tx_ref"];
} else {
    $tx_ref = md5(random("QWERTYUIOPASDFGHJKLZXCVBNM", 4) . "_" . time());
    $CMSNT->insert("payment_flutterwave", ["user_id" => $getUser["id"], "tx_ref" => $tx_ref, "amount" => 0, "currency" => $CMSNT->site("flutterwave_currency_code"), "create_gettime" => gettime(), "update_gettime" => gettime(), "status" => "pending"]);
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
$where = " `user_id` = '" . $getUser["id"] . "' AND `status` = 'success' ";
$shortByDate = "";
$trans_id = "";
$time = "";
$amount = "";
$billName = "";
if(!empty($_GET["trans_id"])) {
    $trans_id = check_string($_GET["trans_id"]);
    $where .= " AND `tx_ref` = \"" . $trans_id . "\" ";
}
if(!empty($_GET["amount"])) {
    $amount = check_string($_GET["amount"]);
    $where .= " AND `amount` = " . $amount . " ";
}
if(!empty($_GET["time"])) {
    $time = check_string($_GET["time"]);
    $create_gettime_1 = str_replace("-", "/", $time);
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `payment_flutterwave` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `payment_flutterwave` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url("?action=recharge-flutterwave&limit=" . $limit . "&shortByDate=" . $shortByDate . "&time=" . $time . "&trans_id=" . $trans_id . "&amount=" . $amount . "&"), $from, $totalDatatable, $limit);
echo "\n\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-7\">\n                <div class=\"account-card\">\n                    <h4 class=\"account-title\">";
echo __("Recharge Flutterwave");
echo "</h4>\n                    <div class=\"text-center mb-4\">\n                        <img width=\"300px\" src=\"";
echo base_url("mod/img/logo-flutterwave.png");
echo "\" />\n                    </div>\n                    <form method=\"POST\" action=\"https://checkout.flutterwave.com/v3/hosted/pay\">\n                    <div class=\"row mb-3\">\n                        <label class=\"col-sm-4 col-form-label\"\n                            for=\"example-hf-email\">";
echo __("Enter the deposit amount: (" . $CMSNT->site("flutterwave_currency_code") . ")");
echo "</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"hidden\" name=\"public_key\" value=\"";
echo $CMSNT->site("flutterwave_publicKey");
echo "\" />\n                            <input type=\"hidden\" name=\"customer[email]\" value=\"";
echo $getUser["email"];
echo "\" />\n                            <input type=\"hidden\" name=\"customer[name]\" value=\"";
echo $getUser["username"];
echo "\" />\n                            <input type=\"hidden\" name=\"tx_ref\" value=\"";
echo $tx_ref;
echo "\" />\n                            <input type=\"text\" class=\"form-control\" name=\"amount\"\n                                placeholder=\"";
echo __("Please enter the amount to deposit");
echo "\" required>\n                            \n                            <input type=\"hidden\" name=\"currency\" value=\"";
echo $CMSNT->site("flutterwave_currency_code");
echo "\" />\n                            <input type=\"hidden\" name=\"meta[token]\" value=\"";
echo $tx_ref;
echo "\" />\n                            <input type=\"hidden\" name=\"redirect_url\" value=\"";
echo base_url("?action=recharge-flutterwave");
echo "\" />\n                        </div>\n                    </div>\n                    <center>\n                        <div class=\"wallet-form\">\n                            <button type=\"submit\" id=\"start-payment-button\">";
echo __("Submit");
echo "</button>\n                        </div>\n                    </center>\n                    </form>\n                </div>\n            </div>\n            <div class=\"col-md-5\">\n                <div class=\"account-card\">\n                    <h4 class=\"account-title\">";
echo __("Lưu ý");
echo "</h4>\n                    ";
echo $CMSNT->site("flutterwave_notice");
echo "                </div>\n            </div>\n            <div class=\"col-md-12\">\n                <div class=\"account-card\">\n                    <h4 class=\"account-title\">";
echo __("Lịch sử nạp Flutterwave");
echo "</h4>\n                    <form action=\"";
echo base_url();
echo "\" method=\"GET\">\n                        <input type=\"hidden\" name=\"action\" value=\"recharge-flutterwave\">\n                        <div class=\"row\">\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control col-sm-2 mb-1\" value=\"";
echo $trans_id;
echo "\"\n                                    name=\"trans_id\" placeholder=\"";
echo __("Search transaction ref");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control col-sm-2 mb-1\" value=\"";
echo $amount;
echo "\" name=\"amount\"\n                                    placeholder=\"";
echo __("Search amount");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-6 col-6\">\n                                <input type=\"text\" class=\"js-flatpickr form-control mb-1\" id=\"example-flatpickr-range\"\n                                    name=\"time\" placeholder=\"";
echo __("Chọn thời gian cần tìm");
echo "\" value=\"";
echo $time;
echo "\"\n                                    data-mode=\"range\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <button class=\"shop-widget-btn mb-2\"><i\n                                        class=\"fas fa-search\"></i><span>";
echo __("Tìm kiếm");
echo "</span></button>\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <a href=\"";
echo base_url("?action=recharge-flutterwave");
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
echo __("TransID");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Amount");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Price");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Create date");
echo "</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
foreach ($listDatatable as $row) {
    echo "                                <tr>\n                                    <td class=\"text-center\"><b>";
    echo $row["tx_ref"];
    echo "</b></td>\n                                    <td class=\"text-center\"><b>";
    echo $row["amount"];
    echo "</b></td>\n                                    <td class=\"text-center\"><b>";
    echo format_currency($row["price"]);
    echo "</b></td>\n                                    <td class=\"text-center\">";
    echo $row["create_gettime"];
    echo "</td>\n                                </tr>\n                                ";
}
echo "                            </tbody>\n                        </table>\n                    </div>\n                    ";
if($totalDatatable == 0) {
    echo "                    <div class=\"empty-state\">\n                        <svg width=\"184\" height=\"152\" viewBox=\"0 0 184 152\" xmlns=\"http://www.w3.org/2000/svg\">\n                            <g fill=\"none\" fill-rule=\"evenodd\">\n                                <g transform=\"translate(24 31.67)\">\n                                    <ellipse fill-opacity=\".8\" fill=\"#F5F5F7\" cx=\"67.797\" cy=\"106.89\" rx=\"67.797\"\n                                        ry=\"12.668\"></ellipse>\n                                    <path\n                                        d=\"M122.034 69.674L98.109 40.229c-1.148-1.386-2.826-2.225-4.593-2.225h-51.44c-1.766 0-3.444.839-4.592 2.225L13.56 69.674v15.383h108.475V69.674z\"\n                                        fill=\"#AEB8C2\"></path>\n                                    <path\n                                        d=\"M101.537 86.214L80.63 61.102c-1.001-1.207-2.507-1.867-4.048-1.867H31.724c-1.54 0-3.047.66-4.048 1.867L6.769 86.214v13.792h94.768V86.214z\"\n                                        fill=\"url(#linearGradient-1)\" transform=\"translate(13.56)\"></path>\n                                    <path\n                                        d=\"M33.83 0h67.933a4 4 0 0 1 4 4v93.344a4 4 0 0 1-4 4H33.83a4 4 0 0 1-4-4V4a4 4 0 0 1 4-4z\"\n                                        fill=\"#F5F5F7\"></path>\n                                    <path\n                                        d=\"M42.678 9.953h50.237a2 2 0 0 1 2 2V36.91a2 2 0 0 1-2 2H42.678a2 2 0 0 1-2-2V11.953a2 2 0 0 1 2-2zM42.94 49.767h49.713a2.262 2.262 0 1 1 0 4.524H42.94a2.262 2.262 0 0 1 0-4.524zM42.94 61.53h49.713a2.262 2.262 0 1 1 0 4.525H42.94a2.262 2.262 0 0 1 0-4.525zM121.813 105.032c-.775 3.071-3.497 5.36-6.735 5.36H20.515c-3.238 0-5.96-2.29-6.734-5.36a7.309 7.309 0 0 1-.222-1.79V69.675h26.318c2.907 0 5.25 2.448 5.25 5.42v.04c0 2.971 2.37 5.37 5.277 5.37h34.785c2.907 0 5.277-2.421 5.277-5.393V75.1c0-2.972 2.343-5.426 5.25-5.426h26.318v33.569c0 .617-.077 1.216-.221 1.789z\"\n                                        fill=\"#DCE0E6\"></path>\n                                </g>\n                                <path\n                                    d=\"M149.121 33.292l-6.83 2.65a1 1 0 0 1-1.317-1.23l1.937-6.207c-2.589-2.944-4.109-6.534-4.109-10.408C138.802 8.102 148.92 0 161.402 0 173.881 0 184 8.102 184 18.097c0 9.995-10.118 18.097-22.599 18.097-4.528 0-8.744-1.066-12.28-2.902z\"\n                                    fill=\"#DCE0E6\"></path>\n                                <g transform=\"translate(149.65 15.383)\" fill=\"#FFF\">\n                                    <ellipse cx=\"20.654\" cy=\"3.167\" rx=\"2.849\" ry=\"2.815\"></ellipse>\n                                    <path d=\"M5.698 5.63H0L2.898.704zM9.259.704h4.985V5.63H9.259z\"></path>\n                                </g>\n                            </g>\n                        </svg>\n                        <p>";
    echo __("Không có dữ liệu");
    echo "</p>\n                    </div>\n                    ";
}
echo "                    <div class=\"bottom-paginate\">\n                        <p class=\"page-info\">Showing ";
echo $limit;
echo " of ";
echo $totalDatatable;
echo " Results</p>\n                        <div class=\"pagination\">\n                            ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n\n<script src=\"https://checkout.squadco.com/widget/squad.min.js\"></script>\n\n<script>\nfunction SquadPay() {\n\n    const squadInstance = new squad({\n        onClose: () => console.log(\"Widget closed\"),\n        onLoad: () => console.log(\"Widget loaded successfully\"),\n        onSuccess: () => console.log(`Linked successfully`),\n        key: \"";
echo $CMSNT->site("squadco_Public_Key");
echo "\",\n        //Change key (test_pk_sample-public-key-1) to the key on your Squad Dashboard\n        email: document.getElementById(\"email\").value,\n        transaction_ref: document.getElementById(\"transaction_ref\").value,\n        currency_code: document.getElementById(\"currency_code\").value,\n        amount: document.getElementById(\"amount\").value * 100\n    });\n    squadInstance.setup();\n    squadInstance.open();\n\n}\n</script>\n\n<script>\nDashmix.helpersOnLoad(['js-flatpickr', 'jq-datepicker', 'jq-maxlength', 'jq-select2', 'jq-rangeslider',\n    'jq-masked-inputs', 'jq-pw-strength'\n]);\n</script>";

?>