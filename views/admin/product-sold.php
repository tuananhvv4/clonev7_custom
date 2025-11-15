<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Toàn bộ tài khoản đã bán | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "view_sold_product")) {
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
$where = " `id` > 0 ";
$create_gettime = "";
$uid = "";
$shortByDate = "";
$buyer = "";
$username = "";
$account = "";
$trans_id = "";
$product_code = "";
if(!empty($_GET["product_code"])) {
    $product_code = check_string($_GET["product_code"]);
    $where .= " AND `product_code` = \"" . $product_code . "\" ";
}
if(!empty($_GET["trans_id"])) {
    $trans_id = check_string($_GET["trans_id"]);
    $where .= " AND `trans_id` = \"" . $trans_id . "\" ";
}
if(!empty($_GET["account"])) {
    $account = check_string($_GET["account"]);
    $where .= " AND `account` LIKE \"%" . $account . "%\" ";
}
if(!empty($_GET["uid"])) {
    $uid = check_string($_GET["uid"]);
    $where .= " AND `uid` LIKE \"%" . $uid . "%\" ";
}
if(!empty($_GET["username"])) {
    $username = check_string($_GET["username"]);
    if($idUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `username` = '" . $username . "' ")) {
        $where .= " AND `buyer` =  \"" . $idUser["id"] . "\" ";
    } else {
        $where .= " AND `buyer` =  \"\" ";
    }
}
if(!empty($_GET["buyer"])) {
    $buyer = check_string($_GET["buyer"]);
    $where .= " AND `buyer` = \"" . $buyer . "\" ";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `product_sold` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `product_sold` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("product-sold&limit=" . $limit . "&shortByDate=" . $shortByDate . "&uid=" . $uid . "&create_gettime=" . $create_gettime . "&buyer=" . $buyer . "&username=" . $username . "&account=" . $account . "&trans_id=" . $trans_id . "&product_code=" . $product_code . "&"), $from, $totalDatatable, $limit);
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-cart-arrow-down\"></i> Tài khoản đã bán</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            TÀI KHOẢN ĐÃ BÁN\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"product-sold\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $uid;
echo "\" name=\"uid\"\n                                        placeholder=\"UID\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $account;
echo "\" name=\"account\"\n                                        placeholder=\"Tài khoản\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $trans_id;
echo "\" name=\"trans_id\"\n                                        placeholder=\"Mã đơn hàng\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $product_code;
echo "\" name=\"product_code\"\n                                        placeholder=\"Mã kho hàng\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $buyer;
echo "\" name=\"buyer\"\n                                        placeholder=\"ID Seller\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $username;
echo "\" name=\"username\"\n                                        placeholder=\"Username Seller\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
echo $create_gettime;
echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\"\n                                        href=\"";
echo base_url_admin("product-sold");
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
echo $limit == 2000 ? "selected" : "";
echo " value=\"2000\">2.000</option>\n                                        <option ";
echo $limit == 5000 ? "selected" : "";
echo " value=\"5000\">5.000</option>\n                                        <option ";
echo $limit == 10000 ? "selected" : "";
echo " value=\"10000\">10.000</option>\n                                        <option ";
echo $limit == 20000 ? "selected" : "";
echo " value=\"20000\">20.000</option>\n                                        <option ";
echo $limit == 50000 ? "selected" : "";
echo " value=\"50000\">50.000</option>\n                                        <option ";
echo $limit == 100000 ? "selected" : "";
echo " value=\"100000\">100.000</option>\n                                    </select>\n                                </div>\n                                <div class=\"filter-short\">\n                                    <label class=\"filter-label\">";
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive table-wrapper mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                                    id=\"check_all_checkbox_product_sold\" value=\"option1\">\n                                            </div>\n                                        </th>\n                                        <th class=\"text-center\">UID</th>\n                                        <th class=\"text-center\">Tài khoản</th>\n                                        <th class=\"text-center\">Mã đơn hàng</th>\n                                        <th class=\"text-center\">Kho hàng</th>\n                                        <th class=\"text-center\">Seller</th>\n                                        <th class=\"text-center\">Thời gian</th>\n                                        <th class=\"text-center\">Check live gần nhất</th>\n                                        <th class=\"text-center\">Type</th>\n                                        <th class=\"text-center\">Thao tác</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input checkbox_product_sold\"\n                                                    data-id=\"";
    echo $row["id"];
    echo "\" data-checkbox=\"";
    echo $row["account"];
    echo "\"\n                                                    name=\"checkbox_product_sold\" value=\"";
    echo $row["id"];
    echo "\" />\n                                            </div>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <strong>";
    echo $row["uid"];
    echo "</strong>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <textarea rows=\"1\" class=\"form-control\">";
    echo $row["account"];
    echo "</textarea>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <strong>";
    echo $row["trans_id"];
    echo "</strong>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <strong>";
    echo $row["product_code"];
    echo "</strong>\n                                        </td>\n                                        <td class=\"text-center\"><a class=\"text-primary\"\n                                                href=\"";
    echo base_url_admin("user-edit&id=" . $row["seller"]);
    echo "\">";
    echo getRowRealtime("users", $row["seller"], "username");
    echo "                                                [ID ";
    echo $row["seller"];
    echo "]</a>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <small data-toggle=\"tooltip\" data-placement=\"bottom\"\n                                                title=\"";
    echo timeAgo(strtotime($row["create_gettime"]));
    echo "\">";
    echo $row["create_gettime"];
    echo "</small>\n                                        </td>\n                                        <td class=\"text-center\"><span\n                                                class=\"badge rounded-pill bg-dark text-white\" data-toggle=\"tooltip\" data-placement=\"bottom\"\n                                                title=\"";
    echo date("H:i:s d-m-Y", $row["time_check_live"]);
    echo "\">";
    echo timeAgo($row["time_check_live"]);
    echo "</span>\n                                        </td> \n                                        <td class=\"text-center\"><small>";
    echo $row["type"];
    echo "</small></td>\n                                        <td class=\"text-center\">\n                                            <a type=\"button\" onclick=\"removeAccount('";
    echo $row["id"];
    echo "')\"\n                                                class=\"btn btn-sm btn-danger\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Xóa");
    echo "\">\n                                                <i class=\"fas fa-trash\"></i> Delete\n                                            </a>\n                                        </td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                                <tfoot>\n                                    <td colspan=\"9\">\n                                        <div class=\"btn-list\">\n                                            <button type=\"button\" onclick=\"exportDataTXT()\" id=\"exportDataTXT\"\n                                                class=\"btn btn-outline-primary shadow-primary btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-file-export\"></i> XUẤT TỆP .TXT</button>\n                                            <button type=\"button\" onclick=\"exportDataClipboard()\"\n                                                id=\"exportDataClipboard\"\n                                                class=\"btn btn-outline-success shadow-success btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-copy\"></i> COPY</button>\n                                            <button type=\"button\" onclick=\"exportUIDClipboard()\" id=\"exportUIDClipboard\"\n                                                class=\"btn btn-outline-info shadow-info btn-wave btn-sm\"><i\n                                                    class=\"fa-regular fa-copy\"></i> COPY UID</button>\n                                            <button type=\"button\" onclick=\"confirmDeleteAccount()\"\n                                                id=\"confirmDeleteAccount\"\n                                                class=\"btn btn-outline-danger shadow-danger btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-trash\"></i> DELETE</button>\n                                        </div>\n                                    </td>\n                                </tfoot>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo format_cash($limit);
