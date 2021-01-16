<?php

class Crystal_Campaignmanage_Block_Adminhtml_Cropanddrop extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct() {
        $this->_controller = 'adminhtml_cropanddrop';
        $this->_blockGroup = 'campaignmanage';
        $this->_headerText = Mage::helper('campaignmanage')->__('Manage Crop and Drop');
        parent::__construct();
    }
}