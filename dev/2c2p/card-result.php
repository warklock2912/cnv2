<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = getCustomer();
$customerID = $customer->getId();
$isSave = isset($_REQUEST['is_save']) && $_REQUEST['is_save'] == 'true' ? true : false;

if (isset($_REQUEST['transaction_id'])) {
    $transactionID = $_REQUEST['transaction_id'];
    $expiredMonth = isset($_REQUEST['expired_month']) ? $_REQUEST['expired_month'] : '';
    $expiredYear = isset($_REQUEST['expired_year']) ? $_REQUEST['expired_year'] : '';
    $isDefault = isset($_REQUEST['is_default']) && $_REQUEST['is_default'] == 'true' ? true : false;
    $paymentInquiry = Mage::helper('twoctwop')->inquiryPayment($transactionID);
    $paymentResult = $paymentInquiry['response'];

    if (!$isSave) {
        if ($paymentInquiry['status']) {
            $refunded = Mage::helper('twoctwop')->processVoidTransaction($paymentResult->invoiceNo);

            dataResponse(200, $paymentInquiry['message'], array(
                'card_token' => $paymentResult->cardToken,
                'refunded' => $refunded
            ));
        } else {
            dataResponse(400, $paymentInquiry['message']);
        }
        die;
    }
    $card = Mage::getModel('p2c2p/token')
        ->getCollection()
        ->addFieldToFilter('transaction_id', $transactionID)
        ->getFirstItem();

    $customerCardsList = Mage::getModel('p2c2p/token')
        ->getCollection()
        ->addFieldToFilter('user_id', $customerID);

    if ($card->getId()) {
        $card->setExpiredMonth($expiredMonth)
            ->setExpiredYear($expiredYear);

        if ($isDefault) {
            $card->setIsDefault($isDefault);
            foreach ($customerCardsList as $item):
                $item->setIsDefault(false)->save();
            endforeach;
        }

        $card->save();
        $dataRes = array('card_id' => $card->getId());
        dataResponse(200, 'success', $dataRes);
    } else {
        $paymentInquiry = Mage::helper('twoctwop')->inquiryPayment($transactionID);
        $paymentResult = $paymentInquiry['response'];
        if ($paymentInquiry['status']) {
            $cardNumber = $paymentResult->cardNo;
            $cardToken = $paymentResult->cardToken;
            $cardType = $paymentResult->channelCode;
            $transID = $paymentResult->tranRef;

            $isCardExist = checkCardExist($cardToken);
            if ($isCardExist) {
                dataResponse(400, 'Card Exist.');
                die;
            }

            $cardNew = Mage::getModel('p2c2p/token');
            $cardNew->setUserId($customerID)
                ->setMaskedPan($cardNumber)
                ->setStoredCardUniqueId($cardToken)
                ->setPaymentScheme($cardType)
                ->setTransactionId($transID)
                ->setExpiredMonth($expiredMonth)
                ->setExpiredYear($expiredYear);


            if (!count($customerCardsList)) {
                $cardNew->setIsDefault(true);
            }
            if ($isDefault) {
                $cardNew->setIsDefault($isDefault);
                foreach ($customerCardsList as $item):
                    $item->setIsDefault(false)->save();
                endforeach;
            }
            $cardNew->save();
            $cardNew->getId();

            $refunded = Mage::helper('twoctwop')->processVoidTransaction($paymentResult->invoiceNo);
            $dataRes = array(
                'card_id' => $cardNew->getId(),
                'refunded' => $refunded
            );

            dataResponse(200, $paymentInquiry['message'], $dataRes);
        } else {
            dataResponse(400, $paymentInquiry['message']);
        }
    }
} else {
    dataResponse(400, 'Missing transaction_id');
}
?>
