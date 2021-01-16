<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Adminhtml_Order_Create_Form_Account extends Amasty_Customerattr_Block_Adminhtml_Order_Create_Form_Account_Pure
{
    protected function _toHtml()
    {
        $html       = parent::_toHtml();
        $attributes = Mage::app()->getLayout()->createBlock('amcustomerattr/adminhtml_order_create_form_attributes');
        return $html . $attributes->toHtml();
    }
}