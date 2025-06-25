<?php
declare(strict_types=1);

namespace app\api\Paypal;

require __DIR__ . "/../../../vendor/autoload.php";

class PaypalAPI
{
    private $clientID;
    private $secret;
    private $url;

    public function __construct()
    {
        $this->url = $_ENV["API_URL_PAYPAL"];
        $this->clientID = $_ENV["KEY_PAYPAL_CLIENT_ID"];
        $this->secret = $_ENV["KEY_PAYPAL_CLIENT_SECRET"];
    }

    /*
        @param string url
        @param string id
        @param string secret
        @param array header
    */
    public function getToken() {
        $ch = curl_init();

        $request_header = [
            "Accept" => "application/json"
        ];
        
       $options = [
        CURLOPT_URL => $this->url,
        CURLOPT_HTTPHEADER => $request_header,
        CURLOPT_POSTFIELDS => $this->clientID,
        CURLOPT_USERPWD => $this->secret
       ];

       curl_setopt_array($ch, $options);

       if($ch === CURLE_OK) {
            curl_exec($ch);
       }

       curl_close($ch);
    }

    public function setPayment()
    {
        
    }
}