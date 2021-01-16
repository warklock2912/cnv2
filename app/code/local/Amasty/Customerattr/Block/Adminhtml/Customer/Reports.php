<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Adminhtml_Customer_Reports
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amcustomerattr';
        $this->_controller = 'adminhtml_customer_reports';
        $this->_headerText = Mage::helper('amcustomerattr')->__('Reports');
        parent::__construct();
        $this->_removeButton('add');
    }

}
