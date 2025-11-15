<?php 

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
require_once __DIR__ . "/../libs/database/users.php";
$CMSNT = new DB();
$user = new users();

$bank = $CMSNT->get_row(" SELECT * FROM `banks` WHERE `status` = 1 ");

$result = getAcbTransaction($bank);

echo json_encode($result);