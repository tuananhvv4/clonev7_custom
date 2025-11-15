<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Danh sách menu", "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n\n\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_menu")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["AddMenu"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used as this is a demo site.") . "\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_menu")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    if($CMSNT->get_row("SELECT * FROM `menu` WHERE `name` = '" . check_string($_POST["name"]) . "' ")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Tên menu này đã tồn tại trong hệ thống.\")){window.history.back().location.reload();}</script>");
    }
    $isCreate = $CMSNT->insert("menu", ["name" => check_string($_POST["name"]), "slug" => create_slug(check_string($_POST["name"])), "href" => !empty($_POST["href"]) ? check_string($_POST["href"]) : "", "icon" => $_POST["icon"], "position" => !empty($_POST["position"]) ? check_string($_POST["position"]) : 3, "target" => !empty($_POST["target"]) ? check_string($_POST["target"]) : "", "content" => !empty($_POST["content"]) ? $_POST["content"] : "", "status" => check_string($_POST["status"])]);
    if($isCreate) {
        $Mobile_Detect = new Mobile_Detect();
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Add Menu (" . check_string($_POST["name"]) . ")"]);
        $my_text = $CMSNT->site("noti_action");
        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
        $my_text = str_replace("{username}", $getUser["username"], $my_text);
        $my_text = str_replace("{action}", "Add Menu (" . check_string($_POST["name"]) . ")", $my_text);
        $my_text = str_replace("{ip}", myip(), $my_text);
        $my_text = str_replace("{time}", gettime(), $my_text);
        sendMessAdmin($my_text);
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công !\")){location.href = \"" . BASE_URL("admin/menu-list") . "\";}</script>");
    }
    exit("<script type=\"text/javascript\">if(!alert(\"Thêm menu thất bại, vui lòng thử lại!\")){window.history.back().location.reload();}</script>");
}
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
$name = "";
$shortByDate = "";
if(!empty($_GET["name"])) {
    $name = check_string($_GET["name"]);
    $where .= " AND `name` LIKE \"%" . $name . "%\" ";
}
if(!empty($_GET["create_gettime"])) {
    $create_gettime = check_string($_GET["create_gettime"]);
    $createdate = $create_gettime;
    $create_gettime_1 = str_replace("-", "/", $create_gettime);
    $create_gettime_1 = explode(" to ", $create_gettime_1);
    if($create_gettime_1[0] != $create_gettime_1[1]) {
        $create_gettime_1 = [$create_gettime_1[0] . " 00:00:00", $create_gettime_1[1] . " 23:59:59"];
        $where .= " AND `create_gettime` >= '" . $create_gettime_1[0] . "' AND `create_gettime` <= '" . $create_gettime_1[1] . "' ";
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
        $where .= " AND `create_gettime` LIKE '%" . $currentDate . "%' ";
    }
    if($shortByDate == 2) {
        $where .= " AND YEAR(create_gettime) = " . $currentYear . " AND WEEK(create_gettime, 1) = " . $currentWeek . " ";
    }
    if($shortByDate == 3) {
        $where .= " AND MONTH(create_gettime) = '" . $currentMonth . "' AND YEAR(create_gettime) = '" . $currentYear . "' ";
    }
}
$listDatatable = $CMSNT->get_list(" SELECT * FROM `menu` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `menu` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("menu-list&limit=" . $limit . "&shortByDate=" . $shortByDate . "&name=" . $name . "&create_gettime=" . $create_gettime . "&"), $from, $totalDatatable, $limit);
echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><i class=\"fa-solid fa-sitemap\"></i> Quản lý Menu</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Menu</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH MENU\n                        </div>\n                        <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                            class=\"btn btn-sm btn-primary shadow-primary\"><i\n                                class=\"ri-add-line fw-semibold align-middle\"></i> Tạo một menu mới</button>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"menu-list\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $name;
echo "\" name=\"name\"\n                                        placeholder=\"Tên menu\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
echo $create_gettime;
echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\"\n                                        href=\"";
echo base_url_admin("menu-list");
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th>NAME</th>\n                                        <th>HREF</th>\n                                        <th>ICON</th>\n                                        <th>TARGET</th>\n                                        <th>ACTION</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td>";
    echo $row["name"];
    echo "</td>\n                                        <td><a href=\"";
    echo $row["href"];
    echo "\" target=\"_blank\">";
    echo $row["href"];
    echo "</a></td>\n                                        <td><textarea class=\"form-control\" rows=\"1\"\n                                                readonly>";
    echo $row["icon"];
    echo "</textarea></td>\n                                        <td>";
    echo $row["target"];
    echo "</td>\n                                        <td>\n                                            <a type=\"button\" href=\"";
    echo base_url_admin("menu-edit&id=" . $row["id"]);
    echo "\"\n                                                class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Edit");
    echo "\">\n                                                <i class=\"fa fa-pencil-alt\"></i>\n                                            </a>\n                                            <a type=\"button\" onclick=\"remove('";
    echo $row["id"];
    echo "')\"\n                                                class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Delete");
    echo "\">\n                                                <i class=\"fas fa-trash\"></i>\n                                            </a>\n                                        </td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n\n\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <div class=\"modal-dialog modal-dialog-centered modal-xl dialog-scrollable\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\"><i class=\"fa-solid fa-plus\"></i> Tạo một menu mới\n                </h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Tên menu (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"text\" class=\"form-control\" name=\"name\" placeholder=\"Nhập tên menu cần tạo\"\n                                required>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
