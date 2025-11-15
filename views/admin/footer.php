<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
echo "\n";
echo $CMSNT->site("script_footer_admin");
echo "\n\n\n<!-- Footer Start -->\n<footer class=\"footer mt-auto py-3 bg-white text-center\">\n    <div class=\"container\">\n        <span class=\"text-muted\"> Copyright © <span id=\"year\"></span> <a href=\"#\"\n                class=\"text-dark fw-semibold\">";
echo $CMSNT->site("title");
echo "</a>.\n            Software by <a href=\"https://www.cmsnt.co/\">\n                <span class=\"fw-semibold text-primary text-decoration-underline\">CMSNT.CO</span> 🇻🇳\n            </a> All\n            rights\n            reserved\n        </span>\n        <div class=\"gtranslate_wrapper\"></div>\n        <script>\n        window.gtranslateSettings = {\n            \"default_language\": \"vi\",\n            \"languages\": [\"vi\", \"en\", \"th\", \"ms\", \"zh-CN\", \"tl\", \"de\", \"km\", \"ru\", \"my\", \"lo\", \"tr\", \"uk\", \"ko\",\n                \"zh-TW\", \"it\", \"fr\", \"ar\"\n            ],\n            \"wrapper_selector\": \".gtranslate_wrapper\"\n        }\n        </script>\n        <script src=\"https://cdn.gtranslate.net/widgets/latest/flags.js\" defer></script>\n    </div>\n</footer>\n<!-- Footer End -->\n\n</div>\n\n<!-- Scroll To Top -->\n<div class=\"scrollToTop\">\n    <span class=\"arrow\"><i class=\"ri-arrow-up-s-fill fs-20\"></i></span>\n</div>\n<div id=\"responsive-overlay\"></div>\n<!-- Scroll To Top -->\n\n";
if($CMSNT->site("status_update") == 1) {
    echo "<script>\n    \$(document).ready(function(){\n        \$.ajax({\n            url: '";
    echo base_url("update.php");
    echo "',\n            type: 'GET',\n            timeout: 4000,\n            success: function(response) {\n                if(response == 'Cập nhật thành công!'){\n                    showMessage('Cập nhật phiên bản thành công!', 'success');\n                }\n            },\n            error: function(xhr, status, error) {\n\n            }\n        });\n        \$.ajax({\n            url: \"";
    echo BASE_URL("install.php");
    echo "\",\n            type: \"GET\",\n            success: function(result) {\n            }\n        });\n    });\n</script>\n";
}
echo "\n\n<script>\n\n</script>\n<!-- Popper JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/@popperjs/core/umd/popper.min.js\"></script>\n\n<!-- Bootstrap JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/bootstrap/js/bootstrap.bundle.min.js\"></script>\n\n<!-- Defaultmenu JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/js/defaultmenu.min.js\"></script>\n\n<!-- Node Waves JS-->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/node-waves/waves.min.js\"></script>\n\n<!-- Sticky JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/js/sticky.js\"></script>\n\n<!-- Simplebar JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/simplebar/simplebar.min.js\"></script>\n<script src=\"";
echo base_url("public/theme/");
echo "assets/js/simplebar.js\"></script>\n\n<!-- Color Picker JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/@simonwep/pickr/pickr.es5.min.js\"></script>\n\n<!-- Custom-Switcher JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/js/custom-switcher.min.js\"></script>\n<!-- Internal Swiper JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/js/swiper.js\"></script>\n\n<!-- Custom JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/js/custom.js\"></script>\n\n\n<!-- Prism JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/prismjs/prism.js\"></script>\n<script src=\"";
echo base_url("public/theme/");
echo "assets/js/prism-custom.js\"></script>\n\n<!-- Modal JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/js/modal.js\"></script>\n\n<!-- Date & Time Picker JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/flatpickr/flatpickr.min.js\"></script>\n<script src=\"";
echo base_url("public/theme/");
echo "assets/js/date&time_pickers.js\"></script>\n\n<!-- Chartjs Chart JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/chart.js/chart.min.js\"></script>\n\n<!-- Gallery JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/glightbox/js/glightbox.min.js\"></script>\n\n<!-- Choices JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/choices.js/public/assets/scripts/choices.min.js\"></script>\n\n<!-- Swiper JS -->\n<script src=\"";
echo base_url("public/theme/");
echo "assets/libs/swiper/swiper-bundle.min.js\"></script>\n\n";
echo $body["footer"];
echo "\n<script>\n\$(document).ready(function() {\n    \$('[data-toggle=\"tooltip\"]').tooltip();\n});\n</script>\n</body>\n\n</html>";

?>