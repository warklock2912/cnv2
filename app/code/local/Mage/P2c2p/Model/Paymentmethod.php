<?php

class Mage_P2c2p_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'p2c2p';
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canReviewPayment = true;

    protected $_formBlockType = 'p2c2p/form_p2c2p';

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('p2c2p/payment/redirect', array(
            '_secure' => true,
        ));
    }

    public function getReturnUrl()
    {
        return Mage::getUrl('p2c2p/payment/success', array(
            '_secure' => true,
        ));
    }

    public function assignData($data)
    {

        if (is_array($data)) {
            if (isset($data["p2c2p_tokenCard"]) && isset($data["is_create_from_ruffle"])) {
                $this->getInfoInstance()->setAdditionalInformation("p2c2p_tokenCard", $data["p2c2p_tokenCard"]);
                $this->getInfoInstance()->setAdditionalInformation("is_create_from_ruffle", $data["is_create_from_ruffle"]);

            }
        } elseif ($data instanceof Varien_Object) {
            if (isset($data["p2c2p_tokenCard"]) && isset($data["is_create_from_ruffle"])) {
                $this->getInfoInstance()->setAdditionalInformation("p2c2p_tokenCard", $data["p2c2p_tokenCard"]);
                $this->getInfoInstance()->setAdditionalInformation("is_create_from_ruffle", $data["is_create_from_ruffle"]);

            }
        }


        //already process data in form submit
        return $this;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState("new");
        $stateObject->setStatus('Pending_2C2P');
        $payment = $this->getInfoInstance();
        if ($payment->getAdditionalInformation('is_create_from_ruffle') == 1 && $payment->getAdditionalInformation('p2c2p_tokenCard') != null) {
            $this->processPayment($payment);
        }

        return $this;
    }

    public function processPayment(Varien_Object $payment)
    {
        $log_name = "raffle_debug_".date("Y-m-d").".log";
        $order = $payment->getOrder();
        $merchantID = Mage::getStoreConfig('payment/p2c2p/merchantid', Mage::app()->getStore());
        $secretKey = Mage::getStoreConfig('payment/p2c2p/apisecretekeytest', Mage::app()->getStore());

        $objRequestHelper = Mage::helper('P2c2p/Request');

        $amount = $objRequestHelper->p2c2p_get_amount_by_currency_type($order->getBaseCurrencyCode(), $order->getBaseTotalDue());

        // $uniqueTransactionCode = $order->getId() . time();
        $uniqueTransactionCode = $order->getIncrementId();
        Mage::log("====CHARGE START====", null, $log_name, true);
        Mage::log("====Order ID ".$uniqueTransactionCode."====", null, $log_name, true);
        $desc = "Create order for Winner's Raffle in backend : Order ID : ".$uniqueTransactionCode;

        $currencyCode = "764";
        $amt = $amount;
        $panCountry = "TH";

        $storeCardUniqueID = $payment->getAdditionalInformation("p2c2p_tokenCard");

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
        Mage::log(print_r($payloadXML, 1), null, 'requestCreateCard.log', true);

        $payload = array(
            'paymentRequest' => base64_encode($payloadXML),
        );
        $response = $this->charge($payload);
        $reponsePayLoadXML = base64_decode($response);

        //Parse ResponseXML


        $xmlObject = simplexml_load_string($reponsePayLoadXML) or die("Error: Cannot create object");

        //Decode payload with base64 to get the Reponse
        $payloadxml = base64_decode($xmlObject->payload);

        //Get the signature from the ResponseXML
        $signaturexml = $xmlObject->signature;

        //Encode the payload
        $base64EncodedPayloadResponse = base64_encode($payloadxml);
        //Generate signature based on "payload"
        $signatureHash = strtoupper(hash_hmac('sha256', $base64EncodedPayloadResponse, $secretKey, false));


        $payloadxml = simplexml_load_string($payloadxml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($payloadxml);
        // Mage::log(print_r($json, 1), null, 'response2c2pCreateRuffleOrderBackend.log', true);

        $arrayReponse = json_decode($json, true);
        $payment_status = $arrayReponse['status'];
        $transaction_ref = $arrayReponse['uniqueTransactionCode'];
        $approval_code = $arrayReponse['approvalCode'];
        if ($signaturexml == $signatureHash) {
            if ($arrayReponse['respCode'] == "00") {
                $payment->setIsTransactionClosed(false)
                    ->addTransaction(
                        Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                        null,
                        false,
                        Mage::helper('p2c2p')->__('Authorizing an amount of %s', $order->getBaseCurrency()->formatTxt($order->getBaseTotalDue()))
                    );
                Mage::log("====CHARGE SUCCESS====", null, $log_name, true);
                Mage::log(json_encode($arrayReponse,true), null, $log_name, true);
            } else {
                $order->setIsRafflePaymentFail(1);
                Mage::log("====CHARGE FAIL====", null, $log_name, true);
                Mage::log(json_encode($arrayReponse,true), null, $log_name, true);
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('p2c2p')->__('Your order something wrong with transaction please try again (%s)',$arrayReponse['respCode'].' : '.$arrayReponse['failReason']));
                // Mage::throwException('Your order have been cancel due to something wrong with transaction please try again');
            }
        } else {
            $order->setIsRafflePaymentFail(1);
            Mage::log("====CHARGE FAIL Error Hash====", null, $log_name, true);
            Mage::log(json_encode($arrayReponse,true), null, $log_name, true);

            // Mage::log(print_r($arrayReponse, 1), null, 'orderRuffleResponseErrorHash.log', true);
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('p2c2p')->__('Your order something wrong with transaction please try again'));

            // Mage::throwException('Your order have been cancel due to wrong hash please try again');
        }
    }

    public function charge($payload)
    {
        $chargeResponse = $this->requestHTTP($payload);

        return $chargeResponse;

    }

    public function requestHTTP($payload)
    {
        $url = $this->getHost();
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;

    }

    public function getHost()
    {
        $url = "https://t.2c2p.com/SecurePayment/Payment.aspx";
        $sandboxUrl = "https://demo2.2c2p.com/2C2PFrontEnd/SecurePayment/Payment.aspx";

        $test_mode = Mage::getStoreConfig('payment/p2c2p/gatewayurl', Mage::app()->getStore());

        if ($test_mode) {
            return $sandboxUrl;
        } else {
            return $url;
        }
    }
}
