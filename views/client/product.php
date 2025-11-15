<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
if(isset($_GET["slug"])) {
    $slug = check_string($_GET["slug"]);
    if(!($product = $CMSNT->get_row("SELECT * FROM `products` WHERE `slug` = '" . $slug . "' AND `status` = 1 "))) {
        redirect(base_url());
    }
} else {
    redirect(base_url());
}
$body = ["title" => __($product["name"]) . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/product-details.css\">\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n \n";
$body["footer"] = "\n \n\n \n";
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
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
echo "<div style=\"margin-bottom:40px;\"></div>\n\n\n<section class=\"inner-section\" style=\"margin-bottom:40px;\">\n    <div class=\"container\">\n        <div class=\"row\">\n            ";
if($CMSNT->site("product_photo_display") == 1 && $product["images"] != "") {
    echo "            <div class=\"col-lg-4\" id=\"col1\" style=\"margin-bottom:20px;\">\n                <div class=\"details-gallery mb-1\">\n                    <div class=\"details-label-group\">\n                        ";
    if(0 < $product["discount"]) {
        echo "                        <label class=\"details-label off\">Sale ";
        echo $product["discount"];
        echo "%</label>\n                        ";
    }
    echo "                    </div>\n                    <ul class=\"details-preview\">\n                        ";
    foreach (explode(PHP_EOL, $product["images"]) as $image) {
        echo "                        <li><img src=\"";
        echo base_url(dirImageProduct($image));
        echo "\" alt=\"product\"></li>\n                        ";
    }
    echo "                    </ul>\n                    <ul class=\"details-thumb\">\n                        ";
    foreach (explode(PHP_EOL, $product["images"]) as $image) {
        echo "                        <li><img src=\"";
        echo base_url(dirImageProduct($image));
        echo "\" alt=\"product\"></li>\n                        ";
    }
    echo "                    </ul>\n                </div>\n            </div>\n            ";
}
echo "\n            <div class=\"col-lg-8\" id=\"col2\">\n                <div class=\"details-content\">\n                    <h3 class=\"details-name\"><img\n                            src=\"";
echo base_url(getRowRealtime("categories", $product["category_id"], "icon"));
echo "\"\n                            width=\"25px\"> ";
echo __($product["name"]);
echo "</h3>\n                    <div class=\"details-meta\">\n                        ";
$stock = $product["supplier_id"] != 0 ? $product["api_stock"] : getStock($product["code"]);
echo "                        <p><label class=\"label-text feat\">";
echo __("Kho hàng:");
echo "                                <strong>";
echo format_cash($stock);
echo "</strong></label>\n                            ";
if($CMSNT->site("product_sold_display") == 1) {
    echo "                            <label class=\"label-text order\">";
    echo __("Đã bán:");
    echo " <strong>\n                                    ";
    echo format_cash($product["sold"]);
    echo "</strong></label>\n                            ";
}
echo "                        </p>\n                    </div>\n                    ";
if($CMSNT->site("product_rating_display") == 1) {
    echo "                    <div class=\"details-rating\"><i class=\"active icofont-star\"></i><i class=\"active icofont-star\"></i><i\n                            class=\"active icofont-star\"></i><i class=\"active icofont-star\"></i><i\n                            class=\"icofont-star\"></i><a href=\"#\">(3 reviews)</a>\n                    </div>\n                    ";
}
echo "                    <h3 class=\"details-price\">\n                        ";
echo 0 < $product["discount"] ? "<del>" . format_currency($product["price"]) . "</del>" : "";
echo "<span>";
echo format_currency($product["price"] - $product["price"] * $product["discount"] / 100);
echo "</span>\n                    </h3>\n                    <p class=\"details-desc\">";
echo str_replace(PHP_EOL, "<br>", $product["short_desc"]);
echo "</p>\n                    <div class=\"details-list-group\"><label class=\"details-list-title\">Share:</label>\n                        <ul class=\"details-share-list\">\n                            <li><a href=\"https://www.facebook.com/sharer/sharer.php?u=";
echo base_url("product/" . $product["slug"]);
echo "\"\n                                    title=\"Facebook\"><i class=\"fa-brands fa-facebook\"></i></a></li>\n                            <li><a href=\"https://twitter.com/intent/tweet?url=";
echo base_url("product/" . $product["slug"]);
echo "\"\n                                    title=\"Twitter\"><i class=\"fa-brands fa-square-x-twitter\"></i></a></li>\n                            <li><a href=\"https://www.linkedin.com/sharing/share-offsite/?url=";
echo base_url("product/" . $product["slug"]);
echo "\"\n                                    title=\"Linkedin\"><i class=\"fa-brands fa-linkedin\"></i></a></li>\n                            <li><a href=\"https://www.instagram.com/?url=";
echo base_url("product/" . $product["slug"]);
echo "\"\n                                    title=\"Instagram\"><i class=\"fa-brands fa-instagram\"></i></a></li>\n                        </ul>\n                    </div>\n                    <div class=\"row\">\n                        <div class=\"col-lg-6\">\n                            <input type=\"hidden\" id=\"product_id\" value=\"";
echo $product["id"];
echo "\">\n                            <input type=\"hidden\" id=\"token\" value=\"";
echo isset($getUser) ? $getUser["token"] : "";
echo "\">\n                            <button id=\"openModal_";
echo $product["id"];
echo "\"\n                                onclick=\"openModal(`";
echo isset($getUser) ? $getUser["token"] : NULL;
echo "`, `";
echo $product["id"];
echo "`)\"\n                                class=\"btn-buy mb-2\"><i class=\"fa-solid fa-cart-shopping\"></i>\n                                ";
echo mb_strtoupper(__("MUA NGAY"));
echo "</button>\n                        </div>\n                        <div class=\"col-lg-6\">\n                            <a class=\"btn-more\" href=\"";
echo base_url();
echo "\" type=\"button\">\n                                <i class=\"fa-solid fa-arrow-left\"></i>\n                                <span>";
echo mb_strtoupper(__("Quay lại"));
echo "</span></a>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n<section class=\"inner-section\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-lg-12\">\n                <ul class=\"nav nav-tabs\">\n                    <li><a href=\"#tab-desc\" class=\"tab-link active\" data-bs-toggle=\"tab\">";
echo __("Chi tiết");
echo "</a></li>\n                    ";
if($CMSNT->site("api_status") == 1) {
    echo "                    <li><a href=\"#tab-api\" class=\"tab-link\" data-bs-toggle=\"tab\">";
    echo __("Tích hợp API");
    echo "</a></li>\n                    ";
}
echo "                    ";
if($CMSNT->site("affiliate_status") == 1) {
    echo "                    <li><a href=\"#tab-aff\" class=\"tab-link\" data-bs-toggle=\"tab\">";
    echo __("Tiếp thị liên kết");
    echo "</a></li>\n                    ";
}
echo "                </ul>\n            </div>\n        </div>\n        <div class=\"tab-pane fade show active\" id=\"tab-desc\">\n            <div class=\"row\">\n                <div class=\"col-lg-12\">\n                    <div class=\"product-details-frame\">\n                        ";
echo base64_decode($product["description"]);
echo "                    </div>\n                </div>\n            </div>\n        </div>\n        ";
if($CMSNT->site("api_status") == 1) {
    echo "        <div class=\"tab-pane fade\" id=\"tab-api\">\n            <div class=\"row\">\n                <div class=\"col-lg-12\">\n                    <div class=\"product-details-frame\">\n                        ";
    if(isset($getUser)) {
        echo "                        <div class=\"form-group row\">\n                            <label class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
        echo __("API Key:");
        echo "</label>\n                            <div class=\"col-lg-8 fv-row\">\n                                <strong class=\"copy\" style=\"color: blue;\" id=\"input_api_key\"\n                                    data-clipboard-target=\"#input_api_key\" onclick=\"copy()\" data-toggle=\"tooltip\"\n                                    data-placement=\"bottom\" title=\"Nhấn vào để Copy\">";
        echo $getUser["api_key"];
        echo "</strong>\n                                <button onclick=\"changeAPIKey(`";
        echo $getUser["token"];
        echo "`)\" data-toggle=\"tooltip\"\n                                    data-placement=\"bottom\"\n                                    title=\"Thay đổi API KEY khác nếu API KEY cũ của bạn bị lộ ra ngoài\"\n                                    class=\"btn btn-danger btn-sm\"><i class=\"fa-solid fa-rotate\"></i></button>\n                            </div>\n                        </div>\n                        <div class=\"form-group row\">\n                            <label\n                                class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
        echo __("Lấy chi tiết sản phẩm");
        echo "</label>\n                            <div class=\"col-lg-8 fv-row\">\n                                <div class=\"input-group mb-3\">\n                                    <span class=\"input-group-text\">GET</span>\n                                    <input type=\"text\" class=\"form-control\" id=\"api_product\"\n                                        value=\"";
        echo base_url("api/product.php?api_key=" . $getUser["api_key"] . "&product=" . $product["id"]);
        echo "\">\n                                    <button class=\"btn btn-primary btn-sm copy\" data-clipboard-target=\"#api_product\"\n                                        onclick=\"copy()\"><i class=\"fa-solid fa-copy\"></i> Copy</button>\n                                </div>\n                            </div>\n                        </div>\n                        <div class=\"form-group row\">\n                            <label class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
        echo __("Mua hàng");
        echo "</label>\n                            <div class=\"col-lg-8 fv-row\">\n                                <div class=\"input-group mb-3\">\n                                    <span class=\"input-group-text\">POST</span>\n                                    <input type=\"text\" class=\"form-control\" id=\"api_buy\"\n                                        value=\"";
        echo base_url("api/buy_product");
        echo "\">\n                                    <button class=\"btn btn-primary btn-sm copy\" data-clipboard-target=\"#api_buy\"\n                                        onclick=\"copy()\"><i class=\"fa-solid fa-copy\"></i> Copy</button>\n                                </div>\n                                <p>form-data:</p>\n                                <ul class=\"mb-1\">\n                                    <li><strong>action</strong>: buyProduct</li>\n                                    <li><strong>id</strong>: ";
        echo $product["id"];
        echo "</li>\n                                    <li><strong>amount</strong>: ";
        echo __("Số lượng cần mua");
        echo "</li>\n                                    <li><strong>coupon</strong>: ";
        echo __("Mã giảm giá nếu có");
        echo "</li>\n                                    <li><strong>api_key</strong>: <span\n                                            style=\"color:blue;\">";
        echo $getUser["api_key"];
        echo "</span></li>\n                                </ul>\n                                <p>Response:</p>\n                                <textarea class=\"form-control\">\n{\n    \"status\": \"success\",\n    \"msg\": \"Tạo đơn hàng thành công!\",\n    \"trans_id\": \"JF465f728224ce11\",\n    \"data\": [\n        \"1000040304952|GUTJXYIFPWLHCNDOMBRKVAQESZ\",\n        \"1000087676467|IVMRLABECWTQYUXHPOFNJDSZGK\",\n        \"1000073612513|ERKPFTVCAJDLINWMXSUOGBQZHY\",\n        \"1000011975745|JXEZTVLYOFBQNRHGDKMIPUCAWS\"\n    ]\n}\n                            </textarea>\n                            </div>\n                        </div>\n                        <script type=\"text/javascript\">\n                        new ClipboardJS(\".copy\");\n\n                        function copy() {\n                            showMessage(\"";
        echo __("Đã sao chép vào bộ nhớ tạm");
        echo "\", 'success');\n                        }\n                        </script>\n                        <script>\n                        function changeAPIKey(token) {\n                            Swal.fire({\n                                title: \"";
        echo __("Bạn có chắc không?");
        echo "\",\n                                text: \"";
        echo __("Hệ thống sẽ thay đổi API KEY nếu bạn Đồng Ý");
        echo "\",\n                                icon: \"warning\",\n                                showCancelButton: true,\n                                confirmButtonColor: \"#3085d6\",\n                                cancelButtonColor: \"#d33\",\n                                confirmButtonText: \"";
        echo __("Tôi đồng ý");
        echo "\",\n                                cancelButtonText: \"";
        echo __("Đóng");
        echo "\"\n                            }).then((result) => {\n                                if (result.isConfirmed) {\n                                    \$.ajax({\n                                        url: \"";
        echo BASE_URL("ajaxs/client/auth.php");
        echo "\",\n                                        method: \"POST\",\n                                        dataType: \"JSON\",\n                                        data: {\n                                            token: token,\n                                            action: 'changeAPIKey'\n                                        },\n                                        success: function(respone) {\n                                            if (respone.status == 'success') {\n                                                showMessage(result.msg, result.status);\n                                                location.reload();\n                                            } else {\n                                                Swal.fire({\n                                                    title: \"";
        echo __("Thất bại!");
        echo "\",\n                                                    text: respone.msg,\n                                                    icon: \"error\"\n                                                });\n                                            }\n                                        },\n                                        error: function() {\n                                            alert(html(response));\n                                            location.reload();\n                                        }\n                                    });\n                                }\n                            });\n                        }\n                        </script>\n                        ";
    } else {
        echo "                        <p>";
        echo __("Vui lòng đăng nhập để sử dụng chức năng này");
        echo "</p>\n\n                        ";
    }
    echo "                    </div>\n                </div>\n            </div>\n        </div>\n        ";
}
echo "        ";
if($CMSNT->site("affiliate_status") == 1) {
    echo "        <div class=\"tab-pane fade\" id=\"tab-aff\">\n            ";
    if(isset($getUser)) {
        echo "            <div class=\"row\">\n                <div class=\"col-lg-12\">\n                    <div class=\"product-details-frame\">\n                        <p class=\"mb-3\">";
        echo __("Chia sẻ liên kết sản phẩm dưới đây cho bạn bè của bạn, bạn sẽ nhận được hoa hồng khi bạn bè của bạn mua hàng thông qua liên kết phía dưới.");
        echo "</p>\n                        <div class=\"form-group row\">\n                            <label\n                                class=\"col-lg-3 col-form-label required fw-bold fs-6\">";
        echo __("Liên kết sản phẩm");
        echo "</label>\n                            <div class=\"col-lg-9 fv-row\">\n                                <div class=\"input-group mb-3\">\n                                    <input type=\"text\" class=\"form-control\" id=\"lien_ket_gioi_thieu\"\n                                        value=\"";
        echo base_url("product/" . $product["slug"] . "&aff=" . $getUser["id"]);
        echo "\">\n                                    <button class=\"btn btn-primary btn-sm copy\"\n                                        data-clipboard-target=\"#lien_ket_gioi_thieu\" onclick=\"copy()\"><i\n                                            class=\"fa-solid fa-copy\"></i> Copy</button>\n                                </div>\n                            </div>\n                        </div>\n                        <div class=\"form-group row\">\n                            <label class=\"col-lg-3 col-form-label required fw-bold fs-6\">";
        echo __("Tỷ lệ hoa hồng");
        echo "</label>\n                            <div class=\"col-lg-9 fv-row\">\n                                <strong  style=\"color: blue;\">";
        $ck = $CMSNT->site("affiliate_ck");
        if(getRowRealtime("users", $getUser["id"], "ref_ck") != 0) {
            $ck = getRowRealtime("users", $getUser["id"], "ref_ck");
        }
        echo $ck;
        echo "%</strong>\n                                 \n                            </div>\n                        </div>\n                        <center><a type=\"button\" href=\"";
        echo base_url("?action=affiliates");
        echo "\"\n                                class=\"btn-more-new mb-3\">";
        echo __("Xem thêm");
        echo "</a></center>\n                    </div>\n                </div>\n            </div>\n            ";
    } else {
        echo "            <p>";
        echo __("Vui lòng đăng nhập để sử dụng chức năng này");
        echo "</p>\n            ";
    }
    echo "        </div>\n        ";
}
echo "\n\n\n\n    </div>\n</section>\n\n\n\n<div class=\"modal fade\" id=\"openModal\" tabindex=\"-1\" aria-labelledby=\"modal-block-popout\" role=\"dialog\"\n    aria-hidden=\"true\">\n    <div class=\"modal-dialog modal-lg modal-dialog-popout\" role=\"document\">\n        <div class=\"modal-content\">\n            <div id=\"modalContent\"></div>\n        </div>\n    </div>\n</div>\n\n<script>\nfunction openModal(token, id) {\n    \$(\"#modalContent\").html('');\n    var originalButtonContent = \$('#openModal_' + id).html();\n    \$('#openModal_' + id).html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "</span>')\n        .prop('disabled',\n            true);\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/client/modal/view-product.php");
echo "\",\n        method: \"GET\",\n        data: {\n            id: id,\n            token: token\n        },\n        success: function(data) {\n            \$(\"#modalContent\").html(data);\n            \$('#openModal').modal('show');\n            \$('#openModal_' + id).html(originalButtonContent).prop('disabled', false);\n        },\n        error: function() {\n            Swal.fire('";
echo __("Thất bại!");
echo "', data, 'error');\n        }\n    });\n}\n</script>\n<script>\nfunction addFavorite2() {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/client/update.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'toggleFavorite',\n            id: \$(\"#product_id\").val(),\n            token: \$(\"#token\").val()\n        },\n        success: function(data) {\n            if (data.status == 'success') {\n                if (data.button == true) {\n                    \$(\"#btnAddFavorite2\").hide();\n                    \$(\"#btnRemoveFavorite2\").show();\n                    // Lấy thẻ sup theo id\n                    var numFavoritesElement = document.getElementById(\"numFavorites\");\n                    // Giá trị hiện tại trong thẻ sup\n                    var currentValue = parseInt(numFavoritesElement.textContent);\n                    // Giả sử bạn muốn cộng thêm 1 vào giá trị hiện tại\n                    var newValue = currentValue + 1;\n                    // Cập nhật giá trị trong thẻ sup\n                    numFavoritesElement.textContent = newValue;\n                } else {\n                    \$(\"#btnAddFavorite2\").show();\n                    \$(\"#btnRemoveFavorite2\").hide();\n                    // Lấy thẻ sup theo id\n                    var numFavoritesElement = document.getElementById(\"numFavorites\");\n                    // Giá trị hiện tại trong thẻ sup\n                    var currentValue = parseInt(numFavoritesElement.textContent);\n                    // Giả sử bạn muốn cộng thêm 1 vào giá trị hiện tại\n                    var newValue = currentValue - 1;\n                    // Cập nhật giá trị trong thẻ sup\n                    numFavoritesElement.textContent = newValue;\n                }\n            }\n            showMessage(data.msg, data.status);\n        },\n        error: function() {\n            showMessage('";
echo __("Vui lòng liên hệ Developer");
echo "', 'error');\n        }\n    });\n}\n</script>\n\n";
require_once __DIR__ . "/footer.php";

?>