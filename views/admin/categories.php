<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Categories") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n\n \n";
$body["footer"] = "\n\n\n<!-- Page JS Plugins -->\n \n\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "view_product")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["submit"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_product")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    if($CMSNT->get_row("SELECT * FROM `categories` WHERE `slug` = '" . create_slug(check_string($_POST["name"])) . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Chuyên mục này đã tồn tại trong hệ thống.\")){window.history.back().location.reload();}</script>");
    }
    $url_icon = NULL;
    if(check_img("icon")) {
        $rand = random("0123456789QWERTYUIOPASDGHJKLZXCVBNM", 4);
        $uploads_dir = "assets/storage/images/icon" . $rand . ".png";
        $tmp_name = $_FILES["icon"]["tmp_name"];
        $addlogo = move_uploaded_file($tmp_name, $uploads_dir);
        if($addlogo) {
            $url_icon = $uploads_dir;
        }
    }
    $isInsert = $CMSNT->insert("categories", ["stt" => check_string($_POST["stt"]), "icon" => $url_icon, "name" => check_string($_POST["name"]), "parent_id" => check_string($_POST["parent_id"]), "slug" => create_slug(check_string($_POST["name"])), "description" => check_string($_POST["description"]), "status" => check_string($_POST["status"]), "create_date" => gettime()]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add Category (" . check_string($_POST["name"]) . ")."]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Add Category (" . check_string($_POST["name"]) . ").", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công !\")){location.href = \"\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại !\")){window.history.back().location.reload();}</script>");
}
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-name fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-layer-group\"></i> Chuyên mục sản phẩm\n            </h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"text-right\">\n\n                </div>\n            </div>\n            <div class=\"col-xl-12\" id=\"card-hide\" style=\"display: none;\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-body\">\n                        <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"stt\">";
echo __("Ưu tiên:");
echo "</label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"text\" class=\"form-control\" value=\"0\" name=\"stt\" required>\n                                    <small>Lưu ý: Ưu tiên càng cao, chuyên mục càng hiển thị trên cùng</small>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\"\n                                    for=\"example-hf-email\">";
echo __("Tên chuyên mục cha:");
echo "                                    <span class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"text\" class=\"form-control\" name=\"name\"\n                                        placeholder=\"";
echo __("Nhập tên chuyên mục");
echo "\" required>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\" style=\"display: none;\">\n                                <label class=\"col-sm-4 col-form-label\"\n                                    for=\"example-hf-email\">";
echo __("Chuyên mục cha:");
echo "                                    <span class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control mb-2\" name=\"parent_id\" required>\n                                        <option value=\"0\">";
echo __("-- Chuyên mục cha --");
echo "</option>\n                                    </select>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Icon:");
echo " <span\n                                        class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <input type=\"file\" class=\"custom-file-input\" name=\"icon\" required>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\"\n                                    for=\"example-hf-email\">";
echo __("Description SEO:");
echo "</label>\n                                <div class=\"col-sm-12\">\n                                    <textarea class=\"form-control\" rows=\"3\" name=\"description\"></textarea>\n                                </div>\n                            </div>\n                            <div class=\"row mb-4\">\n                                <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Status:");
echo " <span\n                                        class=\"text-danger\">*</span></label>\n                                <div class=\"col-sm-8\">\n                                    <select class=\"form-control\" name=\"status\" required>\n                                        <option value=\"1\">ON</option>\n                                        <option value=\"0\">OFF</option>\n                                    </select>\n                                </div>\n                            </div>\n                            <button type=\"submit\" name=\"submit\" class=\"btn btn-primary\"><i\n                                    class=\"fa fa-fw fa-plus me-1\"></i>\n                                ";
echo __("Submit");
echo "</button>\n                        </form>\n                    </div>\n                </div>\n            </div>\n            ";
$parentCategories = $CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = 0 ORDER BY `stt` DESC");
echo "\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <h4 class=\"card-title\">DANH SÁCH CHUYÊN MỤC SẢN PHẨM</h4>\n                    </div>\n                    <div class=\"card-body\">\n                        <nav class=\"nav nav-pills mb-3 nav-justified d-sm-flex d-block\" role=\"tablist\">\n                            <div class=\"row\">\n                                ";
$i = 0;
echo "                                ";
foreach ($parentCategories as $category) {
    echo "                                ";
    $i++;
    echo "                                <div class=\"col-6 col-md-3 mb-2\">\n                                    <a class=\"nav-link ";
    echo $i == 1 ? "active" : "";
    echo " text-center shadow-sm\"\n                                        data-bs-toggle=\"tab\" role=\"tab\" href=\"#tab-category-";
    echo $category["id"];
    echo "\"\n                                        aria-selected=\"";
    echo $i == 1 ? "true" : "false";
    echo "\">\n                                        <img src=\"";
    echo base_url($category["icon"]);
    echo "\" class=\"me-2\" width=\"25px\">\n                                        ";
    echo $category["name"];
    echo "                                    </a>\n                                </div>\n                                ";
}
echo "                                <div class=\"col-6 col-md-3 mb-2\">\n                                    <a class=\"nav-link text-center shadow-sm border border-dark fs-6\"\n                                        id=\"open-card-hide\" type=\"button\">\n                                        <i class=\"fa-solid fa-plus\"></i> Tạo chuyên mục cha\n                                    </a>\n                                </div>\n                            </div>\n                        </nav>\n\n                        <div class=\"tab-content\">\n                            ";
$i = 0;
echo "                            ";
foreach ($parentCategories as $category) {
    echo "                            ";
    $i++;
    echo "                            <div class=\"tab-pane fade ";
    echo $i == 1 ? "show active" : "";
    echo "\"\n                                id=\"tab-category-";
    echo $category["id"];
    echo "\" role=\"tabpanel\">\n                                <div class=\"mb-3 text-right\">\n                                    <a href=\"";
    echo base_url_admin("category-add&id=" . $category["id"]);
    echo "\"\n                                        class=\"btn btn-sm btn-primary me-2\">\n                                        <i class=\"fa fa-plus\"></i> Tạo chuyên mục con\n                                    </a>\n                                    <a href=\"";
    echo base_url_admin("category-edit&id=" . $category["id"]);
    echo "\"\n                                        class=\"btn btn-sm btn-info me-2\">\n                                        <i class=\"fa fa-pencil-alt\"></i> Chỉnh sửa chuyên mục cha\n                                    </a>\n                                    <a onclick=\"RemoveRow('";
    echo $category["id"];
    echo "')\" class=\"btn btn-sm btn-danger\">\n                                        <i class=\"fas fa-trash\"></i> Xóa chuyên mục cha\n                                    </a>\n                                </div>\n\n                                ";
    $childCategories = $CMSNT->get_list("SELECT * FROM `categories` WHERE `parent_id` = '" . $category["id"] . "'");
    echo "                                <div class=\"table-responsive mb-3\">\n                                    <table class=\"table table-striped table-hover table-bordered text-center\">\n                                        <thead class=\"thead-light\">\n                                            <tr>\n                                                <th width=\"8%\">Ưu tiên</th>\n                                                <th>Tên chuyên mục con</th>\n                                                <th>Liên kết tĩnh</th>\n                                                <th>Thống kê</th>\n                                                <th>Ảnh</th>\n                                                <th>Trạng thái</th>\n                                                <th>Thao tác</th>\n                                            </tr>\n                                        </thead>\n                                        <tbody>\n                                            ";
    foreach ($childCategories as $cate) {
        echo "                                            <tr onchange=\"updateForm(`";
        echo $cate["id"];
        echo "`)\">\n                                                <td>\n                                                    <input id=\"stt";
        echo $cate["id"];
        echo "\"\n                                                        class=\"form-control\" type=\"number\"\n                                                        value=\"";
        echo $cate["stt"];
        echo "\">\n                                                </td>\n                                                <td>";
        echo $cate["name"];
        echo "</td>\n                                                <td>";
        echo $cate["slug"];
        echo "</td>\n                                                <td>\n                                                    <span class=\"badge bg-outline-primary\">\n                                                        Sản phẩm:\n                                                        ";
        echo format_cash($CMSNT->num_rows("SELECT * FROM `products` WHERE `category_id` = '" . $cate["id"] . "'"));
        echo "                                                    </span>\n                                                </td>\n                                                <td><img src=\"";
        echo base_url($cate["icon"]);
        echo "\" width=\"40px\"></td>\n                                                <td>\n                                                    <div class=\"form-check form-switch form-check-lg\">\n                                                        <input class=\"form-check-input\" type=\"checkbox\"\n                                                            id=\"status";
        echo $cate["id"];
        echo "\" value=\"1\"\n                                                            ";
        echo $cate["status"] == 1 ? "checked" : "";
        echo ">\n                                                    </div>\n                                                </td>\n                                                <td>\n                                                    <a href=\"";
        echo base_url_admin("category-edit&id=" . $cate["id"]);
        echo "\"\n                                                        class=\"btn btn-sm btn-info\" data-bs-toggle=\"tooltip\"\n                                                        title=\"Edit\">\n                                                        <i class=\"fa fa-pencil-alt\"></i> Edit\n                                                    </a>\n                                                    <a onclick=\"RemoveRow('";
        echo $cate["id"];
        echo "')\"\n                                                        class=\"btn btn-sm btn-danger\" data-bs-toggle=\"tooltip\"\n                                                        title=\"Delete\">\n                                                        <i class=\"fas fa-trash\"></i> Delete\n                                                    </a>\n                                                </td>\n                                            </tr>\n                                            ";
    }
    echo "                                        </tbody>\n                                    </table>\n                                </div>\n                            </div>\n                            ";
}
echo "                        </div>\n                        <p class=\"text-muted\">Lưu ý: Ưu tiên càng cao, chuyên mục càng hiển thị trên cùng</p>\n                    </div>\n                </div>\n            </div>\n\n\n        </div>\n    </div>\n</div>\n\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nfunction updateForm(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/update.php");
echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'updateTableCategory',\n            id: id,\n            stt: \$('#stt' + id).val(),\n            status: \$('#status' + id + ':checked').val()\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n</script>\n\n\n<script type=\"text/javascript\">\nfunction postRemove(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeCategory',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, 'success');\n            } else {\n                showMessage(result.msg, 'error');\n            }\n        }\n    });\n}\n\nfunction deleteConfirm() {\n    var result = confirm(\"Bạn có thực sự muốn xóa các mục đã chọn?\");\n    if (result) {\n        var checkbox = document.getElementsByName('checkbox');\n        for (var i = 0; i < checkbox.length; i++) {\n            if (checkbox[i].checked === true) {\n                postRemove(checkbox[i].value);\n            }\n        }\n        setTimeout(function() {\n            location.reload();\n        }, 1000);\n    }\n}\n\$(document).ready(function() {\n    \$('#check_all').on('click', function() {\n        if (this.checked) {\n            \$('.checkbox').each(function() {\n                this.checked = true;\n            });\n        } else {\n            \$('.checkbox').each(function() {\n                this.checked = false;\n            });\n        }\n    });\n    \$('.checkbox').on('click', function() {\n        if (\$('.checkbox:checked').length == \$('.checkbox').length) {\n            \$('#check_all').prop('checked', true);\n        } else {\n            \$('#check_all').prop('checked', false);\n        }\n    });\n});\n\nfunction RemoveRow(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"";
echo __("Warning");
echo "\",\n        message: \"Bạn có đồng ý xóa mục ID \" + id + \" này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemove(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n</script>\n<script>\ndocument.addEventListener('DOMContentLoaded', function() {\n    var button = document.getElementById('open-card-hide');\n    var card = document.getElementById('card-hide');\n\n    // Thêm sự kiện click cho nút button\n    button.addEventListener('click', function() {\n        // Kiểm tra nếu card đang hiển thị thì ẩn đi, ngược lại hiển thị\n        if (card.style.display === 'none' || card.style.display === '') {\n            card.style.display = 'block';\n        } else {\n            card.style.display = 'none';\n        }\n    });\n});\n</script>";

?>