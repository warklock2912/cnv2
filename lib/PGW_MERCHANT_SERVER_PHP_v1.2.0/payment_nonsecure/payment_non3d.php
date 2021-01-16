<?php
//Merchant's account information
$merchantID = "JT01";        //Get MerchantID when opening account with 2C2P
$secretKey = "7jYcp4FxFdf0";    //Get SecretKey from 2C2P PGW Dashboard

//Transaction Information
$desc = "2 days 1 night hotel room";
$uniqueTransactionCode = time();
$currencyCode = "702";
$amt = "00000000asd0010";
$panCountry = "SG";

//Customer Information
$cardholderName = "John Doe";

//Encrypted card data
$encCardData = $_POST['encryptedCardInfo'];

//Retrieve card information for merchant use if needed
$maskedCardNo = $_POST['maskedCardInfo'];
$expMonth = $_POST['expMonthCardInfo'];
$expYear = $_POST['expYearCardInfo'];

//Set token value based on selected card
$cardid = $_POST['cardid'];                                //Get selected card id from UI
if ($cardid == 1) {
    $storeCardUniqueID = '09111812192582570280';        //assign stored card token
} else {
    $storeCardUniqueID = '04071815240996699335';
}

//Request Information
$version = "9.9";

//Construct payment request message
$xml = "<PaymentRequest>
		<merchantID>$merchantID</merchantID>
		<uniqueTransactionCode>$uniqueTransactionCode</uniqueTransactionCode>
		<desc>$desc</desc>
		<amt>$amt</amt>
		<currencyCode>$currencyCode</currencyCode>  
		<storeCardUniqueID>$storeCardUniqueID</storeCardUniqueID>  
		<panCountry>$panCountry</panCountry> 
		<cardholderName>$cardholderName</cardholderName>
		<encCardData>$encCardData</encCardData>
		</PaymentRequest>";
$paymentPayload = base64_encode($xml); //Convert payload to base64
$signature = strtoupper(hash_hmac('sha256', $paymentPayload, $secretKey, false));
$payloadXML = "<PaymentRequest>
           <version>$version</version>
           <payload>$paymentPayload</payload>
           <signature>$signature</signature>
           </PaymentRequest>";
$data = base64_encode($payloadXML); //Convert payload to base64
$payload = urlencode($data);        //encode with base64

include_once('HTTP.php');

//Send authorization request
$http = new HTTP();
$response = $http->post("https://demo2.2c2p.com/2C2PFrontEnd/SecurePayment/Payment.aspx", "paymentRequest=" . $payload);

//decode response with base64
$reponsePayLoadXML = base64_decode($response);

//Parse ResponseXML
$xmlObject = simplexml_load_string($reponsePayLoadXML) or die("Error: Cannot create object");

//decode payload with base64 to get the Reponse
$payloadxml = base64_decode($xmlObject->payload);
$xml = new SimpleXMLElement($payloadxml);


echo json_encode($xml);
