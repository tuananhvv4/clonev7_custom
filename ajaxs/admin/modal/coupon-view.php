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
if(!checkPermission($getUser["admin"], "view_coupon")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
}
if(!($row = $CMSNT->get_row(" SELECT * FROM `coupons` WHERE `id` = '" . check_string($_GET["id"]) . "'  "))) {
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Item does not exist") . "\")){location.href=`" . base_url_admin("affiliate-withdraw") . "`;}</script>");
}
$id = check_string($_GET["id"]);
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
echo " \n\n<form action=\"";
echo BASE_URL("ajaxs/admin/modal/coupon-view.php?id=" . $row["id"] . "&token=" . $getUser["token"]);
echo "\"\n    method=\"POST\">\n\n    <div class=\"modal-header\">\n        <h6 class=\"modal-title\" id=\"staticBackdropLabel2\"><i class=\"fa-solid fa-clock-rotate-left\"></i> Nhật ký sử dụng mã giảm giá <span\n                class=\"text-primary\">";
echo $row["code"];
echo "</span>\n        </h6>\n        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n    </div>\n    <div class=\"modal-body\">\n        <table id=\"datatable-basic\" class=\"table text-nowrap table-striped table-hover table-bordered\"\n            style=\"width:100%\">\n            <thead>\n                <tr>\n                    <th>#</th>\n                    <th>Username</th>\n                    <th>Đơn hàng</th>\n                    <th>Thời gian</th>\n                </tr>\n            </thead>\n            <tbody>\n                ";
$i = 0;
foreach ($CMSNT->get_list("SELECT * FROM `coupon_used` WHERE `coupon_id` = '" . $id . "' ORDER BY `id` DESC ") as $row) {
    echo "                <tr>\n                    <td>";
    echo $i++;
    echo "</td>\n                    <td><a class=\"text-primary\"\n                            href=\"";
    echo base_url_admin("user-edit&id=" . $row["user_id"]);
    echo "\">";
    echo getRowRealtime("users", $row["user_id"], "username");
    echo "                            [ID ";
    echo $row["user_id"];
    echo "]</a></td>\n                    <td>";
    echo $row["trans_id"];
    echo "</td>\n                    <td>";
    echo $row["create_gettime"];
    echo "</td>\n                </tr>\n                ";
}
echo "            </tbody>\n        </table>\n    </div>\n    <div class=\"modal-footer\">\n        <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\"><i\n                class=\"fa fa-fw fa-times me-1\"></i>\n            Close</button>\n    </div>\n</form>\n\n\n<script>\n\$('#datatable-basic').DataTable({\n    language: {\n        searchPlaceholder: 'Search...',\n        sSearch: '',\n    },\n    \"pageLength\": 10\n});\n</script>\n ";

?>