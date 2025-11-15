<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
require_once __DIR__ . "/../../models/is_admin.php";
if($CMSNT->site("status_demo") != 0) {
    $data = json_encode(["status" => "error", "msg" => __("This function cannot be used because this is a demo site")]);
    exit($data);
}
if(!isset($_POST["action"])) {
    $data = json_encode(["status" => "error", "msg" => "The Request Not Found"]);
    exit($data);
}
exit(json_encode(["status" => "error", "msg" => __("Invalid data")]));

?>