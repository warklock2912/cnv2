<?php
require_once '../../app/Mage.php';
header('Content-Type: application/json');
Mage::app();

require_once(Mage::getBaseDir('lib') . '/PGW_MERCHANT_SERVER_PHP_v1.2.0/merchant_server/utils/PaymentGatewayHelper.php');
$secret_key = Mage::getStoreConfig('twoctwop_options/config/secret_key'); //Get SecretKey from 2C2P PGW dashboard
$encoded_payment_response = urldecode($_REQUEST["paymentResponse"]);

$pgw_helper = new PaymentGatewayHelper();
$log_file_data = 'log.log';


$is_valid_signature = $pgw_helper->validateSignature($encoded_payment_response, $secret_key);

if ($is_valid_signature) {
    $log_msg = 'card exist';

    $payment_response = $pgw_helper->parseAPIResponse($encoded_payment_response);

    $customerID = $payment_response->userDefined1;
    $paymentType = $payment_response->userDefined2;
    $cardNumber = $payment_response->pan;
    $cardToken = $payment_response->cardToken;
    $cardType = $payment_response->channelCode;
//        $expiredMonth   = $payment_response->userDefined3;
//        $expiredYear    = $payment_response->userDefined4;
    $transID = $payment_response->tranRef;

    $card = Mage::getModel('p2c2p/token')->getCollection()->addFieldToFilter('stored_card_unique_id', $cardToken);
    if (!count($card) && $paymentType === 'add_card') {

        $isCardExist = checkCardExist($cardToken);
        if ($isCardExist){
            die;
        }

        $cardNew = Mage::getModel('p2c2p/token');
        $cardNew->setUserId($customerID)
            ->setMaskedPan($cardNumber)
            ->setStoredCardUniqueId($cardToken)
            ->setCardType($cardType)
            ->setTransactionId($transID);


        $customerCardsList = Mage::getModel('p2c2p/token')->getCollection()->addFieldToFilter('customer_id', $customerID);
        if (!count($customerCardsList)) {
            $cardNew->setIsDefault(true);
        }

        $cardNew->save();
        $log_msg = 'success';
    }
} else {
    //Return invalid response message and dont trust this payment response.
    echo $log_msg = "Payment response has been modified by middle man attack, do not trust and use this payment response. Please contact 2c2p support.";
}
file_put_contents($log_file_data, now() . $log_msg . "\n", FILE_APPEND);
