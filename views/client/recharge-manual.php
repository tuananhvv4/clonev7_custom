<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
if(isset($_GET["slug"])) {
    $slug = check_string($_GET["slug"]);
    if(!($payment_manual1 = $CMSNT->get_row("SELECT * FROM `payment_manual` WHERE `slug` = '" . $slug . "' AND `display` = 1 "))) {
        redirect(base_url());
    }
} else {
    redirect(base_url());
}
$body = ["title" => __($payment_manual1["title"]) . " | " . $CMSNT->site("title"), "desc" => $payment_manual1["description"], "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/wallet.css\">\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_user.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
echo "\n\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-lg-12\">\n                <div class=\"account-card p-3\">\n                    ";
$content = base64_decode($payment_manual1["content"]);
$content = str_replace("{fanpage}", $CMSNT->site("fanpage"), $content);
$content = str_replace("{email}", $CMSNT->site("email"), $content);
$content = str_replace("{hotline}", $CMSNT->site("hotline"), $content);
$content = str_replace("{id}", $getUser["id"], $content);
$content = str_replace("{username}", $getUser["username"], $content);
echo "                    ";
echo $content;
echo "                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script type=\"text/javascript\">\nnew ClipboardJS(\".copy\");\n\nfunction copy() {\n    showMessage(\"";
echo __("Đã sao chép vào bộ nhớ tạm");
echo "\", 'success');\n}\n</script>\n \n ";

?>