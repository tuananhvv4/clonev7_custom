<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Nạp tiền") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_user.php";
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
$where = " `user_id` = '" . $getUser["id"] . "' ";
$shortByDate = "";
$description = "";
$tid = "";
$time = "";
if(!empty($_GET["tid"])) {
    $tid = check_string($_GET["tid"]);
    $where .= " AND `tid` = \"" . $tid . "\" ";
}
if(!empty($_GET["description"])) {
    $description = check_string($_GET["description"]);
    $where .= " AND `description` LIKE \"%" . $description . "%\" ";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `payment_bank` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination_client(base_url("?action=recharge-bank&limit=" . $limit . "&shortByDate=" . $shortByDate . "&time=" . $time . "&tid=" . $tid . "&description=" . $description . "&"), $from, $totalDatatable, $limit);
echo "\n\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            ";
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
echo "            <div class=\"col-lg-7\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-triangle-exclamation m-2\"></i> ";
echo mb_strtoupper(__("Lưu ý nạp tiền"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card p-3\">\n                    ";
echo $CMSNT->site("bank_notice");
echo "                </div>\n            </div>\n            <div class=\"col-lg-5\">\n                <div class=\"row\">\n                    <div class=\"col-lg-12\">\n                        <style>\n                            .tab-link {\n                                font-size: 22px;\n                            }\n                            .nav-tabs li {\n                                padding: -3px 30px;\n                            }\n                            .nav-tabs li .active{\n                                padding: 2px 30px;\n                                border: 1px solid #ccc;\n                            }\n                        </style>\n                        <ul class=\"nav nav-tabs\">\n                            ";
$i = 0;
foreach ($CMSNT->get_list("SELECT * FROM `banks` WHERE `status` = 1 ") as $bank) {
    echo "                            <li><a href=\"#tab-";
    echo $bank["id"];
    echo "\" class=\"tab-link ";
    echo $i == 0 ? "active" : "";
    echo "\"\n                                    data-bs-toggle=\"tab\">";
    echo $bank["short_name"];
    echo "</a></li>\n                            ";
    $i++;
}
echo "                        </ul>\n                    </div>\n                </div>\n                ";
$i = 0;
foreach ($CMSNT->get_list("SELECT * FROM `banks` WHERE `status` = 1 ") as $bank) {
    echo "                <div class=\"tab-pane fade ";
    echo $i == 0 ? "active show" : "";
    echo "\" id=\"tab-";
    echo $bank["id"];
    echo "\">\n                    <div class=\"account-card\">\n                        <center class=\"py-3\">\n                            ";
    if($bank["short_name"] == "MOMO") {
        echo "                            ";
        echo file_get_contents("https://api.web2m.com/api/qrmomo.php?amount=0&phone=" . $bank["accountNumber"] . "&noidung=" . urlencode($CMSNT->site("prefix_autobank")) . $getUser["id"] . "&size=300");
        echo "                            ";
    } else {
        echo "                            ";
        $img1 = "https://api.vietqr.io/" . $bank["short_name"] . "/" . $bank["accountNumber"] . "/0/" . $CMSNT->site("prefix_autobank") . $getUser["id"] . "/vietqr_net_2.jpg?accountName=" . $bank["accountName"];
        $img = $img1;
        $is_img = file_get_contents($img1);
        echo "                            ";
        if($is_img != "invalid acqId") {
            echo "                            <img src=\"";
            echo $img;
            echo "\" width=\"300px\" />\n                            ";
        } else {
            echo "                            <img class=\"mb-3\" src=\"";
            echo base_url($bank["image"]);
            echo "\" width=\"100%\">\n                            ";
        }
        echo "                            ";
    }
    echo "                        </center>\n                        <ul class=\"list-group\">\n                            <li class=\"list-group-item\">";
    echo __("Số tài khoản:");
    echo " <b id=\"copySTK";
    echo $bank["id"];
    echo "\"\n                                    style=\"color: green;\">";
    echo $bank["accountNumber"];
    echo "</b> <button onclick=\"copy()\"\n                                    class=\"copy\" data-clipboard-target=\"#copySTK";
    echo $bank["id"];
    echo "\"><i\n                                        class=\"fas fa-copy\"></i></button>\n                            </li>\n                            <li class=\"list-group-item\" style=\"font-size:17px;\">";
    echo __("Nội dung chuyển khoản:");
    echo " <b\n                                    id=\"copyNoiDung";
    echo $bank["id"];
    echo "\"\n                                    style=\"color: red;\">";
    echo $CMSNT->site("prefix_autobank") . $getUser["id"];
    echo "</b>\n                                <button onclick=\"copy()\" class=\"copy\"\n                                    data-clipboard-target=\"#copyNoiDung";
    echo $bank["id"];
    echo "\"><i\n                                        class=\"fas fa-copy\"></i></button>\n\n                            </li>\n                            <li class=\"list-group-item\">";
    echo __("Chủ tài khoản:");
    echo "                                <b>";
    echo $bank["accountName"];
    echo "</b>\n                            </li>\n                            <li class=\"list-group-item\">";
    echo __("Ngân hàng:");
    echo "                                <b>";
    echo $bank["short_name"];
    echo "</b>\n                            </li>\n                        </ul>\n                        <center><small>";
    echo __("Nhập đúng nội dung chuyển khoản để hệ thống cộng tiền tự động...");
    echo "</small></center>\n                    </div>\n                </div>\n                ";
    $i++;
}
echo "            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-lg-12\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-clock-rotate-left m-2\"></i> ";
echo mb_strtoupper(__("Lịch sử nạp tiền"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <form action=\"\" method=\"GET\" class=\"mb-3\">\n                        <input type=\"hidden\" name=\"action\" value=\"recharge-bank\">\n                        <div class=\"row\">\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control mb-2\" value=\"";
echo $tid;
echo "\" name=\"tid\"\n                                    placeholder=\"";
echo __("Mã giao dịch");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control mb-2\" value=\"";
echo $description;
echo "\" name=\"description\"\n                                    placeholder=\"";
echo __("Nội dung chuyển khoản");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input type=\"text\" class=\"js-flatpickr form-control mb-2\" id=\"example-flatpickr-range\"\n                                    name=\"time\" placeholder=\"";
echo __("Chọn thời gian cần tìm");
echo "\" value=\"";
echo $time;
echo "\"\n                                    data-mode=\"range\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <button class=\"shop-widget-btn mb-2\"><i\n                                        class=\"fas fa-search\"></i><span>";
echo __("Tìm kiếm");
echo "</span></button>\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <a href=\"";
echo base_url("?action=recharge-bank");
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
echo "                                    </option>\n                                </select>\n                            </div>\n                        </div>\n                    </form>\n                    <div class=\"table-scroll\">\n                        <table class=\"table fs-sm mb-0\">\n                            <thead>\n                                <tr>\n                                    <th width=\"15%\">";
echo __("Thời gian");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Ngân hàng");
echo "</th>\n                                    <th>";
echo __("Nội dung chuyển khoản");
echo "</th>\n                                    <th class=\"text-right\">";
echo __("Số tiền nạp");
echo "</th>\n                                    <th class=\"text-right\">";
echo __("Thực nhận");
echo "</th>\n                                    <th class=\"text-center\">";
echo __("Trạng thái");
echo "</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
foreach ($listDatatable as $row) {
    echo "                                <tr>\n                                    <td><b>";
    echo $row["create_gettime"];
    echo "</b></td>\n                                    <td class=\"text-center\"><b>";
    echo $row["method"];
    echo "</b></td>\n                                    <td>\n                                        <small\n                                            id=\"RB";
    echo $row["id"];
    echo "\">";
    echo substr($row["description"], 0, 30);
    echo "...</small>\n                                        <small class=\"hidden\"\n                                            id=\"hidden";
    echo $row["id"];
    echo "\">";
    echo $row["description"];
    echo "</small>\n                                        <a href=\"javascript:void(0)\" class=\"hidden\"\n                                            id=\"read-hide";
    echo $row["id"];
    echo "\">";
    echo __("Ẩn bớt");
    echo "</a>\n                                        <a href=\"javascript:void(0)\"\n                                            id=\"read-more";
    echo $row["id"];
    echo "\">";
    echo __("Hiển thị thêm");
    echo "</a>\n                                    </td>\n                                    <td class=\"text-right\"><b\n                                            style=\"color: green;\">";
    echo format_currency($row["amount"]);
    echo "</b></td>\n                                    <td class=\"text-right\"><b\n                                            style=\"color: red;\">";
    echo format_currency($row["received"]);
    echo "</b></td>\n                                    <td class=\"fw-bold text-success text-center\"><b>";
    echo __("Đã thanh toán");
    echo "</b></td>\n                                </tr>\n\n                                <script>\n                                \$(\"#read-more";
    echo $row["id"];
    echo "\").click(function() {\n                                    \$(\"#hidden";
    echo $row["id"];
    echo "\").show(); // hiển thị nội dung đầy đủ\n                                    \$(this).hide(); // Ẩn nút hiển thị thêm\n                                    \$(\"#RB";
    echo $row["id"];
    echo "\").hide(); // Ẩn nội dung rút ngắn\n                                    \$(\"#read-hide";
    echo $row["id"];
    echo "\").show(); // hiển thị nút ẩn bớt\n                                });\n                                \$(\"#read-hide";
    echo $row["id"];
    echo "\").click(function() {\n                                    \$(\"#hidden";
    echo $row["id"];
    echo "\").hide(); // ẩn nội dung\n                                    \$(this).hide(); // ẩn nút ẩn bớt\n                                    \$(\"#RB";
    echo $row["id"];
    echo "\").show(); // hiển thị nội dung rút ngắn\n                                    \$(\"#read-more";
    echo $row["id"];
    echo "\").show(); // hiện nút hiển thị thêm\n                                });\n                                </script>\n                                ";
}
echo "                            </tbody>\n                            <tfoot>\n                                <tr>\n                                    <td colspan=\"7\">\n                                        <div class=\"float-right\">\n                                            ";
echo __("Đã thanh toán:");
echo "                                            <strong\n                                                style=\"color:red;\">";
echo format_currency($CMSNT->get_row(" SELECT SUM(`amount`) FROM `payment_bank` WHERE " . $where . " ")["SUM(`amount`)"]);
echo "</strong>\n                                            |\n\n                                            ";
echo __("Thực nhận:");
echo "                                            <strong\n                                                style=\"color:blue;\">";
echo format_currency($CMSNT->get_row(" SELECT SUM(`received`) FROM `payment_bank` WHERE " . $where . " ")["SUM(`received`)"]);
echo "</strong>\n                                    </td>\n                                </tr>\n                            </tfoot>\n                        </table>\n                    </div>\n                    <div class=\"bottom-paginate\">\n                        <p class=\"page-info\">Showing ";
echo $limit;
echo " of ";
echo $totalDatatable;
echo " Results</p>\n                        <div class=\"pagination\">\n                            ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script type=\"text/javascript\">\nnew ClipboardJS(\".copy\");\n\nfunction copy() {\n    showMessage(\"";
echo __("Đã sao chép vào bộ nhớ tạm");
echo "\", 'success');\n}\n</script>\n\n<script>\nfunction loadData() {\n    \$.ajax({\n        url: \"";
echo base_url("ajaxs/client/view.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'notication_topup',\n            token: '";
echo $getUser["token"];
echo "'\n        },\n        success: function(respone) {\n            if (respone.status == 'success') {\n                Swal.fire({\n                    icon: 'success',\n                    title: '";
echo __("Thành công !");
echo "',\n                    text: respone.msg,\n                    showDenyButton: true,\n                    confirmButtonText: '";
echo __("Nạp Thêm");
echo "',\n                    denyButtonText: `";
echo __("Mua Ngay");
echo "`,\n                }).then((result) => {\n                    if (result.isConfirmed) {\n                        location.reload();\n                    } else if (result.isDenied) {\n                        window.location.href = '";
echo base_url();
echo "';\n                    }\n                });\n            }\n            setTimeout(loadData, 5000);\n        },\n        error: function() {\n            setTimeout(loadData, 5000);\n        }\n    });\n}\nloadData();\n</script>\n\n<script>\nDashmix.helpersOnLoad(['js-flatpickr', 'jq-datepicker', 'jq-maxlength', 'jq-select2', 'jq-rangeslider',\n    'jq-masked-inputs', 'jq-pw-strength'\n]);\n</script>";

?>