<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Đăng ký tài khoản") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
echo "<style>\n    #password-strength-status {\n    margin-top: 10px;\n    border-radius: 4px;\n    font-weight: bold;\n    text-align: center;\n    font-size: 14px;\n}\n/* Màu nền và đường viền cho các mức độ mạnh của mật khẩu */\n#password-strength-status.weak {\n    color: #ff0000; /* Màu chữ đỏ cho mật khẩu yếu */\n    background-color: #f8d7da; /* Màu nền đỏ nhạt */\n    border: 1px solid #f5c6cb; /* Đường viền đỏ nhạt */\n}\n\n#password-strength-status.medium {\n    color: #ff9800; /* Màu chữ cam cho mật khẩu trung bình */\n    background-color: #fff3cd; /* Màu nền cam nhạt */\n    border: 1px solid #ffeeba; /* Đường viền cam nhạt */\n}\n\n#password-strength-status.strong {\n    color: #4caf50; /* Màu chữ xanh lá cho mật khẩu mạnh */\n    background-color: #d4edda; /* Màu nền xanh lá nhạt */\n    border: 1px solid #c3e6cb; /* Đường viền xanh lá nhạt */\n}\n\n</style>\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row justify-content-center\">\n            <div class=\"col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6\">\n                <div class=\"user-form-card\">\n                    <div class=\"user-form-title\">\n                        <h2>";
echo __("Đăng ký tài khoản");
echo "</h2>\n                        <p>";
echo __("Vui lòng nhập thông tin đăng ký");
echo "</p>\n                    </div>\n                    <div class=\"user-form-group\">\n                        <form class=\"user-form\">\n                            <div class=\"form-group\">\n                                <input type=\"hidden\" id=\"csrf_token\" value=\"";
echo generate_csrf_token();
echo "\">\n                                <input type=\"text\" class=\"form-control\" id=\"register-username\" autocomplete=\"username\"\n                                    placeholder=\"";
echo __("Tài khoản đăng nhập");
echo "\">\n                            </div>\n                            <div class=\"form-group\">\n                                <input type=\"email\" class=\"form-control\" id=\"register-email\" autocomplete=\"email\"\n                                    placeholder=\"";
echo __("Địa chỉ Email");
echo "\">\n                            </div>\n                            <div class=\"form-group\">\n                                <input type=\"password\" class=\"form-control\" id=\"register-password\" autocomplete=\"new-password\"\n                                    placeholder=\"";
echo __("Mật khẩu");
echo "\">\n                                <div id=\"password-strength-status\"></div>\n                            </div>\n                            <div class=\"form-group\">\n                                <input type=\"password\" class=\"form-control\" id=\"register-password-confirm\"\n                                    placeholder=\"";
echo __("Nhập lại mật khẩu");
echo "\">\n                            </div>\n                            <center class=\"mb-3\"\n                                ";
echo $CMSNT->site("reCAPTCHA_status") == 1 ? "" : "style=\"display:none;\"";
echo ">\n                                <div class=\"g-recaptcha\" id=\"g-recaptcha-response\"\n                                    data-sitekey=\"";
echo $CMSNT->site("reCAPTCHA_site_key");
echo "\"></div>\n                            </center>\n                            <div class=\"form-button\">\n                                <button type=\"button\" id=\"btnRegister\">";
echo __("Đăng Ký");
echo "</button>\n                                <p>";
echo __("Bạn đã có tài khoản?");
echo " <a href=\"";
echo base_url("client/login");
echo "\">";
echo __("Đăng Nhập");
echo "</a></p>\n                            </div>\n                        </form>\n                    </div>\n                </div>\n                <div class=\"user-form-remind\">\n                    <p>";
echo __("Bạn chưa có tài khoản?");
echo " <a href=\"";
echo base_url("client/register");
echo "\">";
echo __("Đăng Ký Ngay");
echo "</a></p>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "<script>\ndocument.getElementById('register-password').addEventListener('input', function() {\n    var password = this.value;\n    var strengthStatus = document.getElementById('password-strength-status');\n    \n    // Kiểm tra độ mạnh mật khẩu\n    var strength = getPasswordStrength(password);\n    \n    // Hiển thị độ mạnh của mật khẩu\n    strengthStatus.textContent = strength.message;\n    strengthStatus.style.color = strength.color;\n});\n\nfunction getPasswordStrength(password) {\n    var strength = { message: \"❗ ";
echo __("Mật khẩu rất yếu");
echo "\", color: \"red\" };\n    var regexes = [\n        /[A-Z]/,      // Chữ cái viết hoa\n        /[a-z]/,      // Chữ cái viết thường\n        /[0-9]/,      // Số\n        /[\\W_]/,      // Ký tự đặc biệt\n        /.{8,}/      // Độ dài ít nhất 8 ký tự\n    ];\n    var passedChecks = regexes.reduce((acc, regex) => acc + regex.test(password), 0);\n    if (passedChecks === 5) {\n        strength.message = \"🔰 ";
echo __("Mật khẩu mạnh");
echo "\";\n        strength.color = \"green\";\n    } else if (passedChecks >= 3) {\n        strength.message = \"⚠️ ";
echo __("Mật khẩu trung bình");
echo "\";\n        strength.color = \"orange\";\n    }\n\n    return strength;\n}\n\n</script>\n<script type=\"text/javascript\">\n\$(\"#btnRegister\").on(\"click\", function() {\n    \$('#btnRegister').html('<i class=\"fa fa-spinner fa-spin\"></i> ";
echo __("Đang xử lý...");
echo "').prop('disabled',\n        true);\n    \$.ajax({\n        url: \"";
echo base_url("ajaxs/client/auth.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'Register',\n            csrf_token: \$(\"#csrf_token\").val(),\n            recaptcha: \$(\"#g-recaptcha-response\").val(),\n            username: \$(\"#register-username\").val(),\n            email: \$(\"#register-email\").val(),\n            password: \$(\"#register-password\").val(),\n            repassword: \$(\"#register-password-confirm\").val()\n        },\n        success: function(respone) {\n            if (respone.status == 'success') {\n                Swal.fire({\n                    title: '";
echo __("Successful!");
echo "',\n                    text: respone.msg,\n                    icon: 'success',\n                    confirmButtonColor: '#3085d6',\n                    confirmButtonText: 'OK'\n                }).then((result) => {\n                    if (result.isConfirmed) {\n\n                    }\n                });\n                setTimeout(\"location.href = '";
echo BASE_URL("");
echo "';\", 1000);\n            } else {\n                Swal.fire('";
echo __("Failure!");
echo "', respone.msg, 'error');\n            }\n            \$('#btnRegister').html('";
echo __("Đăng Ký");
echo "').prop('disabled', false);\n        },\n        error: function() {\n            showMessage('";
echo __("Không thể xử lý");
echo "', 'error');\n            \$('#btnRegister').html('";
echo __("Đăng Ký");
echo "').prop('disabled', false);\n        }\n\n    });\n});\n</script>";

?>