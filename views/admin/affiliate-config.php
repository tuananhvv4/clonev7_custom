<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Cấu hình Tiếp Thị Liên Kết") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n\n\n";
$body["footer"] = "\n<!-- ckeditor -->\n<script src=\"" . BASE_URL("public/ckeditor/ckeditor.js") . "\"></script>\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "edit_affiliate")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back().location.reload();}</script>");
}
if(isset($_POST["SaveSettings"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Crypto Deposit Configuration"]);
    foreach ($_POST as $key => $value) {
        $CMSNT->update("settings", ["value" => $value], " `name` = '" . $key . "' ");
    }
    $my_text = $CMSNT->site("noti_action");
    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
    $my_text = str_replace("{username}", $getUser["username"], $my_text);
    $my_text = str_replace("{action}", __("Cấu hình Affiliate Program"), $my_text);
    $my_text = str_replace("{ip}", myip(), $my_text);
    $my_text = str_replace("{time}", gettime(), $my_text);
    sendMessAdmin($my_text);
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Save successfully!") . "\")){window.history.back().location.reload();}</script>");
} else {
    echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Cấu hình Affiliate</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"#\">Affiliate Program</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Cấu hình Affiliate</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n\n                <div class=\"alert alert-warning alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n                    <svg class=\"svg-warning\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                        width=\"1.5rem\" fill=\"#000000\">\n                        <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                        <path d=\"M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z\" />\n                    </svg>\n                    Từ phiên bản <strong>2.6.1</strong> trở đi, hoa hồng sẽ được tính khi khách hàng mua hàng thay vì\n                    khi nạp tiền.\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                            class=\"bi bi-x\"></i></button>\n                </div>\n                <div class=\"alert alert-primary alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n                    <svg class=\"svg-primary\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                        width=\"1.5rem\" fill=\"#000000\">\n                        <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                        <path\n                            d=\"M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z\" />\n                    </svg>\n                    Liên kết AFF sẽ lưu Cookie trong 30 ngày.\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                            class=\"bi bi-x\"></i></button>\n                </div>\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CẤU HÌNH AFFILIATE\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row\">\n                                <div class=\"col-lg-12 col-xl-6\">\n                                    <div class=\"row mb-3\">\n                                        <label class=\"col-sm-6 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Trạng thái");
    echo "</label>\n                                        <div class=\"col-sm-6\">\n                                            <select class=\"form-control\" name=\"affiliate_status\" required>\n                                                <option ";
    echo $CMSNT->site("affiliate_status") == 1 ? "selected" : "";
    echo "                                                    value=\"1\">\n                                                    ";
    echo __("ON");
    echo "</option>\n                                                <option ";
    echo $CMSNT->site("affiliate_status") == 0 ? "selected" : "";
    echo "                                                    value=\"0\">\n                                                    ";
    echo __("OFF");
    echo "</option>\n                                            </select>\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-lg-12 col-xl-6\">\n                                    <div class=\"row mb-3\">\n                                        <label class=\"col-sm-6 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Hoa hồng nạp tiền");
    echo "</label>\n                                        <div class=\"col-sm-6\">\n                                            <div class=\"input-group\">\n                                                <input type=\"text\" class=\"form-control\"\n                                                    value=\"";
    echo $CMSNT->site("affiliate_ck");
    echo "\" name=\"affiliate_ck\"\n                                                    placeholder=\"VD 10 = 10%\">\n                                                <span class=\"input-group-text\">\n                                                    %\n                                                </span>\n                                            </div>\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-lg-12 col-xl-6\">\n                                    <div class=\"row mb-3\">\n                                        <label class=\"col-sm-6 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Số tiền rút tối thiểu");
    echo "</label>\n                                        <div class=\"col-sm-6\">\n                                            <div class=\"input-group\">\n                                                <input type=\"text\" class=\"form-control\"\n                                                    value=\"";
    echo $CMSNT->site("affiliate_min");
    echo "\" name=\"affiliate_min\"\n                                                    placeholder=\"VD 100000 = 100.000đ\">\n                                                <span class=\"input-group-text\">\n                                                    ";
    echo __("VNĐ");
    echo "                                                </span>\n                                            </div>\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-lg-12 col-xl-6\">\n                                    <div class=\"row mb-3\">\n                                        <label class=\"col-sm-6 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Phương thức rút tiền");
    echo "</label>\n                                        <div class=\"col-sm-6\">\n                                            <div class=\"input-group\">\n                                                <textarea class=\"form-control\" rows=\"4\"\n                                                    placeholder=\"Mỗi dòng 1 ngân hàng\"\n                                                    name=\"affiliate_banks\">";
    echo $CMSNT->site("affiliate_banks");
    echo "</textarea>\n                                            </div>\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-lg-12 col-xl-12\">\n                                    <div class=\"row mb-3\">\n                                        <label class=\"col-sm-6 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Chat ID Telegram nhận thông báo rút tiền");
    echo "</label>\n                                        <div class=\"col-sm-6\">\n                                            <div class=\"input-group\">\n                                                <input type=\"text\" class=\"form-control\"\n                                                    value=\"";
    echo $CMSNT->site("affiliate_chat_id_telegram");
    echo "\"\n                                                    name=\"affiliate_chat_id_telegram\" placeholder=\"\">\n                                                <span class=\"input-group-text\">\n                                                    ";
    echo __("BOT Telegram");
    echo "                                                </span>\n                                            </div>\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-lg-12 col-xl-12\">\n                                    <div class=\"row mb-3\">\n                                        <label class=\"col-sm-6 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Lưu ý");
    echo "</label>\n                                        <div class=\"col-sm-12\">\n                                            <textarea id=\"affiliate_note\"\n                                                name=\"affiliate_note\">";
    echo $CMSNT->site("affiliate_note");
    echo "</textarea>\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-lg-12 col-xl-6\">\n\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger\" href=\"\"><i class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
    echo __("Reload");
    echo "</a>\n                            <button type=\"submit\" name=\"SaveSettings\" class=\"btn btn-primary\">\n                                <i class=\"fa fa-fw fa-save me-1\"></i> ";
    echo __("Lưu Ngay");
    echo "                            </button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n\n";
    require_once __DIR__ . "/footer.php";
    echo "\n<script>\nCKEDITOR.replace(\"affiliate_note\");\n</script>";
}

?>