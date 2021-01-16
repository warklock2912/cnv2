<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */


class Amasty_Customerattr_Block_Rewrite_Sales_Order_Create_Billing_Address
    extends Mage_Adminhtml_Block_Sales_Order_Create_Billing_Address
{

    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $fieldset = $form->getElements()->getIterator()->current();
        if ($form->getAmCustomerAttriubres()) {
            return;
        }

        $collection = Mage::helper('amcustomerattr')
            ->getCollectionAttributes(
                'used_in_product_listing'
            );
        $entityType = Mage::getSingleton('eav/config')->getEntityType(
            'customer'
        );
        $attrs = array();
        foreach ($collection as $attribute) {
            $attr = Mage::getModel('customer/attribute')->loadByCode(
                $entityType, $attribute->getName()
            );
            $attrs[] = $attr;
        }

        $this->_addAttributesToForm($attrs, $fieldset);
        foreach ($attrs as $attrib) {
            $element = $form->getElement($attrib->getName());
            $element->setName(
                "amcustomerattr[" . $attrib->getAttributeCode() . "]"
            );
        }

        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        $customer = $quote->getCustomer();
        $customerData = $customer->getData();
        $values = array();
        if (empty($customerData)) {
            $orderId = Mage::getSingleton('adminhtml/session_quote')
                ->getData('order_id');
            $guest = Mage::getModel('amcustomerattr/guest')->load(
                $orderId, 'order_id'
            );
            $data = $guest->getData();
            $exclude = array('id', 'order_id');
            foreach ($data as $key => $attr) {
                if (!in_array($key, $exclude)) {
                    $values[$key] = $attr;
                }
            }
        } else {
            $collection = Mage::getModel('customer/attribute')
                ->getCollection();
            $filters = array(
                "is_user_defined = 1",
                "entity_type_id = " . Mage::getModel('eav/entity')
                    ->setType('customer')
                    ->getTypeId()
            );
            $collection = Mage::helper('amcustomerattr')->addFilters(
                $collection, 'eav_attribute', $filters
            );

            foreach ($collection as $attribute) {
                if (isset($customerData[$attribute->getAttributeCode()])
                    && !isset($values[$attribute->getAttributeCode()])
                ) {
                    $values[$attribute->getAttributeCode()]
                        = $customerData[$attribute->getAttributeCode()];
                }

            }
        }
        if (!empty($values)) {
            $form->addValues($values);
        }


        $form->setAmCustomerAttriubres(true);

    }
}


