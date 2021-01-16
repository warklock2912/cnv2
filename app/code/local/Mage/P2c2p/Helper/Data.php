<?php
// app/code/local/payment/P2c2p/Helper/Data.php
class Mage_P2c2p_Helper_Data extends Mage_Core_Helper_Abstract
{

	function getResultDescription($responseCode) {

	        switch ($responseCode) {
	            case "000" : $result = "Transaction Successful"; break;
	            case "001" : $result = "Transaction status is pending"; break;
				case "cancel" : $result = "Transaction faild"; break;
	           
	        }
	        return $result;
	}
	function getListCardCustomer($customerId)
    {
        $p2c2pTokenModel = Mage::getModel('p2c2p/token');
        $customer_data = $p2c2pTokenModel->getCollection()->addFieldToFilter('user_id',$customerId);
        return $customer_data;

    }
    function getDefaultCardCustomer($customerId)
    {
        $p2c2pTokenModel = Mage::getModel('p2c2p/token');
        $defaultCard = $p2c2pTokenModel->getCollection()->addFieldToFilter('user_id',$customerId)->addFieldToFilter('is_default','1')->getFirstItem();
        return $defaultCard;
    }

    function checkCc($cc, $extra_check = false){
        $cards = array(
            "visa" => "(4\d{12}(?:\d{3})?)",
            "amex" => "(3[47]\d{13})",
            "jcb" => "(35[2-8][89]\d\d\d{10})",
            "maestro" => "((?:5020|5038|6304|6579|6761)\d{12}(?:\d\d)?)",
            "solo" => "((?:6334|6767)\d{12}(?:\d\d)?\d?)",
            "mastercard" => "(5[1-5]\d{14})",
            "switch" => "(?:(?:(?:4903|4905|4911|4936|6333|6759)\d{12})|(?:(?:564182|633110)\d{10})(\d\d)?\d?)",
        );
        $names = array("Visa", "American Express", "JCB", "Maestro", "Solo", "Mastercard", "Switch");
        $matches = array();
        $pattern = "#^(?:".implode("|", $cards).")$#";
        $result = preg_match($pattern, str_replace(" ", "", $cc), $matches);
        if($extra_check && $result > 0){
            $result = (validatecard($cc))?1:0;
        }
        return ($result>0)?$names[sizeof($matches)-2]:false;
    }
    function getPayloadIbanking($order)
    {
        $payment=$order->getPayment();
        $merchantID = Mage::getStoreConfig('payment/p2c2p/merchantid', Mage::app()->getStore());
        $secretKey = Mage::getStoreConfig('payment/p2c2p/apisecretekeytest', Mage::app()->getStore());

        $objRequestHelper = Mage::helper('P2c2p/Request');

        $amount = $objRequestHelper->p2c2p_get_amount_by_currency_type($order->getBaseCurrencyCode(), $order->getBaseTotalDue());

        $payment_expiry=Mage::getStoreConfig('payment/p2c2p_onsite_internet_banking/payment_expiry_123', Mage::app()->getStore());
        $descArray=array();
        foreach ($order->getAllVisibleItems() as $item) {
            $descArray[]=$item->getName();
        }
        $desc = implode(",",$descArray);
        // $uniqueTransactionCode = $order->getId() . time();
        $uniqueTransactionCode = $order->getIncrementId();

        $currencyCode = "764";
        $amt = $amount;
        $panCountry = "TH";


        //Payment Options
        $paymentChannel="123";
        $agentCode=$payment->getAdditionalInformation("agentCode");
        $channelCode=$payment->getAdditionalInformation("channelCode");
        $paymentExpiry=  date('Y-m-d H:i:s',
            strtotime("+{$payment_expiry} hour", strtotime(gmdate('Y-m-d H:i:s'))));
        $mobileNo=$order->getBillingAddress()->getTelephone();
        $cardholderEmail=$order->getBillingAddress()->getEmail();
        $cardholderName=mb_substr($order->getCustomerFirstname() ." ". $order->getCustomerLastname(), 0, 49);
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
		<paymentChannel>$paymentChannel</paymentChannel>
		<agentCode>$agentCode</agentCode>
		<channelCode>$channelCode</channelCode>
		<paymentExpiry>$paymentExpiry</paymentExpiry>
		<mobileNo>$mobileNo</mobileNo>
		<cardholderEmail>$cardholderEmail</cardholderEmail>
		<cardholderName>$cardholderName</cardholderName>
		</PaymentRequest>";


        $paymentPayload = base64_encode($xml); //Convert payload to base64
        $signature = strtoupper(hash_hmac('sha256', $paymentPayload, $secretKey, false));
        $payloadXML = "<PaymentRequest>
           <version>$version</version>
           <payload>$paymentPayload</payload>
           <signature>$signature</signature>
           </PaymentRequest>";
        return base64_encode($payloadXML);

    }
}
