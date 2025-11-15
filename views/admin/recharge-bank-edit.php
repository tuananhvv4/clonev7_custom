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
    $row = $CMSNT->get_row("SELECT * FROM `banks` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        redirect(base_url_admin("bank-list"));
    }
} else {
    redirect(base_url_admin("bank-list"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_recharge")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["LuuNganHang"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    $checkKey = checkLicenseKey($CMSNT->site("license_key"));
    if(!$checkKey["status"]) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . $checkKey["msg"] . "\")){window.history.back().location.reload();}</script>");
    }
    if(check_img("image")) {
        unlink($row["image"]);
        $rand = random("0123456789QWERTYUIOPASDGHJKLZXCVBNM", 3);
        $uploads_dir = "assets/storage/images/bank/" . $rand . ".png";
        $tmp_name = $_FILES["image"]["tmp_name"];
        $addlogo = move_uploaded_file($tmp_name, $uploads_dir);
        if($addlogo) {
            $CMSNT->update("banks", ["image" => "assets/storage/images/bank/" . $rand . ".png"], " `id` = '" . $id . "' ");
        }
    }
    $isUpdate = $CMSNT->update("banks", ["short_name" => check_string($_POST["short_name"]), "accountNumber" => check_string($_POST["accountNumber"]), "status" => check_string($_POST["status"]), "token" => check_string(removeSpaces($_POST["token"])), "password" => check_string($_POST["password"]), "accountName" => check_string($_POST["accountName"])], " `id` = '" . $id . "' ");
    if($isUpdate) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Cập nhật thông tin ngân hàng") . " (" . $_POST["short_name"] . " - " . $_POST["accountNumber"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Cập nhật thông tin ngân hàng") . " (" . $_POST["short_name"] . " - " . $_POST["accountNumber"] . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công !\")){window.history.back().location.reload();}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại !\")){window.history.back().location.reload();}</script>");
}
echo "\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Chỉnh sửa ngân hàng ";
echo $row["short_name"];
echo "</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"#\">Nạp tiền</a></li>\n                        <li class=\"breadcrumb-item\"><a href=\"";
echo base_url_admin("recharge-bank-config");
echo "\">Ngân hàng</a>\n                        </li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Chỉnh sửa ngân hàng\n                            ";
echo $row["short_name"];
echo "</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        ";
if(120 <= time() - $CMSNT->site("check_time_cron_bank")) {
    echo "            <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\" href=\"";
    echo base_url("cron/bank.php");
    echo "\"\n                target=\"_blank\">";
    echo base_url("cron/bank.php");
    echo "</a> 1 phút 1 lần để hệ thống xử lý nạp tiền tự động.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
}
echo "        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CHỈNH SỬA NGÂN HÀNG\n                        </div>\n                        <div class=\"d-flex\">\n                            <button data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                                class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                    class=\"ri-add-line fw-semibold align-middle\"></i> Thêm ngân hàng</button>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"mb-4\">\n                                <label for=\"exampleInputEmail1\">";
echo __("Ngân hàng");
echo " <span\n                                        class=\"text-danger\">*</span></label>\n                                <input type=\"text\" class=\"form-control\" value=\"";
echo $row["short_name"];
echo "\" list=\"options\"\n                                    name=\"short_name\" placeholder=\"";
echo __("Nhập tên ngân hàng");
echo "\" required>\n                                <datalist id=\"options\">\n                                    ";
foreach ($config_listbank as $key => $value) {
    echo "                                    <option value=\"";
    echo $key;
    echo "\">";
    echo $value;
    echo "</option>\n                                    ";
}
echo "                                    ";
$data = json_decode(curl_get("https://api.vietqr.io/v2/banks"), true);
foreach ($data["data"] as $bank) {
    echo "                                    <option value=\"";
    echo $bank["code"];
    echo "\">";
    echo $bank["name"];
    echo "</option>\n                                    ";
}
echo "                                </datalist>\n                            </div>\n                            <div class=\"row\">\n                                <div class=\"col-8\">\n                                    <div class=\"mb-4\">\n                                        <label for=\"exampleInputFile\">Image</label>\n                                        <input type=\"file\" class=\"form-control\" name=\"image\">\n                                    </div>\n                                </div>\n                                <div class=\"col-4\">\n                                    <div class=\"form-group\">\n                                        <img width=\"200px\" src=\"";
echo BASE_URL($row["image"]);
echo "\" />\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"mb-4\">\n                                <label for=\"exampleInputEmail1\">";
echo __("Account number");
echo "</label>\n                                <input type=\"text\" class=\"form-control\" name=\"accountNumber\"\n                                    value=\"";
echo $row["accountNumber"];
echo "\" placeholder=\"Nhập số tài khoản\" required>\n                            </div>\n                            <div class=\"mb-4\">\n                                <label for=\"exampleInputEmail1\">";
echo __("Account name");
echo "</label>\n                                <input type=\"text\" class=\"form-control\" name=\"accountName\"\n                                    value=\"";
echo $row["accountName"];
echo "\" placeholder=\"Nhập tên chủ tài khoản\" required>\n                            </div>\n                            <div class=\"mb-4\">\n                                <label for=\"exampleInputEmail1\">Trạng thái</label>\n                                <select class=\"form-control\" name=\"status\">\n                                    <option ";
echo $row["status"] == 1 ? "selected" : "";
echo " value=\"1\">ON</option>\n                                    <option ";
echo $row["status"] == 0 ? "selected" : "";
echo " value=\"0\">OFF</option>\n                                </select>\n                            </div>\n                            <div class=\"mb-4\">\n                                <label for=\"exampleInputEmail1\">";
echo __("Password Internet Banking");
echo "</label>\n                                <input type=\"text\" class=\"form-control\" name=\"password\" value=\"";
echo $row["password"];
echo "\"\n                                    placeholder=\"Áp dụng khi cấu hình nạp tiền tự động.\">\n                            </div>\n                            <div class=\"mb-4\">\n                                <label for=\"exampleInputEmail1\">";
echo __("Token");
echo "</label>\n                                <input type=\"text\" class=\"form-control\" name=\"token\" value=\"";
echo $row["token"];
echo "\"\n                                    placeholder=\"Áp dụng khi cấu hình nạp tiền tự động.\">\n                            </div>\n\n\n                            <a type=\"button\" class=\"btn btn-hero btn-danger\"\n                                href=\"";
echo base_url_admin("recharge-bank-config");
echo "\"><i\n                                    class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
echo __("Back");
echo "</a>\n                            <button type=\"submit\" name=\"LuuNganHang\" class=\"btn btn-hero btn-success\"><i\n                                    class=\"fa fa-fw fa-save me-1\"></i> ";
echo __("Save");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n";
require_once __DIR__ . "/footer.php";

?>