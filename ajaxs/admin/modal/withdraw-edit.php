<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../libs/db.php";
require_once __DIR__ . "/../../../libs/lang.php";
require_once __DIR__ . "/../../../libs/helper.php";
require_once __DIR__ . "/../../../libs/database/users.php";
require_once __DIR__ . "/../../../models/is_admin.php";
if(empty($_GET["token"])) {
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Please log in") . "\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
}
if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_GET["token"]) . "' AND `banned` = 0 AND `admin` != 0 "))) {
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Please log in") . "\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
}
if(!checkPermission($getUser["admin"], "edit_withdraw_affiliate")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
}
if(!($row = $CMSNT->get_row(" SELECT * FROM `aff_withdraw` WHERE `id` = '" . check_string($_GET["id"]) . "'  "))) {
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Item does not exist") . "\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
}
if(isset($_POST["btnSubmit"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
    }
    if($row["status"] == "cancel") {
        exit("<script type=\"text/javascript\">if(!alert(\"Đơn rút này đã được hoàn tiền rồi, không thể thay đổi trạng thái\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
    }
    if($_POST["status"] == "cancel") {
        $User = new users();
        $User->RefundCommission($row["user_id"], $row["amount"], __("Cancellation of withdrawal request") . " #" . $row["trans_id"]);
    }
    $isUpdate = $CMSNT->update("aff_withdraw", ["status" => check_string($_POST["status"]), "reason" => check_string($_POST["reason"]), "update_gettime" => gettime()], " `id` = '" . $row["id"] . "' ");
    if($isUpdate) {
        exit("<script type=\"text/javascript\">if(!alert(\"Lưu thành công!\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Lưu thất bại!\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
}
echo "\n\n<form action=\"";
echo BASE_URL("ajaxs/admin/modal/withdraw-edit.php?id=" . $row["id"] . "&token=" . $getUser["token"]);
echo "\"\n    method=\"POST\">\n\n    <div class=\"modal-header\">\n        <h6 class=\"modal-title\" id=\"staticBackdropLabel2\"><i class=\"fa fa-edit\"></i> ";
echo __("Chỉnh sửa yêu cầu");
echo "            #<span class=\"text-primary\">";
echo $row["trans_id"];
echo "</span>\n        </h6>\n        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n    </div>\n\n    <div class=\"modal-body\">\n        <div class=\"row\">\n            <div class=\"col-lg-6 col-xl-6\">\n                <div class=\"row mb-3\">\n                    <label class=\"col-sm-5 col-form-label\" for=\"example-hf-email\">Ngân hàng:</label>\n                    <div class=\"col-sm-7\">\n                        <input type=\"text\" class=\"form-control\" value=\"";
echo $row["bank"];
echo "\" disabled>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-lg-6 col-xl-6\">\n                <div class=\"row mb-3\">\n                    <label class=\"col-sm-5 col-form-label\" for=\"example-hf-email\">Số tài khoản:</label>\n                    <div class=\"col-sm-7\">\n                        <input type=\"text\" class=\"form-control\" value=\"";
echo $row["stk"];
echo "\" disabled>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-lg-6 col-xl-6\">\n                <div class=\"row mb-3\">\n                    <label class=\"col-sm-5 col-form-label\" for=\"example-hf-email\">Chủ tài khoản:</label>\n                    <div class=\"col-sm-7\">\n                        <input type=\"text\" class=\"form-control\" value=\"";
echo $row["name"];
echo "\" disabled>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-lg-6 col-xl-6\">\n                <div class=\"row mb-3\">\n                    <label class=\"col-sm-5 col-form-label\" for=\"example-hf-email\">Số tiền cần rút:</label>\n                    <div class=\"col-sm-7\">\n                        <input type=\"text\" class=\"form-control\" value=\"";
echo format_currency($row["amount"]);
echo "\" disabled>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-lg-6 col-xl-6\">\n                <div class=\"row mb-3\">\n                    <label class=\"col-sm-5 col-form-label\" for=\"example-hf-email\">Trạng thái:</label>\n                    <div class=\"col-sm-7\">\n                        <select class=\"form-control mb-1\" name=\"status\">\n                        <option ";
echo $row["status"] == "pending" ? "selected" : "";
echo " value=\"pending\">\n                                ";
echo __("Pending");
echo "</option>\n                            <option ";
echo $row["status"] == "completed" ? "selected" : "";
echo " value=\"completed\">\n                                ";
echo __("Completed");
echo "</option>\n                            <option ";
echo $row["status"] == "cancel" ? "selected" : "";
echo " value=\"cancel\">\n                                ";
echo __("Cancel");
echo "</option>\n                        </select>\n                        <ul>\n                            <li>Pending: đang chờ xử lý.</li>\n                            <li>Cancel: huỷ và hoàn tiền.</li>\n                            <li>Completed: hoàn thành yêu cầu rút tiền.</li>\n                        </ul>\n                        </br>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-lg-6 col-xl-6\">\n                <div class=\"row mb-3\">\n                    <label class=\"col-sm-5 col-form-label\" for=\"example-hf-email\">Lý do huỷ đơn nếu có:</label>\n                    <div class=\"col-sm-7\">\n                        <textarea class=\"form-control\" rows=\"4\" name=\"reason\">";
echo $row["reason"];
echo "</textarea>\n                    </div>\n                </div>\n            </div>\n        </div>\n                        <center class=\"py-3\">\n\n                        ";
if($row["bank"] == "Ví MOMO") {
    echo "                        ";
    echo file_get_contents("https://api.web2m.com/api/qrmomo.php?amount=" . $row["amount"] . "&phone=" . $row["stk"] . "&noidung=" . $row["trans_id"] . "&size=300");
    echo "                        ";
} else {
    echo "                        ";
    $img1 = "https://api.vietqr.io/" . $row["bank"] . "/" . $row["stk"] . "/" . $row["amount"] . "/" . $row["trans_id"] . "/vietqr_net_2.jpg?accountName=" . $row["name"];
    $img = $img1;
    $is_img = curl_get($img1);
    echo "                        ";
    if($is_img != "invalid acqId") {
        echo "                        <img src=\"";
        echo $img;
        echo "\" width=\"300px\" />\n                        ";
    } else {
        echo "        \n                        ";
    }
    echo "                        ";
}
echo "                    </center>\n    </div>\n    </div>\n\n    <div class=\"modal-footer\">\n        <button type=\"button\" class=\"btn btn-light\" data-bs-dismiss=\"modal\"><i\n                class=\"fa fa-fw fa-times me-1\"></i> Close</button>\n        <button type=\"submit\" name=\"btnSubmit\" class=\"btn btn-primary\"><i class=\"fa fa-fw fa-save me-1\"></i>\n            Save</button>\n    </div>\n</form>";

?>