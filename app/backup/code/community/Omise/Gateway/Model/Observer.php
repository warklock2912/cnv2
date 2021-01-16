<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Payment
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment Observer
 *
 * @category    Mage
 * @package     Mage_Payment
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Omise_Gateway_Model_Observer
{
    /**
     * Set forced canCreditmemo flag
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Payment_Model_Observer
     */
    public function salesOrderPlaceAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();
        // Mage::log("Observer ", null, 'Omise_Authorize.log');
        if ($order->getPayment()->getMethodInstance()->getCode() != 'omise_gateway') {
            return $this;
        }

        // $charge = $this->chargeDetail;
        // $session = Mage::getSingleton('checkout/session');
        // $orderIncrementId = $session->getLastOrderId();
        // $order = Mage::getModel('sales/order')->load($orderIncrementId);

        // $card = $charge->offsetGet('card');
        $msg = "OmiseCharge API! =>".$order->getState();
        foreach ($order->getPayment()->getMethodInstance()->chargeDetail as $key => $value) {
            $msg .='<br>'."$key => $value";
        }

        if($order->getState() == Mage_Sales_Model_Order::STATE_NEW){
            // $msg .='<br><=1:'.Mage::getStoreConfig('payment/omise_gateway/order_status');
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING,Mage::getStoreConfig('payment/omise_gateway/payment_success_status'),$msg,true);
            $order->sendNewOrderEmail();
            $order->save();

            // if(Mage::getStoreConfig('payment/omise_gateway/autocreateinvoice')){
            //     $invoice = Mage::getModel('sales/service_order',$order)->prepareInvoice();
            //     $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            //     $invoice->register();
            //     $transactionSave = Mage::getModel('core/resource_transaction')
            //         ->addObject($invoice)
            //         ->addObject($invoice->getOrder());
            //     $transactionSave->save();
            // }
        } else {
            $order->addStatusToHistory($order->getStatus(), $order->getState() . " -> " . $msg, false);
            $order->save();
        }
        return $this;
    }
}
