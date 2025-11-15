<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Bank Edit"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n \n\n \n";
$body["footer"] = "\n \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $CMSNT = new DB();
    $id = check_string($_GET["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `payment_manual` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        redirect(base_url_admin("recharge-payment"));
    }
} else {
    redirect(base_url_admin("recharge-payment"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_recharge")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["save"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    if(check_img("icon")) {
        unlink($row["icon"]);
        $rand = random("0123456789QWERTYUIOPASDGHJKLZXCVBNM", 3);
        $uploads_dir = "assets/storage/images/icon_gateway" . $rand . ".png";
        $tmp_name = $_FILES["icon"]["tmp_name"];
        $addlogo = move_uploaded_file($tmp_name, $uploads_dir);
        if($addlogo) {
            $CMSNT->update("payment_manual", ["icon" => "assets/storage/images/icon_gateway" . $rand . ".png"], " `id` = '" . $id . "' ");
        }
    }
    $isUpdate = $CMSNT->update("payment_manual", ["title" => check_string($_POST["title"]), "description" => check_string($_POST["description"]), "slug" => check_string($_POST["slug"]), "content" => isset($_POST["content"]) ? base64_encode($_POST["content"]) : NULL, "display" => check_string($_POST["display"]), "update_gettime" => gettime()], " `id` = '" . $id . "' ");
    if($isUpdate) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Cập nhật trang thanh toán thủ công") . " (" . check_string($_POST["title"]) . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Cập nhật trang thanh toán thủ công") . " (" . check_string($_POST["title"]) . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công !\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại !\")){window.history.back().location.reload();}</script>");
}
echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Chỉnh sửa trang ";
echo $row["title"];
echo "</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"#\">Nạp tiền</a></li>\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("recharge-manual");
echo "\">Manual Payment</a>\n                        </li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Chỉnh sửa trang\n                            ";
echo $row["title"];
echo "</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA TRANG\n                        </div>\n                        <div class=\"d-flex\">\n\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Title:\n                                    <span class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <input name=\"title\" type=\"text\" value=\"";
echo $row["title"];
echo "\" class=\"form-control\" required>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Description:</label>\n                                <div class=\"col-sm-8\">\n                                    <textarea class=\"form-control\" name=\"description\">";
echo $row["description"];
echo "</textarea>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Slug:\n                                    <span class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <input name=\"slug\" type=\"text\" value=\"";
echo $row["slug"];
echo "\" class=\"form-control\" required>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Icon:</label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"file\" class=\"custom-file-input mb-3\" name=\"icon\">\n                                    <img width=\"200px\" src=\"";
echo BASE_URL($row["icon"]);
echo "\" />\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\"\n                                    for=\"example-hf-email\">";
echo __("Nội dung chi tiết:");
echo "</label>\n                                <div class=\"col-sm-12\">\n                                    <textarea class=\"content\" id=\"content\" name=\"content\">";
echo base64_decode($row["content"]);
echo "</textarea>\n                                    <br>\n                                    <ul>\n                                        <li><strong>{username}</strong> => Username của khách hàng.</li>\n                                        <li><strong>{id}</strong> => ID của khách hàng.</li>\n                                        <li><strong>{hotline}</strong> => Hotline đã nhập trong cài đặt.</li>\n                                        <li><strong>{email} </strong> => Email đã nhập trong cài đặt.</li>\n                                        <li><strong>{fanpage}</strong> => Fanpage đã nhập trong cài đặt.</li>\n                                    </ul>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Trạng thái:");
echo "                                    <span class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control\" name=\"display\" required>\n                                        <option ";
echo $row["display"] == 1 ? "selected" : "";
echo " value=\"1\">ON</option>\n                                        <option ";
echo $row["display"] == 0 ? "selected" : "";
echo " value=\"0\">OFF</option>\n                                    </select>\n                                </div>\n                            </div>\n\n\n                            <a type=\"button\" class=\"btn btn-hero btn-danger\"\n                                href=\"";
echo base_url_admin("recharge-manual");
echo "\"><i class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"save\" class=\"btn btn-hero btn-success\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n\n<script>\nCKEDITOR.replace(\"content\");\n</script>\n                                    ";

?>