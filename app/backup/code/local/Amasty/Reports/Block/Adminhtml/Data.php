<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Block_Adminhtml_Data extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        parent::_construct();
        $helper = Mage::helper('amreports');
        $this->_blockGroup = 'amreports';
        $this->_controller = 'adminhtml_data';
        $this->_headerText = $helper->__('Report Management');
        $this->_addButtonLabel = $helper->__('Add report');
    }
}