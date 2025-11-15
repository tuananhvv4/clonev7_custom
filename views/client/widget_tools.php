<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
echo "\n";
if($CMSNT->site("status_menu_tools") == 1) {
    echo "<div class=\"col-md-12\">\n    <div class=\"all-product-main row\">\n        <div class=\"col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-3\">\n            <div class=\"box-product-main\"\n                style=\"background-image: url(";
    echo base_url("mod/img/bg-1.png");
    echo ");box-shadow: 0 2px 10px rgb(36 194 138 / 19%);\">\n                <a href=\"";
    echo base_url("tool/check-live-facebook");
    echo "\" cursorshover=\"true\">\n                    <div class=\"d-flex align-items-center\">\n                        <div class=\"box-img-product\">\n                            <img src=\"";
    echo base_url("mod/img/icon-facebook.png");
    echo "\" alt=\"Accnice\" width=\"45\" height=\"45\">\n                        </div>\n                        <div class=\"box-text-style\">\n                            <div style=\"white-space: nowrap;color: #fff;font-weight: 700;font-size: 18px;\">\n                                ";
    echo __("Check live FB");
    echo "</div>\n                            <div style=\"white-space: nowrap;font-size: 15px;color: #fff;opacity: .7;\">\n                                ";
    echo __("Miễn phí");
    echo "</div>\n\n                        </div>\n                    </div>\n                </a>\n            </div>\n        </div>\n        <div class=\"col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-3\">\n            <div class=\"box-product-main\"\n                style=\"background-image: url(";
    echo base_url("mod/img/bg-2.png");
    echo ");box-shadow: 0 2px 10px rgb(0 147 255 / 27%);\">\n                <a href=\"";
    echo base_url("tool/get-2fa");
    echo "\" cursorshover=\"true\">\n                    <div class=\"d-flex align-items-center\">\n                        <div class=\"box-img-product\">\n                            <img src=\"";
    echo base_url("mod/img/icon-google-auth.png");
    echo "\" alt=\"Accnice\" width=\"45\"\n                                height=\"45\">\n                        </div>\n                        <div class=\"box-text-style\">\n\n                            <div style=\"white-space: nowrap;color: #fff;font-weight: 700;font-size: 18px;\">\n                                ";
    echo __("Lấy mã 2FA");
    echo "</div>\n\n                            <div style=\"white-space: nowrap;font-size: 15px;color: #fff;opacity: .7;\">\n                                ";
    echo __("Miễn phí");
    echo "                            </div>\n                        </div>\n                    </div>\n                </a>\n            </div>\n        </div>\n        <div class=\"col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-3\">\n            <div class=\"box-product-main\"\n                style=\"background-image: url(";
    echo base_url("mod/img/bg-3.png");
    echo ");box-shadow: 0 2px 10px rgb(0 147 255 / 27%);\">\n                <a href=\"";
    echo base_url("tool/icon-facebook");
    echo "\" cursorshover=\"true\">\n                    <div class=\"d-flex align-items-center\">\n                        <div class=\"box-img-product\">\n                            <img src=\"";
    echo base_url("mod/img/icon-facebook.png");
    echo "\" alt=\"Accnice\" width=\"45\" height=\"45\">\n                        </div>\n                        <div class=\"box-text-style\">\n                            <div style=\"white-space: nowrap;color: #fff;font-weight: 700;font-size: 18px;\">\n                                ";
    echo __("Icon Facebook");
    echo "</div>\n                            <div style=\"white-space: nowrap;font-size: 15px;color: #fff;opacity: .7;\">\n                                ";
    echo __("Miễn phí");
    echo "                            </div>\n                        </div>\n                    </div>\n                </a>\n            </div>\n        </div>\n        <div class=\"col-lg-3 col-md-6 col-sm-6 col-xs-12 mb-3\">\n            <div class=\"box-product-main\"\n                style=\"background-image: url(";
    echo base_url("mod/img/bg-4.png");
    echo ");box-shadow: 0 2px 10px rgb(0 147 255 / 27%);\">\n                <a href=\"";
    echo base_url("tool/random-face");
    echo "\" cursorshover=\"true\">\n                    <div class=\"d-flex align-items-center\">\n                        <div class=\"box-img-product\">\n                            <img src=\"";
    echo base_url("mod/img/icon-random-face.png");
    echo "\" alt=\"Accnice\" width=\"45\"\n                                height=\"45\">\n                        </div>\n                        <div class=\"box-text-style\">\n                            <div style=\"white-space: nowrap;color: #fff;font-weight: 700;font-size: 18px;\">\n                                ";
    echo __("Random Face");
    echo "</div>\n                            <div style=\"white-space: nowrap;font-size: 15px;color: #fff;opacity: .7;\">\n                                ";
    echo __("Miễn phí");
    echo "                            </div>\n                        </div>\n                    </div>\n                </a>\n            </div>\n        </div>\n    </div>\n</div>\n";
}

?>