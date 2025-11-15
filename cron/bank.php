<?php
define("IN_SITE", true);
require_once __DIR__ . "/../libs/db.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../libs/lang.php";
require_once __DIR__ . "/../libs/helper.php";
require_once __DIR__ . "/../libs/database/users.php";
$CMSNT = new DB();
$user = new users();

$CMSNT->update("settings", ["value" => time()], " `name` = 'check_time_cron_bank' ");
foreach ($CMSNT->get_list(" SELECT * FROM `banks` WHERE `status` = 1 ") as $bank) {
    if ($bank["short_name"] == "ACB") {
        $result = getAcbTransaction($bank);
        if ($result['success'] == false) {
            echo "Lỗi: ". $result['message'];
            exit();
        }
        foreach ($result["data"]["data"] as $data) {
            $tid = check_string($data["transactionNumber"]);
            $description = str_replace(" ", ".", check_string($data["description"]));
            $amount = check_string($data["amount"]);
            $user_id = parse_order_id($description, $CMSNT->site("prefix_autobank"));
            if (!($amount < $CMSNT->site("bank_min") || $CMSNT->site("bank_max") < $amount)) {
                if (($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $user_id . "' ")) && $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ") == 0) {
                    $received = checkPromotion($amount);
                    $insertSv2 = $CMSNT->insert("payment_bank", ["tid" => $tid, "method" => $bank["short_name"], "user_id" => $getUser["id"], "description" => $description, "amount" => $amount, "received" => $received, "create_gettime" => gettime(), "create_time" => time()]);
                    if ($insertSv2) {
                        $isCong = $user->AddCredits($getUser["id"], $received, "Nạp tiền tự động qua " . $bank["short_name"] . " (#" . $tid . " - " . $description . " - " . $amount . ")", "TOPUP_" . $bank["accountNumber"] . "_" . $tid);
                        if ($isCong) {
                            if ($CMSNT->site("affiliate_status") == 1 && $getUser["ref_id"] != 0) {
                                $ck = $CMSNT->site("affiliate_ck");
                                if (getRowRealtime("users", $getUser["ref_id"], "ref_ck") != 0) {
                                    $ck = getRowRealtime("users", $getUser["ref_id"], "ref_ck");
                                }
                                $price = $received * $ck / 100;
                                $user->AddCommission($getUser["ref_id"], $getUser["id"], $price, __("Hoa hồng thành viên " . $getUser["username"]));
                            }
                            debit_processing($getUser["id"]);
                            $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => $bank["short_name"], "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
                            $my_text = $CMSNT->site("noti_recharge");
                            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                            $my_text = str_replace("{username}", $getUser["username"], $my_text);
                            $my_text = str_replace("{method}", $bank["short_name"], $my_text);
                            $my_text = str_replace("{amount}", format_currency($amount), $my_text);
                            $my_text = str_replace("{price}", format_currency($received), $my_text);
                            $my_text = str_replace("{time}", gettime(), $my_text);
                            sendMessAdmin($my_text);
                            echo "[<b style=\"color:green\">-</b>] Xử lý thành công 1 hoá đơn." . PHP_EOL;
                        }
                    }
                }
                if ($CMSNT->get_row(" SELECT COUNT(id) FROM `log_bank_auto` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "' AND `method` = '" . $bank["short_name"] . "'  ")["COUNT(id)"] == 0) {
                    $CMSNT->insert("log_bank_auto", ["tid" => $tid, "method" => $bank["short_name"], "description" => $description, "type" => $data["type"], "amount" => $amount, "create_gettime" => gettime()]);
                }
            }
        }
    } else {
        if ($bank["short_name"] == "VietinBank" || $bank["short_name"] == "Vietinbank" || $bank["short_name"] == "VTB") {
            $result = curl_get("https://api.sieuthicode.net/api/historyviettinv2/" . $bank["token"]);
            $result = json_decode($result, true);
            foreach ($result["transactions"] as $data) {
                $tid = check_string($data["transactionID"]);
                $description = check_string($data["description"]);
                $amount = check_string((int) $data["amount"]);
                $user_id = parse_order_id($description, $CMSNT->site("prefix_autobank"));
                if (!($amount < $CMSNT->site("bank_min") || $CMSNT->site("bank_max") < $amount)) {
                    if (($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $user_id . "' ")) && $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ") == 0) {
                        $received = checkPromotion($amount);
                        $insertSv2 = $CMSNT->insert("payment_bank", ["tid" => $tid, "method" => $bank["short_name"], "user_id" => $getUser["id"], "description" => $description, "amount" => $amount, "received" => $received, "create_gettime" => gettime(), "create_time" => time()]);
                        if ($insertSv2) {
                            $isCong = $user->AddCredits($getUser["id"], $received, "Nạp tiền tự động qua " . $bank["short_name"] . " (#" . $tid . " - " . $description . " - " . $amount . ")", "TOPUP_" . $bank["accountNumber"] . "_" . $tid);
                            if ($isCong) {
                                if ($CMSNT->site("affiliate_status") == 1 && $getUser["ref_id"] != 0) {
                                    $ck = $CMSNT->site("affiliate_ck");
                                    if (getRowRealtime("users", $getUser["ref_id"], "ref_ck") != 0) {
                                        $ck = getRowRealtime("users", $getUser["ref_id"], "ref_ck");
                                    }
                                    $price = $received * $ck / 100;
                                    $user->AddCommission($getUser["ref_id"], $getUser["id"], $price, __("Hoa hồng thành viên " . $getUser["username"]));
                                }
                                debit_processing($getUser["id"]);
                                $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => $bank["short_name"], "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
                                $my_text = $CMSNT->site("noti_recharge");
                                $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                                $my_text = str_replace("{username}", $getUser["username"], $my_text);
                                $my_text = str_replace("{method}", $bank["short_name"], $my_text);
                                $my_text = str_replace("{amount}", format_currency($amount), $my_text);
                                $my_text = str_replace("{price}", format_currency($received), $my_text);
                                $my_text = str_replace("{time}", gettime(), $my_text);
                                sendMessAdmin($my_text);
                                echo "[<b style=\"color:green\">-</b>] Xử lý thành công 1 hoá đơn." . PHP_EOL;
                            }
                        }
                    }
                    if ($CMSNT->get_row(" SELECT COUNT(id) FROM `log_bank_auto` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "' AND `method` = '" . $bank["short_name"] . "'  ")["COUNT(id)"] == 0) {
                        $CMSNT->insert("log_bank_auto", ["tid" => $tid, "method" => $bank["short_name"], "description" => $description, "type" => $data["type"], "amount" => $amount, "create_gettime" => gettime()]);
                    }
                }
            }
        } else {
            if ($bank["short_name"] == "Techcombank" || $bank["short_name"] == "TCB") {
                $result = curl_get("https://api.sieuthicode.net/api/historytcb/" . $bank["token"]);
                $result = json_decode($result, true);
                foreach ($result["transactions"] as $data) {
                    $tid = check_string($data["transactionID"]);
                    $description = check_string($data["description"]);
                    $amount = check_string(str_replace(",", "", $data["amount"]));
                    $user_id = parse_order_id($description, $CMSNT->site("prefix_autobank"));
                    if (!($amount < $CMSNT->site("bank_min") || $CMSNT->site("bank_max") < $amount)) {
                        if (($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $user_id . "' ")) && $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ") == 0) {
                            $received = checkPromotion($amount);
                            $insertSv2 = $CMSNT->insert("payment_bank", ["tid" => $tid, "method" => $bank["short_name"], "user_id" => $getUser["id"], "description" => $description, "amount" => $amount, "received" => $received, "create_gettime" => gettime(), "create_time" => time()]);
                            if ($insertSv2) {
                                $isCong = $user->AddCredits($getUser["id"], $received, "Nạp tiền tự động qua " . $bank["short_name"] . " (#" . $tid . " - " . $description . " - " . $amount . ")", "TOPUP_" . $bank["accountNumber"] . "_" . $tid);
                                if ($isCong) {
                                    if ($CMSNT->site("affiliate_status") == 1 && $getUser["ref_id"] != 0) {
                                        $ck = $CMSNT->site("affiliate_ck");
                                        if (getRowRealtime("users", $getUser["ref_id"], "ref_ck") != 0) {
                                            $ck = getRowRealtime("users", $getUser["ref_id"], "ref_ck");
                                        }
                                        $price = $received * $ck / 100;
                                        $user->AddCommission($getUser["ref_id"], $getUser["id"], $price, __("Hoa hồng thành viên " . $getUser["username"]));
                                    }
                                    debit_processing($getUser["id"]);
                                    $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => $bank["short_name"], "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
                                    $my_text = $CMSNT->site("noti_recharge");
                                    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                                    $my_text = str_replace("{username}", $getUser["username"], $my_text);
                                    $my_text = str_replace("{method}", $bank["short_name"], $my_text);
                                    $my_text = str_replace("{amount}", format_currency($amount), $my_text);
                                    $my_text = str_replace("{price}", format_currency($received), $my_text);
                                    $my_text = str_replace("{time}", gettime(), $my_text);
                                    sendMessAdmin($my_text);
                                    echo "[<b style=\"color:green\">-</b>] Xử lý thành công 1 hoá đơn." . PHP_EOL;
                                }
                            }
                        }
                        if ($CMSNT->get_row(" SELECT COUNT(id) FROM `log_bank_auto` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "' AND `method` = '" . $bank["short_name"] . "'  ")["COUNT(id)"] == 0) {
                            $CMSNT->insert("log_bank_auto", ["tid" => $tid, "method" => $bank["short_name"], "description" => $description, "type" => $data["type"], "amount" => $amount, "create_gettime" => gettime()]);
                        }
                    }
                }
            } else {
                if ($bank["short_name"] == "VCB" || $bank["short_name"] == "Vietcombank") {
                    $result = curl_get2("https://api.sieuthicode.net/historyapivcbv2/" . $bank["token"]);
                    $result = json_decode($result, true);
                    foreach ($result["transactions"] as $data) {
                        $tid = check_string($data["transactionID"]);
                        $description = check_string($data["description"]);
                        $amount = check_string(str_replace(",", "", $data["amount"]));
                        $user_id = parse_order_id($description, $CMSNT->site("prefix_autobank"));
                        if (!($amount < $CMSNT->site("bank_min") || $CMSNT->site("bank_max") < $amount)) {
                            if (($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $user_id . "' ")) && $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ") == 0) {
                                $received = checkPromotion($amount);
                                $insertSv2 = $CMSNT->insert("payment_bank", ["tid" => $tid, "method" => $bank["short_name"], "user_id" => $getUser["id"], "description" => $description, "amount" => $amount, "received" => $received, "create_gettime" => gettime(), "create_time" => time()]);
                                if ($insertSv2) {
                                    $isCong = $user->AddCredits($getUser["id"], $received, "Nạp tiền tự động qua " . $bank["short_name"] . " (#" . $tid . " - " . $description . " - " . $amount . ")", "TOPUP_" . $bank["accountNumber"] . "_" . $tid);
                                    if ($isCong) {
                                        if ($CMSNT->site("affiliate_status") == 1 && $getUser["ref_id"] != 0) {
                                            $ck = $CMSNT->site("affiliate_ck");
                                            if (getRowRealtime("users", $getUser["ref_id"], "ref_ck") != 0) {
                                                $ck = getRowRealtime("users", $getUser["ref_id"], "ref_ck");
                                            }
                                            $price = $received * $ck / 100;
                                            $user->AddCommission($getUser["ref_id"], $getUser["id"], $price, __("Hoa hồng thành viên " . $getUser["username"]));
                                        }
                                        debit_processing($getUser["id"]);
                                        $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => $bank["short_name"], "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
                                        $my_text = $CMSNT->site("noti_recharge");
                                        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                                        $my_text = str_replace("{username}", $getUser["username"], $my_text);
                                        $my_text = str_replace("{method}", $bank["short_name"], $my_text);
                                        $my_text = str_replace("{amount}", format_currency($amount), $my_text);
                                        $my_text = str_replace("{price}", format_currency($received), $my_text);
                                        $my_text = str_replace("{time}", gettime(), $my_text);
                                        sendMessAdmin($my_text);
                                        echo "[<b style=\"color:green\">-</b>] Xử lý thành công 1 hoá đơn." . PHP_EOL;
                                    }
                                }
                            }
                            if ($CMSNT->get_row(" SELECT COUNT(id) FROM `log_bank_auto` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "' AND `method` = '" . $bank["short_name"] . "'  ")["COUNT(id)"] == 0) {
                                $CMSNT->insert("log_bank_auto", ["tid" => $tid, "method" => $bank["short_name"], "description" => $description, "type" => $data["type"], "amount" => $amount, "create_gettime" => gettime()]);
                            }
                        }
                    }
                } else {
                    if ($bank["short_name"] == "VPBank" || $bank["short_name"] == "VPB") {
                        $result = curl_get("https://api.sieuthicode.net/historyapivpb/" . $bank["token"]);
                        $result = json_decode($result, true);
                        foreach ($result["transactions"] as $data) {
                            $tid = check_string($data["transactionID"]);
                            $description = check_string($data["description"]);
                            $amount = check_string($data["amount"]);
                            $user_id = parse_order_id($description, $CMSNT->site("prefix_autobank"));
                            if (!($amount < $CMSNT->site("bank_min") || $CMSNT->site("bank_max") < $amount)) {
                                if (($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $user_id . "' ")) && $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ") == 0) {
                                    $received = checkPromotion($amount);
                                    $insertSv2 = $CMSNT->insert("payment_bank", ["tid" => $tid, "method" => $bank["short_name"], "user_id" => $getUser["id"], "description" => $description, "amount" => $amount, "received" => $received, "create_gettime" => gettime(), "create_time" => time()]);
                                    if ($insertSv2) {
                                        $isCong = $user->AddCredits($getUser["id"], $received, "Nạp tiền tự động qua " . $bank["short_name"] . " (#" . $tid . " - " . $description . " - " . $amount . ")", "TOPUP_" . $bank["accountNumber"] . "_" . $tid);
                                        if ($isCong) {
                                            if ($CMSNT->site("affiliate_status") == 1 && $getUser["ref_id"] != 0) {
                                                $ck = $CMSNT->site("affiliate_ck");
                                                if (getRowRealtime("users", $getUser["ref_id"], "ref_ck") != 0) {
                                                    $ck = getRowRealtime("users", $getUser["ref_id"], "ref_ck");
                                                }
                                                $price = $received * $ck / 100;
                                                $user->AddCommission($getUser["ref_id"], $getUser["id"], $price, __("Hoa hồng thành viên " . $getUser["username"]));
                                            }
                                            debit_processing($getUser["id"]);
                                            $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => $bank["short_name"], "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
                                            $my_text = $CMSNT->site("noti_recharge");
                                            $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                                            $my_text = str_replace("{username}", $getUser["username"], $my_text);
                                            $my_text = str_replace("{method}", $bank["short_name"], $my_text);
                                            $my_text = str_replace("{amount}", format_currency($amount), $my_text);
                                            $my_text = str_replace("{price}", format_currency($received), $my_text);
                                            $my_text = str_replace("{time}", gettime(), $my_text);
                                            sendMessAdmin($my_text);
                                            echo "[<b style=\"color:green\">-</b>] Xử lý thành công 1 hoá đơn." . PHP_EOL;
                                        }
                                    }
                                }
                                if ($CMSNT->get_row(" SELECT COUNT(id) FROM `log_bank_auto` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "' AND `method` = '" . $bank["short_name"] . "'  ")["COUNT(id)"] == 0) {
                                    $CMSNT->insert("log_bank_auto", ["tid" => $tid, "method" => $bank["short_name"], "description" => $description, "type" => $data["type"], "amount" => $amount, "create_gettime" => gettime()]);
                                }
                            }
                        }
                    } else {
                        if ($bank["short_name"] == "MB" || $bank["short_name"] == "MBBank") {
                            $result = curl_get("https://api.sieuthicode.net/historyapimbbankv2/" . $bank["token"]);
                            $result = json_decode($result, true);
                            foreach ($result["transactions"] as $data) {
                                $tid = check_string($data["transactionID"]);
                                $description = str_replace(" ", ".", check_string($data["description"]));
                                $amount = check_string($data["amount"]);
                                $user_id = parse_order_id($description, $CMSNT->site("prefix_autobank"));
                                if (!($amount < $CMSNT->site("bank_min") || $CMSNT->site("bank_max") < $amount)) {
                                    if (($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $user_id . "' ")) && $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ") == 0) {
                                        $received = checkPromotion($amount);
                                        $insertSv2 = $CMSNT->insert("payment_bank", ["tid" => $tid, "method" => $bank["short_name"], "user_id" => $getUser["id"], "description" => $description, "amount" => $amount, "received" => $received, "create_gettime" => gettime(), "create_time" => time()]);
                                        if ($insertSv2) {
                                            $isCong = $user->AddCredits($getUser["id"], $received, "Nạp tiền tự động qua " . $bank["short_name"] . " (#" . $tid . " - " . $description . " - " . $amount . ")", "TOPUP_" . $bank["accountNumber"] . "_" . $tid);
                                            if ($isCong) {
                                                if ($CMSNT->site("affiliate_status") == 1 && $getUser["ref_id"] != 0) {
                                                    $ck = $CMSNT->site("affiliate_ck");
                                                    if (getRowRealtime("users", $getUser["ref_id"], "ref_ck") != 0) {
                                                        $ck = getRowRealtime("users", $getUser["ref_id"], "ref_ck");
                                                    }
                                                    $price = $received * $ck / 100;
                                                    $user->AddCommission($getUser["ref_id"], $getUser["id"], $price, __("Hoa hồng thành viên " . $getUser["username"]));
                                                }
                                                debit_processing($getUser["id"]);
                                                $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => $bank["short_name"], "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
                                                $my_text = $CMSNT->site("noti_recharge");
                                                $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                                                $my_text = str_replace("{username}", $getUser["username"], $my_text);
                                                $my_text = str_replace("{method}", $bank["short_name"], $my_text);
                                                $my_text = str_replace("{amount}", format_currency($amount), $my_text);
                                                $my_text = str_replace("{price}", format_currency($received), $my_text);
                                                $my_text = str_replace("{time}", gettime(), $my_text);
                                                sendMessAdmin($my_text);
                                                echo "[<b style=\"color:green\">-</b>] Xử lý thành công 1 hoá đơn." . PHP_EOL;
                                            }
                                        }
                                    }
                                    if ($CMSNT->get_row(" SELECT COUNT(id) FROM `log_bank_auto` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "' AND `method` = '" . $bank["short_name"] . "'  ")["COUNT(id)"] == 0) {
                                        $CMSNT->insert("log_bank_auto", ["tid" => $tid, "method" => $bank["short_name"], "description" => $description, "type" => $data["type"], "amount" => $amount, "create_gettime" => gettime()]);
                                    }
                                }
                            }
                        } else {
                            if ($bank["short_name"] == "TPBank" || $bank["short_name"] == "TPB") {
                                $result = curl_get("https://api.sieuthicode.net/api/historytpb/" . $bank["token"]);
                                $result = json_decode($result, true);
                                foreach ($result["transactions"] as $data) {
                                    $tid = check_string($data["transactionID"]);
                                    $description = check_string($data["description"]);
                                    $amount = check_string($data["amount"]);
                                    $user_id = parse_order_id($description, $CMSNT->site("prefix_autobank"));
                                    if (!($amount < $CMSNT->site("bank_min") || $CMSNT->site("bank_max") < $amount)) {
                                        if (($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $user_id . "' ")) && $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ") == 0) {
                                            $received = checkPromotion($amount);
                                            $insertSv2 = $CMSNT->insert("payment_bank", ["tid" => $tid, "method" => $bank["short_name"], "user_id" => $getUser["id"], "description" => $description, "amount" => $amount, "received" => $received, "create_gettime" => gettime(), "create_time" => time()]);
                                            if ($insertSv2) {
                                                $isCong = $user->AddCredits($getUser["id"], $received, "Nạp tiền tự động qua " . $bank["short_name"] . " (#" . $tid . " - " . $description . " - " . $amount . ")", "TOPUP_" . $bank["accountNumber"] . "_" . $tid);
                                                if ($isCong) {
                                                    if ($CMSNT->site("affiliate_status") == 1 && $getUser["ref_id"] != 0) {
                                                        $ck = $CMSNT->site("affiliate_ck");
                                                        if (getRowRealtime("users", $getUser["ref_id"], "ref_ck") != 0) {
                                                            $ck = getRowRealtime("users", $getUser["ref_id"], "ref_ck");
                                                        }
                                                        $price = $received * $ck / 100;
                                                        $user->AddCommission($getUser["ref_id"], $getUser["id"], $price, __("Hoa hồng thành viên " . $getUser["username"]));
                                                    }
                                                    debit_processing($getUser["id"]);
                                                    $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => $bank["short_name"], "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
                                                    $my_text = $CMSNT->site("noti_recharge");
                                                    $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                                                    $my_text = str_replace("{username}", $getUser["username"], $my_text);
                                                    $my_text = str_replace("{method}", $bank["short_name"], $my_text);
                                                    $my_text = str_replace("{amount}", format_currency($amount), $my_text);
                                                    $my_text = str_replace("{price}", format_currency($received), $my_text);
                                                    $my_text = str_replace("{time}", gettime(), $my_text);
                                                    sendMessAdmin($my_text);
                                                    echo "[<b style=\"color:green\">-</b>] Xử lý thành công 1 hoá đơn." . PHP_EOL;
                                                }
                                            }
                                        }
                                        if ($CMSNT->get_row(" SELECT COUNT(id) FROM `log_bank_auto` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "' AND `method` = '" . $bank["short_name"] . "'  ")["COUNT(id)"] == 0) {
                                            $CMSNT->insert("log_bank_auto", ["tid" => $tid, "method" => $bank["short_name"], "description" => $description, "type" => $data["type"], "amount" => $amount, "create_gettime" => gettime()]);
                                        }
                                    }
                                }
                            } else {
                                if ($bank["short_name"] == "BIDV") {
                                    $result = curl_get("https://api.sieuthicode.net/historyapibidvv2/" . $bank["token"]);
                                    $result = json_decode($result, true);
                                    foreach ($result["transactions"] as $data) {
                                        $tid = check_string($data["transactionID"]);
                                        $description = check_string($data["description"]);
                                        $amount = check_string(str_replace(",", "", $data["amount"]));
                                        $user_id = parse_order_id($description, $CMSNT->site("prefix_autobank"));
                                        if (!($amount < $CMSNT->site("bank_min") || $CMSNT->site("bank_max") < $amount)) {
                                            if (($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `id` = '" . $user_id . "' ")) && $CMSNT->num_rows(" SELECT * FROM `payment_bank` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "'  ") == 0) {
                                                $received = checkPromotion($amount);
                                                $insertSv2 = $CMSNT->insert("payment_bank", ["tid" => $tid, "method" => $bank["short_name"], "user_id" => $getUser["id"], "description" => $description, "amount" => $amount, "received" => $received, "create_gettime" => gettime(), "create_time" => time()]);
                                                if ($insertSv2) {
                                                    $isCong = $user->AddCredits($getUser["id"], $received, "Nạp tiền tự động qua " . $bank["short_name"] . " (#" . $tid . " - " . $description . " - " . $amount . ")", "TOPUP_" . $bank["accountNumber"] . "_" . $tid);
                                                    if ($isCong) {
                                                        if ($CMSNT->site("affiliate_status") == 1 && $getUser["ref_id"] != 0) {
                                                            $ck = $CMSNT->site("affiliate_ck");
                                                            if (getRowRealtime("users", $getUser["ref_id"], "ref_ck") != 0) {
                                                                $ck = getRowRealtime("users", $getUser["ref_id"], "ref_ck");
                                                            }
                                                            $price = $received * $ck / 100;
                                                            $user->AddCommission($getUser["ref_id"], $getUser["id"], $price, __("Hoa hồng thành viên " . $getUser["username"]));
                                                        }
                                                        debit_processing($getUser["id"]);
                                                        $CMSNT->insert("deposit_log", ["user_id" => $getUser["id"], "method" => $bank["short_name"], "amount" => $amount, "received" => $received, "create_time" => time(), "is_virtual" => 0]);
                                                        $my_text = $CMSNT->site("noti_recharge");
                                                        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                                                        $my_text = str_replace("{username}", $getUser["username"], $my_text);
                                                        $my_text = str_replace("{method}", $bank["short_name"], $my_text);
                                                        $my_text = str_replace("{amount}", format_currency($amount), $my_text);
                                                        $my_text = str_replace("{price}", format_currency($received), $my_text);
                                                        $my_text = str_replace("{time}", gettime(), $my_text);
                                                        sendMessAdmin($my_text);
                                                        echo "[<b style=\"color:green\">-</b>] Xử lý thành công 1 hoá đơn." . PHP_EOL;
                                                    }
                                                }
                                            }
                                            if ($CMSNT->get_row(" SELECT COUNT(id) FROM `log_bank_auto` WHERE `tid` = '" . $tid . "' AND `description` = '" . $description . "' AND `method` = '" . $bank["short_name"] . "'  ")["COUNT(id)"] == 0) {
                                                $CMSNT->insert("log_bank_auto", ["tid" => $tid, "method" => $bank["short_name"], "description" => $description, "type" => $data["type"], "amount" => $amount, "create_gettime" => gettime()]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
curl_get2(base_url("cron/cron.php"));

?>