<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Adminhtml_Customer_Reports_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('product_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('catalog')->__('Reports'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'main', array(
            'label' => Mage::helper('catalog')->__('Reports'),
            'title' => Mage::helper('catalog')->__('Reports'),
            'content' => $this->getLayout()->createBlock(
                'amcustomerattr/adminhtml_customer_reports_edit_tab_main'
            )->toHtml(),
            'active' => true
        )
        );

        return parent::_beforeToHtml();
    }

}
