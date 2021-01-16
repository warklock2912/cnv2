<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Adminhtml_Customer_Reports_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amcustomerattr';
        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_customer_reports';

        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        if (Mage::registry('entity_attribute')->getId()) {
            $frontendLabel = Mage::registry('entity_attribute')
                ->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return Mage::helper('catalog')->__(
                'View Report for Attribute "%s"',
                $this->htmlEscape($frontendLabel)
            );
        } else {
            return Mage::helper('catalog')->__('New Customer Attribute');
        }
    }

}
