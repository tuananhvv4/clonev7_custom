<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n";
$body["footer"] = "\n \n";
if(isset($_COOKIE["token"])) {
    $getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `token` = '" . check_string($_COOKIE["token"]) . "' ");
    if(!$getUser) {
        header("location: " . BASE_URL("client/logout"));
        exit;
    }
    $_SESSION["login"] = $getUser["token"];
}
if(isset($_SESSION["login"])) {
    require_once __DIR__ . "/../../models/is_user.php";
}
$order_by = " ORDER BY `stt` DESC ";
if($CMSNT->site("order_by_product_home") == 1) {
    $order_by = " ORDER BY `stt` DESC ";
} elseif($CMSNT->site("order_by_product_home") == 2) {
    $order_by = " ORDER BY `price` ASC ";
} elseif($CMSNT->site("order_by_product_home") == 2) {
    $order_by = " ORDER BY `price` DESC ";
}
if(isset($_GET["limit"])) {
    $limit = (int) check_string($_GET["limit"]);
} else {
    $limit = 20;
}
if(isset($_GET["page"])) {
    $page = check_string((int) $_GET["page"]);
} else {
    $page = 1;
}
$from = ($page - 1) * $limit;
$where = " `status` = 1 ";
$keyword = "";
$category_id = "";
$where_category = "";
if(isset($_GET["slug"])) {
    $slug = check_string($_GET["slug"]);
    if(!($category = $CMSNT->get_row("SELECT * FROM `categories` WHERE `slug` = '" . $slug . "' AND `status` = 1 "))) {
        $category_id = "";
    } else {
        $category_id = $category["id"];
        $where_category = "AND `id` = \"" . $category_id . "\" ";
        $body["title"] = $category["name"] . " | " . $CMSNT->site("title");
        $body["desc"] = $category["description"];
    }
}
if(!empty($_GET["category"])) {
    $category_id = check_string($_GET["category"]);
    $where_category = "AND `id` = \"" . $category_id . "\" ";
}
if(!empty($_GET["keyword"])) {
    $keyword = check_string($_GET["keyword"]);
    $where .= " AND `name` LIKE \"%" . $keyword . "%\" ";
}
$listDatatable = $CMSNT->get_list(" SELECT * FROM `products` WHERE " . $where . " " . $order_by . " LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `products` WHERE " . $where . " " . $order_by . " ");
$urlDatatable = pagination_client(base_url("?action=home&limit=" . $limit . "&keyword=" . $keyword . "&category=" . $category_id . "&"), $from, $totalDatatable, $limit);
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
echo "\n<section class=\"section feature-part\">\n    <div class=\"container\">\n        <div class=\"mb-5\">\n\n        </div>\n        <div class=\"row mb-5\">\n            ";
if($CMSNT->site("notice_home") != "") {
    echo "            <div class=\"col-md-12\">\n                <div class=\"account-card pt-3\">\n                    ";
    echo $CMSNT->site("notice_home");
    echo "                </div>\n            </div>\n            ";
}
echo "\n            ";
require_once __DIR__ . "/widget_tools.php";
echo "            <br>\n\n\n\n            <div class=\"";
echo $CMSNT->site("cot_so_du_ben_phai") == 1 ? "col-xl-9" : "col-xl-12";
echo "\">\n                ";
if($CMSNT->site("show_btn_category_home") == 1) {
    echo "                <ul class=\"custom-button-list\">\n                    <li><a class=\"btn-category-home ";
    echo $category_id == 0 ? "active" : "";
    echo "\" href=\"";
    echo base_url();
    echo "\"><i\n                                class=\"fa-solid fa-cart-shopping me-2\"></i>";
    echo __("Tất cả sản phẩm");
    echo "</a>\n                    </li>\n                    ";
    foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `status` = 1 AND `parent_id` != 0 ORDER BY `stt` DESC  ") as $category) {
        echo "                    <li><a class=\"btn-category-home ";
        echo $category_id == $category["id"] ? "active" : "";
        echo "\"\n                            href=\"";
        echo base_url("category/" . $category["slug"]);
        echo "\"><img\n                                src=\"";
        echo base_url($category["icon"]);
        echo "\" width=\"25px\" alt=\"";
        echo $category["name"];
        echo "\"\n                                class=\"me-2\">";
        echo $category["name"];
        echo "</a></li>\n                    ";
    }
    echo "                </ul>\n                ";
}
echo "                ";
if($keyword != "") {
    echo "                <div class=\"home-heading mb-3\">\n                    <h3>\n                        <i class=\"fa-solid fa-magnifying-glass me-2\"></i>\n                        ";
    echo __("Sản phẩm liên quan đến từ khóa");
    echo " '<strong style=\"color:red;\">";
    echo $keyword;
    echo "</strong>'\n                    </h3>\n                </div>\n                ";
    if($CMSNT->site("type_show_product") == "BOX") {
        echo "                <div\n                    class=\"row row-cols-1 row-cols-md-1 row-cols-lg-2 ";
        echo $CMSNT->site("cot_so_du_ben_phai") == 1 ? "row-cols-xl-2" : "row-cols-xl-3";
        echo "\">\n                    ";
        foreach ($listDatatable as $product) {
            echo "                    ";
            $stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
            echo "                    ";
            if($CMSNT->site("product_hide_outstock") == 1 && $stock == 0) {
            } else {
                echo "                    <div>\n                        <div class=\"feature-card ";
                echo $stock == 0 ? "product-disable" : "";
                echo "\">\n                            <div class=\"feature-content\">\n                                <h6 class=\"feature-name\">\n                                    <a href=\"";
                echo base_url("product/" . $product["slug"]);
                echo "\">";
                echo __($product["name"]);
                echo "</a>\n                                </h6>\n                                <div class=\"row\">\n                                    ";
                if($CMSNT->site("product_rating_display") == 1) {
                    echo "                                    <div class=\"col-6 col-md-12\">\n                                        <div class=\"feature-rating\">\n                                            <i class=\"active icofont-star\"></i>\n                                            <i class=\"active icofont-star\"></i>\n                                            <i class=\"active icofont-star\"></i>\n                                            <i class=\"active icofont-star\"></i>\n                                            <i class=\"icofont-star\"></i>\n                                            <a href=\"product-video.html\">(3 Reviews)</a>\n                                        </div>\n                                    </div>\n                                    ";
                }
                echo "                                    <div class=\"col-12 col-md-12\">\n                                        <label class=\"label-text feat\">";
                echo __("Kho hàng:");
                echo "                                            <b>";
                echo format_cash($stock);
                echo "</b></label>\n                                        ";
                if($CMSNT->site("product_sold_display") == 1) {
                    echo "                                        <label class=\"label-text order\">";
                    echo __("Đã bán:");
                    echo "                                            <b>";
                    echo format_cash($product["sold"]);
                    echo "</b></label>\n                                        ";
                }
                echo "                                    </div>\n                                </div>\n                                <h6 class=\"feature-price\">\n                                    ";
                echo 0 < $product["discount"] ? "<del>" . format_currency($product["price"]) . "</del>" : "";
                echo "<span>";
                echo format_currency($product["price"] - $product["price"] * $product["discount"] / 100);
                echo "</span>\n                                </h6>\n                                <p class=\"feature-desc\"><i class=\"fa-solid fa-angles-right\"></i>\n                                    ";
                echo $product["short_desc"];
                echo "</p>\n                                <div class=\"row\">\n                                    <div class=\"col\">\n                                        <a type=\"button\" href=\"";
                echo base_url("product/" . $product["slug"]);
                echo "\"\n                                            class=\"btn-more\"><span>";
                echo __("CHI TIẾT");
                echo "</span></a>\n                                    </div>\n                                    <div class=\"col\">\n                                        <button id=\"openModal_";
                echo $product["id"];
                echo "\"\n                                            onclick=\"openModal(`";
                echo isset($getUser) ? $getUser["token"] : NULL;
                echo "`, `";
                echo $product["id"];
                echo "`)\"\n                                            class=\"btn-buy\" data-id=\"";
                echo $product["id"];
                echo "\">";
                echo __("MUA NGAY");
                echo "</button>\n                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                    ";
            }
        }
        echo "                </div>\n                ";
    }
    echo "                ";
    if($CMSNT->site("type_show_product") == "LIST") {
        echo "                <div class=\"row row-cols-1 row-cols-md-1 row-cols-lg-1 row-cols-xl-1\">\n                    ";
        foreach ($listDatatable as $product) {
            echo "                    ";
            $stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
            echo "                    ";
            if($CMSNT->site("product_hide_outstock") == 1 && $stock == 0) {
            } else {
                echo "                    <div>\n                        <div class=\"feature-card ";
                echo $stock == 0 ? "product-disable" : "";
                echo "\">\n                            <div class=\"feature-content\">\n                                <div class=\"row\">\n                                    <div class=\"col-8 col-md-9\">\n                                        <h6 class=\"feature-name\">\n                                            <a\n                                                href=\"";
                echo base_url("product/" . $product["slug"]);
                echo "\">";
                echo __($product["name"]);
                echo "</a>\n                                        </h6>\n                                        <p class=\"feature-desc\"><i class=\"fa-solid fa-angles-right\"></i>\n                                            ";
                echo $product["short_desc"];
                echo "</p>\n                                        <div class=\"row\">\n                                            ";
                if($CMSNT->site("product_rating_display") == 1) {
                    echo "                                            <div class=\"col-12 col-md-12\">\n                                                <div class=\"feature-rating\">\n                                                    <i class=\"active icofont-star\"></i>\n                                                    <i class=\"active icofont-star\"></i>\n                                                    <i class=\"active icofont-star\"></i>\n                                                    <i class=\"active icofont-star\"></i>\n                                                    <i class=\"icofont-star\"></i>\n                                                    <a href=\"product-video.html\">(3 Reviews)</a>\n                                                </div>\n                                            </div>\n                                            ";
                }
                echo "                                            <div class=\"col-12 col-md-12\">\n                                                <label class=\"label-text feat\">";
                echo __("Kho hàng:");
                echo "                                                    <b>";
                echo format_cash($stock);
                echo "</b></label>\n                                                ";
                if($CMSNT->site("product_sold_display") == 1) {
                    echo "                                                <label class=\"label-text order\">";
                    echo __("Đã bán:");
                    echo "                                                    <b>";
                    echo format_cash($product["sold"]);
                    echo "</b></label>\n                                                ";
                }
                echo "                                                ";
                if(0 < $product["discount"]) {
                    echo "                                                <label class=\"label-text off\" data-toggle=\"tooltip\"\n                                                    data-placement=\"bottom\"\n                                                    title=\"";
                    echo __("Đang được giảm giá");
                    echo "\">-";
                    echo $product["discount"];
                    echo "%</label>\n                                                ";
                }
                echo "                                            </div>\n                                        </div>\n                                    </div>\n                                    <div class=\"col-4 col-md-3\">\n                                        <div class=\"card-price-product-list\">\n                                            <h5 class=\"feature-price\">\n                                                <span>";
                echo format_currency($product["price"] - $product["price"] * $product["discount"] / 100);
                echo "</span>\n                                            </h5>\n                                        </div>\n                                        <button id=\"openModal_";
                echo $product["id"];
                echo "\"\n                                            onclick=\"openModal(`";
                echo isset($getUser) ? $getUser["token"] : NULL;
                echo "`, `";
                echo $product["id"];
                echo "`)\"\n                                            class=\"btn-buy\" data-id=\"";
                echo $product["id"];
                echo "\">";
                echo __("MUA NGAY");
                echo "</button>\n\n                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                    ";
            }
        }
        echo "                </div>\n                ";
    } elseif($CMSNT->site("type_show_product") == "BOX_4") {
        echo "\n                <div class=\"row\">\n                    ";
        foreach ($listDatatable as $product) {
            echo "                    ";
            $stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
            echo "                    ";
            if($CMSNT->site("product_hide_outstock") == 1 && $stock == 0) {
            } else {
                echo "                    <div class=\"prod-item col-sm-6 col-md-4 ";
                echo $CMSNT->site("cot_so_du_ben_phai") == 1 ? "col-xl-4" : "col-xl-3";
                echo " mb-3\"\n                        data-title=\"";
                echo __($product["name"]);
                echo "\">\n                        <div class=\"product-box4\">\n                            <div class=\"product-head-box4\">\n                                <h4>";
                echo __($product["name"]);
                echo " </h4>\n                            </div>\n                            <div class=\"product-body-box4\">\n                                ";
                foreach (explode(PHP_EOL, $product["short_desc"]) as $bf2) {
                    echo "                                <p><i class=\"fa-solid fa-circle-check\"></i> ";
                    echo $bf2;
                    echo "</p>\n                                ";
                }
                echo "                            </div>\n\n                            <div class=\"product-footer-box4\">\n                                <div class=\"row\">\n                                    <div class=\"col-4 text-center border-end-box4\">\n                                        <strong>";
                echo __("Quốc gia");
                echo "</strong>\n                                        ";
                if(!empty($product["flag"])) {
                    echo "                                        <img src=\"https://flagcdn.com/w160/";
                    echo strtolower($product["flag"]);
                    echo ".png\"\n                                            alt=\"product\">\n                                        ";
                }
                echo "                                    </div>\n                                    <div class=\"col-4 text-center border-end-box4\">\n                                        <strong>";
                echo __("Hiện có");
                echo "</strong>\n                                        <span class=\"badge bg-primary rounded-pill\">";
                echo format_cash($stock);
                echo "</span>\n                                    </div>\n                                    <div class=\"col-4\">\n                                        <div class=\"price-box4\">\n                                            ";
                if(0 < $product["discount"]) {
                    echo "                                            <span>";
                    echo format_currency($product["price"]);
                    echo "</span>\n                                            <strong>";
                    echo format_currency($product["price"] - $product["price"] * $product["discount"] / 100);
                    echo "</strong>\n                                            ";
                } else {
                    echo "                                            <strong\n                                                class=\"proce-box4-not-discount\">";
                    echo format_currency($product["price"]);
                    echo "</strong>\n                                            ";
                }
                echo "\n                                        </div>\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"product-buttons-box4\">\n                                <a href=\"";
                echo base_url("product/" . $product["slug"]);
                echo "\" class=\"btn more-btn-box4\">\n                                    <i class=\"fa-solid fa-circle-info me-1\"></i>";
                echo __("Xem chi tiết");
                echo "                                </a>\n                                <button type=\"button\" ";
                echo $stock == 0 ? "disabled" : "";
                echo "                                    id=\"openModal_";
                echo $product["id"];
                echo "\"\n                                    onclick=\"openModal(`";
                echo isset($getUser) ? $getUser["token"] : NULL;
                echo "`, `";
                echo $product["id"];
                echo "`)\"\n                                    class=\"btn buy-btn-box4\"><i\n                                        class=\"fa-solid fa-cart-shopping me-1\"></i>";
                echo __("MUA NGAY");
                echo "</button>\n                            </div>\n                        </div>\n                    </div>\n                    ";
            }
        }
        echo "                </div>\n\n                ";
    }
    echo "                ";
    if($totalDatatable == 0) {
        echo "                <div class=\"empty-state\">\n                    <svg width=\"184\" height=\"152\" viewBox=\"0 0 184 152\" xmlns=\"http://www.w3.org/2000/svg\">\n                        <g fill=\"none\" fill-rule=\"evenodd\">\n                            <g transform=\"translate(24 31.67)\">\n                                <ellipse fill-opacity=\".8\" fill=\"#F5F5F7\" cx=\"67.797\" cy=\"106.89\" rx=\"67.797\"\n                                    ry=\"12.668\">\n                                </ellipse>\n                                <path\n                                    d=\"M122.034 69.674L98.109 40.229c-1.148-1.386-2.826-2.225-4.593-2.225h-51.44c-1.766 0-3.444.839-4.592 2.225L13.56 69.674v15.383h108.475V69.674z\"\n                                    fill=\"#AEB8C2\"></path>\n                                <path\n                                    d=\"M101.537 86.214L80.63 61.102c-1.001-1.207-2.507-1.867-4.048-1.867H31.724c-1.54 0-3.047.66-4.048 1.867L6.769 86.214v13.792h94.768V86.214z\"\n                                    fill=\"url(#linearGradient-1)\" transform=\"translate(13.56)\"></path>\n                                <path\n                                    d=\"M33.83 0h67.933a4 4 0 0 1 4 4v93.344a4 4 0 0 1-4 4H33.83a4 4 0 0 1-4-4V4a4 4 0 0 1 4-4z\"\n                                    fill=\"#F5F5F7\"></path>\n                                <path\n                                    d=\"M42.678 9.953h50.237a2 2 0 0 1 2 2V36.91a2 2 0 0 1-2 2H42.678a2 2 0 0 1-2-2V11.953a2 2 0 0 1 2-2zM42.94 49.767h49.713a2.262 2.262 0 1 1 0 4.524H42.94a2.262 2.262 0 0 1 0-4.524zM42.94 61.53h49.713a2.262 2.262 0 1 1 0 4.525H42.94a2.262 2.262 0 0 1 0-4.525zM121.813 105.032c-.775 3.071-3.497 5.36-6.735 5.36H20.515c-3.238 0-5.96-2.29-6.734-5.36a7.309 7.309 0 0 1-.222-1.79V69.675h26.318c2.907 0 5.25 2.448 5.25 5.42v.04c0 2.971 2.37 5.37 5.277 5.37h34.785c2.907 0 5.277-2.421 5.277-5.393V75.1c0-2.972 2.343-5.426 5.25-5.426h26.318v33.569c0 .617-.077 1.216-.221 1.789z\"\n                                    fill=\"#DCE0E6\"></path>\n                            </g>\n                            <path\n                                d=\"M149.121 33.292l-6.83 2.65a1 1 0 0 1-1.317-1.23l1.937-6.207c-2.589-2.944-4.109-6.534-4.109-10.408C138.802 8.102 148.92 0 161.402 0 173.881 0 184 8.102 184 18.097c0 9.995-10.118 18.097-22.599 18.097-4.528 0-8.744-1.066-12.28-2.902z\"\n                                fill=\"#DCE0E6\"></path>\n                            <g transform=\"translate(149.65 15.383)\" fill=\"#FFF\">\n                                <ellipse cx=\"20.654\" cy=\"3.167\" rx=\"2.849\" ry=\"2.815\"></ellipse>\n                                <path d=\"M5.698 5.63H0L2.898.704zM9.259.704h4.985V5.63H9.259z\"></path>\n                            </g>\n                        </g>\n                    </svg>\n                    <p>";
        echo __("Không có sản phẩm nào liên quan");
        echo "</p>\n                </div>\n                ";
    }
    echo "                <div class=\"bottom-paginate\">\n                    <p class=\"page-info\">";
    echo __("Hiển thị");
    echo " ";
    echo $limit;
    echo " ";
    echo __("trong số");
    echo " ";
    echo $totalDatatable;
    echo "                        ";
    echo __("sản phẩm");
    echo "</p>\n                    <div class=\"pagination\">\n                        ";
    echo $limit < $totalDatatable ? $urlDatatable : "";
    echo "                    </div>\n                </div>\n\n                ";
} else {
    echo "                <div class=\"row\">\n                    ";
    foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `status` = 1 AND `parent_id` != 0 " . $where_category . " ORDER BY `stt` DESC ") as $category) {
        echo "                    <div class=\"col-lg-12 mb-5\" id=\"category";
        echo $category["id"];
        echo "\">\n                        ";
        if($CMSNT->site("type_show_product") == "BOX") {
            echo "                        <div class=\"home-heading mb-3\">\n                            <h3>\n                                <img src=\"";
            echo base_url($category["icon"]);
            echo "\" alt=\"";
            echo $category["name"];
            echo "\">\n                                ";
            echo $category["name"];
            echo "                            </h3>\n                        </div>\n                        <div\n                            class=\"row row-cols-1 row-cols-md-1 row-cols-lg-2 ";
            echo $CMSNT->site("cot_so_du_ben_phai") == 1 ? "row-cols-xl-2" : "row-cols-xl-3";
            echo "\">\n                            ";
            $i = -1;
            foreach ($CMSNT->get_list(" SELECT * FROM `products` WHERE `status` = 1 AND `category_id` = '" . $category["id"] . "' " . $order_by . " ") as $product) {
                echo "                            ";
                $stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
                echo "                            ";
                if($CMSNT->site("product_hide_outstock") == 1 && $stock == 0) {
                } else {
                    $i++;
                    if($category_id == "" && $CMSNT->site("max_show_product_home") <= $i) {
                    } else {
                        echo "                            <div>\n                                <div class=\"feature-card ";
                        echo $stock == 0 ? "product-disable" : "";
                        echo "\">\n                                    <div class=\"feature-content\">\n                                        <h6 class=\"feature-name\">\n                                            <a\n                                                href=\"";
                        echo base_url("product/" . $product["slug"]);
                        echo "\">";
                        echo __($product["name"]);
                        echo "</a>\n                                        </h6>\n                                        <div class=\"row\">\n                                            ";
                        if($CMSNT->site("product_rating_display") == 1) {
                            echo "                                            <div class=\"col-6 col-md-12\">\n                                                <div class=\"feature-rating\">\n                                                    <i class=\"active icofont-star\"></i>\n                                                    <i class=\"active icofont-star\"></i>\n                                                    <i class=\"active icofont-star\"></i>\n                                                    <i class=\"active icofont-star\"></i>\n                                                    <i class=\"icofont-star\"></i>\n                                                    <a href=\"product-video.html\">(3 Reviews)</a>\n                                                </div>\n                                            </div>\n                                            ";
                        }
                        echo "                                            <div class=\"col-12 col-md-12\">\n                                                <label class=\"label-text feat\">";
                        echo __("Kho hàng:");
                        echo "                                                    <b>";
                        echo format_cash($stock);
                        echo "</b></label>\n                                                ";
                        if($CMSNT->site("product_sold_display") == 1) {
                            echo "                                                <label class=\"label-text order\">";
                            echo __("Đã bán:");
                            echo "                                                    <b>";
                            echo format_cash($product["sold"]);
                            echo "</b></label>\n                                                ";
                        }
                        echo "                                            </div>\n                                        </div>\n                                        <h6 class=\"feature-price\">\n                                            ";
                        echo 0 < $product["discount"] ? "<del>" . format_currency($product["price"]) . "</del>" : "";
                        echo "<span>";
                        echo format_currency($product["price"] - $product["price"] * $product["discount"] / 100);
                        echo "</span>\n                                        </h6>\n                                        <p class=\"feature-desc\"><i class=\"fa-solid fa-angles-right\"></i>\n                                            ";
                        echo $product["short_desc"];
                        echo "</p>\n                                        <div class=\"row\">\n                                            <div class=\"col\">\n                                                <a type=\"button\" href=\"";
                        echo base_url("product/" . $product["slug"]);
                        echo "\"\n                                                    class=\"btn-more\"><span>";
                        echo __("CHI TIẾT");
                        echo "</span></a>\n                                            </div>\n                                            <div class=\"col\">\n                                                <button id=\"openModal_";
                        echo $product["id"];
                        echo "\"\n                                                    onclick=\"openModal(`";
                        echo isset($getUser) ? $getUser["token"] : NULL;
                        echo "`, `";
                        echo $product["id"];
                        echo "`)\"\n                                                    class=\"btn-buy\"\n                                                    data-id=\"";
                        echo $product["id"];
                        echo "\">";
                        echo __("MUA NGAY");
                        echo "</button>\n                                            </div>\n                                        </div>\n                                    </div>\n                                </div>\n                            </div>\n                            ";
                    }
                }
            }
            echo "                        </div>\n                        ";
        } elseif($CMSNT->site("type_show_product") == "LIST") {
            echo "                        <div class=\"home-heading mb-3\">\n                            <h3>\n                                <img src=\"";
            echo base_url($category["icon"]);
            echo "\">\n                                ";
            echo $category["name"];
            echo "                            </h3>\n                        </div>\n                        <div class=\"row row-cols-1 row-cols-md-1 row-cols-lg-1 row-cols-xl-1\">\n                            ";
            $i = -1;
            foreach ($CMSNT->get_list(" SELECT * FROM `products` WHERE `status` = 1 AND `category_id` = '" . $category["id"] . "' " . $order_by . " ") as $product) {
                echo "                            ";
                $stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
                echo "                            ";
                if($CMSNT->site("product_hide_outstock") == 1 && $stock == 0) {
                } else {
                    $i++;
                    if($category_id == "" && $CMSNT->site("max_show_product_home") <= $i) {
                    } else {
                        echo "                            <div>\n                                <div class=\"feature-card ";
                        echo $stock == 0 ? "product-disable" : "";
                        echo "\">\n                                    <div class=\"feature-content\">\n                                        <div class=\"row\">\n                                            <div class=\"col-8 col-md-9\">\n                                                <h6 class=\"feature-name\">\n                                                    <a\n                                                        href=\"";
                        echo base_url("product/" . $product["slug"]);
                        echo "\">";
                        echo __($product["name"]);
                        echo "</a>\n                                                </h6>\n                                                <p class=\"feature-desc\"><i class=\"fa-solid fa-angles-right\"></i>\n                                                    ";
                        echo $product["short_desc"];
                        echo "</p>\n                                                <div class=\"row\">\n                                                    ";
                        if($CMSNT->site("product_rating_display") == 1) {
                            echo "                                                    <div class=\"col-12 col-md-12\">\n                                                        <div class=\"feature-rating\">\n                                                            <i class=\"active icofont-star\"></i>\n                                                            <i class=\"active icofont-star\"></i>\n                                                            <i class=\"active icofont-star\"></i>\n                                                            <i class=\"active icofont-star\"></i>\n                                                            <i class=\"icofont-star\"></i>\n                                                            <a href=\"product-video.html\">(3 Reviews)</a>\n                                                        </div>\n                                                    </div>\n                                                    ";
                        }
                        echo "                                                    <div class=\"col-12 col-md-12\">\n                                                        <label class=\"label-text feat\">";
                        echo __("Kho hàng:");
                        echo "                                                            <b>";
                        echo format_cash($stock);
                        echo "</b></label>\n                                                        ";
                        if($CMSNT->site("product_sold_display") == 1) {
                            echo "                                                        <label class=\"label-text order\">";
                            echo __("Đã bán:");
                            echo "                                                            <b>";
                            echo format_cash($product["sold"]);
                            echo "</b></label>\n                                                        ";
                        }
                        echo "                                                        ";
                        if(0 < $product["discount"]) {
                            echo "                                                        <label class=\"label-text off\" data-toggle=\"tooltip\"\n                                                            data-placement=\"bottom\"\n                                                            title=\"";
                            echo __("Đang được giảm giá");
                            echo "\">-";
                            echo $product["discount"];
                            echo "%</label>\n                                                        ";
                        }
                        echo "                                                    </div>\n                                                </div>\n                                            </div>\n                                            <div class=\"col-4 col-md-3\">\n                                                <div class=\"card-price-product-list\">\n                                                    <h5 class=\"feature-price\">\n                                                        <span>";
                        echo format_currency($product["price"] - $product["price"] * $product["discount"] / 100);
                        echo "</span>\n                                                    </h5>\n                                                </div>\n                                                <button id=\"openModal_";
                        echo $product["id"];
                        echo "\"\n                                                    onclick=\"openModal(`";
                        echo isset($getUser) ? $getUser["token"] : NULL;
                        echo "`, `";
                        echo $product["id"];
                        echo "`)\"\n                                                    class=\"btn-buy\"\n                                                    data-id=\"";
                        echo $product["id"];
                        echo "\">";
                        echo __("MUA NGAY");
                        echo "</button>\n\n                                            </div>\n                                        </div>\n                                    </div>\n                                </div>\n                            </div>\n                            ";
                    }
                }
            }
            echo "                        </div>\n                        ";
        } elseif($CMSNT->site("type_show_product") == "BOX_4") {
            echo "                        <div class=\"home-heading mb-3\">\n                            <h3>\n                                <img src=\"";
            echo base_url($category["icon"]);
            echo "\">\n                                ";
            echo $category["name"];
            echo "                            </h3>\n                        </div>\n                        <div class=\"row\">\n                            ";
            $i = -1;
            foreach ($CMSNT->get_list(" SELECT * FROM `products` WHERE `status` = 1 AND `category_id` = '" . $category["id"] . "' " . $order_by . " ") as $product) {
                echo "                            ";
                $stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
                echo "                            ";
                if($CMSNT->site("product_hide_outstock") == 1 && $stock == 0) {
                } else {
                    $i++;
                    if($category_id == "" && $CMSNT->site("max_show_product_home") <= $i) {
                    } else {
                        echo "                            <div class=\"prod-item col-sm-6 col-md-4 ";
                        echo $CMSNT->site("cot_so_du_ben_phai") == 1 ? "col-xl-4" : "col-xl-3";
                        echo " mb-3\"\n                                data-title=\"";
                        echo __($product["name"]);
                        echo "\">\n                                <div class=\"product-box4 \">\n                                    <div class=\"product-head-box4\">\n                                        <img src=\"";
                        echo base_url($category["icon"]);
                        echo "\" />\n                                        <h4>";
                        echo __($product["name"]);
                        echo " </h4>\n                                    </div>\n                                    <div class=\"product-body-box4\">\n                                        ";
                        $images = array_filter(explode(PHP_EOL, $product["images"]));
                        if(!empty($images)) {
                            $firstImage = reset($images);
                            echo "                                        <img class=\"mb-2\" src=\"";
                            echo base_url(dirImageProduct($firstImage));
                            echo "\" width=\"100%\"\n                                            alt=\"image\">\n                                        ";
                        }
                        echo "                                        ";
                        foreach (explode(PHP_EOL, $product["short_desc"]) as $bf2) {
                            echo "                                        <p><i class=\"fa-solid fa-circle-check\"></i> ";
                            echo $bf2;
                            echo "</p>\n                                        ";
                        }
                        echo "                                    </div>\n\n                                    <div class=\"product-footer-box4\">\n                                        <div class=\"row\">\n                                            <div class=\"col-4 text-center border-end-box4\">\n                                                <strong>";
                        echo __("Quốc gia");
                        echo "</strong>\n                                                ";
                        if(!empty($product["flag"])) {
                            echo "                                                <img src=\"https://flagcdn.com/w160/";
                            echo strtolower($product["flag"]);
                            echo ".png\"\n                                                    alt=\"product\">\n                                                ";
                        }
                        echo "                                            </div>\n                                            <div class=\"col-4 text-center border-end-box4\">\n                                                <strong>";
                        echo __("Hiện có");
                        echo "</strong>\n                                                <span\n                                                    class=\"badge bg-primary rounded-pill\">";
                        echo format_cash($stock);
                        echo "</span>\n                                            </div>\n                                            <div class=\"col-4\">\n                                                <div class=\"price-box4\">\n                                                    ";
                        if(0 < $product["discount"]) {
                            echo "                                                    <span>";
                            echo format_currency($product["price"]);
                            echo "</span>\n                                                    <strong>";
                            echo format_currency($product["price"] - $product["price"] * $product["discount"] / 100);
                            echo "</strong>\n                                                    ";
                        } else {
                            echo "                                                    <b\n                                                        class=\"proce-box4-not-discount\">";
                            echo format_currency($product["price"]);
                            echo "</b>\n                                                    ";
                        }
                        echo "                                                </div>\n                                            </div>\n                                        </div>\n                                    </div>\n                                    <div class=\"product-buttons-box4\">\n                                        <a href=\"";
                        echo base_url("product/" . $product["slug"]);
                        echo "\" class=\"btn more-btn-box4\">\n                                            <i class=\"fa-solid fa-circle-info me-1\"></i>";
                        echo __("Xem chi tiết");
                        echo "                                        </a>\n                                        <button type=\"button\" ";
                        echo $stock == 0 ? "disabled" : "";
                        echo "                                            id=\"openModal_";
                        echo $product["id"];
                        echo "\"\n                                            onclick=\"openModal(`";
                        echo isset($getUser) ? $getUser["token"] : NULL;
                        echo "`, `";
                        echo $product["id"];
                        echo "`)\"\n                                            class=\"btn buy-btn-box4\">\n                                            ";
                        if($stock == 0) {
                            echo "                                            <i class=\"fa-solid fa-triangle-exclamation me-1\"></i>";
                            echo __("HẾT HÀNG");
                            echo "                                            ";
                        } else {
                            echo "                                            <i class=\"fa-solid fa-cart-shopping me-1\"></i>";
                            echo __("MUA NGAY");
                            echo "                                            ";
                        }
                        echo "                                        </button>\n                                    </div>\n                                </div>\n                            </div>\n                            ";
                    }
                }
            }
            echo "                        </div>\n                        ";
        }
        echo "\n                        ";
        if($category_id == "" && $CMSNT->site("max_show_product_home") <= $i) {
            echo "                        <center><a type=\"button\" href=\"";
            echo base_url("category/" . $category["slug"]);
            echo "\"\n                                class=\"btn-more-new mb-3\">";
            echo __("Xem thêm");
            echo "</a></center>\n                        ";
        }
        echo "                    </div>\n                    ";
    }
    echo "                </div>\n                ";
}
echo "            </div>\n            ";
if($CMSNT->site("cot_so_du_ben_phai") == 1) {
    echo "            <div class=\"col-xl-3\">\n                <div class=\"account-card card-wallet-home py-4\">\n                    ";
    if(isset($getUser)) {
        echo "                    <div class=\"my-wallet\">\n                        <p>";
        echo __("Số dư hiện tại");
        echo "</p>\n                        <h3>";
        echo format_currency($getUser["money"]);
        echo "</h3>\n                    </div>\n                    <div class=\"wallet-card-group\">\n                        <div class=\"wallet-card\">\n                            <p>";
        echo __("Tổng tiền nạp");
        echo "</p>\n                            <h3>";
        echo format_currency($getUser["total_money"]);
        echo "</h3>\n                        </div>\n                        <div class=\"wallet-card\">\n                            <p>";
        echo __("Số dư đã sử dụng");
        echo "</p>\n                            <h3>";
        echo format_currency($getUser["total_money"] - $getUser["money"]);
        echo "</h3>\n                        </div>\n                        <div class=\"wallet-card\">\n                            <p>";
        echo __("Giảm giá");
        echo "</p>\n                            <h3>";
        echo $getUser["discount"];
        echo "%</h3>\n                        </div>\n                    </div>\n                    ";
    } else {
        echo "                    <ul class=\"user-form-social\">\n                        <li><a href=\"";
        echo base_url("client/login");
        echo "\" class=\"facebook\"><i\n                                    class=\"fa-solid fa-right-to-bracket\"></i> ";
        echo mb_strtoupper(__("Đăng nhập"));
        echo "</a>\n                        </li>\n                        <li><a href=\"";
        echo base_url("client/register");
        echo "\" class=\"google\"><i\n                                    class=\"fa-solid fa-user-plus\"></i> ";
        echo mb_strtoupper(__("Đăng ký tài khoản"));
        echo "</a>\n                        </li>\n                    </ul>\n                    ";
    }
    echo "                </div>\n            </div>\n            ";
}
echo "        </div>\n        ";
if($CMSNT->site("status_giao_dich_gan_day") == 1) {
    echo "        <div class=\"row\">\n            <div class=\"col-lg-6 mb-3\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-cart-shopping m-2\"></i> ";
    echo mb_strtoupper(__("Đơn hàng gần đây"));
    echo "                    </h3>\n                </div>\n                <div style=\"height:350px;overflow-x:hidden;overflow-y:auto;\">\n                    ";
    foreach ($CMSNT->get_list("SELECT * FROM `order_log` ORDER BY id DESC limit 20 ") as $log_order) {
        echo "                    <div class=\"feature-card\">\n                        <div class=\"feature-content\">\n                            <div class=\"row\">\n                                <div class=\"col-10 col-md-10\">\n                                    ";
        $content = $CMSNT->site("content_gd_mua_gan_day");
        $content = str_replace("{username}", mb_substr(getRowRealtime("users", $log_order["buyer"], "username"), -3, 3), $content);
        $content = str_replace("{amount}", format_cash($log_order["amount"]), $content);
        $content = str_replace("{product_name}", mb_substr($log_order["product_name"], 0, 30) . "...", $content);
        $content_gd_mua_gan_day = str_replace("{price}", format_currency($log_order["pay"]), $content);
        echo "                                    ";
        echo $content_gd_mua_gan_day;
        echo "                                </div>\n                                <div class=\"col-2 col-md-2\">\n                                    <span class=\"badge bg-primary\">";
        echo timeAgo($log_order["create_time"]);
        echo "</span>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                    ";
    }
    echo "                </div>\n            </div>\n            <div class=\"col-lg-6 mb-3\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-solid fa-credit-card m-2\"></i> ";
    echo mb_strtoupper(__("Nạp tiền gần đây"));
    echo "                    </h3>\n                </div>\n                <div style=\"height:350px;overflow-x:hidden;overflow-y:auto;\">\n                    ";
    foreach ($CMSNT->get_list("SELECT * FROM `deposit_log` ORDER BY id DESC limit 20 ") as $log_payment) {
        echo "                    <div class=\"feature-card\">\n                        <div class=\"feature-content\">\n                            <div class=\"row\">\n                                <div class=\"col-9 col-md-10\">\n                                    ";
        $content = $CMSNT->site("content_gd_nap_tien_gan_day");
        $content = str_replace("{username}", mb_substr(getRowRealtime("users", $log_payment["user_id"], "username"), -3, 3), $content);
        $content = str_replace("{amount}", format_currency($log_payment["amount"]), $content);
        $content = str_replace("{method}", mb_substr($log_payment["method"], 0, 45), $content);
        $content_gd_nap_tien_gan_day = str_replace("{received}", format_currency($log_payment["received"]), $content);
        echo "                                    ";
        echo $content_gd_nap_tien_gan_day;
        echo "                                </div>\n                                <div class=\"col-3 col-md-2\">\n                                    <span class=\"badge bg-primary\">";
        echo timeAgo($log_payment["create_time"]);
        echo "</span>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                    ";
    }
    echo "                </div>\n            </div>\n        </div>\n        ";
}
echo "\n\n    </div>\n</section>\n\n\n\n\n<div class=\"modal fade\" id=\"openModal\" tabindex=\"-1\" aria-labelledby=\"modal-block-popout\" role=\"dialog\"\n    aria-hidden=\"true\">\n    <div class=\"modal-dialog modal-lg modal-dialog-popout\" role=\"document\">\n        <div class=\"modal-content\">\n            <div id=\"modalContent\"></div>\n        </div>\n    </div>\n</div>\n\n<script>\nfunction openModal(token, id) {\n    \$(\"#modalContent\").html('');\n    var originalButtonContent = \$('#openModal_' + id).html();\n    \$('#openModal_' + id).html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "</span>')\n        .prop('disabled',\n            true);\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/client/modal/view-product.php");
echo "\",\n        method: \"GET\",\n        data: {\n            id: id,\n            token: token\n        },\n        success: function(data) {\n            \$(\"#modalContent\").html(data);\n            \$('#openModal').modal('show');\n            \$('#openModal_' + id).html(originalButtonContent).prop('disabled', false);\n        },\n        error: function() {\n            Swal.fire('";
echo __("Thất bại!");
echo "', data, 'error');\n        }\n    });\n}\n</script>\n\n\n\n\n";
if($CMSNT->site("menu_category_right") != 0) {
    echo "<ul id=\"top-menu-";
    echo $CMSNT->site("menu_category_right") == 1 ? "right" : "left";
    echo "\">\n    <li>\n        <a class=\"menu-item\" id=\"toggle-menu-button\">\n            <i class=\"fa-solid fa-eye-slash\"></i>\n            <span>";
    echo __("Đóng menu");
    echo "</span></a>\n    </li>\n    <li>\n        <a class=\"menu-item\" href=\"";
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
        echo "\"></i>\n            <span>";
        echo $category["name"];
        echo "</span></a>\n    </li>\n    ";
    }
    echo "</ul>\n<script>\n// JavaScript để ẩn/hiện menu khi click vào nút\nconst toggleMenuButton = document.getElementById('toggle-menu-button');\nconst topMenu = document.getElementById('top-menu-";
    echo $CMSNT->site("menu_category_right") == 1 ? "right" : "left";
    echo "');\ntoggleMenuButton.addEventListener('click', function() {\n    topMenu.classList.toggle('hidden');\n});\n</script>\n";
}
echo "\n\n";
if($CMSNT->site("popup_status") == 1) {
    echo "<div class=\"modal fade\" id=\"modal_notification\" tabindex=\"-1\" aria-labelledby=\"exampleModalLabel\" aria-hidden=\"true\">\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"exampleModalLabel1\"><i class=\"fa-solid fa-bell\"></i> ";
    echo __("Thông Báo");
    echo "                </h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <div class=\"modal-body\">\n                ";
    echo $CMSNT->site("popup_noti");
    echo "            </div>\n            <div class=\"modal-footer\">\n                <button type=\"button\" class=\"btn btn-danger\"\n                    id=\"dontShowAgainBtn\">";
    echo __("Không hiển thị lại trong 2 giờ");
    echo "</button>\n            </div>\n        </div>\n    </div>\n</div>\n<script>\ndocument.addEventListener(\"DOMContentLoaded\", function() {\n    var modal = document.getElementById('modal_notification');\n    var dontShowAgainBtn = document.getElementById('dontShowAgainBtn');\n    var modalClosedTime = localStorage.getItem('modalClosedTime');\n\n    // Nếu modalClosedTime chưa được lưu hoặc đã quá 2 giờ, hiển thị modal\n    if (!modalClosedTime || (Date.now() - parseInt(modalClosedTime) > 2 * 60 * 60 * 1000)) {\n        var bootstrapModal = new bootstrap.Modal(modal);\n        bootstrapModal.show();\n    }\n\n    // Lưu thời gian khi modal được đóng khi người dùng click vào nút \"Không hiển thị lại\" và ẩn modal\n    dontShowAgainBtn.addEventListener('click', function() {\n        localStorage.setItem('modalClosedTime', Date.now());\n        var bootstrapModal = bootstrap.Modal.getInstance(modal);\n        bootstrapModal.hide();\n    });\n});\n</script>\n";
}
echo "\n\n";
require_once __DIR__ . "/footer.php";

?>