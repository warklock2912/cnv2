<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Import necessary classes
require "../utils/PaymentGatewayHelper.php";
require "../enum/APIEnvironment.php";
require "../model/SignatureError.php";

//Check request URI
if(!strpos($_SERVER['REQUEST_URI'], '/paymentInquiry')) {

    http_response_code(404);
    echo "Wrong request URI, please make sure it's `/paymentInquiry`";

    exit();
}

//Set API request enviroment
$api_environment = APIEnvironment::SANDBOX . "paymentInquiry";

//Request information
//Set your payment token
$payment_token = "kSAops9Zwhos8hSTSeLTUcI4IrvCos0pedoYR6Sp04aLJdWhFR2JaByqgUo9HOcnUo7U2svTEAsIUemRvDdAreQsp6ZybV0ZRWSR17avQxk=";

//Merchant's account information
//Get MerchantID when opening account with 2C2P
$mid = "JT04";
//Get SecretKey from 2C2P PGW dashboard
$secret_key = "7jYcp4FxFdf0";

//Inquiry information
//Set your invoice no
$invoice_no = "1590156388";

//---------------------------------- Request ---------------------------------------//

//Build payment inquiry request
$payment_inquiry_request = new stdClass();

//Can be using either payment token or mid with invoice no
$payment_inquiry_request->paymentToken = $payment_token;

//Or

// $payment_inquiry_request->merchantID = $mid;
// $payment_inquiry_request->invoiceNo = $invoice_no;

$payment_inquiry_request->locale = "en";

//Important:
//Init 2C2P PaymentGatewayHelper
$pgw_helper = new PaymentGatewayHelper();

//Generate payload
$request_payload_json = $pgw_helper->generatePayload($payment_inquiry_request, $secret_key);

//---------------------------------- Response ---------------------------------------//

//Do Payment Inquiry API request
$response_payload_json = $pgw_helper->requestAPI($api_environment, $request_payload_json);

//Check Payload availability
if($pgw_helper->containPayload($response_payload_json)) {

    // Important: Verify response payload
    if($pgw_helper->validatePayload($response_payload_json, $secret_key)) {

        //Parse response
        $payment_inquiry_response = $pgw_helper->parseAPIResponse($response_payload_json);

        //Get payment result
        $invoice_no = $payment_inquiry_response->invoiceNo;
        $resp_code = $payment_inquiry_response->respCode;

        //Pass payment result to your mobile application.
        echo base64_encode(json_encode(get_object_vars($payment_inquiry_response)));
    } else {

        //Return error response
        echo json_encode(get_object_vars(new SignatureError()));
    }
} else {

    //Show error response from API
    print_r($response_payload_json);
}

?>
