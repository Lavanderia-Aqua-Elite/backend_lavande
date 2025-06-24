<?php
declare(strict_types=1);

namespace app\api\Paypal;

$paypal = curl_init();

if($paypal === CURLE_OK) {
    curl_setopt($paypal, CURLOPT_URL, "https://api-m.sandbox.paypal.com/v1/oauth2/token");
    curl_setopt($paypal, CURLOPT_HEADER, 0);

}
if(curl_exec($paypal) === false) {
    echo json_encode([
        "message" => "No se ejecuto el api"
    ]);
    exit;
}

curl_close($paypal);