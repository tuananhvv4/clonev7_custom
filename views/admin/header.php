<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
echo "\n<!doctype html>\n<html lang=\"en\" dir=\"ltr\" data-nav-layout=\"vertical\" data-theme-mode=\"light\" data-header-styles=\"light\"\n    data-menu-styles=\"dark\" data-toggled=\"close\">\n\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no'>\n    <meta name=\"robots\" content=\"noindex, nofollow\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <title>";
echo isset($body["title"]) ? $body["title"] : $CMSNT->site("title");
echo "</title>\n    <!-- Choices JS -->\n    <script src=\"";
echo base_url("public/theme/");
echo "assets/libs/choices.js/public/assets/scripts/choices.min.js\"></script>\n\n    <!-- Main Theme Js -->\n    <script src=\"";
echo base_url("public/theme/");
echo "assets/js/main.js\"></script>\n\n    <!-- Bootstrap Css -->\n    <link id=\"style\" href=\"";
echo base_url("public/theme/");
echo "assets/libs/bootstrap/css/bootstrap.min.css\" rel=\"stylesheet\">\n\n    <!-- Style Css -->\n    <link href=\"";
echo base_url("public/theme/");
echo "assets/css/styles.min.css\" rel=\"stylesheet\">\n\n    <!-- Icons Css -->\n    <link href=\"";
echo base_url("public/theme/");
echo "assets/css/icons.css\" rel=\"stylesheet\">\n\n    <!-- Node Waves Css -->\n    <link href=\"";
echo base_url("public/theme/");
echo "assets/libs/node-waves/waves.min.css\" rel=\"stylesheet\">\n\n    <!-- Simplebar Css -->\n    <link href=\"";
echo base_url("public/theme/");
echo "assets/libs/simplebar/simplebar.min.css\" rel=\"stylesheet\">\n\n    <!-- Color Picker Css -->\n    <link rel=\"stylesheet\" href=\"";
echo base_url("public/theme/");
echo "assets/libs/flatpickr/flatpickr.min.css\">\n    <link rel=\"stylesheet\" href=\"";
echo base_url("public/theme/");
echo "assets/libs/@simonwep/pickr/themes/nano.min.css\">\n\n    <!-- Prism CSS -->\n    <link rel=\"stylesheet\" href=\"";
echo base_url("public/theme/");
echo "assets/libs/prismjs/themes/prism-coy.min.css\">\n\n    <!-- Choices Css -->\n    <link rel=\"stylesheet\"\n        href=\"";
echo base_url("public/theme/");
echo "assets/libs/choices.js/public/assets/styles/choices.min.css\">\n\n    <link rel=\"stylesheet\" href=\"";
echo base_url("public/theme/");
echo "assets/libs/glightbox/css/glightbox.min.css\">\n\n    <!-- Simple Notify CSS -->\n    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/simple-notify@1.0.4/dist/simple-notify.css\" />\n    <!-- Simple Notify JS -->\n    <script src=\"https://cdn.jsdelivr.net/npm/simple-notify@1.0.4/dist/simple-notify.min.js\"></script>\n\n    <!-- Sweetalerts CSS -->\n    <link rel=\"stylesheet\" href=\"";
echo base_url("public/theme/");
echo "assets/libs/sweetalert2/sweetalert2.min.css\">\n    <!-- Sweetalerts JS -->\n    <script src=\"";
echo base_url("public/theme/");
echo "assets/libs/sweetalert2/sweetalert2.min.js\"></script>\n    <script src=\"";
echo base_url("public/theme/");
echo "assets/js/sweet-alerts.js\"></script>\n\n    <!-- SwiperJS Css -->\n    <link rel=\"stylesheet\" href=\"";
echo base_url("public/theme/");
echo "assets/libs/swiper/swiper-bundle.min.css\">\n\n    <link rel=\"stylesheet\" href=\"";
echo base_url("public/theme/");
echo "assets/css/styles.css\">\n    <!-- Cute Alert -->\n    <link class=\"main-stylesheet\" href=\"";
echo BASE_URL("public/");
echo "cute-alert/style.css\" rel=\"stylesheet\" type=\"text/css\">\n    <script src=\"";
echo BASE_URL("public/");
echo "cute-alert/cute-alert.js\"></script>\n\n    <script src=\"";
echo base_url("public/js/");
echo "jquery-3.6.0.js\"></script>\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/fontawesome/");
echo "css/all.min.css\">\n\n\n    <script src=\"";
echo BASE_URL("public/ckeditor/ckeditor.js");
echo "\"></script>\n    <script src=\"https://cdn.jsdelivr.net/npm/chart.js\"></script>\n\n    <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n<link href=\"https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap\" rel=\"stylesheet\">\n\n    ";
echo $body["header"];
echo "</head>\n<style>\nbody {\n    font-family: \"Roboto\", sans-serif;\n}\n</style>\n\n<style>\n/* Cho trình duyệt WebKit (Chrome, Safari) */\n::-webkit-scrollbar {\n    width: 15px;\n    /* Độ rộng của thanh cuộn */\n\n}\n\n::-webkit-scrollbar-thumb {\n    background-color: #c3c3c3;\n    /* Màu nền của thanh cuộn */\n}\n\n.top-filter {\n    display: -webkit-box;\n    display: -ms-flexbox;\n    display: flex;\n    -webkit-box-align: center;\n    -ms-flex-align: center;\n    align-items: center;\n    -webkit-box-pack: justify;\n    -ms-flex-pack: justify;\n    justify-content: space-between;\n    margin-bottom: 25px;\n}\n\n.filter-show {\n    width: 150px;\n    display: -webkit-box;\n    display: -ms-flexbox;\n    display: flex;\n    -webkit-box-align: center;\n    -ms-flex-align: center;\n    align-items: center;\n    -webkit-box-pack: center;\n    -ms-flex-pack: center;\n    justify-content: center;\n}\n\n.filter-label {\n    font-size: 14px;\n    font-weight: 500;\n    margin-right: 8px;\n    white-space: nowrap;\n    text-transform: uppercase;\n}\n\n.filter-select {\n    height: 40px;\n    background-color: transparent;\n}\n\n.filter-short {\n    width: 225px;\n    display: -webkit-box;\n    display: -ms-flexbox;\n    display: flex;\n    -webkit-box-align: center;\n    -ms-flex-align: center;\n    align-items: center;\n    -webkit-box-pack: center;\n    -ms-flex-pack: center;\n    justify-content: center;\n}\n\n.text-right {\n    text-align: right;\n}\n\n.tab-content .tab-pane {\n    padding: 0px;\n}\n\n.nav.tab-style-1 {\n    background-color: var(--white-9);\n}\n\n.nav.tab-style-1 .nav-link.active {\n    box-shadow: 3px 0rem 20px 0px rgb(10 10 10 / 38%);\n}\n\n.form-check-input {\n    background-color: #bdbdbd;\n}\n\n.table-responsive {\n    overflow-x: auto;\n    -webkit-overflow-scrolling: touch;\n}\n\n/* Customize scrollbar */\n.table-responsive::-webkit-scrollbar {\n    width: 10px;\n    /* Chiều rộng của thanh trượt */\n}\n\n/* Track */\n.table-responsive::-webkit-scrollbar-track {\n    background: #f1f1f1;\n    /* Màu nền của thanh trượt */\n}\n\n/* Handle */\n.table-responsive::-webkit-scrollbar-thumb {\n    background: #888;\n    /* Màu của phần cần kéo */\n    border-radius: 20px;\n    /* Độ cong của phần cần kéo */\n}\n\n/* Handle on hover */\n.table-responsive::-webkit-scrollbar-thumb:hover {\n    background: #555;\n    /* Màu của phần cần kéo khi di chuột qua */\n}\n\n.tab-content .tab-pane {\n    border: 0px solid var(--default-border);\n}\n\n.table-wrapper {\n    max-height: 700px;\n    /* Đặt chiều cao tối đa của bảng */\n    overflow-y: auto;\n    /* Kích hoạt thanh cuộn dọc khi nội dung vượt quá chiều cao */\n}\n\n.table-wrapper table {\n    width: 100%;\n}\n\nth,\ntd {\n    padding: 8px;\n    /* Để tránh lệch về bố cục khi cuộn */\n}\n\n/* Đảm bảo phần header cố định */\nthead {\n    position: sticky;\n    top: 0;\n    background-color: white;\n    z-index: 1;\n}\n\n/* Đảm bảo phần footer cố định */\ntfoot {\n    position: sticky;\n    bottom: 0;\n    background-color: white;\n    z-index: 1;\n}\n</style>\n<script>\nfunction showMessage(message, type) {\n    const commonOptions = {\n        effect: 'fade',\n        speed: 300,\n        customClass: null,\n        customIcon: null,\n        showIcon: true,\n        showCloseButton: true,\n        autoclose: true,\n        autotimeout: 3000,\n        gap: 20,\n        distance: 20,\n        type: 'outline',\n        position: 'right top'\n    };\n\n    const options = {\n        success: {\n            status: 'success',\n            title: 'Thành công!',\n            text: message,\n        },\n        error: {\n            status: 'error',\n            title: 'Thất bại!',\n            text: message,\n        }\n    };\n    new Notify(Object.assign({}, commonOptions, options[type]));\n}\n</script>";

?>