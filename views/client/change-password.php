<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Profile") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/profile.css\">\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_user.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
echo "\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n    <div class=\"row content-reverse\">\n            <div class=\"col-lg-3\">\n                ";
require_once __DIR__ . "/sidebar.php";
echo "            </div>\n            <div class=\"col-lg-9\">\n                <div class=\"account-card\">\n                    <div class=\"account-title\">\n                        <h4>";
echo __("Thay đổi mật khẩu");
echo "</h4>\n                    </div>\n                    <div class=\"account-content\">\n                        <p class=\"mb-3 text-muted\">\n                            ";
echo __("Thay đổi mật khẩu đăng nhập của bạn là một cách dễ dàng để giữ an toàn cho tài khoản của bạn.");
echo "                        </p>\n                        <div class=\"row\">\n                            <div class=\"col-md-6 col-lg-4\">\n                                <div class=\"form-group\">\n                                    <label class=\"form-label\">";
echo __("Mật khẩu hiện tại");
echo "</label>\n                                    <input type=\"hidden\" class=\"form-control\" id=\"token\" value=\"";
echo $getUser["token"];
echo "\">\n                                    <input type=\"password\" class=\"form-control\" id=\"dm-profile-edit-password\"\n                                        name=\"dm-profile-edit-password\">\n                                </div>\n                            </div>\n                            <div class=\"col-md-6 col-lg-4\">\n                                <div class=\"form-group\">\n                                    <label class=\"form-label\">";
echo __("Mật khẩu mới");
echo "</label>\n                                    <input type=\"password\" class=\"form-control\" id=\"dm-profile-edit-password-new\"\n                                        name=\"dm-profile-edit-password-new\">\n                                </div>\n                            </div>\n                            <div class=\"col-md-6 col-lg-4\">\n                                <div class=\"form-group\"><label\n                                        class=\"form-label\">";
echo __("Nhập lại mật khẩu mới");
echo "</label>\n                                    <input type=\"password\" class=\"form-control\"\n                                        id=\"dm-profile-edit-password-new-confirm\"\n                                        name=\"dm-profile-edit-password-new-confirm\">\n                                </div>\n                            </div>\n                            <center>\n                                <button class=\"form-btn\" id=\"btnChangePasswordProfile\"\n                                    type=\"button\">";
echo __("Cập Nhật");
echo "</button>\n                            </center>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n \n\n<script type=\"text/javascript\">\n\$(\"#btnChangePasswordProfile\").on(\"click\", function() {\n    \$('#btnChangePasswordProfile').html('<i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Processing...");
echo "')\n        .prop('disabled',\n            true);\n    \$.ajax({\n        url: \"";
echo base_url("ajaxs/client/auth.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'ChangePasswordProfile',\n            token: \$(\"#token\").val(),\n            password: \$(\"#dm-profile-edit-password\").val(),\n            newpassword: \$(\"#dm-profile-edit-password-new\").val(),\n            renewpassword: \$(\"#dm-profile-edit-password-new-confirm\").val()\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                Swal.fire('";
echo __("Successful!");
echo "', result.msg, 'success');\n                setTimeout(\"location.href = '";
echo BASE_URL("client/login");
echo "';\", 1000);\n            } else {\n                Swal.fire('";
echo __("Failure!");
echo "', result.msg, 'error');\n            }\n            \$('#btnChangePasswordProfile').html(\n                '";
echo __("Cập Nhật");
echo "'\n            ).prop('disabled',\n                false);\n        },\n        error: function() {\n            showMessage('Không thể xử lý', 'error');\n            \$('#btnChangePasswordProfile').html(\n                '";
echo __("Cập Nhật");
echo "'\n            ).prop('disabled',\n                false);\n        }\n\n    });\n});\n\n \n</script>\n\n\n";
require_once __DIR__ . "/footer.php";

?>