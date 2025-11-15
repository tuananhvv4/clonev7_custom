<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
require_once __DIR__ . "/../../libs/sendEmail.php";
require_once __DIR__ . "/../../libs/suppliers.php";
require_once __DIR__ . "/../../libs/database/users.php";
$Mobile_Detect = new Mobile_Detect();
if($CMSNT->site("status") != 1) {
    $data = json_encode(["status" => "error", "msg" => __("Hệ thống đang bảo trì!")]);
    exit($data);
}
if(!isset($_REQUEST["action"])) {
    $data = json_encode(["status" => "error", "msg" => __("The Request Not Found")]);
    exit($data);
}
if($_REQUEST["action"] == "buyProduct") {
    if($CMSNT->site("status_demo") != 0) {
        exit(json_encode(["status" => "error", "msg" => __("This function cannot be used because this is a demo site")]));
    }
    if(empty($_REQUEST["api_key"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `api_key` = '" . check_string($_REQUEST["api_key"]) . "' "))) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    if($getUser["banned"] != 0) {
        exit(json_encode(["status" => "error", "msg" => __("Tài khoản của bạn đã bị cấm")]));
    }
    if(time() - $getUser["time_request"] < $CMSNT->site("thoi_gian_mua_cach_nhau")) {
        exit(json_encode(["status" => "error", "msg" => __("Thao tác quá nhanh, vui lòng chờ")]));
    }
    if(empty($_REQUEST["id"])) {
        exit(json_encode(["status" => "error", "msg" => __("Sản phẩm không hợp lệ")]));
    }
    if(!($product = $CMSNT->get_row("SELECT * FROM `products` WHERE `id` = '" . check_string($_REQUEST["id"]) . "' AND `status` = 1 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Sản phẩm không tồn tại trong hệ thống")]));
    }
    if(empty($_REQUEST["amount"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập số lượng cần mua")]));
    }
    $amount = check_string($_REQUEST["amount"]);
    if($amount < $product["min"]) {
        exit(json_encode(["status" => "error", "msg" => __("Số lượng cần mua tối thiểu là") . " " . format_cash($product["min"])]));
    }
    if($product["max"] < $amount) {
        exit(json_encode(["status" => "error", "msg" => __("Số lượng cần mua tối đa là") . " " . format_cash($product["max"])]));
    }
    if(is_numeric($amount) && floor($amount) != $amount) {
        exit(json_encode(["status" => "error", "msg" => __("Số lượng mua không hợp lệ")]));
    }
    if($product["supplier_id"] == 0) {
        if($CMSNT->get_row(" SELECT COUNT(id) FROM `product_stock` WHERE `product_code` = '" . $product["code"] . "' ")["COUNT(id)"] < $amount) {
            exit(json_encode(["status" => "error", "msg" => __("Số lượng còn lại trong hệ thống không đủ")]));
        }
    } elseif(!($supplier = $CMSNT->get_row(" SELECT * FROM `suppliers` WHERE `id` = '" . $product["supplier_id"] . "' AND `status` = 1 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Sản phẩm này đang bảo trì, không thể mua hàng vào lúc này")]));
    }
    $trans_id = random("QWERTYUOPASDFGHJKZXCVBNM123456789", 4) . uniqid();
    $price = $product["discount"] == 0 ? $product["price"] : $product["price"] - $product["price"] * $product["discount"] / 100;
    $money = $amount * $price;
    if($getUser["discount"] == 0) {
        $discount = $money * getDiscount($amount, $product["id"]) / 100;
    } else {
        $discount = $money * $getUser["discount"] / 100;
    }
    $pay = $money - $discount;
    if(!empty($_REQUEST["coupon"])) {
        $discount_coupon = checkCoupon($product["id"], check_string($_REQUEST["coupon"]), $getUser["id"], $money, $pay);
        $discount = $discount + $discount_coupon;
        $pay = $money - $discount;
    }
    if(getRowRealtime("users", $getUser["id"], "money") < $pay) {
        exit(json_encode(["status" => "error", "msg" => __("Số dư không đủ, vui lòng nạp thêm")]));
    }
    $User = new users();
    $isTru = $User->RemoveCredits($getUser["id"], $pay, __("Thanh toán đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "ORDER_" . $trans_id);
    if($isTru) {
        if(getRowRealtime("users", $getUser["id"], "money") < -500) {
            $User->Banned($getUser["id"], __("Gian lận khi mua tài khoản"));
            exit(json_encode(["status" => "error", "msg" => __("Bạn đã bị khoá tài khoản vì gian lận")]));
        }
        $api_trans_id = NULL;
        $isValue = 0;
        if($product["supplier_id"] == 0) {
            $order_by = "ORDER BY id ASC";
            if($product["order_by"] == 1) {
                $order_by = "ORDER BY time_check_live DESC";
            } elseif($product["order_by"] == 2) {
                $order_by = "ORDER BY id ASC";
            } elseif($product["order_by"] == 3) {
                $order_by = "ORDER BY id DESC";
            } elseif($product["order_by"] == 4) {
                $order_by = "ORDER BY RAND()";
            }
            foreach ($CMSNT->get_list(" SELECT * FROM `product_stock` WHERE `product_code` = '" . $product["code"] . "' " . $order_by . " LIMIT " . $amount . " ") as $product_stock) {
                $isInsertSold = $CMSNT->insert("product_sold", ["type" => $product_stock["type"], "product_code" => $product_stock["product_code"], "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product_stock["seller"], "uid" => $product_stock["uid"], "account" => $product_stock["account"], "create_gettime" => gettime(), "time_check_live" => $product_stock["time_check_live"]]);
                if($isInsertSold) {
                    $isValue++;
                    $CMSNT->remove("product_stock", " `id` = '" . $product_stock["id"] . "' ");
                }
            }
        } else {
            if($supplier["type"] == "SHOPCLONE6") {
                $data = curl_get2($supplier["domain"] . "/api/BResource.php?username=" . $supplier["username"] . "&password=" . $supplier["password"] . "&id=" . $product["api_id"] . "&amount=" . $amount);
                $data = json_decode($data, true);
                if(!isset($data)) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["msg"])]));
                }
                if($data["msg"] == "Số dư không đủ, vui lòng nạp thêm") {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __('Tài nguyên không đủ')]));
                }
                if ($data["msg"] == "Số dư API không đủ, vui lòng liên hệ admin") {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __('Tài nguyên không đủ')]));
                }

                if($data["status"] == "error") {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["msg"])]));
                }
                $api_trans_id = $data["data"]["trans_id"];
                foreach ($data["data"]["lists"] as $account) {
                    $account = check_string($account["account"]);
                    list($uid) = explode("|", $account);
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "SHOPCLONE7") {
                $data = buy_API_SHOPCLONE7($supplier["domain"], $supplier["coupon"], $supplier["api_key"], $product["api_id"], $amount);
                $data = json_decode($data, true);
                if(!isset($data)) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["msg"])]));
                }
                
                if($data["msg"] == "Số dư không đủ, vui lòng nạp thêm") {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __('Tài nguyên không đủ')]));
                }

                if ($data["msg"] == "Số dư API không đủ, vui lòng liên hệ admin") {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __('Tài nguyên không đủ')]));
                }
                if($data["status"] == "error") {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["msg"])]));
                }
                $api_trans_id = $data["trans_id"];
                foreach ($data["data"] as $account) {
                    $account = check_string($account);
                    list($uid) = explode("|", $account);
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_1") {
                $dataPost = ["api_key" => $supplier["api_key"], "id_product" => $product["api_id"], "quantity" => $amount];
                $response = buy_API_1($supplier["domain"], $dataPost);
                $data = json_decode($response, true);
                if(!$data["status"]) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["msg"])]));
                }
                $api_trans_id = $data["order_id"];
                $response = order_API_1($supplier["domain"], $supplier["api_key"], $api_trans_id);
                $result = json_decode($response, true);
                foreach ($result["data"] as $account) {
                    $account = check_string($account["full_info"]);
                    list($uid) = explode("|", $account);
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_4") {
                $response = buy_API_4($supplier["domain"], $supplier["token"], $product["api_id"], $amount);
                $result = json_decode($response, true);
                if(!isset($result["data"])) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($result["message"]["messageVNI"])]));
                }
                $api_trans_id = NULL;
                foreach ($result["data"] as $account) {
                    $account = check_string($account);
                    list($uid) = explode("|", $account);
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_6") {
                $response = curl_get2($supplier["domain"] . "/api.php?apikey=" . $supplier["api_key"] . "&action=create-order&service_id=" . $product["api_id"] . "&amount=" . $amount);
                $data = json_decode($response, true);
                if($data["code"] != 200) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["message"])]));
                }
                $api_trans_id = $data["order_id"];
                while (true) {
                    $response = curl_get2($supplier["domain"] . "/api.php?apikey=" . $supplier["api_key"] . "&action=get-order-detail&order_id=" . $api_trans_id);
                    $data_account = json_decode($response, true);
                    if($data_account["order"]["status"] == 1) {
                        break;
                    }
                }
                if(explode(PHP_EOL, $data_account["order"]["data"])) {
                    $lines = explode(PHP_EOL, $data_account["order"]["data"]);
                } else {
                    $lines = $data_account["order"]["data"];
                }
                foreach ($lines as $account) {
                    if(empty($account)) {
                    } else {
                        $account = check_string($account);
                        list($uid) = explode("|", $account);
                        if(!isset(explode("|", $account)[1])) {
                        } else {
                            $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                            if($isInsertAPI) {
                                $isValue++;
                            }
                        }
                    }
                }
            }
            if($supplier["type"] == "API_9") {
                $dataPost = ["type_id" => $product["api_id"], "quantity" => $amount];
                $response = buy_API_9($supplier["domain"], $supplier["api_key"], $dataPost);
                $result = json_decode($response, true);
                if($result["error"] != 0) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($result["error"])]));
                }
                $api_trans_id = $result["data"]["buy_id"];
                foreach ($result["data"]["data"] as $account) {
                    $account = check_string($account);
                    list($uid) = explode("|", $account);
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_14") {
                $response = buy_API_14($supplier["domain"], $supplier["token"], $product["api_id"], $amount);
                $data = json_decode($response, true);
                if($data["error_code"] == 1) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["message"])]));
                }
                $api_trans_id = $data["order_id"];
                while (true) {
                    $response = getOrder_API_14($supplier["domain"], $supplier["token"], $api_trans_id);
                    $data_account = json_decode($response, true);
                    if(isset($data_account["data"])) {
                        break;
                    }
                }
                $lines = explode(PHP_EOL, $data_account["data"]["data"]);
                foreach ($lines as $account) {
                    if($account == "") {
                    } else {
                        $account = check_string($account);
                        list($uid) = explode("|", $account);
                        $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                        if($isInsertAPI) {
                            $isValue++;
                        }
                    }
                }
            }
            if($supplier["type"] == "API_17") {
                $data = buy_API_17($supplier["domain"], $supplier["username"], $supplier["password"], $product["api_id"], $amount);
                $data = json_decode($data, true);
                if(!isset($data)) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["msg"])]));
                }
                if($data["status"] == "error") {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["msg"])]));
                }
                $api_trans_id = $data["data"]["trans_id"];
                foreach ($data["data"]["lists"] as $account) {
                    $account = check_string($account["account"]);
                    list($uid) = explode("|", $account);
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_18") {
                $response = buy_API_18($supplier["domain"], $supplier["api_key"], $product["api_id"], $amount);
                $data = json_decode($response, true);
                if(isset($data["error"])) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __("Số lượng còn lại trong hệ thống không đủ")]));
                }
                $api_trans_id = $data["Data"]["TransId"];
                foreach ($data["Data"]["Emails"] as $account) {
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $account["Email"], "account" => $account["Email"] . "|" . $account["Password"] . "|" . $account["RefreshToken"] . "|" . $account["AccessToken"] . "|" . $account["ClientId"], "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_19") {
                $response = buy_API_19($supplier["domain"], $supplier["api_key"], $product["api_id"], $amount);
                $data = json_decode($response, true);
                if($data["error_code"] != 200) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["message"])]));
                }
                $api_trans_id = $data["data"]["order_code"];
                foreach ($data["data"]["list_data"] as $account) {
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => explode("|", $account)[0], "account" => $account, "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_20") {
                $response = curl_get($supplier["domain"] . "api/buyProducts?kioskToken=" . $supplier["api_key"] . "&userToken=" . $supplier["token"] . "&quantity=" . $amount);
                $data = json_decode($response, true);
                if(!$data["success"]) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["description"])]));
                }
                $api_trans_id = $data["order_id"];
                sleep(1);
                $response = curl_get($supplier["domain"] . "api/getProducts?orderId=" . $api_trans_id . "&userToken=" . $supplier["token"]);
                $result = json_decode($response, true);
                if($result["success"]) {
                    if(isset($result["data"])) {
                        foreach ($result["data"] as $account) {
                            $account = check_string($account["product"]);
                            list($uid) = explode("|", $account);
                            $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $uid, "account" => $account, "create_gettime" => gettime()]);
                            if($isInsertAPI) {
                                $isValue++;
                            }
                        }
                    } else {
                        exit(json_encode(["status" => "error", "msg" => __($data)]));
                    }
                } else {
                    exit(json_encode(["status" => "error", "msg" => __($data["description"])]));
                }
            }
            if($supplier["type"] == "API_21") {
                $response = buy_API_21($supplier["domain"], $supplier["token"], $product["api_id"], $amount);
                $data = json_decode($response, true);
                if(!$data["status"]) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["message"])]));
                }
                $api_trans_id = NULL;
                foreach ($data["data"] as $account) {
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => explode("|", $account)[0], "account" => $account, "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_22") {
                $response = buy_API_22($supplier["domain"], $supplier["token"], $product["api_id"], $amount);
                $data = json_decode($response, true);
                if($data["status"] != "success") {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __($data["message"])]));
                }
                $api_trans_id = NULL;
                foreach ($data["data"] as $account) {
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $account["email"], "account" => $account["email"] . "|" . $account["password"] . "|" . $account["refresh_token"] . "|" . $account["client_id"], "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_23") {
                $response = buy_API_23($supplier["domain"], $supplier["api_key"], $product["api_id"], $amount);
                $data = json_decode($response, true);
                if(isset($data["Code"]) && $data["Code"] == 1) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __("Số lượng còn lại trong hệ thống không đủ")]));
                }
                $api_trans_id = $data["PurchaseId"];
                foreach ($data["Accounts"] as $account) {
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $account["Email"], "account" => $account["Email"] . "|" . $account["Password"] . "|" . $account["RefreshToken"] . "|" . $account["ClientId"], "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
            if($supplier["type"] == "API_24") {
                $response = buy_API_24($supplier["domain"], $supplier["api_key"], $product["api_id"], $amount);
                $data = json_decode($response, true);
                if(!isset($data["data"])) {
                    $User->RefundCredits($getUser["id"], $pay, "[Error] " . __("Hoàn tiền đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "REFUND_" . $trans_id);
                    exit(json_encode(["status" => "error", "msg" => __("Số lượng còn lại trong hệ thống không đủ")]));
                }
                foreach ($data["data"] as $account) {
                    $api_trans_id = $account["order_id"];
                    $isInsertAPI = $CMSNT->insert("product_sold", ["type" => $supplier["domain"], "product_code" => NULL, "supplier_id" => $product["supplier_id"], "trans_id" => $trans_id, "buyer" => $getUser["id"], "seller" => $product["user_id"], "uid" => $account["email"], "account" => $account["email"] . "|" . $account["password"], "create_gettime" => gettime()]);
                    if($isInsertAPI) {
                        $isValue++;
                    }
                }
            }
        }
        if(0 < $isValue) {
            if($product["supplier_id"] == 0 && $isValue < $amount) {
                foreach ($CMSNT->get_list(" SELECT * FROM `product_sold` WHERE `trans_id` = '" . $trans_id . "' AND `supplier_id` = 0 ") as $product_sold) {
                    $isInsertStock = $CMSNT->insert("product_stock", ["product_code" => $product_sold["product_code"], "seller" => $product_sold["seller"], "uid" => $product_sold["uid"], "account" => $product_sold["account"], "status" => $product_sold["status"], "create_gettime" => gettime(), "type" => "Back", "time_check_live" => $product_sold["time_check_live"]]);
                    if($isInsertStock) {
                        $CMSNT->remove("product_sold", " `id` = '" . $product_sold["id"] . "' ");
                    }
                }
                $User->RefundCredits($getUser["id"], $pay, "Hoàn tiền đơn hàng mua tài khoản #" . $trans_id, "REFUND_" . $trans_id);
                exit(json_encode(["status" => "error", "msg" => __("Số lượng còn lại trong hệ thống không đủ")]));
            } else {
                $isInsertOrder = $CMSNT->insert("product_order", ["trans_id" => $trans_id, "api_transid" => $api_trans_id, "supplier_id" => $product["supplier_id"], "product_id" => $product["id"], "product_name" => $product["name"], "buyer" => $getUser["id"], "seller" => $product["user_id"], "amount" => $amount, "money" => $money, "pay" => $pay, "cost" => $product["cost"] * $amount, "create_gettime" => gettime(), "update_gettime" => gettime(), "trash" => 0, "status_view_order" => $getUser["status_view_order"], "ip" => myip(), "device" => $Mobile_Detect->getUserAgent()]);
                if($isInsertOrder) {
                    if($CMSNT->site("cong_tien_nguoi_ban") == 1) {
                        $User->AddCredits($product["user_id"], $pay, __("Doanh thu đơn hàng mua tài khoản") . " <b>" . $product["name"] . "</b> - #" . $trans_id, "DOANH_THU_" . $trans_id);
                    }
                    if($CMSNT->site("affiliate_status") == 1 && $getUser["ref_id"] != 0) {
                        $ck = $CMSNT->site("affiliate_ck");
                        if(getRowRealtime("users", $getUser["ref_id"], "ref_ck") != 0) {
                            $ck = getRowRealtime("users", $getUser["ref_id"], "ref_ck");
                        }
                        $price = $pay * $ck / 100;
                        $User->AddCommission($getUser["ref_id"], $getUser["id"], $price, __("Hoa hồng thành viên " . $getUser["username"]));
                    }
                    if(isset($discount_coupon) && 0 < $discount_coupon) {
                        $isAddCoupon = $CMSNT->cong("coupons", "used", 1, " `code` = '" . check_string($_REQUEST["coupon"]) . "' ");
                        if($isAddCoupon) {
                            $CMSNT->insert("coupon_used", ["coupon_id" => $CMSNT->get_row("SELECT * FROM `coupons` WHERE `code` = '" . check_string($_REQUEST["coupon"]) . "' ")["id"], "user_id" => $getUser["id"], "trans_id" => $trans_id, "create_gettime" => gettime()]);
                        }
                    }
                    $CMSNT->cong("products", "sold", $amount, " `id` = '" . $product["id"] . "' ");
                    $accounts = [];
                    $file_txt_email = "";
                    foreach ($CMSNT->get_list("SELECT * FROM `product_sold` WHERE `trans_id` = '" . $trans_id . "' ") as $account_sold) {
                        $accounts[] = preg_replace("/\r/", "", $account_sold["account"]);
                        $file_txt_email .= PHP_EOL . htmlspecialchars_decode($account_sold["account"]);
                    }
                    $CMSNT->update("users", ["time_request" => time()], " `id` = '" . $getUser["id"] . "' ");
                    $CMSNT->insert("order_log", ["buyer" => $getUser["id"], "product_name" => $product["name"], "pay" => $pay, "amount" => $amount, "create_time" => time(), "is_virtual" => 0]);
                    if($CMSNT->site("email_temp_subject_buy_order") != "") {
                        $content = $CMSNT->site("email_temp_content_buy_order");
                        $content = str_replace("{domain}", $_SERVER["SERVER_NAME"], $content);
                        $content = str_replace("{title}", $CMSNT->site("title"), $content);
                        $content = str_replace("{username}", $getUser["username"], $content);
                        $content = str_replace("{ip}", myip(), $content);
                        $content = str_replace("{device}", $Mobile_Detect->getUserAgent(), $content);
                        $content = str_replace("{time}", gettime(), $content);
                        $content = str_replace("{product}", $product["name"], $content);
                        $content = str_replace("{amount}", format_cash($amount), $content);
                        $content = str_replace("{trans_id}", $trans_id, $content);
                        $content = str_replace("{pay}", format_currency($pay), $content);
                        $subject = $CMSNT->site("email_temp_subject_buy_order");
                        $subject = str_replace("{domain}", $_SERVER["SERVER_NAME"], $subject);
                        $subject = str_replace("{title}", $CMSNT->site("title"), $subject);
                        $subject = str_replace("{username}", $getUser["username"], $subject);
                        $subject = str_replace("{ip}", myip(), $subject);
                        $subject = str_replace("{device}", $Mobile_Detect->getUserAgent(), $subject);
                        $subject = str_replace("{time}", gettime(), $subject);
                        $subject = str_replace("{product}", $product["name"], $subject);
                        $subject = str_replace("{amount}", format_cash($amount), $subject);
                        $subject = str_replace("{trans_id}", $trans_id, $subject);
                        $subject = str_replace("{pay}", format_currency($pay), $subject);
                        $bcc = $CMSNT->site("title");
                        $file_txt_name = $trans_id . ".txt";
                        file_put_contents($file_txt_name, $file_txt_email);
                        sendCSM($getUser["email"], $getUser["username"], $subject, $content, $bcc, $file_txt_name);
                        unlink($file_txt_name);
                    }
                    if($CMSNT->site("noti_buy_product") != "") {
                        $my_text = $CMSNT->site("noti_buy_product");
                        $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                        $my_text = str_replace("{username}", $getUser["username"], $my_text);
                        $my_text = str_replace("{product}", $product["name"], $my_text);
                        $my_text = str_replace("{amount}", format_cash($amount), $my_text);
                        $my_text = str_replace("{trans_id}", $trans_id, $my_text);
                        $my_text = str_replace("{pay}", format_currency($pay), $my_text);
                        $my_text = str_replace("{ip}", myip(), $my_text);
                        $my_text = str_replace("{time}", gettime(), $my_text);
                        sendMessAdmin($my_text);
                    }
                    exit(json_encode(["status" => "success", "msg" => __("Tạo đơn hàng thành công!"), "trans_id" => $trans_id, "data" => $accounts]));
                } else {
                    exit(json_encode(["status" => "error", "msg" => "ERROR 1 - " . __("System error")]));
                }
            }
        } else {
            $User->RefundCredits($getUser["id"], $pay, __("[Error 2] Hoàn tiền đơn hàng mua tài khoản") . " #" . $trans_id, "REFUND_" . $trans_id);
            exit(json_encode(["status" => "error", "msg" => __("Số lượng còn lại trong hệ thống không đủ")]));
        }
    } else {
        exit(json_encode(["status" => "error", "msg" => "ERROR 2 - " . __("Vui lòng thử lại")]));
    }
} else {
    if($_REQUEST["action"] == "total_payment") {
        if(empty($_REQUEST["id"])) {
            exit(json_encode(["status" => "error", "msg" => __("Sản phẩm không tồn tại trong hệ thống")]));
        }
        $id = check_string($_REQUEST["id"]);
        if(empty($_REQUEST["amount"])) {
            exit(json_encode(["status" => "error", "msg" => __("Vui lòng nhập số lượng cần mua")]));
        }
        $amount = check_string($_REQUEST["amount"]);
        if(!($product = $CMSNT->get_row("SELECT * FROM `products` WHERE `id` = '" . $id . "' AND `status` = 1 "))) {
            exit(json_encode(["status" => "error", "msg" => __("Sản phẩm không khả dụng")]));
        }
        $discount = 0;
        $price = $product["discount"] == 0 ? $product["price"] : $product["price"] - $product["price"] * $product["discount"] / 100;
        $money = $amount * $price;
        $pay = $money;
        if(!empty($_REQUEST["token"])) {
            $token = check_string($_REQUEST["token"]);
            if($getUser = $CMSNT->get_row(" SELECT * FROM `users` WHERE `token` = '" . $token . "' AND `banned` = 0 ")) {
                if($getUser["discount"] == 0) {
                    $discount = $money * getDiscount($amount, $product["id"]) / 100;
                } else {
                    $discount = $money * $getUser["discount"] / 100;
                }
                $pay = $money - $discount;
                if(!empty($_REQUEST["coupon"])) {
                    $discount_coupon = checkCoupon($product["id"], check_string($_REQUEST["coupon"]), $getUser["id"], $money, $pay);
                    $discount = $discount + $discount_coupon;
                    $pay = $money - $discount;
                }
            }
        } else {
            $discount = $money * getDiscount($amount, $product["id"]) / 100;
        }
        exit(json_encode(["status" => "success", "money" => format_currency($money), "discount" => format_currency($discount), "discount_number" => $discount, "pay" => format_currency($pay)]));
    }
    exit(json_encode(["status" => "error", "msg" => __("Request does not exist")]));
}

?>