<?php
define("IN_SITE", true);
require_once __DIR__ . "/libs/db.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/libs/lang.php";
require_once __DIR__ . "/libs/helper.php";
require_once __DIR__ . "/libs/database/users.php";
$CMSNT = new DB();
if($CMSNT->site("status") != 1 && !isset($_SESSION["admin_login"])) {
    if(isset($_COOKIE["token"])) {
        if($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `token` = '" . check_string($_COOKIE["token"]) . "' AND `admin` != 0 ")) {
            $_SESSION["admin_login"] = $getUser["token"];
        } else {
            require_once __DIR__ . "/views/common/maintenance.php";
            exit;
        }
    } else {
        require_once __DIR__ . "/views/common/maintenance.php";
        exit;
    }
}
if(isset($_GET["utm_source"])) {
    $utm_source = check_string($_GET["utm_source"]);
    setcookie("utm_source", $utm_source, time() + 2592000, "/");
}
if(isset($_GET["aff"])) {
    $aff = check_string($_GET["aff"]);
    setcookie("aff", $aff, time() + 2592000, "/");
    if($user_ref = $CMSNT->get_row("SELECT * FROM `users` WHERE `id` = '" . $aff . "' ")) {
        $CMSNT->cong("users", "ref_click", 1, " `id` = '" . $user_ref["id"] . "' ");
    }
}
$module = !empty($_GET["module"]) ? check_path($_GET["module"]) : "client";
$home = $module == "client" ? $CMSNT->site("home_page") : "home";
$action = !empty($_GET["action"]) ? check_path($_GET["action"]) : $home;
if($action == "footer" || $action == "header" || $action == "sidebar" || $action == "nav" || $action == "widget-tools") {
    require_once __DIR__ . "/views/common/404.php";
    exit;
}
$path = "views/" . $module . "/" . $action . ".php";
if(file_exists($path)) {
    require_once __DIR__ . "/" . $path;
    exit;
}
require_once __DIR__ . "/views/common/404.php";
exit;

?>