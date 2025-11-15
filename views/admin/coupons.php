<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Mã giảm giá", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n \n\n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "view_coupon")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["AddCoupon"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used as this is a demo site.") . "\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_coupon")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    if(empty($_POST["code"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập mã giảm giá cần tạo\")){window.history.back().location.reload();}</script>");
    }
    $code = check_string($_POST["code"]);
    if($CMSNT->get_row("SELECT * FROM `coupons` WHERE `code` = '" . $code . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Mã giảm giá này đã tồn tại trong hệ thống\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["amount"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Số lượng không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    $amount = check_string($_POST["amount"]);
    if($amount <= 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Số lượng không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["discount"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập chiết khấu giảm giá\")){window.history.back().location.reload();}</script>");
    }
    $discount = check_string($_POST["discount"]);
    $product_id = json_encode($_POST["product_id"]);
    if(empty($_POST["product_id"])) {
        $product_id = NULL;
    }
    if(empty($_POST["min"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập giá trị đơn hàng tối thiểu\")){window.history.back().location.reload();}</script>");
    }
    $min = check_string($_POST["min"]);
    if($min <= 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Giá trị đơn hàng tối thiểu không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["max"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập giá trị đơn hàng tối đa\")){window.history.back().location.reload();}</script>");
    }
    $max = check_string($_POST["max"]);
    if($max <= 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Giá trị đơn hàng tối đa không hợp lệ\")){window.history.back().location.reload();}</script>");
    }
    $isInsert = $CMSNT->insert("coupons", ["code" => $code, "product_id" => $product_id, "amount" => $amount, "used" => 0, "discount" => $discount, "create_gettime" => gettime(), "update_gettime" => gettime(), "min" => $min, "max" => $max]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add Coupon (" . $code . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Add Coupon (" . $code . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công!\")){location.href = \"" . base_url_admin("coupons") . "\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại!\")){window.history.back().location.reload();}</script>");
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
$where = " `id` > 0 ";
$create_gettime = "";
$code = "";
$shortByDate = "";
if(!empty($_GET["code"])) {
    $code = check_string($_GET["code"]);
    $where .= " AND `code` LIKE \"%" . $code . "%\" ";
}
if(!empty($_GET["create_gettime"])) {
    $create_gettime = check_string($_GET["create_gettime"]);
    $createdate = $create_gettime;
    $create_gettime_1 = str_replace("-", "/", $create_gettime);
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `coupons` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `coupons` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("coupons&limit=" . $limit . "&shortByDate=" . $shortByDate . "&code=" . $code . "&create_gettime=" . $create_gettime . "&"), $from, $totalDatatable, $limit);
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-tags\"></i> Mã giảm giá</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Coupons</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH MÃ GIẢM GIÁ\n                        </div>\n                        <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                            class=\"btn btn-sm btn-primary shadow-primary\"><i\n                                class=\"ri-add-line fw-semibold align-middle\"></i> Tạo mã giảm giá mới</button>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"coupons\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $code;
echo "\" name=\"code\"\n                                        placeholder=\"Mã giảm giá\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
echo $create_gettime;
echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\" href=\"";
echo base_url_admin("coupons");
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th>Mã giảm giá</th>\n                                        <th>Sản phẩm áp dụng</th>\n                                        <th class=\"text-center\">Số lượng</th>\n                                        <th class=\"text-center\">Đã sử dụng</th>\n                                        <th>Giảm</th>\n                                        <th>Thời gian</th>\n                                        <th class=\"text-center\">Thao tác</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td><b>";
    echo $row["code"];
    echo "</b><br>\n                                            (";
    echo $row["amount"] <= $row["used"] ? "<span style=\"color:red\">Đã sử dụng hết</span>" : "<span style=\"color:green\">Còn " . ($AWWW2 = $row["amount"] - $row["used"] . " lượt sử dụng</span>");
    echo ")\n                                        </td>\n                                        <td>\n                                            ";
    if(empty($row["product_id"])) {
        echo "<span class=\"badge bg-info-transparent\">Áp dụng cho toàn bộ sản phẩm</span>";
    } else {
        foreach (json_decode($row["product_id"]) as $product_id) {
            echo "<span class=\"badge bg-primary-transparent\">" . getRowRealtime("products", $product_id, "name") . "</span><br>";
        }
    }
    echo "                                        </td>\n                                        <td class=\"text-center\"><span style=\"font-size: 15px;\"\n                                                class=\"badge bg-info\">";
    echo format_cash($row["amount"]);
    echo "</span>\n                                        </td>\n                                        <td class=\"text-center\"><span style=\"font-size: 15px;\"\n                                                class=\"badge bg-danger\">";
    echo format_cash($CMSNT->num_rows(" SELECT * FROM coupon_used WHERE `coupon_id` = '" . $row["id"] . "' "));
    echo "</span>\n                                        </td>\n                                        <td><span style=\"font-size: 15px;\"\n                                                class=\"badge bg-primary\">";
    echo $row["discount"];
    echo "%</span></td>\n                                        <td>";
    echo $row["create_gettime"];
    echo "</td>\n                                        <td class=\"text-center\">\n                                            <buton type=\"button\"\n                                                onclick=\"modalViewCoupon(`";
    echo $getUser["token"];
    echo "`, `";
    echo $row["id"];
    echo "`)\"\n                                                class=\"btn btn-sm btn-info\" data-bs-toggle=\"tooltip\"\n                                                title=\"Nhật ký sử dụng\">\n                                                <i class=\"fa-solid fa-clock-rotate-left\"></i>\n                                            </buton>\n                                            <a type=\"button\" href=\"";
    echo base_url_admin("coupon-edit&id=" . $row["id"]);
    echo "\"\n                                                class=\"btn btn-sm btn-primary\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Edit");
    echo "\">\n                                                <i class=\"fa-solid fa-pen-to-square\"></i>\n                                            </a>\n                                            <a type=\"button\" onclick=\"remove('";
    echo $row["id"];
    echo "')\"\n                                                class=\"btn btn-sm btn-danger\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Delete");
    echo "\">\n                                                <i class=\"fas fa-trash\"></i>\n                                            </a>\n                                        </td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered modal-lg dialog-scrollable\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\"><i class=\"fa-solid fa-plus\"></i> Tạo mã giảm giá mới\n                </h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Mã giảm giá (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group mb-3\">\n                                <input type=\"text\" class=\"form-control\" id=\"code\" name=\"code\"\n                                    placeholder=\"Nhập mã giảm giá cần tạo\" required>\n                                <button class=\"btn btn-danger\" type=\"button\" onclick=\"randomCode()\"><i\n                                        class=\"fa-solid fa-shuffle\"></i> Tạo mã ngẫu\n                                    nhiên</button>\n                            </div>\n\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Số lượng mã giảm giá (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group mb-3\">\n                                <button class=\"btn btn-primary shadow-primary\" type=\"button\" id=\"button-minus-amount\"><i\n                                        class=\"fa-solid fa-minus\"></i></button>\n                                <input type=\"number\" class=\"form-control text-center\" placeholder=\"\" value=\"1\"\n                                    name=\"amount\" required>\n                                <button class=\"btn btn-primary shadow-primary\" type=\"button\" id=\"button-plus-amount\"><i\n                                        class=\"fa-solid fa-plus\"></i></button>\n                            </div>\n                            <script>\n                            document.getElementById('button-plus-amount').addEventListener('click', function() {\n                                incrementValue();\n                            });\n                            document.getElementById('button-minus-amount').addEventListener('click', function() {\n                                decrementValue();\n                            });\n\n                            function incrementValue() {\n                                var inputElement = document.getElementsByName('amount')[0];\n                                var currentValue = parseInt(inputElement.value, 10);\n                                inputElement.value = currentValue + 1;\n                            }\n\n                            function decrementValue() {\n                                var inputElement = document.getElementsByName('amount')[0];\n                                var currentValue = parseInt(inputElement.value, 10);\n                                if (currentValue > 1) {\n                                    inputElement.value = currentValue - 1;\n                                }\n                            }\n                            </script>\n                            <small>Nếu bạn chọn 10, sẽ có 10 lượt sử dụng mã giảm giá cho 10 user khác nhau.</small>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Chiết khấu giảm (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group\">\n                                <input type=\"text\" class=\"form-control\" name=\"discount\" required>\n                                <span class=\"input-group-text\">\n                                    <i class=\"fa-solid fa-percent\"></i>\n                                </span>\n                            </div>\n                            <small>Nhập 10 tức giảm 10% cho đơn hàng áp dụng mã giảm giá này.</small>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Sản phẩm áp dụng (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <select class=\"form-control\" name=\"product_id[]\" id=\"listProduct\" multiple>\n                                <option value=\"\">Mặc định sẽ áp dụng cho toàn bộ sản phẩm nếu không chọn</option>\n                                ";
foreach ($CMSNT->get_list(" SELECT * FROM `categories` ") as $category) {
    echo "                                <optgroup label=\"__";
    echo $category["name"];
    echo "__\">\n                                    ";
    foreach ($CMSNT->get_list(" SELECT * FROM `products` WHERE `category_id` = '" . $category["id"] . "' ") as $product) {
        echo "                                    <option value=\"";
        echo $product["id"];
        echo "\">";
        echo $product["name"];
        echo "</option>\n                                    ";
    }
    echo "                                </optgroup>\n                                ";
}
echo "                            </select>\n                        </div>\n                        <script>\n                        const multipleCancelButton = new Choices(\n                            '#listProduct', {\n                                allowHTML: true,\n                                removeItemButton: true,\n                            }\n                        );\n                        </script>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Giá trị đơn hàng tối thiểu (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group\">\n                                <input type=\"text\" class=\"form-control\" value=\"100000\" name=\"min\" required>\n                                <span class=\"input-group-text\">\n                                    ";
echo currencyDefault();
echo "                                </span>\n                            </div>\n                            <small>Giá trị đơn hàng tối thiểu để áp dụng mã giảm giá</small>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Giá trị đơn hàng tối đa (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group\">\n                                <input type=\"text\" class=\"form-control\" value=\"100000000\" name=\"max\" required>\n                                <span class=\"input-group-text\">\n                                    ";
echo currencyDefault();
echo "                                </span>\n                            </div>\n                            <small>Giá trị đơn hàng tối đa để áp dụng mã giảm giá</small>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light \" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddCoupon\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>\n                        ";
echo __("Submit");
echo "</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nfunction random(length) {\n    var result = '';\n    var characters = 'QWERTYUPASDFGHJKZXCVBNM123456789';\n    var charactersLength = characters.length;\n    for (var i = 0; i < length; i++) {\n        result += characters.charAt(Math.floor(Math.random() *\n            charactersLength));\n    }\n    return result;\n}\n\nfunction randomCode() {\n    document.getElementById('code').value = random(8);\n}\n</script>\n\n\n<script>\nfunction postRemove(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeCounpon',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        }\n    });\n}\n\nfunction remove(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa Counpon\",\n        message: \"Bạn có chắc chắn muốn xóa Coupon này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemove(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n</script>\n\n\n<div class=\"modal fade\" id=\"ModalDialogViewCoupon\" tabindex=\"-1\" aria-labelledby=\"modal-block-popout\" role=\"dialog\"\n    aria-hidden=\"true\">\n    <div class=\"modal-dialog modal-dialog-centered modal-xl dialog-scrollable\">\n        <div class=\"modal-content\">\n            <div id=\"modalViewCoupon\"></div>\n        </div>\n    </div>\n</div>\n<script>\nfunction modalViewCoupon(token, id) {\n    \$(\"#modalViewCoupon\").html('');\n    \$.get(\"";
echo BASE_URL("ajaxs/admin/modal/coupon-view.php?id=");
echo "\" + id + '&token=' + token, function(data) {\n        \$(\"#modalViewCoupon\").html(data);\n    });\n    \$('#ModalDialogViewCoupon').modal('show')\n}\n</script>";

?>