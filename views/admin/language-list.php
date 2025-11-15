<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("List of languages"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css\">\n\n";
$body["footer"] = "\n<script src=\"https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js\"></script>\n<script src=\"https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js\"></script>\n<script src=\"https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js\"></script>\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_lang")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["AddLang"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used as this is a demo site.") . "\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_lang")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    $icon = "";
    if(check_img("icon")) {
        $rand = check_string($_POST["lang"]);
        $uploads_dir = "assets/storage/flags/flag_" . $rand . ".png";
        $tmp_name = $_FILES["icon"]["tmp_name"];
        $addIcon = move_uploaded_file($tmp_name, $uploads_dir);
        if($addIcon) {
            $icon = "assets/storage/flags/flag_" . $rand . ".png";
        } else {
            $icon = "";
        }
    }
    $isInsert = $CMSNT->insert("languages", ["icon" => $icon, "lang" => check_string($_POST["lang"]), "status" => check_string($_POST["status"])]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Thêm ngôn ngữ") . " (" . $_POST["lang"] . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", __("Thêm ngôn ngữ") . " (" . $_POST["lang"] . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("Successfully added new!") . "\")){location.href = \"" . base_url_admin("language-list") . "\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Add new failure!") . "\")){window.history.back().location.reload();}</script>");
}
if(isset($_POST["SaveSettings"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_lang")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Cấu hình ngôn ngữ")]);
    foreach ($_POST as $key => $value) {
        $CMSNT->update("settings", ["value" => $value], " `name` = '" . $key . "' ");
    }
    $my_text = $CMSNT->site("noti_action");
    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
    $my_text = str_replace("{username}", $getUser["username"], $my_text);
    $my_text = str_replace("{action}", __("Cấu hình ngôn ngữ"), $my_text);
    $my_text = str_replace("{ip}", myip(), $my_text);
    $my_text = str_replace("{time}", gettime(), $my_text);
    sendMessAdmin($my_text);
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Save successfully!") . "\")){window.history.back().location.reload();}</script>");
} else {
    echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Languages</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Languages</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-6\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row\">\n                                <div class=\"col-lg-12 col-xl-12\">\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-4 col-form-label\"\n                                            for=\"example-hf-email\">";
    echo __("Loại");
    echo "</label>\n                                        <div class=\"col-sm-8\">\n                                            <select class=\"form-control\" id=\"language_type\" name=\"language_type\">\n                                                <option\n                                                    ";
    echo $CMSNT->site("language_type") == "manual" ? "selected" : "";
    echo "                                                    value=\"manual\">Dịch thủ công\n                                                </option>\n                                                <option\n                                                    ";
    echo $CMSNT->site("language_type") == "gtranslate" ? "selected" : "";
    echo "                                                    value=\"gtranslate\">Gtranslate.io\n                                                </option>\n                                            </select>\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-lg-12 col-xl-12\" id=\"gtranslate_script\" style=\"display:none;\">\n                                    <div class=\"row mb-4\">\n                                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Gtranslate\n                                            Script</label>\n                                        <div class=\"col-sm-8\">\n                                            <textarea class=\"form-control\" rows=\"5\" name=\"gtranslate_script\">";
    echo $CMSNT->site("gtranslate_script");
    echo "</textarea>\n                                            <small>Truy cập vào <a href=\"https://gtranslate.io/website-translator-widget\" class=\"text-primary\" target=\"_blank\">gtranslate.io</a> để tạo mã sciprt theo nhu cầu của bạn, hoặc sử dụng sciprt mặc định của chúng tôi cung cấp.</small>\n                                        </div>\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"d-grid gap-2 mb-4\">\n                                <button type=\"submit\" name=\"SaveSettings\" class=\"btn btn-primary btn-block\"><i\n                                        class=\"fa fa-fw fa-save me-1\"></i>\n                                    ";
    echo __("Save");
    echo "</button>\n                            </div>\n                        </form>\n                        <p>Hướng dẫn sử dụng tính năng đa ngôn ngữ: <a target=\"_blank\" class=\"text-primary\" href=\"https://help.cmsnt.co/danh-muc/huong-dan-su-dung-tinh-nang-da-ngon-ngu/\">https://help.cmsnt.co/danh-muc/huong-dan-su-dung-tinh-nang-da-ngon-ngu/</a></p>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xl-12\" id=\"table2a\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH NGÔN NGỮ\n                        </div>\n                        <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                            class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                class=\"ri-add-line fw-semibold align-middle\"></i> Thêm ngôn ngữ mới</button>\n                    </div>\n                    <div class=\"card-body\">\n                        <table id=\"datatable-basic\" class=\"table text-nowrap table-striped table-hover table-bordered\"\n                            style=\"width:100%\">\n                            <thead>\n                                <tr>\n                                    <th style=\"width: 5px;\">#</th>\n                                    <th>";
    echo __("Language");
    echo "</th>\n                                    <th>";
    echo __("Default");
    echo "</th>\n                                    <th class=\"text-center\">";
    echo __("Status");
    echo "</th>\n                                    <th width=\"20%\">";
    echo __("Action");
    echo "</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
    foreach ($CMSNT->get_list("SELECT * FROM `languages` ORDER BY `id` DESC ") as $row) {
        echo "                                <tr>\n                                    <td>";
        echo $row["id"];
        echo "</td>\n                                    <td><img width=\"25px\" src=\"";
        echo base_url($row["icon"]);
        echo "\"> ";
        echo $row["lang"];
        echo "                                    </td>\n                                    <td>";
        echo display_mark($row["lang_default"]);
        echo "</td>\n                                    <td class=\"text-center\">";
        echo display_status_product($row["status"]);
        echo "</td>\n                                    <td class=\"text-center fs-base\">\n                                        <a type=\"button\" onclick=\"setDefault('";
        echo $row["id"];
        echo "')\"\n                                            class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                            title=\"";
        echo __("Set Default");
        echo "\">\n                                            <i class=\"fa fa-key\"></i>\n                                        </a>\n                                        <a type=\"button\" href=\"";
        echo base_url_admin("translate-list&id=" . $row["id"]);
        echo "\"\n                                            class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                            title=\"";
        echo __("Translate");
        echo "\">\n                                            <i class=\"fa fa-language\"></i>\n                                        </a>\n                                        <a type=\"button\" href=\"";
        echo base_url_admin("language-edit&id=" . $row["id"]);
        echo "\"\n                                            class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                            title=\"";
        echo __("Edit");
        echo "\">\n                                            <i class=\"fa fa-pencil-alt\"></i>\n                                        </a>\n                                        <a type=\"button\" onclick=\"RemoveRow('";
        echo $row["id"];
        echo "')\"\n                                            class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                            title=\"";
        echo __("Delete");
        echo "\">\n                                            <i class=\"fas fa-trash\"></i>\n                                        </a>\n                                    </td>\n                                </tr>\n                                ";
    }
    echo "                            </tbody>\n                        </table>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered modal-lg dialog-scrollable\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Thêm ngôn ngữ mới</h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Tên ngôn ngữ</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"text\" class=\"form-control\" name=\"lang\"\n                                placeholder=\"Nhập tên ngôn ngữ VD: English\" required>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Flag</label>\n                        <div class=\"col-sm-8\">\n\n                            <input class=\"form-control\" type=\"file\" name=\"icon\" required id=\"example-file-input\">\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Trạng thái</label>\n                        <div class=\"col-sm-8\">\n                            <select class=\"form-control\" name=\"status\" required>\n                                <option value=\"1\">";
    echo __("Show");
    echo "</option>\n                                <option value=\"0\">";
    echo __("Hide");
    echo "</option>\n                            </select>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light \" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddLang\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>\n                        ";
    echo __("Submit");
    echo "</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n\n\n";
    require_once __DIR__ . "/footer.php";
    echo "\n<script>\ndocument.addEventListener(\"DOMContentLoaded\", function() {\n    // Lắng nghe sự kiện thay đổi của select\n    document.getElementById('language_type').addEventListener('change', function() {\n        // Lấy giá trị được chọn\n        var selectedValue = this.value;\n\n        // Kiểm tra nếu giá trị là 'gtranslate' thì ẩn div #table, ngược lại hiển thị\n        if (selectedValue === 'gtranslate') {\n            document.getElementById('table2a').style.display = 'none';\n            document.getElementById('gtranslate_script').style.display = 'block';\n        } else {\n            document.getElementById('table2a').style.display = 'block';\n            document.getElementById('gtranslate_script').style.display = 'none';\n        }\n    });\n\n    // Kích hoạt sự kiện change để ẩn/div #table ban đầu nếu là 'gtranslate'\n    var initialSelectedValue = document.getElementById('language_type').value;\n    if (initialSelectedValue === 'gtranslate') {\n        document.getElementById('table2a').style.display = 'none';\n        document.getElementById('gtranslate_script').style.display = 'block';\n    }\n});\n</script>\n<script type=\"text/javascript\">\nfunction setDefault(id) {\n    \$('.setDefault').html('<i class=\"fa fa-spinner fa-spin\"></i> Loading...').prop('disabled',\n        true);\n    \$.ajax({\n        url: \"";
    echo BASE_URL("ajaxs/admin/update.php");
    echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'setDefaultLanguage',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n                location.reload();\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n\nfunction RemoveRow(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"";
    echo __("Confirm Language Remove");
    echo "\",\n        message: \"Bạn có chắc chắn muốn xóa ngôn ngữ ID \" + id + \" không ?\",\n        confirmText: \"Okey\",\n        cancelText: \"Close\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
    echo BASE_URL("ajaxs/admin/remove.php");
    echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    action: 'removeLanguage',\n                    id: id\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n</script>\n\n<script>\n\$('#datatable-basic').DataTable({\n    language: {\n        searchPlaceholder: 'Search...',\n        sSearch: '',\n    },\n    \"pageLength\": 10,\n    scrollX: true\n});\n</script>";
}

?>