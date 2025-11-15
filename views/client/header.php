<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
echo "<!doctype html>\n<html class=\"h-100\">\n\n<head>\n    <meta charset=\"utf-8\" />\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">\n   <link rel=\"canonical\" href=\"";
// echo "<meta http-equiv=\"Content-Security-Policy\" content=\"upgrade-insecure-requests\">\n ";
echo url();
echo "\" />\n    <title>";
echo isset($body["title"]) ? $body["title"] : $CMSNT->site("title");
echo "</title>\n    <meta name=\"description\" content=\"";
echo isset($body["desc"]) ? $body["desc"] : $CMSNT->site("description");
echo "\" />\n    <meta name=\"keywords\" content=\"";
echo isset($body["keyword"]) ? $body["keyword"] : $CMSNT->site("keywords");
echo "\">\n    <meta name=\"copyright\" content=\"";
echo $CMSNT->site("author");
echo "\" />\n    <meta name=\"author\" content=\"";
echo $CMSNT->site("author");
echo "\" />\n    <meta property=\"og:url\" content=\"";
echo base_url("");
echo "\">\n    <meta property=\"og:site_name\" content=\"";
echo base_url();
echo "\" />\n    <meta property=\"og:title\" content=\"";
echo $body["title"];
echo "\" />\n    <meta property=\"og:type\" content=\"website\" />\n    <meta property=\"og:image\"\n        content=\"";
echo isset($body["image"]) ? $body["image"] : BASE_URL($CMSNT->site("image"));
echo "\" />\n    <meta property=\"og:image:secure\"\n        content=\"";
echo isset($body["image"]) ? $body["image"] : BASE_URL($CMSNT->site("image"));
echo "\" />\n    <meta name=\"twitter:title\" content=\"";
echo $body["title"];
echo "\" />\n    <meta name=\"twitter:image\"\n        content=\"";
echo isset($body["image"]) ? $body["image"] : BASE_URL($CMSNT->site("image"));
echo "\" />\n    <meta name=\"twitter:image:alt\" content=\"";
echo $body["title"];
echo "\" />\n    <link rel=\"icon\" type=\"image/png\" href=\"";
echo BASE_URL($CMSNT->site("favicon"));
echo "\" />\n    <style>\n    :root {\n        --primary: ";
echo $CMSNT->site("theme_color");
echo ";\n    }\n    </style>\n    <style>\n    :root {\n        --primary1: ";
echo $CMSNT->site("theme_color1");
echo ";\n    }\n    </style>\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "fonts/flaticon/flaticon.css\">\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "fonts/icofont/icofont.min.css\">\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "fonts/fontawesome/fontawesome.min.css\">\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "vendor/venobox/venobox.min.css\">\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "vendor/slickslider/slick.min.css\">\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "vendor/niceselect/nice-select.min.css\">\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "vendor/bootstrap/bootstrap.min.css\">\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "css/main.css\">\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "css/user-auth.css\">\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/client/");
echo "css/index.css\">\n\n    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("public/fontawesome/");
echo "css/all.min.css\">\n\n    <!-- sweetalert2-->\n    <link class=\"main-stylesheet\" href=\"";
echo BASE_URL("public/");
echo "sweetalert2/default.css\" rel=\"stylesheet\"\n        type=\"text/css\">\n    <script src=\"";
echo BASE_URL("public/");
echo "sweetalert2/sweetalert2.js\"></script>\n    <!-- Cute Alert -->\n    <link class=\"main-stylesheet\" href=\"";
echo BASE_URL("public/");
echo "cute-alert/style.css\" rel=\"stylesheet\" type=\"text/css\">\n    <script src=\"";
echo BASE_URL("public/");
echo "cute-alert/cute-alert.js\"></script>\n    <!-- Simple Notify CSS -->\n    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/simple-notify@1.0.4/dist/simple-notify.css\" />\n    <!-- Simple Notify JS -->\n    <script src=\"https://cdn.jsdelivr.net/npm/simple-notify@1.0.4/dist/simple-notify.min.js\"></script>\n\n    <!-- jQuery -->\n    <script src=\"";
echo base_url("public/js/jquery-3.6.0.js");
echo "\"></script>\n\n    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css\">\n    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css\">\n\n    <script src=\"";
echo base_url("public/js/main.js");
echo "\"></script>\n    ";
if($CMSNT->site("google_analytics_status") == 1) {
    echo "    <!-- Google tag (gtag.js) -->\n    <script async src=\"https://www.googletagmanager.com/gtag/js?id=";
    echo $CMSNT->site("google_analytics_id");
    echo "\"></script>\n    <script>\n    window.dataLayer = window.dataLayer || [];\n    function gtag(){dataLayer.push(arguments);}\n    gtag('js', new Date());\n\n    gtag('config', '";
    echo $CMSNT->site("google_analytics_id");
    echo "');\n    </script>\n    ";
}
echo "    ";
if($CMSNT->site("reCAPTCHA_status") == 1) {
    echo "    <!-- reCaptcha -->\n    <script src=\"https://www.google.com/recaptcha/api.js\" async defer></script>\n    ";
}
echo "    ";
echo $body["header"];
echo "    <link rel=\"stylesheet\" href=\"";
echo BASE_URL("mod/css/main.css?v=17");
echo "\">\n    <script src=\"";
echo base_url("mod/js/main.js");
echo "\"></script> \n    ";
echo $CMSNT->site("javascript_header");
echo "\n</head>\n\n<script>\nfunction showMessage(message, type) {\n  const commonOptions = {\n    effect: 'fade',\n    speed: 300,\n    customClass: null,\n    customIcon: null,\n    showIcon: true,\n    showCloseButton: true,\n    autoclose: true,\n    autotimeout: 3000,\n    gap: 20,\n    distance: 20,\n    type: 'outline',\n    position: 'right top'\n  };\n\n  const options = {\n    success: {\n      status: 'success',\n      title: '";
echo __("Thành công!");
echo "',\n      text: message,\n    },\n    error: {\n      status: 'error',\n      title: '";
echo __("Thất bại!");
echo "',\n      text: message,\n    }\n  };\n  new Notify(Object.assign({}, commonOptions, options[type]));\n}\n\n</script>\n\n<style>\nbody {\n    ";
echo $CMSNT->site("font_family");
echo "}\nhtml {\n  scroll-behavior: smooth;\n}\n\n.feature-content {\n    padding-left: 0px;\n    border-left: none;\n}\n.product-disable::before {\n    position: absolute;\n    content: \"";
echo __("Hết hàng");
echo "\";\n    top: 89%;\n    left: 50%;\n    z-index: 2;\n    width: 100%;\n    font-size: 15px;\n    font-weight: 400;\n    padding: 0px;\n    text-align: center;\n    text-transform: uppercase;\n    text-shadow: var(--primary-tshadow);\n    -webkit-transform: translate(-50%, -50%);\n    transform: translate(-50%, -50%);\n    color: var(--white);\n    background: rgba(224, 152, 22, 0.9);\n}\n</style>\n\n\n ";

?>