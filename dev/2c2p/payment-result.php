<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
$customer = getCustomer();
$customerID = $customer->getId();
if (isset($_REQUEST['transaction_id'])) {
    $transactionID = $_REQUEST['transaction_id'];
    $paymentInquiry = Mage::helper('twoctwop')->inquiryPayment($transactionID);
    $paymentResult = $paymentInquiry['response'];
    if ($paymentInquiry['status']) {
        $invoiceNo = $paymentResult->invoiceNo;
        $order = Mage::getModel('sales/order')
            ->loadByIncrementId($invoiceNo);
        $payment = $order->getPayment();

        $details = array(
            'create_time' => gmdate("YmdHis", time()),
            '2c2p_transaction_id' => $transactionID,
            'amount' => $order->getTotalDue()
        );
        $payment
            ->setAdditionalData(serialize($details))
            ->save();
        
        if ($order->getCanSendNewEmailFlag()) {
            $order->queueNewOrderEmail();
        }
        dataResponse(200, 'success', $order->getData());
    } else {
        dataResponse(400, $paymentInquiry['message']);
    }

} else {
    dataResponse(400, 'Missing transaction_id');
}
?>
