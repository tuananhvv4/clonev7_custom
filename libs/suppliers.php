<?php

if(!defined("IN_SITE")) {
    exit("The Request Not Found");
}
function balance_API_24($domain, $api_key)
{
    return curl_get2($domain . "api/checkapikey=" . $api_key);
}
function buy_API_24($domain, $api_key, $api_id, $amount)
{
    return curl_get($domain . "api/byproduct/apikey=" . $api_key . "&product_id=" . $api_id . "&quality=" . $amount);
}
function listProduct_API_24($domain, $api_key)
{
    return curl_get($domain . "api/checkprice=" . $api_key);
}
function buy_API_23($domain, $api_key, $api_id, $amount)
{
    return curl_get($domain . "purchase?api_key=" . $api_key . "&accountcode=" . $api_id . "&quantity=" . $amount);
}
function listProduct_API_23($domain)
{
    return curl_get($domain . "instock");
}
function balance_API_23($domain, $api_key)
{
    return curl_get2($domain . "balance?api_key=" . $api_key);
}
function buy_API_22($domain, $token, $product_id, $amount)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "api/buyHotMailUd", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["quantity" => $amount, "token" => $token, "product_id" => $product_id]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function listProduct_API_22($domain, $token)
{
    return curl_get($domain . "api/quantity?token=" . $token);
}
function buy_API_17($domain, $username, $password, $api_id, $amount)
{
    return curl_get2($domain . "/api/BResource.php?username=" . $username . "&password=" . $password . "&id=" . $api_id . "&amount=" . $amount);
}
function listProduct_API_17($domain, $username, $password)
{
    return curl_get2($domain . "/api/CategoryList.php?username=" . $username . "&password=" . $password);
}
function balance_API_17($domain, $username, $password)
{
    return curl_get($domain . "/api/GetBalance.php?username=" . $username . "&password=" . $password);
}
function buy_API_21($domain, $token, $product_id, $amount)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "api/buy-products", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["quantity" => $amount, "token" => $token, "product_id" => $product_id]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function listProduct_API_21($domain, $token)
{
    return curl_get($domain . "api/quantity?token=" . $token);
}
function buy_API_9($domain, $password, $dataPost)
{
    $data = json_encode($dataPost);
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "v1/api/buy?api_key=" . $password, CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 0, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => $data, CURLOPT_HTTPHEADER => ["Content-Type: application/json"]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function listProduct_API_9($domain, $password)
{
    return curl_get($domain . "v1/api/categories?api_key=" . $password);
}
function balance_API_9($domain, $password)
{
    return curl_get($domain . "v1/api/me?api_key=" . $password);
}
function buy_API_4($domain, $token, $id_product, $amount)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "v1/user/partnerbuy", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["amount" => $amount, "categoryId" => $id_product], CURLOPT_HTTPHEADER => ["authorization: " . $token]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function balance_API_4($domain, $username, $password)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "v1/user/login", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["username" => $username, "password" => $password]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function listProduct_API_4($domain)
{
    return curl_get2($domain . "v1/public/category/list");
}
function buy_API_19($domain, $api_key, $id_api, $amount)
{
    return curl_get2($domain . "user/buy?apikey=" . $api_key . "&account_type=" . $id_api . "&quality=" . $amount . "&type=null");
}
function listProduct_API_19($domain, $api_key)
{
    return curl_get2($domain . "user/account_type?apikey=" . $api_key);
}
function balance_API_19($domain, $api_key)
{
    return curl_get2($domain . "user/balance?apikey=" . $api_key);
}
function buy_API_18($domain, $api_key, $id_api, $amount)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "mail/buy?mailcode=" . $id_api . "&quantity=" . $amount, CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $api_key]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function listProduct_API_18($domain)
{
    return curl_get($domain . "mail/currentstock");
}
function balance_API_18($domain, $apikey)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "auth/me", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "GET", CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $apikey]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function buy_API_SHOPCLONE7($domain, $coupon, $api_key, $id_api, $amount)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "ajaxs/client/product.php", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["action" => "buyProduct", "id" => $id_api, "amount" => $amount, "coupon" => $coupon, "api_key" => $api_key], CURLOPT_HTTPHEADER => []]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function listProduct_API_SHOPCLONE7($domain, $api_key)
{
    return curl_get2($domain . "/api/products.php?api_key=" . $api_key);
}
function balance_API_SHOPCLONE7($domain, $api_key)
{
    return curl_get2($domain . "api/profile.php?api_key=" . $api_key);
}
function getOrder_API_14($domain, $token, $order_id)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "api", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_HTTPHEADER => ["Authorization: " . $token], CURLOPT_POSTFIELDS => "{\n            \"act\": \"Get-Order\",\n            \"data\": {\n                \"order_id\": " . $order_id . "\n            }\n        }"]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function buy_API_14($domain, $token, $id_api, $amount)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "api", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_HTTPHEADER => ["Authorization: " . $token], CURLOPT_POSTFIELDS => "{\n        \"act\": \"Create-Order\",\n        \"data\": {\n            \"service_id\": " . $id_api . ",\n            \"quantity\": " . $amount . "\n        }\n    }"]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function listProduct_API_14($domain, $token)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "api", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["act" => "Get-Products"], CURLOPT_HTTPHEADER => ["Authorization: " . $token]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function balance_API_14($domain, $token)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "api", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["act" => "Me"], CURLOPT_HTTPHEADER => ["Authorization: " . $token]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function balance_API_6($domain, $api_key)
{
    return curl_get($domain . "/api.php?apikey=" . $api_key . "&action=get-balance");
}
function balance_API_1($domain, $token)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "api/v1/balance", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 10, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["api_key" => $token]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function listProduct_API_1($domain)
{
    return curl_get2($domain . "api/v1/categories");
}
function buy_API_1($domain, $dataPost)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "api/v1/buy", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => $dataPost]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function order_API_1($domain, $api_key, $order_id)
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_URL => $domain . "api/v1/order", CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => "", CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST", CURLOPT_POSTFIELDS => ["api_key" => $api_key, "order_id" => $order_id]]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

?>