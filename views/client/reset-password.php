<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Thay đổi mật khẩu") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n\n";
$body["footer"] = "\n\n";
if(empty($_GET["token"])) {
    redirect(base_url());
}
if(!($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `token_forgot_password` = '" . check_string($_GET["token"]) . "' AND `token_forgot_password` IS NOT NULL "))) {
    if(empty($getUser["token_forgot_password"])) {
        redirect(base_url());
    }
    redirect(base_url());
}
require_once __DIR__ . "/header.php";
echo "\n<body>\n    <section class=\"user-form-part\">\n        <div class=\"container\">\n            <div class=\"row justify-content-center\">\n                <div class=\"col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5\">\n                    <div class=\"user-form-logo\"><a href=\"";
echo base_url();
echo "\"><img\n                                src=\"";
echo BASE_URL($CMSNT->site("logo_light"));
echo "\" alt=\"logo\"></a></div>\n                    <div class=\"user-form-card\">\n                        <div class=\"user-form-title\">\n                            <h2>";
echo __("Thay đổi mật khẩu");
echo "</h2>\n                        </div>\n                        <form class=\"user-form\">\n                        <input type=\"hidden\" id=\"csrf_token\" value=\"";
echo generate_csrf_token();
echo "\">\n                         <input type=\"hidden\" id=\"ChangePassword_token\" value=\"";
echo $getUser["token_forgot_password"];
echo "\">\n                            <div class=\"form-group\">\n                                <input type=\"password\" id=\"ChangePassword_password\" class=\"form-control\"\n                                    placeholder=\"";
echo __("Vui lòng nhập mật khẩu mới");
echo "\">\n                            </div>\n                            <div class=\"form-group\">\n                                <input type=\"password\" id=\"ChangePassword_repassword\" class=\"form-control\"\n                                    placeholder=\"";
echo __("Nhập lại mật khẩu mới");
echo "\">\n                            </div>\n                            <div class=\"form-button\"><button type=\"button\"\n                                    id=\"btnChangePassword\">";
echo __("Thay đổi mật khẩu");
echo "</button></div>\n                        </form>\n                    </div>\n                    <div class=\"user-form-remind\">\n                        <p>";
echo __("Bạn đã có tài khoản?");
echo " <a href=\"";
echo BASE_URL();
echo "\">";
echo __("Đăng Nhập");
echo "</a></p>\n                    </div>\n                    <div class=\"user-form-footer\">\n                        <p>&COPY; Copyright by <a href=\"";
echo BASE_URL();
echo "\">";
echo $CMSNT->site("title");
echo "</a></p>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </section>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "vendor/bootstrap/jquery-1.12.4.min.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "vendor/bootstrap/popper.min.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "vendor/bootstrap/bootstrap.min.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "vendor/countdown/countdown.min.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "vendor/niceselect/nice-select.min.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "vendor/slickslider/slick.min.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "vendor/venobox/venobox.min.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "js/nice-select.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "js/countdown.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "js/accordion.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "js/venobox.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "js/slick.js\"></script>\n    <script src=\"";
echo BASE_URL("public/client/");
echo "js/main.js\"></script>\n</body>\n\n</html>\n\n \n\n<script type=\"text/javascript\">\n\$(\"#btnChangePassword\").on(\"click\", function() {\n    \$('#btnChangePassword').html('<i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "').prop(\n        'disabled',\n        true);\n    \n    \$.ajax({\n        url: \"";
echo base_url("ajaxs/client/auth.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'ChangePassword',\n            csrf_token: \$(\"#csrf_token\").val(),\n            token: \$(\"#ChangePassword_token\").val(),\n            newpassword: \$(\"#ChangePassword_password\").val(),\n            renewpassword: \$(\"#ChangePassword_repassword\").val()\n        },\n        success: function(respone) {\n            if (respone.status == 'success') {\n                Swal.fire({\n                    title: '";
echo __("Successful !");
echo "',\n                    text: respone.msg,\n                    icon: 'success',\n                    confirmButtonColor: '#3085d6',\n                    confirmButtonText: '";
echo __("Sign In");
echo "'\n                }).then((result) => {\n                    if (result.isConfirmed) {\n                        location.href = '";
echo BASE_URL();
echo "';\n                    }\n                });\n                location.href = '";
echo BASE_URL();
echo "';\n            } else {\n                Swal.fire('";
echo __("Failure!");
echo "', respone.msg, 'error');\n            }\n            \$('#btnChangePassword').html(\n                '";
echo __("Thay đổi mật khẩu");
echo "'\n                ).prop('disabled', false);\n        },\n        error: function() {\n            showMessage('";
echo __("Không thể xử lý");
echo "', 'error');\n            \$('#btnChangePassword').html(\n                '";
echo __("Thay đổi mật khẩu");
echo "'\n                ).prop('disabled', false);\n        }\n\n    });\n});\n</script>";

?>