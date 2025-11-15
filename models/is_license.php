<?php

echo "@ -1,269 +1 @@\n";
if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$CMSNT = new DB();
if($CMSNT->site("license_key") == "" || !checkLicenseKey($CMSNT->site("license_key"))["status"]) {
    if(isset($_POST["btnSaveLicense"])) {
        if($CMSNT->site("status_demo") != 0) {
            exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
        }
        foreach ($_POST as $key => $value) {
            $CMSNT->update("settings", ["value" => $value], " `name` = '" . $key . "' ");
        }
        $checkKey = checkLicenseKey($CMSNT->site("license_key"));
        if(!$checkKey["status"]) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $checkKey["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công !\")){window.history.back().location.reload();}</script>");
    } else {
        echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">License</h1>\n            <div class=\"ms-md-1 ms-0\">\n\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-md-6\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <h3 class=\"card-title\">THÔNG TIN BẢN QUYỀN CODE</h3>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\">\n                            <div class=\"form-group row mb-3\">\n                                <label class=\"col-sm-4 col-form-label\">Mã bản quyền (license key)</label>\n                                <div class=\"col-sm-8\">\n                                    <div class=\"form-line\">\n                                        <input type=\"text\" name=\"license_key\"\n                                            placeholder=\"Nhập mã bản quyền của bạn để sử dụng chức năng này\"\n                                            value=\"";
        echo $CMSNT->site("license_key");
        echo "\" class=\"form-control\" required>\n                                    </div>\n                                </div>\n                            </div>\n                            <center>\n                                <button type=\"submit\" name=\"btnSaveLicense\" class=\"btn btn-primary btn-block\">\n                                    <span>Save</span></button>\n                            </center>\n                        </form>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-md-6\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <h3 class=\"card-title\">HƯỚNG DẪN</h3>\n                    </div>\n                    <div class=\"card-body\">\n                        <p>Quý khách có thể lấy License key tại đây: <a target=\"_blank\"\n                                href=\"https://client.cmsnt.co/clientarea.php?action=products&module=licensing\">https://client.cmsnt.co/clientarea.php?action=products&module=licensing</a>\n                        </p>\n                        <p>Chỉ áp dúng cho những ai mua chính hãng, không hỗ trợ những trường hợp mua lại hay sử dụng mã nguồn\n                            lậu.</p>\n                        <p>Nếu bạn chưa mua code tại CMSNT.CO, bạn có thể mua giấy phép tại đây: <a target=\"_blank\"\n                                href=\"https://www.cmsnt.co/\">CLIENT\n                                CMSNT</a></p>\n                                <p>Việc mua chính hãng sẽ giúp website bạn uy tín hơn trong mắt khách hàng và đối tác.</p>\n                        <img src=\"https://i.imgur.com/VzDVIx0.png\" width=\"100%\">\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n";
        require_once __DIR__ . "/../views/admin/footer.php";
        exit;
    }
} else {
    echo " ";
}

?>