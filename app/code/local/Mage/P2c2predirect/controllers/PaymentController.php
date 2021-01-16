<?php
/**
 * @author Tigren Solutions <info@tigren.com>
 * @copyright Copyright (c) 2019 Tigren Solutions <https://www.tigren.com>. All rights reserved.
 * @license Open Software License ("OSL") v.3.0
 */

// app/code/local/payment/p2c2predirect/controllers/PaymentController.php

/**
 * Class Mage_P2c2predirect_PaymentController
 */
class Mage_P2c2predirect_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     */
    public function redirectAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'p2c2predirect', array('template' => 'p2c2predirect/redirect.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    /**
     * @throws \Mage_Core_Model_Store_Exception
     */
    public function responseAction()
    {

        $hashHelper = Mage::helper('p2c2predirect/hash');

        if (empty($_REQUEST) || empty($_REQUEST['order_id'])) {
            Mage_Core_Controller_Varien_Action::_redirect('');

            return;
        }

        $order = Mage::getModel('sales/order')->loadbyIncrementId($_REQUEST['order_id']);

        if (empty($order)) {
            Mage_Core_Controller_Varien_Action::_redirect('');

            return;
        }

        $payment_status_code = $_REQUEST['payment_status'];
        $transaction_ref = $_REQUEST['transaction_ref'];
        $approval_code = $_REQUEST['approval_code'];
        $payment_status = $_REQUEST['payment_status'];
        $orderId = $_REQUEST['order_id'];

        //Redirect to home page when hash value is wrong.
        if (!$hashHelper->isValidHash($_REQUEST)) {

            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $order->setData('state', Mage_Sales_Model_Order::STATUS_FRAUD);
            $order->setData('statuscode', $payment_status);
            $order->setStatus(Mage_Sales_Model_Order::STATUS_FRAUD);
            $order->setData('transaction_ref', $transaction_ref);
            $order->setData('approval_code', $approval_code);
            foreach (Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection() as $item) {
                Mage::getSingleton('checkout/cart')->removeItem($item->getId())->save();
            }

            Mage_Core_Controller_Varien_Action::_redirect('');

            return;
        }

        //Store response meta into p2c2predirect_meta database table.
        $metaHelper = Mage::helper('p2c2predirect/meta');
        $metaHelper->p2c2predirect_meta($_REQUEST);

        //Check payment status.
        if ($payment_status_code == "000" || $payment_status_code == "00") { // SUCCESS
            //Check if user is logged in or not.
            if (!empty($order->getCustomerId())) {
                if (!empty($_REQUEST['stored_card_unique_id'])) {
                    $customer_id = $order->getCustomerId();
                    $isFouned = false;

                    //Fatch data from database by customer ID.
                    $p2c2predirectTokenModel = Mage::getModel('p2c2p/token');

                    if (!$p2c2predirectTokenModel) {
                        die("2C2P Expected Model not available.");
                    }

                    $customer_data = $p2c2predirectTokenModel->getCollection();

                    $data = array('user_id' => $customer_id,
                        'stored_card_unique_id' => $_REQUEST['stored_card_unique_id'],
                        'masked_pan' => $_REQUEST['masked_pan'],
                        'created_time' => now(),
                        'payment_scheme'=>$_REQUEST['payment_scheme']
                    );

                    //If matched the ignore if not match then add to database entry to prevent duplicate entry.
                    foreach ($customer_data as $key => $value) {
                        if (strcasecmp($value->getData('masked_pan'), $_REQUEST['masked_pan']) == 0 && strcasecmp($value->getData('stored_card_unique_id'), $_REQUEST['stored_card_unique_id']) == 0) {
                            $isFouned = true;
                            break;
                        }
                    }

                    if (!$isFouned) {
                        $model = $p2c2predirectTokenModel->setData($data);
                        $model->save();
                    }
                }
            }

            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            $status = Mage_Sales_Model_Order::STATE_PROCESSING;
            $order->setState($state, $status);

            $order->setData('statuscode', $payment_status);
            $order->setData('transaction_ref', $transaction_ref);
            $order->setData('approval_code', $approval_code);

            $order->save();

            $success = Mage::getStoreConfig('payment/p2c2predirect/toc2p_url_success', Mage::app()->getStore());
            Mage::app()->getFrontController()->getResponse()->setRedirect($success)->sendResponse();
            die;

            // $this->loadLayout();
            // $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','p2c2predirect',array('template' => 'p2c2predirect/success.phtml'));
            // $this->getLayout()->getBlock('content')->append($block);
            // $this->renderLayout();
        } else {
            if ($payment_status_code == "001") { // Pending Payment

                $state = Mage_Sales_Model_Order::STATE_NEW;
                $status = "Pending_2C2P";

                $order->setState($state, $status);

                $order->setData('statuscode', $payment_status);
                $order->setData('transaction_ref', $transaction_ref);
                $order->setData('approval_code', $approval_code);

                $order->save();

                $success = Mage::getStoreConfig('payment/p2c2predirect/toc2p_url_success', Mage::app()->getStore());
                Mage::app()->getFrontController()->getResponse()->setRedirect($success)->sendResponse();
                die;

                // $this->loadLayout();
                //
                // $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','p2c2predirect',array('template' => 'p2c2predirect/success.phtml'));
                // $this->getLayout()->getBlock('content')->append($block);
                // $this->renderLayout();
            } else {

                $order->setData('statuscode', $payment_status);
                $order->setData('transaction_ref', $transaction_ref);
                $order->setData('approval_code', $approval_code);

                if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
                    if ($lastQuoteId = Mage::getSingleton('checkout/session')->getLastQuoteId()) {
                        $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                        $quote->setIsActive(true)->save();
                        $order->cancel()->save();
                    }
                    Mage::getSingleton('core/session')->addError($this->__('AN ERROR OCCURRED IN THE PROCESS OF PAYMENT'));
                    $this->_redirect('checkout/cart'); //Redirect to cart

                    return;
                }
            }
        }
    }

    /**
     *
     */
    public function removeAction()
    {

        $token = $_REQUEST['token'];

        if (!isset($token)) {
            echo "0";
            die;
        }

        $p2c2predirectTokenModel = Mage::getModel('p2c2predirect/token');

        if (!$p2c2predirectTokenModel) {
            die("2C2P Expected Model not available.");
        }

        $model = $p2c2predirectTokenModel->load($token);

        try {
            $model->delete();
            echo "1";
            die;
        } catch (Exception $e) {
            echo "0";
            die;
        }
    }
}
