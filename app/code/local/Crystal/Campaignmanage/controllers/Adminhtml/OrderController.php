<?php

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'CreateController.php';

class Crystal_Campaignmanage_Adminhtml_OrderController extends Mage_Adminhtml_Sales_Order_CreateController
{

    public function createOrderAction()
    {
        $data = $this->getRequest()->getParams();
        $joinerId = $data['joiner_id'];
        $joiner = Mage::getModel('campaignmanage/raffleonline')->load($joinerId);
        $newOrderResult = $this->createOrder($joiner);
        if ($newOrderResult['success']) {
            $incrementId = $newOrderResult['increment_id'];
            Mage::getSingleton('adminhtml/session')
                ->addSuccess($this->__('The winner\'s order %s has been created.', $incrementId));
            // $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId()));
            $this->_redirect('*/Raffleonline/edit', array('id' => $joiner->getRaffleId()));
        } else {
            Mage::getSingleton('adminhtml/session')
                ->addError($this->__($newOrderResult["messages"]));
            $this->_redirect('*/Raffleonline/edit', array('id' => $joiner->getRaffleId()));
        }
    }

    public function createOrder($joiner)
    {

        $this->_getSession()->clear();
        $storeId = Mage::app()
            ->getWebsite(true)
            ->getDefaultGroup()
            ->getDefaultStoreId();
        $website = Mage::app()->getWebsite();
        $customerId = $joiner->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $raffle = Mage::getModel('campaignmanage/campaignonline')->load($joiner->getRaffleId());

        if ($joiner->getStorepickupId() != null) {
            $storePickupId = $joiner->getStorepickupId();
            $store = Mage::getModel('storepickup/store')->load($storePickupId);
            $storepickup_shipping_price = $store->getData('shipping_price');

            Mage::getSingleton('checkout/session')->setData('storepickup_shipping_price', $storepickup_shipping_price);

            $data = array('store_id' => $storePickupId);
            Mage::getSingleton('checkout/session')->setData('storepickup_session', $data);

            $shippingAddress = array(
                'customer_address_id' => '',
                'prefix' => '',
                'firstname' => Mage::helper('storepickup')->__('Store'),
                'middlename' => '',
                'lastname' => $store->getData('store_name'),
                'suffix' => '',
                'company' => '',
                'street' => $store->getData('address'),
                'city' => $store->getCity(),
                'country_id' => $store->getData('country'), // country code
                'region' => $store->getState(),
                'region_id' => $store->getData('state_id'),
                'postcode' => $store->getData('zipcode'),
                'telephone' => $store->getStorePhone(),
                'fax' => $store->getStoreFax(),
            );
            $shippingMethod = 'storepickup_storepickup';

        } elseif ($joiner->getShippingId() != null && $raffle->getData('am_table_method_id')) {
            $shippingAddressId = $joiner->getShippingId();
            $shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
            $shippingAddress = $shippingAddress->getData();
            $shippingMethod = 'amtable_amtable' . $raffle->getData('am_table_method_id');
        } else {
            $shippingAddressId = $customer->getDefaultShipping();
            $shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
            $shippingAddress = $shippingAddress->getData();
            $shippingMethod = 'storepickup_storepickup';
        }

        if (!$customer->getDefaultBilling()) {
            $billingAddress = $shippingAddress;
        } else {
            $billingAddressId = $customer->getDefaultBilling();
            $billingAddress = Mage::getModel('customer/address')->load($billingAddressId);
            $billingAddress = $billingAddress->getData();
            if (!isset($billingAddress['firstname'])) {
                $billingAddress = $shippingAddress;
            }
        }
        if (isset($billingAddress['save_in_address_book'])) {
            unset($billingAddress['save_in_address_book']);
        };
        if (isset($shippingAddress['save_in_address_book'])) {
            unset($shippingAddress['save_in_address_book']);
        };

        $paymentMethod = 'crystal_twoctwop';


        $this->_getSession()->setCustomerId((int)$customerId);
        if($customer->getStoreId())
        {
            $this->_getSession()->setStoreId($customer->getStoreId());
        }else
        {
            $websites =  Mage::app()->getWebsites();
            foreach ($websites as $website){
                if($website->getData('is_default')){
                    $defaultStore =  $website->getDefaultStore()->getId();
                    break;
                }
            }
            $this->_getSession()->setStoreId($defaultStore);
        }

        $productId = $joiner->getProductId();
        $size = $joiner->getOption();
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product->isConfigurable()) {
            $superAttributes = array(
                '255' => $size
            );
            $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($superAttributes, $product);
            $productId = $childProduct->getId();
        }


        $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
        $this->_getOrderCreateModel()->setRecollect(true);

        $paymentData = array(
            'method' => $paymentMethod
        );

        $orderData = array(
            'currency' => $currency_code,
            'account' => array(
                'group_id' => $customer->getGroupId(),
                'email' => $customer->getEmail(),
                'profile_photo' => '',
            ),
            'billing_address' => $billingAddress,
            'shipping_address' => $shippingAddress,
            'shipping_method' => $shippingMethod,
            // 'shipping_method' => 'freeshipping_freeshipping',
            'comment' => array(
                'customer_note' => '',
            ),
            'send_confirmation' => '0',
        );

