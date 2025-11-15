<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
require_once __DIR__ . "/../../libs/sendEmail.php";
$CMSNT = new DB();
$Mobile_Detect = new Mobile_Detect();
if(isset($_POST["action"])) {
    if($CMSNT->site("status") != 1 && !isset($_SESSION["admin_login"])) {
        exit(json_encode(["status" => "error", "msg" => __("The system is under maintenance, please come back later!")]));
    }
    if($_POST["action"] == "Login") {
        $username = check_string($_POST["username"]);
        $password = check_string($_POST["password"]);
        if(!($username = check_string($_POST["username"]))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập username")]));
        }
        if(empty($_POST["password"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập mật khẩu")]));
        }
        if($CMSNT->site("reCAPTCHA_status") == 1) {
            if(empty($_POST["recaptcha"])) {
                exit(json_encode(["status" => "error", "msg" => __("Vui lòng xác minh Captcha")]));
            }
            $recaptcha = check_string($_POST["recaptcha"]);
            $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . $CMSNT->site("reCAPTCHA_secret_key") . "&response=" . $recaptcha;
            $verify = file_get_contents($url);
            $captcha_success = json_decode($verify);
            if(!$captcha_success->success) {
                exit(json_encode(["status" => "error", "msg" => __("Vui lòng xác minh Captcha")]));
            }
        }
        $getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `username` = '" . $username . "' ");
        if(!$getUser) {
            $ip_address = myip();
            $max_attempts = $CMSNT->site("limit_block_ip_login");
            $lockout_time = 900;
            $attempt = $CMSNT->get_row("SELECT * FROM `failed_attempts` WHERE `ip_address` = '" . $ip_address . "' AND `type` = 'Login' ");
            if($attempt && $max_attempts <= $attempt["attempts"] && time() - strtotime($attempt["create_gettime"]) < $lockout_time) {
                $CMSNT->insert("block_ip", ["ip" => $ip_address, "attempts" => $attempt["attempts"], "create_gettime" => gettime(), "banned" => 1, "reason" => __("Đăng nhập thất bại nhiều lần")]);
                $CMSNT->remove("failed_attempts", " `ip_address` = '" . $ip_address . "' ");
                exit(json_encode(["status" => "error", "msg" => __("IP của bạn đã bị khóa. Vui lòng thử lại sau.")]));
            }
            if($attempt) {
                $CMSNT->cong("failed_attempts", "attempts", 1, " `ip_address` = '" . $ip_address . "' ");
            } else {
                $CMSNT->insert("failed_attempts", ["ip_address" => $ip_address, "attempts" => 1, "type" => "Login", "create_gettime" => gettime()]);
            }
            exit(json_encode(["status" => "error", "msg" => __("Thông tin đăng nhập không chính xác")]));
        }
        if($getUser["time_request"] < time() && time() - $getUser["time_request"] < $config["max_time_load"]) {
            exit(json_encode(["status" => "error", "msg" => __("Bạn đang thao tác quá nhanh, vui lòng đợi")]));
        }
        if($CMSNT->site("type_password") == "bcrypt") {
            if(!password_verify($password, $getUser["password"])) {
                $ip_address = myip();
                $max_attempts = $CMSNT->site("limit_block_ip_login");
                $lockout_time = 900;
                $attempt = $CMSNT->get_row("SELECT * FROM `failed_attempts` WHERE `ip_address` = '" . $ip_address . "' AND `type` = 'Login' ");
                if($attempt && $max_attempts <= $attempt["attempts"] && time() - strtotime($attempt["create_gettime"]) < $lockout_time) {
                    $CMSNT->insert("block_ip", ["ip" => $ip_address, "attempts" => $attempt["attempts"], "create_gettime" => gettime(), "banned" => 1, "reason" => __("Đăng nhập thất bại nhiều lần")]);
                    $CMSNT->remove("failed_attempts", " `ip_address` = '" . $ip_address . "' ");
                    exit(json_encode(["status" => "error", "msg" => __("IP của bạn đã bị khóa. Vui lòng thử lại sau.")]));
                }
                if($attempt) {
                    $CMSNT->cong("failed_attempts", "attempts", 1, " `ip_address` = '" . $ip_address . "' ");
                } else {
                    $CMSNT->insert("failed_attempts", ["ip_address" => $ip_address, "attempts" => 1, "type" => "Login", "create_gettime" => gettime()]);
                }
                if($CMSNT->site("limit_block_client_login") <= $getUser["login_attempts"]) {
                    $User = new users();
                    $User->Banned($getUser["id"], __("Đăng nhập thất bại nhiều lần"));
                    exit(json_encode(["status" => "error", "msg" => __("Tài khoản của bạn đã bị tạm khoá do đang nhập sai nhiều lần")]));
                }
                $CMSNT->cong("users", "login_attempts", 1, " `id` = '" . $getUser["id"] . "' ");
                exit(json_encode(["status" => "error", "msg" => __("Thông tin đăng nhập không chính xác")]));
            }
        } elseif($getUser["password"] != TypePassword($password)) {
            $ip_address = myip();
            $max_attempts = $CMSNT->site("limit_block_ip_login");
            $lockout_time = 900;
            $attempt = $CMSNT->get_row("SELECT * FROM `failed_attempts` WHERE `ip_address` = '" . $ip_address . "' AND `type` = 'Login' ");
            if($attempt && $max_attempts <= $attempt["attempts"] && time() - strtotime($attempt["create_gettime"]) < $lockout_time) {
                $CMSNT->insert("block_ip", ["ip" => $ip_address, "attempts" => $attempt["attempts"], "create_gettime" => gettime(), "banned" => 1, "reason" => __("Đăng nhập thất bại nhiều lần")]);
                $CMSNT->remove("failed_attempts", " `ip_address` = '" . $ip_address . "' ");
                exit(json_encode(["status" => "error", "msg" => __("IP của bạn đã bị khóa. Vui lòng thử lại sau.")]));
            }
            if($attempt) {
                $CMSNT->cong("failed_attempts", "attempts", 1, " `ip_address` = '" . $ip_address . "' ");
            } else {
                $CMSNT->insert("failed_attempts", ["ip_address" => $ip_address, "attempts" => 1, "type" => "Login", "create_gettime" => gettime()]);
            }
            if($CMSNT->site("limit_block_client_login") <= $getUser["login_attempts"]) {
                $User = new users();
                $User->Banned($getUser["id"], __("Đăng nhập thất bại nhiều lần"));
                exit(json_encode(["status" => "error", "msg" => __("Tài khoản của bạn đã bị tạm khoá do đang nhập sai nhiều lần")]));
            }
            $CMSNT->cong("users", "login_attempts", 1, " `id` = '" . $getUser["id"] . "' ");
            exit(json_encode(["status" => "error", "msg" => __("Thông tin đăng nhập không chính xác")]));
        }
        if($getUser["banned"] == 1) {
            exit(json_encode(["status" => "error", "msg" => __("Tài khoản của bạn đã bị khoá truy cập")]));
        }
        if($getUser["status_otp_mail"] == 1) {
            $otp_mail = random("QWERTYUOPASDFGHJKZXCVBNM0126456789", 6);
            $token_otp_mail = md5(uniqid()) . md5(random("QWERTYUOPASDFGHJKZXCVBNM0126456789", 12));
            $CMSNT->update("users", ["token_otp_mail" => $token_otp_mail, "otp_mail" => $otp_mail, "limit_otp_mail" => 0], " `id` = '" . $getUser["id"] . "' ");
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "[Warning] " . __("Đăng nhập thành công - đang tiến hành đến bước xác minh OTP Mail")]);
            if($CMSNT->site("email_temp_subject_otp_mail") != "") {
                $content = $CMSNT->site("email_temp_content_otp_mail");
                $content = str_replace("{domain}", $_SERVER["SERVER_NAME"], $content);
                $content = str_replace("{title}", $CMSNT->site("title"), $content);
                $content = str_replace("{username}", $getUser["username"], $content);
                $content = str_replace("{otp}", $otp_mail, $content);
                $content = str_replace("{ip}", myip(), $content);
                $content = str_replace("{device}", $Mobile_Detect->getUserAgent(), $content);
                $content = str_replace("{time}", gettime(), $content);
                $subject = $CMSNT->site("email_temp_subject_otp_mail");
                $subject = str_replace("{domain}", $_SERVER["SERVER_NAME"], $subject);
                $subject = str_replace("{title}", $CMSNT->site("title"), $subject);
                $subject = str_replace("{username}", $getUser["username"], $subject);
                $subject = str_replace("{otp}", $otp_mail, $subject);
                $subject = str_replace("{ip}", myip(), $subject);
                $subject = str_replace("{device}", $Mobile_Detect->getUserAgent(), $subject);
                $subject = str_replace("{time}", gettime(), $subject);
                $bcc = $CMSNT->site("title");
                sendCSM($getUser["email"], $getUser["username"], $subject, $content, $bcc);
            }
            exit(json_encode(["status" => "verify", "url" => base_url("?action=verify_otp&token=" . $token_otp_mail), "msg" => __("Vui lòng xác minh OTP để hoàn tất quá trình đăng nhập")]));
        }
        if($getUser["status_2fa"] == 1) {
            $token_2fa = md5(random("qwertyuiopasdfghjklzxcvbnm0123456789", 55)) . md5(uniqid());
            $CMSNT->update("users", ["token_2fa" => $token_2fa, "limit_2fa" => 0], " `id` = '" . $getUser["id"] . "' ");
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "[Warning] " . __("Đăng nhập thành công - đang tiến hành đến bước xác minh 2FA")]);
            exit(json_encode(["status" => "verify", "url" => base_url("?action=verify_2fa&token=" . $token_2fa), "msg" => __("Vui lòng xác minh 2FA để hoàn tất quá trình đăng nhập")]));
        }
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "[Warning] " . __("Thực hiện đăng nhập vào website")]);
        $CMSNT->update("users", ["token_2fa" => NULL, "limit_2fa" => 0, "token_otp_mail" => NULL, "limit_otp_mail" => 0, "otp_mail" => NULL, "ip" => myip(), "time_request" => time(), "time_session" => time(), "device" => $Mobile_Detect->getUserAgent()], " `id` = '" . $getUser["id"] . "' ");
        if($getUser["status_noti_login_to_mail"] == 1 && $CMSNT->site("email_temp_subject_warning_login") != "") {
            $content = $CMSNT->site("email_temp_content_warning_login");
            $content = str_replace("{domain}", $_SERVER["SERVER_NAME"], $content);
            $content = str_replace("{title}", $CMSNT->site("title"), $content);
            $content = str_replace("{username}", $getUser["username"], $content);
            $content = str_replace("{ip}", myip(), $content);
            $content = str_replace("{device}", $Mobile_Detect->getUserAgent(), $content);
            $content = str_replace("{time}", gettime(), $content);
            $subject = $CMSNT->site("email_temp_subject_warning_login");
            $subject = str_replace("{domain}", $_SERVER["SERVER_NAME"], $subject);
            $subject = str_replace("{title}", $CMSNT->site("title"), $subject);
            $subject = str_replace("{username}", $getUser["username"], $subject);
            $subject = str_replace("{ip}", myip(), $subject);
            $subject = str_replace("{device}", $Mobile_Detect->getUserAgent(), $subject);
            $subject = str_replace("{time}", gettime(), $subject);
            $bcc = $CMSNT->site("title");
            sendCSM($getUser["email"], $getUser["username"], $subject, $content, $bcc);
        }
        setcookie("token", $getUser["token"], time() + $CMSNT->site("session_login"), "/");
        $_SESSION["login"] = $getUser["token"];
        exit(json_encode(["status" => "success", "msg" => __("Đăng nhập thành công!")]));
    }
    if($_POST["action"] == "Verify2FA") {
        if(empty($_POST["token_2fa"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng thực hiện đăng nhập lại")]));
        }
        $token_2fa = check_string($_POST["token_2fa"]);
        if(empty($_POST["code"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập mã xác minh")]));
        }
        $code = check_string($_POST["code"]);
        if($CMSNT->site("reCAPTCHA_status") == 1) {
            if(empty($_POST["recaptcha"])) {
                exit(json_encode(["status" => "error", "msg" => __("Vui lòng xác minh Captcha")]));
            }
            $recaptcha = check_string($_POST["recaptcha"]);
            $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . $CMSNT->site("reCAPTCHA_secret_key") . "&response=" . $recaptcha;
            $verify = file_get_contents($url);
            $captcha_success = json_decode($verify);
            if(!$captcha_success->success) {
                exit(json_encode(["status" => "error", "msg" => __("Vui lòng xác minh Captcha")]));
            }
        }
        $getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token_2fa` = '" . $token_2fa . "' AND `token_2fa` IS NOT NULL ");
        if(!$getUser) {
            exit(json_encode(["status" => "error", "msg" => __("Thông tin đăng nhập không chính xác")]));
        }
        if(empty($getUser["token_2fa"])) {
            exit(json_encode(["status" => "error", "msg" => __("Dữ liệu không hợp lệ")]));
        }
        if(5 <= $getUser["limit_2fa"]) {
            $CMSNT->update("users", ["limit_2fa" => 0, "token_2fa" => NULL], " `id` = '" . $getUser["id"] . "' ");
            $CMSNT->insert("block_ip", ["ip" => myip(), "attempts" => $getUser["limit_2fa"], "create_gettime" => gettime(), "banned" => 1, "reason" => __("Nhập sai 2FA quá 5 lần")]);
            exit(json_encode(["status" => "error", "msg" => __("Bạn đã nhập sai quá nhiều lần, vui lòng xác minh lại từ đầu")]));
        }
        $google2fa = new PragmaRX\Google2FAQRCode\Google2FA();
        if(!$google2fa->verifyKey($getUser["SecretKey_2fa"], $code, 2)) {
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "[Warning] Phát hiện có người đang cố gắng nhập mã xác minh 2FA"]);
            $CMSNT->cong("users", "limit_2fa", 1, " `id` = '" . $getUser["id"] . "' ");
            exit(json_encode(["status" => "error", "msg" => __("Mã xác minh không chính xác")]));
        }
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "[Warning] " . __("Xác thực 2FA thành công - đã đăng nhập vào website")]);
        $CMSNT->update("users", ["token_2fa" => NULL, "limit_2fa" => 0, "ip" => myip(), "time_request" => time(), "time_session" => time(), "device" => $Mobile_Detect->getUserAgent()], " `id` = '" . $getUser["id"] . "' ");
        if($getUser["status_noti_login_to_mail"] == 1 && $CMSNT->site("email_temp_subject_warning_login") != "") {
            $content = $CMSNT->site("email_temp_content_warning_login");
            $content = str_replace("{domain}", $_SERVER["SERVER_NAME"], $content);
            $content = str_replace("{title}", $CMSNT->site("title"), $content);
            $content = str_replace("{username}", $getUser["username"], $content);
            $content = str_replace("{ip}", myip(), $content);
            $content = str_replace("{device}", $Mobile_Detect->getUserAgent(), $content);
            $content = str_replace("{time}", gettime(), $content);
            $subject = $CMSNT->site("email_temp_subject_warning_login");
            $subject = str_replace("{domain}", $_SERVER["SERVER_NAME"], $subject);
            $subject = str_replace("{title}", $CMSNT->site("title"), $subject);
            $subject = str_replace("{username}", $getUser["username"], $subject);
            $subject = str_replace("{ip}", myip(), $subject);
            $subject = str_replace("{device}", $Mobile_Detect->getUserAgent(), $subject);
            $subject = str_replace("{time}", gettime(), $subject);
            $bcc = $CMSNT->site("title");
            sendCSM($getUser["email"], $getUser["username"], $subject, $content, $bcc);
        }
        setcookie("token", $getUser["token"], time() + $CMSNT->site("session_login"), "/");
        $_SESSION["login"] = $getUser["token"];
        exit(json_encode(["status" => "success", "msg" => __("Đăng nhập thành công!")]));
    }
    if($_POST["action"] == "VerifyOTP") {
        if(empty($_POST["token_otp_mail"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng thực hiện đăng nhập lại")]));
        }
        $token_otp_mail = check_string($_POST["token_otp_mail"]);
        if(empty($_POST["code"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập OTP")]));
        }
        $code = check_string($_POST["code"]);
        if($CMSNT->site("reCAPTCHA_status") == 1) {
            if(empty($_POST["recaptcha"])) {
                exit(json_encode(["status" => "error", "msg" => __("Vui lòng xác minh Captcha")]));
            }
            $recaptcha = check_string($_POST["recaptcha"]);
            $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . $CMSNT->site("reCAPTCHA_secret_key") . "&response=" . $recaptcha;
            $verify = file_get_contents($url);
            $captcha_success = json_decode($verify);
            if(!$captcha_success->success) {
                exit(json_encode(["status" => "error", "msg" => __("Vui lòng xác minh Captcha")]));
            }
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token_otp_mail` = '" . $token_otp_mail . "' AND `token_otp_mail` IS NOT NULL "))) {
            exit(json_encode(["status" => "error", "msg" => __("Thông tin đăng nhập không chính xác")]));
        }
        if(empty($getUser["token_otp_mail"])) {
            exit(json_encode(["status" => "error", "msg" => __("Thông tin đăng nhập không chính xác")]));
        }
        if(5 <= $getUser["limit_otp_mail"]) {
            $CMSNT->update("users", ["limit_otp_mail" => 0, "token_otp_mail" => NULL, "otp_mail" => NULL], " `id` = '" . $getUser["id"] . "' ");
            $CMSNT->insert("block_ip", ["ip" => myip(), "attempts" => $getUser["limit_otp_mail"], "create_gettime" => gettime(), "banned" => 1, "reason" => __("Nhập sai OTP quá 5 lần")]);
            exit(json_encode(["status" => "error", "msg" => __("Bạn đã nhập sai quá nhiều lần, vui lòng xác minh lại từ đầu")]));
        }
        if($code != $getUser["otp_mail"]) {
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "[Warning] Phát hiện có người đang cố gắng nhập OTP"]);
            $CMSNT->cong("users", "limit_otp_mail", 1, " `id` = '" . $getUser["id"] . "' ");
            exit(json_encode(["status" => "error", "msg" => __("OTP không chính xác")]));
        }
        $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => "[Warning] " . __("Xác thực OTP thành công - đã đăng nhập vào website")]);
        $CMSNT->update("users", ["token_otp_mail" => NULL, "limit_otp_mail" => 0, "otp_mail" => NULL, "ip" => myip(), "time_request" => time(), "time_session" => time(), "device" => $Mobile_Detect->getUserAgent()], " `id` = '" . $getUser["id"] . "' ");
        if($getUser["status_noti_login_to_mail"] == 1 && $CMSNT->site("email_temp_subject_warning_login") != "") {
            $content = $CMSNT->site("email_temp_content_warning_login");
            $content = str_replace("{domain}", $_SERVER["SERVER_NAME"], $content);
            $content = str_replace("{title}", $CMSNT->site("title"), $content);
            $content = str_replace("{username}", $getUser["username"], $content);
            $content = str_replace("{ip}", myip(), $content);
            $content = str_replace("{device}", $Mobile_Detect->getUserAgent(), $content);
            $content = str_replace("{time}", gettime(), $content);
            $subject = $CMSNT->site("email_temp_subject_warning_login");
            $subject = str_replace("{domain}", $_SERVER["SERVER_NAME"], $subject);
            $subject = str_replace("{title}", $CMSNT->site("title"), $subject);
            $subject = str_replace("{username}", $getUser["username"], $subject);
            $subject = str_replace("{ip}", myip(), $subject);
            $subject = str_replace("{device}", $Mobile_Detect->getUserAgent(), $subject);
            $subject = str_replace("{time}", gettime(), $subject);
            $bcc = $CMSNT->site("title");
            sendCSM($getUser["email"], $getUser["username"], $subject, $content, $bcc);
        }
        setcookie("token", $getUser["token"], time() + $CMSNT->site("session_login"), "/");
        $_SESSION["login"] = $getUser["token"];
        exit(json_encode(["status" => "success", "msg" => __("Đăng nhập thành công!")]));
    }
    if($_POST["action"] == "Register") {
        if(empty($_POST["username"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập username")]));
        }
        $username = check_string($_POST["username"]);
        if(!validateUsername($username)) {
            exit(json_encode(["status" => "error", "msg" => __("Username không hợp lệ")]));
        }
        if(empty($_POST["email"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập địa chỉ Email")]));
        }
        $email = check_string($_POST["email"]);
        if(!validateEmail($email)) {
            exit(json_encode(["status" => "error", "msg" => __("Định dạng Email không hợp lệ")]));
        }
        if(empty($_POST["password"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập mật khẩu")]));
        }
        if(empty($_POST["repassword"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập lại mật khẩu")]));
        }
        $password = check_string($_POST["password"]);
        $repassword = check_string($_POST["repassword"]);
        if($password != $repassword) {
            exit(json_encode(["status" => "error", "msg" => __("Xác minh mật khẩu không chính xác")]));
        }
        if($CMSNT->site("reCAPTCHA_status") == 1) {
            if(empty($_POST["recaptcha"])) {
                exit(json_encode(["status" => "error", "msg" => __("Vui lòng xác minh Captcha")]));
            }
            $recaptcha = check_string($_POST["recaptcha"]);
            $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . $CMSNT->site("reCAPTCHA_secret_key") . "&response=" . $recaptcha;
            $verify = file_get_contents($url);
            $captcha_success = json_decode($verify);
            if(!$captcha_success->success) {
                exit(json_encode(["status" => "error", "msg" => __("Vui lòng xác minh Captcha")]));
            }
        }
        if(0 < $CMSNT->num_rows("SELECT * FROM `users` WHERE `username` = '" . $username . "' ")) {
            exit(json_encode(["status" => "error", "msg" => __("Tên đăng nhập đã tồn tại trong hệ thống")]));
        }
        if(0 < $CMSNT->num_rows("SELECT * FROM `users` WHERE `email` = '" . $email . "' ")) {
            exit(json_encode(["status" => "error", "msg" => __("Địa chỉ email đã tồn tại trong hệ thống")]));
        }
        if($CMSNT->site("max_register_ip") <= $CMSNT->num_rows("SELECT * FROM `users` WHERE `ip` = '" . myip() . "' ")) {
            exit(json_encode(["status" => "error", "msg" => __("IP của bạn đã đạt đến giới hạn tạo tài khoản cho phép")]));
        }
        $google2fa = new PragmaRX\Google2FAQRCode\Google2FA();
        $token = random("QWERTYUIOPASDGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789", 64) . time() . md5(random("QWERTYUIOPASDGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789", 32));
        $isCreate = $CMSNT->insert("users", ["ref_id" => isset($_COOKIE["aff"]) ? check_string($_COOKIE["aff"]) : 0, "utm_source" => isset($_COOKIE["utm_source"]) ? check_string($_COOKIE["utm_source"]) : "web", "token" => $token, "username" => $username, "email" => $email, "password" => TypePassword($password), "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "create_date" => gettime(), "update_date" => gettime(), "time_session" => time(), "api_key" => md5($username . time() . random("QWERTYUIOPASDFGHJKL", 6)), "SecretKey_2fa" => $google2fa->generateSecretKey()]);
        if($isCreate) {
            $CMSNT->insert("logs", ["user_id" => $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . $token . "' ")["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Create an account")]);
            setcookie("token", $token, time() + $CMSNT->site("session_login"), "/");
            $_SESSION["login"] = $token;
            exit(json_encode(["status" => "success", "msg" => __("Đăng ký thành công!")]));
        }
        exit(json_encode(["status" => "error", "msg" => __("Tạo tài khoản không thành công, vui lòng thử lại")]));
    }
    if($_POST["action"] == "ChangeProfile") {
        if($CMSNT->site("status_demo") != 0) {
            exit(json_encode(["status" => "error", "msg" => __("Chức năng này không thể sử dụng trên website demo")]));
        }
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
        }
        $isUpdate = $CMSNT->update("users", ["fullname" => isset($_POST["fullname"]) ? check_string($_POST["fullname"]) : NULL, "telegram_chat_id" => isset($_POST["telegram_chat_id"]) ? check_string($_POST["telegram_chat_id"]) : "Male", "phone" => isset($_POST["phone"]) ? check_string($_POST["phone"]) : NULL], " `token` = '" . check_string($_POST["token"]) . "' ");
        if($isUpdate) {
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Change profile information")]);
            exit(json_encode(["status" => "success", "msg" => __("Lưu thành công")]));
        }
        exit(json_encode(["status" => "error", "msg" => __("Lưu thất bại")]));
    }
    if($_POST["action"] == "ChangePasswordProfile") {
        if($CMSNT->site("status_demo") != 0) {
            exit(json_encode(["status" => "error", "msg" => __("Chức năng này không thể sử dụng trên website demo")]));
        }
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
        }
        if(empty($_POST["password"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập mật khẩu hiện tại")]));
        }
        if(empty($_POST["newpassword"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập mật khẩu mới")]));
        }
        if(strlen($_POST["newpassword"]) < 5) {
            exit(json_encode(["status" => "error", "msg" => __("Mật khẩu mới quá ngắn")]));
        }
        if(empty($_POST["renewpassword"])) {
            exit(json_encode(["status" => "error", "msg" => __("Xác nhận mật khẩu không chính xác")]));
        }
        if($_POST["renewpassword"] != $_POST["newpassword"]) {
            exit(json_encode(["status" => "error", "msg" => __("Xác nhận mật khẩu không chính xác")]));
        }
        $password = check_string($_POST["password"]);
        if($CMSNT->site("type_password") == "bcrypt") {
            if(!password_verify($password, $getUser["password"])) {
                exit(json_encode(["status" => "error", "msg" => __("Mật khẩu hiện tại không đúng")]));
            }
        } elseif($getUser["password"] != TypePassword($password)) {
            exit(json_encode(["status" => "error", "msg" => __("Mật khẩu hiện tại không đúng")]));
        }
        $token = random("QWERTYUIOPASDGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789", 64) . time() . md5(random("QWERTYUIOPASDGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789", 32));
        $isUpdate = $CMSNT->update("users", ["password" => TypePassword(check_string($_POST["newpassword"])), "token" => $token], " `token` = '" . check_string($_POST["token"]) . "' ");
        if($isUpdate) {
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Change Password")]);
            exit(json_encode(["status" => "success", "msg" => __("Change password successfully!")]));
        }
        exit(json_encode(["status" => "error", "msg" => __("Password change failed!")]));
    }
    if($_POST["action"] == "ForgotPassword") {
        if($CMSNT->site("status_demo") != 0) {
            exit(json_encode(["status" => "error", "msg" => __("Chức năng này không thể sử dụng trên website demo")]));
        }
        if($CMSNT->site("status_demo") != 0) {
            exit(json_encode(["status" => "error", "msg" => __("Chức năng này không thể sử dụng trên website demo")]));
        }
        if(!$CMSNT->site("smtp_password")) {
            exit(json_encode(["status" => "error", "msg" => __("Website chưa được cấu hình SMTP, vui lòng liên hệ Admin")]));
        }
        if(empty($_POST["email"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập địa chỉ Email")]));
        }
        if(!($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `email` = '" . check_string($_POST["email"]) . "' "))) {
            exit(json_encode(["status" => "error", "msg" => __("Địa chỉ Email này không tồn tại trong hệ thống")]));
        }
        if(time() - $getUser["time_forgot_password"] < 60) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng thử lại trong ít phút")]));
        }
        $token = md5(random("QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789", 6) . time()) . md5(random("QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789", 55));
        if($CMSNT->site("email_temp_subject_forgot_password") != "") {
            $content = $CMSNT->site("email_temp_content_forgot_password");
            $content = str_replace("{domain}", $_SERVER["SERVER_NAME"], $content);
            $content = str_replace("{title}", $CMSNT->site("title"), $content);
            $content = str_replace("{username}", $getUser["username"], $content);
            $content = str_replace("{link}", "<a target=\"_blank\" href=\"" . base_url("?action=reset-password&token=" . $token) . "\">" . base_url("?action=reset-password&token=" . $token) . "</a>", $content);
            $content = str_replace("{ip}", myip(), $content);
            $content = str_replace("{device}", $Mobile_Detect->getUserAgent(), $content);
            $content = str_replace("{time}", gettime(), $content);
            $subject = $CMSNT->site("email_temp_subject_forgot_password");
            $subject = str_replace("{domain}", $_SERVER["SERVER_NAME"], $subject);
            $subject = str_replace("{title}", $CMSNT->site("title"), $subject);
            $subject = str_replace("{username}", $getUser["username"], $subject);
            $subject = str_replace("{link}", "<a target=\"_blank\" href=\"" . base_url("?action=reset-password&token=" . $token) . "\">" . base_url("?action=reset-password&token=" . $token) . "</a>", $subject);
            $subject = str_replace("{ip}", myip(), $subject);
            $subject = str_replace("{device}", $Mobile_Detect->getUserAgent(), $subject);
            $subject = str_replace("{time}", gettime(), $subject);
            $bcc = $CMSNT->site("title");
            sendCSM($getUser["email"], $getUser["username"], $subject, $content, $bcc);
        }
        $isUpdate = $CMSNT->update("users", ["token_forgot_password" => $token, "time_forgot_password" => time()], " `id` = '" . $getUser["id"] . "' ");
        if($isUpdate) {
            exit(json_encode(["status" => "success", "msg" => __("Vui lòng kiểm tra Email của bạn để hoàn tất quá trình đặt lại mật khẩu")]));
        }
        exit(json_encode(["status" => "error", "msg" => __("Có lỗi hệ thống, vui lòng liên hệ Developer")]));
    }
    if($_POST["action"] == "ChangePassword") {
        if($CMSNT->site("status_demo") != 0) {
            exit(json_encode(["status" => "error", "msg" => __("Chức năng này không thể sử dụng trên website demo")]));
        }
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Liên kết không hợp lệ")]));
        }
        $token = check_string($_POST["token"]);
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token_forgot_password` = '" . $token . "' AND `token_forgot_password` IS NOT NULL "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
        }
        if(empty($getUser["token_forgot_password"])) {
            exit(json_encode(["status" => "error", "msg" => __("Liên kết không tồn tại")]));
        }
        if(empty($_POST["newpassword"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập mật khẩu mới")]));
        }
        if(strlen($_POST["newpassword"]) < 5) {
            exit(json_encode(["status" => "error", "msg" => __("Mật khẩu mới quá ngắn")]));
        }
        if(empty($_POST["renewpassword"])) {
            exit(json_encode(["status" => "error", "msg" => __("Xác nhận mật khẩu không chính xác")]));
        }
        if($_POST["renewpassword"] != $_POST["newpassword"]) {
            exit(json_encode(["status" => "error", "msg" => __("Xác nhận mật khẩu không chính xác")]));
        }
        $password = check_string($_POST["newpassword"]);
        $token = random("QWERTYUIOPASDGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789", 64) . time() . md5(random("QWERTYUIOPASDGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789", 32));
        $isUpdate = $CMSNT->update("users", ["token_forgot_password" => NULL, "password" => TypePassword($password), "token" => $token], " `id` = '" . $getUser["id"] . "' ");
        if($isUpdate) {
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Khôi phục lại mật khẩu")]);
            $my_text = $CMSNT->site("noti_action");
            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
            $my_text = str_replace("{username}", $getUser["username"], $my_text);
            $my_text = str_replace("{action}", __("Khôi phục lại mật khẩu"), $my_text);
            $my_text = str_replace("{ip}", myip(), $my_text);
            $my_text = str_replace("{time}", gettime(), $my_text);
            sendMessAdmin($my_text);
            exit(json_encode(["status" => "success", "msg" => __("Thay đổi mật khẩu thành công")]));
        }
        exit(json_encode(["status" => "error", "msg" => __("Thay đổi mật khẩu thất bại")]));
    }
    if($_POST["action"] == "changeAPIKey") {
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        $api_key = md5($getUser["username"] . time() . random("QWERTYUIOPASDFGHJKL", 6));
        $isUpdate = $CMSNT->update("users", ["api_key" => $api_key], " `id` = '" . $getUser["id"] . "' ");
        if($isUpdate) {
            $Mobile_Detect = new Mobile_Detect();
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Thay đổi API KEY")]);
            $data = json_encode(["api_key" => $api_key, "status" => "success", "msg" => __("Thay đổi API KEY thành công!")]);
            exit($data);
        }
    }
    if($_POST["action"] == "changeSecurity") {
        if($CMSNT->site("status_demo") != 0) {
            exit(json_encode(["status" => "error", "msg" => __("Chức năng này không thể sử dụng trên website demo")]));
        }
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if($CMSNT->site("smtp_status") != 1) {
            exit(json_encode(["status" => "error", "msg" => __("SMTP chưa được cấu hình, vui lòng liên hệ Admin")]));
        }
        $isUpdate = $CMSNT->update("users", ["status_noti_login_to_mail" => !empty($_POST["status_noti_login_to_mail"]) ? check_string($_POST["status_noti_login_to_mail"]) : 0, "status_otp_mail" => !empty($_POST["status_otp_mail"]) ? check_string($_POST["status_otp_mail"]) : 0, "status_view_order" => !empty($_POST["status_view_order"]) ? check_string($_POST["status_view_order"]) : 0], " `id` = '" . $getUser["id"] . "' ");
        if($isUpdate) {
            $Mobile_Detect = new Mobile_Detect();
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => __("Cấu hình bảo mật")]);
            $data = json_encode(["status" => "success", "msg" => __("Lưu thay đổi thành công!")]);
            exit($data);
        }
    }
    if($_POST["action"] == "Save2FA") {
        if($CMSNT->site("status_demo") != 0) {
            exit(json_encode(["status" => "error", "msg" => __("Chức năng này không thể sử dụng trên website demo")]));
        }
        if(empty($_POST["token"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
        }
        if(empty($_POST["secret"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập mã xác minh 2FA")]));
        }
        $google2fa = new PragmaRX\Google2FAQRCode\Google2FA();
        if(!$google2fa->verifyKey($getUser["SecretKey_2fa"], check_string($_POST["secret"]))) {
            exit(json_encode(["status" => "error", "msg" => __("Mã xác minh không chính xác")]));
        }
        $status_2fa = !empty($_POST["status_2fa"]) ? check_string($_POST["status_2fa"]) : 0;
        if($status_2fa == 1) {
            $action = __("Bật xác thực Google Authenticator");
            $SecretKey_2fa = $getUser["SecretKey_2fa"];
        } else {
            $action = __("Tắt xác thực Google Authenticator");
            $SecretKey_2fa = $google2fa->generateSecretKey();
        }
        $isUpdate = $CMSNT->update("users", ["status_2fa" => $status_2fa, "SecretKey_2fa" => $SecretKey_2fa], " `id` = '" . $getUser["id"] . "' ");
        if($isUpdate) {
            $Mobile_Detect = new Mobile_Detect();
            $CMSNT->insert("logs", ["user_id" => $getUser["id"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent(), "createdate" => gettime(), "action" => $action]);
            $data = json_encode(["status" => "success", "msg" => $action . " " . __("thành công")]);
            exit($data);
        }
    }
}

?>