<?php

define("IN_SITE", true);
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../libs/db.php";
require_once __DIR__ . "/../../libs/lang.php";
require_once __DIR__ . "/../../libs/helper.php";
require_once __DIR__ . "/../../libs/database/users.php";
if(!isset($_POST["action"])) {
    $data = json_encode(["status" => "error", "msg" => __("The Request Not Found")]);
    exit($data);
}
if($_POST["action"] == "saveNoteOrder") {
    if(empty($_POST["id"])) {
        exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không tồn tại trong hệ thống")]));
    }
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
    }
    $id = check_string($_POST["id"]);
    if(!($order = $CMSNT->get_row("SELECT * FROM `product_order` WHERE `id` = '" . $id . "' AND `buyer` = " . $getUser["id"] . " "))) {
        exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không tồn tại trong hệ thống")]));
    }
    $isUpdate = $CMSNT->update("product_order", ["note" => !empty($_POST["note"]) ? check_string($_POST["note"]) : NULL], " `id` = '" . $order["id"] . "' ");
    if($isUpdate) {
        exit(json_encode(["status" => "success", "msg" => __("Cập nhật ghi chú thành công!")]));
    }
    exit(json_encode(["status" => "error", "msg" => __("Cập nhật ghi chú thất bại!")]));
}
if($_POST["action"] == "toggleFavorite") {
    if(empty($_POST["id"])) {
        exit(json_encode(["status" => "error", "msg" => __("Sản phẩm không hợp lệ")]));
    }
    $id = check_string($_POST["id"]);
    if(!($product = $CMSNT->get_row("SELECT * FROM `products` WHERE `id` = '" . $id . "' AND `status` = 1 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Sản phẩm không tồn tại trong hệ thống")]));
    }
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập để sử dụng tính năng này")]));
    }
    if($isFavorite = $CMSNT->get_row(" SELECT * FROM `favorites` WHERE `user_id` = '" . $getUser["id"] . "' AND `product_id` = '" . $product["id"] . "' ")) {
        $CMSNT->remove("favorites", " `id` = '" . $isFavorite["id"] . "' ");
        exit(json_encode(["status" => "success", "button" => false, "msg" => __("Đã xóa sản phẩm khỏi danh sách yêu thích")]));
    }
    $CMSNT->insert("favorites", ["product_id" => $product["id"], "user_id" => $getUser["id"], "create_gettime" => gettime()]);
    exit(json_encode(["status" => "success", "button" => true, "msg" => __("Đã thêm sản phẩm vào danh sách yêu thích")]));
}
if($_POST["action"] == "changeLanguage") {
    if(empty($_POST["id"])) {
        exit(json_encode(["status" => "error", "msg" => __("Data does not exist")]));
    }
    $id = check_string($_POST["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `languages` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        exit(json_encode(["status" => "error", "msg" => __("Data does not exist")]));
    }
    $isUpdate = setLanguage($id);
    if($isUpdate) {
        $data = json_encode(["status" => "success", "msg" => __("Change language successfully")]);
        exit($data);
    }
}
if($_POST["action"] == "changeCurrency") {
    if(empty($_POST["id"])) {
        exit(json_encode(["status" => "error", "msg" => __("Data does not exist")]));
    }
    $id = check_string($_POST["id"]);
    $row = $CMSNT->get_row("SELECT * FROM `currencies` WHERE `id` = '" . $id . "' ");
    if(!$row) {
        exit(json_encode(["status" => "error", "msg" => __("Data does not exist")]));
    }
    $isUpdate = setCurrency($id);
    if($isUpdate) {
        $data = json_encode(["status" => "success", "msg" => __("Successful currency change")]);
        exit($data);
    }
}
if($CMSNT->site("status_demo") != 0) {
    $data = json_encode(["status" => "error", "msg" => __("This function cannot be used because this is a demo site")]);
    exit($data);
}
if($_POST["action"] == "confirmPaypal" && isset($_POST["order"])) {
    if($CMSNT->site("paypal_status") != 1) {
        exit(json_encode(["status" => "error", "msg" => __("Chức năng này đang được bảo trì")]));
    }
    if(empty($_POST["token"])) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    if(!($getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '" . check_string($_POST["token"]) . "' AND `banned` = 0 "))) {
        exit(json_encode(["status" => "error", "msg" => __("Vui lòng đăng nhập")]));
    }
    $clientId = $CMSNT->site("paypal_clientId");
    $clientSecret = $CMSNT->site("paypal_clientSecret");
    $environment = new PayPalCheckoutSdk\Core\ProductionEnvironment($clientId, $clientSecret);
    $client = new PayPalCheckoutSdk\Core\PayPalHttpClient($environment);
    $orderData = $_POST["order"];
    $request = new PayPalCheckoutSdk\Orders\OrdersGetRequest($orderData["id"]);
    try {
        $response = $client->execute($request);
        if($response->statusCode != 200) {
            exit(json_encode(["status" => "error", "msg" => __("Đã xảy ra lỗi!")]));
        }
        $order = $response->result;
        if($order->status != "COMPLETED") {
            exit(json_encode(["status" => "error", "msg" => __("Đơn hàng không hợp lệ hoặc chưa thanh toán")]));
        }
        $orderDetail = $order->purchase_units[0];
        if(0 < $CMSNT->num_rows("SELECT * FROM `payment_paypal` WHERE `trans_id` = '" . $order->id . "' ")) {
            exit(json_encode(["status" => "error", "msg" => __("Giao dịch này đã được xử lý")]));
        }
        $price = $CMSNT->site("paypal_rate") * $orderDetail->amount->value;
        $isInsert = $CMSNT->insert("payment_paypal", ["user_id" => $getUser["id"], "trans_id" => $order->id, "amount" => $orderDetail->amount->value, "price" => $price, "create_date" => gettime(), "create_time" => time()]);
        if($isInsert) {
            $user = new users();
            $isCong = $user->AddCredits($getUser["id"], $price, __("Nạp tiền tự động qua PayPal") . " - " . $order->id, "TOPUP_PAYPAL_" . $order->id);
            if($isCong) {
                $my_text = $CMSNT->site("noti_recharge");
                $my_text = str_replace("{domain}", $_SERVER["SERVER_NAME"], $my_text);
                $my_text = str_replace("{username}", $getUser["username"], $my_text);
                $my_text = str_replace("{method}", "PayPal", $my_text);
                $my_text = str_replace("{amount}", $orderDetail->amount->value, $my_text);
                $my_text = str_replace("{price}", $price, $my_text);
                $my_text = str_replace("{time}", gettime(), $my_text);
                sendMessAdmin($my_text);
                exit(json_encode(["status" => "success", "msg" => __("Nạp tiền thành công")]));
            }
            exit(json_encode(["status" => "error", "msg" => __("Hóa đơn này đã được cộng tiền rồi")]));
        }
    } catch (PayPalHttp\HttpException $e) {
        exit(json_encode(["status" => "error", "msg" => $e->getMessage()]));
    } catch (Exception $e) {
        exit(json_encode(["status" => "error", "msg" => $e->getMessage()]));
    }
}
exit(json_encode(["status" => "error", "msg" => __("Invalid data")]));

?>