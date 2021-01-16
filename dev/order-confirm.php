<?php
require_once '../app/Mage.php';
require_once 'functions.php';
require_once(Mage::getBaseDir('lib') . '/Crystal/Braintree/lib/Braintree.php');
require_once(Mage::getBaseDir('lib') . '/omise-php/lib/Omise.php');
checkIsLoggedIn();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);

$order_id = isset($data['order_id']) ? $data['order_id'] : '';
if ($order_id != '') {
    try {

        $payment_method = $data['paynment_method'];
        $transaction_id = $data['paypal_transaction_id'] ? $data['paypal_transaction_id'] : '';
        $p2c2p_transaction_id = $data['p2c2p_transaction_id'] ? $data['p2c2p_transaction_id'] : '';

        $store_id = getStoreId();
        $order = Mage::getModel('sales/order')
            ->loadByIncrementId($order_id);

        if ($payment_method == 'crystal_paypal') {
            $payment = $order->getPayment();

            $details = array(
                'Payment Method' => 'Paypal on app',
                'update_time' => gmdate("YmdHis", time()),
                'paypal_transaction_id' => $transaction_id,
                'amount' => $order->getTotalDue()
            );
            $payment
                ->setAdditionalData(serialize($details))
                ->save();
            $order->setState(Mage::getStoreConfig("payment/crystal_paypal/order_status"), true)->save();
        }

        if ($payment_method == 'p2c2p_onsite_internet_banking') {
            $payment = $order->getPayment();

            $details = array(
                'Payment Method' => 'Onsite internet banking on app',
                'update_time' => gmdate("YmdHis", time()),
                'p2c2p_transaction_id' => $p2c2p_transaction_id,
                'amount' => $order->getTotalDue()
            );
            $payment
                ->setAdditionalData(serialize($details))
                ->save();
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            $order->setState($state,true)->save();
        }


        $data = $order->getData();
        if ($order->getCanSendNewEmailFlag()) {
            $order->queueNewOrderEmail();
        }
        dataResponse(200, 'successfully', $data);
    } catch (Exception $e) {
        dataResponse(400, $e->getMessage());
    }

} else {
    dataResponse(400, 'Missing order ID');
}