echo __("Menu cha:");
echo "                            <span class=\"text-danger\">*</span></label>\n                        <div class=\"col-sm-8\">\n                            <select class=\"form-control mb-2\" name=\"parent_id\" required>\n                                <option value=\"0\">Menu cha</option>\n                                ";
foreach ($CMSNT->get_list("SELECT * FROM `menu` WHERE `parent_id` = 0 ") as $option) {
    echo "                                <option value=\"";
    echo $option["id"];
    echo "\" ";
    echo $id == $option["id"] ? "selected" : "";
    echo ">\n                                    ";
    echo $option["name"];
    echo "</option>\n                                ";
    foreach ($CMSNT->get_list("SELECT * FROM `menu` WHERE `parent_id` = '" . $option["id"] . "' ") as $option1) {
        echo "                                <option disabled value=\"";
        echo $option1["id"];
        echo "\">__";
        echo $option1["name"];
        echo "</option>\n                                ";
    }
    echo "                                ";
}
echo "                            </select>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Liên kết</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"text\" class=\"form-control\"\n                                placeholder=\"Nhập địa chỉ liên kết cần tới khi click vào menu này\" name=\"href\">\n                            <small>Chỉ áp dụng khi nội dung hiển thị trống</small>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-12 col-form-label\" for=\"example-hf-email\">Nội dung hiển thị (nếu\n                            có)</label>\n                        <div class=\"col-sm-12\">\n                            <textarea id=\"content\" name=\"content\"\n                                placeholder=\"Để trống nếu muốn sử dụng liên kết\"></textarea>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Vị trí hiển thị</label>\n                        <div class=\"col-sm-8\">\n                            <select class=\"form-control\" name=\"position\" required>\n                                <option value=\"1\">Trong menu SỐ DƯ</option>\n                                <option value=\"2\">Trong menu NẠP TIỀN</option>\n                                <option value=\"3\">Trong menu KHÁC</option>\n                            </select>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Icon menu (<span\n                                class=\"text-danger\">*</span>)</label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"text\" class=\"form-control\" placeholder='Ví dụ: <i class=\"fas fa-home\"></i>'\n                                name=\"icon\" required>\n                            <small>Tìm thêm icon tại <a target=\"_blank\"\n                                    href=\"https://fontawesome.com/v5.15/icons?d=gallery&p=2\">đây</a></small>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Trạng thái</label>\n                        <div class=\"col-sm-8\">\n                            <select class=\"form-control\" name=\"status\" required>\n                                <option value=\"1\">Hiển thị</option>\n                                <option value=\"0\">Ẩn</option>\n                            </select>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <div class=\"col-sm-4\"></div>\n                        <div class=\"col-sm-8\">\n                            <div class=\"form-check form-check-md d-flex align-items-center mb-2\">\n                                <input class=\"form-check-input\" type=\"checkbox\" name=\"target\" value=\"_blank\"\n                                    id=\"customCheckbox2\" checked>\n                                <label class=\"form-check-label\" for=\"customCheckbox2\">\n                                    Mở tab mới khi\n                                    click\n                                </label>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light \" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddMenu\" class=\"btn btn-primary shadow-primary btn-wave\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>\n                        ";
echo __("Submit");
echo "</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n\n\n\n<script>\nfunction postRemove(id) {\n    \$.ajax({\n        url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeMenu',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        }\n    });\n}\n\nfunction remove(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa Menu\",\n        message: \"Bạn có chắc chắn muốn xóa menu này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemove(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n</script>\n\n\n<script>\nCKEDITOR.replace(\"content\");\n</script>";

?>