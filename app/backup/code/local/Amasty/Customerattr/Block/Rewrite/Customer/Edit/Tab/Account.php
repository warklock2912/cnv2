<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */


class Amasty_Customerattr_Block_Rewrite_Customer_Edit_Tab_Account
    extends Mage_Adminhtml_Block_Customer_Edit_Tab_Account
{
    public function getForm()
    {
        $form = parent::getForm();
        $fieldset = $form->getElements()->getIterator()->current();
        if (!$form->getAmCustomerAttriubres()) {
            $collection = Mage::helper('amcustomerattr')
                ->getCollectionAttributes(
                    'is_visible_on_front'
                );
            $entityType = Mage::getSingleton('eav/config')->getEntityType(
                'customer'
            );
            $attrCodes = array();
            $values = array();
            $customer = Mage::registry('current_customer');
            foreach ($collection as $attribute) {
                $values[$attribute->getName()] = $customer->getDataUsingMethod(
                    $attribute->getName()
                );

                $attrCodes[] = $attribute->getName();
            }

            $attrs = Mage::getModel('customer/attribute')->getCollection();
            $filters = array("attribute_code in('" . implode("','", $attrCodes)
                             . "')");
            $attrs = Mage::helper('amcustomerattr')->addFilters(
                $attrs,
                'eav_attribute',
                $filters
            );
            $this->_setFieldset($attrs, $fieldset);
            $form->addValues($values);
            $form->setAmCustomerAttriubres(true);
        }
        return $form;
    }
}