<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Icon Facebook") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n\n";
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
if($CMSNT->site("status_menu_tools") != 1) {
    redirect(base_url("client/home"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
echo "\n\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-12\">\n                <div class=\"posterd home mb-3\" style=\"background-image: url(";
echo base_url("mod/img/bg-intro.png");
echo ")\">\n                    <div class=\"welcomto\">\n                        <div class=\"box-intro\">\n                            <img src=\"";
echo base_url("mod/img/icon-facebook.png");
echo "\" alt=\"Accnice\" width=\"70\" height=\"70\">\n                        </div>\n                        <div class=\"\">\n                            <div\n                                style=\"font-size: 15px; text-shadow: rgba(0, 0, 0, 0.25) 0px 3px 5px;font-family: Robot,Roboto,sans-serif;\">\n                                ";
echo __("Bạn đang xem");
echo "</div>\n                            <h1\n                                style=\"color: #fff; font-size: 25px; font-weight:500; margin-top: 10px; text-shadow: rgba(0, 0, 0, 0.25) 0px 3px 5px;font-family: Robot,Roboto,sans-serif;\">\n                                ";
echo __("Tool Icon Facebook");
echo "</h1>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            ";
require_once __DIR__ . "/widget_tools.php";
echo "            <div class=\"mb-5\"></div>\n\n\n            <div class=\"col-md-12\">\n                <div class=\"account-card pt-3\">\n                    <iframe src=\"https://www.smileysapp.com/emojiPicker/\" class=\"border border-dark\" width=\"100%\"\n                        style=\"height:calc(100vh - 150px)\"></iframe>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n\n";
require_once __DIR__ . "/footer.php";

?>