<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
if(isset($_GET["slug"])) {
    if(!($row = $CMSNT->get_row(" SELECT * FROM `posts` WHERE `slug` = '" . check_string($_GET["slug"]) . "' "))) {
        redirect(base_url("blogs"));
    }
    $CMSNT->cong("posts", "view", 1, " `id` =  '" . $row["id"] . "' ");
} else {
    redirect(base_url("blogs"));
}
$body = ["title" => __($row["title"]) . " | " . $CMSNT->site("title"), "desc" => check_string(substr(base64_decode($row["content"]), 0, 300)) . " ...", "keyword" => $CMSNT->site("keywords"), "image" => $row["image"]];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/blog-grid.css\">\n";
$body["footer"] = "\n \n";
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
echo "<section class=\"inner-section single-banner\" style=\"background: url(";
echo base_url($row["image"]);
echo ") no-repeat center;\">\n    <div class=\"container\">\n        <h2>";
echo $row["title"];
echo "</h2>\n    </div>\n</section>\n \n<section class=\"inner-section blog-grid\">\n    <div class=\"container\">\n        <div class=\"row justify-content-center\">\n            <div class=\"col-lg-8\">\n                <div class=\"blog-widget\">\n                    ";
echo base64_decode($row["content"]);
echo "                </div>\n            </div>\n            <div class=\"col-lg-4\">\n                <div class=\"blog-widget\">\n                    <h3 class=\"blog-widget-title\">";
echo __("Bài viết phổ biến");
echo "</h3>\n                    <ul class=\"blog-widget-feed\">\n                        ";
foreach ($CMSNT->get_list(" SELECT * FROM `posts` WHERE `status` = 1 ORDER BY `view` DESC ") as $popular) {
    echo "                        <li>\n                            <a class=\"blog-widget-media\" href=\"";
    echo base_url("blog/" . $popular["slug"]);
    echo "\">\n                                <img style=\"height: 100%;\" src=\"";
    echo base_url($popular["image"]);
    echo "\" alt=\"blog-widget\">\n                            </a>\n                            <h6 class=\"blog-widget-text\"><a\n                                    href=\"";
    echo base_url("blog/" . $popular["slug"]);
    echo "\">";
    echo $popular["title"];
    echo "</a><span\n                                    class=\"fw-bold text-dark\">";
    echo getRowRealtime("post_category", $popular["category_id"], "name");
    echo "</span>\n                            </h6>\n                        </li>\n                        ";
}
echo "                    </ul>\n                </div>\n                <div class=\"blog-widget\">\n                    <h3 class=\"blog-widget-title\">";
echo __("Chuyên mục");
echo "</h3>\n                    <ul class=\"blog-widget-category\">\n                        ";
foreach ($CMSNT->get_list(" SELECT * FROM `post_category` WHERE `status` = 1 ") as $category) {
    echo "                        <li><a href=\"";
    echo base_url("?action=blogs&category=" . $category["id"]);
    echo "\">";
    echo $category["name"];
    echo "                                <span>";
    echo $CMSNT->get_row(" SELECT COUNT(id) FROM `posts` WHERE `category_id` = '" . $category["id"] . "' ")["COUNT(id)"];
    echo "</span></a>\n                        </li>\n                        ";
}
echo "                    </ul>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n";
require_once __DIR__ . "/footer.php";

?>