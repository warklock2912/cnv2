<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */

/**
 * @author Amasty
 */   
class Amasty_Payrestriction_Block_Adminhtml_Rule extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_rule';
        $this->_blockGroup = 'ampayrestriction';
        $this->_headerText = Mage::helper('ampayrestriction')->__('Rules');
        $this->_addButtonLabel = Mage::helper('ampayrestriction')->__('Add Rule');
        parent::__construct();
    }
}