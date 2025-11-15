<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("FAQ") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/contact.css\">\n";
$body["footer"] = "\n\n";
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
echo "<section class=\"inner-section single-banner\"\n    style=\"background: url('";
echo base_url($CMSNT->site("banner_singer"));
echo "') no-repeat center;\">\n    <div class=\"container\">\n        <h2>";
echo __("FAQ");
echo "</h2>\n        <ol class=\"breadcrumb\">\n            <li class=\"breadcrumb-item\"><a href=\"";
echo base_url();
echo "\">";
echo __("Trang chủ");
echo "</a></li>\n            <li class=\"breadcrumb-item active\" aria-current=\"page\">";
echo __("FAQ");
echo "</li>\n        </ol>\n    </div>\n</section>\n<section class=\"inner-section contact-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-lg-12\">\n                <div class=\"account-card pt-4\">\n                    ";
echo $CMSNT->site("page_faq");
echo "                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n";
require_once __DIR__ . "/footer.php";

?>