echo " of\n                                    ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n \n\n\n<script>\nfunction postRemoveAccount(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeAccountSold',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        }\n    });\n}\n\nfunction removeAccount(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa tài khoản\",\n        message: \"Bạn có chắc chắn muốn xóa tài khoản này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemoveAccount(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n</script>\n<script>\nfunction confirmDeleteAccount() {\n    var checkbox = document.getElementsByName('checkbox_product_sold');\n    var isAnyCheckboxChecked = false;\n    for (var i = 0; i < checkbox.length; i++) {\n        if (checkbox[i].checked === true) {\n            isAnyCheckboxChecked = true;\n            break;\n        }\n    }\n    if (!isAnyCheckboxChecked) {\n        showMessage('Vui lòng chọn ít nhất một bản ghi', 'error');\n        return;\n    }\n    var result = confirm('Bạn có đồng ý xóa các bản ghi đã chọn không?');\n    if (result) {\n        \$('#confirmDeleteAccount').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "</span>')\n            .prop('disabled',\n                true);\n\n        function postUpdatesSequentially(index) {\n            if (index < checkbox.length) {\n                if (checkbox[index].checked === true) {\n                    postRemoveAccount(checkbox[index].value);\n                }\n                setTimeout(function() {\n                    postUpdatesSequentially(index + 1);\n                }, 100);\n            } else {\n                setTimeout(function() {\n                    location.reload();\n                }, 1000);\n            }\n        }\n        postUpdatesSequentially(0);\n    }\n}\n\n\$(function() {\n    \$('#check_all_checkbox_product_sold').on('click', function() {\n        \$('.checkbox_product_sold').prop('checked', this.checked);\n    });\n    \$('.checkbox_product_sold').on('click', function() {\n        \$('#check_all_checkbox_product_sold').prop('checked', \$('.checkbox_product_sold:checked')\n            .length === \$('.checkbox_product_sold').length);\n    });\n});\n</script>\n\n<script>\nfunction exportDataTXT() {\n    // Lấy tất cả các phần tử input có type là checkbox và được chọn\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_sold\"]:checked');\n\n    // Kiểm tra nếu không có checkbox nào được chọn\n    if (checkboxes.length === 0) {\n        showMessage('Vui lòng chọn ít nhất một bản ghi', 'error');\n        return;\n    }\n    \$('#exportDataTXT').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "</span>')\n        .prop('disabled',\n            true);\n    // Tạo một mảng để lưu trữ giá trị của các checkbox được chọn\n    var selectedData = [];\n\n    // Duyệt qua mỗi checkbox được chọn và thêm giá trị vào mảng\n    checkboxes.forEach(function(checkbox) {\n        selectedData.push(checkbox.getAttribute('data-checkbox') + ''); // Thêm dòng mới sau mỗi giá trị\n    });\n\n    // Lấy số lượng dữ liệu được xuất\n    var numberOfData = checkboxes.length;\n\n    // Chuyển đổi mảng thành chuỗi với mỗi giá trị trên một dòng\n    var dataString = selectedData.join('');\n\n    // Tạo một đối tượng Blob chứa dữ liệu\n    var blob = new Blob([dataString], {\n        type: 'text/plain'\n    });\n\n    // Tạo một đường link để tải xuống tệp tin TXT\n    var link = document.createElement('a');\n    link.href = URL.createObjectURL(blob);\n    link.download = numberOfData + '.txt';\n\n    // Thêm đường link vào trang và kích hoạt sự kiện click để tải xuống\n    document.body.appendChild(link);\n    link.click();\n\n    // Xóa đường link sau khi đã tải xuống\n    document.body.removeChild(link);\n    \$('#exportDataTXT').html(\n        '<i class=\"fa-solid fa-file-export\"></i> XUẤT TỆP .TXT'\n    ).prop('disabled',\n        false);\n\n\n}\n</script>\n\n<script>\nfunction exportDataClipboard() {\n    // Lấy tất cả các phần tử input có type là checkbox và được chọn\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_sold\"]:checked');\n\n    // Kiểm tra nếu không có checkbox nào được chọn\n    if (checkboxes.length === 0) {\n        showMessage('Vui lòng chọn ít nhất một bản ghi', 'error');\n        return;\n    }\n    \$('#exportDataClipboard').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "</span>')\n        .prop('disabled',\n            true);\n    // Tạo một mảng để lưu trữ giá trị của các checkbox được chọn\n    var selectedData = [];\n\n    // Duyệt qua mỗi checkbox được chọn và thêm giá trị vào mảng\n    checkboxes.forEach(function(checkbox) {\n        // Đảm bảo rằng có một dòng mới sau mỗi giá trị\n        selectedData.push(checkbox.getAttribute('data-checkbox').trim());\n    });\n\n    // Chuyển đổi mảng thành chuỗi, với mỗi giá trị trên một dòng\n    var dataString = selectedData.join('\\n');\n\n    // Sao chép chuỗi vào clipboard\n    navigator.clipboard.writeText(dataString).then(function() {\n        showMessage(\"Nội dung đã được sao chép vào clipboard!\", 'success');\n        \$('#exportDataClipboard').html(\n            '<i class=\"fa-solid fa-copy\"></i> COPY'\n        ).prop('disabled',\n            false);\n    }).catch(function(error) {\n        \$('#exportDataClipboard').html(\n            '<i class=\"fa-solid fa-copy\"></i> COPY'\n        ).prop('disabled',\n            false);\n        showMessage('Có lỗi xảy ra trong quá trình sao chép: ' + error, 'error');\n    });\n}\n</script>\n\n<script>\nfunction exportUIDClipboard() {\n    // Lấy tất cả các phần tử input có type là checkbox và được chọn\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_sold\"]:checked');\n\n    // Kiểm tra nếu không có checkbox nào được chọn\n    if (checkboxes.length === 0) {\n        showMessage('Vui lòng chọn ít nhất một bản ghi', 'error');\n        return;\n    }\n    \$('#exportUIDClipboard').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "</span>')\n        .prop('disabled',\n            true);\n    // Tạo một mảng để lưu trữ giá trị của các checkbox được chọn\n    var selectedData = [];\n\n    // Duyệt qua mỗi checkbox được chọn và thêm giá trị vào mảng\n    checkboxes.forEach(function(checkbox) {\n        // Lấy dữ liệu và chia nó dựa trên dấu '|'\n        var fullData = checkbox.getAttribute('data-checkbox').trim();\n        var splitData = fullData.split('|');\n        // Kiểm tra để chắc chắn rằng dữ liệu tồn tại trước khi thêm vào mảng\n        if (splitData.length > 0) {\n            selectedData.push(splitData[0]); // Chỉ lấy phần trước dấu '|'\n        }\n    });\n\n    // Chuyển đổi mảng thành chuỗi, với mỗi giá trị trên một dòng\n    var dataString = selectedData.join('\\n');\n\n    // Sao chép chuỗi vào clipboard\n    navigator.clipboard.writeText(dataString).then(function() {\n        showMessage(\"Nội dung đã được sao chép vào clipboard!\", 'success');\n        \$('#exportUIDClipboard').html(\n            '<i class=\"fa-regular fa-copy\"></i> COPY UID'\n        ).prop('disabled',\n            false);\n    }).catch(function(error) {\n        showMessage('Có lỗi xảy ra trong quá trình sao chép: ' + error, 'error');\n    });\n}\n</script>\n\n<script type=\"text/javascript\">\nnew ClipboardJS(\".copy\");\n\nfunction copy() {\n    showMessage(\"";
echo __("Đã sao chép vào bộ nhớ tạm");
echo "\", 'success');\n}\n</script>\n\n";

?>