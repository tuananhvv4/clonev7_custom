<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Tài liệu tích hợp API") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n \n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_user.php";
if($CMSNT->site("api_status") != 1) {
    redirect(base_url("client/home"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
echo "\n\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-md-12\">\n                <div class=\"home-heading mb-3\">\n                    <h3><i class=\"fa-regular fa-file-code m-2\"></i>\n                        ";
echo mb_strtoupper(__("Tài liệu tích hợp API"));
echo "                    </h3>\n                </div>\n                <div class=\"account-card pt-3\">\n                    <div class=\"form-group row\">\n                        <label class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
echo __("API Key:");
echo "</label>\n                        <div class=\"col-lg-8 fv-row\">\n                            <strong class=\"copy\" style=\"color: blue;\" id=\"api_key\" data-clipboard-target=\"#api_key\"\n                                onclick=\"copy()\" data-toggle=\"tooltip\" data-placement=\"bottom\"\n                                title=\"Nhấn vào để Copy\">";
echo $getUser["api_key"];
echo "</strong> <button\n                                onclick=\"changeAPIKey(`";
echo $getUser["token"];
echo "`)\" data-toggle=\"tooltip\"\n                                data-placement=\"bottom\"\n                                title=\"Thay đổi API KEY khác nếu API KEY cũ của bạn bị lộ ra ngoài\"\n                                class=\"btn btn-danger btn-sm\"><i class=\"fa-solid fa-rotate\"></i></button>\n                        </div>\n                    </div>\n                    <div class=\"form-group row\">\n                        <label\n                            class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
echo __("Lấy thông tin tài khoản");
echo "</label>\n                        <div class=\"col-lg-8 fv-row\">\n                            <div class=\"input-group mb-3\">\n                                <span class=\"input-group-text\">GET</span>\n                                <input type=\"text\" class=\"form-control\" id=\"api_profile\" value=\"";
echo base_url("api/profile.php?api_key=" . $getUser["api_key"]);
echo "\">\n                                <button class=\"btn btn-primary btn-sm copy\" data-clipboard-target=\"#api_profile\" onclick=\"copy()\"><i class=\"fa-solid fa-copy\"></i> Copy</button>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"form-group row\">\n                        <label\n                            class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
echo __("Lấy danh sách chuyên mục và sản phẩm");
echo "</label>\n                        <div class=\"col-lg-8 fv-row\">\n                            <div class=\"input-group mb-3\">\n                                <span class=\"input-group-text\">GET</span>\n                                <input type=\"text\" class=\"form-control\" id=\"api_products\" value=\"";
echo base_url("api/products.php?api_key=" . $getUser["api_key"]);
echo "\">\n                                <button class=\"btn btn-primary btn-sm copy\" data-clipboard-target=\"#api_products\" onclick=\"copy()\"><i class=\"fa-solid fa-copy\"></i> Copy</button>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"form-group row\">\n                        <label\n                            class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
echo __("Lấy chi tiết sản phẩm");
echo "</label>\n                        <div class=\"col-lg-8 fv-row\">\n                            <div class=\"input-group mb-3\">\n                                <span class=\"input-group-text\">GET</span>\n                                <input type=\"text\" class=\"form-control\" id=\"api_product\" value=\"";
echo base_url("api/product.php?api_key=" . $getUser["api_key"] . "&product=3");
echo "\">\n                                <button class=\"btn btn-primary btn-sm copy\" data-clipboard-target=\"#api_product\" onclick=\"copy()\"><i class=\"fa-solid fa-copy\"></i> Copy</button>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"form-group row\">\n                        <label\n                            class=\"col-lg-4 col-form-label required fw-bold fs-6\">";
echo __("Mua hàng");
echo "</label>\n                        <div class=\"col-lg-8 fv-row\">\n                            <div class=\"input-group mb-3\">\n                                <span class=\"input-group-text\">POST or GET</span>\n                                <input type=\"text\" class=\"form-control\" id=\"api_buy\" value=\"";
echo base_url("api/buy_product");
echo "\">\n                                <button class=\"btn btn-primary btn-sm copy\" data-clipboard-target=\"#api_buy\" onclick=\"copy()\"><i class=\"fa-solid fa-copy\"></i> Copy</button>\n                            </div>\n                            <p>form-data:</p>\n                            <ul class=\"mb-1\">\n                                <li><strong>action</strong>: buyProduct</li>\n                                <li><strong>id</strong>: ";
echo __("ID sản phẩm cần mua");
echo "</li>\n                                <li><strong>amount</strong>: ";
echo __("Số lượng cần mua");
echo "</li>\n                                <li><strong>coupon</strong>: ";
echo __("Mã giảm giá nếu có");
echo "</li>\n                                <li><strong>api_key</strong>: <span style=\"color:blue;\">";
echo $getUser["api_key"];
echo "</span></li>\n                            </ul>\n                            <p>Response:</p>\n                            <textarea class=\"form-control\">\n{\n    \"status\": \"success\",\n    \"msg\": \"Tạo đơn hàng thành công!\",\n    \"trans_id\": \"JF465f728224ce11\",\n    \"data\": [\n        \"1000040304952|GUTJXYIFPWLHCNDOMBRKVAQESZ\",\n        \"1000087676467|IVMRLABECWTQYUXHPOFNJDSZGK\",\n        \"1000073612513|ERKPFTVCAJDLINWMXSUOGBQZHY\",\n        \"1000011975745|JXEZTVLYOFBQNRHGDKMIPUCAWS\"\n    ]\n}\n                            </textarea>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo " \n\n\n<script type=\"text/javascript\">\nnew ClipboardJS(\".copy\");\n\nfunction copy() {\n    showMessage(\"";
echo __("Đã sao chép vào bộ nhớ tạm");
echo "\", 'success');\n}\n</script>\n<script>\nfunction changeAPIKey(token) {\n    Swal.fire({\n        title: \"";
echo __("Bạn có chắc không?");
echo "\",\n        text: \"";
echo __("Hệ thống sẽ thay đổi API KEY nếu bạn Đồng Ý");
echo "\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"";
echo __("Tôi đồng ý");
echo "\",\n        cancelButtonText: \"";
echo __("Đóng");
echo "\"\n    }).then((result) => {\n        if (result.isConfirmed) {\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/client/auth.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    token: token,\n                    action: 'changeAPIKey'\n                },\n                success: function(respone) {\n                    if (respone.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        Swal.fire({\n                            title: \"";
echo __("Thất bại!");
echo "\",\n                            text: respone.msg,\n                            icon: \"error\"\n                        });\n                    }\n                },\n                error: function() {\n                    alert(html(response));\n                    location.reload();\n                }\n            });\n        }\n    });\n}\n</script>";

?>