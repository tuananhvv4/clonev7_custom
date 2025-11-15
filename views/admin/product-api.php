<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Quản lý kết nối API") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "manager_suppliers")) {
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
$shortByDate = "";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `suppliers` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `suppliers` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("product-api&limit=" . $limit . "&shortByDate=" . $shortByDate . "&"), $from, $totalDatatable, $limit);
$doanh_thu = $CMSNT->get_row(" SELECT SUM(pay) FROM product_order WHERE `refund` = 0 AND supplier_id != 0 ")["SUM(pay)"];
$tien_von = $CMSNT->get_row(" SELECT SUM(cost) FROM product_order WHERE `refund` = 0 AND supplier_id != 0 ")["SUM(cost)"];
$loi_nhuan = $doanh_thu - $tien_von;
$don_hang = $CMSNT->get_row(" SELECT COUNT(id) FROM product_order WHERE `refund` = 0 AND supplier_id != 0 ")["COUNT(id)"];
echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-code\"></i> Kết nối API</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-top\">\n                            <div class=\"me-3\">\n                                <span class=\"avatar avatar-md p-2 bg-primary\">\n                                    <i class=\"fa-solid fa-cart-shopping fs-16\"></i>\n                                </span>\n                            </div>\n                            <div class=\"flex-fill\">\n                                <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                    <h5 class=\"fw-semibold mb-0 lh-1\">\n                                        ";
echo format_cash($don_hang);
echo "                                    </h5>\n                                </div>\n                                <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">ĐƠN HÀNG</p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-top\">\n                            <div class=\"me-3\">\n                                <span class=\"avatar avatar-md p-2 bg-info\">\n                                    <i class=\"fa-solid fa-money-bill-1 fs-16\"></i>\n                                </span>\n                            </div>\n                            <div class=\"flex-fill\">\n                                <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                    <h5 class=\"fw-semibold mb-0 lh-1\">\n                                        ";
echo format_currency($doanh_thu);
echo "                                    </h5>\n                                </div>\n                                <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">DOANH THU ĐƠN HÀNG</p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-top\">\n                            <div class=\"me-3\">\n                                <span class=\"avatar avatar-md p-2 bg-warning\">\n                                    <i class=\"fa-solid fa-money-bill-1 fs-16\"></i>\n                                </span>\n                            </div>\n                            <div class=\"flex-fill\">\n                                <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                    <h5 class=\"fw-semibold mb-0 lh-1\">\n                                        ";
echo format_currency($tien_von);
echo "                                    </h5>\n                                </div>\n                                <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">GIÁ VỐN</p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <div class=\"d-flex align-items-top\">\n                            <div class=\"me-3\">\n                                <span class=\"avatar avatar-md p-2 bg-success\">\n                                    <i class=\"fa-solid fa-money-bill-1 fs-16\"></i>\n                                </span>\n                            </div>\n                            <div class=\"flex-fill\">\n                                <div class=\"d-flex mb-1 align-items-top justify-content-between\">\n                                    <h5 class=\"fw-semibold mb-0 lh-1\">\n                                        ";
echo format_currency($loi_nhuan);
echo "                                    </h5>\n                                </div>\n                                <p class=\"mb-0 fs-10 op-7 text-muted fw-semibold\">LỢI NHUẬN</p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"row\">\n        <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header\">\n                        <div class=\"card-title\">THỐNG KÊ ĐƠN HÀNG THÁNG ";
echo date("m");
echo "</div>\n                    </div>\n                    <div class=\"card-body\">\n                        <canvas id=\"chartjs-line\" class=\"chartjs-chart\"></canvas>\n                        <script>\n                        (function() {\n                            document.addEventListener('DOMContentLoaded', function() {\n                                setTimeout(function() {\n                                    Chart.defaults.borderColor = \"rgba(142, 156, 173,0.1)\";\n                                    Chart.defaults.color = \"#8c9097\";\n\n                                    \$.ajax({\n                                        url: '";
echo base_url("ajaxs/admin/view.php");
echo "',\n                                        method: 'POST',\n                                        dataType: 'json',\n                                        data: {\n                                            action: 'view_chart_thong_ke_don_hang_api_thang',\n                                            token: '";
echo $getUser["token"];
echo "'\n                                        },\n                                        success: function(response) {\n                                            const labels = response.labels;\n                                            const revenues = response.revenues;\n                                            const profits = response.profits;\n\n                                            const data = {\n                                                labels: labels,\n                                                datasets: [{\n                                                        label: 'Doanh thu',\n                                                        backgroundColor: 'rgb(132, 90, 223)',\n                                                        borderColor: 'rgb(132, 90, 223)',\n                                                        data: revenues,\n                                                    },\n                                                    {\n                                                        label: 'Lợi nhuận',\n                                                        backgroundColor: 'rgb(73,182,245)',\n                                                        borderColor: 'rgb(73,182,245)',\n                                                        data: profits,\n                                                    }\n                                                ]\n                                            };\n\n                                            const config = {\n                                                type: 'bar',\n                                                data: data,\n                                                options: {}\n                                            };\n\n                                            const myChart = new Chart(\n                                                document.getElementById(\n                                                    'chartjs-line'),\n                                                config\n                                            );\n                                        }\n                                    });\n                                }, 5);\n                            });\n                        })();\n                        </script>\n                    </div>\n                </div>\n            </div>\n             \n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH API ĐANG KẾT NỐI\n                        </div>\n                        <div class=\"d-flex\">\n                            <a type=\"button\" href=\"";
echo base_url_admin("product-api-add");
echo "\"\n                                class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                    class=\"ri-add-line fw-semibold align-middle\"></i> THÊM WEBSITE API</a>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"";
echo base_url_admin();
echo "\" class=\"align-items-center mb-3\" name=\"formSearch\"\n                            method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"product-api\">\n                            </div>\n                            <div class=\"top-filter\">\n                                <div class=\"filter-show\">\n                                    <label class=\"filter-label\">Show :</label>\n                                    <select name=\"limit\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option ";
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th>";
echo __("Website");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Type");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Số dư");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Thống kê");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Chi tiết");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Trạng thái");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Thao tác");
echo "</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
foreach ($listDatatable as $supplier) {
    echo "                                    ";
    $query = "SELECT \n                                        SUM(pay) AS total_pay, \n                                        SUM(cost) AS total_cost, \n                                        COUNT(id) AS total_orders \n                                        FROM product_order \n                                        WHERE supplier_id = '" . $supplier["id"] . "'";
    $result = $CMSNT->get_row($query);
    $doanh_thu = $result["total_pay"];
    $loi_nhuan = $doanh_thu - $result["total_cost"];
    $don_hang = $result["total_orders"];
    echo "                                    <tr onchange=\"updateForm(`";
    echo $supplier["id"];
    echo "`)\">\n                                        <td>\n                                            <i class=\"fa-solid fa-link\"></i> Domain: <a class=\"text-primary\"\n                                                href=\"";
    echo $supplier["domain"];
    echo "\"\n                                                target=\"_blank\">";
    echo $supplier["domain"];
    echo "</a><br>\n                                            <i class=\"fa-solid fa-user\"></i> Username:\n                                            <strong>";
    echo substr($supplier["username"], 0, 4);
    echo "...</strong> <i\n                                                class=\"fa-solid fa-lock\"></i> Password:\n                                            <strong>";
    echo substr($supplier["password"], 0, 4);
    echo "...</strong><br>\n                                            <i class=\"fa-solid fa-key\"></i> API Key:\n                                            <strong>";
    echo substr($supplier["api_key"], 0, 12);
    echo "...</strong><br>\n                                            <i class=\"fa-solid fa-key\"></i> Token:\n                                            <strong>";
    echo substr($supplier["token"], 0, 12);
    echo "...</strong>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <span class=\"badge bg-primary\">";
    echo $supplier["type"];
    echo "</span>\n                                        </td>\n                                        <td class=\"text-right\">\n                                            ";
    echo check_string($supplier["price"]);
    echo "                                        </td>\n                                        <td>\n                                            <span class=\"badge bg-outline-warning\">Chuyên mục:\n                                                ";
    echo format_cash($CMSNT->get_row(" SELECT COUNT(id) FROM `categories` WHERE `supplier_id` = '" . $supplier["id"] . "' ")["COUNT(id)"]);
    echo "</span><br>\n                                            <span class=\"badge bg-outline-primary\">Sản phẩm:\n                                                ";
    echo format_cash($CMSNT->get_row(" SELECT COUNT(id) FROM `products` WHERE `supplier_id` = '" . $supplier["id"] . "' ")["COUNT(id)"]);
    echo "</span><br>\n                                            <span class=\"badge bg-outline-info\">Đơn hàng:\n                                                ";
    echo format_cash($don_hang);
    echo "</span><br>\n                                            <span class=\"badge bg-outline-danger\">Doanh thu:\n                                                ";
    echo format_currency($doanh_thu);
    echo "</span><br>\n                                            <span class=\"badge bg-outline-success\">Lợi nhuận:\n                                                ";
    echo format_currency($loi_nhuan);
    echo "</span>\n                                        </td>\n                                        <td>\n                                            Tăng giá: <span\n                                                class=\"badge bg-outline-primary\">";
    echo $supplier["discount"];
    echo "%</span><br>\n                                            Cập nhật tên sản phẩm:\n                                            ";
    echo $supplier["update_name"] == "ON" ? "<span class=\"badge bg-success\">ON</span>" : "<span class=\"badge bg-danger\">OFF</span>";
    echo "<br>\n                                            Cập nhật giá bán:\n                                            ";
    echo $supplier["update_price"] == "ON" ? "<span class=\"badge bg-success\">ON</span>" : "<span class=\"badge bg-danger\">OFF</span>";
    echo "<br>\n                                            Làm tròn giá bán:\n                                            ";
    echo $supplier["roundMoney"] == "ON" ? "<span class=\"badge bg-success\">ON</span>" : "<span class=\"badge bg-danger\">OFF</span>";
    echo "<br>\n                                            Đồng bộ chuyên mục API:\n                                            ";
    echo $supplier["sync_category"] == "ON" ? "<span class=\"badge bg-success\">ON</span>" : "<span class=\"badge bg-danger\">OFF</span>";
    echo "                                        </td>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-switch form-check-lg\">\n                                                <input class=\"form-check-input\" type=\"checkbox\"\n                                                    id=\"status";
    echo $supplier["id"];
    echo "\" value=\"1\"\n                                                    ";
    echo $supplier["status"] == 1 ? "checked=\"\"" : "";
    echo ">\n                                            </div>\n                                        </td>\n                                        <td>\n                                            <a type=\"button\"\n                                                href=\"";
    echo base_url_admin("product-api-manager&id=" . $supplier["id"]);
    echo "\"\n                                                class=\"btn btn-sm btn-primary shadow-primary btn-wave\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
    echo __("Quản lý API");
    echo "\">\n                                                <i class=\"fa-solid fa-bars-progress\"></i> Manager\n                                            </a>\n                                            <a type=\"button\"\n                                                href=\"";
    echo base_url_admin("product-api-edit&id=" . $supplier["id"]);
    echo "\"\n                                                class=\"btn btn-sm btn-info shadow-info btn-wave\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
    echo __("Chỉnh sửa");
    echo "\">\n                                                <i class=\"fas fa-edit\"></i> Edit\n                                            </a>\n                                            <a type=\"button\"\n                                                href=\"";
    echo base_url_admin("product-orders&supplier_id=" . $supplier["id"]);
    echo "\"\n                                                class=\"btn btn-sm btn-success shadow-success btn-wave\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
    echo __("Đơn hàng");
    echo "\">\n                                                <i class=\"fa-solid fa-cart-shopping\"></i> Đơn hàng\n                                            </a>\n                                            <a type=\"button\" onclick=\"removeItem('";
    echo $supplier["id"];
    echo "')\"\n                                                class=\"btn btn-sm btn-danger shadow-danger btn-wave\"\n                                                data-bs-toggle=\"tooltip\" title=\"";
    echo __("Xóa");
    echo "\">\n                                                <i class=\"fas fa-trash\"></i> Delete\n                                            </a>\n                                        </td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n\n\n\n        <style>\n        .brand-carousel {\n            width: 100%;\n            overflow: hidden;\n            animation: moveCards 25s linear infinite;\n            white-space: nowrap;\n        }\n\n        .brand-carousel-container {\n            width: 100%;\n            overflow-x: auto;\n        }\n\n        .brand-carousel {\n            white-space: nowrap;\n            font-size: 0;\n            width: max-content\n                /* Đảm bảo rằng .brand-carousel có đủ rộng để chứa tất cả các .brand-card trên cùng một hàng */\n        }\n\n        .brand-card {\n            font-size: 16px;\n            display: inline-block;\n            vertical-align: top;\n            margin-right: 20px;\n        }\n\n        /* Các phần còn lại giữ nguyên */\n\n        .brand-carousel:hover {\n            animation-play-state: paused;\n        }\n\n        @keyframes moveCards {\n            0% {\n                transform: translateX(0%);\n            }\n\n            100% {\n                transform: translateX(-100%);\n            }\n        }\n\n        .brand-card {\n            position: relative;\n            display: inline-block;\n            margin: 10px;\n            vertical-align: middle;\n            background-color: #fff;\n            border-radius: 10px;\n            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);\n            padding: 20px;\n        }\n\n        .brand-card img {\n            width: 100px;\n        }\n\n        .connect-button,\n        .website-button {\n            position: absolute;\n            bottom: 10px;\n            left: 50%;\n            transform: translateX(-50%);\n            background-color: #007bff;\n            color: #fff;\n            padding: 5px 10px;\n            border: none;\n            border-radius: 5px;\n            cursor: pointer;\n            opacity: 0;\n            transition: opacity 0.3s ease;\n        }\n\n        .brand-card:hover .connect-button,\n        .brand-card:hover .website-button {\n            opacity: 1;\n        }\n\n        .website-button {\n            bottom: 40px;\n        }\n        </style>\n        <div class=\"row justify-content-center py-3\">\n            <center>\n                <h5><i class=\"fa-solid fa-boxes-packing\"></i> Nhà cung cấp gợi ý</h5>\n            </center>\n            <div class=\"brand-carousel-container\">\n                <div class=\"brand-carousel animated-carousel\">\n\n                </div>\n            </div>\n            <p id=\"notitcation_suppliers\"></p>\n        </div>\n        <script>\n        \$(document).ready(function() {\n            \$('.brand-carousel').html('');\n            \$.ajax({\n                url: 'https://api.cmsnt.co/suppliers.php',\n                type: 'GET',\n                dataType: 'json',\n                success: function(response) {\n                    // Xử lý dữ liệu trả về từ server\n                    if (response && response.suppliers.length > 0) {\n                        var html = '';\n                        \$.each(response.suppliers, function(index, brand) {\n                            html += '<div class=\"brand-card\">';\n                            html += '<img src=\"' + brand.logo + '\" alt=\"Logo\">';\n                            html +=\n                                '<a href=\"";
echo base_url_admin("product-api-add");
echo "&domain=' +\n                                brand.domain + '&type=' + brand.type +\n                                '\" class=\"connect-button btn btn-sm btn-danger\">Kết nối</a>';\n                            html += '<a href=\"' + brand.domain +\n                                '?utm_source=ads_cmsnt\" target=\"_blank\" class=\"website-button btn btn-sm btn-primary\">Xem</a>';\n                            html += '</div>';\n                        });\n                        \$('.brand-carousel').html(html);\n                        \$('#notitcation_suppliers').html(response.notication);\n                        calculateAndSetAnimationDuration();\n                    } else {\n                        \$('.brand-carousel').html('');\n                    }\n                },\n                error: function() {\n                    \$('.brand-carousel').html('');\n                }\n            });\n        });\n        // Function to calculate carousel width and set animation duration\n        function calculateAndSetAnimationDuration() {\n            var carousel = \$('.animated-carousel');\n            var carouselWidth = carousel[0].scrollWidth;\n            var cardWidth = carousel.children().first().outerWidth(true); // Including margin\n            var numberOfCards = carouselWidth / cardWidth;\n            var animationDuration = numberOfCards * 2; // Adjust this multiplier as needed\n            carousel.css('animation-duration', animationDuration + 's');\n        }\n        </script>\n\n\n\n        </div>\n\n    </div>\n</div>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nfunction updateForm(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'updateTableProductAPI',\n            id: id,\n            status: \$('#status' + id + ':checked').val()\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n\n\n\n\nvar lightboxVideo = GLightbox({\n    selector: '.glightbox'\n});\n</script>\n\n\n\n<div class=\"modal fade\" id=\"confirmDeleteModal\" tabindex=\"-1\" aria-labelledby=\"confirmDeleteModalLabel\"\n    aria-hidden=\"true\">\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"confirmDeleteModalLabel\"><i class=\"fa-solid fa-triangle-exclamation\"></i>\n                    Lưu ý!</h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <div class=\"modal-body\">\n                <p>Hệ thống sẽ xóa API này khỏi hệ thống bao gồm sản phẩm của API này và thống kê lợi nhuận của API này.\n                </p>\n                <div class=\"form-check form-check-lg d-flex align-items-center\">\n                    <input class=\"form-check-input\" type=\"checkbox\" value=\"\" id=\"confirmCheckbox\">\n                    <label class=\"form-check-label\" for=\"confirmCheckbox\">\n                        Tôi đồng ý và chấp nhận rũi ro trên\n                    </label>\n                </div>\n            </div>\n            <div class=\"modal-footer\">\n                <button type=\"button\" class=\"btn btn-light\" data-bs-dismiss=\"modal\">Cancel</button>\n                <button type=\"button\" class=\"btn btn-danger\" id=\"confirmDeleteButton\" disabled>Delete</button>\n            </div>\n        </div>\n    </div>\n</div>\n<script>\nfunction removeItem(id) {\n    \$('#confirmDeleteModal').modal('show');\n\n    \$('#confirmDeleteButton').off('click').on('click', function() {\n        if (\$('#confirmCheckbox').prop('checked')) {\n            \$('#confirmDeleteButton').html('<i class=\"fa fa-spinner fa-spin\"></i> Processing...').prop(\n                'disabled',\n                true);\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    id: id,\n                    action: 'removeSupplier'\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        Swal.fire({\n                            title: \"Thành công!\",\n                            text: result.msg,\n                            icon: \"success\"\n                        });\n                        setTimeout(function() {\n                            location.reload();\n                        }, 1000);\n                    } else {\n                        showMessage(result.msg, result.status);\n                        \$('#confirmDeleteButton').html('Delete').prop(\n                            'disabled',\n                            false);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    });\n\n    \$('#confirmCheckbox').off('change').on('change', function() {\n        if (\$(this).prop('checked')) {\n            \$('#confirmDeleteButton').prop('disabled', false);\n        } else {\n            \$('#confirmDeleteButton').prop('disabled', true);\n        }\n    });\n}\n</script>";

?>