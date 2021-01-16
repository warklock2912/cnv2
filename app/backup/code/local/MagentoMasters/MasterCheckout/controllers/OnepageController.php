<?php

require_once 'Mage/Checkout/controllers/OnepageController.php';


class MagentoMasters_MasterCheckout_OnepageController extends Mage_Checkout_OnepageController
{

  public function saveShippingAction()
  {
    if ($this->_expireAjax()) {
      return;
    }
    if ($this->getRequest()->isPost()) {
      $data = $this->getRequest()->getPost('shipping', array());
      $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
      $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

      if (!isset($result['error'])) {
        $result['goto_section'] = 'billing';
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
      }
      else{
        return $result;
      }
    }
  }

  public function saveBillingAction()
  {
    if ($this->_expireAjax()) {
      return;
    }
    if ($this->getRequest()->isPost()) {
      $data = $this->getRequest()->getPost('billing', array());
      $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

      if (isset($data['email'])) {
        $data['email'] = trim($data['email']);
      }
      $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

      if (!isset($result['error'])) {
        $result['goto_section'] = 'shipping_method';
        $result['update_section'] = array(
          'name' => 'shipping-method',
          'html' => $this->_getShippingMethodsHtml()
        );
      }

      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
  }

  public function saveBillingNewaddressAction()
  {
    if ($this->_expireAjax()) {
      return;
    }
    if ($this->getRequest()->isPost()) {
      $data = $this->getRequest()->getPost('billing', array());
      $keys = array();
      foreach ($data as $key => $value) {
        $keys[] = str_replace('_new', '', $key);
      }
      $data = array_combine($keys, $data);
      $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
      if (isset($data['email'])) {
        $data['email'] = trim($data['email']);
      }
      $taxvat = $this->getRequest()->getParam('taxvat');
      if ($taxvat) {
        try {
          $customer = Mage::getSingleton('customer/session')->getCustomer();
          $customer->setTaxvat($taxvat);
          $customer->save();
        } catch (Exception $e) {
        }
      }

      $this->getRequest()->setPost('shipping', $data);
      $this->getRequest()->setPost('billing', $data);
      $this->saveShippingAction();
      $this->saveBillingAction();
    }
  }

  public function saveToAddressBookAction() {
    $this->getResponse()->setHeader('Content-type', 'application/json');
    $_result = array();
    $_result['message'] = 'fail';

    $data = $this->getRequest()->getPost();

    if(isset($data['from_form_address'])){
      $from_form_address = $data['from_form_address'];
      $customer = Mage::getSingleton('customer/session')->getCustomer();

      $address  = Mage::getModel('customer/address');

      if(isset($data[$from_form_address.'_address_id'])){
        $addressId = $data[$from_form_address.'_address_id'];
      }

      if ($addressId) {
        $existsAddress = $customer->getAddressById($addressId);
        if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
          $address->setId($existsAddress->getId());
        }
      }

      $addressForm = Mage::getModel('customer/form');
      $addressForm->setFormCode('customer_address_edit')
        ->setEntity($address);
      try {
        $defaultShipping = 0;
        $defaultBilling = 0;
        if(!$customer->getPrimaryShippingAddress()){
          $defaultShipping = 1;
        }
        if(!$customer->getPrimaryBillingAddress()){
          $defaultBilling = 1;
        }

        $addressForm->compactData($data[$from_form_address]);
        $address->setCustomerId($customer->getId())
          ->setIsDefaultBilling($defaultShipping)
          ->setIsDefaultShipping($defaultBilling);

        $address->save();

        $_result['message'] = 'success';
        $_result['checkout_address_id'] = $address->getEntityId();
        $_result['checkout_address_input'] = $address->format('oneline');
        $_result['firstname'] = $address->getData('firstname');
        $_result['lastname'] = $address->getData('lastname');
        $_result['street'] = $address->getData('street');
        $_result['region'] = $address->getData('region_id');
        $_result['city'] = $address->getData('city_id');
        $_result['subdistrict'] = $address->getData('subdistrict_id');
        $_result['postcode'] = $address->getData('postcode');
        $_result['telephone'] = $address->getData('telephone');
      }
      catch(Mage_Core_Exception $e) {

      }
    }

    return $this->getResponse()->setBody(json_encode($_result));
  }

  public function saveAddressAction() {
    $billing = $this->getRequest()->getPost('billing', array());
    $shipping = $this->getRequest()->getPost('shipping', array());
    $billingSameShipping = '';

    if(isset($billing['same_as_shipping'])){
      $billingSameShipping = $billing['same_as_shipping'];
    }

    if ($billingSameShipping) {
      if(isset($billing['invoice_needed'])){
        $shipping['invoice_needed'] = $billing['invoice_needed'];
        $shipping['taxvat'] = $billing['taxvat'];
      }

      $billingNew = $shipping;
      $billingNew['same_as_shipping'] = 1;
      $this->getRequest()->setPost('billing', $billingNew);
      $addressId = $billing = $this->getRequest()->getPost('shipping_address_id', false);
      $this->getRequest()->setPost('billing_address_id', $addressId);
    }
    else {
      if(isset($shipping['email'])){
        $billing['email'] = $shipping['email'];
      }
      if(isset($shipping['customer_password'])){
        $billing['customer_password'] = $shipping['customer_password'];
      }
      if(isset($shipping['confirm_password'])){
        $billing['confirm_password'] = $shipping['confirm_password'];
      }

      $this->getRequest()->setPost('billing', $billing);
    }

    $shippingResult = $this->saveShippingAction();
    if(isset($shippingResult['error']) && ($shippingResult['error'] == 1)){
      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($shippingResult));
    }
    else{
      $this->saveBillingAction();
    }
  }
}