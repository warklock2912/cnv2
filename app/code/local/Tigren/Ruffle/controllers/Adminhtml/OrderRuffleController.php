<?php

/*
* Copyright (c) 2017 www.tigren.com
*/

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'Order'.DS.'CreateController.php';

class Tigren_Ruffle_Adminhtml_OrderRuffleController extends Mage_Adminhtml_Sales_Order_CreateController
{
    public function createOrderAction()
    {
        $log_name = "raffle_debug_".date("Y-m-d").".log";
        Mage::log("=====================================", null, $log_name, true);
        Mage::log("====START CREATE ORDER FOR WINNER====", null, $log_name, true);
        
        $this->_getSession()->clear();
        $parent = $this->getRequest()->getParam('product_id');
        $optionOfProduct = $this->getRequest()->getParam('option_id');

        $productId = $this->getChildProduct($parent, $optionOfProduct);
        $params = $this->getRequest()->getParams();
        $customerId = $this->getRequest()->getParam('customer_id');
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $defaultShippingAddress = Mage::getModel('customer/address')->load($customer->getDefaultShipping())->getData();

        $joiner = Mage::getModel('ruffle/joiner')->load($this->getRequest()->getParam('ruffle_joiner'));
        $raffle = Mage::getModel('ruffle/ruffle')->load($joiner->getData('ruffle_id'));
        $p2c2pCustomerCard = $joiner->getData('customer_card_token');
        $isSaveCard = $joiner->getData('is_savecard');
        $shippingMethod = '';
        
        Mage::log("JOINER ID : ".$joiner->getData('joiner_id'), null, $log_name, true);

        if($customer->getDefaultBilling()){
            $defaultBillingAddress = Mage::getModel('customer/address')->load($customer->getDefaultBilling())->getData();
            $billingAddress = array(
                'customer_address_id' => '',
                'prefix' => $defaultBillingAddress['prefix'],
                'firstname' => $defaultBillingAddress['firstname'],
                'middlename' => '',
                'lastname' => $defaultBillingAddress['lastname'],
                'suffix' => '',
                'company' => $defaultBillingAddress['company'],
                'street' => $defaultBillingAddress['street'],
                'city' => $defaultBillingAddress['city'],
                'country_id' => $defaultBillingAddress['country_id'], // country code
                'region' => $defaultBillingAddress['region'],
                'region_id' => $defaultBillingAddress['region_id'],
                'postcode' => $defaultBillingAddress['postcode'],
                'telephone' => $joiner->getData('telephone'),
                'fax' => $defaultBillingAddress['fax'],
            );
        }

        if ($storePickupId = $joiner->getData('storepickup_id')) {
            $shippingMethod = 'storepickup_storepickup';
            $store = Mage::getModel('storepickup/store')->load($storePickupId);
            $storepickup_shipping_price = $store->getData('shipping_price');
            Mage::getSingleton('checkout/session')->setData('storepickup_shipping_price', $storepickup_shipping_price);

            $data = array('store_id' => $storePickupId);
            Mage::getSingleton('checkout/session')->setData('storepickup_session', $data);

            $sameBilling = 0;
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
            if(!$customer->getDefaultBilling()){
                $billingAddress = $shippingAddress;
                $billingAddress['firstname'] =  $joiner->getData('firstname');
                $billingAddress['lastname'] =  '- ' . $joiner->getData('lastname');
            }
            if(isset($billingAddress['save_in_address_book'])){
                unset($billingAddress['save_in_address_book']);
            };
            if(isset($shippingAddress['save_in_address_book'])){
                unset($shippingAddress['save_in_address_book']);
            };
        } elseif ($raffle->getData('am_table_method_id')) {


            $sameBilling = 1;
            $shippingMethod = 'amtable_amtable'.$raffle->getData('am_table_method_id');

            $region_id = $joiner->getData('region_id');
            $city_id = $joiner->getData('city_id');
            $subdistrict_id = $joiner->getData('subdistrict_id');
            if ($region_id) {
                $region = Mage::getModel('directory/region')->load($region_id)->getCode();
            }
            if ($city_id) {
                $city = Mage::getModel('customaddress/city')->load($city_id)->getCode();
            }
            if ($subdistrict_id) {
                $subdistrict = Mage::getModel('customaddress/subdistrict')->load($subdistrict_id)->getCode();
            }

            $shippingAddress = array(
                'customer_address_id' => '',
                'prefix' => $defaultShippingAddress['prefix'],
                'firstname' => $joiner->getData('firstname') ? $joiner->getData('firstname') : $defaultShippingAddress['firstname'],
                'middlename' => '',
                'lastname' => $joiner->getData('lastname') ? $joiner->getData('lastname') : $defaultShippingAddress['lastname'],
                'suffix' => '',
                'company' => '',
                'street' => $joiner->getData('customer_ruffle_address'),
                'city' => $city,
                'city_id' => $city_id,
                'subdistrict' => $subdistrict,
                'subdistrict_id' => $subdistrict_id,
                'country_id' => $joiner->getData('country_id') ? $joiner->getData('country_id') : $defaultShippingAddress['country_id'], // country code
                'region' => $region,
                'region_id' => $region_id,
                'postcode' => $joiner->getData('postcode') ? $joiner->getData('postcode') : $defaultShippingAddress['postcode'],
                'telephone' => $joiner->getData('telephone') ? $joiner->getData('telephone') : $defaultShippingAddress['telephone'],
                'fax' => $defaultShippingAddress['fax'] ? $defaultShippingAddress['fax'] : '',
            );

            $billingAddress = $shippingAddress;
        }

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


        // $omiseCustomer = Mage::helper('ruffle/omise')->getCustomerOmise($customer->getData('customer_api_id'));
        // $omiseCustomerCard = $omiseCustomer->getValue()['default_card'];
        //Set payment method for the quote

           $paymentData = array(
               'method' => 'p2c2p',
               'p2c2p_tokenCard' => $p2c2pCustomerCard,
               'is_create_from_ruffle'=> 1
            );




        /// using for omise only
//        if($isSaveCard){
//            $paymentData = array(
//                'method' => 'p2c2p',
//                'omise_customer_id' => $customer->getData('customer_api_id'),
//                'omise_card_id' => $omiseCustomerCard,
//            );
//
//
//        }else{
//            $paymentData = array(
//                'method' => 'omise_gateway',
//                'omise_token' => $omiseCustomerCard,
//            );
//        }
        $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
        $this->_getOrderCreateModel()->setRecollect(true);

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

        $order = null;
        try {
            $this->_processActionData('save', $orderData, $paymentData, $itemData, $sameBilling);
            $this->_getOrderCreateModel()->setPaymentData($paymentData);
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($orderData)
                ->createOrder();
            
            if($order->getIsRafflePaymentFail() != 1){

                ///create invoice
                $payment = $order->getPayment();

                $invoice = $order->prepareInvoice()->register();
                $payment->setCreatedInvoice($invoice)
                    ->setIsTransactionClosed(false)
                    ->setIsTransactionPending(true)
                    ->addTransaction(
                        Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                        $invoice,
                        false,
                        Mage::helper('p2c2p')->__('Capturing an amount of %s ', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                    );

                $order->addRelatedObject(@$invoice);
                $items = array();
                foreach ($order->getAllItems() as $item) {
                    $items[$item->getId()] = $item->getQtyOrdered();
                }

                $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $items, null, false, true);
                #capture the invoice
                Mage::getModel('sales/order_invoice_api')->capture($invoiceId);

                ///////save order to ruffle joiner
                $joiner->setData('order_id', $order->getId());
                $joiner->save();

                
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The winner\'s order %s has been created.',$order->getIncrementId()));

            }else if($order->getIsRafflePaymentFail() == 1){
                // delete order
                Mage::getSingleton('adminhtml/session')->addWarning($this->__('The winner\'s order %s can\'t charge.',$order->getIncrementId()));
                Mage::getSingleton('adminhtml/session')->addWarning($this->__('Delete order %s',$order->getIncrementId()));
                $order->cancel()->save();
                $order->delete(); 

            }

