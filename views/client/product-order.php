<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
require_once __DIR__ . "/../../models/is_user.php";
if(isset($_GET["trans_id"])) {
    $trans_id = check_string($_GET["trans_id"]);
    if(!($order = $CMSNT->get_row("SELECT * FROM `product_order` WHERE `trans_id` = '" . $trans_id . "' AND `buyer` = '" . $getUser["id"] . "' AND `trash` = 0 "))) {
        redirect(base_url("product-orders/"));
    }
} else {
    redirect(base_url("product-orders/"));
}
$product = $CMSNT->get_row(" SELECT * FROM `products` WHERE `id` = '" . $order["product_id"] . "' ");
$body = ["title" => __("Chi tiết đơn hàng") . " #" . $order["trans_id"] . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/product-details.css\">\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n\n";
$body["footer"] = "\n \n";
if(($order["status_view_order"] == 1 || $CMSNT->site("isPurchaseIpVerified") == 1) && $order["ip"] != myip()) {
    exit(__("Địa chỉ IP của bạn không khớp với địa chỉ IP bạn dùng để mua hàng"));
}
if($order["status_view_order"] == 1 || $CMSNT->site("isPurchaseDeviceVerified") == 1) {
    $Mobile_Detect = new Mobile_Detect();
    if($order["device"] != $Mobile_Detect->getUserAgent()) {
        exit(__("Trình duyệt của bạn không khớp với trình duyệt lúc bạn mua hàng"));
    }
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
$where = " `trans_id` = '" . $order["trans_id"] . "' ";
$shortByDate = "";
$account = "";
if(!empty($_GET["account"])) {
    $account = check_string($_GET["account"]);
    $where .= " AND `account` LIKE \"%" . $account . "%\" ";
}
$listDatatable = $CMSNT->get_list(" SELECT * FROM `product_sold` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `product_sold` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination_client(base_url("?action=product-order&limit=" . $limit . "&account=" . $account . "&trans_id=" . $trans_id . "&"), $from, $totalDatatable, $limit);
echo "<div style=\"margin-bottom:40px;\"></div>\n<section class=\"inner-section\" style=\"margin-bottom:40px;\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-6 mb-3\">\n                <a class=\"btn btn-danger btn-sm mb-2\" href=\"";
echo base_url("product-orders");
echo "\" type=\"button\">\n                    <i class=\"fa-solid fa-arrow-left\"></i>\n                    <span>";
echo __("Quay lại");
echo "</span></a><br>\n                ";
echo __("Mã đơn hàng:");
echo "                <strong>";
echo $order["trans_id"];
echo "</strong><br>\n            </div>\n            <div class=\"col-6 mb-3\">\n                <div class=\"text-right\">\n                    <button type=\"button\" class=\"btn btn-info btn-sm mb-1\" onclick=\"copyText()\">\n                        <i class=\"fa-solid fa-copy\"></i> ";
echo __("Copy");
echo "                    </button>\n                    <button type=\"button\" id=\"downloadAccounts\" class=\"btn btn-primary btn-sm mb-1\">\n                        <i class=\"fa-solid fa-cloud-arrow-down\"></i> ";
echo __("Tải về đơn hàng");
echo "                    </button>\n                    <button type=\"button\" onclick=\"deleteOrder()\" class=\"btn btn-danger btn-sm mb-1\">\n                        <i class=\"fa-solid fa-trash\"></i> ";
echo __("Xóa đơn hàng");
echo "                    </button>\n                </div>\n            </div>\n            <div class=\"col-lg-12\">\n                <div class=\"account-card pt-3\">\n                    <h3 class=\"details-name\">";
echo __("Sản phẩm:");
echo " <a\n                            href=\"";
echo base_url("product/" . $product["slug"]);
echo "\">";
echo __($product["name"]);
echo "</a></h3>\n\n                    <div class=\"details-meta\">\n                        <p>\n                            <label class=\"label-text feat\">";
echo __("Số lượng mua:");
echo "                                <strong>";
echo format_cash($order["amount"]);
echo "</strong></label>\n                            <label class=\"label-text order\">";
echo __("Thanh toán:");
echo "                                <strong>";
echo format_currency($order["pay"]);
echo "</strong></label>\n                        </p>\n                    </div>\n\n                    <p class=\"details-desc\">";
echo base64_decode($product["note"]);
echo "</p>\n                </div>\n            </div>\n\n        </div>\n    </div>\n</section>\n<section class=\"inner-section\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-lg-12\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-circle-info m-2\"></i> ";
echo mb_strtoupper(__("Chi tiết đơn hàng"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <form action=\"";
echo base_url();
echo "\" method=\"GET\">\n                        <input type=\"hidden\" name=\"action\" value=\"product-order\">\n                        <input type=\"hidden\" name=\"trans_id\" value=\"";
echo $order["trans_id"];
echo "\">\n                        <div class=\"row\">\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control mb-2\" type=\"text\" value=\"";
echo $account;
echo "\" name=\"account\"\n                                    placeholder=\"";
echo __("Tài khoản");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <button class=\"shop-widget-btn mb-2\"><i\n                                        class=\"fas fa-search\"></i><span>";
echo __("Tìm kiếm");
echo "</span></button>\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <a href=\"";
echo base_url("product-order/" . $order["trans_id"]);
echo "\"\n                                    class=\"shop-widget-btn mb-2\"><i\n                                        class=\"far fa-trash-alt\"></i><span>";
echo __("Bỏ lọc");
echo "</span></a>\n                            </div>\n                        </div>\n                        <div class=\"top-filter\">\n                            <div class=\"filter-short\">\n                                <label class=\"filter-label\">Show :</label>\n                                <select name=\"limit\" onchange=\"this.form.submit()\" class=\"form-select filter-select\">\n                                    <option ";
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
echo " value=\"1000\">1.000</option>\n                                    <option ";
echo $limit == 10000 ? "selected" : "";
echo " value=\"10000\">10.000</option>\n                                    <option ";
echo $limit == 20000 ? "selected" : "";
echo " value=\"20000\">20.000</option>\n                                </select>\n                            </div>\n                        </div>\n                    </form>\n                    <div class=\"table-scroll table-wrapper\">\n                        <table class=\"table fs-sm text-nowrap table-hover mb-0\">\n                            <thead>\n                                <th class=\"text-center\">\n                                    <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                        id=\"check_all_checkbox_product_sold\" value=\"option1\">\n                                </th>\n                                <th class=\"text-center\">UID</th>\n                                <th class=\"text-center\">";
echo __("Tài khoản");
echo "</th>\n                                <th class=\"text-center\">";
echo __("Thao tác");
echo "</th>\n                            </thead>\n                            <tbody>\n                                ";
foreach ($listDatatable as $row) {
    echo "                                <tr style=\"vertical-align: middle;\">\n                                    <td class=\"text-center\">\n                                        <input type=\"checkbox\" class=\"form-check-input checkbox_product_sold\"\n                                            data-id=\"";
    echo $row["id"];
    echo "\" data-checkbox=\"";
    echo $row["account"];
    echo "\"\n                                            name=\"checkbox_product_sold\" value=\"";
    echo $row["id"];
    echo "\" />\n                                    </td>\n                                    <td class=\"text-center\">\n                                        <strong>";
    echo $row["uid"];
    echo "</strong>\n                                    </td>\n                                    <td class=\"text-center\">\n                                        <textarea class=\"form-control\" id=\"copy";
    echo $row["id"];
    echo "\" rows=\"1\"\n                                            readonly>";
    echo $row["account"];
    echo "</textarea>\n                                    </td>\n                                    <td class=\"text-center\">\n                                        <button class=\"btn btn-info btn-sm copy\" onclick=\"copy()\"\n                                            data-clipboard-target=\"#copy";
    echo $row["id"];
    echo "\">\n                                            <i class=\"fa-solid fa-copy\"></i> ";
    echo __("Copy");
    echo "</button>\n                                    </td>\n                                </tr>\n                                ";
}
echo "                            </tbody>\n                            <tfoot>\n                                <td colspan=\"5\">\n                                    <div class=\"btn-group\">\n                                        <button type=\"button\" class=\"btn btn-dark dropdown-toggle\"\n                                            data-bs-toggle=\"dropdown\" aria-expanded=\"false\">\n                                            <i class=\"fa-solid fa-screwdriver-wrench\"></i>\n                                        </button>\n                                        <ul class=\"dropdown-menu ano20\">\n                                            <li><a class=\"dropdown-item\" href=\"javascript:void(0);\" type=\"button\"\n                                                    onclick=\"exportDataTXT()\"><i class=\"fa-solid fa-file-export\"></i>\n                                                    ";
echo __("Lưu các tài khoản đã chọn vào tệp .txt");
echo "</a></li>\n                                            <li><a class=\"dropdown-item\" href=\"javascript:void(0);\" type=\"button\"\n                                                    onclick=\"exportDataClipboard()\"><i class=\"fa-solid fa-copy\"></i>\n                                                    ";
echo __("Sao chép các tài khoản đã chọn");
echo "</a></li>\n                                            <li><a class=\"dropdown-item\" href=\"javascript:void(0);\" type=\"button\"\n                                                    onclick=\"exportUIDClipboard()\"><i class=\"fa-regular fa-copy\"></i>\n                                                    ";
echo __("Chỉ sao chép UID các tài khoản đã chọn");
echo "</a></li>\n                                        </ul>\n                                    </div>\n                                </td>\n                            </tfoot>\n                        </table>\n                    </div>\n                    <div class=\"bottom-paginate\">\n                        <p class=\"page-info\">Showing ";
echo $limit;
echo " of ";
echo $totalDatatable;
echo " Results</p>\n                        <div class=\"pagination\">\n                            ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n\n<textarea class=\"form-control\" id=\"listAccount\"\n    hidden>";
foreach ($CMSNT->get_list(" SELECT * FROM `product_sold` WHERE `trans_id` = '" . $order["trans_id"] . "' ") as $acc) {
    echo $acc["account"] . PHP_EOL;
}
echo "</textarea>\n<script>\ndocument.addEventListener(\"DOMContentLoaded\", function() {\n    document.getElementById('downloadAccounts').addEventListener('click', function() {\n        Swal.fire({\n            title: \"";
echo __("Bạn có chắc không?");
echo "\",\n            text: \"";
echo __("Hệ thống sẽ tải về đơn hàng khi bạn nhấn đồng ý");
echo "\",\n            icon: \"warning\",\n            showCancelButton: true,\n            confirmButtonColor: \"#3085d6\",\n            cancelButtonColor: \"#d33\",\n            confirmButtonText: \"";
echo __("Đồng ý");
echo "\",\n            cancelButtonText: \"";
echo __("Đóng");
echo "\",\n        }).then((result) => {\n            if (result.isConfirmed) {\n                // // Lấy nội dung từ textarea\n                // var listAccountContent = document.getElementById('listAccount').value;\n                // // Tạo đối tượng Blob\n                // var blob = new Blob([listAccountContent], {\n                //     type: 'text/plain'\n                // });\n                // // Tạo đường dẫn tạm thời\n                // var url = URL.createObjectURL(blob);\n                // // Tạo phần tử a và thiết lập các thuộc tính\n                // var a = document.createElement('a');\n                // a.href = url;\n                // a.download = '";
echo $trans_id;
echo ".txt';\n                // // Thêm phần tử a vào DOM và kích hoạt sự kiện click trên nó\n                // document.body.appendChild(a);\n                // a.click();\n                // // Loại bỏ phần tử a khỏi DOM sau khi đã kích hoạt\n                // document.body.removeChild(a);\n\n                \$.ajax({\n                    url: \"";
echo BASE_URL("ajaxs/client/view.php");
echo "\",\n                    method: \"POST\",\n                    dataType: \"JSON\",\n                    data: {\n                        action: 'download_order',\n                        trans_id: '";
echo $trans_id;
echo "',\n                        token: '";
echo $getUser["token"];
echo "',\n                    },\n                    success: function(result) {\n                        if (result.status == 'success') {\n                            showMessage(result.msg, result.status);\n                            downloadTXT(result.filename, result.accounts);\n                        } else {\n                            Swal.fire({\n                                title: \"";
echo __("Thất bại!");
echo "\",\n                                text: result.msg,\n                                icon: \"error\"\n                            });\n                        }\n                    },\n                    error: function() {\n                        alert(html(response));\n                        location.reload();\n                    }\n                });\n\n            }\n        });\n    });\n});\n\nfunction downloadTXT(filename, text) {\n    var element = document.createElement('a');\n    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));\n    element.setAttribute('download', filename);\n    element.style.display = 'none';\n    document.body.appendChild(element);\n    element.click();\n    document.body.removeChild(element);\n}\n</script>\n<script>\nfunction deleteOrder() {\n    Swal.fire({\n        title: \"";
echo __("Bạn có chắc không?");
echo "\",\n        text: \"";
echo __("Hệ thống sẽ xóa đơn hàng khỏi lịch sử của bạn khi bạn nhấn đồng ý");
echo "\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"";
echo __("Đồng ý");
echo "\",\n        cancelButtonText: \"";
echo __("Đóng");
echo "\",\n    }).then((result) => {\n        if (result.isConfirmed) {\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/client/remove.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    id: '";
echo $order["id"];
echo "',\n                    token: '";
echo $getUser["token"];
echo "',\n                    action: 'removeOrder'\n                },\n                success: function(respone) {\n                    if (respone.status == 'success') {\n                        location.reload();\n                    } else {\n                        Swal.fire({\n                            title: \"";
echo __("Thất bại!");
echo "\",\n                            text: respone.msg,\n                            icon: \"error\"\n                        });\n                    }\n                },\n                error: function() {\n                    alert(html(response));\n                    location.reload();\n                }\n            });\n        }\n    });\n}\n</script>\n\n<script>\nfunction copyText() {\n    var textarea = document.getElementById(\"listAccount\");\n    textarea.removeAttribute(\"hidden\"); // Hiển thị textarea trước khi sao chép\n    textarea.select(); // Chọn nội dung trong textarea\n    document.execCommand(\"copy\"); // Sao chép nội dung vào clipboard\n    textarea.setAttribute(\"hidden\", true); // Ẩn lại textarea sau khi sao chép\n    showMessage('";
echo __("Đã sao chép toàn bộ tài khoản vào bộ nhớ tạm");
echo "', 'success');\n}\n</script>\n<script>\n\$(function() {\n    \$('#check_all_checkbox_product_sold').on('click', function() {\n        \$('.checkbox_product_sold').prop('checked', this.checked);\n    });\n    \$('.checkbox_product_sold').on('click', function() {\n        \$('#check_all_checkbox_product_sold').prop('checked', \$('.checkbox_product_sold:checked')\n            .length === \$('.checkbox_product_sold').length);\n    });\n});\n</script>\n<script type=\"text/javascript\">\nnew ClipboardJS(\".copy\");\n\nfunction copy() {\n    showMessage(\"";
echo __("Đã sao chép vào bộ nhớ tạm");
echo "\", 'success');\n}\n</script>\n\n<script>\nfunction exportDataTXT() {\n    // Lấy tất cả các phần tử input có type là checkbox và được chọn\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_sold\"]:checked');\n\n    // Kiểm tra nếu không có checkbox nào được chọn\n    if (checkboxes.length === 0) {\n        return showMessage('";
echo __("Vui lòng chọn ít nhất một tài khoản");
echo "', 'error');\n    }\n\n    // Tạo một mảng để lưu trữ giá trị của các checkbox được chọn\n    var selectedData = [];\n\n    // Duyệt qua mỗi checkbox được chọn và thêm giá trị vào mảng\n    checkboxes.forEach(function(checkbox) {\n        selectedData.push(checkbox.getAttribute('data-checkbox') + ''); // Thêm dòng mới sau mỗi giá trị\n    });\n\n    // Lấy số lượng dữ liệu được xuất\n    var numberOfData = checkboxes.length;\n\n    // Chuyển đổi mảng thành chuỗi với mỗi giá trị trên một dòng\n    var dataString = selectedData.join('');\n\n    // Tạo một đối tượng Blob chứa dữ liệu\n    var blob = new Blob([dataString], {\n        type: 'text/plain'\n    });\n\n    // Tạo một đường link để tải xuống tệp tin TXT\n    var link = document.createElement('a');\n    link.href = URL.createObjectURL(blob);\n    link.download = '";
echo $trans_id;
echo "_' + numberOfData + '.txt';\n\n    // Thêm đường link vào trang và kích hoạt sự kiện click để tải xuống\n    document.body.appendChild(link);\n    link.click();\n\n    // Xóa đường link sau khi đã tải xuống\n    document.body.removeChild(link);\n}\n</script>\n\n<script>\nfunction exportDataClipboard() {\n    // Lấy tất cả các phần tử input có type là checkbox và được chọn\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_sold\"]:checked');\n\n    // Kiểm tra nếu không có checkbox nào được chọn\n    if (checkboxes.length === 0) {\n        return showMessage('";
echo __("Vui lòng chọn ít nhất một tài khoản");
echo "', 'error');\n    }\n\n    // Tạo một mảng để lưu trữ giá trị của các checkbox được chọn\n    var selectedData = [];\n\n    // Duyệt qua mỗi checkbox được chọn và thêm giá trị vào mảng\n    checkboxes.forEach(function(checkbox) {\n        // Đảm bảo rằng có một dòng mới sau mỗi giá trị\n        selectedData.push(checkbox.getAttribute('data-checkbox').trim());\n    });\n\n    // Chuyển đổi mảng thành chuỗi, với mỗi giá trị trên một dòng\n    var dataString = selectedData.join('\\n');\n\n    // Sao chép chuỗi vào clipboard\n    navigator.clipboard.writeText(dataString).then(function() {\n        showMessage('";
echo __("Nội dung đã được sao chép vào clipboard!");
echo "', 'success');\n    }).catch(function(error) {\n        alert('Có lỗi xảy ra trong quá trình sao chép: ' + error);\n    });\n}\n</script>\n\n<script>\nfunction exportUIDClipboard() {\n    // Lấy tất cả các phần tử input có type là checkbox và được chọn\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_sold\"]:checked');\n\n    // Kiểm tra nếu không có checkbox nào được chọn\n    if (checkboxes.length === 0) {\n        return showMessage('";
echo __("Vui lòng chọn ít nhất một tài khoản");
echo "', 'error');\n    }\n\n    // Tạo một mảng để lưu trữ giá trị của các checkbox được chọn\n    var selectedData = [];\n\n    // Duyệt qua mỗi checkbox được chọn và thêm giá trị vào mảng\n    checkboxes.forEach(function(checkbox) {\n        // Lấy dữ liệu và chia nó dựa trên dấu '|'\n        var fullData = checkbox.getAttribute('data-checkbox').trim();\n        var splitData = fullData.split('|');\n        // Kiểm tra để chắc chắn rằng dữ liệu tồn tại trước khi thêm vào mảng\n        if (splitData.length > 0) {\n            selectedData.push(splitData[0]); // Chỉ lấy phần trước dấu '|'\n        }\n    });\n\n    // Chuyển đổi mảng thành chuỗi, với mỗi giá trị trên một dòng\n    var dataString = selectedData.join('\\n');\n\n    // Sao chép chuỗi vào clipboard\n    navigator.clipboard.writeText(dataString).then(function() {\n        showMessage('";
echo __("Nội dung đã được sao chép vào clipboard!");
echo "', 'success');\n    }).catch(function(error) {\n        alert('Có lỗi xảy ra trong quá trình sao chép: ' + error);\n    });\n}\n</script>\n";
require_once __DIR__ . "/footer.php";

?>