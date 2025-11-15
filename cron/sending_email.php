<?php

define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../libs/helper.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/sendEmail.php";
$CMSNT = new DB();
if($CMSNT->site("check_time_cron_sending_email") < time() && time() - $CMSNT->site("check_time_cron_sending_email") < 3) {
    exit("Thao tác quá nhanh, vui lòng đợi");
}
$CMSNT->update("settings", ["value" => time()], " `name` = 'check_time_cron_sending_email' ");
if($CMSNT->site("smtp_status") != 1) {
    exit("Vui lòng cấu hình SMTP");
}
if($CMSNT->site("smtp_email") == "" || $CMSNT->site("smtp_password") == "") {
    exit("Vui lòng cấu hình SMTP");
}
$arrContextOptions = ["ssl" => ["verify_peer" => false, "verify_peer_name" => false]];
$checkAddon = true;
foreach ($CMSNT->get_list(" SELECT * FROM `email_campaigns` WHERE `status` = 0 ") as $camp) {
    foreach ($CMSNT->get_list(" SELECT * FROM `email_sending` WHERE `camp_id` = '" . $camp["id"] . "' AND `status` = 0 ORDER BY id ASC LIMIT 20 ") as $row) {
        $content = $camp["content"];
        $title = $camp["subject"];
        $content_email = file_get_contents(base_url("libs/mails/notification.php"), false, stream_context_create($arrContextOptions));
        $content_email = str_replace("{title}", $title, $content_email);
        $content_email = str_replace("{content}", $content, $content_email);
        $email = getRowRealtime("users", $row["user_id"], "email");
        $response = "Vui lòng kích hoạt Addon này";
        $status = 2;
        if($email == "") {
            $response = "Không tìm thấy Email người nhận";
            $status = 2;
        } else {
            $response = sendCSM($email, $camp["cc"], $title, $content_email, $camp["bcc"]);
            $status = 1;
        }
        $CMSNT->update("email_sending", ["status" => $status, "update_gettime" => gettime(), "response" => $response], " `id` = '" . $row["id"] . "' ");
    }
    if(!$CMSNT->get_row(" SELECT * FROM `email_sending` WHERE `camp_id` = '" . $camp["id"] . "' AND `status` = 0  ")) {
        $CMSNT->update("email_campaigns", ["status" => 1, "update_gettime" => gettime()], " `id` = '" . $camp["id"] . "' ");
    }
}

?>