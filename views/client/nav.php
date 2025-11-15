<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
echo "\n<body>\n    <div class=\"backdrop\"></div><a class=\"backtop\" href=\"#\"><i class=\"fa-sharp fa-solid fa-chevron-up\"></i></a>\n    <div class=\"header-top\">\n        <div class=\"container\">\n            <div class=\"row\">\n                <div class=\"col-md-12 col-lg-5\">\n                    <div class=\"header-top-welcome\">\n                        <p>";
echo $CMSNT->site("notice_top_left");
echo "</p>\n                    </div>\n                </div>\n                <div class=\"col-md-5 col-lg-3\">\n                    <div class=\"header-top-select\">\n                        <div class=\"header-select\"><i class=\"icofont-world\"></i>\n                            ";
if($CMSNT->site("language_type") == "manual") {
    echo "                            <select class=\"select\" id=\"changeLanguage\" onchange=\"changeLanguage()\">\n                                ";
    foreach ($CMSNT->get_list("SELECT * FROM `languages` WHERE `status` = 1 ") as $lang) {
        echo "                                <option value=\"";
        echo $lang["id"];
        echo "\"\n                                    ";
        echo getLanguage() == $lang["lang"] ? "selected" : "";
        echo ">";
        echo $lang["lang"];
        echo "</option>\n                                ";
    }
    echo "                            </select>\n                            ";
} elseif($CMSNT->site("language_type") == "gtranslate") {
    echo "                            ";
    echo $CMSNT->site("gtranslate_script");
    echo "                            ";
}
echo "                        </div>\n                        <div class=\"header-select\"><i class=\"icofont-money\"></i>\n                            <select class=\"select\" id=\"changeCurrency\" onchange=\"changeCurrency()\">\n                                ";
foreach ($CMSNT->get_list("SELECT * FROM `currencies` WHERE `display` = 1 ") as $currency) {
    echo "                                <option value=\"";
    echo $currency["id"];
    echo "\"\n                                    ";
    echo getCurrency() == $currency["id"] ? "selected" : "";
    echo ">";
    echo $currency["code"];
    echo "                                </option>\n                                ";
}
echo "                            </select>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"col-md-7 col-lg-4\">\n                    <ul class=\"header-top-list\">\n                        <li><a href=\"";
echo base_url("client/policy");
echo "\">";
echo __("Chính sách");
echo "</a></li>\n                        <li><a href=\"";
echo base_url("client/faq");
echo "\">";
echo __("FAQ");
echo "</a></li>\n                        <li><a href=\"";
echo base_url("client/contact");
echo "\">";
echo __("Liên Hệ");
echo "</a></li>\n                    </ul>\n                </div>\n            </div>\n        </div>\n    </div>\n    <header class=\"header-part\">\n        <div class=\"container\">\n            <div class=\"header-content\">\n                <div class=\"header-media-group\">\n                    <button class=\"header-user\"><i class=\"fa-solid fa-bars\"></i></button>\n                    <a href=\"";
echo base_url();
echo "\">\n                        <img src=\"";
echo BASE_URL($CMSNT->site("logo_light"));
echo "\" alt=\"logo\"></a>\n                    <button class=\"header-src\"><i class=\"fas fa-search\"></i></button>\n                </div>\n                <a href=\"";
echo base_url();
echo "\" class=\"header-logo\"><img src=\"";
echo BASE_URL($CMSNT->site("logo_light"));
echo "\"\n                        alt=\"logo\"></a>\n                <form class=\"header-form\" method=\"GET\" action=\"";
echo base_url();
echo "\">\n                    <input type=\"hidden\" name=\"action\" value=\"home\">\n                    <input type=\"text\" name=\"keyword\" value=\"";
echo isset($keyword) ? $keyword : "";
echo "\"\n                        placeholder=\"";
echo __("Tìm kiếm sản phẩm...");
echo "\"><button><i class=\"fas fa-search\"></i></button>\n                </form>\n                <div class=\"header-widget-group\">\n                    <a href=\"";
echo base_url("product-orders/");
echo "\" class=\"header-widget\" title=\"";
echo __("Đơn hàng");
echo "\"><i\n                            class=\"fa-solid fa-cart-arrow-down\"></i></a>\n                    <a href=\"";
echo base_url("client/favorites");
echo "\" class=\"header-widget\"\n                        title=\"";
echo __("Sản phẩm yêu thích");
echo "\">\n                        <i class=\"fas fa-heart\"></i>\n                        <sup\n                            id=\"numFavorites\">";
echo isset($getUser) ? $CMSNT->get_row(" SELECT COUNT(id) FROM `favorites` WHERE `user_id` = '" . $getUser["id"] . "' ")["COUNT(id)"] : 0;
echo "</sup>\n                    </a>\n                    <button class=\"header-widget header-cart\" title=\"";
echo __("Nạp tiền");
echo "\"><i\n                            class=\"fa-solid fa-building-columns\"></i>\n\n                    </button>\n                    ";
if(isset($getUser)) {
    echo "                    <a href=\"";
    echo base_url("client/profile");
    echo "\" class=\"header-widget\" title=\"Profile\">\n                        <img src=\"";
    echo BASE_URL($CMSNT->site("avatar"));
    echo "\" alt=\"user\"><span>\n                            <p class=\"text-uppercase\">";
    echo $getUser["username"];
    echo "</p>\n                            <p style=\"color:blue;\">";
    echo format_currency($getUser["money"]);
    echo "</p>\n                        </span>\n                    </a>\n                    ";
} else {
    echo "                    <a href=\"";
    echo base_url("client/login");
    echo "\" class=\"header-widget\" title=\"Login\">\n                        <img src=\"";
    echo BASE_URL($CMSNT->site("avatar"));
    echo "\" alt=\"user\"><span>Login</span>\n                    </a>\n                    ";
}
echo "                </div>\n            </div>\n        </div>\n    </header>\n    <nav class=\"navbar-part\">\n        <div class=\"container\">\n            <div class=\"row\">\n                <div class=\"col-lg-12\">\n                    <div class=\"navbar-content\">\n                        <ul class=\"navbar-list\">\n                            <li class=\"navbar-item\"><a class=\"navbar-link\"\n                                    href=\"";
echo base_url("client/home");
echo "\">";
echo __("Trang chủ");
echo "</a>\n                            </li>\n                            <li class=\"navbar-item dropdown-megamenu\"><a class=\"navbar-link dropdown-arrow\"\n                                    href=\"#\">";
echo __("Sản phẩm");
echo "</a>\n                                <div class=\"megamenu\">\n                                    <div class=\"container\">\n                                        <div class=\"row row-cols-5\">\n                                            ";
foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `status` = 1 AND `parent_id` = 0 ORDER BY `stt` DESC ") as $category) {
    echo "                                            <div class=\"col-4\">\n                                                <div class=\"megamenu-wrap\">\n                                                    <h5 class=\"megamenu-title\">";
    echo __($category["name"]);
    echo "</h5>\n                                                    <ul class=\"megamenu-list\">\n                                                        ";
    foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `status` = 1 AND `parent_id` = '" . $category["id"] . "' ORDER BY `stt` DESC ") as $category1) {
        echo "                                                        <li><a href=\"";
        echo base_url("category/" . $category1["slug"]);
        echo "\"><img\n                                                                    width=\"25px\"\n                                                                    src=\"";
        echo base_url($category1["icon"]);
        echo "\">\n                                                                ";
        echo __($category1["name"]);
        echo "</a></li>\n                                                        ";
    }
    echo "                                                    </ul>\n                                                </div>\n                                            </div>\n                                            ";
}
echo "                                        </div>\n                                    </div>\n                                </div>\n                            </li>\n                            <li class=\"navbar-item dropdown\">\n                                <a class=\"navbar-link dropdown-arrow\" href=\"#\">";
echo __("Nạp tiền");
echo "</a>\n                                <ul class=\"dropdown-position-list\">\n                                    ";
if($CMSNT->site("bank_status") == 1) {
    echo "                                    <li><a href=\"";
    echo base_url("?action=recharge-bank");
    echo "\"><img width=\"20px\"\n                                                src=\"";
    echo base_url("assets/img/icon-bank.svg");
    echo "\">\n                                            ";
    echo __("Ngân hàng");
    echo "</a></li>\n                                    ";
}
echo "                                    ";
if($CMSNT->site("momo_status") == 1) {
    echo "                                    <li><a href=\"";
    echo base_url("?action=recharge-momo");
    echo "\"><img width=\"20px\"\n                                                src=\"";
    echo base_url("assets/img/icon-momo.png");
    echo "\">\n                                            ";
    echo __("Ví MOMO");
    echo "</a></li>\n                                    ";
}
echo "                                    ";
if($CMSNT->site("card_status") == 1) {
    echo "                                    <li><a href=\"";
    echo base_url("?action=recharge-card");
    echo "\"><img width=\"20px\"\n                                                src=\"";
    echo base_url("assets/img/icon-cards.png");
    echo "\">\n                                            ";
    echo __("Thẻ cào");
    echo "</a>\n                                    </li>\n                                    ";
}
echo "                                    ";
if($CMSNT->site("crypto_status") == 1) {
    echo "                                    <li><a href=\"";
    echo base_url("?action=recharge-crypto");
    echo "\"><img width=\"20px\"\n                                                src=\"";
    echo base_url("assets/img/icon-usdt.svg");
    echo "\"> ";
    echo __("Crypto");
    echo "</a>\n                                    </li>\n                                    ";
}
echo "                                    ";
if($CMSNT->site("paypal_status") == 1) {
    echo "                                    <li><a href=\"";
    echo base_url("?action=recharge-paypal");
    echo "\"><img width=\"20px\"\n                                                src=\"";
    echo base_url("assets/img/icon-paypal.svg");
    echo "\">\n                                            ";
    echo __("Paypal");
    echo "</a></li>\n                                    ";
}
echo "                                    ";
if($CMSNT->site("perfectmoney_status") == 1) {
    echo "                                    <li><a href=\"";
    echo base_url("?action=recharge-perfectmoney");
    echo "\"><img width=\"20px\"\n                                                src=\"";
    echo base_url("assets/img/icon-perfectmoney.svg");
    echo "\">\n                                            ";
    echo __("Perfect Money");
    echo "</a></li>\n                                    ";
}
echo "                                    ";
if($CMSNT->site("toyyibpay_status") == 1) {
    echo "                                    <li><a href=\"";
    echo base_url("?action=recharge-toyyibpay");
    echo "\"><img width=\"20px\"\n                                                src=\"";
    echo base_url("assets/img/icon-toyyibpay.jpeg");
    echo "\">\n                                            ";
    echo __("Toyyibpay Malaysia");
    echo "</a></li>\n                                    ";
}
echo "                                    ";
if($CMSNT->site("squadco_status") == 1) {
    echo "                                    <li><a href=\"";
    echo base_url("?action=recharge-squadco");
    echo "\"><img width=\"20px\"\n                                                src=\"";
    echo base_url("assets/img/icon-squadco.png");
    echo "\">\n                                            ";
    echo __("Squadco Nigeria");
    echo "</a></li>\n                                    ";
}
echo "                                    ";
if($CMSNT->site("flutterwave_status") == 1) {
    echo "                                    <li><a href=\"";
    echo base_url("?action=recharge-flutterwave");
    echo "\"><img width=\"20px\"\n                                                src=\"";
    echo base_url("mod/img/icon-flutterwave.png");
    echo "\">\n                                            ";
    echo __("Flutterwave");
    echo "</a></li>\n                                    ";
}
echo "                                    ";
foreach ($CMSNT->get_list(" SELECT * FROM `payment_manual` WHERE `display` = 1 ") as $payment_manual) {
    echo "                                    <li><a href=\"";
    echo base_url("recharge-manual/" . $payment_manual["slug"]);
    echo "\"><img\n                                                width=\"20px\" src=\"";
    echo base_url($payment_manual["icon"]);
    echo "\">\n                                            ";
    echo __($payment_manual["title"]);
    echo "</a></li>\n                                    ";
}
echo "                                </ul>\n                            </li>\n                            <li class=\"navbar-item dropdown\">\n                                <a class=\"navbar-link dropdown-arrow\" href=\"#\">";
echo __("Lịch sử");
echo "</a>\n                                <ul class=\"dropdown-position-list\">\n                                    <li><a href=\"";
echo base_url("product-orders/");
echo "\">";
echo __("Lịch sử đơn hàng");
echo "</a>\n                                    </li>\n                                    <li><a href=\"";
echo base_url("client/logs");
echo "\">";
echo __("Nhật ký hoạt động");
echo "</a></li>\n                                    <li><a href=\"";
echo base_url("client/transactions");
echo "\">";
echo __("Biến động số dư");
echo "</a>\n                                    </li>\n                                </ul>\n                            </li>\n                            ";
if($CMSNT->site("affiliate_status") == 1) {
    echo "                            <li class=\"navbar-item dropdown\">\n                                <a class=\"navbar-link dropdown-arrow\" href=\"#\">";
    echo __("Affiliate Program");
    echo "</a>\n                                <ul class=\"dropdown-position-list\">\n                                    <li><a href=\"";
    echo base_url("?action=affiliates");
    echo "\">";
    echo __("Thống kê");
    echo "</a></li>\n                                    <li><a href=\"";
    echo base_url("?action=affiliate-history");
    echo "\">";
    echo __("Lịch sử");
    echo "</a>\n                                    </li>\n                                    <li><a href=\"";
    echo base_url("?action=affiliate-withdraw");
    echo "\">";
    echo __("Rút tiền");
    echo "</a>\n                                    </li>\n                                </ul>\n                            </li>\n                            ";
}
echo "                            ";
if($CMSNT->site("blog_status") == 1) {
    echo "                            <li class=\"navbar-item\"><a class=\"navbar-link\"\n                                    href=\"";
    echo base_url("blogs");
    echo "\">";
    echo __("Blogs");
    echo "</a></li>\n                            ";
}
echo "                            ";
if($CMSNT->site("api_status") == 1) {
    echo "                            <li class=\"navbar-item\"><a class=\"navbar-link\"\n                                    href=\"";
    echo base_url("document-api");
    echo "\">";
    echo __("Tài liệu API");
    echo "</a></li>\n                            ";
}
echo "                            ";
if(isset($getUser) && $getUser["admin"] != 0) {
    echo "                            <li class=\"navbar-item\"><a class=\"navbar-link\"\n                                    href=\"";
    echo base_url_admin();
    echo "\">";
    echo __("Admin Panel");
    echo "</a></li>\n                            ";
}
echo "                        </ul>\n                        <div class=\"navbar-info-group\">\n                            <div class=\"navbar-info\"><i class=\"fa-solid fa-phone\"></i>\n                                <p><small>";
echo __("Hotline");
echo "</small><span>";
echo $CMSNT->site("hotline");
echo "</span></p>\n                            </div>\n                            <div class=\"navbar-info\"><i class=\"fa-regular fa-envelope\"></i>\n                                <p><small>";
echo __("Email");
echo "</small><span>";
echo $CMSNT->site("email");
echo "</span></p>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </nav>\n    <aside class=\"category-sidebar\">\n        <div class=\"category-header\">\n            <h4 class=\"category-title\"><i class=\"fas fa-align-left\"></i><span>";
echo __("Sản phẩm");
echo "</span></h4><button\n                class=\"category-close\"><i class=\"icofont-close\"></i></button>\n        </div>\n        <!--menu mobile-->\n        <ul class=\"category-list\">\n            ";
foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `status` = 1 AND `parent_id` = 0 ") as $category) {
    echo "            <li class=\"category-item\">\n                <a class=\"category-link dropdown-link\" href=\"#\">\n                    <img src=\"";
    echo base_url($category["icon"]);
    echo "\" style=\"margin-right: 10px;\" width=\"30px\">\n                    ";
    echo __($category["name"]);
    echo " </a>\n                <ul class=\"dropdown-list\">\n                    ";
    foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `status` = 1 AND `parent_id` = '" . $category["id"] . "' ") as $category1) {
        echo "                    <li><a href=\"";
        echo base_url("category/" . $category1["slug"]);
        echo "\">";
        echo __($category1["name"]);
        echo "</a>\n                    </li>\n                    ";
    }
    echo "                </ul>\n            </li>\n            ";
}
echo "        </ul>\n    </aside>\n    <aside class=\"cart-sidebar\">\n        <div class=\"cart-header\">\n            <div class=\"cart-total\"><i\n                    class=\"fa-solid fa-building-columns\"></i><span>";
echo __("Chọn phương thức nạp tiền");
echo "</span></div>\n            <button class=\"cart-close\"><i class=\"icofont-close\"></i></button>\n        </div>\n        <ul class=\"category-list\">\n            ";
if($CMSNT->site("bank_status") == 1) {
    echo "            <li class=\"category-item\">\n                <a class=\"category-link\" href=\"";
    echo base_url("?action=recharge-bank");
    echo "\"><img style=\"margin-right: 10px;\"\n                        width=\"30px\" src=\"";
    echo base_url("assets/img/icon-bank.svg");
    echo "\">\n                    ";
    echo __("Ngân hàng");
    echo "</a>\n            </li>\n            ";
}
echo "            ";
if($CMSNT->site("momo_status") == 1) {
    echo "            <li class=\"category-item\"><a href=\"";
    echo base_url("?action=recharge-momo");
    echo "\" class=\"category-link\"><img\n                        style=\"margin-right: 10px;\" width=\"30px\" src=\"";
    echo base_url("assets/img/icon-momo.png");
    echo "\">\n                    ";
    echo __("Ví MOMO");
    echo "</a></li>\n            ";
}
echo "            ";
if($CMSNT->site("card_status") == 1) {
    echo "            <li class=\"category-item\"><a class=\"category-link\" href=\"";
    echo base_url("?action=recharge-card");
    echo "\">\n                    <img width=\"30px\" style=\"margin-right: 10px;\" src=\"";
    echo base_url("assets/img/icon-cards.png");
    echo "\">\n                    ";
    echo __("Thẻ cào");
    echo "</a>\n            </li>\n            ";
}
echo "            ";
if($CMSNT->site("crypto_status") == 1) {
    echo "            <li class=\"category-item\"><a class=\"category-link\" href=\"";
    echo base_url("?action=recharge-crypto");
    echo "\"><img\n                        style=\"margin-right: 10px;\" width=\"30px\" src=\"";
    echo base_url("assets/img/icon-usdt.svg");
    echo "\">\n                    ";
    echo __("Crypto");
    echo "</a>\n            </li>\n            ";
}
echo "            ";
if($CMSNT->site("paypal_status") == 1) {
    echo "            <li class=\"category-item\"><a class=\"category-link\" href=\"";
    echo base_url("?action=recharge-paypal");
    echo "\"><img\n                        style=\"margin-right: 10px;\" width=\"30px\" src=\"";
    echo base_url("assets/img/icon-paypal.svg");
    echo "\">\n                    ";
    echo __("Paypal");
    echo "</a></li>\n            ";
}
echo "            ";
if($CMSNT->site("perfectmoney_status") == 1) {
    echo "            <li class=\"category-item\"><a class=\"category-link\"\n                    href=\"";
    echo base_url("?action=recharge-perfectmoney");
    echo "\"><img style=\"margin-right: 10px;\" width=\"30px\"\n                        src=\"";
    echo base_url("assets/img/icon-perfectmoney.svg");
    echo "\">\n                    ";
    echo __("Perfect Money");
    echo "</a></li>\n            ";
}
echo "            ";
if($CMSNT->site("toyyibpay_status") == 1) {
    echo "            <li class=\"category-item\"><a class=\"category-link\" href=\"";
    echo base_url("?action=recharge-toyyibpay");
    echo "\"><img\n                        style=\"margin-right: 10px;\" width=\"30px\" src=\"";
    echo base_url("assets/img/icon-toyyibpay.jpeg");
    echo "\">\n                    ";
    echo __("Toyyibpay Malaysia");
    echo "</a></li>\n            ";
}
echo "            ";
if($CMSNT->site("squadco_status") == 1) {
    echo "            <li class=\"category-item\"><a class=\"category-link\" href=\"";
    echo base_url("?action=recharge-squadco");
    echo "\"><img\n                        style=\"margin-right: 10px;\" width=\"30px\" src=\"";
    echo base_url("assets/img/icon-squadco.png");
    echo "\">\n                    ";
    echo __("Squadco Nigeria");
    echo "</a></li>\n            ";
}
echo "            ";
if($CMSNT->site("flutterwave_status") == 1) {
    echo "            <li class=\"category-item\"><a class=\"category-link\"\n                    href=\"";
    echo base_url("?action=recharge-flutterwave");
    echo "\"><img style=\"margin-right: 10px;\" width=\"30px\"\n                        src=\"";
    echo base_url("mod/img/icon-flutterwave.png");
    echo "\">\n                    ";
    echo __("Flutterwave");
    echo "</a></li>\n            ";
}
echo "            ";
foreach ($CMSNT->get_list(" SELECT * FROM `payment_manual` WHERE `display` = 1 ") as $payment_manual) {
    echo "            <li class=\"category-item\"><a class=\"category-link\"\n                    href=\"";
    echo base_url("recharge-manual/" . $payment_manual["slug"]);
    echo "\"><img style=\"margin-right: 10px;\"\n                        width=\"30px\" src=\"";
    echo base_url($payment_manual["icon"]);
    echo "\">\n                    ";
    echo __($payment_manual["title"]);
    echo "</a></li>\n            ";
}
echo "        </ul>\n    </aside>\n    <aside class=\"nav-sidebar\">\n        <div class=\"nav-header\"><a href=\"";
echo base_url();
echo "\"><img src=\"";
echo BASE_URL($CMSNT->site("logo_light"));
echo "\"\n                    alt=\"logo\"></a><button class=\"nav-close\"><i class=\"icofont-close\"></i></button></div>\n        <div class=\"nav-content\">\n            <div class=\"nav-btn\">\n                ";
if(isset($getUser)) {
    echo "                <a href=\"";
    echo base_url("client/profile");
    echo "\" class=\"btn btn-inline\">\n                    <i class=\"fa fa-user\"></i> <span>";
    echo $getUser["username"];
    echo "</span></a>\n                ";
} else {
    echo "                <a href=\"";
    echo base_url("client/login");
    echo "\" class=\"btn btn-inline\">\n                    <i class=\"fa fa-unlock-alt\"></i> <span>";
    echo __("Đăng Nhập");
    echo "</span></a>\n                ";
}
echo "\n            </div>\n            <div class=\"nav-select-group\">\n                <p>";
echo __("Số dư của tôi:");
echo " <strong\n                        class=\"text-wallet\">";
echo isset($getUser) ? format_currency($getUser["money"]) : 0;
echo "</strong></p>\n            </div>\n            <ul class=\"nav-list\">\n                <li><a class=\"nav-link\" href=\"";
echo base_url("client/home");
echo "\"><i\n                            class=\"icofont-home\"></i>";
echo __("Trang chủ");
echo "</a></li>\n                <li><a class=\"nav-link dropdown-link\" href=\"#\"><i\n                            class=\"fa-solid fa-cart-shopping\"></i>";
echo __("Sản phẩm");
echo "</a>\n                    <ul class=\"dropdown-list\">\n                        ";
foreach ($CMSNT->get_list(" SELECT * FROM `categories` WHERE `status` = 1 AND `parent_id` != 0 ") as $category1) {
    echo "                        <li><a href=\"";
    echo base_url("category/" . $category1["slug"]);
    echo "\"><img width=\"25px\"\n                                    class=\"me-2 active\" src=\"";
    echo base_url($category1["icon"]);
    echo "\">\n                                ";
    echo __($category1["name"]);
    echo "</a></li>\n                        ";
}
echo "                    </ul>\n                </li>\n                <li><a class=\"nav-link dropdown-link\" href=\"#\"><i\n                            class=\"fa-solid fa-building-columns\"></i>";
echo __("Nạp tiền");
echo "</a>\n                    <ul class=\"dropdown-list\">\n                        ";
if($CMSNT->site("bank_status") == 1) {
    echo "                        <li><a href=\"";
    echo base_url("?action=recharge-bank");
    echo "\"><img width=\"20px\" class=\"me-2\"\n                                    src=\"";
    echo base_url("assets/img/icon-bank.svg");
    echo "\">\n                                ";
    echo __("Ngân hàng");
    echo "</a></li>\n                        ";
}
echo "                        ";
if($CMSNT->site("momo_status") == 1) {
    echo "                        <li><a href=\"";
    echo base_url("?action=recharge-momo");
    echo "\"><img width=\"20px\" class=\"me-2\"\n                                    src=\"";
    echo base_url("assets/img/icon-momo.png");
    echo "\">\n                                ";
    echo __("Ví MOMO");
    echo "</a></li>\n                        ";
}
echo "                        ";
if($CMSNT->site("card_status") == 1) {
    echo "                        <li><a href=\"";
    echo base_url("?action=recharge-card");
    echo "\"><img width=\"20px\" class=\"me-2\"\n                                    src=\"";
    echo base_url("assets/img/icon-cards.png");
    echo "\">\n                                ";
    echo __("Thẻ cào");
    echo "</a>\n                        </li>\n                        ";
}
echo "                        ";
if($CMSNT->site("crypto_status") == 1) {
    echo "                        <li><a href=\"";
    echo base_url("?action=recharge-crypto");
    echo "\"><img width=\"20px\" class=\"me-2\"\n                                    src=\"";
    echo base_url("assets/img/icon-usdt.svg");
    echo "\"> ";
    echo __("Crypto");
    echo "</a>\n                        </li>\n                        ";
}
echo "                        ";
if($CMSNT->site("paypal_status") == 1) {
    echo "                        <li><a href=\"";
    echo base_url("?action=recharge-paypal");
    echo "\"><img width=\"20px\" class=\"me-2\"\n                                    src=\"";
    echo base_url("assets/img/icon-paypal.svg");
    echo "\">\n                                ";
    echo __("Paypal");
    echo "</a></li>\n                        ";
}
echo "                        ";
if($CMSNT->site("perfectmoney_status") == 1) {
    echo "                        <li><a href=\"";
    echo base_url("?action=recharge-perfectmoney");
    echo "\"><img width=\"20px\" class=\"me-2\"\n                                    src=\"";
    echo base_url("assets/img/icon-perfectmoney.svg");
    echo "\">\n                                ";
    echo __("Perfect Money");
    echo "</a></li>\n                        ";
}
echo "                        ";
if($CMSNT->site("toyyibpay_status") == 1) {
    echo "                        <li><a href=\"";
    echo base_url("?action=recharge-toyyibpay");
    echo "\"><img width=\"20px\" class=\"me-2\"\n                                    src=\"";
    echo base_url("assets/img/icon-toyyibpay.jpeg");
    echo "\">\n                                ";
    echo __("Toyyibpay Malaysia");
    echo "</a></li>\n                        ";
}
echo "                        ";
if($CMSNT->site("squadco_status") == 1) {
    echo "                        <li><a href=\"";
    echo base_url("?action=recharge-squadco");
    echo "\"><img width=\"20px\" class=\"me-2\"\n                                    src=\"";
    echo base_url("assets/img/icon-squadco.png");
    echo "\">\n                                ";
    echo __("Squadco Nigeria");
    echo "</a></li>\n                        ";
}
echo "                        ";
if($CMSNT->site("flutterwave_status") == 1) {
    echo "                        <li><a href=\"";
    echo base_url("?action=recharge-flutterwave");
    echo "\"><img width=\"20px\" class=\"me-2\"\n                                    src=\"";
    echo base_url("mod/img/icon-flutterwave.png");
    echo "\">\n                                ";
    echo __("Flutterwave");
    echo "</a></li>\n                        ";
}
echo "                        ";
foreach ($CMSNT->get_list(" SELECT * FROM `payment_manual` WHERE `display` = 1 ") as $payment_manual) {
    echo "                        <li><a href=\"";
    echo base_url("recharge-manual/" . $payment_manual["slug"]);
    echo "\"><img width=\"20px\"\n                                    class=\"me-2\" src=\"";
    echo base_url($payment_manual["icon"]);
    echo "\">\n                                ";
    echo __($payment_manual["title"]);
    echo "</a></li>\n                        ";
}
echo "                    </ul>\n                </li>\n                <li><a class=\"nav-link dropdown-link\" href=\"#\"><i\n                            class=\"fa-solid fa-clock-rotate-left\"></i>";
echo __("Lịch sử");
echo "</a>\n                    <ul class=\"dropdown-list\">\n                        <li><a href=\"";
echo base_url("product-orders/");
echo "\">";
echo __("Lịch sử đơn hàng");
echo "</a>\n                        </li>\n                        <li><a href=\"";
echo base_url("client/logs");
echo "\">";
echo __("Nhật ký hoạt động");
echo "</a></li>\n                        <li><a href=\"";
echo base_url("client/transactions");
echo "\">";
echo __("Biến động số dư");
echo "</a>\n                        </li>\n                    </ul>\n                </li>\n                ";
if($CMSNT->site("affiliate_status") == 1) {
    echo "                <li><a class=\"nav-link dropdown-link\" href=\"#\"><i\n                            class=\"fa-solid fa-money-bill-trend-up\"></i>";
    echo __("Affiliate Program");
    echo "</a>\n                    <ul class=\"dropdown-list\">\n                        <li><a href=\"";
    echo base_url("?action=affiliates");
    echo "\">";
    echo __("Thống kê");
    echo "</a></li>\n                        <li><a href=\"";
    echo base_url("?action=affiliate-history");
    echo "\">";
    echo __("Lịch sử");
    echo "</a>\n                        </li>\n                        <li><a href=\"";
    echo base_url("?action=affiliate-withdraw");
    echo "\">";
    echo __("Rút tiền");
    echo "</a>\n                        </li>\n                    </ul>\n                </li>\n                ";
}
echo "                ";
if($CMSNT->site("blog_status") == 1) {
    echo "                <li><a class=\"nav-link\" href=\"";
    echo base_url("blogs");
    echo "\"><i\n                            class=\"fa-solid fa-newspaper\"></i>";
    echo __("Blogs");
    echo "</a></li>\n                ";
}
echo "                ";
if($CMSNT->site("api_status") == 1) {
    echo "                <li><a class=\"nav-link\" href=\"";
    echo base_url("document-api");
    echo "\"><i\n                            class=\"fa-regular fa-file-code\"></i>";
    echo __("Tài liệu API");
    echo "</a></li>\n                ";
}
echo "                ";
if(isset($getUser) && $getUser["admin"] != 0) {
    echo "                <li><a class=\"nav-link\" href=\"";
    echo base_url_admin();
    echo "\"><i\n                            class=\"fa-solid fa-gear\"></i>";
    echo __("Admin Panel");
    echo "</a></li>\n                ";
}
echo "                <li><a class=\"nav-link\" href=\"";
echo base_url("client/logout");
echo "\"><i\n                            class=\"icofont-logout\"></i>";
echo __("Đăng xuất");
echo "</a></li>\n            </ul>\n            <div class=\"nav-info-group\">\n                <div class=\"nav-info\"><i class=\"icofont-ui-touch-phone\"></i>\n                    <p><span>";
echo $CMSNT->site("hotline");
echo "</span></p>\n                </div>\n                <div class=\"nav-info\"><i class=\"icofont-ui-email\"></i>\n                    <p><span>";
echo $CMSNT->site("email");
echo "</span></p>\n                </div>\n            </div>\n        </div>\n    </aside>\n    <div class=\"mobile-menu\">\n        <a href=\"";
echo base_url("client/home");
echo "\" title=\"";
echo __("Trang chủ");
echo "\"\n            class=\"";
echo active_sidebar_client(["home", ""]);
echo "\"><i\n                class=\"fas fa-home\"></i><span>";
echo __("Trang chủ");
echo "</span></a>\n        <button class=\"cate-btn\" title=\"";
echo __("Sản phẩm");
echo "\"><i\n                class=\"fas fa-list\"></i><span>";
echo __("Sản phẩm");
echo "</span></button>\n        <button\n            class=\"cart-btn ";
echo active_sidebar_client(["recharge-flutterwave", "recharge-bank", "recharge-crypto", "recharge-card", "recharge-paypal", "recharge-perfectmoney", "recharge-toyyibpay", "recharge-squadco", "recharge-flutterwave", "recharge-manual"]);
echo "\"\n            title=\"";
echo __("Nạp tiền");
echo "\"><i\n                class=\"fa-solid fa-building-columns\"></i><span>";
echo __("Nạp tiền");
echo "</span></button>\n        <a href=\"";
echo base_url("product-orders");
echo "\"\n            class=\"";
echo active_sidebar_client(["product-orders", "product-order"]);
echo "\" title=\"";
echo __("Đơn hàng");
echo "\"><i\n                class=\"fa-solid fa-cart-shopping\"></i><span>";
echo __("Đơn hàng");
echo "</span></a>\n        <a href=\"";
echo base_url("client/profile");
echo "\" title=\"Profile\" class=\"";
echo active_sidebar_client(["profile"]);
echo "\"><i\n                class=\"fa-solid fa-user\"></i><span>";
echo __("Thông tin");
echo "</span></a>\n    </div>\n\n\n\n\n\n    <script>\n    function changeLanguage() {\n        var id = document.getElementById(\"changeLanguage\").value;\n        \$.ajax({\n            url: \"";
echo BASE_URL("ajaxs/client/update.php");
echo "\",\n            method: \"POST\",\n            dataType: \"JSON\",\n            data: {\n                action: 'changeLanguage',\n                id: id\n            },\n            success: function(respone) {\n                if (respone.status == 'success') {\n                    location.reload();\n                } else {\n                    cuteAlert({\n                        type: \"error\",\n                        title: \"Error\",\n                        message: respone.msg,\n                        buttonText: \"Okay\"\n                    });\n                }\n            },\n            error: function() {\n                alert(html(response));\n                history.back();\n            }\n        });\n    }\n    </script>\n    <script>\n    function changeCurrency() {\n        var id = document.getElementById(\"changeCurrency\").value;\n        \$.ajax({\n            url: \"";
echo BASE_URL("ajaxs/client/update.php");
echo "\",\n            method: \"POST\",\n            dataType: \"JSON\",\n            data: {\n                action: 'changeCurrency',\n                id: id\n            },\n            success: function(respone) {\n                if (respone.status == 'success') {\n                    location.reload();\n                } else {\n                    cuteAlert({\n                        type: \"error\",\n                        title: \"Error\",\n                        message: respone.msg,\n                        buttonText: \"Okay\"\n                    });\n                }\n            },\n            error: function() {\n                alert(html(response));\n                history.back();\n            }\n        });\n    }\n    </script>";

?>