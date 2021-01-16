<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Import necessary classes
require "../utils/PaymentGatewayHelper.php";
require "../enum/APIEnvironment.php";
require "../model/SignatureError.php";

//Check request URI
if(!strpos($_SERVER['REQUEST_URI'], '/paymentToken')) {

    http_response_code(404);
    echo "Wrong request URI, please make sure it's `/paymentToken`";

    exit();
}

//Set API request enviroment
$api_environment = APIEnvironment::SANDBOX . "paymentToken";

//Request information
//Generate an unique random string
$nonce_str = uniqid('', true);
//Set API locale
$locale = "en";
//Set frontend return URL
$frontend_return_url = "https://www.merchant.com/frontend/index.php";
//Set backend return URL
$backend_return_url = "https://www.merchant.com/backend/index.php";

//Merchant's account information
//Get MerchantID when opening account with 2C2P
$mid = "JT04";
//Get SecretKey from 2C2P PGW dashboard
$secret_key = "7jYcp4FxFdf0";

//Transaction information
//Set product description
$desc = "2 days 1 night hotel room";
//Set an unique invoice no
$invoice_no = "IN" . time();
//Set currency code in 3 alphabet values as specified in ISO 4217
$currency_code = "THB";
//Set amount
$amount = 10.00;

//Set payment options
//Set payment channel
$payment_channel = array("ALL");
//Set credit card 3D Secure mode
$request_3ds = "Y";
//Enable Card Tokenize option on V4-UI
$tokenize = true;
//Enforce user use card token only instead of new card on V4-UI
$card_token_only = true;

//Card Token
$card_tokens = array("08051916093675382631", "00703161432274043492");

//promotion code
$promotion_code = "";

//Set advance payment options
//IPP
$interest_type = "M";
$installment_period_filter = array(3, 6);
$product_code = "";

//Recurring
$recurring = true;				//Enable / Disable RPP option
$invoice_prefix = 'demo'.time();			//RPP transaction invoice prefix
$recurring_amount = 1.00;		//Recurring amount
$allow_accumulate = true;				//Allow failed authorization to be accumulated
$max_accumulate_amount = 10.00;				//Maximum threshold of total accumulated amount
$recurring_interval = 5;			//Recurring interval by no of days
$recurring_count = 3;				//Number of Recurring occurance
$charge_next_date = (new DateTime('tomorrow'))->format("dmY");	//The first day to start recurring charges. format DDMMYYYY
$charge_on_date = (new DateTime('tomorrow'))->format("dmY");

//Tokenization without authorization
$tokenize_only = true;

//Payment expiry date
$payment_expiry = (new DateTime('tomorrow'))->format("dmY");

//Payment route Id
$payment_route_id = "";

//FX
$fx_provider_code = "";

//Master merchant account
$sub_merchant_1 = new stdClass;
$sub_merchant_1->merchantID = "ztest001";
$sub_merchant_1->invoiceNo = "SubInvoice123";
$sub_merchant_1->description = "Hotel towel";
$sub_merchant_1->amount = 5.00;

$sub_merchant_2 = new stdClass;
$sub_merchant_2->merchantID = "ztest002";
$sub_merchant_2->invoiceNo = "SubInvoice345";
$sub_merchant_2->description = "Hotel shampoo";
$sub_merchant_2->amount = 5.00;

$sub_merchants = array($sub_merchant_1, $sub_merchant_2);

//UI Parameter for V4-UI
$immediate_payment = true;
$ui_params = new stdClass;

//User info for auto fill-in input form on V4-UI
$user_info = new stdClass;
$user_info->name = "DavidBilly";
$user_info->email = "davidbilly@2c2p.com";
$user_info->mobileNo = "088888888";
$user_info->countryCode = "SGP";
$user_info->mobileNoPrefix = "65";
$user_info->currencyCode = $currency_code;

$ui_params->userInfo = $user_info;

//---------------------------------- Request ---------------------------------------//

//Construct payment token request
$payment_token_request = new stdClass();
$payment_token_request->merchantID = $mid;
$payment_token_request->invoiceNo = $invoice_no;
$payment_token_request->description = $desc;
$payment_token_request->amount = $amount;
$payment_token_request->currencyCode = $currency_code;
$payment_token_request->paymentChannel = $payment_channel;
$payment_token_request->request3DS = $request_3ds;
// $payment_token_request->tokenize = $tokenize;
// $payment_token_request->cardTokens = $card_tokens;
// $payment_token_request->cardTokenOnly = $card_token_only;
// $payment_token_request->tokenizeOnly = $tokenize_only;
// $payment_token_request->interestType = $interest_type;
// $payment_token_request->installmentPeriodFilter = $installment_period_filter;
// $payment_token_request->productCode = $product_code;
// $payment_token_request->recurring = $recurring;
// $payment_token_request->invoicePrefix = $invoice_prefix;
// $payment_token_request->recurringAmount = $recurring_amount;
// $payment_token_request->allowAccumulate = $allow_accumulate;
// $payment_token_request->maxAccumulateAmount = $max_accumulate_amount;
// $payment_token_request->recurringInterval = $recurring_interval;
// $payment_token_request->recurringCount = $recurring_count;
// $payment_token_request->chargeNextDate = $charge_next_date;
// $payment_token_request->chargeOnDate = $charge_on_date;
// $payment_token_request->paymentExpiry = $payment_expiry;
// $payment_token_request->promotionCode = $promotion_code;
// $payment_token_request->paymentRouteID = $payment_route_id;
// $payment_token_request->fxProviderCode = $fx_provider_code;
// $payment_token_request->immediatePayment = $immediate_payment;
// $payment_token_request->userDefined1 = "This is my user defined 1";
// $payment_token_request->userDefined2 = "This is my user defined 2";
// $payment_token_request->userDefined3 = "This is my user defined 3";
// $payment_token_request->userDefined4 = "This is my user defined 4";
// $payment_token_request->userDefined5 = "This is my user defined 5";
// $payment_token_request->statementDescriptor = "Statement descriptor";
// $payment_token_request->subMerchants = $sub_merchants;
// $payment_token_request->locale = $locale;
// $payment_token_request->frontendReturnUrl = $frontend_return_url;
// $payment_token_request->backendReturnUrl = $backend_return_url;
$payment_token_request->nonceStr = $nonce_str;
// $payment_token_request->uiParams = $ui_params;

//Important:
//Init 2C2P PaymentGatewayHelper
$pgw_helper = new PaymentGatewayHelper();

//Generate payload
$request_payload_json = $pgw_helper->generatePayload($payment_token_request, $secret_key);

//---------------------------------- Response ---------------------------------------//

//Do Payment Token API request
$response_payload_json = $pgw_helper->requestAPI($api_environment, $request_payload_json);

//Check Payload availability
if($pgw_helper->containPayload($response_payload_json)) {

    // Important: Verify response payload
    if($pgw_helper->validatePayload($response_payload_json, $secret_key)) {

        //Parse response
        $payment_token_response = $pgw_helper->parseAPIResponse($response_payload_json);

        //Get payment token and pass token to your mobile application.
        echo $payment_token = $payment_token_response->paymentToken;

        //Or open V4UI for web payment
        // echo $web_payment_url = $payment_token_response->webPaymentUrl;
    } else {

        //Return error response
        echo json_encode(get_object_vars(new SignatureError()));
    }
} else {

    //Show error response from API
    print_r($response_payload_json);
}

?>
