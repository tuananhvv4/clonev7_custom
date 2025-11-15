<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
require_once __DIR__ . "/../../models/is_user.php";
$body = ["title" => __("Lịch sử đơn hàng") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n\n";
$body["footer"] = "\n \n";
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
$where = " `buyer` = '" . $getUser["id"] . "' ";
$shortByDate = "";
$trans_id = "";
$time = "";
if(!empty($_GET["trans_id"])) {
    $trans_id = check_string($_GET["trans_id"]);
    $where .= " AND `trans_id` = \"" . $trans_id . "\" ";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `product_order` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `product_order` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination_client(base_url("?action=product-orders&limit=" . $limit . "&trans_id=" . $trans_id . "&shortByDate=" . $shortByDate . "&time=" . $time . "&"), $from, $totalDatatable, $limit);
echo "\n\n<div style=\"margin-bottom:40px;\"></div>\n<section class=\"inner-section\">\n    <div class=\"container\">\n        ";
if($CMSNT->site("notice_orders") != "") {
    echo "        <div class=\"col-md-12\">\n            <div class=\"account-card pt-3\">\n                ";
    echo $CMSNT->site("notice_orders");
    echo "            </div>\n        </div>\n        ";
}
echo "        <div class=\"row\">\n            <div class=\"col-lg-12\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-cart-shopping m-2\"></i> ";
echo mb_strtoupper(__("Lịch sử đơn hàng"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <form action=\"";
echo base_url();
echo "\" method=\"GET\">\n                        <input type=\"hidden\" name=\"action\" value=\"product-orders\">\n                        <div class=\"row\">\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input class=\"form-control mb-2\" type=\"hidden\" value=\"";
echo $getUser["token"];
echo "\"\n                                    id=\"token\">\n                                <input class=\"form-control mb-2\" type=\"text\" value=\"";
echo $trans_id;
echo "\" name=\"trans_id\"\n                                    placeholder=\"";
echo __("Mã đơn hàng");
echo "\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <input type=\"text\" class=\"js-flatpickr form-control mb-2\" id=\"example-flatpickr-range\"\n                                    name=\"time\" placeholder=\"";
echo __("Chọn thời gian cần tìm");
echo "\" value=\"";
echo $time;
echo "\"\n                                    data-mode=\"range\">\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <button class=\"shop-widget-btn mb-2\"><i\n                                        class=\"fas fa-search\"></i><span>";
echo __("Tìm kiếm");
echo "</span></button>\n                            </div>\n                            <div class=\"col-lg col-md-4 col-6\">\n                                <a href=\"";
echo base_url("product-orders/");
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
echo " value=\"1000\">1000\n                                    </option>\n                                </select>\n                            </div>\n                            <div class=\"filter-short\">\n                                <label class=\"filter-label\">";
echo __("Short by Date:");
echo "</label>\n                                <select name=\"shortByDate\" onchange=\"this.form.submit()\"\n                                    class=\"form-select filter-select\">\n                                    <option value=\"\">";
echo __("Tất cả");
echo "</option>\n                                    <option ";
echo $shortByDate == 1 ? "selected" : "";
echo " value=\"1\">\n                                        ";
echo __("Hôm nay");
echo "                                    </option>\n                                    <option ";
echo $shortByDate == 2 ? "selected" : "";
echo " value=\"2\">\n                                        ";
echo __("Tuần này");
echo "                                    </option>\n                                    <option ";
echo $shortByDate == 3 ? "selected" : "";
echo " value=\"3\">\n                                        ";
echo __("Tháng này");
echo "                                    </option>\n                                </select>\n                            </div>\n                        </div>\n                    </form>\n                    <div class=\"table-scroll table-wrapper\">\n                        <table class=\"table fs-sm text-nowrap table-hover  mb-0\">\n                            <thead>\n                                <th class=\"text-center\">\n                                    <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                        id=\"check_all_checkbox\" value=\"option1\">\n                                </th>\n                                <th class=\"text-center\">";
echo __("Thao tác");
echo "</th>\n                                <th class=\"text-center\">";
echo __("Mã đơn hàng");
echo "</th>\n                                <th class=\"text-center\">";
echo __("Sản phẩm");
echo "</th>\n                                <th class=\"text-center\">";
echo __("Số lượng");
echo "</th>\n                                <th class=\"text-center\">";
echo __("Thanh toán");
echo "</th>\n                                <th class=\"text-center\">";
echo __("Ghi chú cá nhân");
echo "</th>\n                                <th class=\"text-center\">";
echo __("Thời gian");
echo "</th>\n                            </thead>\n                            <tbody>\n                                ";
foreach ($listDatatable as $order) {
    echo "                                <tr\n                                    style=\"vertical-align: middle;";
    echo $order["trash"] == 1 ? "background-color:#ffd6d6;" : "";
    echo "\">\n                                    <td class=\"text-center\">\n                                        ";
    if($order["trash"] == 0) {
        echo "                                        <input type=\"checkbox\" class=\"form-check-input checkbox\"\n                                            data-id=\"";
        echo $order["id"];
        echo "\" name=\"checkbox\" value=\"";
        echo $order["id"];
        echo "\" />\n                                        ";
    }
    echo "                                    </td>\n                                    <td class=\"text-center\">\n                                        ";
    if($order["trash"] == 1) {
        echo "                                        <strong>";
        echo __("Đơn hàng đã bị xóa");
        echo "</strong>\n                                        ";
    } else {
        echo "                                        <a class=\"btn btn-info btn-sm\"\n                                            href=\"";
        echo base_url("product-order/" . $order["trans_id"]);
        echo "\" type=\"button\"><i\n                                                class=\"fa-solid fa-eye\"></i>\n                                            ";
        echo __("View");
        echo "</a>\n                                        <button class=\"btn btn-primary btn-sm\"\n                                            onclick=\"downloadOrder(`";
        echo $order["trans_id"];
        echo "`)\"><i\n                                                class=\"fa-solid fa-cloud-arrow-down\"></i>\n                                            ";
        echo __("Download");
        echo "</button>\n                                        <button type=\"button\" onclick=\"deleteOrder(`";
        echo $order["id"];
        echo "`)\"\n                                            class=\"btn btn-danger btn-sm\">\n                                            <i class=\"fa-solid fa-trash\"></i> ";
        echo __("Delete");
        echo "                                        </button>\n                                        ";
    }
    echo "                                    </td>\n                                    <td class=\"text-center\">\n                                        ";
    echo $order["trans_id"];
    echo "                                    </td>\n                                    <td class=\"text-center\">\n                                        <strong><small>";
    echo $order["product_name"];
    echo "</small></strong>\n                                    </td>\n                                    <td class=\"text-right\">\n                                        <span class=\"badge bg-primary\">";
    echo format_cash($order["amount"]);
    echo "</span>\n                                    </td>\n                                    <td class=\"text-right\">\n                                        <span class=\"badge bg-danger\">";
    echo format_currency($order["pay"]);
    echo "</span>\n                                    </td>\n                                    <td class=\"text-center\">\n                                        <textarea class=\"saveNote\" rows=\"1\"\n                                            data-id=\"";
    echo $order["id"];
    echo "\">";
    echo $order["note"];
    echo "</textarea>\n                                    </td>\n                                    <td class=\"text-center\">\n                                        <strong data-toggle=\"tooltip\" data-placement=\"bottom\"\n                                            title=\"";
    echo timeAgo(strtotime($order["create_gettime"]));
    echo "\"><small>";
    echo $order["create_gettime"];
    echo "</small></strong>\n                                    </td>\n                                </tr>\n                                ";
}
echo "                            </tbody>\n                            <tfoot>\n                                <td colspan=\"7\">\n                                    <button type=\"button\" id=\"btn_delete\" class=\"btn btn-danger btn-sm\"\n                                        data-toggle=\"tooltip\" data-placement=\"bottom\"\n                                        title=\"";
echo __("Xóa đơn hàng đã chọn khỏi lịch sử của bạn");
echo "\">\n                                        <i class=\"fa-solid fa-trash\"></i> ";
echo __("Xóa đơn hàng");
echo "                                    </button>\n                                </td>\n                            </tfoot>\n                        </table>\n                    </div>\n                    <div class=\"bottom-paginate\">\n                        <p class=\"page-info\">Showing ";
echo $limit;
echo " of ";
echo $totalDatatable;
echo " Results</p>\n                        <div class=\"pagination\">\n                            ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n<script>\n\$(document).ready(function() {\n    \$('.saveNote').on('input', function() {\n        saveNote(\$(this));\n    });\n\n    function saveNote(textarea) {\n        var note = textarea.val();\n        var id = textarea.data('id');\n        \$.ajax({\n            url: \"";
echo BASE_URL("ajaxs/client/update.php");
echo "\",\n            method: \"POST\",\n            dataType: \"JSON\",\n            data: {\n                id: id,\n                note: note,\n                token: \$(\"#token\").val(),\n                action: 'saveNoteOrder'\n            },\n            success: function(result) {},\n            error: function() {}\n        });\n    }\n});\n</script>\n<script>\n\$(function() {\n    \$('#check_all_checkbox').on('click', function() {\n        \$('.checkbox').prop('checked', this.checked);\n    });\n    \$('.checkbox').on('click', function() {\n        \$('#check_all_checkbox').prop('checked', \$('.checkbox:checked')\n            .length === \$('.checkbox').length);\n    });\n});\n\nfunction delete_records() {\n    var checkbox = document.getElementsByName('checkbox');\n\n    function postUpdatesSequentially(index) {\n        if (index < checkbox.length) {\n            if (checkbox[index].checked === true) {\n                post_remove(checkbox[index].value);\n            }\n            setTimeout(function() {\n                postUpdatesSequentially(index + 1);\n            }, 100);\n        } else {\n            Swal.fire({\n                title: \"";
echo __("Thành công!");
echo "\",\n                text: \"";
echo __("Xóa đơn hàng thành công");
echo "\",\n                icon: \"success\"\n            });\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    }\n    postUpdatesSequentially(0);\n}\n\n\$(\"#btn_delete\").click(function() {\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox\"]:checked');\n    if (checkboxes.length === 0) {\n        showMessage('";
echo __("Vui lòng chọn ít nhất một đơn hàng.");
echo "', 'error');\n        return;\n    }\n    Swal.fire({\n        title: \"";
echo __("Bạn có chắc không?");
echo "\",\n        text: \"";
echo __("Hệ thống sẽ xóa");
echo " \" + checkboxes.length +\n            \" ";
echo __("đơn hàng bạn chọn khi nhấn Đồng Ý");
echo "\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"";
echo __("Đồng ý");
echo "\",\n        cancelButtonText: \"";
echo __("Đóng");
echo "\"\n    }).then((result) => {\n        if (result.isConfirmed) {\n            delete_records();\n        }\n    });\n});\n</script>\n\n<script>\nfunction post_remove(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/client/remove.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            id: id,\n            token: \$(\"#token\").val(),\n            action: 'removeOrder'\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        },\n        error: function() {\n            alert(html(response));\n            location.reload();\n        }\n    });\n}\n\nfunction deleteOrder(id) {\n    Swal.fire({\n        title: \"";
echo __("Bạn có chắc không?");
echo "\",\n        text: \"";
echo __("Hệ thống sẽ xóa đơn hàng khỏi lịch sử của bạn khi bạn nhấn đồng ý");
echo "\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"";
echo __("Đồng ý");
echo "\",\n        cancelButtonText: \"";
echo __("Đóng");
echo "\",\n    }).then((result) => {\n        if (result.isConfirmed) {\n            post_remove(id);\n            setTimeout(function() {\n                location.reload();\n            }, 500);\n        }\n    });\n}\n</script>\n\n\n\n<script>\nfunction downloadOrder(trans_id) {\n    Swal.fire({\n        title: \"";
echo __("Bạn có chắc không?");
echo "\",\n        text: \"";
echo __("Hệ thống sẽ tải về đơn hàng khi bạn nhấn đồng ý");
echo "\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"";
echo __("Đồng ý");
echo "\",\n        cancelButtonText: \"";
echo __("Đóng");
echo "\",\n    }).then((result) => {\n        if (result.isConfirmed) {\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/client/view.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    action: 'download_order',\n                    trans_id: trans_id,\n                    token: \$(\"#token\").val(),\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        downloadTXT(result.filename, result.accounts);\n                    } else {\n                        Swal.fire({\n                            title: \"";
echo __("Thất bại!");
echo "\",\n                            text: result.msg,\n                            icon: \"error\"\n                        });\n                    }\n                },\n                error: function() {\n                    alert(html(response));\n                    location.reload();\n                }\n            });\n        }\n    });\n}\n\nfunction downloadTXT(filename, text) {\n    var element = document.createElement('a');\n    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));\n    element.setAttribute('download', filename);\n    element.style.display = 'none';\n    document.body.appendChild(element);\n    element.click();\n    document.body.removeChild(element);\n}\n</script>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nDashmix.helpersOnLoad(['js-flatpickr', 'jq-datepicker', 'jq-maxlength', 'jq-select2', 'jq-rangeslider',\n    'jq-masked-inputs', 'jq-pw-strength'\n]);\n</script>";

?>