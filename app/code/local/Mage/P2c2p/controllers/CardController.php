<?php

require_once Mage::getBaseDir('lib') . DS . 'PaymentActionP2c2p' . DS . 'pkcs7.php';
require_once Mage::getBaseDir('lib') . DS . 'PaymentActionP2c2p' . DS . 'HTTP.php';



class Mage_P2c2p_CardController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function listAction(){
        $this->loadLayout();
        $this->_title(Mage::helper('omise_gateway')->__('My Saved Cards'));
        $this->renderLayout();
    }
    public function newAction()
    {

        //Merchant's account information
        $merchantID = Mage::getStoreConfig('payment/p2c2p/merchantid', Mage::app()->getStore());
        $secretKey = Mage::getStoreConfig('payment/p2c2p/apisecretekeytest', Mage::app()->getStore());

        $desc = "create card ";
        $uniqueTransactionCode = time();
        $currencyCode = "764";
        $amt  = "000000000100";
        $panCountry = "TH";
        $cardholderName = $_POST['holder_name'];
        $cardType = $_POST['card_type'];

        // if($_POST['isfromRuffle'] == 1 && $cardType != 'credit_card'){
        //     $title = Mage::helper('omise_gateway')->__("Can't use debit card for raffle");
        //     $message = Mage::helper('omise_gateway')->__("PLEASE TRY A DIFFERENT CARD.");
        //     $html_popup = Mage::app()->getLayout()->createBlock('core/template')
        //         ->setData('error_message',$message)
        //         ->setData('title',$title)
        //         ->setData('error_code',$message)
        //         ->setTemplate('ruffle/cardsaved.phtml')->toHtml();
        //     $result['html_popup'] = $html_popup;
        //     $result['result'] = true;
        //     $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        //     return $this;
        // }

        //Encrypted card data
        $encCardData = $_POST['encryptedCardInfo'];
        $storeCard = "Y";
        //Request Information
        $version = "9.9";


        //Construct payment request message

        $xml = "<PaymentRequest>
		<merchantID>$merchantID</merchantID>
		<uniqueTransactionCode>$uniqueTransactionCode</uniqueTransactionCode>
		<desc>$desc</desc>
		<amt>$amt</amt>
		<currencyCode>$currencyCode</currencyCode>  
		<panCountry>$panCountry</panCountry> 
		<cardholderName>$cardholderName</cardholderName>
	    <storeCard>$storeCard</storeCard>
		<encCardData>$encCardData</encCardData>
		</PaymentRequest>";


        $paymentPayload = base64_encode($xml); //Convert payload to base64
        $signature = strtoupper(hash_hmac('sha256', $paymentPayload, $secretKey, false));
        $payloadXML = "<PaymentRequest>
           <version>$version</version>
           <payload>$paymentPayload</payload>
           <signature>$signature</signature>
           </PaymentRequest>";

        $payload = array(
            'paymentRequest' => base64_encode($payloadXML)
        );
        $response= $this->charge($payload);
        $reponsePayLoadXML = base64_decode($response);

        //Parse ResponseXML


        $xmlObject =simplexml_load_string($reponsePayLoadXML) or die("Error: Cannot create object");

        //Decode payload with base64 to get the Reponse
        $payloadxml = base64_decode($xmlObject->payload);

        //Get the signature from the ResponseXML
        $signaturexml = $xmlObject->signature;

        //Encode the payload
        $base64EncodedPayloadResponse=base64_encode($payloadxml);
        //Generate signature based on "payload"
        $signatureHash = strtoupper(hash_hmac('sha256', $base64EncodedPayloadResponse ,$secretKey, false));


        $payloadxml= simplexml_load_string($payloadxml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($payloadxml);
        $arrayReponse = json_decode($json,TRUE);
        Mage::log(print_r($arrayReponse, 1), null, 'responseCard2c2p.log');
        //Compare the response signature with payload signature with secretKey
        if($signaturexml == $signatureHash){
            if($arrayReponse['respCode']== "00")
            {
                try
                {
                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                    $customer_id=$customer->getId();

                    $isFound = false;

                    //Fatch data from database by customer ID.
                    $p2c2pTokenModel = Mage::getModel('p2c2p/token');

                    if(!$p2c2pTokenModel) {
                        die("2C2P Expected Model not available.");
                    }

                    $data = array(
                        'user_id' => $customer_id,
                        'stored_card_unique_id' => $arrayReponse['storeCardUniqueID'],
                        'masked_pan' =>  $arrayReponse['pan'] ,
                        'created_time' => now(),
                        'card_type' => $cardType,
                        'payment_scheme'=>$arrayReponse['paymentScheme']
                    );
                    /* Ken's code : dont need to foreach collection to filter $storeCardUniqueID */
                    $p2c2pTokenModel->getByCardUniqueToken($arrayReponse['storeCardUniqueID']);
                    if($p2c2pTokenModel->getId()){
                        $isFound = true;
                    }
                    /* end Ken's code */

                    /* Pune's code
                    //If matched the ignore if not match then add to database entry to prevent duplicate entry.
                    $customer_data = $p2c2pTokenModel->getCollection()->addFieldToFilter('stored_card_unique_id', $storeCardUniqueID)->getFirstItem();
                    if (!empty($customer_data)) {
                        $isFound = true;
                    }

                    */

                    /* old dudu code
                    $customer_data = $p2c2pTokenModel->getCollection();
                    foreach ($customer_data as $key => $value) {
                        if(strcasecmp($value->getData('masked_pan'),$arrayReponse['pan']) == 0 && strcasecmp($value->getData('stored_card_unique_id'), $arrayReponse['storeCardUniqueID']) == 0){
                            $isFound = true;
                            break;
                        }
                    }
                    */

                    if($_POST['isfromRuffle'] == 1) ///////from ruffle
                    {
                        if($_POST['isSavedCard'] && !$isFound)
                        {
                            $model = $p2c2pTokenModel->setData($data);
                            $model->save();
                        }
                    }else //from customer account
                    {
                        if(!$isFound){
                            $model = $p2c2pTokenModel->setData($data);
                            $model->save();
                        }
                    }

                    /////process void process
                    //Request Information



                    $this->processVoidTransaction($arrayReponse['uniqueTransactionCode'],$arrayReponse['storeCardUniqueID'],$isFound);

                }catch (Exception $e)
                {
                    die($e->getMessage());
                }
            }else
            {
                $html_popup=Mage::app()->getLayout()->createBlock('core/template')
                    ->setData('error_code',$arrayReponse['respCode'])
                    ->setTemplate('ruffle/cardsaved.phtml')->toHtml()
                ;
                $result['html_popup'] = $html_popup;
                $result['result'] = true;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
        else{
            //If Signature does not match
            die('Your reponse signature do not match please contact for more information');
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

    public function processVoidTransaction($invoiceNumber,$cardToken,$isFound)
    {
        //Merchant's account information
        $merchantID = Mage::getStoreConfig('payment/p2c2p/merchantid', Mage::app()->getStore());
        $secretKey = Mage::getStoreConfig('payment/p2c2p/apisecretekeytest', Mage::app()->getStore());

        $processType = "V";
        $invoiceNo = $invoiceNumber;
        $version = "3.4";

        //Construct signature string
        $stringToHash = $version . $merchantID . $processType . $invoiceNo ;
        $hash = strtoupper(hash_hmac('sha1', $stringToHash ,$secretKey, false));	//Compute hash value

        //Construct request message
        $xml = "<PaymentProcessRequest>
			<version>$version</version> 
			<merchantID>$merchantID</merchantID>
			<processType>$processType</processType>
			<invoiceNo>$invoiceNo</invoiceNo> 
			<hashValue>$hash</hashValue>
			</PaymentProcessRequest>";

        $pkcs7 = new pkcs7();

        $crtfile = Mage::getStoreConfig('payment/p2c2p/crt_file', Mage::app()->getStore());
        $pemfile = Mage::getStoreConfig('payment/p2c2p/pem_file', Mage::app()->getStore());
        $crt_file_2c2p_request = Mage::getStoreConfig('payment/p2c2p/crt_file_2c2p_request', Mage::app()->getStore());
        $merchantPassword= Mage::getStoreConfig('payment/p2c2p/merchant_private_password', Mage::app()->getStore());

        $payload = $pkcs7->encrypt($xml,$crt_file_2c2p_request); //Encrypt payload
        $http = new HTTP();
        $url=$this->getPaymentAction();
        $response = $http->post($url,"paymentRequest=".$payload);

        $response = $pkcs7->decrypt($response,$crtfile,$pemfile,$merchantPassword);
//Validate response Hash
        $resXml=simplexml_load_string($response);
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
        $res_stringToHash = $res_version.$res_respCode.$res_processType.$res_invoiceNo.$res_amount.$res_status.$res_approvalCode.$res_referenceNo.$res_transactionDateTime.$res_paidAgent.$res_paidChannel.$res_maskedPan.$res_eci.$res_paymentScheme.$res_processBy.$res_refundReferenceNo.$res_userDefined1.$res_userDefined2.$res_userDefined3.$res_userDefined4.$res_userDefined5 ;

        $res_responseHash = strtoupper(hash_hmac('sha1',$res_stringToHash,$secretKey, false));
        if($resXml->hashValue == strtolower($res_responseHash)){
            if($res_respCode=='00')
            {
                if($_POST['isfromRuffle']==1)   /////we must check if it is from ruffle 1 st
                {

                    if($isFound)   // if not choose saved card and card is existing
                    {
                        $html_popup=Mage::app()->getLayout()->createBlock('core/template')
                            ->setTemplate('ruffle/cardsaved.phtml')->toHtml()
                        ;
                        $result['html_popup'] = $html_popup;
                        $result['result'] = true;
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }else
                    {
                        $html_popup = Mage::app()->getLayout()->createBlock('ruffle/card')
                            ->setData('check',json_decode($_POST['check'],true))
                            ->setData('p2c2p_card_token', $cardToken)
                            ->setData('isSavedCard', $_POST['isSavedCard'])
                            ->setTemplate('ruffle/form-information.phtml')->toHtml();
                        $result['html_popup'] = $html_popup;
                        $result['result'] = true;
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    }
                } else if($isFound) {
                    $html_popup=Mage::app()->getLayout()->createBlock('core/template')
                        ->setTemplate('ruffle/cardsaved.phtml')->toHtml()
                    ;
                    $result['html_popup'] = $html_popup;
                    $result['result'] = true;
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                } else
                {
                    die('1');
                }
            }else
            {
                $html_popup=Mage::app()->getLayout()->createBlock('core/template')
                    ->setData('error_code',$res_respCode)
                    ->setTemplate('ruffle/cardsaved.phtml')->toHtml()
                ;
                $result['html_popup'] = $html_popup;
                $result['result'] = true;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
        else{
            die("Transaction can't be voided");
        }
    }
    public function ruffleUseoldcardAction()
    {
        if($_POST['isfromRuffle']==1)
        {
            if(!$_POST['idcardtoken'])
            {
                die('Please chose a valid card');
            }
            $p2c2pTokenModel = Mage::getModel('p2c2p/token');

            if(!$p2c2pTokenModel) {
                die("2C2P Expected Model not available.");
            }

            $model = $p2c2pTokenModel->load($_POST['idcardtoken']);
            // Arrang : Client need to allow Debit Card
            // if($model->getData('card_type') != 'credit_card'){
            //     $title = Mage::helper('omise_gateway')->__("Can't use debit card for raffle");
            //     $message = Mage::helper('omise_gateway')->__("PLEASE TRY A DIFFERENT CARD.");
            //     $html_popup = Mage::app()->getLayout()->createBlock('core/template')
            //         ->setData('error_message',$message)
            //         ->setData('title',$title)
            //         ->setData('error_code',$message)
            //         ->setTemplate('ruffle/cardsaved.phtml')->toHtml();
            //     $result['html_popup'] = $html_popup;
            //     $result['result'] = true;
            //     $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            // }else{
                $html_popup = Mage::app()->getLayout()->createBlock('ruffle/card')
                    ->setData('check',json_decode($_POST['check'],true))
                    ->setData('p2c2p_card_token', $model->getData('stored_card_unique_id'))
                    ->setData('isSavedCard', 1)
                    ->setTemplate('ruffle/form-information.phtml')->toHtml();
                $result['html_popup'] = $html_popup;
                $result['result'] = true;
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

            // }
        }else {
            die('1');
        }
    }
    public function getPaymentAction()
    {
        $url = "https://t.2c2p.com/PaymentActionV2/PaymentAction.aspx";
        $sandboxUrl = "https://demo2.2c2p.com/2C2PFrontend/PaymentActionV2/PaymentAction.aspx";

        $test_mode = Mage::getStoreConfig('payment/p2c2p/gatewayurl', Mage::app()->getStore());

        if ($test_mode) {
            return $sandboxUrl;
        }
        else {
            return $url;
        }
    }
    public function getHost()
    {
        $url = "https://t.2c2p.com/SecurePayment/Payment.aspx";
        $sandboxUrl = "https://demo2.2c2p.com/2C2PFrontEnd/SecurePayment/Payment.aspx";

        $test_mode = Mage::getStoreConfig('payment/p2c2p/gatewayurl', Mage::app()->getStore());

        if ($test_mode) {
            return $sandboxUrl;
        }
        else {
            return $url;
        }

    }

    public function removeAction(){

        $token =  $_REQUEST['token'];

        if(!isset($token)) {
            echo "0"; die;
        }

        $p2c2pTokenModel = Mage::getModel('p2c2p/token');

        if(!$p2c2pTokenModel) {
            die("2C2P Expected Model not available.");
        }

        $model = $p2c2pTokenModel->load($token);

        try {
            $model->delete();
            echo "1"; die;
        }
        catch (Exception $e){
            echo "0"; die;
        }
    }
    public function setDefaultAction()
    {
        $token =  $_REQUEST['token'];

        if(!isset($token)) {
            echo "0"; die;
        }

        $p2c2pTokenModel = Mage::getModel('p2c2p/token');

        if(!$p2c2pTokenModel) {
            die("2C2P Expected Model not available.");
        }

        $model = $p2c2pTokenModel->load($token);
        $_customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_id=$_customer->getId();

        $collection =$p2c2pTokenModel->getCollection()->addFieldToFilter('user_id',$customer_id);

        foreach ($collection as $key => $value)
        {
            $value->setData('is_default',0);
            $value->save();
        }
        try {

            $model->setData('is_default',1);
            $model->save();
            echo "1"; die;
        }
        catch (Exception $e){
            echo "0"; die;
        }
    }
}
