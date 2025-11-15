<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
echo "\n<a class=\"sidebar_profile ";
echo active_sidebar_client(["profile"]);
echo "\" href=\"";
echo base_url("client/profile");
echo "\">\n    <h6><i class=\"fas fa-user\"></i> <span>";
echo __("Thông tin cá nhân");
echo "</span></h6>\n</a>\n<a class=\"sidebar_profile ";
echo active_sidebar_client(["security"]);
echo "\" href=\"";
echo base_url("client/security");
echo "\">\n    <h6><i class=\"fa-solid fa-shield-halved\"></i> <span>";
echo __("Bảo mật");
echo "</span></h6>\n</a>\n<a class=\"sidebar_profile ";
echo active_sidebar_client(["logs"]);
echo "\" href=\"";
echo base_url("?action=logs");
echo "\">\n    <h6><i class=\"fa fa-history\" aria-hidden=\"true\"></i> <span>";
echo __("Nhật ký hoạt động");
echo "</span></h6>\n</a>\n<a class=\"sidebar_profile ";
echo active_sidebar_client(["transactions"]);
echo "\" href=\"";
echo base_url("?action=transactions");
echo "\">\n    <h6><i class=\"fa-solid fa-wallet\"></i> <span>";
echo __("Biến động số dư");
echo "</span></h6>\n</a>\n<a class=\"sidebar_profile ";
echo active_sidebar_client(["change-password"]);
echo "\" href=\"";
echo base_url("client/change-password");
echo "\">\n    <h6><i class=\"fa fa-key\" aria-hidden=\"true\"></i> <span>";
echo __("Thay đổi mật khẩu");
echo "</span></h6>\n</a>\n<a class=\"sidebar_profile\" onclick=\"logout()\" href=\"javascript:void(0)\">\n    <h6><i class=\"fa-solid fa-right-from-bracket\"></i> <span>";
echo __("Đăng xuất");
echo "</span></h6>\n</a>\n\n<script type=\"text/javascript\">\nfunction logout() {\n    Swal.fire({\n        title: '";
echo __("Bạn có chắc không?");
echo "',\n        text: \"";
echo __("Bạn sẽ bị đăng xuất khỏi tài khoản khi nhấn Đồng Ý");
echo "\",\n        icon: 'warning',\n        showCancelButton: true,\n        confirmButtonColor: '#3085d6',\n        cancelButtonColor: '#d33',\n        confirmButtonText: '";
echo __("Đồng ý");
echo "',\n        cancelButtonText: '";
echo __("Huỷ bỏ");
echo "'\n    }).then((result) => {\n        if (result.isConfirmed) {\n            window.location = \"";
echo base_url("client/logout");
echo "\";\n        }\n    })\n}\n</script>";

?>