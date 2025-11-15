<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Sản phẩm yêu thích") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n";
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
$time = "";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `favorites` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `favorites` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination_client(base_url("?action=favorites&limit=" . $limit . "&shortByDate=" . $shortByDate . "&"), $from, $totalDatatable, $limit);
echo "<section class=\"inner-section single-banner\"\n    style=\"background: url(";
echo base_url($CMSNT->site("banner_singer"));
echo ") no-repeat center;\">\n    <div class=\"container\">\n        <h2>";
echo __("Yêu thích");
echo "</h2>\n        <ol class=\"breadcrumb\">\n            <li class=\"breadcrumb-item\"><a href=\"index.html\">";
echo __("Trang chủ");
echo "</a></li>\n            <li class=\"breadcrumb-item active\" aria-current=\"page\">";
echo __("Yêu thích");
echo "</li>\n        </ol>\n    </div>\n</section>\n<section class=\"inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row content-reverse\">\n            <div class=\"col-lg-12\">\n                <div class=\"row\">\n                    <div class=\"col-lg-12\">\n                        <div class=\"account-card\">\n                            <h4 class=\"account-title\">";
echo __("Danh sách sản phẩm yêu thích");
echo "</h4>\n                            <form action=\"";
echo base_url();
echo "\" method=\"GET\">\n                                <input type=\"hidden\" name=\"action\" value=\"favorites\">\n                                <div class=\"top-filter\">\n                                    <div class=\"filter-show\"><label class=\"filter-label\">Show :</label>\n                                        <select name=\"limit\" onchange=\"this.form.submit()\"\n                                            class=\"form-select filter-select\">\n                                            <option ";
echo $limit == 5 ? "selected" : "";
echo " value=\"5\">5</option>\n                                            <option ";
echo $limit == 10 ? "selected" : "";
echo " value=\"10\">10</option>\n                                            <option ";
echo $limit == 20 ? "selected" : "";
echo " value=\"20\">20</option>\n                                            <option ";
echo $limit == 50 ? "selected" : "";
echo " value=\"50\">50</option>\n                                            <option ";
echo $limit == 100 ? "selected" : "";
echo " value=\"100\">100</option>\n                                            <option ";
echo $limit == 500 ? "selected" : "";
echo " value=\"500\">500</option>\n                                            <option ";
echo $limit == 1000 ? "selected" : "";
echo " value=\"1000\">1000\n                                            </option>\n                                        </select>\n                                    </div>\n                                    <div class=\"filter-short\">\n                                        <label class=\"filter-label\">";
echo __("Short by Date:");
echo "</label>\n                                        <select name=\"shortByDate\" onchange=\"this.form.submit()\"\n                                            class=\"form-select filter-select\">\n                                            <option value=\"\">";
echo __("Tất cả");
echo "</option>\n                                            <option ";
echo $shortByDate == 1 ? "selected" : "";
echo " value=\"1\">\n                                                ";
echo __("Hôm nay");
echo "                                            </option>\n                                            <option ";
echo $shortByDate == 2 ? "selected" : "";
echo " value=\"2\">\n                                                ";
echo __("Tuần này");
echo "                                            </option>\n                                            <option ";
echo $shortByDate == 3 ? "selected" : "";
echo " value=\"3\">\n                                                ";
echo __("Tháng này");
echo "                                            </option>\n                                        </select>\n                                    </div>\n                                </div>\n                            </form>\n                            <div class=\"table-scroll\">\n                                <table class=\"table fs-sm text-nowrap mb-0\">\n                                    <thead>\n                                        <tr>\n                                            <th>";
echo __("Sản phẩm");
echo "</th>\n                                            <th class=\"text-center\">";
echo __("Kho hàng");
echo "</th>\n                                            <th class=\"text-center\">";
echo __("Giá");
echo "</th>\n                                            <th class=\"text-center\">";
echo __("Thời gian");
echo "</th>\n                                            <th>";
echo __("Thao tác");
echo "</th>\n                                        </tr>\n                                    </thead>\n                                    <tbody>\n                                        ";
foreach ($listDatatable as $favorite) {
    echo "                                        ";
    $product = $CMSNT->get_row(" SELECT * FROM `products` WHERE `id` = '" . $favorite["product_id"] . "' ");
    if(!$CMSNT->get_row(" SELECT * FROM `products` WHERE `id` = '" . $favorite["product_id"] . "' ")) {
    } else {
        echo "                                        <tr>\n                                            <td>\n                                                <h6 class=\"feature-name\"><a href=\"";
        echo base_url("product/" . $product["slug"]);
        echo "\">";
        echo $product["name"];
        echo "</a>\n                                                </h6>\n                                            </td>\n                                            <td class=\"text-center\">\n                                                <label class=\"label-text feat\">\n                                                    ";
        $stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
        echo "                                                    <b>";
        echo format_cash($stock);
        echo "</b></label>\n                                            </td>\n                                            <td class=\"text-right\">\n                                                <b\n                                                    style=\"color:red;\">";
        echo format_currency($product["price"]);
        echo "</b>\n                                            </td>\n                                            <td class=\"text-center\">";
        echo $favorite["create_gettime"];
        echo "</td>\n                                            <td>\n                                                <a class=\"btn btn-sm btn-primary\" onclick=\"openModal(`";
        echo isset($getUser) ? $getUser["token"] : NULL;
        echo "`, `";
        echo $favorite["product_id"];
        echo "`)\" type=\"button\"><i class=\"fa-solid fa-cart-shopping\"></i> ";
        echo __("Mua");
        echo "</a>\n                                                <a class=\"btn btn-sm btn-success\" href=\"";
        echo base_url("product/" . $product["slug"]);
        echo "\" type=\"button\"><i\n                                                        class=\"fas fa-eye\"></i> ";
        echo __("Xem");
        echo "</a>\n                                                <a class=\"btn btn-sm btn-danger\" onclick=\"remove(`";
        echo $favorite["id"];
        echo "`)\"\n                                                    type=\"button\" title=\"Remove Wishlist\"><i class=\"icofont-trash\"></i>\n                                                    ";
        echo __("Xóa");
        echo "</a>\n                                            </td>\n                                        </tr>\n                                        ";
    }
}
echo "                                    </tbody>\n                                </table>\n                            </div>\n                            ";
if($totalDatatable == 0) {
    echo "                            <div class=\"empty-state\">\n                                <svg width=\"184\" height=\"152\" viewBox=\"0 0 184 152\" xmlns=\"http://www.w3.org/2000/svg\">\n                                    <g fill=\"none\" fill-rule=\"evenodd\">\n                                        <g transform=\"translate(24 31.67)\">\n                                            <ellipse fill-opacity=\".8\" fill=\"#F5F5F7\" cx=\"67.797\" cy=\"106.89\"\n                                                rx=\"67.797\" ry=\"12.668\"></ellipse>\n                                            <path\n                                                d=\"M122.034 69.674L98.109 40.229c-1.148-1.386-2.826-2.225-4.593-2.225h-51.44c-1.766 0-3.444.839-4.592 2.225L13.56 69.674v15.383h108.475V69.674z\"\n                                                fill=\"#AEB8C2\"></path>\n                                            <path\n                                                d=\"M101.537 86.214L80.63 61.102c-1.001-1.207-2.507-1.867-4.048-1.867H31.724c-1.54 0-3.047.66-4.048 1.867L6.769 86.214v13.792h94.768V86.214z\"\n                                                fill=\"url(#linearGradient-1)\" transform=\"translate(13.56)\"></path>\n                                            <path\n                                                d=\"M33.83 0h67.933a4 4 0 0 1 4 4v93.344a4 4 0 0 1-4 4H33.83a4 4 0 0 1-4-4V4a4 4 0 0 1 4-4z\"\n                                                fill=\"#F5F5F7\"></path>\n                                            <path\n                                                d=\"M42.678 9.953h50.237a2 2 0 0 1 2 2V36.91a2 2 0 0 1-2 2H42.678a2 2 0 0 1-2-2V11.953a2 2 0 0 1 2-2zM42.94 49.767h49.713a2.262 2.262 0 1 1 0 4.524H42.94a2.262 2.262 0 0 1 0-4.524zM42.94 61.53h49.713a2.262 2.262 0 1 1 0 4.525H42.94a2.262 2.262 0 0 1 0-4.525zM121.813 105.032c-.775 3.071-3.497 5.36-6.735 5.36H20.515c-3.238 0-5.96-2.29-6.734-5.36a7.309 7.309 0 0 1-.222-1.79V69.675h26.318c2.907 0 5.25 2.448 5.25 5.42v.04c0 2.971 2.37 5.37 5.277 5.37h34.785c2.907 0 5.277-2.421 5.277-5.393V75.1c0-2.972 2.343-5.426 5.25-5.426h26.318v33.569c0 .617-.077 1.216-.221 1.789z\"\n                                                fill=\"#DCE0E6\"></path>\n                                        </g>\n                                        <path\n                                            d=\"M149.121 33.292l-6.83 2.65a1 1 0 0 1-1.317-1.23l1.937-6.207c-2.589-2.944-4.109-6.534-4.109-10.408C138.802 8.102 148.92 0 161.402 0 173.881 0 184 8.102 184 18.097c0 9.995-10.118 18.097-22.599 18.097-4.528 0-8.744-1.066-12.28-2.902z\"\n                                            fill=\"#DCE0E6\"></path>\n                                        <g transform=\"translate(149.65 15.383)\" fill=\"#FFF\">\n                                            <ellipse cx=\"20.654\" cy=\"3.167\" rx=\"2.849\" ry=\"2.815\"></ellipse>\n                                            <path d=\"M5.698 5.63H0L2.898.704zM9.259.704h4.985V5.63H9.259z\"></path>\n                                        </g>\n                                    </g>\n                                </svg>\n                                <p>";
    echo __("Không có dữ liệu");
    echo "</p>\n                            </div>\n                            ";
}
echo "                            <div class=\"bottom-paginate\">\n                                <p class=\"page-info\">Showing ";
echo $limit;
echo " of ";
echo $totalDatatable;
echo " Results</p>\n                                <div class=\"pagination\">\n                                    ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n";
if($CMSNT->site("menu_category_right") != 0) {
    echo "<ul id=\"top-menu-";
    echo $CMSNT->site("menu_category_right") == 1 ? "right" : "left";
    echo "\">\n    <li>\n        <a class=\"menu-item\" id=\"toggle-menu-button\">\n            <i class=\"fa-solid fa-eye-slash\"></i>\n            <span>";
    echo __("Đóng menu");
    echo "</span></a>\n    </li>\n    <li>\n        <a class=\"menu-item active\" href=\"";
    echo base_url("client/favorites");
    echo "\">\n            <i class=\"fa-solid fa-heart\" style=\"color:red;\"></i></i>\n            <span>";
    echo __("Sản phẩm yêu thích");
    echo "</span></a>\n    </li>\n    ";
    foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `status` = 1 AND `parent_id` != 0 ") as $category) {
        echo "    <li>\n        <a class=\"menu-item ";
        echo $category_id == $category["id"] ? "active" : "";
        echo "\"\n            href=\"";
        echo base_url("category/" . $category["slug"]);
        echo "\"><i>\n                <img alt=\"";
        echo $category["name"];
        echo "\" src=\"";
        echo base_url($category["icon"]);
        echo "\"></i> <span>";
        echo $category["name"];
        echo "</span></a>\n    </li>\n    ";
    }
    echo "</ul>\n<script>\n// JavaScript để ẩn/hiện menu khi click vào nút\nconst toggleMenuButton = document.getElementById('toggle-menu-button');\nconst topMenu = document.getElementById('top-menu-";
    echo $CMSNT->site("menu_category_right") == 1 ? "right" : "left";
    echo "');\ntoggleMenuButton.addEventListener('click', function() {\n    topMenu.classList.toggle('hidden');\n});\n</script>\n";
}
echo "<script>\nfunction remove(id) {\n    Swal.fire({\n        title: \"";
echo __("Bạn có chắc không?");
echo "\",\n        text: \"";
echo __("Hệ thống sẽ thực hiện xóa dữ liệu này nếu bạn nhấn đồng ý");
echo "\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"";
echo __("Đồng ý");
echo "\",\n        cancelButtonText: \"";
echo __("Hủy");
echo "\"\n    }).then((result) => {\n        if (result.isConfirmed) {\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/client/remove.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    id: id,\n                    token: '";
echo $getUser["token"];
echo "',\n                    action: 'removeFavorite'\n                },\n                success: function(respone) {\n                    if (respone.status == 'success') {\n                        Swal.fire({\n                            title: \"";
echo __("Thành công!");
echo "\",\n                            text: respone.msg,\n                            icon: \"success\"\n                        });\n                        location.reload();\n                    } else {\n                        Swal.fire({\n                            title: \"";
echo __("Thất bại!");
echo "\",\n                            text: respone.msg,\n                            icon: \"error\"\n                        });\n                    }\n                },\n                error: function() {\n                    alert(html(response));\n                    location.reload();\n                }\n            });\n        }\n    });\n}\n</script>\n\n<div class=\"modal fade\" id=\"openModal\" tabindex=\"-1\" aria-labelledby=\"modal-block-popout\" role=\"dialog\"\n    aria-hidden=\"true\">\n    <div class=\"modal-dialog modal-lg modal-dialog-popout\" role=\"document\">\n        <div class=\"modal-content\">\n            <div id=\"modalContent\"></div>\n        </div>\n    </div>\n</div>\n\n<script>\nfunction openModal(token, id) {\n    \$(\"#modalContent\").html('');\n    const url = `";
echo BASE_URL("ajaxs/client/modal/view-product.php?id=\${id}&token=\${token}");
echo "`;\n    const buttons = \$(\".btn-buy\");\n    // Thay đổi nội dung và vô hiệu hóa tất cả các button\n    buttons.each(function() {\n        const button = \$(this);\n        button.html('<i class=\"fa fa-spinner fa-spin\"></i>');\n        button.prop('disabled', true);\n    });\n    fetch(url)\n        .then(response => {\n            if (!response.ok) {\n                throw new Error(`AJAX request failed with status: \${response.status}`);\n            }\n            return response.text();\n        })\n        .then(data => {\n            \$(\"#modalContent\").html(data);\n            \$('#openModal').modal('show');\n        })\n        .catch(error => {\n            showMessage('AJAX request failed: ' + error.message, 'error');\n        });\n    // Khi yêu cầu hoàn tất, khôi phục lại nội dung và kích hoạt lại tất cả các button\n    buttons.each(function() {\n        const button = \$(this);\n        button.html('<i class=\"fa-solid fa-cart-shopping\"></i> ";
echo __("MUA NGAY");
echo "');\n        button.prop('disabled', false);\n    });\n}\n</script>\n\n\n";
require_once __DIR__ . "/footer.php";

?>