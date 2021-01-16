<?php

class Magebuzz_Myaccount_IndexController extends Mage_Core_Controller_Front_Action {

    public function get_order_html_in_listAction() {
        try {
            $_order_id = $this->getRequest()->getParam('order_id');
            if (!$_order_id) {
                throw new Exception('Cannot get order_id');
            }
            $this->loadLayout();
            $_layout = $this->getLayout();
            $_order = Mage::getModel('sales/order')->load($_order_id);
            $_layout->getBlock('order_items')->setOrder($_order);
            $_layout->getBlock('order_totals')->setOrder($_order);
            $_layout->getBlock('tax')->setOrder($_order);
            $result['html'] = $this->getLayout()->getBlock('order_items')->toHtml();
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function updateShippingValueAction() {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $shippingId = $this->getRequest()->getPost('shippingId');
        $customAddress = Mage::getModel('customer/address');
        $customAddress->load($shippingId);
        $customAddress->setIsDefaultShipping('1');
        $customAddress->save();
    }

    public function updateBillingValueAction() {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $billingId = $this->getRequest()->getPost('billingId');
        $customAddress = Mage::getModel('customer/address');
        $customAddress->load($billingId);
        $customAddress->setIsDefaultBilling('1');
        $customAddress->save();
    }

    public function deleteAddressAction() {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $addressId = $this->getRequest()->getPost('addressId');
        $customAddress = Mage::getModel('customer/address');
        $customAddress->load($addressId);
        $customAddress->delete();
        $result = array();
        $result['addressId'] = $addressId;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function updateNewsletterAction() {
        try {
            $result = array();
            $isSubscribed = (boolean) $this->getRequest()->getPost('is_subscribed', false);
            Mage::getSingleton('customer/session')->getCustomer()
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setIsSubscribed($isSubscribed)
                    ->save();
            $result['success'] = true;
            if ($isSubscribed) {
                $result['message'] = $this->__('The subscription has been saved.');
            } else {
                $result['message'] = $this->__('The subscription has been removed.');
            }
        } catch (Exception $e) {
            $result['success'] = false;
            $result['message'] = $this->__('An error occurred while saving your subscription.');
        }
        $result['html'] = Mage::getSingleton('core/layout')->createBlock('core/template', 'ajax_update_newsletter')
                        ->setTemplate('page/html/popup_notification.phtml')
                        ->setPopupMessage($result['message'])->renderView();


        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function subscribeAction() {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session = Mage::getSingleton('core/session');
            $customerSession = Mage::getSingleton('customer/session');
            $email = (string) $this->getRequest()->getPost('email');
            try {
                $result = array();
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                        !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('This email address is already assigned to another user.'));
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $result['message'] = $this->__('Confirmation request has been sent.');
                } else {
                    $result['message'] = $this->__('Thank you for your subscription.');
                }
                $result['success'] = true;
            } catch (Mage_Core_Exception $e) {
                $result['success'] = false;
                $result['message'] = $this->__('There was a problem with the subscription: %s', $e->getMessage());
            } catch (Exception $e) {
                $result['success'] = false;
                $result['message'] = $this->__('There was a problem with the subscription.');
            }

            $result['html'] = Mage::getSingleton('core/layout')->createBlock('core/template', 'ajax_update_newsletter')
                            ->setTemplate('page/html/popup_notification.phtml')
                            ->setPopupMessage($result['message'])->renderView();


            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkLoginAction() {
        $checkLogin = $this->getRequest()->getPost('check_login', false);
        if ($checkLogin) {
            $result = array();
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $result['is_login'] = true;
            } else {
                $result['is_login'] = false;
            }
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function setdefaultAction() {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $_response = array();
        $_response['success'] = 'error';

        $actionSave = $this->getRequest()->getParam('action');
        $addressId = $this->getRequest()->getParam('address_id');

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $address = Mage::getModel('customer/address');

        if ($addressId) {
            $existsAddress = $customer->getAddressById($addressId);
            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                $address->setId($existsAddress->getId());
                $address->setCustomerId($customer->getId());

                if ($actionSave == 'shipping') {
                    $oldAddressId = $customer->getDefaultShipping();
                    $address->setIsDefaultShipping(1);
                }
                if ($actionSave == 'billing') {
                    $oldAddressId = $customer->getDefaultBilling();
                    $address->setIsDefaultBilling(1);
                }
                try {
                    $address->save();
                    $_response['success'] = 'saved';
                    $_response['old_id'] = $oldAddressId;
                } catch (Exception $e) {
                    //$_response['susccess'] == false;
                }
            }
        }

        $this->getResponse()->setBody(json_encode($_response));
    }

    public function savesubcriptionAction() {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $_response = array();
        try {
            Mage::getSingleton('customer/session')->getCustomer()
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setIsSubscribed((boolean) $this->getRequest()->getParam('is_subscribed', false))
                    ->save();
            if ((boolean) $this->getRequest()->getParam('is_subscribed', false)) {
                $_response['success'] = 'success';
                $message = $this->__('The subscription has been saved.');
                $html = $this->getLayout()->createBlock('customer/account_dashboard_info')->setTemplate('customer/account/dashboard/update-subcription.html')->toHtml();
                $_response['message'] = '<ul class="messages"><li class="success-msg"><ul><li> <span>' . $message . '</span></li> </ul></li></ul>';
                $_response['html'] = $html;
            } else {
                $_response['success'] = 'success';
                $message = $this->__('The subscription has been removed.');
                $_response['message'] = '<ul class="messages"><li class="success-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                $html = $this->getLayout()->createBlock('customer/account_dashboard_info')->setTemplate('customer/account/dashboard/update-subcription.html')->toHtml();
                $_response['html'] = $html;
            }
        } catch (Exception $e) {
            $_response['success'] = 'error';
            $message = $this->__('An error occurred while saving your subscription.');
            $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
    }

    public function newsLetterSubmitAction() {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $_response = array();
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session = Mage::getSingleton('core/session');
            $customerSession = Mage::getSingleton('customer/session');
            $email = (string) $this->getRequest()->getPost('email');

            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    $_response['success'] = 'error';
                    $message = $this->__('Please enter a valid email address.');
                    $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
                    return;
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && !$customerSession->isLoggedIn()) {
                    $_response['success'] = 'error';
                    $message = $this->__('Sorry, but administrator denied subscription for guests');
                    $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
                    return;
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    $_response['success'] = 'error';
                    $message = $this->__('This email address is already assigned to another user.');
                    $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
                    return;
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $_response['success'] = 'success';
                    $message = $this->__('Confirmation request has been sent.');
                    $_response['message'] = '<ul class="messages"><li class="success-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
                    return;
                } else {
                    $_response['success'] = 'success';
                    $message = $this->__('Thank you for your subscription.');
                    $_response['message'] = '<ul class="messages"><li class="success-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $_response['success'] = 'error';
                $message = $this->__('There was a problem with the subscription.');
                $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
                return;
            } catch (Exception $e) {
                $_response['success'] = 'error';
                $message = $this->__('There was a problem with the subscription.');
                $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
                return;
            }
        } else {
            $_response['success'] = 'error';
            $message = $this->__('Please enter a valid email address.');
            $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
            return;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
    }

    public function get_address_edit_htmlAction() {
        try {
            $custom_address_id = $this->getRequest()->getParam('address_id');
            if (!$custom_address_id) {
                throw new Exception('Cannot get customer address id');
            }
            $this->loadLayout();
            $_layout = $this->getLayout();
            $_layout->getBlock('customer_address_edit')->getRequest()->setParam('id', $custom_address_id);
            $result['html'] = $this->getLayout()->getBlock('customer_address_edit')->toHtml();
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function formPostAction() {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $_response = array();
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
            $_response['success'] = 'error';
            $message = $this->__('Cannot save address.');
            $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
            return;
        }
        // Save data
        if ($this->getRequest()->isPost()) {
            $customer = $this->_getSession()->getCustomer();
            /* @var $address Mage_Customer_Model_Address */
            $address = Mage::getModel('customer/address');
            $addressId = $this->getRequest()->getParam('id');
            if ($addressId) {
                $existsAddress = $customer->getAddressById($addressId);
                if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                    $address->setId($existsAddress->getId());
                }
            }

            $errors = array();

            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                    ->setEntity($address);
            $addressData = $addressForm->extractData($this->getRequest());
            $addressErrors = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }

            try {
                $addressForm->compactData($addressData);
                $address->setCustomerId($customer->getId())
                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

                $addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }

                if (count($errors) === 0) {
                    $address->save();
                    $_response['success'] = 'success';
                    $message = $this->__('The address has been saved.');
                    $_response['message'] = '<ul class="messages"><li class="success-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                    $_response['html'] = $address->format('html');
                    $customer = $address->getCustomer();
                    $defaultBilling  = $customer->getDefaultBillingAddress();
                    $defaultShipping = $customer->getDefaultShippingAddress();
                    if($defaultBilling){
                      if($defaultBilling->getId() == $address->getId()){
                        $_response['is_defaultbilling'] = '1';
                      }
                    }
                  if($defaultShipping){
                    if($defaultShipping->getId() == $address->getId()){
                      $_response['is_defaultShipping'] = '1';
                    }
                  }
                } else {
                    $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
                    $message = '';
                    foreach ($errors as $errorMessage) {
                        $_response['success'] = 'error';
                        $message .= $errorMessage . '<br>';
                        $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $_response['success'] = 'error';
                $message = $e->getMessage();
                $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
            } catch (Exception $e) {
                $_response['success'] = 'error';
                $message = $this->__('Cannot save address.');
                $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>' . $message . '</span></li></ul></li></ul>';
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
        return;
    }

}
