<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Products") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "view_product")) {
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
$user_id = "";
$name = "";
$create_gettime = "";
$username = "";
$shortByDate = "";
$category_id = "";
$supplier_id = "";
$status = "";
$code = "";
if(!empty($_GET["code"])) {
    $code = check_string($_GET["code"]);
    $where .= " AND `code` = \"" . $code . "\" ";
}
if(!empty($_GET["status"])) {
    $status = check_string($_GET["status"]);
    if($status == 2) {
        $where .= " AND `status` = 0 ";
    } elseif($status == 1) {
        $where .= " AND `status` = 1 ";
    }
}
if(!empty($_GET["supplier_id"])) {
    $supplier_id = check_string($_GET["supplier_id"]);
    $supplier_id_value = $supplier_id == "none" ? 0 : $supplier_id;
    $where .= " AND `supplier_id` = \"" . $supplier_id_value . "\" ";
}
if(!empty($_GET["category_id"])) {
    $category_id = check_string($_GET["category_id"]);
    $where .= " AND `category_id` = \"" . $category_id . "\" ";
}
if(!empty($_GET["username"])) {
    $username = check_string($_GET["username"]);
    if($idUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `username` = '" . $username . "' ")) {
        $where .= " AND `user_id` =  \"" . $idUser["id"] . "\" ";
    } else {
        $where .= " AND `user_id` =  \"\" ";
    }
}
if(!empty($_GET["user_id"])) {
    $user_id = check_string($_GET["user_id"]);
    $where .= " AND `user_id` = \"" . $user_id . "\" ";
}
if(!empty($_GET["name"])) {
    $name = check_string($_GET["name"]);
    $where .= " AND `name` LIKE \"%" . $name . "%\" ";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `products` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `products` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("products&limit=" . $limit . "&shortByDate=" . $shortByDate . "&user_id=" . $user_id . "&name=" . $name . "&create_gettime=" . $create_gettime . "&username=" . $username . "&category_id=" . $category_id . "&supplier_id=" . $supplier_id . "&status=" . $status . "&code=" . $code . "&"), $from, $totalDatatable, $limit);
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-cart-shopping\"></i> Products</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH SẢN PHẨM\n                        </div>\n                        <div class=\"d-flex\">\n                            <a type=\"button\" href=\"";
echo base_url_admin("product-add");
echo "\"\n                                class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                    class=\"ri-add-line fw-semibold align-middle\"></i> Thêm sản phẩm mới</a>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"";
echo base_url_admin();
echo "\" class=\"align-items-center mb-3\" name=\"formSearch\"\n                            method=\"GET\">\n                            <div class=\"row g-2 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"products\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" value=\"";
echo $user_id;
echo "\" name=\"user_id\"\n                                        placeholder=\"ID User\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" value=\"";
echo $username;
echo "\" name=\"username\"\n                                        placeholder=\"Username\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" value=\"";
echo $name;
echo "\" name=\"name\"\n                                        placeholder=\"Tên sản phẩm\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control\" value=\"";
echo $code;
echo "\" name=\"code\"\n                                        placeholder=\"Mã kho hàng\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <select class=\"form-control\" name=\"status\">\n                                        <option value=\"\">";
echo __("-- Trạng thái --");
echo "</option>\n                                        <option ";
echo $status == 1 ? "selected" : "";
echo " value=\"1\">Hiển Thị</option>\n                                        <option ";
echo $status == 2 ? "selected" : "";
echo " value=\"2\">Ẩn</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <select class=\"form-control\" name=\"category_id\">\n                                        <option value=\"\">";
echo __("-- Chuyên mục --");
echo "</option>\n                                        ";
foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = 0 ") as $option) {
    echo "                                        <option disabled value=\"";
    echo $option["id"];
    echo "\">";
    echo $option["name"];
    echo "</option>\n                                        ";
    foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = '" . $option["id"] . "' ") as $option1) {
        echo "                                        <option ";
        echo $category_id == $option1["id"] ? "selected" : "";
        echo "                                            value=\"";
        echo $option1["id"];
        echo "\">__";
        echo $option1["name"];
        echo "</option>\n                                        ";
    }
    echo "                                        ";
}
echo "                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <select class=\"form-control\" name=\"supplier_id\">\n                                        <option value=\"\">";
echo __("-- API Supplier --");
echo "</option>\n                                        <option value=\"none\" ";
echo $supplier_id == "none" ? "selected" : "";
echo ">\n                                            ";
echo __("Sản phẩm hệ thống");
echo "</option>\n                                        ";
foreach ($CMSNT->get_list("SELECT * FROM `suppliers` ") as $supplier) {
    echo "                                        <option ";
    echo $supplier_id == $supplier["id"] ? "selected" : "";
    echo "                                            value=\"";
    echo $supplier["id"];
    echo "\">";
    echo $supplier["domain"];
    echo "</option>\n                                        ";
}
echo "                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control\" id=\"daterange\"\n                                        value=\"";
echo $create_gettime;
echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-wave btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-wave btn-outline-danger btn-sm\" href=\"";
echo base_url_admin("products");
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive table-wrapper mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                                    id=\"check_all_checkbox_product\" value=\"option1\">\n                                            </div>\n                                        </th>\n                                        <th>Ưu tiên</th>\n                                        <th class=\"text-center\">";
echo __("Thao tác");
echo "</th>\n                                        <th>";
echo __("Sản phẩm");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Chuyên mục");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Trạng thái");
echo "</th>\n                                        <!-- <th class=\"text-center\">";
echo __("Ảnh");
echo "</th> -->\n                                        <th class=\"text-center\">";
echo __("Giá bán");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Chi tiết");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Seller");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Thời gian");
echo "</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
foreach ($listDatatable as $product) {
    echo "                                    <tr>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input checkbox_product\"\n                                                    data-id=\"";
    echo $product["id"];
    echo "\" name=\"checkbox_product\"\n                                                    value=\"";
    echo $product["id"];
    echo "\" />\n                                            </div>\n                                        </td>\n                                        <td width=\"100px\"><input onchange=\"post_update_stt_table_product(`";
    echo $product["id"];
    echo "`)\"\n                                                id=\"stt";
    echo $product["id"];
    echo "\" class=\"form-control\" type=\"number\"\n                                                value=\"";
    echo $product["stt"];
    echo "\"></td>\n                                        <td>\n                                            ";
    if($product["supplier_id"] == 0) {
        echo "                                            <a type=\"button\"\n                                                href=\"";
        echo base_url_admin("product-stock&code=" . $product["code"]);
        echo "\"\n                                                class=\"btn btn-warning-gradient btn-wave btn-sm\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
        echo __("Kho hàng");
        echo "\">\n                                                <i class=\"fa-solid fa-cart-shopping\"></i> Kho hàng\n                                            </a>\n                                            ";
    } else {
        echo "                                            <a type=\"button\"\n                                                href=\"";
        echo base_url_admin("product-api-manager&id=" . $product["supplier_id"]);
        echo "\"\n                                                class=\"btn btn-primary-gradient btn-wave btn-sm\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
        echo __("Quản lý API");
        echo "\">\n                                                <i class=\"fa-solid fa-bars-progress\"></i> Quản lý API\n                                            </a>\n                                            ";
    }
    echo "                                            <a type=\"button\"\n                                                href=\"";
    echo base_url_admin("product-edit&id=" . $product["id"]);
    echo "\"\n                                                class=\"btn btn-sm btn-secondary shadow-secondary btn-wave\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
    echo __("Chỉnh sửa");
    echo "\">\n                                                <i class=\"fa-solid fa-pen-to-square\"></i>\n                                            </a>\n                                            <a type=\"button\" onclick=\"remove('";
    echo $product["id"];
    echo "')\"\n                                                class=\"btn btn-sm btn-danger shadow-danger btn-wave\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
    echo __("Xóa");
    echo "\">\n                                                <i class=\"fas fa-trash\"></i>\n                                            </a>\n                                        </td>\n                                        <td>\n                                            <small><a\n                                                    href=\"";
    echo base_url_admin("product-edit&id=" . $product["id"]);
    echo "\">";
    echo $product["name"];
    echo "</a>\n                                        </td>\n                                        <td class=\"text-center\"><span\n                                                class=\"badge bg-primary\">";
    echo $product["category_id"] != 0 ? getRowRealtime("categories", $product["category_id"], "name") : "";
    echo "</span>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-switch form-check-lg\"\n                                                onchange=\"post_update_status_table_product(`";
    echo $product["id"];
    echo "`)\">\n                                                <input class=\"form-check-input\" type=\"checkbox\"\n                                                    id=\"status";
    echo $product["id"];
    echo "\" value=\"1\"\n                                                    ";
    echo $product["status"] == 1 ? "checked=\"\"" : "";
    echo ">\n                                            </div>\n                                        </td>\n                                        <!-- <td class=\"text-center\">\n                                            <div class=\"js-gallery img-fluid-100\">\n                                                ";
    $i = 0;
    foreach (explode(PHP_EOL, $product["images"]) as $image) {
        echo "                                                ";
        $i++;
        echo "                                                <div class=\"animated fadeIn\"\n                                                    ";
        echo 1 < $i ? "style=\"display:none;\"" : "";
        echo ">\n                                                    <a class=\"glightbox\" data-gallery=\"gallery1\"\n                                                        href=\"";
        echo base_url(dirImageProduct($image));
        echo "\">\n                                                        <img style=\"width:40px;\"\n                                                            src=\"";
        echo base_url(dirImageProduct($image));
        echo "\" alt=\"\">\n                                                    </a>\n                                                </div>\n                                                ";
    }
    echo "                                            </div>\n                                        </td> -->\n                                        <td>\n                                            Giá bán: <b\n                                                style=\"color:red;\">";
    echo format_currency($product["price"]);
    echo "</b><br>\n                                            Giá vốn: <b style=\"color:blue;\">";
    echo format_currency($product["cost"]);
    echo "</b>\n                                        </td>\n                                        <td>\n                                            <span style=\"color:green;\">Live</span>:\n                                            <b>";
    echo format_cash($product["supplier_id"] == 0 ? getStock($product["code"]) : $product["api_stock"]);
    echo "</b><br>\n                                            <span style=\"color:red;\">Die</span>:\n                                            <b>";
    echo format_cash($CMSNT->get_row(" SELECT COUNT(id) FROM product_die WHERE `product_code` = '" . $product["code"] . "' ")["COUNT(id)"]);
    echo "</b><br>\n                                            Đã bán: <b>";
    echo format_cash($product["sold"]);
    echo "</b>\n                                        </td>\n                                        <td class=\"text-center\"><a class=\"text-primary\"\n                                                href=\"";
    echo base_url_admin("user-edit&id=" . $product["user_id"]);
    echo "\">";
    echo getRowRealtime("users", $product["user_id"], "username");
    echo "                                                [ID ";
    echo $product["user_id"];
    echo "]</a>\n                                        </td>\n                                        <td><small>";
    echo $product["create_gettime"];
    echo "</small></td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                                <tfoot>\n                                    <td colspan=\"10\">\n                                        <div class=\"btn-list\">\n                                            <button type=\"button\" id=\"btn_cap_nhat_nhanh\"\n                                                class=\"btn btn-outline-primary shadow-primary btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-pen-to-square\"></i> CẬP NHẬT NHANH</button>\n                                            <button type=\"button\" id=\"btn_delete_product\"\n                                                class=\"btn btn-outline-danger shadow-danger btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-trash\"></i> XÓA SẢN PHẨM</button>\n                                        </div>\n                                    </td>\n                                </tfoot>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                        <p>Lưu ý: Ưu tiên càng cao, sản phẩm càng hiển thị trên cùng</p>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "<script>\n\$(function() {\n    \$('#check_all_checkbox_product').on('click', function() {\n        \$('.checkbox_product').prop('checked', this.checked);\n    });\n    \$('.checkbox_product').on('click', function() {\n        \$('#check_all_checkbox_product').prop('checked', \$('.checkbox_product:checked')\n            .length === \$('.checkbox_product').length);\n    });\n});\n</script>\n<script>\nfunction post_update_stt_table_product(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'update_stt_table_product',\n            id: id,\n            stt: \$('#stt' + id).val()\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n</script>\n<script>\nfunction post_cap_nhat_nhanh(id, category_id, status, discount) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'cap_nhat_san_pham_nhanh',\n            id: id,\n            category_id: category_id,\n            status: status,\n            discount: discount\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, 'success');\n            } else {\n                showMessage(result.msg, 'error');\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n\n\nfunction post_update_status_table_product(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'update_status_product',\n            id: id,\n            status: \$('#status' + id + ':checked').val()\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, 'success');\n            } else {\n                showMessage(result.msg, 'error');\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n\nfunction post_remove_product(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            id: id,\n            action: 'removeProduct'\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, 'success');\n            } else {\n                showMessage(result.msg, 'error');\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n\nfunction remove(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác Nhận Xóa sản phẩm\",\n        message: \"Bạn có chắc chắn muốn xóa sản phẩm ID \" + id + \" không ?\",\n        confirmText: \"Đồng Ý\",\n        cancelText: \"Hủy\"\n    }).then((e) => {\n        if (e) {\n            post_remove_product(id);\n            setTimeout(function() {\n                location.reload();\n            }, 500);\n        }\n    })\n}\n</script>\n\n\n\n<script>\n\$(document).ready(function() {\n\n    \$(\"#btn_cap_nhat_nhanh\").click(function() {\n        var checkboxes = document.querySelectorAll('input[name=\"checkbox_product\"]:checked');\n        if (checkboxes.length === 0) {\n            showMessage('Vui lòng chọn ít nhất một sản phẩm.', 'error');\n            return;\n        }\n        \$(\".checkboxeslength\").html(checkboxes.length);\n        \$(\"#modal_cap_nhat_nhanh\").modal('show');\n    });\n\n    \$(\"#btn_delete_product\").click(function() {\n        var checkboxes = document.querySelectorAll('input[name=\"checkbox_product\"]:checked');\n        if (checkboxes.length === 0) {\n            showMessage('Vui lòng chọn ít nhất một sản phẩm.', 'error');\n            return;\n        }\n        Swal.fire({\n            title: \"Bạn có chắc không?\",\n            text: \"Hệ thống sẽ xóa \" + checkboxes.length +\n                \" sản phẩm bạn chọn khi nhấn Đồng Ý\",\n            icon: \"warning\",\n            showCancelButton: true,\n            confirmButtonColor: \"#3085d6\",\n            cancelButtonColor: \"#d33\",\n            confirmButtonText: \"Đồng ý\",\n            cancelButtonText: \"Đóng\"\n        }).then((result) => {\n            if (result.isConfirmed) {\n                delete_records();\n            }\n        });\n    });\n\n});\n</script>\n\n<script>\nfunction cap_nhat_nhanh_records() {\n    \$('#cap_nhat_nhanh_records').html('<i class=\"fa fa-spinner fa-spin\"></i>').prop('disabled',\n        true);\n    var category_id = document.getElementById('category_id').value;\n    var status = document.getElementById('status').value;\n    var discount = document.getElementById('discount').value;\n    var checkbox = document.getElementsByName('checkbox_product');\n    // Sử dụng hàm đệ quy để thực hiện lần lượt từng postUpdate với thời gian chờ 100ms\n    function postUpdatesSequentially(index) {\n        if (index < checkbox.length) {\n            if (checkbox[index].checked === true) {\n                post_cap_nhat_nhanh(checkbox[index].value, category_id, status, discount);\n            }\n            setTimeout(function() {\n                postUpdatesSequentially(index + 1);\n            }, 100);\n        } else {\n            Swal.fire({\n                title: \"Thành công!\",\n                text: \"Cập nhật thành công\",\n                icon: \"success\"\n            });\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n            \$('#cap_nhat_nhanh_records').html('<i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "').prop(\n                'disabled',\n                false);\n        }\n    }\n    // Bắt đầu gọi hàm đệ quy từ index 0\n    postUpdatesSequentially(0);\n}\n\nfunction delete_records() {\n    var checkbox = document.getElementsByName('checkbox_product');\n\n    function postUpdatesSequentially(index) {\n        if (index < checkbox.length) {\n            if (checkbox[index].checked === true) {\n                post_remove_product(checkbox[index].value);\n            }\n            setTimeout(function() {\n                postUpdatesSequentially(index + 1);\n            }, 100);\n        } else {\n            Swal.fire({\n                title: \"Thành công!\",\n                text: \"Xóa sản phẩm thành công\",\n                icon: \"success\"\n            });\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    }\n    postUpdatesSequentially(0);\n}\n</script>\n<div class=\"modal fade\" id=\"modal_cap_nhat_nhanh\" tabindex=\"-1\" aria-labelledby=\"Cập nhật nhanh\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Cập nhật nhanh toàn bộ <mark\n                        class=\"checkboxeslength\"></mark> sản phẩm đã chọn</h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <div class=\"modal-body\">\n                <div class=\"row mb-4\">\n                    <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Chuyên mục:");
echo "</label>\n                    <div class=\"col-sm-8\">\n                        <select class=\"form-control\" id=\"category_id\" required>\n                            <option value=\"\">";
echo __("Giữ nguyên chuyên mục hiện tại");
echo "</option>\n                            ";
foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = 0 ") as $option) {
    echo "                            <option disabled value=\"";
    echo $option["id"];
    echo "\">";
    echo $option["name"];
    echo "</option>\n                            ";
    foreach ($CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = '" . $option["id"] . "' ") as $option1) {
        echo "                            <option value=\"";
        echo $option1["id"];
        echo "\">__";
        echo $option1["name"];
        echo "</option>\n                            ";
    }
    echo "                            ";
}
echo "                        </select>\n                    </div>\n                </div>\n                <div class=\"row mb-4\">\n                    <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Trạng thái:");
echo "</label>\n                    <div class=\"col-sm-8\">\n                        <select class=\"form-control\" id=\"status\" required>\n                            <option value=\"\">Giữ nguyên trạng thái hiện tại</option>\n                            <option value=\"ON\">ON</option>\n                            <option value=\"OFF\">OFF</option>\n                        </select>\n                    </div>\n                </div>\n                <div class=\"row mb-4\">\n                    <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Giảm giá:");
echo "</label>\n                    <div class=\"col-sm-8\">\n                        <div class=\"input-group\">\n                            <input type=\"text\" class=\"form-control text-center\"\n                                id=\"discount\" placeholder=\"Để trống nếu muốn giữ nguyên mặc định\">\n                            <span class=\"input-group-text\">%</span>\n                        </div>\n                    </div>\n                </div>\n                <p>Khi bạn nhấn vào nút UPDATE đồng nghĩa các sản phẩm mà bạn đã chọn sẽ được cập nhật thông tin trên.\n                </p>\n\n            </div>\n            <div class=\"modal-footer\">\n                <button type=\"button\" class=\"btn btn-light\" data-bs-dismiss=\"modal\">Close</button>\n                <button type=\"button\" onclick=\"cap_nhat_nhanh_records()\" id=\"cap_nhat_nhanh_records\"\n                    class=\"btn btn-primary\"><i class=\"fa fa-solid fa-save\"></i> ";
echo __("Update");
echo "</button>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n<script>\nvar lightboxVideo = GLightbox({\n    selector: '.glightbox'\n});\n</script>";

?>