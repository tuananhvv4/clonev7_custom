<?php

define("IN_SITE", true);
require_once __DIR__ . "/libs/db.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/libs/helper.php";
$CMSNT = new DB();
$whitelist = ["127.0.0.1", "::1"];
$arrContextOptions = ["ssl" => ["verify_peer" => false, "verify_peer_name" => false]];
// if(in_array($_SERVER["REMOTE_ADDR"], $whitelist)) {
//     exit("Localhost không thể sử dụng chức năng này");
// }

exit("Chức năng khóa");

?>