            $this->_getSession()->clear();
            // $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId()));
        
        } catch (Exception $e) {
            Mage::log("=====SOME THING WRONG=====", null, $log_name, true);

            Mage::log($e->getMessage(), null, $log_name, true);

            if ($order && $order->getId()) {
                //save order to ruffle joiner
                $joiner->setData('order_id', $order->getId());
                $joiner->save();
            }
            

            Mage::getSingleton('adminhtml/session')->addError($this->__($e->getMessage()));
            Mage::getSingleton('adminhtml/session')->addError($this->__('Can\'t create order '));
            Mage::logException($e);
            

            
        }finally{
            Mage::log("=====END CREATE ORDER FOR WINNER=====", null, $log_name, true);
            Mage::log("=====================================", null, $log_name, true);

            $this->_redirect('adminhtml/ruffle/edit', array('id' => $raffle->getId()));
        }

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

    function getChildProduct($parentProductId, $optionData)
    {
        $product = Mage::getModel('catalog/product')->load($parentProductId);
        if ($optionData) {
            $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(unserialize($optionData), $product);
        } else {
            $childProduct = $product;
            if (!$childProduct) {
                return 0;
            }
        }

        return $childProduct->getId();
    }

    /**
     * @throws Mage_Core_Exception
     */
    function captureAction()
    {
        $order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));
        $chargeId = $order->getPayment()->getAdditionalInformation('omise_charge_id');
        $payment = $order->getPayment();
        if (!$order->canInvoice()) {
            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
        }
        try {
            $joiner = Mage::getModel('ruffle/joiner')->load($this->getRequest()->getParam('ruffle_joiner'));
            $raffle = Mage::getModel('ruffle/ruffle')->load($joiner->getData('ruffle_id'));
            $chargeResult = Mage::helper('ruffle/omise')->updateInvoiceFromChargeID($chargeId);
            // $paidresult = $chargeResult['paid'];

            if ($chargeResult->status == 'successful' && $chargeResult->paid) {
                // $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                // $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                // $invoice->register();
                // $invoice->pay();
                // $invoice->setIsPaid(true);
                // $invoice->sendEmail(true, '');
                // $invoice->save();
                //
                // $payment->setCreatedInvoice($invoice)
                //     ->setIsTransactionClosed(false)
                //     ->setIsTransactionPending(true)
                //     ->addTransaction(
                //         Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                //         $invoice,
                //         false,
                //         Mage::helper('omise_gateway')->__('Capturing an amount of %s via Omise 3-D Secure payment.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                //     );
                //
                // $transactionSave = Mage::getModel('core/resource_transaction')
                //     ->addObject($invoice)
                //     ->addObject($invoice->getOrder());
                // $transactionSave->save();
                // $msg = Mage::helper('omise_gateway')->__('Captured amount of %s online.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()));
                //
                // $order->setIsValid(true);
                // $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
                // $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
                // $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, $msg, false);
                // $order->save();


                $invoice = $order->prepareInvoice()->register();
                $payment->setCreatedInvoice($invoice)
                    ->setIsTransactionClosed(false)
                    ->setIsTransactionPending(true)
                    ->addTransaction(
                        Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                        $invoice,
                        false,
                        Mage::helper('omise_gateway')->__('Capturing an amount of %s via Omise 3-D Secure payment.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                    );

                $order->addRelatedObject($invoice);
                $items = array();
                foreach ($order->getAllItems() as $item) {
                    $items[$item->getId()] = $item->getQtyOrdered();
                }
                $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), $items, null, false, true);
                #capture the invoice
                Mage::getModel('sales/order_invoice_api')->capture($invoiceId);

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Invoice has been created.'));

                $this->_redirect('adminhtml/ruffle/edit', array('id' => $raffle->getId()));
            } else {
                throw new Exception('Can\'t update charge on omise');
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Can\'t create invoice '));
            Mage::logException($e);
            $this->_redirect('adminhtml/ruffle/edit', array('id' => $raffle->getId()));
        }

    }

}
