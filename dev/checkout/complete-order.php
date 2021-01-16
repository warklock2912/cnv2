<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
checkIsLoggedIn();
if (isset($_REQUEST['status']) && isset($_REQUEST['order_id'])) {
    $status = $_REQUEST['status'];
    $orderIncrementId = $_REQUEST['order_id'];
    $order = Mage::getModel('sales/order')
        ->loadByIncrementId($orderIncrementId);
    if ($status == 'success') {
        $invoice = $order->prepareInvoice();
        $invoice->register();
        Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
//        $invoice->sendEmail(true, '');
        if($order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)) {
            $order->save();
        }
        dataResponse(200, 'Success', 'success');
    } else {
        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED,true)->save();
        dataResponse(200, 'Canceled', 'canceled');
    }
} else {
    dataResponse(404, 'Missing Status');
}