        $itemData = array(
            $productId => array(
                'qty' => '1',
                'use_discount' => '1',
                'action' => '',
            ),
        );


        try {
            $this->_processActionData('save', $orderData, $paymentData, $itemData, 0);
            $this->_getOrderCreateModel()->setPaymentData($paymentData);
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($orderData)
                ->createOrder();

            $payment = $order->getPayment();
            $createTransaction2c2p = $this->createPaymentRaffleWinner($order, $joiner->getCcCardToken());

            if ($createTransaction2c2p['status']) {
                $details = array(
                    'create_time' => gmdate("YmdHis", time()),
                    '2c2p_transaction_id' => $createTransaction2c2p['transaction_id'],
                    'type' => 'Raffle mobile winner',
                    'amount' => $order->getTotalDue()
                );
                $payment
                    ->setAdditionalData(serialize($details))
                    ->save();
                $joiner->setData('2c2p_status', true);

                ///create invoice
                if ($order->canInvoice()) {

                    $items = array();
                    foreach ($order->getAllItems() as $item) {
                        $items[$item->getId()] = $item->getQtyOrdered();
                    }

                    $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $items, null, false, true);
                    #capture the invoice
//                    Mage::getModel('sales/order_invoice_api')->capture($invoiceId);
                    if ($order->getCanSendNewEmailFlag()) {
                        $order->queueNewOrderEmail();
                    }
                }
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
//                $this->sendNotificationToWinner($joiner);
            } else {
                $details = array(
                    'create_time' => gmdate("YmdHis", time()),
                    'amount' => $order->getTotalDue(),
                    'type' => 'Raffle mobile winner',
//                    'payment_detail' => $createTransaction2c2p['message']
                );
                $order->cancel()->save();
            }
            $payment
                ->setAdditionalData(serialize($details))
                ->save();
            $joiner->setData('2c2p_status', false);
//            }

            $joiner->setOrderId($order->getId())->save();
            $result['success'] = true;
            $result['increment_id'] = $order->getIncrementId();
            $this->_getSession()->clear();
        } catch (Mage_Core_Exception $e) {
            $result['success'] = false;
            $result['messages'] = $e->getMessage();
            Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

            if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                Mage::getSingleton('checkout/session')->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    Mage::getSingleton('checkout/session')->addError(Mage::helper('core')->escapeHtml($message));
                }
            }
        } catch (Exception $e) {
            $result['success'] = false;
            $result['messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
            Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

            Mage::logException($e);
            //$this->_goBack();
        }
        return $result;
    }

    protected function createPaymentRaffleWinner($order, $cardToken)
    {

        $result['status'] = false;
        $payment = Mage::helper('twoctwop')->paymentWithToken($cardToken, $order);
        if ($payment['status'] == 'F') {
            $result['message'] = $payment['failReason'];
        } else {
            $result['status'] = true;
            $result['transaction_id'] = $payment['tranRef'];
            $result['message'] = 'Success.';
        }
        return $result;
    }

    protected function _processActionData(
        $action = null,
        $orderData = null,
        $customPaymentData = null,
        $itemData = null,
        $syncFlag = 1
    ) {
        $eventData = array(
            'order_create_model' => $this->_getOrderCreateModel(),
            'request_model' => $this->getRequest(),
            'session' => $this->_getSession(),
        );

        Mage::dispatchEvent('adminhtml_sales_order_create_process_data_before', $eventData);

        /**
         * Saving order data
         */
        if ($data = $orderData) {
            $this->_getOrderCreateModel()->importPostData($data);
        }

        /**
         * Initialize catalog rule data
         */
        $this->_getOrderCreateModel()->initRuleData();

        /**
         * init first billing address, need for virtual products
         */
        $this->_getOrderCreateModel()->getBillingAddress();

        /**
         * Flag for using billing address for shipping
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()) {
            // $syncFlag = 1;
            // $syncFlag = 0;
            $shippingMethod = $this->_getOrderCreateModel()->getShippingAddress()->getShippingMethod();
            if (is_null($syncFlag)
                && $this->_getOrderCreateModel()->getShippingAddress()->getSameAsBilling()
                && empty($shippingMethod)
            ) {
                $this->_getOrderCreateModel()->setShippingAsBilling(1);
            } else {
                $this->_getOrderCreateModel()->setShippingAsBilling((int)$syncFlag);
            }
        }

        /**
         * Change shipping address flag
         */
//        $this->_getOrderCreateModel()->resetShippingMethod(true);


        ////add product
        $items = $itemData;
        $items = $this->_processFiles($items);
        $this->_getOrderCreateModel()->addProducts($items);


        if ($paymentData = $customPaymentData) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }

        $eventData = array(
            'order_create_model' => $this->_getOrderCreateModel(),
            'request' => $this->getRequest()->getPost(),
        );

        Mage::dispatchEvent('adminhtml_sales_order_create_process_data', $eventData);

        // $this->_getOrderCreateModel()->getShippingAddress()->setFreeShipping(true);
//        $this->_getOrderCreateModel()->collectRates();
        $this->_getOrderCreateModel()
            ->collectShippingRates();

        $this->_getOrderCreateModel()
            ->saveQuote();

        if ($paymentData = $customPaymentData) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }


        return $this;
    }
}