<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Blogs") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/client/") . "css/blog-grid.css\">\n";
$body["footer"] = "\n \n\n \n";
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
if(isset($_GET["limit"])) {
    $limit = (int) check_string($_GET["limit"]);
} else {
    $limit = 10;
}
if(isset($_GET["page"])) {
    $page = check_string((int) $_GET["page"]);
} else {
    $page = 1;
}
$from = ($page - 1) * $limit;
$where = " `status` = 1 ";
$keyword = "";
$shortby = 1;
$category = "";
if(!empty($_GET["category"])) {
    $category = check_string($_GET["category"]);
    $where .= " AND `category_id` = \"" . $category . "\" ";
}
if(!empty($_GET["keyword"])) {
    $keyword = check_string($_GET["keyword"]);
    $where .= " AND `title` LIKE \"%" . $keyword . "%\" ";
}
if(!empty($_GET["time"])) {
    $time = check_string($_GET["time"]);
    $create_date_1 = str_replace("-", "/", $time);
    $create_date_1 = explode(" to ", $create_date_1);
    if($create_date_1[0] != $create_date_1[1]) {
        $create_date_1 = [$create_date_1[0] . " 00:00:00", $create_date_1[1] . " 23:59:59"];
        $where .= " AND `create_gettime` >= '" . $create_date_1[0] . "' AND `create_gettime` <= '" . $create_date_1[1] . "' ";
    }
}
if(isset($_GET["shortby"])) {
    $shortby = check_string($_GET["shortby"]);
}
if($shortby == 1) {
    $where .= " ORDER BY `id` DESC ";
}
if($shortby == 2) {
    $where .= " ORDER BY `view` DESC ";
}
$listDatatable = $CMSNT->get_list(" SELECT * FROM `posts` WHERE " . $where . " LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `posts` WHERE " . $where . " ");
$urlDatatable = pagination_client(base_url("?action=blogs&limit=" . $limit . "&keyword=" . $keyword . "&shortby=" . $shortby . "&category=" . $category . "&"), $from, $totalDatatable, $limit);
if($category != 0) {
    echo "<section class=\"inner-section single-banner\"\n    style=\"background: url(";
    echo base_url(getRowRealtime("post_category", $category, "icon"));
    echo ") no-repeat center;\">\n    <div class=\"container\">\n        <h2>";
    echo getRowRealtime("post_category", $category, "name");
    echo "</h2>\n    </div>\n</section>\n";
}
echo "<section class=\"inner-section ";
echo $category == 0 ? "py-5" : "";
echo " blog-grid\">\n    <form action=\"";
echo base_url();
echo "\" method=\"GET\">\n        <input type=\"hidden\" name=\"action\" value=\"blogs\">\n        <div class=\"container\">\n            <div class=\"row justify-content-center\">\n                <div class=\"col-lg-8\">\n                    <div class=\"row\">\n                        <div class=\"col-lg-12\">\n                            <div class=\"top-filter\">\n                                <div class=\"filter-show\"><label class=\"filter-label\">Show :</label>\n                                    <select name=\"limit\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option ";
echo $limit == 10 ? "selected" : "";
echo " value=\"10\">10</option>\n                                        <option ";
echo $limit == 20 ? "selected" : "";
echo " value=\"20\">20</option>\n                                        <option ";
echo $limit == 40 ? "selected" : "";
echo " value=\"40\">40</option>\n                                        <option ";
echo $limit == 100 ? "selected" : "";
echo " value=\"100\">100</option>\n                                        <option ";
echo $limit == 400 ? "selected" : "";
echo " value=\"400\">400</option>\n                                        <option ";
echo $limit == 1000 ? "selected" : "";
echo " value=\"1000\">1000</option>\n                                    </select>\n                                </div>\n                                <div class=\"filter-action\"><label class=\"filter-label\">Short by :</label>\n                                    <select name=\"shortby\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option ";
echo $shortby == 1 ? "selected" : "";
echo " value=\"1\">";
echo __("Mặc định");
echo "                                        </option>\n                                        <option ";
echo $shortby == 2 ? "selected" : "";
echo " value=\"2\">";
echo __("Phổ biến");
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                    <div class=\"row\">\n                        ";
foreach ($listDatatable as $row) {
    echo "                        <div class=\"col-md-6 col-lg-6\">\n                            <div class=\"blog-card\">\n                                <div class=\"blog-media\"><a class=\"blog-img\"\n                                        href=\"";
    echo base_url("blog/" . $row["slug"]);
    echo "\"><img\n                                            style=\"width: 100%; height: 300px;\" src=\"";
    echo base_url($row["image"]);
    echo "\"\n                                            alt=\"blog\"></a></div>\n                                <div class=\"blog-content\">\n                                    <ul class=\"blog-meta\">\n                                        <li><i\n                                                class=\"fas fa-user\"></i><span>";
    echo getRowRealtime("users", $row["user_id"], "fullname");
    echo "</span>\n                                        </li>\n                                        <li><i class=\"fas fa-calendar-alt\"></i><span>";
    echo $row["create_gettime"];
    echo "</span>\n                                        </li>\n                                    </ul>\n                                    <h4 class=\"blog-title\"><a\n                                            href=\"";
    echo base_url("blog/" . $row["slug"]);
    echo "\">";
    echo $row["title"];
    echo "</a></h4>\n                                    <p class=\"blog-desc\">\n                                        ";
    echo strip_tags(substr(base64_decode($row["content"]), 0, 200)) . " ...";
    echo "</p><a\n                                        class=\"blog-btn\"\n                                        href=\"";
    echo base_url("blog/" . $row["slug"]);
    echo "\"><span>";
    echo __("Xem thêm");
    echo "</span><i\n                                            class=\"icofont-arrow-right\"></i></a>\n                                </div>\n                            </div>\n                        </div>\n                        ";
}
echo "\n                    </div>\n                    <div class=\"row\">\n                        <div class=\"col-lg-12\">\n                            <div class=\"bottom-paginate\">\n                                <p class=\"page-info\">Showing ";
echo $limit;
echo " of ";
echo $totalDatatable;
echo " Results</p>\n                                <div class=\"pagination\">\n                                    ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"col-lg-4\">\n                    <div class=\"blog-widget\">\n                        <h3 class=\"blog-widget-title\">";
echo __("Tìm kiếm bài viết");
echo "</h3>\n                        <form class=\"blog-widget-form\"></form>\n                        <form class=\"blog-widget-form\" action=\"";
echo base_url();
echo "\" method=\"GET\">\n                            <input type=\"hidden\" name=\"action\" value=\"blogs\">\n                            <input type=\"text\" name=\"keyword\" value=\"";
echo $keyword;
echo "\"\n                                placeholder=\"";
echo __("Search blogs");
echo "\">\n                            <button class=\"icofont-search-1\"></button>\n                        </form>\n                    </div>\n                    <div class=\"blog-widget\">\n                        <h3 class=\"blog-widget-title\">";
echo __("Bài viết phổ biến");
echo "</h3>\n                        <ul class=\"blog-widget-feed\">\n                            ";
foreach ($CMSNT->get_list(" SELECT * FROM `posts` WHERE `status` = 1 ORDER BY `view` DESC ") as $popular) {
    echo "                            <li>\n                                <a class=\"blog-widget-media\" href=\"";
    echo base_url("blog/" . $popular["slug"]);
    echo "\"><img\n                                        style=\"height: 100%;\" src=\"";
    echo base_url($popular["image"]);
    echo "\"\n                                        alt=\"blog-widget\"></a>\n                                <h6 class=\"blog-widget-text\"><a\n                                        href=\"";
    echo base_url("blog/" . $popular["slug"]);
    echo "\">";
    echo $popular["title"];
    echo "</a><span\n                                        class=\"fw-bold text-dark\">";
    echo getRowRealtime("post_category", $popular["category_id"], "name");
    echo "</span>\n                                </h6>\n                            </li>\n                            ";
}
echo "\n                        </ul>\n                    </div>\n                    <div class=\"blog-widget\">\n                        <h3 class=\"blog-widget-title\">";
echo __("Chuyên mục");
echo "</h3>\n                        <ul class=\"blog-widget-category\">\n                            ";
foreach ($CMSNT->get_list(" SELECT * FROM `post_category` WHERE `status` = 1 ") as $category) {
    echo "                            <li><a href=\"";
    echo base_url("?action=blogs&category=" . $category["id"]);
    echo "\">";
    echo $category["name"];
    echo "                                    <span>";
    echo $CMSNT->get_row(" SELECT COUNT(id) FROM `posts` WHERE `category_id` = '" . $category["id"] . "' ")["COUNT(id)"];
    echo "</span></a>\n                            </li>\n                            ";
}
echo "                        </ul>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </form>\n</section>\n\n\n";
require_once __DIR__ . "/footer.php";

?>