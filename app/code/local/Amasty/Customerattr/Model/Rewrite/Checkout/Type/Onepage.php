<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */



class Amasty_Customerattr_Model_Rewrite_Checkout_Type_Onepage
    extends Mage_Checkout_Model_Type_Onepage
{
    public function saveBilling($data, $customerAddressId)
    {
        if (!isset($data['amcustomerattr'])) {
          $usingCaseBilling = isset($data['same_as_shipping']) ? (int)$data['same_as_shipping'] : 0;
          if($usingCaseBilling == 1){
            $this->getQuote()->getBillingAddress()->setSameAsShipping(1);
          }else{
            $this->getQuote()->getBillingAddress()->setSameAsShipping(0);
          }
            return parent::saveBilling($data, $customerAddressId);
        }

        // checking unique attributes
        $checkUnique = array();
        $nameGroupAttribute = '';
        $idGroupSelect = '';
        $collection = Mage::getModel('eav/entity_attribute')->getCollection();

        $filters = array(
            "is_user_defined = 1",
            "entity_type_id  = " . Mage::getModel('eav/entity')
                ->setType('customer')
                ->getTypeId()
        );
        $collection = Mage::helper('amcustomerattr')->addFilters(
            $collection,
            'eav_attribute',
            $filters
        );

        /* @
         * setup $checkUnique array('attribute_code','attribute_label')
         */
        foreach ($collection as $attribute) {
            if ($attribute->getIsUnique()) {
                $translations = $attribute->getStoreLabels();
                $storeId = Mage::app()->getStore()->getId();
                $checkUnique[$attribute->getAttributeCode()]
                    = isset($translations[$storeId])
                    ? $translations[$storeId]
                    : $attribute->getFrontend()->getLabel();
            }
        }
        /* @
         * get attribute code for last attribute with type_internal == 'selectgroup'
         */
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $filters = array(
            "is_user_defined = 1",
            "entity_type_id  = " . Mage::getModel('eav/entity')
                ->setType('customer')
                ->getTypeId()
        );
        $collection = Mage::helper('amcustomerattr')->addFilters(
            $collection,
            'eav_attribute',
            $filters
        );

        foreach ($collection as $attribute) {
            if ('selectgroup' == $attribute->getTypeInternal()) {
                $nameGroupAttribute = $attribute->getAttributeCode();
            }
        }
        foreach (
            $data['amcustomerattr'] as $attributeCode => $attributeValue
        ) {
            if ($attributeCode == $nameGroupAttribute) {
                $idGroupSelect = $attributeValue;
            }
        }
        if ($idGroupSelect) {
            $option = Mage::getModel('eav/entity_attribute_option')->load(
                $idGroupSelect
            );
            if ($option && $option->getGroupId()) {
                $customer = Mage::getModel('customer/customer');
                $customer->setGroupId($option->getGroupId());
            }
        }

        if (!empty($checkUnique)) {
            foreach ($checkUnique as $attributeCode => $attributeLabel) {
                //skip empty values

                if (!array_key_exists(
                        $attributeCode, $data['amcustomerattr']
                    )
                    || !$data['amcustomerattr'][$attributeCode]
                ) {
                    continue;
                }
                $customerCollection = Mage::getResourceModel(
                    'customer/customer_collection'
                );
                $customerCollection->addAttributeToFilter(
                    $attributeCode,
                    array('eq' => $data['amcustomerattr'][$attributeCode])
                );
                if ($customerId = Mage::getSingleton('customer/session')
                    ->getCustomer()->getId()
                ) {
                    $mainAlias = (false !== strpos(
                            $customerCollection->getSelect()->__toString(),
                            'AS `e'
                        )) ? 'e' : 'main_table';
                    $customerCollection->getSelect()->where(
                        $mainAlias . '.entity_id != ?', $customerId
                    );
                }
                if ($customerCollection->getSize() > 0) {
                    $result = array(
                        'error'   => 1,
                        'message' => Mage::helper('amcustomerattr')->__(
                            'Please specify different value for "%s" attribute. Customer with such value already exists.',
                            $attributeLabel
                        ),
                    );
                    return $result;
                }
            }
        }
        Mage::getSingleton('checkout/session')->setAmcustomerattr(
            $data['amcustomerattr']
        );
        Mage::getSingleton('customer/session')->setAmcustomerattr(
            $data['amcustomerattr']
        );

        return parent::saveBilling($data, $customerAddressId);
    }

  protected function _prepareCustomerQuote()
  {
    $quote      = $this->getQuote();
    $billing    = $quote->getBillingAddress();
    $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

    $customer = $this->getCustomerSession()->getCustomer();

    if ($shipping && !$shipping->getSameAsBilling() &&(!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())) {
      $customerShipping = $shipping->exportCustomerAddress();
      $customer->addAddress($customerShipping);
      $shipping->setCustomerAddress($customerShipping);
    }
    if (!$billing->getSameAsShipping() && (!$billing->getCustomerId() || $billing->getSaveInAddressBook())) {
      $customerBilling = $billing->exportCustomerAddress();
      $customer->addAddress($customerBilling);
      $billing->setCustomerAddress($customerBilling);
    }

    if (isset($customerBilling) && !$customer->getDefaultBilling() && !$billing->getSameAsShipping()) {
      $customerBilling->setIsDefaultBilling(true);
    }
    if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
      $customerShipping->setIsDefaultShipping(true);
      if($billing->getSameAsShipping()){
        $customerShipping->setIsDefaultBilling(true);
      }
    } else if (isset($customerBilling) && !$customer->getDefaultShipping()) {
      $customerBilling->setIsDefaultShipping(true);
    }

    $quote->setCustomer($customer);
  }
}