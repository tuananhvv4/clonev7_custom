<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Thêm API cần kết nối"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../libs/suppliers.php";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
require_once __DIR__ . "/../../models/is_license.php";
if(!checkPermission($getUser["admin"], "manager_suppliers")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["submit"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if(empty($_POST["type"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng chọn loại API cần kết nối\")){window.history.back().location.reload();}</script>");
    }
    $type = check_string($_POST["type"]);
    if(empty($_POST["domain"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập domain cần kết nối\")){window.history.back().location.reload();}</script>");
    }
    $domain = check_string($_POST["domain"]);
    $price = "";
    $token = !empty($_POST["token"]) ? check_string($_POST["token"]) : NULL;
    $checkKey = checkLicenseKey($CMSNT->site("license_key"));
    if(!$checkKey["status"]) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . $checkKey["msg"] . "\")){window.history.back().location.reload();}</script>");
    }
    if($type == "SHOPCLONE6") {
        if(empty($_POST["username"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập username\")){window.history.back().location.reload();}</script>");
        }
        $username = check_string($_POST["username"]);
        if(empty($_POST["password"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập password\")){window.history.back().location.reload();}</script>");
        }
        $password = check_string($_POST["password"]);
        $checkdomain = curl_get("https://api.cmsnt.co/checkdomain.php?domain=" . $domain);
        $checkdomain = json_decode($checkdomain, true);
        if(!$checkdomain["status"]) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $checkdomain["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        $data = curl_get(check_string($_POST["domain"]) . "/api/GetBalance.php?username=" . $username . "&password=" . $password);
        $price = $data;
        $data = json_decode($data, true);
        if(isset($data["status"]) && $data["status"] == "error") {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $data["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
    }
    if($type == "SHOPCLONE7") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập api_key\")){window.history.back().location.reload();}</script>");
        }
        $checkdomain = curl_get("https://api.cmsnt.co/checkdomain.php?domain=" . $domain);
        $checkdomain = json_decode($checkdomain, true);
        if(!$checkdomain["status"]) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $checkdomain["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        $response = balance_API_SHOPCLONE7(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($response, true);
        if($result["status"] == "error") {
            $price = $result["msg"];
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["data"]["money"]);
    }
    if($type == "API_1") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập api_key\")){window.history.back().location.reload();}</script>");
        }
        $response = balance_API_1(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($response, true);
        if(!$result["status"]) {
            $price = $result["msg"];
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["balance"]);
    }
    if($type == "API_4") {
        if(empty($_POST["username"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập username\")){window.history.back().location.reload();}</script>");
        }
        if(empty($_POST["password"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập password\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_4(check_string($_POST["domain"]), check_string($_POST["username"]), check_string($_POST["password"]));
        $result = json_decode($result, true);
        if(!isset($result["data"]["Data"]["userDetail"]["coin"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Thông tin kết nối không chính xác\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency(check_string($result["data"]["Data"]["userDetail"]["coin"]));
        $token = check_string($result["data"]["Data"]["accessToken"]);
    }
    if($type == "API_6") {
        $result = balance_API_6(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if(!isset($result["balance"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["balance"]);
    }
    if($type == "API_9") {
        $result = balance_API_9(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if($result["error"] != 0) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["data"]["balance"]);
    }
    if($type == "API_14") {
        if(empty($_POST["token"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập token\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_14(check_string($_POST["domain"]), check_string($_POST["token"]));
        $result = json_decode($result, true);
        if(!isset($result["user"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["user"]["balance"]);
    }
    if($type == "API17") {
        if(empty($_POST["username"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập username\")){window.history.back().location.reload();}</script>");
        }
        $username = check_string($_POST["username"]);
        if(empty($_POST["password"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập password\")){window.history.back().location.reload();}</script>");
        }
        $password = check_string($_POST["password"]);
        $data = balance_API_17(check_string($_POST["domain"]), $username, $password);
        $price = $data;
        $data = json_decode($data, true);
        if(isset($data["status"]) && $data["status"] == "error") {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $data["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
    }
    if($type == "API_18") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập api_key\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_18(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if(isset($result["error"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["error"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = "\$" . $result["balance"];
    }
    if($type == "API_19") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập api_key\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_19(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if(!isset($result["balance"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency($result["balance"]);
    }
    if($type == "API_20") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập kioskToken\")){window.history.back().location.reload();}</script>");
        }
        if(empty($_POST["token"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập userToken\")){window.history.back().location.reload();}</script>");
        }
        $result = curl_get(check_string($_POST["domain"]) . "api/getStock?kioskToken=" . check_string($_POST["api_key"]) . "&userToken=" . check_string($_POST["token"]));
        $result = json_decode($result, true);
        if(!$result["success"]) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["description"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = check_string($result["name"]);
    }
    if($type == "API_21") {
        if(empty($_POST["token"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập token\")){window.history.back().location.reload();}</script>");
        }
        $price = "Không có API lấy số dư";
    }
    if($type == "API_22") {
        if(empty($_POST["token"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập token\")){window.history.back().location.reload();}</script>");
        }
        $price = "Không có API lấy số dư";
    }
    if($type == "API_23") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập kioskToken\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_23(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if($result["Code"] != 0) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $result["Message"] . "\")){window.history.back().location.reload();}</script>");
        }
        $price = check_string("\$" . $result["Balance"]);
    }
    if($type == "API_24") {
        if(empty($_POST["api_key"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập api_key\")){window.history.back().location.reload();}</script>");
        }
        $result = balance_API_24(check_string($_POST["domain"]), check_string($_POST["api_key"]));
        $result = json_decode($result, true);
        if(!isset($result["data"][0]["money_available"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"[SYSTEM] Thông tin kết nối không chính xác\")){window.history.back().location.reload();}</script>");
        }
        $price = format_currency(check_string($result["data"][0]["money_available"]));
    }
    $isInsert = $CMSNT->insert("suppliers", ["user_id" => $getUser["id"], "type" => $type, "domain" => $domain, "username" => !empty($_POST["username"]) ? check_string($_POST["username"]) : NULL, "password" => !empty($_POST["password"]) ? check_string($_POST["password"]) : NULL, "api_key" => !empty($_POST["api_key"]) ? check_string($_POST["api_key"]) : NULL, "token" => $token, "coupon" => !empty($_POST["coupon"]) ? check_string($_POST["coupon"]) : NULL, "price" => check_string($price), "discount" => check_string($_POST["discount"]), "update_name" => check_string($_POST["update_name"]), "sync_category" => !empty($_POST["sync_category"]) ? check_string($_POST["sync_category"]) : "OFF", "update_price" => check_string($_POST["update_price"]), "roundMoney" => check_string($_POST["roundMoney"]), "status" => 1, "check_string_api" => check_string($_POST["check_string_api"]), "create_gettime" => gettime(), "update_gettime" => gettime()]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add API Supplier (" . check_string($_POST["domain"]) . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Add API Supplier (" . check_string($_POST["domain"]) . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công !\")){location.href = \"" . base_url_admin("product-api") . "\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại !\")){window.history.back().location.reload();}</script>");
}
$domain = "";
if(!empty($_GET["domain"])) {
    $domain = check_string($_GET["domain"]);
}
$type = "";
if(!empty($_GET["type"])) {
    $type = check_string($_GET["type"]);
}
echo "\n\n\n\n<div class=\"main-content app-content\">\n\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><a type=\"button\"\n                    class=\"btn btn-dark btn-raised-shadow btn-wave btn-sm me-1\"\n                    href=\"";
echo base_url_admin("product-api");
echo "\"><i class=\"fa-solid fa-arrow-left\"></i></a> Thêm API cần\n                kết nối\n            </h1>\n        </div>\n\n\n        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n            <div class=\"row\">\n                <div class=\"col-xl-7\">\n                    <div class=\"card custom-card\">\n                        <div class=\"card-header justify-content-between\">\n                            <div class=\"card-title\">\n                                NHẬP THÔNG TIN API CẦN KẾT NỐI\n                            </div>\n                        </div>\n                        <div class=\"card-body\">\n                            <div class=\"row mb-5 gy-2\">\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Loại API:");
echo " <span\n                                            class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" id=\"api-select\" name=\"type\" required>\n                                        <option value=\"\">-- Chọn loại API --</option>\n                                        <option ";
echo $type == "SHOPCLONE7" ? "selected" : "";
echo " value=\"SHOPCLONE7\">\n                                            SHOPCLONE7 CMSNT (Miễn phí)</option>\n                                        <option ";
echo $type == "SHOPCLONE6" ? "selected" : "";
echo " value=\"SHOPCLONE6\">\n                                            SHOPCLONE5 & SHOPCLONE6 CMSNT (Miễn phí)</option>\n                                        <option value=\"API_1\">API (200.000đ / lần)</option>\n                                        <option value=\"API_4\">API (200.000đ / lần)</option>\n                                        <option value=\"API_6\">API (200.000đ / lần)</option>\n                                        <option value=\"API_9\">API (200.000đ / lần)</option>\n                                        <option value=\"API_14\">API (200.000đ / lần)</option>\n                                        <option value=\"API_17\">API (200.000đ / lần)</option>\n                                        <option value=\"API_18\">API (200.000đ / lần)</option>\n                                        <option value=\"API_19\">API (200.000đ / lần)</option>\n                                        <option value=\"API_20\">API (Không còn hỗ trợ)</option>\n                                        <option value=\"API_21\">API (200.000đ / lần)</option>\n                                        <option value=\"API_22\">API (200.000đ / lần)</option>\n                                        <option value=\"API_23\">API (200.000đ / lần)</option>\n                                        <option value=\"API_24\">API (200.000đ / lần)</option>\n                                    </select>\n                                </div>\n                                <script>\n                                function shuffleOptions(selectElement) {\n                                    // Lấy tất cả các option\n                                    let options = Array.from(selectElement.options);\n\n                                    // Lấy các option từ vị trí thứ 4 trở đi (index = 3)\n                                    let optionsToShuffle = options.slice(3);\n\n                                    // Random thứ tự các option này\n                                    for (let i = optionsToShuffle.length - 1; i > 0; i--) {\n                                        let j = Math.floor(Math.random() * (i + 1));\n                                        [optionsToShuffle[i], optionsToShuffle[j]] = [optionsToShuffle[j],\n                                            optionsToShuffle[i]\n                                        ];\n                                    }\n\n                                    // Xóa hết các option cũ\n                                    selectElement.innerHTML = '';\n\n                                    // Thêm lại các option không bị random\n                                    selectElement.appendChild(options[0]);\n                                    selectElement.appendChild(options[1]);\n                                    selectElement.appendChild(options[2]);\n\n                                    // Thêm lại các option đã được random\n                                    optionsToShuffle.forEach(option => selectElement.appendChild(option));\n                                }\n\n                                document.addEventListener(\"DOMContentLoaded\", function() {\n                                    const selectElement = document.getElementById(\"api-select\");\n                                    shuffleOptions(selectElement);\n                                });\n                                </script>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Domain");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" value=\"";
echo $domain;
echo "\"\n                                        placeholder=\"Link Website cần kết nối VD: https://domain.com/\" name=\"domain\"\n                                        required>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"username\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Username:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"username\"\n                                        placeholder=\"";
echo __("Nhập tên đăng nhập website API");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"password\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Password:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"password\"\n                                        placeholder=\"";
echo __("Nhập mật khẩu đăng nhập website API");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"api_key\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("API Key:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"api_key\"\n                                        placeholder=\"";
echo __("Nhập Api Key trong website API");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"token\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Token:");
echo "                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" name=\"token\"\n                                        placeholder=\"";
echo __("Nhập Token trong website API");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"coupon\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">";
echo __("Coupon:");
echo "</label>\n                                    <input type=\"text\" class=\"form-control\" name=\"coupon\"\n                                        placeholder=\"";
echo __("Nhập mã giảm giá nếu có");
echo "\">\n                                </div>\n                                <div class=\"col-sm-12 mb-2\" id=\"sync_category\" style=\"display: none;\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Đồng bộ chuyên mục từ API\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\"  name=\"sync_category\" required>\n                                        <option value=\"OFF\">OFF</option>\n                                        <option value=\"ON\">ON</option>\n                                    </select>\n                                    <small>Hệ thống sẽ tự động thêm chuyên mục giống với API vào sản phẩm.</small>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Cập nhật giá bán tự động\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" name=\"update_price\" required>\n                                        <option value=\"ON\">ON</option>\n                                        <option value=\"OFF\">OFF</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Làm tròn giá bán\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" name=\"roundMoney\" required>\n                                        <option value=\"OFF\">OFF</option>\n                                        <option value=\"ON\">ON</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Nâng giá tự động so với giá gốc\n                                        <span class=\"text-danger\">*</span></label>\n                                    <input type=\"text\" class=\"form-control\" value=\"0\"\n                                        placeholder=\"Ví dụ: nhập 10 hệ thống sẽ tăng giá bán 10% so với giá gốc, nhập 0 để chỉnh giá như giá gốc\"\n                                        name=\"discount\" required>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Cập nhật tên sản phẩm, mô tả ngắn\n                                        tự động\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" name=\"update_name\" required>\n                                        <option value=\"ON\">ON</option>\n                                        <option value=\"OFF\">OFF</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-sm-12 mb-2\">\n                                    <label class=\"form-label\" for=\"example-hf-email\">Lọc HTML trong Tên sản phẩm & mô tả\n                                        sản phẩm trong API\n                                        <span class=\"text-danger\">*</span></label>\n                                    <select class=\"form-control\" name=\"check_string_api\" required>\n                                        <option value=\"ON\">ON</option>\n                                        <option value=\"OFF\">OFF</option>\n                                    </select>\n                                    <small>Khi bật chức năng này hệ thống sẽ lọc Tên sản phẩm & mô tả sản phẩm của bên\n                                        API tránh việc bên API cố tình chèn bug vào website bạn.</small>\n                                </div>\n                            </div>\n                            <div class=\"d-grid gap-2 mb-3\">\n                                <button type=\"submit\" name=\"submit\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                                        class=\"fa fa-fw fa-plus\"></i> Kết nối ngay</button>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"col-xl-5\">\n                    <div class=\"card custom-card\">\n                        <div class=\"card-body\">\n                            <p>Chức năng này cho phép quý khách bán lại sản phẩm của website khác trên chính website của\n                                quý khách.</p>\n                            <p>Trong trường hợp quý khách cấu hình đúng nhưng không hiện số dư của API => do máy chủ của\n                                quý khách không thể cURL đến được máy chủ của họ, trường hợp này chúng tôi sẽ không giúp\n                                ngoài việc chờ đợi hoặc làm theo hướng dẫn sau <a class=\"text-primary\" target=\"_blank\" href=\"https://help.cmsnt.co/huong-dan/ket-noi-api-nhap-dung-thong-tin-nhung-khong-ra-so-du-thi-lam-sao/\">đây</a>.</p>\n                            <p>Kết nối API website cùng hệ sinh thái CMSNT sẽ miễn phí, tích hợp API website khác hệ\n                                sinh thái chúng tôi sẽ tính phí 200.000đ / 1 lần thêm kết nối (tính theo số lần thêm API\n                                chứ không phải tính theo web, ví dụ quý khách cần tôi thêm API websitecandau.com giá\n                                200.000đ, sau đó quý khách xóa websitecandau.com ra khỏi hệ thống, chúng tôi sẽ tiếp tục\n                                tính phí 200.000đ khi quý khách cần chúng tôi thêm lại).</p>\n                            <p>Liên hệ hỗ trợ kết nối API ngoài hệ thống quý khách nhấn vào <a href=\"https://www.cmsnt.co/p/contact.html\" class=\"text-primary\"\n                                    target=\"_blank\">đây</a>.</p>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </form>\n\n\n        <style>\n        .brand-carousel {\n            width: 100%;\n            overflow: hidden;\n            animation: moveCards 25s linear infinite;\n            white-space: nowrap;\n        }\n\n        .brand-carousel-container {\n            width: 100%;\n            overflow-x: auto;\n        }\n\n        .brand-carousel {\n            white-space: nowrap;\n            font-size: 0;\n            width: max-content\n                /* Đảm bảo rằng .brand-carousel có đủ rộng để chứa tất cả các .brand-card trên cùng một hàng */\n        }\n\n        .brand-card {\n            font-size: 16px;\n            display: inline-block;\n            vertical-align: top;\n            margin-right: 20px;\n        }\n\n        /* Các phần còn lại giữ nguyên */\n\n        .brand-carousel:hover {\n            animation-play-state: paused;\n        }\n\n        @keyframes moveCards {\n            0% {\n                transform: translateX(0%);\n            }\n\n            100% {\n                transform: translateX(-100%);\n            }\n        }\n\n        .brand-card {\n            position: relative;\n            display: inline-block;\n            margin: 10px;\n            vertical-align: middle;\n            background-color: #fff;\n            border-radius: 10px;\n            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);\n            padding: 20px;\n        }\n\n        .brand-card img {\n            width: 100px;\n        }\n\n        .connect-button,\n        .website-button {\n            position: absolute;\n            bottom: 10px;\n            left: 50%;\n            transform: translateX(-50%);\n            background-color: #007bff;\n            color: #fff;\n            padding: 5px 10px;\n            border: none;\n            border-radius: 5px;\n            cursor: pointer;\n            opacity: 0;\n            transition: opacity 0.3s ease;\n        }\n\n        .brand-card:hover .connect-button,\n        .brand-card:hover .website-button {\n            opacity: 1;\n        }\n\n        .website-button {\n            bottom: 40px;\n        }\n        </style>\n        <div class=\"row justify-content-center py-3\">\n            <center>\n                <h5><i class=\"fa-solid fa-boxes-packing\"></i> Nhà cung cấp gợi ý</h5>\n            </center>\n            <div class=\"brand-carousel-container\">\n                <div class=\"brand-carousel animated-carousel\">\n\n                </div>\n            </div>\n            <p id=\"notitcation_suppliers\"></p>\n        </div>\n        <script>\n        \$(document).ready(function() {\n            \$('.brand-carousel').html('');\n            \$.ajax({\n                url: 'https://api.cmsnt.co/suppliers.php',\n                type: 'GET',\n                dataType: 'json',\n                success: function(response) {\n                    // Xử lý dữ liệu trả về từ server\n                    if (response && response.suppliers.length > 0) {\n                        var html = '';\n                        \$.each(response.suppliers, function(index, brand) {\n                            html += '<div class=\"brand-card\">';\n                            html += '<img src=\"' + brand.logo + '\" alt=\"Logo\">';\n                            html +=\n                                '<a href=\"";
echo base_url_admin("product-api-add");
echo "&domain=' +\n                                brand.domain + '&type=' + brand.type +\n                                '\" class=\"connect-button btn btn-sm btn-danger\">Kết nối</a>';\n                            html += '<a href=\"' + brand.domain +\n                                '?utm_source=ads_cmsnt\" target=\"_blank\" class=\"website-button btn btn-sm btn-primary\">Xem</a>';\n                            html += '</div>';\n                        });\n                        \$('.brand-carousel').html(html);\n                        \$('#notitcation_suppliers').html(response.notication);\n                        calculateAndSetAnimationDuration();\n                    } else {\n                        \$('.brand-carousel').html('');\n                    }\n                },\n                error: function() {\n                    \$('.brand-carousel').html('');\n                }\n            });\n        });\n        // Function to calculate carousel width and set animation duration\n        function calculateAndSetAnimationDuration() {\n            var carousel = \$('.animated-carousel');\n            var carouselWidth = carousel[0].scrollWidth;\n            var cardWidth = carousel.children().first().outerWidth(true); // Including margin\n            var numberOfCards = carouselWidth / cardWidth;\n            var animationDuration = numberOfCards * 2; // Adjust this multiplier as needed\n            carousel.css('animation-duration', animationDuration + 's');\n        }\n        </script>\n\n\n    </div>\n</div>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\ndocument.addEventListener(\"DOMContentLoaded\", function() {\n    const typeSelect = document.querySelector(\"select[name='type']\");\n    const usernameField = document.getElementById(\"username\");\n    const passwordField = document.getElementById(\"password\");\n    const apiKeyField = document.getElementById(\"api_key\");\n    const tokenField = document.getElementById(\"token\");\n    const couponField = document.getElementById(\"coupon\");\n    const sync_category = document.getElementById(\"sync_category\");\n\n    function toggleFields() {\n        const selectedType = typeSelect.value;\n        usernameField.style.display = \"none\";\n        passwordField.style.display = \"none\";\n        apiKeyField.style.display = \"none\";\n        tokenField.style.display = \"none\";\n        couponField.style.display = \"none\";\n        sync_category.style.display = \"none\";\n\n        if (selectedType === \"SHOPCLONE6\") {\n            sync_category.style.display = \"block\";\n            usernameField.style.display = \"block\";\n            passwordField.style.display = \"block\";\n        } else if (selectedType === \"SHOPCLONE7\") {\n            sync_category.style.display = \"block\";\n            apiKeyField.style.display = \"block\";\n            couponField.style.display = \"block\";\n        } else if (selectedType === \"API_4\" || selectedType === \"API_17\") {\n            usernameField.style.display = \"block\";\n            passwordField.style.display = \"block\";\n        } else if (selectedType === \"API_1\" || selectedType === \"API_6\" || selectedType === \"API_18\" ||\n            selectedType === \"API_19\" || selectedType === \"API_9\" || selectedType === \"API_23\" || selectedType === \"API_24\") {\n            apiKeyField.style.display = \"block\";\n        } else if (selectedType === \"API_14\" || selectedType === \"API_21\" || selectedType === \"API_22\") {\n            tokenField.style.display = \"block\";\n        } else if (selectedType === \"API_20\") {\n            apiKeyField.style.display = \"block\";\n            tokenField.style.display = \"block\";\n        }\n    }\n    toggleFields();\n    typeSelect.addEventListener(\"change\", toggleFields);\n});\n</script>";

?>