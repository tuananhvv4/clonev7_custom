<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Recharge Bank Config"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css\">\n\n";
$body["footer"] = "\n<script src=\"https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js\"></script>\n<script src=\"https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js\"></script>\n<script src=\"https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js\"></script>\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "edit_recharge")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["SaveSettings"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    $checkKey = checkLicenseKey($CMSNT->site("license_key"));
    if(!$checkKey["status"]) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . $checkKey["msg"] . "\")){window.history.back().location.reload();}</script>");
    }
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Cấu hình nạp tiền Ngân Hàng")]);
    foreach ($_POST as $key => $value) {
        $CMSNT->update("settings", ["value" => $value], " `name` = '" . $key . "' ");
    }
    $my_text = $CMSNT->site("noti_action");
    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
    $my_text = str_replace("{username}", $getUser["username"], $my_text);
    $my_text = str_replace("{action}", __("Cấu hình nạp tiền Ngân Hàng"), $my_text);
    $my_text = str_replace("{ip}", myip(), $my_text);
    $my_text = str_replace("{time}", gettime(), $my_text);
    sendMessAdmin($my_text);
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Lưu thành công!") . "\")){window.history.back().location.reload();}</script>");
} else {
    if(isset($_POST["ThemNganHang"])) {
        if($CMSNT->site("status_demo") != 0) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . __("Bạn không thể sử dụng chức năng này vì đây là trang web demo") . "\")){window.history.back().location.reload();}</script>");
        }
        $checkKey = checkLicenseKey($CMSNT->site("license_key"));
        if(!$checkKey["status"]) {
            exit("<script type=\"text/javascript\">if(!alert(\"" . $checkKey["msg"] . "\")){window.history.back().location.reload();}</script>");
        }
        $url_image = "";
        if(check_img("image")) {
            $rand = random("0123456789QWERTYUIOPASDGHJKLZXCVBNM", 3);
            $uploads_dir = "assets/storage/images/bank/" . $rand . ".png";
            $tmp_name = $_FILES["image"]["tmp_name"];
            $addlogo = move_uploaded_file($tmp_name, $uploads_dir);
            if($addlogo) {
                $url_image = "assets/storage/images/bank/" . $rand . ".png";
            }
        }
        $isInsert = $CMSNT->insert("banks", ["image" => $url_image, "short_name" => check_string($_POST["short_name"]), "accountNumber" => check_string($_POST["accountNumber"]), "token" => check_string(removeSpaces($_POST["token"])), "password" => check_string($_POST["password"]), "accountName" => check_string($_POST["accountName"])]);
        if($isInsert) {
            $Mobile_Detect = new Mobile_Detect();
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Thêm ngân hàng") . " (" . $_POST["short_name"] . " - " . $_POST["accountNumber"] . ")."]);
            $my_text = $CMSNT->site("noti_action");
            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
            $my_text = str_replace("{username}", $getUser["username"], $my_text);
            $my_text = str_replace("{action}", __("Thêm ngân hàng") . " (" . $_POST["short_name"] . " - " . $_POST["accountNumber"] . ").", $my_text);
            $my_text = str_replace("{ip}", myip(), $my_text);
            $my_text = str_replace("{time}", gettime(), $my_text);
            sendMessAdmin($my_text);
            exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công !\")){window.history.back().location.reload();}</script>");
        }
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại !\")){window.history.back().location.reload();}</script>");
    }
    echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Cấu hình ngân hàng</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"#\">Nạp tiền</a></li>\n                        <li class=\"breadcrumb-item\"><a href=\"";
    echo base_url_admin("recharge-bank");
    echo "\">Ngân hàng</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Cấu hình</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        ";
    if(120 <= time() - $CMSNT->site("check_time_cron_bank")) {
        echo "            <div class=\"alert alert-danger alert-dismissible fade show custom-alert-icon shadow-sm\" role=\"alert\">\n            <svg class=\"svg-danger\" xmlns=\"http://www.w3.org/2000/svg\" height=\"1.5rem\" viewBox=\"0 0 24 24\"\n                width=\"1.5rem\" fill=\"#000000\">\n                <path d=\"M0 0h24v24H0z\" fill=\"none\" />\n                <path\n                    d=\"M15.73 3H8.27L3 8.27v7.46L8.27 21h7.46L21 15.73V8.27L15.73 3zM12 17.3c-.72 0-1.3-.58-1.3-1.3 0-.72.58-1.3 1.3-1.3.72 0 1.3.58 1.3 1.3 0 .72-.58 1.3-1.3 1.3zm1-4.3h-2V7h2v6z\" />\n            </svg>\n            Vui lòng thực hiện <b><a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-xu-ly-khi-website-bao-loi-cron/\">CRON JOB</a></b> liên kết: <a class=\"text-primary\" href=\"";
        echo base_url("cron/bank.php");
        echo "\"\n                target=\"_blank\">";
        echo base_url("cron/bank.php");
        echo "</a> 1 phút 1 lần để hệ thống xử lý nạp tiền tự động.\n            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"><i\n                    class=\"bi bi-x\"></i></button>\n        </div>\n        ";
    }
    echo "        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"text-right\">\n                    <a class=\"btn btn-danger label-btn mb-3\" href=\"";
    echo base_url_admin("recharge-bank");
    echo "\">\n                        <i class=\"ri-arrow-go-back-line label-btn-icon me-2\"></i> QUAY LẠI\n                    </a>\n                </div>\n            </div>\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH NGÂN HÀNG\n                        </div>\n                        <div class=\"d-flex\">\n                            <button data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                                class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                    class=\"ri-add-line fw-semibold align-middle\"></i> Thêm ngân hàng</button>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <table id=\"datatable-basic\" class=\"table text-nowrap table-striped table-hover table-bordered\"\n                            style=\"width:100%\">\n                            <thead>\n                                <tr>\n                                    <th>#</th>\n                                    <th>";
    echo __("Ngân hàng");
    echo "</th>\n                                    <th>";
    echo __("Số tài khoản");
    echo "</th>\n                                    <th>";
    echo __("Chủ tài khoản");
    echo "</th>\n                                    <th>Trạng thái</th>\n                                    <th>";
    echo __("Thao tác");
    echo "</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
    $i = 0;
    foreach ($CMSNT->get_list("SELECT * FROM `banks`  ") as $bank) {
        echo "                                <tr>\n                                    <td>";
        echo $i++;
        echo "</td>\n                                    <td>";
        echo $bank["short_name"];
        echo "</td>\n                                    <td>";
        echo $bank["accountNumber"];
        echo "</td>\n                                    <td>";
        echo $bank["accountName"];
        echo "</td>\n                                    <td>";
        echo display_status_product($bank["status"]);
        echo "</td>\n                                    <td><a aria-label=\"\"\n                                            href=\"";
        echo base_url_admin("recharge-bank-edit&id=" . $bank["id"]);
        echo "\"\n                                            style=\"color:white;\" class=\"btn btn-info btn-sm btn-icon-left m-b-10\"\n                                            type=\"button\">\n                                            <i class=\"fas fa-edit mr-1\"></i><span class=\"\"> Edit</span>\n                                        </a>\n                                        <button style=\"color:white;\" onclick=\"RemoveRow('";
        echo $bank["id"];
        echo "')\"\n                                            class=\"btn btn-danger btn-sm btn-icon-left m-b-10\" type=\"button\">\n                                            <i class=\"fas fa-trash mr-1\"></i><span class=\"\"> Delete</span>\n                                        </button>\n                                    </td>\n                                </tr>\n                                ";
    }
    echo "                            </tbody>\n                        </table>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            CẤU HÌNH\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row\">\n                                <div class=\"col-lg-12 col-xl-6\">\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Trạng\n                                            thái</label>\n                                        <div class=\"col-sm-8\">\n                                            <select class=\"form-control form-control-sm\" name=\"bank_status\">\n                                                <option ";
    echo $CMSNT->site("bank_status") == 0 ? "selected" : "";
    echo "                                                    value=\"0\">OFF\n                                                </option>\n                                                <option ";
    echo $CMSNT->site("bank_status") == 1 ? "selected" : "";
    echo "                                                    value=\"1\">ON\n                                                </option>\n                                            </select>\n                                        </div>\n                                    </div>\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-4 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Prefix");
    echo "</label>\n                                        <div class=\"col-sm-8\">\n                                            <div class=\"input-group\">\n                                                <input type=\"text\" class=\"form-control form-control-sm\"\n                                                    value=\"";
    echo $CMSNT->site("prefix_autobank");
    echo "\" name=\"prefix_autobank\"\n                                                    placeholder=\"VD: NAPTIEN\">\n                                                <span class=\"input-group-text\">\n                                                    ";
    echo $getUser["id"];
    echo "                                                </span>\n                                            </div>\n                                            <small>Không được để trống Prefix, Prefix là nội dung nạp tiền vào hệ thống.</small>\n                                        </div>\n                                    </div>\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-4 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Token Webhook API");
    echo "</label>\n                                        <div class=\"col-sm-8\">\n                                            <div class=\"input-group\">\n                                                <input type=\"text\" class=\"form-control\"\n                                                    value=\"";
    echo $CMSNT->site("token_webhook_web2m");
    echo "\" name=\"token_webhook_web2m\"\n                                                    placeholder=\"Token Webhook API nếu có\">\n                                        \n                                            </div>\n                                            <small>Nếu dùng CRON thì không cần quan tâm đến thông tin này</small>\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-lg-12 col-xl-6\">\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-6 col-form-label\" for=\"example-hf-email\">Số tiền\n                                            nạp tối thiểu</label>\n                                        <div class=\"col-sm-6\">\n                                            <input type=\"text\" class=\"form-control form-control-sm\"\n                                                value=\"";
    echo $CMSNT->site("bank_min");
    echo "\" name=\"bank_min\">\n                                        </div>\n                                    </div>\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-6 col-form-label\" for=\"example-hf-email\">Số tiền\n                                            nạp tối đa</label>\n                                        <div class=\"col-sm-6\">\n                                            <input type=\"text\" class=\"form-control form-control-sm\"\n                                                value=\"";
    echo $CMSNT->site("bank_max");
    echo "\" name=\"bank_max\">\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-lg-12 col-xl-12\">\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-6 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Lưu ý nạp tiền");
    echo "</label>\n                                        <div class=\"col-sm-12\">\n                                            <textarea id=\"bank_notice\"\n                                                name=\"bank_notice\">";
    echo $CMSNT->site("bank_notice");
    echo "</textarea>\n                                        </div>\n                                    </div>\n                                </div>\n                            </div>\n                            <a type=\"button\" class=\"btn btn-danger\" href=\"\"><i class=\"fa fa-fw fa-undo me-1\"></i>\n                                ";
    echo __("Reload");
    echo "</a>\n                            <button type=\"submit\" name=\"SaveSettings\" class=\"btn btn-success\">\n                                <i class=\"fa fa-fw fa-save me-1\"></i> ";
    echo __("Save");
    echo "                            </button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n\n    <div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n        data-bs-keyboard=\"false\" aria-hidden=\"true\">\n        <div class=\"modal-dialog modal-dialog-centered modal-lg\">\n            <div class=\"modal-content\">\n                <div class=\"modal-header\">\n                    <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Thêm ngân hàng mới</h6>\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n                </div>\n                <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                    <div class=\"modal-body\">\n                        <div class=\"row mb-4\">\n                            <label class=\"col-sm-4 col-form-label\">";
    echo __("Ngân hàng");
    echo " <span\n                                    class=\"text-danger\">*</span></label>\n                            <div class=\"col-sm-8\">\n                                <input type=\"text\" class=\"form-control\" list=\"options\" name=\"short_name\"\n                                    placeholder=\"";
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
    echo "                                </datalist>\n                            </div>\n                        </div>\n                        <div class=\"row mb-4\">\n                            <label class=\"col-sm-4 col-form-label\">Image <span class=\"text-danger\">*</span></label>\n                            <div class=\"col-sm-8\">\n                                <input type=\"file\" class=\"form-control\" name=\"image\" required>\n                                <small>";
    echo __("Khi VietQR không hoạt động, hệ thống sẽ hiện ảnh này thay cho mã QR");
    echo "</small>\n                            </div>\n                        </div>\n                        <div class=\"row mb-4\">\n                            <label class=\"col-sm-4 col-form-label\">";
    echo __("Số tài khoản");
    echo " <span\n                                    class=\"text-danger\">*</span></label>\n                            <div class=\"col-sm-8\">\n                                <input type=\"text\" class=\"form-control\" name=\"accountNumber\"\n                                    placeholder=\"Nhập số tài khoản\" required>\n                            </div>\n                        </div>\n                        <div class=\"row mb-4\">\n                            <label class=\"col-sm-4 col-form-label\">";
    echo __("Chủ tài khoản");
    echo " <span\n                                    class=\"text-danger\">*</span></label>\n                            <div class=\"col-sm-8\">\n                                <input type=\"text\" class=\"form-control\" name=\"accountName\"\n                                    placeholder=\"Nhập tên chủ tài khoản\" required>\n                            </div>\n                        </div>\n                        <div class=\"row mb-4\">\n                            <label class=\"col-sm-4 col-form-label\"\n                                for=\"example-hf-email\">";
    echo __("Password Internet Banking");
    echo "</label>\n                            <div class=\"col-sm-8\">\n                                <input type=\"text\" class=\"form-control\" name=\"password\"\n                                    placeholder=\"Áp dụng khi cấu hình nạp tiền tự động.\">\n                            </div>\n                        </div>\n                        <div class=\"row mb-4\">\n                            <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
    echo __("Token");
    echo "</label>\n                            <div class=\"col-sm-8\">\n                                <input type=\"text\" class=\"form-control\" name=\"token\"\n                                    placeholder=\"Áp dụng khi cấu hình nạp tiền tự động.\">\n                            </div>\n                        </div>\n                        <small>Hướng dẫn tích hợp tự động nạp tiền bằng Ngân Hàng tại <a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/huong-dan/huong-dan-tich-hop-nap-tien-bang-ngan-hang-vn-tu-dong/\">đây</a></small>\n                    </div>\n                    <div class=\"modal-footer\">\n                        <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\">Close</button>\n                        <button type=\"submit\" name=\"ThemNganHang\" class=\"btn btn-primary btn-sm\"><i\n                                class=\"fa fa-fw fa-plus me-1\"></i>\n                            ";
    echo __("Submit");
    echo "</button>\n                    </div>\n                </form>\n            </div>\n        </div>\n    </div>\n</div>\n\n";
    require_once __DIR__ . "/footer.php";
    echo "<script>\nCKEDITOR.replace(\"bank_notice\");\n</script>\n\n\n<script type=\"text/javascript\">\nfunction RemoveRow(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"";
    echo __("Confirm Remove");
    echo "\",\n        message: \"";
    echo __("Are you sure you want to delete the ID");
    echo " \" + id + \" ?\",\n        confirmText: \"Okey\",\n        cancelText: \"Close\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
    echo BASE_URL("ajaxs/admin/remove.php");
    echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    action: 'removeBank',\n                    id: id\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n</script>\n\n<script>\n\$('#datatable-basic').DataTable({\n    language: {\n        searchPlaceholder: 'Search...',\n        sSearch: '',\n    },\n    \"pageLength\": 10,\n    scrollX: true\n});\n</script>";
}

?>