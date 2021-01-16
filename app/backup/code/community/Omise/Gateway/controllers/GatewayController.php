<?php
class Omise_Gateway_GatewayController extends Mage_Core_Controller_Front_Action{

    public function callbackAction(){
        $order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('orderid'));
        if($order->getId()){
            $payment = $order->getPayment();
            if(!empty($payment->getData('additional_information')['reference'])){
                echo "XE";
            } else {
                $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
                $this->getResponse()->setHeader('Status','404 File not found');
                $this->_forward('defaultNoRoute');
            }
        }
    }
}
?>
