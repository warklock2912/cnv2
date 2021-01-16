<?php
require_once(Mage::getBaseDir('lib') . '/PGW_MERCHANT_SERVER_PHP_v1.2.0/merchant_server/enum/APIEnvironment.php');
require_once(Mage::getBaseDir('lib') . '/PGW_MERCHANT_SERVER_PHP_v1.2.0/merchant_server/utils/PaymentGatewayHelper.php');
require_once(Mage::getBaseDir('lib') . '/PGW_MERCHANT_SERVER_PHP_v1.2.0/merchant_server/model/SignatureError.php');
require_once(Mage::getBaseDir('lib') . '/PGW_MERCHANT_SERVER_PHP_v1.2.0/merchant_server/paymentToken/enum/CardSecureMode.php');
require_once(Mage::getBaseDir('lib') . '/PGW_MERCHANT_SERVER_PHP_v1.2.0/merchant_server/paymentToken/enum/PaymentChannel.php');
require_once(Mage::getBaseDir('lib') . '/PGW_MERCHANT_SERVER_PHP_v1.2.0/payment_nonsecure/HTTP.php');
require_once(Mage::getBaseDir('lib') . '/PGW_MERCHANT_SERVER_PHP_v1.2.0/payment_nonsecure/pkcs7.php');

class Crystal_Twoctwop_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $api_version = "10.01";
    private $currencyCode;

    public function __construct()
    {
        $this->currencyCode = '764';
    }

    public function getMerchantID()
    {
        return Mage::getStoreConfig('payment/crystal_twoctwop/merchant_id');
    }

    public function getApiEnvironment()
    {
        $env = Mage::getStoreConfig('payment/crystal_twoctwop/env');
        if ($env === 'test') {
            return APIEnvironment::SANDBOX;
        } else {
            return APIEnvironment::PRODUCTION;
        }
    }

    public function getSecretKey()
    {
        return Mage::getStoreConfig('payment/crystal_twoctwop/secret_key');
    }

    public function getPaymentToken($paymentDetail)
    {
        $result = array(
            'status' => false
        );
        $apiEnv = $this->getApiEnvironment() . "/paymentToken";

        $mid = $this->getMerchantID();
        $secretKey = $this->getSecretKey();
        $nonce_str = uniqid('', true);

        $paymentTokenRequest = new stdClass();

        $paymentTokenRequest->version = $this->api_version;
        $paymentTokenRequest->merchantID = $mid;
        $paymentTokenRequest->nonceStr = $nonce_str;
        $paymentTokenRequest->request3DS = CardSecureMode::NO;
        $paymentTokenRequest->invoiceNo = $paymentDetail->invoiceNo;
        $paymentTokenRequest->desc = $paymentDetail->desc;
        $paymentTokenRequest->amount = $paymentDetail->amount;
        $paymentTokenRequest->currencyCode = $paymentDetail->currencyCode;
        $paymentTokenRequest->userDefined1 = $paymentDetail->userDefined1;
        $paymentTokenRequest->userDefined2 = $paymentDetail->userDefined2;
        $paymentTokenRequest->userDefined3 = $paymentDetail->userDefined3;
        $paymentTokenRequest->userDefined4 = $paymentDetail->userDefined4;
        $paymentTokenRequest->userDefined5 = $paymentDetail->userDefined5;

        //Important: Generate signature
        $pgw_helper = new PaymentGatewayHelper();
        $hashed_signature = $pgw_helper->generateSignature($paymentTokenRequest, $secretKey);
        $paymentTokenRequest->signature = $hashed_signature;
        //Do Payment Token API request
        $encoded_payment_token_response = $pgw_helper->requestAPI($apiEnv, $paymentTokenRequest);
        $is_valid_signature = $pgw_helper->validateSignature($encoded_payment_token_response, $secretKey);

        if ($is_valid_signature) {
            //Valid signature, Get payment token and pass token to your mobile application.
            $payment_token_response = $pgw_helper->parseAPIResponse($encoded_payment_token_response);

            if ($payment_token_response->paymentToken && $payment_token_response->paymentToken != '') {
                $result['status'] = true;
                $result['payment_response'] = $payment_token_response;
                $result['message'] = $payment_token_response->respDesc;
            } else {
                $result['message'] = $payment_token_response->respDesc;
            }
        } else {
            $result['message'] = 'Invalid Signature';
        }
        return $result;
    }

    public function inquiryPayment($transactionId)
    {
        $result = array(
            'status' => false
        );
        $api_env = $this->getApiEnvironment() . "/paymentInquiry";
        $api_version = "1.1";
        $mid = $this->getMerchantID();
        $secretKey = $this->getSecretKey();

        $payment_inquiry_request = new stdClass();
        $payment_inquiry_request->version = $api_version;
        $payment_inquiry_request->merchantID = $mid;
        $payment_inquiry_request->transactionID = $transactionId;

        //Important: Generate signature
        $pgw_helper = new PaymentGatewayHelper();
        $hashed_signature = $pgw_helper->generateSignature($payment_inquiry_request, $secretKey);
        $payment_inquiry_request->signature = $hashed_signature;

        //Do Payment Inquiry API request
        $encoded_payment_inquiry_response = $pgw_helper->requestAPI($api_env, $payment_inquiry_request);

        //Important: Verify response signature
        $is_valid_signature = $pgw_helper->validateSignature($encoded_payment_inquiry_response, $secretKey);
        if ($is_valid_signature) {
            $payment_inquiry_response = $pgw_helper->parseAPIResponse($encoded_payment_inquiry_response);
            $arrayReponse = json_encode($payment_inquiry_response, TRUE);
            Mage::log(print_r($arrayReponse, 1), null, '2c2pResponseApp.log');

            if ($payment_inquiry_response->respCode == "00" || $payment_inquiry_response->respCode == "000") {
                $result['status'] = true;
                $result['response'] = $payment_inquiry_response;
                $result['message'] = $payment_inquiry_response->respDesc;
            } else {
                $result['message'] = $payment_inquiry_response->respDesc;
                $result['resCode'] = $payment_inquiry_response->respCode;
            }
        } else {
            $result['message'] = 'Invalid Signature';
        }
        return $result;
    }

    public function formatAmount($amount)
    {
        $amountResult = $amount . '00';
        if (strlen($amount) < 10) {
            $zeroPrefixLenght = 10 - strlen($amount);
            for ($i = 0; $i < $zeroPrefixLenght; $i++) {
                $amountResult = '0' . $amountResult;
            }
        }
        return $amountResult;
    }

    public function paymentWithToken($cardToken, $order)
    {

        $mid = $this->getMerchantID();
        $secretKey = $this->getSecretKey();
        $version = "9.9";

        $desc = "Raffle online winner payment";
        $uniqueTransactionCode = $order->getIncrementId();
        $currencyCode = $this->currencyCode;
        $amount = (int )$order->getGrandTotal();
        $amount = Mage::helper('twoctwop')->formatAmount($amount);
        $panCountry = "TH";
        $storeCardUniqueID = $cardToken;
        //Construct payment request message
        $xml = "<PaymentRequest>
		<merchantID>$mid</merchantID>
		<uniqueTransactionCode>$uniqueTransactionCode</uniqueTransactionCode>
		<desc>$desc</desc>
		<amt>$amount</amt>
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

        //Send authorization request
        $http = new HTTP();


        $env = Mage::getStoreConfig('payment/crystal_twoctwop/env');
        if ($env === 'test') {
            $url = "https://demo2.2c2p.com/2C2PFrontEnd/SecurePayment/Payment.aspx";
        } else {
            $url = "https://t.2c2p.com/SecurePayment/Payment.aspx";
        }
        $response = $http->post($url, 'paymentRequest=' . $payload);

        //decode response with base64
        $reponsePayLoadXML = base64_decode($response);

        //Parse ResponseXML
        $xmlObject = simplexml_load_string($reponsePayLoadXML)
        or
        die("Error: Cannot create object");

        //decode payload with base64 to get the Reponse
        $payloadxml = base64_decode($xmlObject->payload);
        $xmlObj = new SimpleXMLElement($payloadxml);
        $json = json_encode($xmlObj);
        $resultArray = json_decode($json, TRUE);
        Mage::log(print_r($resultArray, 1), null, '2c2pResponseApp.log');
        return $resultArray;
    }


    public function processVoidTransaction($invoiceNumber)
    {
        try {
            //Merchant's account information
            $merchantID = $this->getMerchantID();
            $secretKey = $this->getSecretKey();

            $processType = "V";
            $invoiceNo = $invoiceNumber;
            $version = "3.4";

            //Construct signature string
            $stringToHash = $version . $merchantID . $processType . $invoiceNo;
            $hash = strtoupper(hash_hmac('sha1', $stringToHash, $secretKey, false));    //Compute hash value

            //Construct request message
            $xml = "<PaymentProcessRequest>
			<version>$version</version>
			<merchantID>$merchantID</merchantID>
			<processType>$processType</processType>
			<invoiceNo>$invoiceNo</invoiceNo>
			<hashValue>$hash</hashValue>
			</PaymentProcessRequest>";

            $pkcs7 = new pkcs7();

            $crtfile = Mage::getStoreConfig('payment/crystal_twoctwop/crt_file');
            $pemfile = Mage::getStoreConfig('payment/crystal_twoctwop/pem_file');
            $crt_file_2c2p_request = Mage::getStoreConfig('payment/crystal_twoctwop/crt_file_2c2p_request');
            $merchantPassword = Mage::getStoreConfig('payment/crystal_twoctwop/merchant_private_password');

            $payload = $pkcs7->encrypt($xml, $crt_file_2c2p_request); //Encrypt payload
            $http = new HTTP();
            $url = $this->getPaymentAction();
            $response = $http->post($url, "paymentRequest=" . $payload);

            $response = $pkcs7->decrypt($response, $crtfile, $pemfile, $merchantPassword);
            if ($response['success']) {
                $response = $response['content'];
//Validate response Hash
                $resXml = simplexml_load_string($response);
                $res_version = $resXml->version;
                $res_respCode = $resXml->respCode;
                $res_processType = $resXml->processType;
                $res_invoiceNo = $resXml->invoiceNo;
                $res_amount = $resXml->amount;
                $res_status = $resXml->status;
                $res_approvalCode = $resXml->approvalCode;
                $res_referenceNo = $resXml->referenceNo;
                $res_transactionDateTime = $resXml->transactionDateTime;
                $res_paidAgent = $resXml->paidAgent;
                $res_paidChannel = $resXml->paidChannel;
                $res_maskedPan = $resXml->maskedPan;
                $res_eci = $resXml->eci;
                $res_paymentScheme = $resXml->paymentScheme;
                $res_processBy = $resXml->processBy;
                $res_refundReferenceNo = $resXml->refundReferenceNo;
                $res_userDefined1 = $resXml->userDefined1;
                $res_userDefined2 = $resXml->userDefined2;
                $res_userDefined3 = $resXml->userDefined3;
                $res_userDefined4 = $resXml->userDefined4;
                $res_userDefined5 = $resXml->userDefined5;
                Mage::log(print_r($resXml, 1), null, 'cardRefund2c2p.log');

                //Compute response hash
                $res_stringToHash = $res_version . $res_respCode . $res_processType . $res_invoiceNo . $res_amount . $res_status . $res_approvalCode . $res_referenceNo . $res_transactionDateTime . $res_paidAgent . $res_paidChannel . $res_maskedPan . $res_eci . $res_paymentScheme . $res_processBy . $res_refundReferenceNo . $res_userDefined1 . $res_userDefined2 . $res_userDefined3 . $res_userDefined4 . $res_userDefined5;

                $res_responseHash = strtoupper(hash_hmac('sha1', $res_stringToHash, $secretKey, false));
                if ($resXml->hashValue == strtolower($res_responseHash)) {
                    $result['result'] = true;
                    $result['message'] = 'success';
                } else {
                    $result['result'] = false;
                    $result['message'] = "Transaction can't be voided";
                }
            } else {
                $result['result'] = false;
                $result['message'] = "Decrypt failed.";
            }
        } catch (Exception $exception) {
            $result['result'] = false;
            $result['message'] = $exception;
        }

        return $result;
    }

    public function getPaymentAction()
    {
        $url = "https://t.2c2p.com/PaymentActionV2/PaymentAction.aspx";
        $sandboxUrl = "https://demo2.2c2p.com/2C2PFrontend/PaymentActionV2/PaymentAction.aspx";
        $env = Mage::getStoreConfig('payment/crystal_twoctwop/env');
        if ($env == 'test') {
            return $sandboxUrl;
        } else {
            return $url;
        }
    }
}
