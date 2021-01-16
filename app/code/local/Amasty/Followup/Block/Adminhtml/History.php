<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

/**
 * @author Amasty
 */   
class Amasty_Followup_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_history';
        $this->_blockGroup = 'amfollowup';
        $this->_headerText = Mage::helper('amfollowup')->__('History');
        
        parent::__construct();
        
        $this->_removeButton('add');
    }
    
}