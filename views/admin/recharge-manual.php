<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Recharge Manual"), "desc" => "CMSNT Panel", "keyword" => "cmsnt, CMSNT, cmsnt.co,"];
$body["header"] = "\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css\">\n<link rel=\"stylesheet\" href=\"https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css\">\n\n";
$body["footer"] = "\n<script src=\"https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js\"></script>\n<script src=\"https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js\"></script>\n<script src=\"https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js\"></script>\n<script src=\"https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js\"></script>\n";
require_once __DIR__ . "/../../models/is_admin.php";
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/sidebar.php";
require_once __DIR__ . "/nav.php";
if(!checkPermission($getUser["admin"], "view_recharge")) {
    exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
}
if(isset($_POST["SaveSettings"])) {
    if($CMSNT->site("status_demo") != 0) {
        exit("<script type=\"text/javascript\">if(!alert(\"" . __("This function cannot be used because this is a demo site") . "\")){window.history.back().location.reload();}</script>");
    }
    if(!checkPermission($getUser["admin"], "edit_recharge")) {
        exit("<script type=\"text/javascript\">if(!alert(\"Bạn không có quyền sử dụng tính năng này\")){window.history.back();}</script>");
    }
    $Mobile_Detect = new Mobile_Detect();
    $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Cấu hình Manual Payment")]);
    foreach ($_POST as $key => $value) {
        $CMSNT->update("settings", ["value" => $value], " `name` = '" . $key . "' ");
    }
    $my_text = $CMSNT->site("noti_action");
    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
    $my_text = str_replace("{username}", $getUser["username"], $my_text);
    $my_text = str_replace("{action}", __("Cấu hình nạp tiền thẻ cào"), $my_text);
    $my_text = str_replace("{ip}", myip(), $my_text);
    $my_text = str_replace("{time}", gettime(), $my_text);
    sendMessAdmin($my_text);
    exit("<script type=\"text/javascript\">if(!alert(\"" . __("Save successfully!") . "\")){window.history.back().location.reload();}</script>");
} else {
    if(isset($_POST["AddPage"])) {
        if($CMSNT->site("status_demo") != 0) {
            exit("<script type=\"text/javascript\">if(!alert(\"Không được dùng chức năng này vì đây là trang web demo.\")){window.history.back().location.reload();}</script>");
        }
        $url_icon = NULL;
        if(check_img("icon")) {
            $rand = random("0123456789QWERTYUIOPASDGHJKLZXCVBNM", 4);
            $uploads_dir = "assets/storage/images/icon_gateway" . $rand . ".png";
            $tmp_name = $_FILES["icon"]["tmp_name"];
            $addlogo = move_uploaded_file($tmp_name, $uploads_dir);
            if($addlogo) {
                $url_icon = $uploads_dir;
            }
        }
        $isInsert = $CMSNT->insert("payment_manual", ["icon" => $url_icon, "title" => check_string($_POST["title"]), "description" => check_string($_POST["description"]), "slug" => check_string($_POST["slug"]), "content" => isset($_POST["content"]) ? base64_encode($_POST["content"]) : NULL, "display" => check_string($_POST["display"]), "create_gettime" => gettime(), "update_gettime" => gettime()]);
        if($isInsert) {
            $Mobile_Detect = new Mobile_Detect();
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Tạo trang thanh toán thủ công") . " (" . check_string($_POST["title"]) . ")."]);
            $my_text = $CMSNT->site("noti_action");
            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
            $my_text = str_replace("{username}", $getUser["username"], $my_text);
            $my_text = str_replace("{action}", __("Tạo trang thanh toán thủ công") . " (" . check_string($_POST["title"]) . ").", $my_text);
            $my_text = str_replace("{ip}", myip(), $my_text);
            $my_text = str_replace("{time}", gettime(), $my_text);
            sendMessAdmin($my_text);
            exit("<script type=\"text/javascript\">if(!alert(\"Thêm thành công !\")){location.href = \"\";}</script>");
        }
        exit("<script type=\"text/javascript\">if(!alert(\"Thêm thất bại !\")){window.history.back().location.reload();}</script>");
    }
    echo "\n\n<div class=\"main-content app-content\">\n    <div class=\"container-fluid\">\n        <div class=\"d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb\">\n            <h1 class=\"page-title fw-semibold fs-18 mb-0\">Phương thức nạp tiền Thủ Công</h1>\n            <div class=\"ms-md-1 ms-0\">\n                <nav>\n                    <ol class=\"breadcrumb mb-0\">\n                        <li class=\"breadcrumb-item\"><a href=\"#\">Nạp tiền</a></li>\n                        <li class=\"breadcrumb-item active\" aria-current=\"page\">Manual Payment</li>\n                    </ol>\n                </nav>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-xl-12\">\n                <div class=\"card custom-card\">\n                    <div class=\"card-header justify-content-between\">\n                        <div class=\"card-title\">\n                            DANH SÁCH TRANG NẠP TIỀN THỦ CÔNG\n                        </div>\n                        <div class=\"d-flex\">\n                            <button data-bs-toggle=\"modal\" data-bs-target=\"#exampleModalScrollable2\"\n                                class=\"btn btn-sm btn-primary btn-wave waves-light waves-effect waves-light\"><i\n                                    class=\"ri-add-line fw-semibold align-middle\"></i> Thêm trang mới</button>\n                        </div>\n                    </div>\n                    <div class=\"card-body\">\n                        <table id=\"datatable-basic\" class=\"table text-nowrap table-striped table-hover table-bordered\"\n                            style=\"width:100%\">\n                            <thead>\n                                <tr>\n                                    <th>#</th>\n                                    <th>";
    echo __("Title");
    echo "</th>\n                                    <th>";
    echo __("Icon");
    echo "</th>\n                                    <th>";
    echo __("Trạng thái");
    echo "</th>\n                                    <th>";
    echo __("Thời gian thêm");
    echo "</th>\n                                    <th>";
    echo __("Cập nhật");
    echo "</th>\n                                    <th>";
    echo __("Thao tác");
    echo "</th>\n                                </tr>\n                            </thead>\n                            <tbody>\n                                ";
    $i = 0;
    foreach ($CMSNT->get_list("SELECT * FROM `payment_manual`  ") as $payment_manual) {
        echo "                                <tr>\n                                    <td>";
        echo $i++;
        echo "</td>\n                                    <td>";
        echo $payment_manual["title"];
        echo "</td>\n                                    <td><img width=\"40px\" src=\"";
        echo base_url($payment_manual["icon"]);
        echo "\"></td>\n                                    <td>";
        echo display_status_product($payment_manual["display"]);
        echo "</td>\n                                    <td>";
        echo $payment_manual["create_gettime"];
        echo "</td>\n                                    <td>";
        echo $payment_manual["update_gettime"];
        echo "</td>\n                                    <td><a aria-label=\"\"\n                                            href=\"";
        echo base_url_admin("recharge-manual-edit&id=" . $payment_manual["id"]);
        echo "\"\n                                            style=\"color:white;\" class=\"btn btn-info btn-sm btn-icon-left m-b-10\"\n                                            type=\"button\">\n                                            <i class=\"fas fa-edit mr-1\"></i><span class=\"\"> Edit</span>\n                                        </a>\n                                        <button style=\"color:white;\" onclick=\"RemoveRow('";
        echo $payment_manual["id"];
        echo "')\"\n                                            class=\"btn btn-danger btn-sm btn-icon-left m-b-10\" type=\"button\">\n                                            <i class=\"fas fa-trash mr-1\"></i><span class=\"\"> Delete</span>\n                                        </button>\n                                    </td>\n                                </tr>\n                                ";
    }
    echo "                            </tbody>\n                        </table>\n                        <br>\n                        <p>Hướng dẫn sử dụng chức năng này tại: <a class=\"text-primary\"\n                                href=\"https://help.cmsnt.co/huong-dan/shopclone7-cach-tao-trang-nap-tien-thu-cong-manual-payment/\"\n                                target=\"_blank\">https://help.cmsnt.co/huong-dan/shopclone7-cach-tao-trang-nap-tien-thu-cong-manual-payment/</a>\n                        </p>\n                    </div>\n                </div>\n            </div>\n\n        </div>\n    </div>\n</div>\n\n<div class=\"modal fade\" id=\"exampleModalScrollable2\" tabindex=\"-1\" aria-labelledby=\"exampleModalScrollable2\"\n    data-bs-keyboard=\"false\" aria-hidden=\"true\">\n    <div class=\"modal-dialog modal-dialog-centered modal-xl\">\n        <div class=\"modal-content\">\n            <div class=\"modal-header\">\n                <h6 class=\"modal-title\" id=\"staticBackdropLabel2\">Thêm trang mới</h6>\n                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n            </div>\n            <form action=\"\" method=\"POST\" enctype=\"multipart/form-data\">\n                <div class=\"modal-body\">\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Title:\n                            <span class=\"text-danger\">*</span></label>\n                        <div class=\"col-sm-8\">\n                            <input name=\"title\" type=\"text\" class=\"form-control\" required>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Slug:\n                            <span class=\"text-danger\">*</span></label>\n                        <div class=\"col-sm-8\">\n                            <div class=\"input-group\">\n                                <span class=\"input-group-text\">";
    echo base_url("recharge-manual/");
    echo "</span>\n                                <input type=\"text\" class=\"form-control\" name=\"slug\" required>\n                            </div>\n                        </div>\n                    </div>\n                    <script>\n                    function removeVietnameseTones(str) {\n                        return str.normalize('NFD') // Tách tổ hợp ký tự và dấu\n                            .replace(/[\\u0300-\\u036f]/g, '') // Loại bỏ dấu\n                            .replace(/đ/g, 'd') // Chuyển đổi chữ \"đ\" thành \"d\"\n                            .replace(/Đ/g, 'D'); // Chuyển đổi chữ \"Đ\" thành \"D\"\n                    }\n\n                    document.querySelector('input[name=\"title\"]').addEventListener('input', function() {\n                        var productName = this.value;\n\n                        // Chuyển tên sản phẩm thành slug\n                        var slug = removeVietnameseTones(productName.toLowerCase())\n                            .replace(/ /g, '-') // Thay khoảng trắng bằng dấu gạch ngang\n                            .replace(/[^\\w-]+/g, ''); // Loại bỏ các ký tự không hợp lệ\n\n                        // Đặt giá trị slug vào trường input slug\n                        document.querySelector('input[name=\"slug\"]').value = slug;\n                    });\n                    </script>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Description:</label>\n                        <div class=\"col-sm-8\">\n                            <textarea class=\"form-control\" name=\"description\"></textarea>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">Icon:\n                            <span class=\"text-danger\">*</span></label>\n                        <div class=\"col-sm-8\">\n                            <input type=\"file\" class=\"custom-file-input\" name=\"icon\" required>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\"\n                            for=\"example-hf-email\">";
    echo __("Nội dung chi tiết:");
    echo "</label>\n                        <div class=\"col-sm-12\">\n                            <textarea class=\"content\" id=\"content\" name=\"content\"></textarea>\n                            <br>\n                            <ul>\n                                <li><strong>{username}</strong> => Username của khách hàng.</li>\n                                <li><strong>{id}</strong> => ID của khách hàng.</li>\n                                <li><strong>{hotline}</strong> => Hotline đã nhập trong cài đặt.</li>\n                                <li><strong>{email} </strong> => Email đã nhập trong cài đặt.</li>\n                                <li><strong>{fanpage}</strong> => Fanpage đã nhập trong cài đặt.</li>\n                            </ul>\n                        </div>\n                    </div>\n                    <div class=\"row mb-4\">\n                        <label class=\"col-sm-4 col-form-label\" for=\"example-hf-email\">";
    echo __("Trạng thái:");
    echo " <span\n                                class=\"text-danger\">*</span></label>\n                        <div class=\"col-sm-8\">\n                            <select class=\"form-control\" name=\"display\" required>\n                                <option value=\"1\">ON</option>\n                                <option value=\"0\">OFF</option>\n                            </select>\n                        </div>\n                    </div>\n                </div>\n                <div class=\"modal-footer\">\n                    <button type=\"button\" class=\"btn btn-light btn-sm\" data-bs-dismiss=\"modal\">Close</button>\n                    <button type=\"submit\" name=\"AddPage\" class=\"btn btn-primary btn-sm\"><i\n                            class=\"fa fa-fw fa-plus me-1\"></i>\n                        ";
    echo __("Submit");
    echo "</button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>\n\n";
    require_once __DIR__ . "/footer.php";
    echo "\n\n<script>\nCKEDITOR.replace(\"content\");\n</script>\n\n<script type=\"text/javascript\">\nfunction RemoveRow(id) {\n    cuteAlert({\n        type: \"question\",\n        title: \"";
    echo __("Xác nhận xóa item");
    echo "\",\n        message: \"";
    echo __("Bạn có chắc chắn muốn xóa ID");
    echo " \" + id + \" ?\",\n        confirmText: \"Okey\",\n        cancelText: \"Close\"\n    }).then((e) => {\n        if (e) {\n            \$.ajax({\n                url: \"";
    echo BASE_URL("ajaxs/admin/remove.php");
    echo "\",\n                method: \"POST\",\n                dataType: \"JSON\",\n                data: {\n                    action: 'remove_payment_manual',\n                    id: id\n                },\n                success: function(result) {\n                    if (result.status == 'success') {\n                        showMessage(result.msg, result.status);\n                        location.reload();\n                    } else {\n                        showMessage(result.msg, result.status);\n                    }\n                },\n                error: function() {\n                    alert(html(result));\n                    location.reload();\n                }\n            });\n        }\n    })\n}\n</script>\n<script>\n\$('#datatable-basic').DataTable({\n    language: {\n        searchPlaceholder: 'Search...',\n        sSearch: '',\n    },\n    \"pageLength\": 10,\n    scrollX: true\n});\n</script>";
}

?>