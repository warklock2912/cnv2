<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
//Merchant's account information
$merchantID = "764764000002254";		//Get MerchantID when opening account with 2C2P
$secretKey = "6E4AD01A945A2125946A0A6ACD18B7359C851794076174E99CC738DBD885B4E8";	//Get SecretKey from 2C2P PGW Dashboard

//Transaction Information
$desc = "2 days 1 night hotel room";
$uniqueTransactionCode = '19-08-19849';
$currencyCode = "764";
$amt  = "000000000010";
$panCountry = "TH";

//Customer Information



    $storeCardUniqueID = '00703161432274043492';		//assign stored card token


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
$response = $http->post("https://demo2.2c2p.com/2C2PFrontEnd/SecurePayment/Payment.aspx","paymentRequest=".$payload);

//decode response with base64
$reponsePayLoadXML = base64_decode($response);

//Parse ResponseXML
$xmlObject =simplexml_load_string($reponsePayLoadXML) or die("Error: Cannot create object");

//decode payload with base64 to get the Reponse
$payloadxml = base64_decode($xmlObject->payload);
echo "Response :<br/><textarea style='width:100%;height:80px'>". $payloadxml."</textarea>";
?>

