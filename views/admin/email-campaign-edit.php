<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Edit Campaign"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n";
$body["footer"] = "\n<!-- bs-custom-file-input -->\n<script src=\"" . BASE_URL("public/AdminLTE3/") . "plugins/bs-custom-file-input/bs-custom-file-input.min.js\"></script>\n<!-- Page specific script -->\n<script>\n\$(function () {\n  bsCustomFileInput.init();\n});\n</script> \n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["id"])) {
    $id = check_string($_GET["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `email_campaigns` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        redirect(base_url_admin("email-campaigns"));
    }
} else {
    redirect(base_url_admin("email-campaigns"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(isset($_POST["submit"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_email_campaigns")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    $isInsert = $CMSNT->update("email_campaigns", ["name" => check_string($_POST["name"]), "subject" => $_POST["subject"], "cc" => !empty($_POST["cc"]) ? check_string($_POST["cc"]) : NULL, "bcc" => !empty($_POST["bcc"]) ? check_string($_POST["bcc"]) : NULL, "content" => $_POST["content"], "update_gettime" => gettime()], " `id` = '" . $row["id"] . "' ");
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Chỉnh sửa chiến dịch Email Marketing") . " (" . check_string($_POST["name"]) . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Chỉnh sửa chiến dịch Email Marketing") . " (" . check_string($_POST["name"]) . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Successful !\")){location.href = \"\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Failure !\")){window.history.back().location.reload();}</script>");
}
echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Chỉnh sửa chiến dịch ";
echo __($row["name"]);
echo "</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("email-campaigns");
echo "\">Email\n                                Campaigns</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Chỉnh sửa chiến dịch\n                            ";
echo __($row["name"]);
echo "</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA CHIẾN DỊCH\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\">\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Tên chiến dịch");
echo "                                    <span class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <input class=\"form-control\" value=\"";
echo $row["name"];
echo "\" type=\"text\"\n                                        placeholder=\"Nhập tên cho chiến dịch\" name=\"name\" required>\n                                </div>\n                            </div>\n                            <hr>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Subject");
echo " <span\n                                        class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <input class=\"form-control\" value=\"";
echo $row["subject"];
echo "\" type=\"text\" name=\"subject\"\n                                        required>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("CC");
echo "</label>\n                                <div class=\"col-sm-8\">\n                                    <input class=\"form-control\" type=\"text\" value=\"";
echo $row["cc"];
echo "\" name=\"cc\">\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("BCC");
echo "</label>\n                                <div class=\"col-sm-8\">\n                                    <input class=\"form-control\" type=\"text\" value=\"";
echo $row["bcc"];
echo "\" name=\"bcc\">\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Nội dung Email");
echo "                                    <span class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-12\">\n                                    <textarea class=\"content\" id=\"content\" name=\"content\"\n                                        required>";
echo $row["content"];
echo "</textarea>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger\" href=\"";
echo base_url_admin("email-campaigns");
echo "\"><i\n                                    class=\"fa fa-fw fa-undo me-1\"></i> ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"submit\" class=\"btn btn-primary\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n\n    </div>\n</div>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nCKEDITOR.replace(\"content\");\n</script>";

?>