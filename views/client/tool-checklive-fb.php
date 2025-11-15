<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
$body = ["title" => __("Công cụ Check Live UID Facebook") . " | " . $CMSNT->site("title"), "desc" => $CMSNT->site("description"), "keyword" => $CMSNT->site("keywords")];
$body["header"] = "\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.6/clipboard.min.js\"></script>\n \n";
$body["footer"] = "\n\n";
if(isset($_COOKIE["token"])) {
    $getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `token` = '" . check_string($_COOKIE["token"]) . "' ");
    if(!$getUser) {
        header("location: " . BASE_URL("client/logout"));
        exit;
    }
    $_SESSION["login"] = $getUser["token"];
}
if(isset($_SESSION["login"])) {
    require_once __DIR__ . "/../../models/is_user.php";
}
if($CMSNT->site("status_menu_tools") != 1) {
    redirect(base_url("client/home"));
}
require_once __DIR__ . "/header.php";
require_once __DIR__ . "/nav.php";
echo "\n\n<section class=\"py-5 inner-section profile-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-12\">\n                <div class=\"posterd home mb-3\" style=\"background-image: url(";
echo base_url("mod/img/bg-intro.png");
echo ")\">\n                    <div class=\"welcomto\">\n                        <div class=\"box-intro\">\n                            <img src=\"";
echo base_url("mod/img/icon-facebook.png");
echo "\" alt=\"Accnice\" width=\"70\" height=\"70\">\n                        </div>\n                        <div class=\"\">\n                            <div\n                                style=\"font-size: 15px; text-shadow: rgba(0, 0, 0, 0.25) 0px 3px 5px;font-family: Robot,Roboto,sans-serif;\">\n                                ";
echo __("Bạn đang xem");
echo "</div>\n                            <h1\n                                style=\"color: #fff; font-size: 25px; font-weight:500; margin-top: 10px; text-shadow: rgba(0, 0, 0, 0.25) 0px 3px 5px;font-family: Robot,Roboto,sans-serif;\">\n                                ";
echo __("Tool Check live UID Facebook");
echo "</h1>\n                        </div>\n                    </div>\n                </div>\n            </div>\n            ";
require_once __DIR__ . "/widget_tools.php";
echo "            <div class=\"mb-5\"></div>\n            <div class=\"col-12 d-flex justify-content-center mb-3\">\n                <div class=\"d-flex\">\n                    <span class=\"mx-1 px-4 py-3 border rounded bg-success text-white fw-bold\">\n                        Live: <span id=\"liveCount\">0</span>\n                    </span>\n                    <span class=\"mx-1 px-4 py-3 border rounded bg-danger text-white fw-bold\">\n                        Dead: <span id=\"dieCount\">0</span>\n                    </span>\n                    <span class=\"mx-1 px-4 py-3 border rounded bg-warning text-white fw-bold\">\n                        Checked: <span id=\"totalCount\">0</span> account\n                    </span>\n                </div>\n            </div>\n\n            <div class=\"col-md-12\">\n                <div class=\"account-card pt-3\">\n                    <div class=\"row mt-4\">\n                        <div class=\"col-12\">\n                            <div class=\"mb-3\">\n                                <div class=\"d-flex justify-content-between mb-2\">\n                                    <label for\n                                        class=\"form-label fw-bold text-success\">";
echo __("Nhập danh sách UID");
echo "</label>\n                                </div>\n                                <div class=\"form-group\">\n                                    <textarea class=\"form-control\" name id=\"listId\"\n                                        placeholder=\"";
echo __("Mỗi dòng 1 UID");
echo "\" rows=\"10\" autofocus></textarea>\n                                </div>\n                            </div>\n                            <center>\n                                <button class=\"btn btn-primary fw-bold mb-5\" id=\"btnStart\">\n                                    <i class=\"fa-solid fa-play\"></i> Start </button>\n                            </center>\n                        </div>\n                        <div class=\"col-12\">\n                            <div class=\"row\">\n                                <div class=\"col-md-6\">\n                                    <div class=\"mb-3\">\n                                        <div class=\"d-flex justify-content-between mb-2\">\n                                            <label for\n                                                class=\"form-label fw-bold text-success\">";
echo __("Tài khoản Live");
echo "</label>\n                                            <button class=\"btn btn-secondary btn-sm\"\n                                                id=\"btnCopyLive\"><b>Copy</b></button>\n                                        </div>\n                                        <div class=\"form-group\">\n                                            <textarea class=\"form-control\" readonly name id=\"listLive\"\n                                                rows=\"10\"></textarea>\n                                        </div>\n                                    </div>\n                                </div>\n                                <div class=\"col-md-6\">\n                                    <div class=\"mb-3\">\n                                        <div class=\"d-flex justify-content-between mb-2\">\n                                            <label for\n                                                class=\"form-label fw-bold text-danger\">";
echo __("Tài khoản Die");
echo "</label>\n                                            <button class=\"btn btn-secondary btn-sm\"\n                                                id=\"btnCopyDie\"><b>Copy</b></button>\n                                        </div>\n                                        <div class=\"form-group\">\n                                            <textarea class=\"form-control\" readonly name id=\"listDie\"\n                                                rows=\"10\"></textarea>\n                                        </div>\n                                    </div>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</section>\n\n\n\n";
require_once __DIR__ . "/footer.php";
echo "\n<script>\n\$(document).ready(() => {\n\n    \$(\"#btnCopyLive\").click(function() {\n        \$(\"#listLive\").select();\n        document.execCommand('copy');\n        showMessage(\"";
echo __("Đã sao chép vào bộ nhớ tạm");
echo "\", 'success');\n    });\n    \$(\"#btnCopyDie\").click(function() {\n        \$(\"#listDie\").select();\n        document.execCommand('copy');\n        showMessage(\"";
echo __("Đã sao chép vào bộ nhớ tạm");
echo "\", 'success');\n    });\n\n    let live = 0\n    let dies = 0\n    let c = 0;\n    var n;\n    var arrclone;\n\n    \$('#btnStart').click(() => {\n        //get data\n\n        if (!\$('#listId').val().trim()) {\n            get = false;\n            return;\n        }\n\n        \$(\"#listLive\").empty()\n        \$(\"#listDie\").empty()\n\n        die = 0\n        live = 0\n\n        n = 0\n        let data = \$('#listId').val().split(/\\r?\\n/);\n        //post len server\n\n        data = [...new Set(data)];\n\n\n        data = data.filter(nx => nx)\n        arrclone = data\n        c = arrclone.length;\n\n        \$(\"#totalCount\").html(c)\n\n        \$('#btnStart').html('<i class=\"fa fa-spinner fa-spin\"></i> Processing...').prop('disabled',\n            true);\n\n        for (var i = 0; i < 20; i++) {\n            check_live_uid_progress();\n        }\n\n        \$(\"#listId\").val(data.join(\"\\n\"))\n\n\n    })\n\n    function check_live_uid_progress() {\n\n\n        \$(\"#dieCount\").html(dies.length)\n\n        n = n + 1;\n        var m = n - 1;\n\n        if (!arrclone[m])\n            return;\n\n        var uid = get_uid(arrclone[m]);\n        var url = 'https://graph.facebook.com/' + uid + '/picture?type=normal';\n        fetch(url).then((response) => {\n            if (/100x100/.test(response.url)) {\n                \$('#liveCount').show();\n                live++;\n                \$('#liveCount').html(live);\n                \$('#listLive').append(arrclone[m] + \"\\n\");\n            } else {\n                \$('#dieCount').show();\n                die++;\n                \$('#dieCount').html(die);\n                \$('#listDie').append(arrclone[m] + \"\\n\");\n            }\n            // var r = \$(\".progress-bar\");\n            // var t = Math.floor(n * 100 / c);\n            // r.css(\"width\", t + \"%\"), jQuery(\"span\", r).html(t + \"%\");\n            if (n < c) {\n                check_live_uid_progress();\n            } else {\n                \$('#btnStart').html('<i class=\"fa-solid fa-play\"></i> Start ').prop('disabled', false);\n                return false;\n            }\n        });\n\n\n    }\n\n    function get_uid(data) {\n        if (data && data.includes(\"|\")) {\n            var clone = data.split(\"|\");\n            return clone[0];\n        }\n        return data;\n\n    }\n\n\n})\n</script>\n\n<script type=\"text/javascript\">\nnew ClipboardJS(\".copy\");\n\nfunction copy() {\n    showMessage(\"";
echo __("Đã sao chép vào bộ nhớ tạm");
echo "\", 'success');\n}\n</script>";

?>