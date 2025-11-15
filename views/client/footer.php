<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
echo "\n\n";
if($CMSNT->site("widget_phone1_status") == 1) {
    echo "<div class=\"hotline-phone-ring-wrap\">\n    <div class=\"hotline-phone-ring\">\n        <div class=\"hotline-phone-ring-circle\"></div>\n        <div class=\"hotline-phone-ring-circle-fill\"></div>\n        <div class=\"hotline-phone-ring-img-circle\">\n            <a href=\"tel:";
    echo $CMSNT->site("widget_phone1_sdt");
    echo "\" class=\"pps-btn-img\">\n                <img src=\"";
    echo base_url("mod/img/");
    echo "icon-call-nh.png\" alt=\"Gọi điện thoại\" width=\"50\">\n            </a>\n        </div>\n    </div>\n    <div class=\"hotline-bar\">\n        <a href=\"tel:";
    echo $CMSNT->site("widget_phone1_sdt");
    echo "\">\n            <span class=\"text-hotline\">";
    echo $CMSNT->site("widget_phone1_sdt");
    echo "</span>\n        </a>\n    </div>\n</div>\n\n<style>\n.hotline-phone-ring-wrap {\n    position: fixed;\n    bottom: 20px;\n    left: 0;\n    z-index: 999999;\n}\n\n.hotline-phone-ring {\n    position: relative;\n    visibility: visible;\n    background-color: transparent;\n    width: 110px;\n    height: 110px;\n    cursor: pointer;\n    z-index: 11;\n    -webkit-backface-visibility: hidden;\n    -webkit-transform: translateZ(0);\n    transition: visibility .5s;\n    left: 0;\n    bottom: 0;\n    display: block;\n}\n\n.hotline-phone-ring-circle {\n    width: 85px;\n    height: 85px;\n    top: 10px;\n    left: 10px;\n    position: absolute;\n    background-color: transparent;\n    border-radius: 100%;\n    border: 2px solid #e60808;\n    -webkit-animation: phonering-alo-circle-anim 1.2s infinite ease-in-out;\n    animation: phonering-alo-circle-anim 1.2s infinite ease-in-out;\n    transition: all .5s;\n    -webkit-transform-origin: 50% 50%;\n    -ms-transform-origin: 50% 50%;\n    transform-origin: 50% 50%;\n    opacity: 0.5;\n}\n\n.hotline-phone-ring-circle-fill {\n    width: 55px;\n    height: 55px;\n    top: 25px;\n    left: 25px;\n    position: absolute;\n    background-color: rgba(230, 8, 8, 0.7);\n    border-radius: 100%;\n    border: 2px solid transparent;\n    -webkit-animation: phonering-alo-circle-fill-anim 2.3s infinite ease-in-out;\n    animation: phonering-alo-circle-fill-anim 2.3s infinite ease-in-out;\n    transition: all .5s;\n    -webkit-transform-origin: 50% 50%;\n    -ms-transform-origin: 50% 50%;\n    transform-origin: 50% 50%;\n}\n\n.hotline-phone-ring-img-circle {\n    background-color: #e60808;\n    width: 33px;\n    height: 33px;\n    top: 37px;\n    left: 37px;\n    position: absolute;\n    background-size: 20px;\n    border-radius: 100%;\n    border: 2px solid transparent;\n    -webkit-animation: phonering-alo-circle-img-anim 1s infinite ease-in-out;\n    animation: phonering-alo-circle-img-anim 1s infinite ease-in-out;\n    -webkit-transform-origin: 50% 50%;\n    -ms-transform-origin: 50% 50%;\n    transform-origin: 50% 50%;\n    display: -webkit-box;\n    display: -webkit-flex;\n    display: -ms-flexbox;\n    display: flex;\n    align-items: center;\n    justify-content: center;\n}\n\n.hotline-phone-ring-img-circle .pps-btn-img {\n    display: -webkit-box;\n    display: -webkit-flex;\n    display: -ms-flexbox;\n    display: flex;\n}\n\n.hotline-phone-ring-img-circle .pps-btn-img img {\n    width: 20px;\n    height: 20px;\n}\n\n.hotline-bar {\n    position: absolute;\n    background: rgba(230, 8, 8, 0.75);\n    height: 40px;\n    width: 180px;\n    line-height: 40px;\n    border-radius: 3px;\n    padding: 0 10px;\n    background-size: 100%;\n    cursor: pointer;\n    transition: all 0.8s;\n    -webkit-transition: all 0.8s;\n    z-index: 9;\n    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.1);\n    border-radius: 50px !important;\n    /* width: 175px !important; */\n    left: 33px;\n    bottom: 37px;\n}\n\n.hotline-bar>a {\n    color: #fff;\n    text-decoration: none;\n    font-size: 15px;\n    font-weight: bold;\n    text-indent: 50px;\n    display: block;\n    letter-spacing: 1px;\n    line-height: 40px;\n    font-family: Arial;\n}\n\n.hotline-bar>a:hover,\n.hotline-bar>a:active {\n    color: #fff;\n}\n\n@-webkit-keyframes phonering-alo-circle-anim {\n    0% {\n        -webkit-transform: rotate(0) scale(0.5) skew(1deg);\n        -webkit-opacity: 0.1;\n    }\n\n    30% {\n        -webkit-transform: rotate(0) scale(0.7) skew(1deg);\n        -webkit-opacity: 0.5;\n    }\n\n    100% {\n        -webkit-transform: rotate(0) scale(1) skew(1deg);\n        -webkit-opacity: 0.1;\n    }\n}\n\n@-webkit-keyframes phonering-alo-circle-fill-anim {\n    0% {\n        -webkit-transform: rotate(0) scale(0.7) skew(1deg);\n        opacity: 0.6;\n    }\n\n    50% {\n        -webkit-transform: rotate(0) scale(1) skew(1deg);\n        opacity: 0.6;\n    }\n\n    100% {\n        -webkit-transform: rotate(0) scale(0.7) skew(1deg);\n        opacity: 0.6;\n    }\n}\n\n@-webkit-keyframes phonering-alo-circle-img-anim {\n    0% {\n        -webkit-transform: rotate(0) scale(1) skew(1deg);\n    }\n\n    10% {\n        -webkit-transform: rotate(-25deg) scale(1) skew(1deg);\n    }\n\n    20% {\n        -webkit-transform: rotate(25deg) scale(1) skew(1deg);\n    }\n\n    30% {\n        -webkit-transform: rotate(-25deg) scale(1) skew(1deg);\n    }\n\n    40% {\n        -webkit-transform: rotate(25deg) scale(1) skew(1deg);\n    }\n\n    50% {\n        -webkit-transform: rotate(0) scale(1) skew(1deg);\n    }\n\n    100% {\n        -webkit-transform: rotate(0) scale(1) skew(1deg);\n    }\n}\n\n@media (max-width: 768px) {\n    .hotline-bar {\n        display: none;\n    }\n}\n</style>\n";
}
echo "\n\n";
if($CMSNT->site("widget_zalo1_status") == 1) {
    echo "<a href=\"https://chat.zalo.me/?phone=";
    echo $CMSNT->site("widget_zalo1_sdt");
    echo "\" id=\"linkzalo\" target=\"_blank\"\n    rel=\"noopener noreferrer\">\n    <div id=\"fcta-zalo-tracking\" class=\"fcta-zalo-mess\">\n        <span id=\"fcta-zalo-tracking\">";
    echo __("Chat hỗ trợ");
    echo "</span>\n    </div>\n    <div class=\"fcta-zalo-vi-tri-nut\">\n        <div id=\"fcta-zalo-tracking\" class=\"fcta-zalo-nen-nut\">\n            <div id=\"fcta-zalo-tracking\" class=\"fcta-zalo-ben-trong-nut\"> <svg xmlns=\"http://www.w3.org/2000/svg\"\n                    viewBox=\"0 0 460.1 436.6\">\n                    <path fill=\"currentColor\" class=\"st0\"\n                        d=\"M82.6 380.9c-1.8-.8-3.1-1.7-1-3.5 1.3-1 2.7-1.9 4.1-2.8 13.1-8.5 25.4-17.8 33.5-31.5 6.8-11.4 5.7-18.1-2.8-26.5C69 269.2 48.2 212.5 58.6 145.5 64.5 107.7 81.8 75 107 46.6c15.2-17.2 33.3-31.1 53.1-42.7 1.2-.7 2.9-.9 3.1-2.7-.4-1-1.1-.7-1.7-.7-33.7 0-67.4-.7-101 .2C28.3 1.7.5 26.6.6 62.3c.2 104.3 0 208.6 0 313 0 32.4 24.7 59.5 57 60.7 27.3 1.1 54.6.2 82 .1 2 .1 4 .2 6 .2H290c36 0 72 .2 108 0 33.4 0 60.5-27 60.5-60.3v-.6-58.5c0-1.4.5-2.9-.4-4.4-1.8.1-2.5 1.6-3.5 2.6-19.4 19.5-42.3 35.2-67.4 46.3-61.5 27.1-124.1 29-187.6 7.2-5.5-2-11.5-2.2-17.2-.8-8.4 2.1-16.7 4.6-25 7.1-24.4 7.6-49.3 11-74.8 6zm72.5-168.5c1.7-2.2 2.6-3.5 3.6-4.8 13.1-16.6 26.2-33.2 39.3-49.9 3.8-4.8 7.6-9.7 10-15.5 2.8-6.6-.2-12.8-7-15.2-3-.9-6.2-1.3-9.4-1.1-17.8-.1-35.7-.1-53.5 0-2.5 0-5 .3-7.4.9-5.6 1.4-9 7.1-7.6 12.8 1 3.8 4 6.8 7.8 7.7 2.4.6 4.9.9 7.4.8 10.8.1 21.7 0 32.5.1 1.2 0 2.7-.8 3.6 1-.9 1.2-1.8 2.4-2.7 3.5-15.5 19.6-30.9 39.3-46.4 58.9-3.8 4.9-5.8 10.3-3 16.3s8.5 7.1 14.3 7.5c4.6.3 9.3.1 14 .1 16.2 0 32.3.1 48.5-.1 8.6-.1 13.2-5.3 12.3-13.3-.7-6.3-5-9.6-13-9.7-14.1-.1-28.2 0-43.3 0zm116-52.6c-12.5-10.9-26.3-11.6-39.8-3.6-16.4 9.6-22.4 25.3-20.4 43.5 1.9 17 9.3 30.9 27.1 36.6 11.1 3.6 21.4 2.3 30.5-5.1 2.4-1.9 3.1-1.5 4.8.6 3.3 4.2 9 5.8 14 3.9 5-1.5 8.3-6.1 8.3-11.3.1-20 .2-40 0-60-.1-8-7.6-13.1-15.4-11.5-4.3.9-6.7 3.8-9.1 6.9zm69.3 37.1c-.4 25 20.3 43.9 46.3 41.3 23.9-2.4 39.4-20.3 38.6-45.6-.8-25-19.4-42.1-44.9-41.3-23.9.7-40.8 19.9-40 45.6zm-8.8-19.9c0-15.7.1-31.3 0-47 0-8-5.1-13-12.7-12.9-7.4.1-12.3 5.1-12.4 12.8-.1 4.7 0 9.3 0 14v79.5c0 6.2 3.8 11.6 8.8 12.9 6.9 1.9 14-2.2 15.8-9.1.3-1.2.5-2.4.4-3.7.2-15.5.1-31 .1-46.5z\">\n                    </path>\n                </svg></div>\n            <div id=\"fcta-zalo-tracking\" class=\"fcta-zalo-text\">";
    echo __("Chat ngay");
    echo "</div>\n        </div>\n    </div>\n</a>\n\n<style>\n@keyframes zoom {\n    0% {\n        transform: scale(.5);\n        opacity: 0\n    }\n\n    50% {\n        opacity: 1\n    }\n\n    to {\n        opacity: 0;\n        transform: scale(1)\n    }\n}\n\n@keyframes lucidgenzalo {\n    0% to {\n        transform: rotate(-25deg)\n    }\n\n    50% {\n        transform: rotate(25deg)\n    }\n}\n\n.jscroll-to-top {\n    bottom: 100px\n}\n\n.fcta-zalo-ben-trong-nut svg path {\n    fill: #fff\n}\n\n.fcta-zalo-vi-tri-nut {\n    position: fixed;\n    bottom: 71px;\n    right: 20px;\n    z-index: 999\n}\n\n.fcta-zalo-nen-nut,\ndiv.fcta-zalo-mess {\n    box-shadow: 0 1px 6px rgba(0, 0, 0, .06), 0 2px 32px rgba(0, 0, 0, .16)\n}\n\n.fcta-zalo-nen-nut {\n    width: 50px;\n    height: 50px;\n    text-align: center;\n    color: #fff;\n    background: #0068ff;\n    border-radius: 50%;\n    position: relative\n}\n\n.fcta-zalo-nen-nut::after,\n.fcta-zalo-nen-nut::before {\n    content: \"\";\n    position: absolute;\n    border: 1px solid #0068ff;\n    background: #0068ff80;\n    z-index: -1;\n    left: -20px;\n    right: -20px;\n    top: -20px;\n    bottom: -20px;\n    border-radius: 50%;\n    animation: zoom 1.9s linear infinite\n}\n\n.fcta-zalo-nen-nut::after {\n    animation-delay: .4s\n}\n\n.fcta-zalo-ben-trong-nut,\n.fcta-zalo-ben-trong-nut i {\n    transition: all 1s\n}\n\n.fcta-zalo-ben-trong-nut {\n    position: absolute;\n    text-align: center;\n    width: 60%;\n    height: 60%;\n    left: 10px;\n    bottom: 31px;\n    line-height: 70px;\n    font-size: 25px;\n    opacity: 1\n}\n\n.fcta-zalo-ben-trong-nut i {\n    animation: lucidgenzalo 1s linear infinite\n}\n\n.fcta-zalo-nen-nut:hover .fcta-zalo-ben-trong-nut,\n.fcta-zalo-text {\n    opacity: 0\n}\n\n.fcta-zalo-nen-nut:hover i {\n    transform: scale(.5);\n    transition: all .5s ease-in\n}\n\n.fcta-zalo-text a {\n    text-decoration: none;\n    color: #fff\n}\n\n.fcta-zalo-text {\n    position: absolute;\n    top: 6px;\n    text-transform: uppercase;\n    font-size: 12px;\n    font-weight: 700;\n    transform: scaleX(-1);\n    transition: all .5s;\n    line-height: 1.5\n}\n\n.fcta-zalo-nen-nut:hover .fcta-zalo-text {\n    transform: scaleX(1);\n    opacity: 1\n}\n\ndiv.fcta-zalo-mess {\n    position: fixed;\n    bottom: 75px;\n    right: 58px;\n    z-index: 99;\n    background: #fff;\n    padding: 7px 25px 7px 15px;\n    color: #0068ff;\n    border-radius: 50px 0 0 50px;\n    font-weight: 700;\n    font-size: 15px\n}\n\n.fcta-zalo-mess span {\n    color: #0068ff !important\n}\n\nspan#fcta-zalo-tracking {\n    font-family: Roboto;\n    line-height: 1.5\n}\n\n.fcta-zalo-text {\n    font-family: Roboto\n}\n</style>\n\n<script>\nif (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {\n    document.getElementById(\"linkzalo\").href = \"https://zalo.me/";
    echo $CMSNT->site("widget_zalo1_sdt");
    echo "\";\n}\n</script>\n";
}
echo "\n";
if($CMSNT->site("widget_fbzalo2_status") == 1) {
    echo "<style>\n.giuseart-pc-contact-bar{\nleft: 9px;\nbottom: 59px;\nposition: fixed;\nz-index: 998;\nmargin-bottom:0\n}\n.giuseart-pc-contact-bar li{\nwidth: 44px;\nheight: 46px;\noverflow: hidden;\n margin-bottom: 0;\nlist-style: none;\n}\n.giuseart-pc-contact-bar li.facebook{\nmargin-bottom: 8px;\nbackground: url(";
    echo base_url("mod/img/icon-mess.webp");
    echo ");\nbackground-repeat: no-repeat;\n}\n.giuseart-pc-contact-bar li.zalo{\nbackground: url(";
    echo base_url("mod/img/icon-chat-zalo.webp");
    echo ");    background-repeat: no-repeat;\n}\n.giuseart-pc-contact-bar li a{\ndisplay: block;\nwidth: 44px;\nheight: 44px;\n}\n\n</style>\n<ul class=\"giuseart-pc-contact-bar\">\n<li class=\"facebook\">\n<a href=\"";
    echo $CMSNT->site("widget_fbzalo2_fb");
    echo "\" target=\"_blank\" rel=\"nofollow\"></a>\n</li>\n<li class=\"zalo\">\n<a href=\"";
    echo $CMSNT->site("widget_fbzalo2_zalo");
    echo "\" target=\"_blank\" rel=\"nofollow\"></a>\n</li>\n</ul>\n";
}
echo "\n\n<footer class=\"footer-part\">\n    <div class=\"container\">\n        <div class=\"row\">\n            <div class=\"col-sm-6 col-xl-4\">\n                <div class=\"footer-widget\">\n                    <a class=\"footer-logo\" href=\"";
echo base_url();
echo "\">\n                        <img src=\"";
echo BASE_URL($CMSNT->site("logo_light"));
echo "\" alt=\"logo\"></a>\n                    <p class=\"footer-desc\">\n                        ";
echo $CMSNT->site("description");
echo "</p>\n                </div>\n            </div>\n            <div class=\"col-sm-6 col-xl-4\">\n                <div class=\"footer-widget contact\">\n                    <h3 class=\"footer-title\">";
echo __("Liên hệ");
echo "</h3>\n                    <ul class=\"footer-contact\">\n                        <li><i class=\"icofont-ui-email\"></i>\n                            <p>";
echo $CMSNT->site("email");
echo "</p>\n                        </li>\n                        <li><i class=\"icofont-ui-touch-phone\"></i>\n                            <p>";
echo $CMSNT->site("hotline");
echo "</p>\n                        </li>\n                        <li><i class=\"icofont-location-pin\"></i>\n                            <p>";
echo $CMSNT->site("address");
echo "</p>\n                        </li>\n                    </ul>\n                </div>\n            </div>\n            <div class=\"col-sm-6 col-xl-4\">\n                <div class=\"footer-widget\">\n                    <h3 class=\"footer-title\">";
echo __("Liên kết");
echo "</h3>\n                    <div class=\"footer-links\">\n                        <ul>\n                            <li><a href=\"";
echo base_url("client/policy");
echo "\">";
echo __("Chính sách");
echo "</a></li>\n                            <li><a href=\"";
echo base_url("client/faq");
echo "\">";
echo __("Câu hỏi thường gặp");
echo "</a></li>\n                            <li><a href=\"";
echo base_url("client/contact");
echo "\">";
echo __("Liên hệ chúng tôi");
echo "</a></li>\n                            <li><a href=\"";
echo base_url("client/document-api");
echo "\">";
echo __("Tài liệu API");
echo "</a></li>\n                        </ul>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <div class=\"row\">\n            <div class=\"col-12\">\n                <div class=\"footer-bottom\">\n                    <p class=\"footer-copytext\">© All Copyrights Reserved by <a href=\"#\">";
echo $CMSNT->site("title");
echo "</a>\n                        | ";
echo $CMSNT->site("copyright_footer");
echo "</p>\n                    <div class=\"footer-card\">\n                        ";
echo $CMSNT->site("footer_card");
echo "\n\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</footer>\n<script src=\"";
echo BASE_URL("public/client/");
echo "vendor/bootstrap/jquery-1.12.4.min.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "vendor/bootstrap/popper.min.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "vendor/bootstrap/bootstrap.min.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "vendor/countdown/countdown.min.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "vendor/niceselect/nice-select.min.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "vendor/slickslider/slick.min.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "vendor/venobox/venobox.min.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "js/nice-select.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "js/countdown.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "js/accordion.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "js/venobox.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "js/slick.js\"></script>\n<script src=\"";
echo BASE_URL("public/client/");
echo "js/main.js\"></script>\n<script src=\"https://cdn.jsdelivr.net/npm/flatpickr\"></script>\n<script>\nflatpickr(\"#example-flatpickr-range\");\n</script>\n<script src=\"https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js\"></script>\n\n";
echo $body["footer"];
echo $CMSNT->site("javascript_footer");
echo "</body>\n\n</html>";

?>