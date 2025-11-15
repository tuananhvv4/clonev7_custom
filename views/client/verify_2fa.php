<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Xác minh 2FA") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n";
$body["footer"] = "\n\n";
if(isset($_GET["token"])) {
    $token = check_string($_GET["token"]);
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token_2fa` = '" . $token . "' AND `token_2fa` IS NOT NULL "))) {
        redirect(base_url("client/login"));
    }
    if(empty($getUser["token_2fa"])) {
        redirect(base_url("client/login"));
    }
} else {
    redirect(base_url("client/login"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
echo "\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row justify-content-center\">\n            <div class=\"col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6\">\n\n                <div class=\"user-form-card\">\n                    <div class=\"user-form-title\">\n                        <h2>";
echo __("Xác Minh 2FA");
echo "</h2>\n                        <p>";
echo __("Nhập mã xác minh mà bạn dùng để bật 2FA vào ô dưới đây để xác minh đăng nhập hợp lệ");
echo "</p>\n                    </div>\n                    <div class=\"user-form-group\">\n                        \n                        <form class=\"user-form\">\n                            <div class=\"form-group\">\n                                <input type=\"hidden\" id=\"token_2fa\" value=\"";
echo $getUser["token_2fa"];
echo "\">\n                                <input type=\"text\" id=\"code\" class=\"form-control\"\n                                    placeholder=\"";
echo __("Vui lòng nhập mã xác minh");
echo "\">\n                            </div>\n                            <center class=\"mb-3\"\n                                ";
echo $CMSNT->site("reCAPTCHA_status") == 1 ? "" : "style=\"display:none;\"";
echo ">\n                                <div class=\"g-recaptcha\" id=\"g-recaptcha-response\"  \n                                    data-sitekey=\"";
echo $CMSNT->site("reCAPTCHA_site_key");
echo "\"></div>\n                            </center>\n                            <div class=\"form-button\">\n                            <button type=\"button\" id=\"btnsubmit\">";
echo __("Submit");
echo "</button>\n                            </div>\n                        </form>\n                    </div>\n                </div>\n                <div class=\"user-form-remind\">\n                <p>";
echo __("Bạn chưa có tài khoản?");
echo " <a href=\"";
echo base_url("client/register");
echo "\">";
echo __("Đăng Ký Ngay");
echo "</a></p>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script type=\"text/javascript\">\n\$(\"#btnsubmit\").on(\"click\", function() {\n    \$('#btnsubmit').html('<i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Đang xử lý...");
echo "').prop('disabled',\n        true);\n    \$.ajax({\n        url: \"";
echo base_url("ajaxs/client/auth.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'Verify2FA',\n            token_2fa: \$(\"#token_2fa\").val(),\n            code: \$(\"#code\").val(),\n            recaptcha: \$(\"#g-recaptcha-response\").val()\n        },\n        success: function(respone) {\n            if (respone.status == 'success') {\n                Swal.fire({\n                    title: '";
echo __("Successful!");
echo "',\n                    text: respone.msg,\n                    icon: 'success',\n                    confirmButtonColor: '#3085d6',\n                    confirmButtonText: 'OK'\n                }).then((result) => {\n                    if (result.isConfirmed) {\n                        location.href = '";
echo BASE_URL("");
echo "';\n                    }\n                });\n                location.href = '";
echo BASE_URL("");
echo "';\n            } else {\n                Swal.fire('";
echo __("Failure!");
echo "', respone.msg, 'error');\n            }\n            \$('#btnsubmit').html('";
echo __("Submit");
echo "').prop('disabled', false);\n        },\n        error: function() {\n            showMessage('";
echo __("Vui lòng liên hệ Developer");
echo "', 'error');\n            \$('#btnsubmit').html('";
echo __("Submit");
echo "').prop('disabled', false);\n        }\n\n    });\n});\n</script>\n ";

?>