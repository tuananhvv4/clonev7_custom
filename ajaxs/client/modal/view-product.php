<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../libs/db.php";
require_once __DIR__ . "/../../../libs/lang.php";
require_once __DIR__ . "/../../../libs/helper.php";
if(isset($_COOKIE["token"])) {
    $getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `token` = '" . check_string($_COOKIE["token"]) . "' ");
    if(!$getUser) {
        exit(__("Vui lòng thử lại sau!"));
    }
    $_SESSION["login"] = $getUser["token"];
}
if(isset($_SESSION["login"])) {
    require_once __DIR__ . "/../../../models/is_user.php";
}
if(!($product = $CMSNT->get_row(" SELECT * FROM `products` WHERE `id` = '" . check_string($_GET["id"]) . "' AND `status` = 1 "))) {
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Sản phảm không tồn tại") . "\")){location.reload();}</script>");
}
$stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
echo "<button class=\"modal-close icofont-close\" data-bs-dismiss=\"modal\"></button>\n<div class=\"product-view\">\n    <div class=\"row\">\n        <div class=\"col-md-6 col-lg-6\">\n            <div class=\"view-details\">\n                <h3 class=\"view-name\"><a\n                        href=\"";
echo base_url("product/" . $product["slug"]);
echo "\">";
echo __($product["name"]);
echo "</a></h3>\n                <div class=\"view-meta\">\n                    <p><label class=\"label-text feat\">";
echo __("Kho hàng:");
echo "                            <strong>";
echo format_cash($stock);
echo "</strong></label>\n                        ";
if($CMSNT->site("product_sold_display") == 1) {
    echo "                        <label class=\"label-text order\">";
    echo __("Đã bán:");
    echo "                            <strong>";
    echo format_cash($product["sold"]);
    echo "</strong></label>\n                        ";
}
echo "                    </p>\n                </div>\n                ";
if($CMSNT->site("product_rating_display") == 1) {
    echo "                <div class=\"view-rating\">\n                    <i class=\"active icofont-star\"></i>\n                    <i class=\"active icofont-star\"></i>\n                    <i class=\"active icofont-star\"></i>\n                    <i class=\"active icofont-star\"></i><i class=\"icofont-star\"></i>\n                    <a href=\"product-video.html\">(3 reviews)</a>\n                </div>\n                ";
}
echo "\n                <h3 class=\"view-price\">\n                    ";
echo 0 < $product["discount"] ? "<del>" . format_currency($product["price"]) . "</del>" : "";
echo "<span>";
echo format_currency($product["price"] - $product["price"] * $product["discount"] / 100);
echo "</span>\n                </h3>\n                ";
if(!isset($getUser) || $getUser["discount"] == 0) {
    echo "                ";
    if(0 < $CMSNT->num_rows(" SELECT * FROM product_discount WHERE product_id = '" . $product["id"] . "' ")) {
        echo "                <div class=\"mb-3 card-hot-deal\">\n                    <span><i class=\"fa-solid fa-fire-flame-simple\" style=\"color:red;\"></i> Hot Deal: </span><br>\n                    ";
        foreach ($CMSNT->get_list(" SELECT * FROM product_discount WHERE product_id = '" . $product["id"] . "' ") as $product_discount) {
            echo "                    <span> * ";
            echo __("Mua");
            echo " >= <b style=\"color:blue;\">";
            echo format_cash($product_discount["min"]);
            echo "</b>\n                        ";
            echo __("tài khoản giảm");
            echo " <b style=\"color:red;\">";
            echo $product_discount["discount"];
            echo "%</b></span>\n                    <br>\n                    ";
        }
        echo "                </div>\n                ";
    }
    echo "                ";
}
echo "                <p class=\"view-desc\">";
echo str_replace(PHP_EOL, "<br>", $product["short_desc"]);
echo "</p>\n                <div class=\"view-list-group\">\n                    <label class=\"view-list-title\">";
echo __("Chia sẻ:");
echo "</label>\n                    <ul class=\"view-share-list\">\n                        <li><a href=\"https://www.facebook.com/sharer/sharer.php?u=";
echo base_url("product/" . $product["slug"]);
echo "\"\n                                title=\"Facebook\"><i class=\"fa-brands fa-facebook\"></i></a></li>\n                        <li><a href=\"https://twitter.com/intent/tweet?url=";
echo base_url("product/" . $product["slug"]);
echo "\"\n                                title=\"Twitter\"><i class=\"fa-brands fa-square-x-twitter\"></i></a></li>\n                        <li><a href=\"https://www.linkedin.com/sharing/share-offsite/?url=";
echo base_url("product/" . $product["slug"]);
echo "\"\n                                title=\"Linkedin\"><i class=\"fa-brands fa-linkedin\"></i></a></li>\n                        <li><a href=\"https://www.instagram.com/?url=";
echo base_url("product/" . $product["slug"]);
echo "\"\n                                title=\"Instagram\"><i class=\"fa-brands fa-instagram\"></i></a></li>\n                    </ul>\n                </div>\n            </div>\n        </div>\n        <div class=\"col-md-6 col-lg-6\">\n            <div class=\"view-details\">\n                <table class=\"table fs-sm mb-0\">\n                    <tbody>\n                        <tr>\n                            <td colspan=\"2\" align=\"center\"><strong>";
echo __("THÔNG TIN MUA HÀNG");
echo "</strong></td>\n                        </tr>\n                        <tr>\n                        <tr>\n                            <td>";
echo __("Số dư của tôi:");
echo "</td>\n                            <td class=\"text-right\"><strong\n                                    class=\"text-wallet\">";
echo isset($getUser) ? format_currency($getUser["money"]) : 0;
echo "</strong>\n                            </td>\n                        </tr>\n                        <td>";
echo __("Số lượng cần mua:");
echo " (<span class=\"text-danger\">*</span>)</td>\n                        <td>\n                            <div class=\"product-action\" style=\"display: flex;\">\n                                <input type=\"hidden\" id=\"product_id\" value=\"";
echo $product["id"];
echo "\">\n                                <input type=\"hidden\" id=\"api_key\" value=\"";
echo isset($getUser) ? $getUser["api_key"] : "";
echo "\">\n                                <input type=\"hidden\" id=\"token\" value=\"";
echo isset($getUser) ? $getUser["token"] : "";
echo "\">\n                                <button class=\"action-minus1\" title=\"Quantity Minus\"><i\n                                        class=\"fa-solid fa-minus\"></i></button>\n                                <input class=\"action-input\" onkeyup=\"totalPayment()\" title=\"Quantity Number\" type=\"number\"\n                                    id=\"amount\" value=\"1\">\n                                <button class=\"action-plus1\" title=\"Quantity Plus\"><i\n                                        class=\"fa-solid fa-plus\"></i></button>\n                            </div>\n                        </td>\n                        </tr>\n                        <tr>\n                            <td>";
echo __("Mã giảm giá:");
echo "</td>\n                            <td><input class=\"form-control-view-product\" onchange=\"totalPayment()\" type=\"text\"\n                                    id=\"coupon\" placeholder=\"";
echo __("Nhập mã giảm giá nếu có");
echo "\"></td>\n                        </tr>\n                        <tr>\n                            <td>";
echo __("Thành tiền:");
echo "</td>\n                            <td class=\"text-right\"><strong id=\"into_money\">0</strong></td>\n                        </tr>\n                        <tr>\n                            <td>";
echo __("Số tiền giảm:");
echo "</td>\n                            <td class=\"text-right\"><strong style=\"color: red;\" id=\"into_discount\">0</strong></td>\n                        </tr>\n                        <tr>\n                            <td>";
echo __("Tổng tiền thanh toán:");
echo "</td>\n                            <td class=\"text-right\"><strong style=\"color: blue;\" id=\"into_pay\">0</strong></td>\n                        </tr>\n                    </tbody>\n                </table>\n                <div class=\"view-add-group\">\n                    <button class=\"btn-buy\" id=\"btnBuy\" onclick=\"buyProduct()\">\n                        <i class=\"fa-solid fa-cart-shopping\"></i>\n                        <span>";
echo __("THANH TOÁN");
echo "</span>\n                    </button>\n                </div>\n                <div class=\"view-action-group\">\n                    ";
$isButtonFavorite = false;
if(isset($getUser["id"])) {
    $isButtonFavorite = $CMSNT->get_row(" SELECT * FROM `favorites` WHERE `user_id` = '" . $getUser["id"] . "' AND `product_id` = '" . $product["id"] . "' ");
}
echo "                    <input type=\"checkbox\" ";
echo $isButtonFavorite ? "checked=\"checked\"" : "";
echo "                        onclick=\"addFavorite()\" id=\"favorite\" class=\"input_favorite\" name=\"favorite-checkbox\"\n                        value=\"favorite-button\">\n                    <label for=\"favorite\" class=\"label_favorite\">\n                        <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\"\n                            stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"\n                            class=\"feather feather-heart\">\n                            <path\n                                d=\"M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z\">\n                            </path>\n                        </svg>\n                        <div class=\"action\">\n                            <span class=\"option-1\">";
echo __("Thêm vào mục yêu thích");
echo "</span>\n                            <span class=\"option-2\">";
echo __("Đã thêm vào mục yêu thích");
echo "</span>\n                        </div>\n                    </label>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n<script>\nfunction buyProduct() {\n    \$('#btnBuy').html('<i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Đang xử lý...");
echo "').prop(\n        'disabled',\n        true);\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/client/product.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'buyProduct',\n            id: \$(\"#product_id\").val(),\n            amount: \$(\"#amount\").val(),\n            coupon: \$(\"#coupon\").val(),\n            api_key: \$(\"#api_key\").val()\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                Swal.fire({\n                    icon: 'success',\n                    title: '";
echo __("Thành công !");
echo "',\n                    text: result.msg,\n                    showDenyButton: true,\n                    confirmButtonText: '";
echo __("Mua thêm");
echo "',\n                    denyButtonText: `";
echo __("Xem chi tiết đơn hàng");
echo "`,\n                }).then((result) => {\n                    if (result.isConfirmed) {\n                        location.reload();\n                    } else if (result.isDenied) {\n                        window.location.href =\n                            '";
echo base_url("product-order/");
echo "' + result.trans_id;\n                    }\n                });\n            } else {\n                Swal.fire('";
echo __("Thất bại!");
echo "', result.msg, 'error');\n            }\n            \$('#btnBuy').html(\n                '<i class=\"fa-solid fa-cart-shopping\"></i> <span>";
echo __("THANH TOÁN");
echo "</span>').prop(\n                'disabled',\n                false);\n        },\n        error: function() {\n            showMessage('";
echo __("Vui lòng liên hệ Developer");
echo "', 'error');\n        }\n    });\n}\n</script>\n<script>\nfunction totalPayment() {\n    const product_id = \$(\"#product_id\").val();\n    const amount = \$(\"#amount\").val();\n    const coupon = \$(\"#coupon\").val();\n    const token = \$(\"#token\").val();\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/client/product.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'total_payment',\n            id: product_id,\n            amount: amount,\n            coupon: coupon,\n            token: token\n        },\n        success: function(data) {\n            if (data.status == 'success') {\n                const into_money = \$(\"#into_money\");\n                const into_discount = \$(\"#into_discount\");\n                const into_pay = \$(\"#into_pay\");\n                into_money.html(data.money);\n                into_discount.html(data.discount);\n                into_pay.html(data.pay);\n                if (data.discount_number != 0) {\n                    showMessage('";
echo __("Áp dụng giảm giá thành công!");
echo "', 'success');\n                }\n            } else {\n                showMessage(data.msg, data.status);\n            }\n        },\n        error: function() {\n            showMessage('";
echo __("Vui lòng liên hệ Developer");
echo "', 'error');\n        }\n    });\n}\ntotalPayment();\n</script>\n\n<script>\nconst inputElement = document.querySelector('#amount');\nconst plusButton = document.querySelector('.action-plus1');\nconst minusButton = document.querySelector('.action-minus1');\nplusButton.addEventListener('click', function() {\n    let currentValue = parseInt(inputElement.value);\n    currentValue++;\n    inputElement.value = currentValue;\n    totalPayment();\n});\nminusButton.addEventListener('click', function() {\n    let currentValue = parseInt(inputElement.value);\n    currentValue = Math.max(1, currentValue - 1);\n    inputElement.value = currentValue;\n    totalPayment();\n});\n</script>\n\n<script>\nfunction addFavorite() {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/client/update.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'toggleFavorite',\n            id: \$(\"#product_id\").val(),\n            token: \$(\"#token\").val()\n        },\n        success: function(data) {\n            if (data.status == 'success') {\n                if (data.button == true) {\n                    \$(\"#btnAddFavorite\").hide();\n                    \$(\"#btnRemoveFavorite\").show();\n                    // Lấy thẻ sup theo id\n                    var numFavoritesElement = document.getElementById(\"numFavorites\");\n                    // Giá trị hiện tại trong thẻ sup\n                    var currentValue = parseInt(numFavoritesElement.textContent);\n                    // Giả sử bạn muốn cộng thêm 1 vào giá trị hiện tại\n                    var newValue = currentValue + 1;\n                    // Cập nhật giá trị trong thẻ sup\n                    numFavoritesElement.textContent = newValue;\n                } else {\n                    \$(\"#btnAddFavorite\").show();\n                    \$(\"#btnRemoveFavorite\").hide();\n                    // Lấy thẻ sup theo id\n                    var numFavoritesElement = document.getElementById(\"numFavorites\");\n                    // Giá trị hiện tại trong thẻ sup\n                    var currentValue = parseInt(numFavoritesElement.textContent);\n                    // Giả sử bạn muốn cộng thêm 1 vào giá trị hiện tại\n                    var newValue = currentValue - 1;\n                    // Cập nhật giá trị trong thẻ sup\n                    numFavoritesElement.textContent = newValue;\n                }\n            }else{\n                showMessage(data.msg, 'error');\n            }\n            \n        },\n        error: function() {\n            showMessage('";
echo __("Vui lòng liên hệ Developer");
echo "', 'error');\n        }\n    });\n}\n</script>";

?>