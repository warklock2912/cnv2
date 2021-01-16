<?php

//Import necessary classes
require "../utils/PaymentGatewayHelper.php";

//Check request URI
if(!strpos($_SERVER['REQUEST_URI'], '/backendResponse')) {

    http_response_code(404);
    echo "Wrong request URI, please make sure it's `/backendResponse`";

    exit();
}

//Get SecretKey from 2C2P PGW dashboard
$secret_key = "7jYcp4FxFdf0";

//Get payment response from POST method
$response_payload_json = $_REQUEST["paymentResponse"];

//Important:
//Init 2C2P PaymentGatewayHelper
$pgw_helper = new PaymentGatewayHelper();

//Check Payload availability
if($pgw_helper->containPayload($response_payload_json)) {

    // Important: Verify response payload
    if($pgw_helper->validatePayload($response_payload_json, $secret_key)) {

        //Parse response
        $payment_inquiry_response = $pgw_helper->parseAPIResponse($response_payload_json);

        //Get payment result
        $invoice_no = $payment_inquiry_response->invoiceNo;
        $resp_code = $payment_inquiry_response->respCode;

        //Store payment result into your databases for transantion record.
        echo json_encode(get_object_vars($payment_inquiry_response));
    } else {

        //Return error response
        echo json_encode(get_object_vars(new SignatureError()));
    }
} else {

    //Show error response from API
    print_r($response_payload_json);
}


?>
