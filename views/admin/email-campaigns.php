<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Email Campaigns") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n\n";
$body["footer"] = "\n\n\n\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "view_email_campaigns")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["submit"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_email_campaigns")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    $isInsert = $CMSNT->insert("email_campaigns", ["name" => check_string($_POST["name"]), "subject" => $_POST["subject"], "cc" => !empty($_POST["cc"]) ? check_string($_POST["cc"]) : NULL, "bcc" => !empty($_POST["bcc"]) ? check_string($_POST["bcc"]) : NULL, "content" => $_POST["content"], "create_gettime" => gettime(), "update_gettime" => gettime(), "status" => 0]);
    if(empty($_POST["listUser"])) {
        foreach ($CMSNT->get_list("SELECT * FROM `users` WHERE `banned` = 0 AND `email` IS NOT NULL ") as $user) {
            $CMSNT->insert("email_sending", ["camp_id" => $CMSNT->get_row(" SELECT `id` FROM `email_campaigns` ORDER BY id DESC LIMIT 1 ")["id"], "user_id" => $user["id"], "status" => 0, "create_gettime" => gettime(), "update_gettime" => gettime()]);
        }
    } else {
        foreach ($_POST["listUser"] as $user) {
            $user = $CMSNT->get_row("SELECT * FROM `users` WHERE `id` = '" . $user . "' ");
            $CMSNT->insert("email_sending", ["camp_id" => $CMSNT->get_row(" SELECT `id` FROM `email_campaigns` ORDER BY id DESC LIMIT 1 ")["id"], "user_id" => $user["id"], "status" => 0, "create_gettime" => gettime(), "update_gettime" => gettime()]);
        }
    }
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Tạo chiến dịch Email Makreting") . " (" . check_string($_POST["name"]) . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Tạo chiến dịch Email Makreting") . " (" . check_string($_POST["name"]) . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Successful !\")){location.href = \"" . base_url_admin("email-campaigns") . "\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Failure !\")){window.history.back().location.reload();}</script>");
} else {
    if(isset($_GET["limit"])) {
        $limit = (int) check_string($_GET["limit"]);
    } else {
        $limit = 10;
    }
    if(isset($_GET["page"])) {
        $page = check_string((int) $_GET["page"]);
    } else {
        $page = 1;
    }
    $from = ($page - 1) * $limit;
    $where = " `id` > 0 ";
    $create_gettime = "";
    $subject = "";
    $shortByDate = "";
    $name = "";
    $status = "";
    if(!empty($_GET["status"])) {
        $status = check_string($_GET["status"]);
        $stt22 = $status - 1;
        $where .= " AND `status` = \"" . $stt22 . "\" ";
    }
    if(!empty($_GET["subject"])) {
        $subject = check_string($_GET["subject"]);
        $where .= " AND `subject` LIKE \"%" . $subject . "%\" ";
    }
    if(!empty($_GET["name"])) {
        $name = check_string($_GET["name"]);
        $where .= " AND `name` LIKE \"%" . $name . "%\" ";
    }
    if(!empty($_GET["create_gettime"])) {
        $create_date = check_string($_GET["create_date"]);
        $create_gettime = $create_date;
        $create_date_1 = str_replace("-", "/", $create_date);
        $create_date_1 = explode(" to ", $create_date_1);
        if($create_date_1[0] != $create_date_1[1]) {
            $create_date_1 = [$create_date_1[0] . " 00:00:00", $create_date_1[1] . " 23:59:59"];
            $where .= " AND `thoigian` >= '" . $create_date_1[0] . "' AND `thoigian` <= '" . $create_date_1[1] . "' ";
        }
    }
    if(isset($_GET["shortByDate"])) {
        $shortByDate = check_string($_GET["shortByDate"]);
        $yesterday = date("Y-m-d", strtotime("-1 day"));
        $currentWeek = date("W");
        $currentMonth = date("m");
        $currentYear = date("Y");
        $currentDate = date("Y-m-d");
        if($shortByDate == 1) {
            $where .= " AND `thoigian` LIKE '%" . $currentDate . "%' ";
        }
        if($shortByDate == 2) {
            $where .= " AND YEAR(thoigian) = " . $currentYear . " AND WEEK(thoigian, 1) = " . $currentWeek . " ";
        }
        if($shortByDate == 3) {
            $where .= " AND MONTH(thoigian) = '" . $currentMonth . "' AND YEAR(thoigian) = '" . $currentYear . "' ";
        }
    }
    $listDatatable = $CMSNT->get_list(" SELECT * FROM `email_campaigns` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
    $totalDatatable = $CMSNT->num_rows(" SELECT * FROM `email_campaigns` WHERE " . $where . " ORDER BY id DESC ");
    $urlDatatable = pagination(base_url_admin("email-campaigns&limit=" . $limit . "&shortByDate=" . $shortByDate . "&subject=" . $subject . "&create_gettime=" . $create_gettime . "&name=" . $name . "&status=" . $status . "&"), $from, $totalDatatable, $limit);
    echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-envelope\"></i> Email Campaigns</h1>\n        </div>\n        ";
    if(120 <= time() - $CMSNT->site("check_time_cron_sending_email")) {
        echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\"\n                href=\"";
        echo base_url("cron/sending_email.php");
        echo "\"\n                target=\"_blank\">";
        echo base_url("cron/sending_email.php");
        echo "</a> 1 phút 1 lần để sử dụng chức năng Email\n            Campaigns.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
    }
    echo "        ";
    if($CMSNT->site("smtp_status") != 1) {
        echo "        <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng cấu hình <b>SMTP</b> để sử dụng chức năng Email Campaigns\n            <a class=\"text-primary\"\n                href=\"https://help.cmsnt.co/huong-dan/huong-dan-cau-hinh-smtp-vao-website-shopclone7/\"\n                target=\"_blank\">Xem Hướng Dẫn</a>.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
    }
    echo "        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH CHIẾN DỊCH EMAIL MARKETING\n                        </div>\n                        <div class=\"d-flex\">\n                            <a type=\"button\" href=\"";
    echo base_url_admin("email-campaign-add");
    echo "\"\n                                class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                    class=\"ri-add-line fw-semibold align-middle\"></i> Tạo chiến dịch mới</a>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"email-campaigns\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
    echo $name;
    echo "\" name=\"name\"\n                                        placeholder=\"Tên chiến dịch\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
    echo $subject;
    echo "\" name=\"subject\"\n                                        placeholder=\"Tiêu đề mail\">\n                                </div>\n                                <div class=\"col-md-3 col-6\">\n                                    <select class=\"form-control form-control-sm\" name=\"status\">\n                                        <option value=\"\">";
    echo __("Trạng thái");
    echo "</option>\n                                        <option ";
    echo $status == 1 ? "selected" : "";
    echo " value=\"1\">Processing</option>\n                                        <option ";
    echo $status == 3 ? "selected" : "";
    echo " value=\"3\">Cancel</option>\n                                        <option ";
    echo $status == 2 ? "selected" : "";
    echo " value=\"2\">Completed</option>\n                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
    echo $create_gettime;
    echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
    echo __("Search");
    echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\"\n                                        href=\"";
    echo base_url_admin("email-campaigns");
    echo "\"><i class=\"fa fa-trash\"></i>\n                                        ";
    echo __("Clear filter");
    echo "                                    </a>\n                                </div>\n                            </div>\n                            <div class=\"top-filter\">\n                                <div class=\"filter-show\">\n                                    <label class=\"filter-label\">Show :</label>\n                                    <select name=\"limit\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option ";
    echo $limit == 5 ? "selected" : "";
    echo " value=\"5\">5</option>\n                                        <option ";
    echo $limit == 10 ? "selected" : "";
    echo " value=\"10\">10</option>\n                                        <option ";
    echo $limit == 20 ? "selected" : "";
    echo " value=\"20\">20</option>\n                                        <option ";
    echo $limit == 50 ? "selected" : "";
    echo " value=\"50\">50</option>\n                                        <option ";
    echo $limit == 100 ? "selected" : "";
    echo " value=\"100\">100</option>\n                                        <option ";
    echo $limit == 500 ? "selected" : "";
    echo " value=\"500\">500</option>\n                                        <option ";
    echo $limit == 1000 ? "selected" : "";
    echo " value=\"1000\">1000</option>\n                                    </select>\n                                </div>\n                                <div class=\"filter-short\">\n                                    <label class=\"filter-label\">";
    echo __("Short by Date:");
    echo "</label>\n                                    <select name=\"shortByDate\" onchange=\"this.form.submit()\"\n                                        class=\"form-select filter-select\">\n                                        <option value=\"\">";
    echo __("Tất cả");
    echo "</option>\n                                        <option ";
    echo $shortByDate == 1 ? "selected" : "";
    echo " value=\"1\">";
    echo __("Hôm nay");
    echo "                                        </option>\n                                        <option ";
    echo $shortByDate == 2 ? "selected" : "";
    echo " value=\"2\">";
    echo __("Tuần này");
    echo "                                        </option>\n                                        <option ";
    echo $shortByDate == 3 ? "selected" : "";
    echo " value=\"3\">\n                                            ";
    echo __("Tháng này");
    echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th>Tên chiến dịch</th>\n                                        <th>Tiêu đề mail</th>\n                                        <th class=\"text-center\">Trạng thái</th>\n                                        <th class=\"text-center\">Tiến trình</th>\n                                        <th class=\"text-center\">Thời gian</th>\n                                        <th>Thao tác</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
    $i = 0;
    foreach ($listDatatable as $row) {
        echo "                                    <tr>\n                                        <td><b>";
        echo $row["name"];
        echo "</b></td>\n                                        <td><small>";
        echo $row["subject"];
        echo "</small></td>\n                                        <td class=\"text-center\">";
        echo display_camp($row["status"]);
        echo "</td>\n                                        <td>\n                                            ";
        $total_success = $CMSNT->get_row(" SELECT COUNT(id) FROM `email_sending` WHERE `camp_id` = '" . $row["id"] . "' AND `status` = 1 ")["COUNT(id)"];
        $total = $CMSNT->get_row(" SELECT COUNT(id) FROM `email_sending` WHERE `camp_id` = '" . $row["id"] . "' ")["COUNT(id)"];
        $phantram = 0;
        if($total != 0) {
            $phantram = $total_success / $total * 100;
        }
        echo "\n                                            <div class=\"progress progress-xl  progress-animate custom-progress-4 info\"\n                                                role=\"progressbar\" aria-valuenow=\"";
        echo $total_success;
        echo "\"\n                                                aria-valuemin=\"0\" aria-valuemax=\"";
        echo $total;
        echo "\">\n                                                <div class=\"progress-bar bg-info-gradient\"\n                                                    style=\"width: ";
        echo $phantram;
        echo "%\"></div>\n                                                <div class=\"progress-bar-label\">\n                                                    ";
        echo format_cash($total_success);
        echo "/";
        echo format_cash($total);
        echo "                                                    (";
        echo format_cash($phantram);
        echo "%)</div>\n                                            </div>\n                                            <div class=\"text-center\"><a class=\"text-primary\"\n                                                    href=\"";
        echo base_url_admin("email-sending-view&id=" . $row["id"]);
        echo "\">View\n                                                    Sending Report</a></div>\n                                        </td>\n                                        <td class=\"text-center\"><span\n                                                class=\"badge bg-light text-dark\">";
        echo $row["create_gettime"];
        echo "</span>\n                                        </td>\n                                        <td>\n                                            <div class=\"dropdown\">\n                                                <button type=\"button\" class=\"btn btn-dark btn-sm dropdown-toggle\"\n                                                    data-bs-toggle=\"dropdown\" aria-haspopup=\"true\"\n                                                    aria-expanded=\"false\">\n                                                    ";
        echo __("Manage");
        echo "                                                </button>\n                                                <div class=\"dropdown-menu\" style=\"\">\n                                                    <a class=\"dropdown-item\"\n                                                        href=\"";
        echo base_url_admin("email-sending-view&id=" . $row["id"]);
        echo "\"><i\n                                                            class=\"fa-solid fa-eye\"></i> View</a>\n                                                    <a class=\"dropdown-item\"\n                                                        href=\"";
        echo base_url_admin("email-campaign-edit&id=" . $row["id"]);
        echo "\"><i\n                                                            class=\"fa-solid fa-pen-to-square\"></i> Edit</a>\n                                                    <button class=\"dropdown-item\" onclick=\"CancelRow(";
        echo $row["id"];
        echo ")\"\n                                                        ";
        echo $row["status"] == 2 ? "disabled" : "";
        echo "><i\n                                                            class=\"fa-solid fa-ban\"></i> ";
        echo __("Cancel");
        echo "</button>\n\n                                                    <button class=\"dropdown-item\"\n                                                        onclick=\"RemoveRow(";
        echo $row["id"];
        echo ")\"><i\n                                                            class=\"fa-solid fa-trash\"></i> ";
        echo __("Delete");
        echo "</button>\n                                                </div>\n                                            </div>\n                                        </td>\n                                    </tr>\n                                    ";
    }
    echo "                                </tbody>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
    echo $limit;
    echo " of ";
    echo format_cash($totalDatatable);
    echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
    echo $limit < $totalDatatable ? $urlDatatable : "";
    echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n";
    require_once __DIR__ . "/footer.php";
    echo "\n<script>\nCKEDITOR.replace(\"content\");\n\nconst multipleCancelButton = new Choices(\n    '#listUser', {\n        allowHTML: true,\n        removeItemButton: true,\n    }\n);\n</script>\n\n<script type=\"text/javascript\">\nfunction CancelRow(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Campaign Cancel Confirmation\",\n        message: \"Are you sure you want to cancel this campaign?\",\n        confirmText: \"Ok\",\n        cancelText: \"Cancel\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
    echo BASE_URL("ajaxs/admin/update.php");
    echo "\",\n                type: 'POST',\n                dataType: \"JSON\",\n                data: {\n                    action: 'cancel_email_campaigns',\n                    id: id\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                }\n            });\n        }\n    })\n}\n\n\nfunction RemoveRow(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Campaign deletion confirmation\",\n        message: \"Are you sure you want to delete this campaign?\",\n        confirmText: \"Ok\",\n        cancelText: \"Cancel\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
    echo BASE_URL("ajaxs/admin/remove.php");
    echo "\",\n                type: 'POST',\n                dataType: \"JSON\",\n                data: {\n                    action: 'email_campaigns',\n                    id: id\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                }\n            });\n        }\n    })\n}\n</script>";
}

?>