<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => "Kho hàng sản phẩm | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n";
$body["footer"] = "\n\n";
require_once __DIR__ . "/../../models/is_admin.php";
if(isset($_GET["code"])) {
    $code = check_string($_GET["code"]);
} else {
    redirect(base_url_admin("products"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
if(!checkPermission($getUser["admin"], "edit_stock_product")) {
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
$where = " `product_code` = '" . $code . "' ";
$create_gettime = "";
$uid = "";
$shortByDate = "";
$user_id = "";
$username = "";
$account = "";
if(!empty($_GET["account"])) {
    $account = check_string($_GET["account"]);
    $where .= " AND `account` LIKE \"%" . $account . "%\" ";
}
if(!empty($_GET["uid"])) {
    $uid = check_string($_GET["uid"]);
    $where .= " AND `uid` LIKE \"%" . $uid . "%\" ";
}
if(!empty($_GET["username"])) {
    $username = check_string($_GET["username"]);
    if($idUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `username` = '" . $username . "' ")) {
        $where .= " AND `user_id` =  \"" . $idUser["id"] . "\" ";
    } else {
        $where .= " AND `user_id` =  \"\" ";
    }
}
if(!empty($_GET["user_id"])) {
    $user_id = check_string($_GET["user_id"]);
    $where .= " AND `user_id` = \"" . $user_id . "\" ";
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
$listDatatable = $CMSNT->get_list(" SELECT * FROM `product_stock` WHERE " . $where . " ORDER BY `id` DESC LIMIT " . $from . "," . $limit . " ");
$totalDatatable = $CMSNT->num_rows(" SELECT * FROM `product_stock` WHERE " . $where . " ORDER BY id DESC ");
$urlDatatable = pagination(base_url_admin("product-stock&limit=" . $limit . "&shortByDate=" . $shortByDate . "&code=" . $code . "&uid=" . $uid . "&create_gettime=" . $create_gettime . "&user_id=" . $user_id . "&username=" . $username . "&account=" . $account . "&"), $from, $totalDatatable, $limit);
if(isset($_POST["RemoveAccounts"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    $value_remove = 0;
    if(empty($_POST["accounts"])) {
        exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập tài khoản cần thêm.\")){window.history.back().location.reload();}</script>");
    }
    $accounts = check_string($_POST["accounts"]);
    $list = explode(PHP_EOL, $accounts);
    foreach ($list as $account) {
        list($uid) = explode("|", $account);
        $isRemove = $CMSNT->remove("product_stock", " `uid` = '" . $uid . "' ");
        if($isRemove) {
            $value_remove++;
        }
    }
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Xóa tài khoản khỏi kho hàng " . $code]);
    $my_text = $CMSNT->site("noti_action");
    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
    $my_text = str_replace("{username}", $getUser["username"], $my_text);
    $my_text = str_replace("{action}", "Xóa tài khoản khỏi kho hàng " . $code, $my_text);
    $my_text = str_replace("{ip}", myip(), $my_text);
    $my_text = str_replace("{time}", gettime(), $my_text);
    sendMessAdmin($my_text);
    exit("<script type=\"text/javascript\">if(!alert(\"Xóa thành công [" . $value_remove . "] tài khoản.\")){window.history.back().location.reload();}</script>");
} elseif(isset($_POST["AddAccounts"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
    }
    $value_add = 0;
    $value_update = 0;
    $list = [];
    if($_POST["type"] == "an") {
        if(empty($_POST["accounts"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập tài khoản cần thêm.\")){window.history.back().location.reload();}</script>");
        }
        $accounts = check_string($_POST["accounts"]);
        array_push($list, $accounts);
    }
    if($_POST["type"] == "multi") {
        if(empty($_POST["accounts"])) {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng nhập tài khoản cần thêm.\")){window.history.back().location.reload();}</script>");
        }
        $accounts = check_string($_POST["accounts"]);
        $list = explode(PHP_EOL, $accounts);
        $list = array_filter($list, function ($line) {
            return trim($line) !== "";
        });
    }
    if($_POST["type"] == "txt") {
        $file_name = $_FILES["files_txt"]["name"];
        $file_tmp = $_FILES["files_txt"]["tmp_name"];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        if(strtolower($file_extension) == "txt") {
            $fileContent = file_get_contents($file_tmp);
            $list = explode(PHP_EOL, check_string($fileContent));
        } else {
            exit("<script type=\"text/javascript\">if(!alert(\"Vui lòng chọn file có định dạng .txt\")){window.history.back().location.reload();}</script>");
        }
    }
    foreach ($list as $account) {
        list($uid) = explode("|", $account);
        if(isset($_POST["loc_trung_uid"]) && $_POST["loc_trung_uid"] == 1) {
            if($CMSNT->get_row(" SELECT * FROM `product_stock` WHERE `uid` = '" . $uid . "' ")) {
                $isUpdate = $CMSNT->update("product_stock", ["product_code" => $code, "seller" => $getUser["id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()], " `uid` = '" . $uid . "' ");
                if($isUpdate) {
                    $value_update++;
                }
            } else {
                $isAdd = $CMSNT->insert("product_stock", ["product_code" => $code, "seller" => $getUser["id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                if($isAdd) {
                    $value_add++;
                }
            }
        } else {
            $isAdd = $CMSNT->insert("product_stock", ["product_code" => $code, "seller" => $getUser["id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
            if($isAdd) {
                $value_add++;
            }
        }
    }
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "Import tài khoản vào kho hàng " . $code]);
    $my_text = $CMSNT->site("noti_action");
    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
    $my_text = str_replace("{username}", $getUser["username"], $my_text);
    $my_text = str_replace("{action}", "Import tài khoản vào kho hàng " . $code, $my_text);
    $my_text = str_replace("{ip}", myip(), $my_text);
    $my_text = str_replace("{time}", gettime(), $my_text);
    sendMessAdmin($my_text);
    exit("<script type=\"text/javascript\">if(!alert(\"Hệ thống đã Thêm [" . $value_add . "] tài khoản và Cập nhật [" . $value_update . "] tài khoản\")){window.history.back().location.reload();}</script>");
} else {
    echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\"><a type=\"button\"\n                    class=\"btn btn-dark btn-raised-shadow btn-wave btn-sm me-1\"\n                    href=\"";
    echo base_url_admin("products");
    echo "\"><i class=\"fa-solid fa-arrow-left\"></i></a> Quản lý kho hàng\n                \"<b style=\"color:red;\">";
    echo $code;
    echo "</b>\"</h1>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-6\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            SẢN PHẨM ĐANG SỬ DỤNG KHO HÀNG NÀY\n                        </div>\n                    </div>\n                    <div class=\"card-body p-2\">\n                        <div class=\"table-responsive mb-2\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th class=\"text-center\">";
    echo __("Thao tác");
    echo "</th>\n                                        <th class=\"text-center\">";
    echo __("Trạng thái");
    echo "</th>\n                                        <th>";
    echo __("Sản phẩm");
    echo "</th>\n                                        <th class=\"text-center\">";
    echo __("Chuyên mục");
    echo "</th>\n                                        <th class=\"text-center\">";
    echo __("Giá bán");
    echo "</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
    foreach ($CMSNT->get_list(" SELECT * FROM `products` WHERE `code` = '" . $code . "' ") as $product) {
        echo "                                    <tr onchange=\"updateFormProduct(`";
        echo $product["id"];
        echo "`)\">\n                                        <td>\n                                            <a type=\"button\"\n                                                href=\"";
        echo base_url_admin("product-edit&id=" . $product["id"]);
        echo "\"\n                                                class=\"btn btn-sm btn-info\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
        echo __("Chỉnh sửa");
        echo "\">\n                                                <i class=\"fa fa-pencil-alt\"></i>\n                                            </a>\n                                            <a type=\"button\" onclick=\"removeProduct('";
        echo $product["id"];
        echo "')\"\n                                                class=\"btn btn-sm btn-danger\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
        echo __("Xóa");
        echo "\">\n                                                <i class=\"fas fa-trash\"></i>\n                                            </a>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-switch form-check-lg\">\n                                                <input class=\"form-check-input\" type=\"checkbox\"\n                                                    id=\"status";
        echo $product["id"];
        echo "\" value=\"1\"\n                                                    ";
        echo $product["status"] == 1 ? "checked=\"\"" : "";
        echo ">\n                                            </div>\n                                        </td>\n                                        <td>\n                                            ";
        echo $product["name"];
        echo "                                        </td>\n                                        <td class=\"text-center\"><span\n                                                class=\"badge bg-primary\">";
        echo getRowRealtime("categories", $product["category_id"], "name");
        echo "</span>\n                                        </td>\n                                        <td class=\"text-right\"><span\n                                                class=\"badge bg-danger\">";
        echo format_currency($product["price"]);
        echo "</span>\n                                        </td>\n                                    </tr>\n                                    ";
    }
    echo "                                </tbody>\n                            </table>\n                        </div>\n                        <div class=\"text-right\">\n                            <a type=\"button\" target=\"_blank\" href=\"";
    echo base_url_admin("product-add&code=" . $code);
    echo "\"\n                                class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                    class=\"ri-add-line fw-semibold align-middle\"></i> Tạo sản phẩm sử dụng kho hàng\n                                ";
    echo $code;
    echo "</a>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xl-6\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            NHẬP TÀI KHOẢN VÀO KHO HÀNG\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <div class=\"row\">\n                            <div class=\"col-xl-6 d-grid gap-2\">\n                                <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#nhap_tung_tai_khoan\"\n                                    class=\"btn btn-outline-danger btn-w-lg btn-wave mb-2\"><i\n                                        class=\"fa-solid fa-file\"></i> Nhập từng tài\n                                    khoản</button>\n                            </div>\n                            <div class=\"col-xl-6 d-grid gap-2\">\n                                <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#nhap_nhieu_tai_khoan\"\n                                    class=\"btn btn-outline-primary btn-w-lg btn-wave mb-2\"><i\n                                        class=\"fa-solid fa-folder\"></i> Nhập nhiều tài\n                                    khoản</button>\n                            </div>\n                            <div class=\"col-xl-6 d-grid gap-2\">\n                                <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#nhap_tai_khoan_bang_txt\"\n                                    class=\"btn btn-outline-info btn-w-lg btn-wave mb-2\"><i class='bx bxs-file-txt'\n                                        style=\"font-size: 16px;\"></i> Nhập bằng tệp\n                                    .txt</button>\n                            </div>\n                            <!-- <div class=\"col-xl-6 d-grid gap-2\">\n                                <button type=\"button\" class=\"btn btn-outline-success btn-w-lg btn-wave mb-2\"><i\n                                        class=\"fa-solid fa-file-csv\"></i> Nhập bằng tệp\n                                    .csv</button>\n                            </div> -->\n                            <div class=\"col-xl-6 d-grid gap-2\">\n                                <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#nhap_bang_api\"\n                                    class=\"btn btn-outline-dark btn-w-lg btn-wave mb-2\"><i class=\"fa-solid fa-code\"></i>\n                                    Nhập bằng API</button>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            XÓA TÀI KHOẢN KHỎI KHO HÀNG\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <div class=\"row\">\n                            <div class=\"col-xl-6 d-grid gap-2\">\n                                <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#xoa_nhieu_tai_khoan\"\n                                    class=\"btn btn-danger btn-w-lg btn-wave mb-2\"><i class=\"fa-solid fa-trash-can\"></i>\n                                    Xóa nhiều tài khoản</button>\n                            </div>\n                            <div class=\"col-xl-6 d-grid gap-2\">\n                                <button type=\"button\" data-bs-toggle=\"modal\" data-bs-target=\"#xoa_toan_bo_tai_khoan\"\n                                    class=\"btn btn-danger-gradient btn-wave mb-2\"><i class=\"fa-solid fa-trash\"></i>\n                                    Xóa toàn bộ tài khoản đang bán</button>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            TÀI KHOẢN <strong style=\"color:green;\">LIVE</strong> ĐANG BÁN\n                        </div>\n                        <div class=\"btn-list\">\n\n                            <button type=\"button\" onclick=\"viewListLIVE(`";
    echo $code;
    echo "`)\" id=\"btn_viewListLIVE\"\n                                class=\"btn btn-success btn-sm my-1 me-2\"><i class=\"fa-solid fa-copy\"></i> TÀI KHOẢN LIVE\n                                <span\n                                    class=\"badge ms-2 bg-dark text-white\">";
    echo format_cash($totalDatatable);
    echo "</span></button>\n                            <button type=\"button\" onclick=\"viewListDIE(`";
    echo $code;
    echo "`)\" id=\"btn_viewListDIE\"\n                                class=\"btn btn-danger btn-sm my-1 me-2\"><i class=\"fa-solid fa-copy\"></i> TÀI KHOẢN DIE\n                                <span\n                                    class=\"badge ms-2 bg-dark text-white\">";
    echo format_cash($CMSNT->get_row(" SELECT COUNT(id) FROM `product_die` WHERE `product_code` = '" . $code . "' ")["COUNT(id)"]);
    echo "</span>\n\n                            </button>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <form action=\"\" class=\"align-items-center mb-3\" name=\"formSearch\" method=\"GET\">\n                            <div class=\"row row-cols-lg-auto g-3 mb-3\">\n                                <input type=\"hidden\" name=\"module\" value=\"admin\">\n                                <input type=\"hidden\" name=\"action\" value=\"product-stock\">\n                                <input type=\"hidden\" name=\"code\" value=\"";
    echo $code;
    echo "\">\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
    echo $uid;
    echo "\" name=\"uid\"\n                                        placeholder=\"UID\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
    echo $account;
    echo "\" name=\"account\"\n                                        placeholder=\"Tài khoản\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
    echo $user_id;
    echo "\" name=\"user_id\"\n                                        placeholder=\"ID Seller\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input class=\"form-control form-control-sm\" value=\"";
    echo $username;
    echo "\" name=\"username\"\n                                        placeholder=\"Username Seller\">\n                                </div>\n                                <div class=\"col-lg col-md-4 col-6\">\n                                    <input type=\"text\" name=\"create_gettime\" class=\"form-control form-control-sm\"\n                                        id=\"daterange\" value=\"";
    echo $create_gettime;
    echo "\" placeholder=\"Chọn thời gian\">\n                                </div>\n                                <div class=\"col-12\">\n                                    <button class=\"btn btn-hero btn-sm btn-primary\"><i class=\"fa fa-search\"></i>\n                                        ";
    echo __("Search");
    echo "                                    </button>\n                                    <a class=\"btn btn-hero btn-sm btn-danger\"\n                                        href=\"";
    echo base_url_admin("product-stock&code=" . $code);
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
    echo " value=\"1000\">1.000</option>\n                                        <option ";
    echo $limit == 2000 ? "selected" : "";
    echo " value=\"2000\">2.000</option>\n                                        <option ";
    echo $limit == 5000 ? "selected" : "";
    echo " value=\"5000\">5.000</option>\n                                        <option ";
    echo $limit == 10000 ? "selected" : "";
    echo " value=\"10000\">10.000</option>\n                                        <option ";
    echo $limit == 20000 ? "selected" : "";
    echo " value=\"20000\">20.000</option>\n                                        <option ";
    echo $limit == 50000 ? "selected" : "";
    echo " value=\"50000\">50.000</option>\n                                        <option ";
    echo $limit == 100000 ? "selected" : "";
    echo " value=\"100000\">100.000</option>\n                                    </select>\n                                </div>\n                                <div class=\"filter-short\">\n                                    <label class=\"filter-label\">";
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
    echo "                                        </option>\n                                    </select>\n                                </div>\n                            </div>\n                        </form>\n                        <div class=\"table-responsive table-wrapper mb-3\">\n                            <table class=\"table text-nowrap table-striped table-hover table-bordered\">\n                                <thead>\n                                    <tr>\n                                        <th class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input\" name=\"check_all\"\n                                                    id=\"check_all_checkbox_product_stock\" value=\"option1\">\n                                            </div>\n                                        </th>\n                                        <th class=\"text-center\">UID</th>\n                                        <th class=\"text-center\">Tài khoản</th>\n                                        <th class=\"text-center\">Seller</th>\n                                        <th class=\"text-center\">Thời gian</th>\n                                        <th class=\"text-center\">Check live gần nhất</th>\n                                        <th class=\"text-center\">Type</th>\n                                        <th class=\"text-center\">Thao tác</th>\n                                    </tr>\n                                </thead>\n                                <tbody>\n                                    ";
    foreach ($listDatatable as $row) {
        echo "                                    <tr>\n                                        <td class=\"text-center\">\n                                            <div class=\"form-check form-check-md d-flex align-items-center\">\n                                                <input type=\"checkbox\" class=\"form-check-input checkbox_product_stock\"\n                                                    data-id=\"";
        echo $row["id"];
        echo "\" data-checkbox=\"";
        echo $row["account"];
        echo "\"\n                                                    name=\"checkbox_product_stock\" value=\"";
        echo $row["id"];
        echo "\" />\n                                            </div>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <strong>";
        echo $row["uid"];
        echo "</strong>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <textarea rows=\"1\" class=\"form-control\">";
        echo $row["account"];
        echo "</textarea>\n                                        </td>\n                                        <td class=\"text-center\"><a class=\"text-primary\"\n                                                href=\"";
        echo base_url_admin("user-edit&id=" . $row["seller"]);
        echo "\">";
        echo getRowRealtime("users", $row["seller"], "username");
        echo "                                                [ID ";
        echo $row["seller"];
        echo "]</a>\n                                        </td>\n                                        <td class=\"text-center\">\n                                            <small data-toggle=\"tooltip\" data-placement=\"bottom\"\n                                                title=\"";
        echo timeAgo(strtotime($row["create_gettime"]));
        echo "\">";
        echo $row["create_gettime"];
        echo "</small>\n                                        </td>\n                                        <td class=\"text-center\"><span\n                                                class=\"badge rounded-pill bg-dark text-white\" data-toggle=\"tooltip\" data-placement=\"bottom\"\n                                                title=\"";
        echo date("H:i:s d-m-Y", $row["time_check_live"]);
        echo "\">";
        echo timeAgo($row["time_check_live"]);
        echo "</span>\n                                        </td> \n                                        <td class=\"text-center\"><b>";
        echo $row["type"];
        echo "</b></td>\n                                        <td class=\"text-center\">\n                                            <a type=\"button\" onclick=\"removeAccount('";
        echo $row["id"];
        echo "')\"\n                                                class=\"btn btn-sm btn-danger\" data-bs-toggle=\"tooltip\"\n                                                title=\"";
        echo __("Xóa");
        echo "\">\n                                                <i class=\"fas fa-trash\"></i> Delete\n                                            </a>\n                                        </td>\n                                    </tr>\n                                    ";
    }
    echo "                                </tbody>\n                                <tfoot>\n                                    <td colspan=\"9\">\n                                        <div class=\"btn-list\">\n                                            <button type=\"button\" onclick=\"exportDataTXT()\" id=\"exportDataTXT\"\n                                                class=\"btn btn-outline-primary shadow-primary btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-file-export\"></i> XUẤT TỆP .TXT</button>\n                                            <button type=\"button\" onclick=\"exportDataClipboard()\"\n                                                id=\"exportDataClipboard\"\n                                                class=\"btn btn-outline-success shadow-success btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-copy\"></i> COPY</button>\n                                            <button type=\"button\" onclick=\"exportUIDClipboard()\" id=\"exportUIDClipboard\"\n                                                class=\"btn btn-outline-info shadow-info btn-wave btn-sm\"><i\n                                                    class=\"fa-regular fa-copy\"></i> COPY UID</button>\n                                            <button type=\"button\" onclick=\"confirmDeleteAccount()\"\n                                                id=\"confirmDeleteAccount\"\n                                                class=\"btn btn-outline-danger shadow-danger btn-wave btn-sm\"><i\n                                                    class=\"fa-solid fa-trash\"></i> DELETE</button>\n                                        </div>\n                                    </td>\n                                </tfoot>\n                            </table>\n                        </div>\n                        <div class=\"row\">\n                            <div class=\"col-sm-12 col-md-5\">\n                                <p class=\"dataTables_info\">Showing ";
    echo format_cash($limit);
    echo " of\n                                    ";
    echo format_cash($totalDatatable);
    echo "                                    Results</p>\n                            </div>\n                            <div class=\"col-sm-12 col-md-7 mb-3\">\n                                ";
    echo $limit < $totalDatatable ? $urlDatatable : "";
    echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</div>\n\n\n";
    require_once __DIR__ . "/footer.php";
    echo "\n<div class=\"modal fade\" id=\"nhap_tung_tai_khoan\" tabindex=\"-1\" aria-labelledby=\"h6_nhap_tung_tai_khoan\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <form action=\"\" method=\"POST\">\n                <div class=\"modal-header\">\n                    <h6 class=\"modal-title\" id=\"h6_nhap_tung_tai_khoan\">NHẬP TỪNG TÀI KHOẢN VÀO KHO HÀNG\n                    </h6>\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n                </div>\n                <div class=\"modal-body\">\n                    <div class=\"form-group mb-3\">\n                        <label for=\"text-area\" class=\"form-label\">Tài khoản cần thêm:</label>\n                        <textarea class=\"form-control\" name=\"accounts\" placeholder=\"Định dạng UID|PASS|...\" rows=\"2\"\n                            required></textarea>\n                        <input type=\"hidden\" name=\"type\" value=\"an\" readonly>\n                    </div>\n                    <div class=\"form-check form-check-md d-flex align-items-center\">\n                        <input class=\"form-check-input\" type=\"checkbox\" value=\"1\" id=\"dsg9898w\" name=\"loc_trung_uid\">\n                        <label class=\"form-check-label\" for=\"dsg9898w\">\n                            Lọc trùng UID\n                        </label>\n                    </div>\n                    <small>Tắt lọc trùng UID sẽ giúp tăng tốc độ tải sản phẩm lên.</small>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddAccounts\" class=\"btn btn-primary btn-sm\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i> Submit</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n<div class=\"modal fade\" id=\"nhap_nhieu_tai_khoan\" tabindex=\"-1\" aria-labelledby=\"h6_nhap_nhieu_tai_khoan\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <form action=\"\" method=\"POST\">\n                <div class=\"modal-header\">\n                    <h6 class=\"modal-title\" id=\"h6_nhap_nhieu_tai_khoan\">NHẬP NHIỀU TÀI KHOẢN VÀO KHO HÀNG\n                    </h6>\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n                </div>\n                <div class=\"modal-body\">\n                    <div class=\"form-group mb-3\">\n                        <label for=\"text-area\" class=\"form-label\">Tài khoản cần thêm: (1 dòng 1 tài khoản)</label>\n                        <textarea class=\"form-control\" name=\"accounts\" id=\"accounts\" placeholder=\"UID|PASS|...\nUID|PASS|...\nUID|PASS|...\nUID|PASS|...\" rows=\"5\" required></textarea>\n                        <small>Nhấn Submit để thêm <strong style=\"color: red;\" id=\"countAdd\">0</strong> tài\n                            khoản</small>\n                        <input type=\"hidden\" name=\"type\" value=\"multi\" readonly>\n                    </div>\n                    <div class=\"form-check form-check-md d-flex align-items-center\">\n                        <input class=\"form-check-input\" type=\"checkbox\" value=\"1\" id=\"a9895w22\" name=\"loc_trung_uid\">\n                        <label class=\"form-check-label\" for=\"a9895w22\">\n                            Lọc trùng UID\n                        </label>\n                    </div>\n                    <small>Tắt lọc trùng UID sẽ giúp tăng tốc độ tải sản phẩm lên.</small>\n\n                    <script>\n                    document.addEventListener(\"DOMContentLoaded\", function() {\n                        var textarea = document.getElementById('accounts');\n                        var countAdd = document.getElementById(\"countAdd\");\n\n                        if (textarea && countAdd) {\n                            textarea.addEventListener(\"input\", function() {\n                                var lines = textarea.value.split('\\n');\n                                var nonEmptyLinesCount = lines.filter(function(line) {\n                                    return line.trim().length >\n                                        0; // Lọc ra những dòng không rỗng\n                                }).length;\n                                countAdd.innerText =\n                                    nonEmptyLinesCount; // Cập nhật số dòng không rỗng vào countAdd\n                            });\n                        }\n                    });\n                    </script>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddAccounts\" class=\"btn btn-primary btn-sm\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i> Submit</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n<div class=\"modal fade\" id=\"nhap_tai_khoan_bang_txt\" tabindex=\"-1\" aria-labelledby=\"h6_nhap_tai_khoan_bang_txt\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-header\">\n                    <h6 class=\"modal-title\" id=\"h6_nhap_tai_khoan_bang_txt\">NHẬP TÀI KHOẢN BẰNG TỆP .TXT\n                    </h6>\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n                </div>\n                <div class=\"modal-body\">\n                    <div class=\"form-group mb-2\">\n                        <label for=\"formFile\" class=\"form-label\">Tải lên tệp tài khoản .txt</label>\n                        <input class=\"form-control\" type=\"file\" name=\"files_txt\" required>\n                        <input type=\"hidden\" name=\"type\" value=\"txt\" readonly>\n                    </div>\n                    <ul>\n                        <li>Chỉ nhập tệp định dạng .TXT</li>\n                        <li>1 dòng 1 tài khoản</li>\n                    </ul>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddAccounts\" class=\"btn btn-primary btn-sm\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i> Submit</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n<div class=\"modal fade\" id=\"xoa_toan_bo_tai_khoan\" tabindex=\"-1\" aria-labelledby=\"h6_xoa_toan_bo_tai_khoan\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <form action=\"\" method=\"POST\">\n                <div class=\"modal-header\">\n                    <h6 class=\"modal-title\" id=\"h6_xoa_toan_bo_tai_khoan\"><i class=\"fa-solid fa-triangle-exclamation\"></i> XÓA TOÀN BỘ TÀI KHOẢN ĐANG BÁN\n                    </h6>\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n                </div>\n                <div class=\"modal-body\">\n                    <p>Hệ thống sẽ thực hiện XÓA TOÀN BỘ tài khoản đang bán của kho hàng <b>";
    echo $code;
    echo "</b> nếu bạn xác nhận vào Input dưới đây.</p>\n                    <p>Để xác nhận XÓA TOÀN BỘ tài khoản trong kho hàng <b>";
    echo $code;
    echo "</b>, vui lòng nhập vào ô dưới đây nội dung là <b style=\"color:red;font-size:15px;\">toi dong y</b> để tiến hành xóa.</p>\n                    <input class=\"form-control\" type=\"text\" id=\"confirm_empty_list_account\" placeholder=\"Nhập nội dung toi dong y nếu bạn chắc chắn đã hiểu nội dung trên\"> \n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"button\" id=\"btn_format_list_account\" class=\"btn btn-primary btn-sm\"><i\n                            class=\"fa fa-fw fa-trash me-1\"></i> Xóa ngay</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n<script>\n\$(\"#btn_format_list_account\").click(function() {\n    Swal.fire({\n        title: \"Bạn có chắc không?\",\n        text: \"Hệ thống sẽ xóa vĩnh viễn toàn bộ dữ liệu tài khoản đang bán của kho hàng ";
    echo $code;
    echo " khi bạn nhấn Đồng Ý\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"Đồng ý\",\n        cancelButtonText: \"Đóng\"\n    }).then((result) => {\n        if (result.isConfirmed) {\n            \$.ajax({\n                url: \"";
    echo base_url("ajaxs/admin/remove.php");
    echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    action: 'empty_list_account_stock',\n                    token: '";
    echo $getUser["token"];
    echo "',\n                    confirm_empty_list_account: \$('#confirm_empty_list_account').val(),\n                    id: '";
    echo $code;
    echo "'\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, 'success');\n                        setTimeout(\"location.href = '';\", 1000);\n                    } else {\n                        showMessage(result.msg, 'error');\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    });\n});\n</script>\n<div class=\"modal fade\" id=\"xoa_nhieu_tai_khoan\" tabindex=\"-1\" aria-labelledby=\"h6_xoa_nhieu_tai_khoan\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered\">\n        <div class=\"modal-content\">\n            <form action=\"\" method=\"POST\">\n                <div class=\"modal-header\">\n                    <h6 class=\"modal-title\" id=\"h6_xoa_nhieu_tai_khoan\">XÓA NHIỀU TÀI KHOẢN ĐANG BÁN\n                    </h6>\n                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n                </div>\n                <div class=\"modal-body\">\n                    <div class=\"form-group\">\n                        <label for=\"text-area\" class=\"form-label\">Tài khoản cần xóa: (1 dòng 1 tài khoản)</label>\n                        <textarea class=\"form-control\" name=\"accounts\" id=\"accounts_remove\" placeholder=\"UID|... HOẶC MỖI UID\nUID|... HOẶC MỖI UID\nUID|... HOẶC MỖI UID\nUID|... HOẶC MỖI UID\" rows=\"5\" required></textarea>\n<small>Nhấn Submit để xóa <strong style=\"color: red;\" id=\"countRemove\">0</strong> tài\n                            khoản</small>\n                        <input type=\"hidden\" name=\"type\" value=\"multi\" readonly>\n                        <script>\n                    document.addEventListener(\"DOMContentLoaded\", function() {\n                        var textarea = document.getElementById('accounts_remove');\n                        var countAdd = document.getElementById(\"countRemove\");\n\n                        if (textarea && countAdd) {\n                            textarea.addEventListener(\"input\", function() {\n                                var lines = textarea.value.split('\\n');\n                                var nonEmptyLinesCount = lines.filter(function(line) {\n                                    return line.trim().length >\n                                        0; // Lọc ra những dòng không rỗng\n                                }).length;\n                                countAdd.innerText =\n                                    nonEmptyLinesCount; // Cập nhật số dòng không rỗng vào countAdd\n                            });\n                        }\n                    });\n                    </script>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"RemoveAccounts\" class=\"btn btn-primary btn-sm\"><i\n                            class=\"fa fa-fw fa-trash me-1\"></i> Submit</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n<div class=\"modal fade\" id=\"nhap_bang_api\" tabindex=\"-1\" aria-labelledby=\"h6_nhap_bang_api\" data-bs-keyboard=\"false\"\n    aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered modal-lg\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"h6_nhap_bang_api\"><i class=\"fa-solid fa-code\"></i> NHẬP TÀI KHOẢN BẰNG\n                    API\n                </h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <div class=\"modal-body\">\n                <div class=\"form-group\">\n                    <label for=\"text-area\" class=\"form-label\">API nhập tài khoản vào kho hàng này</label>\n                    <div class=\"input-group mb-3\">\n                        <input type=\"text\" class=\"form-control\" id=\"url_api\"\n                            value=\"";
    echo base_url("api/importAccount.php?code=" . $code . "&api_key=" . $getUser["api_key"] . "&account=&filter=1");
    echo "\">\n                        <button class=\"btn btn-info copy\" data-clipboard-target=\"#url_api\" type=\"button\"\n                            onclick=\"copy()\">Copy</button>\n                    </div>\n                </div>\n                <p>API Key của bạn là: <strong class=\"copy\" style=\"color: blue;\" id=\"api_key\"\n                        data-clipboard-target=\"#api_key\" onclick=\"copy()\" data-toggle=\"tooltip\" data-placement=\"bottom\"\n                        title=\"Nhấn vào để Copy\">";
    echo $getUser["api_key"];
    echo "</strong> <button\n                        onclick=\"changeAPIKey(`";
    echo $getUser["token"];
    echo "`)\" data-toggle=\"tooltip\" data-placement=\"bottom\"\n                        title=\"Thay đổi API KEY khác nếu API KEY cũ của bạn bị lộ ra ngoài\"\n                        class=\"btn btn-danger btn-sm\"><i class=\"fa-solid fa-rotate\"></i></button></p>\n                <p>Trong đó:</p>\n                <ul>\n                    <li><strong>code</strong>: mã của kho hàng, ví dụ kho hàng hiện tại của bạn là <strong\n                            style=\"color:red;\">";
    echo $code;
    echo "</strong></li>\n                    <li><strong>api_key</strong>: API Key của tài khoản admin có role <strong>Quản lý kho hàng sản phẩm</strong>\n                        <span class=\"badge bg-primary-transparent\">edit_stock_product</span>\n                    </li>\n                    <li><strong>filter</strong>: 1 để bật lọc trùng UID, 0 để tắt lọc trùng UID.</span>\n                    </li>\n                    <li><strong>account</strong>: tài khoản cần thêm vào kho hàng</li>\n                </ul>\n\n\n            </div>\n            <div class=\"modal-footer\">\n                <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\">Close</button>\n            </div>\n        </div>\n    </div>\n</div>\n<script>\nfunction updateFormProduct(id) {\n    \$.ajax({\n        url: \"";
    echo BASE_URL("ajaxs/admin/update.php");
    echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'update_status_product',\n            id: id,\n            status: \$('#status' + id + ':checked').val()\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, result.status);\n            } else {\n                showMessage(result.msg, result.status);\n            }\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n\nfunction removeProduct(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác Nhận Xóa sản phẩm\",\n        message: \"Bạn có chắc chắn muốn xóa sản phẩm ID \" + id + \" không ?\",\n        confirmText: \"Đồng Ý\",\n        cancelText: \"Hủy\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
    echo BASE_URL("ajaxs/admin/remove.php");
    echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    id: id,\n                    action: 'removeProduct'\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n</script>\n\n\n\n<script>\nfunction postRemoveAccount(id) {\n    \$.ajax({\n        url: \"";
    echo BASE_URL("ajaxs/admin/remove.php");
    echo "\",\n        type: 'POST',\n        dataType: \"JSON\",\n        data: {\n            action: 'removeAccountStock',\n            id: id\n        },\n        success: function(result) {\n            if (result.status == 'success') {\n                showMessage(result.msg, 'success');\n            } else {\n                showMessage(result.msg, 'error');\n            }\n        }\n    });\n}\n\nfunction removeAccount(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Xác nhận xóa tài khoản\",\n        message: \"Bạn có chắc chắn muốn xóa tài khoản này không ?\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            postRemoveAccount(id);\n            setTimeout(function() {\n                location.reload();\n            }, 1000);\n        }\n    })\n}\n</script>\n<script>\nfunction confirmDeleteAccount() {\n    var checkbox = document.getElementsByName('checkbox_product_stock');\n    var isAnyCheckboxChecked = false;\n    for (var i = 0; i < checkbox.length; i++) {\n        if (checkbox[i].checked === true) {\n            isAnyCheckboxChecked = true;\n            break;\n        }\n    }\n    if (!isAnyCheckboxChecked) {\n        showMessage('Vui lòng chọn ít nhất một bản ghi', 'error');\n        return;\n    }\n    var result = confirm('Bạn có đồng ý xóa các bản ghi đã chọn không?');\n    if (result) {\n        \$('#confirmDeleteAccount').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
    echo __("Processing...");
    echo "</span>')\n            .prop('disabled',\n                true);\n\n        function postUpdatesSequentially(index) {\n            if (index < checkbox.length) {\n                if (checkbox[index].checked === true) {\n                    postRemoveAccount(checkbox[index].value);\n                }\n                setTimeout(function() {\n                    postUpdatesSequentially(index + 1);\n                }, 100);\n            } else {\n                setTimeout(function() {\n                    location.reload();\n                }, 1000);\n            }\n        }\n        postUpdatesSequentially(0);\n    }\n}\n\n\$(function() {\n    \$('#check_all_checkbox_product_stock').on('click', function() {\n        \$('.checkbox_product_stock').prop('checked', this.checked);\n    });\n    \$('.checkbox_product_stock').on('click', function() {\n        \$('#check_all_checkbox_product_stock').prop('checked', \$('.checkbox_product_stock:checked')\n            .length === \$('.checkbox_product_stock').length);\n    });\n});\n</script>\n\n<script>\nfunction exportDataTXT() {\n    // Lấy tất cả các phần tử input có type là checkbox và được chọn\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_stock\"]:checked');\n\n    // Kiểm tra nếu không có checkbox nào được chọn\n    if (checkboxes.length === 0) {\n        showMessage('Vui lòng chọn ít nhất một bản ghi', 'error');\n        return;\n    }\n    \$('#exportDataTXT').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
    echo __("Processing...");
    echo "</span>')\n        .prop('disabled',\n            true);\n    // Tạo một mảng để lưu trữ giá trị của các checkbox được chọn\n    var selectedData = [];\n\n    // Duyệt qua mỗi checkbox được chọn và thêm giá trị vào mảng\n    checkboxes.forEach(function(checkbox) {\n        selectedData.push(checkbox.getAttribute('data-checkbox') + ''); // Thêm dòng mới sau mỗi giá trị\n    });\n\n    // Lấy số lượng dữ liệu được xuất\n    var numberOfData = checkboxes.length;\n\n    // Chuyển đổi mảng thành chuỗi với mỗi giá trị trên một dòng\n    var dataString = selectedData.join('');\n\n    // Tạo một đối tượng Blob chứa dữ liệu\n    var blob = new Blob([dataString], {\n        type: 'text/plain'\n    });\n\n    // Tạo một đường link để tải xuống tệp tin TXT\n    var link = document.createElement('a');\n    link.href = URL.createObjectURL(blob);\n    link.download = '";
    echo $code;
    echo "_' + numberOfData + '.txt';\n\n    // Thêm đường link vào trang và kích hoạt sự kiện click để tải xuống\n    document.body.appendChild(link);\n    link.click();\n\n    // Xóa đường link sau khi đã tải xuống\n    document.body.removeChild(link);\n    \$('#exportDataTXT').html(\n        '<i class=\"fa-solid fa-file-export\"></i> XUẤT TỆP .TXT'\n    ).prop('disabled',\n        false);\n\n\n}\n</script>\n\n<script>\nfunction exportDataClipboard() {\n    // Lấy tất cả các phần tử input có type là checkbox và được chọn\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_stock\"]:checked');\n\n    // Kiểm tra nếu không có checkbox nào được chọn\n    if (checkboxes.length === 0) {\n        showMessage('Vui lòng chọn ít nhất một bản ghi', 'error');\n        return;\n    }\n    \$('#exportDataClipboard').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
    echo __("Processing...");
    echo "</span>')\n        .prop('disabled',\n            true);\n    // Tạo một mảng để lưu trữ giá trị của các checkbox được chọn\n    var selectedData = [];\n\n    // Duyệt qua mỗi checkbox được chọn và thêm giá trị vào mảng\n    checkboxes.forEach(function(checkbox) {\n        // Đảm bảo rằng có một dòng mới sau mỗi giá trị\n        selectedData.push(checkbox.getAttribute('data-checkbox').trim());\n    });\n\n    // Chuyển đổi mảng thành chuỗi, với mỗi giá trị trên một dòng\n    var dataString = selectedData.join('\\n');\n\n    // Sao chép chuỗi vào clipboard\n    navigator.clipboard.writeText(dataString).then(function() {\n        showMessage(\"Nội dung đã được sao chép vào clipboard!\", 'success');\n        \$('#exportDataClipboard').html(\n            '<i class=\"fa-solid fa-copy\"></i> COPY'\n        ).prop('disabled',\n            false);\n    }).catch(function(error) {\n        \$('#exportDataClipboard').html(\n            '<i class=\"fa-solid fa-copy\"></i> COPY'\n        ).prop('disabled',\n            false);\n        alert('Có lỗi xảy ra trong quá trình sao chép: ' + error);\n    });\n}\n</script>\n\n<script>\nfunction exportUIDClipboard() {\n    // Lấy tất cả các phần tử input có type là checkbox và được chọn\n    var checkboxes = document.querySelectorAll('input[name=\"checkbox_product_stock\"]:checked');\n\n    // Kiểm tra nếu không có checkbox nào được chọn\n    if (checkboxes.length === 0) {\n        showMessage('Vui lòng chọn ít nhất một bản ghi', 'error');\n        return;\n    }\n    \$('#exportUIDClipboard').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
    echo __("Processing...");
    echo "</span>')\n        .prop('disabled',\n            true);\n    // Tạo một mảng để lưu trữ giá trị của các checkbox được chọn\n    var selectedData = [];\n\n    // Duyệt qua mỗi checkbox được chọn và thêm giá trị vào mảng\n    checkboxes.forEach(function(checkbox) {\n        // Lấy dữ liệu và chia nó dựa trên dấu '|'\n        var fullData = checkbox.getAttribute('data-checkbox').trim();\n        var splitData = fullData.split('|');\n        // Kiểm tra để chắc chắn rằng dữ liệu tồn tại trước khi thêm vào mảng\n        if (splitData.length > 0) {\n            selectedData.push(splitData[0]); // Chỉ lấy phần trước dấu '|'\n        }\n    });\n\n    // Chuyển đổi mảng thành chuỗi, với mỗi giá trị trên một dòng\n    var dataString = selectedData.join('\\n');\n\n    // Sao chép chuỗi vào clipboard\n    navigator.clipboard.writeText(dataString).then(function() {\n        showMessage(\"Nội dung đã được sao chép vào clipboard!\", 'success');\n        \$('#exportUIDClipboard').html(\n            '<i class=\"fa-regular fa-copy\"></i> COPY UID'\n        ).prop('disabled',\n            false);\n    }).catch(function(error) {\n        alert('Có lỗi xảy ra trong quá trình sao chép: ' + error);\n    });\n}\n</script>\n\n<script>\nfunction changeAPIKey(token) {\n    cuteAlert({\n        type: \"question\",\n        title: \"Bạn có chắc không?\",\n        message: \"Hệ thống sẽ thay đổi API KEY nếu bạn nhấn Đồng Ý\",\n        confirmText: \"Đồng ý\",\n        cancelText: \"Không\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
    echo BASE_URL("ajaxs/client/auth.php");
    echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    token: '";
    echo $getUser["token"];
    echo "',\n                    action: 'changeAPIKey'\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, 'success');\n                        document.getElementById(\"url_api\").value =\n                            '";
    echo base_url();
    echo "api/importAccount.php?code=";
    echo $code;
    echo "&api_key=' +\n                            result.api_key + '&account=';\n                        document.getElementById(\"api_key\").innerHTML = result.api_key;\n                    } else {\n                        Swal.fire({\n                            title: \"";
    echo __("Thất bại!");
    echo "\",\n                            text: result.msg,\n                            icon: \"error\"\n                        });\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n</script>\n<script type=\"text/javascript\">\nnew ClipboardJS(\".copy\");\n\nfunction copy() {\n    showMessage(\"";
    echo __("Đã sao chép vào bộ nhớ tạm");
    echo "\", 'success');\n}\n</script>\n\n<div class=\"modal fade\" id=\"viewListDIE\" tabindex=\"-1\" aria-labelledby=\"viewListDIE\" data-bs-keyboard=\"false\"\n    aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered modal-lg\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"viewListDIE\">DANH SÁCH TÀI KHOẢN <strong style=\"color:red;\">DIE</strong> CỦA\n                    KHO HÀNG <strong style=\"color:red;\">";
    echo $code;
    echo "</strong>\n                </h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <div class=\"modal-body\">\n                <textarea class=\"form-control mb-2\" id=\"coypyBox_viewListDIE\" readonly rows=\"10\"></textarea>\n                <button type=\"button\" id=\"btn_format_list_die\" class=\"btn btn-danger btn-sm\"><i class=\"fa-solid fa-trash\"></i> Xóa toàn bộ</button>\n            </div>\n            <div class=\"modal-footer\">\n                <button type=\"button\" onclick=\"copy()\" data-clipboard-target=\"#coypyBox_viewListDIE\"\n                    class=\"btn btn-info shadow-info btn-wave copy\">Copy</button>\n                <button type=\"button\" class=\"btn btn-light shadow-light btn-wave\" data-bs-dismiss=\"modal\">Đóng</button>\n            </div>\n        </div>\n    </div>\n</div>\n<script type=\"text/javascript\">\nfunction viewListDIE(code) {\n    var originalButtonContent = \$('#btn_viewListDIE').html();\n    \$('#btn_viewListDIE').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
    echo __("Processing...");
    echo "</span>')\n        .prop('disabled',\n            true);\n    \$.ajax({\n        url: \"";
    echo base_url("ajaxs/admin/view.php");
    echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'view_product_die',\n            token: '";
    echo $getUser["token"];
    echo "',\n            code: code\n        },\n        success: function(result) {\n            \$('#viewListDIE').modal('show');\n            \$('#coypyBox_viewListDIE').val(result.accounts);\n            \$('#btn_viewListDIE').html(originalButtonContent).prop('disabled', false);\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n</script>\n\n<div class=\"modal fade\" id=\"viewListLIVE\" tabindex=\"-1\" aria-labelledby=\"viewListLIVE\" data-bs-keyboard=\"false\"\n    aria-hidden=\"true\">\n    <!-- Scrollable modal -->\n    <div class=\"modal-dialog modal-dialog-centered modal-lg\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"viewListLIVE\">DANH SÁCH TÀI KHOẢN <strong style=\"color:green;\">LIVE</strong>\n                    CỦA KHO HÀNG <strong style=\"color:red;\">";
    echo $code;
    echo "</strong>\n                </h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <div class=\"modal-body\">\n                <textarea class=\"form-control\" id=\"coypyBox_viewListLIVE\" readonly rows=\"10\"></textarea>\n            </div>\n            <div class=\"modal-footer\">\n                <button type=\"button\" onclick=\"copy()\" data-clipboard-target=\"#coypyBox_viewListLIVE\"\n                    class=\"btn btn-info shadow-info btn-wave copy\">Copy</button>\n                <button type=\"button\" class=\"btn btn-light shadow-light btn-wave\" data-bs-dismiss=\"modal\">Đóng</button>\n            </div>\n        </div>\n    </div>\n</div>\n<script type=\"text/javascript\">\nfunction viewListLIVE(code) {\n    var originalButtonContent = \$('#btn_viewListLIVE').html();\n    \$('#btn_viewListLIVE').html('<span><i class=\"fa fa-spinner fa-spin\"></i> ";
    echo __("Processing...");
    echo "</span>')\n        .prop('disabled',\n            true);\n    \$.ajax({\n        url: \"";
    echo base_url("ajaxs/admin/view.php");
    echo "\",\n        method: \"POST\",\n        dataType: \"JSON\",\n        data: {\n            action: 'view_product_live',\n            token: '";
    echo $getUser["token"];
    echo "',\n            code: code\n        },\n        success: function(result) {\n            \$('#viewListLIVE').modal('show');\n            \$('#coypyBox_viewListLIVE').val(result.accounts);\n            \$('#btn_viewListLIVE').html(originalButtonContent).prop('disabled', false);\n        },\n        error: function() {\n            alert(html(result));\n            location.reload();\n        }\n    });\n}\n</script>\n\n<script>\n\$(\"#btn_format_list_die\").click(function() {\n    Swal.fire({\n        title: \"Bạn có chắc không?\",\n        text: \"Hệ thống sẽ xóa vĩnh viễn toàn bộ dữ liệu tài khoản DIE của kho hàng ";
    echo $code;
    echo " khi bạn nhấn Đồng Ý\",\n        icon: \"warning\",\n        showCancelButton: true,\n        confirmButtonColor: \"#3085d6\",\n        cancelButtonColor: \"#d33\",\n        confirmButtonText: \"Đồng ý\",\n        cancelButtonText: \"Đóng\"\n    }).then((result) => {\n        if (result.isConfirmed) {\n            \$.ajax({\n                url: \"";
    echo base_url("ajaxs/admin/remove.php");
    echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    action: 'empty_list_die',\n                    token: '";
    echo $getUser["token"];
    echo "',\n                    id: '";
    echo $code;
    echo "'\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, 'success');\n                        setTimeout(\"location.href = '';\", 1000);\n                    } else {\n                        showMessage(result.msg, 'error');\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    });\n});\n</script>";
}

?>