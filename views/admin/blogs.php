<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Danh sách bài viết") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n\n<!-- Page JS Plugins CSS -->\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css\">\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css\">\n<link rel=\"stylesheet\" href=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css\">\n";
$body["footer"] = "\n\n\n<!-- Page JS Plugins -->\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables/jquery.dataTables.min.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-responsive/js/dataTables.responsive.min.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-buttons/dataTables.buttons.min.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-buttons-jszip/jszip.min.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-buttons-pdfmake/pdfmake.min.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-buttons-pdfmake/vfs_fonts.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-buttons/buttons.print.min.js\"></script>\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/plugins/datatables-buttons/buttons.html5.min.js\"></script>\n<!-- Page JS Code -->\n<script src=\"" . BASE_URL("public/theme/") . "assets/js/pages/be_tables_datatables.min.js\"></script>\n\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "view_blog")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
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
$category = "";
$create_gettime = "";
$title = "";
$shortByDate = "";
if(!empty($_GET["title"])) {
    $title = check_string($_GET["title"]);
    $where .= " AND `title` LIKE \"%" . $title . "%\" ";
}
if(!empty($_GET["category"])) {
    $category = check_string($_GET["category"]);
    $where .= " AND `category_id` = " . $category . " ";
}
if(!empty($_GET["create_gettime"])) {
    $create_date = check_string($_GET["create_gettime"]);
    $create_gettime = $create_date;
    $create_date_1 = str_replace("-", "/", $create_date);
    $create_date_1 = explode(" to ", $create_date_1);
    if($create_date_1[0] != $create_date_1[1]) {
        $create_date_1 = [$create_date_1[0] . " 00:00:00", $create_date_1[1] . " 23:59:59"];
        $where .= " AND `create_gettime` >= '" . $create_date_1[0] . "' AND `create_gettime` <= '" . $create_date_1[1] . "' ";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `posts` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `posts` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("blogs&limit=" . $limit . "&shortByDate=" . $shortByDate . "&title=" . $title . "&category=" . $category . "&create_gettime=" . $create_gettime . "&"), $from, $totalDatatable, $limit);
echo "\n\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Blogs</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Blogs</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH BÀI VIẾT\n                        </div>\n                        <div class=\"d-flex\">\n                            <a type=\"button\" href=\"";
echo base_url_admin("blog-add");
echo "\"\n                                class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                    class=\"ri-add-line fw-semibold align-middle\"></i> Viết bài mới</a>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"blogs\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
echo $title;
echo "\" name=\"title\"\n                                        placeholder=\"";
echo __("Title");
echo "\">\n                                </div>\n                                <div class=\"col-md-3 col-6\">\n                                    <select class=\"form-control form-control-sm mb-1\" name=\"category\">\n                                        <option value=\"\">-- Chuyên mục --</option>\n                                        ";
foreach ($CMSNT->get_list(" SELECT * FROM `post_category` ") as $listcategory) {
    echo "                                        <option ";
    echo $listcategory["id"] == $category ? "selected" : "";
    echo "                                            value=\"";
    echo $listcategory["id"];
    echo "\">";
    echo $listcategory["name"];
    echo "</option>\n                                        ";
}
echo "                                    </select>\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
echo $create_gettime;
echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
echo __("Search");
echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\" href=\"";
echo base_url_admin("blogs");
echo "\"><i\n                                            class=\"fa fa-trash\"></i>\n                                        ";
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
echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th>";
echo __("Tiêu đề bài viết");
echo "</th>\n                                        <th>";
echo __("Ảnh");
echo "</th>\n                                        <th>";
echo __("Chuyên mục");
echo "</th>\n                                        <th class=\"text-center\">";
echo __("Trạng thái");
echo "</th>\n                                        <th class=\"text-center\">Lượt xem</th>\n                                        <th>";
echo __("Thao tác");
echo "</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
foreach ($listDatatable as $row) {
    echo "                                    <tr>\n                                        <td>";
    echo $row["title"];
    echo "</td>\n                                        <td>";
    if(!empty($row["image"])) {
        echo "<img src=\"";
        echo base_url($row["image"]);
        echo "\"\n                                                width=\"100px\">";
    }
    echo "</td>\n                                        <td><a class=\"text-primary\" href=\"";
    echo base_url_admin("blog-category-edit&id=" . $row["category_id"]);
    echo "\"\n                                                target=\"_blank\"><i class=\"fa fa-pencil-alt\"></i>\n                                                ";
    echo getRowRealtime("post_category", $row["category_id"], "name");
    echo "</a>\n                                        </td>\n                                        <td class=\"text-center\">";
    echo display_status_product($row["status"]);
    echo "</td>\n                                        <td class=\"text-center\">";
    echo $row["view"];
    echo " lượt xem</td>\n                                        <td>\n                                            <a type=\"button\" target=\"_blank\" href=\"";
    echo base_url("blog/" . $row["slug"]);
    echo "\"\n                                                class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Xem");
    echo "\">\n                                                <i class=\"fa fa-eye\"></i>\n                                            </a>\n                                            <a type=\"button\" href=\"";
    echo base_url_admin("blog-edit&id=" . $row["id"]);
    echo "\"\n                                                class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Chỉnh sửa");
    echo "\">\n                                                <i class=\"fa fa-pencil-alt\"></i>\n                                            </a>\n                                            <a type=\"button\" onclick=\"RemoveRow('";
    echo $row["id"];
    echo "')\"\n                                                class=\"btn btn-sm btn-light\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
    echo __("Xoá");
    echo "\">\n                                                <i class=\"fas fa-trash\"></i>\n                                            </a>\n                                        </td>\n                                    </tr>\n                                    ";
}
echo "                                </tbody>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
echo $limit;
echo " of ";
echo format_cash($totalDatatable);
echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
echo $limit < $totalDatatable ? $urlDatatable : "";
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\nCKEDITOR.replace(\"content\");\n\n\nfunction RemoveRow(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"";
echo __("Xác nhận xoá item");
echo "\",\n        message: \"";
echo __("Bạn có chắc chắn muốn xóa item này không ?");
echo "\",\n        confirmText: \"";
echo __("Đồng Ý");
echo "\",\n        cancelText: \"";
echo __("Huỷ");
echo "\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
echo BASE_URL("ajaxs/admin/remove.php");
echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    id: id,\n                    action: 'removePost'\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n</script>";

?>