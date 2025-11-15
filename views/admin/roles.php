<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Roles", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css\">\n";
$body["footer"] = "\n<script src=\"https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js\"></script>\n<script src=\"https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js\"></script>\n<script src=\"https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js\"></script>\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_role")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["addRole"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_role")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    if(empty($_POST["name"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập tên vai trò\")){window.history.back().location.reload();}</script>");
    }
    $name = check_string($_POST["name"]);
    if(empty($_POST["role"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng chọn quyền cho role\")){window.history.back().location.reload();}</script>");
    }
    $role = json_encode($_POST["role"]);
    $isInsert = $CMSNT->insert("admin_role", ["name" => $name, "role" => $role, "create_gettime" => gettime(), "update_gettime" => gettime()]);
    if($isInsert) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add Role (" . $name . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Add Role (" . $name . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công!\")){location.href = \"" . base_url_admin("roles") . "\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại!\")){window.history.back().location.reload();}</script>");
}
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-shield-halved\"></i> Admin Role</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Admin Role</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH ROLE\n                        </div>\n                        <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                            class=\"btn btn-sm btn-primary shadow-primary\"><i class=\"fa-solid fa-plus\"></i> Tạo một role mới</button>\n                    </div>\n                    <div class=\"card-body\">\n                        <table id=\"datatable-basic\" class=\"table text-nowrap table-striped table-hover table-bordered\"\n                            style=\"width:100%\">\n                            <thead>\n                                <tr>\n                                    <th class=\"text-center\">Thao tác</th>\n                                    <th>Vai trò</th>\n                                    <th>Quyền hạn</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
foreach ($CMSNT->get_list("SELECT * FROM `admin_role` ORDER BY id DESC ") as $row) {
    echo "                                <tr>\n                                    <td class=\"text-center\">\n                                        <a type=\"button\" href=\"";
    echo base_url_admin("role-edit&id=" . $row["id"]);
    echo "\"\n                                            class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                            title=\"";
    echo __("Edit");
    echo "\">\n                                            <i class=\"fa fa-pencil-alt\"></i>\n                                        </a>\n                                        <a type=\"button\" onclick=\"remove('";
    echo $row["id"];
    echo "')\"\n                                            class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                            title=\"";
    echo __("Delete");
    echo "\">\n                                            <i class=\"fas fa-trash\"></i>\n                                        </a>\n                                    </td>\n                                    <td>\n                                        ";
    echo $row["name"];
    echo "                                    </td>\n                                    <td>\n                                        ";
    foreach (json_decode($row["role"]) as $rl) {
        echo "                                        <span class=\"badge bg-outline-primary\">";
        echo $rl;
        echo "</span>\n                                        ";
    }
    echo "                                    </td>\n                                </tr>\n                                ";
}
echo "                            </tbody>\n                        </table>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered modal-lg dialog-scrollable\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\"><i class='bx bx-plus'></i> Tạo một role mới</h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Tên vai trò (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"text\" class=\"form-control\" name=\"name\" placeholder=\"VD: Super Admin\" required>\n                        </div>\n                    </div>\n                    <div class=\"form-check form-check-md d-flex align-items-center mb-2\">\n                        <input class=\"form-check-input\" type=\"checkbox\" value=\"\" id=\"selectAll\"\n                            onclick=\"toggleAllCheckboxes()\">\n                        <label class=\"form-check-label\" for=\"selectAll\">\n                            Chọn tất cả các quyền\n                        </label>\n                    </div>\n                    <div class=\"row mb-4\">\n                        ";
foreach ($admin_roles as $category => $roles) {
    echo "                        <hr>\n                        <div class=\"col-4\">\n                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                <input class=\"form-check-input\" type=\"checkbox\" value=\"\"\n                                    id=\"";
    echo strtolower(str_replace(" ", "_", $category));
    echo "\"\n                                    onclick=\"toggleCategory('";
    echo strtolower(str_replace(" ", "_", $category));
    echo "')\">\n                                <label class=\"form-check-label\"\n                                    for=\"";
    echo strtolower(str_replace(" ", "_", $category));
    echo "\">\n                                    ";
    echo $category;
    echo "                                </label>\n                            </div>\n                        </div>\n                        <div class=\"col-8\">\n                            ";
    foreach ($roles as $key => $label) {
        echo "                            <div class=\"form-check\">\n                                <input class=\"form-check-input\" type=\"checkbox\" value=\"";
        echo $key;
        echo "\" name=\"role[]\"\n                                    id=\"";
        echo $key;
        echo "\"\n                                    data-category=\"";
        echo strtolower(str_replace(" ", "_", $category));
        echo "\">\n                                <label class=\"form-check-label\" for=\"";
        echo $key;
        echo "\">\n                                    ";
        echo $label;
        echo " <span class=\"badge bg-primary-transparent\">";
        echo $key;
        echo "</span>\n                                </label>\n                            </div>\n                            ";
    }
    echo "                        </div>\n                        ";
}
echo "                    </div>\n\n                    <script>\n                    function toggleAllCheckboxes() {\n                        var checkboxes = document.querySelectorAll('[name=\"role[]\"]');\n                        var selectAllCheckbox = document.getElementById('selectAll');\n\n                        checkboxes.forEach(function(checkbox) {\n                            checkbox.checked = selectAllCheckbox.checked;\n                        });\n                    }\n\n                    function toggleCategory(categoryId) {\n                        var checkboxes = document.querySelectorAll('[data-category=\"' + categoryId + '\"]');\n                        var categoryCheckbox = document.getElementById(categoryId);\n                        var selectAllCheckbox = document.getElementById('selectAll');\n\n                        checkboxes.forEach(function(checkbox) {\n                            checkbox.checked = categoryCheckbox.checked;\n                        });\n\n                        // Kiểm tra xem tất cả ô checkbox trong danh mục đã được chọn hay không\n                        selectAllCheckbox.checked = checkboxes.length === document.querySelectorAll('[data-category=\"' +\n                            categoryId + '\"]:checked').length;\n                    }\n                    </script>\n\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light \" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"addRole\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>Submit</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n\n\n\n<script>\n\$('#datatable-basic').DataTable({\n    language: {\n        searchPlaceholder: 'Search...',\n        sSearch: '',\n    },\n    \"pageLength\": 10,\n    scrollX: true\n});\n</script>\n\n<script>\nfunction postRemove(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeRole',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        }\n    });\n}\n\nfunction remove(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa Role\",\n        message: \"Bạn có chắc chắn muốn xóa Role này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemove(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n</script>";

?>