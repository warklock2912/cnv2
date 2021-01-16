<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

/**
 * @author Amasty
 */   
class Amasty_Followup_Block_Adminhtml_Rule extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_rule';
        $this->_blockGroup = 'amfollowup';
        $this->_headerText = Mage::helper('amfollowup')->__('Rules');
        $this->_addButtonLabel = Mage::helper('amfollowup')->__('Add Rule');
        parent::__construct();
    }
}