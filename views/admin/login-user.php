<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
require_once __DIR__ . "/../../models/is_admin.php";
if(!checkPermission($getUser["admin"], "login_user")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_GET["id"])) {
    $CMSNT = new DB();
    $id = check_string($_GET["id"]);
    $user = $CMSNT->get_row("SELECT * FROM `users` WHERE `id` = '" . $id . "' ");
    if(!$user) {
        redirect(base_url_admin());
    }
    $Mobile_Detect = new Detection\MobileDetect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Login user " . $user["username"]]);
    setcookie("token", $user["token"], time() + $CMSNT->site("session_login"), "/");
    $_SESSION["login"] = $user["token"];
    redirect(base_url());
}
redirect(base_url_admin());

?>