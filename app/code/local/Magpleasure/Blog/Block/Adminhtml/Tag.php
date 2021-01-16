<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Tag extends Magpleasure_Blog_Block_Adminhtml_Filterable
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function __construct()
    {
        $this->_controller = 'adminhtml_tag';
        $this->_blockGroup = 'mpblog';
        $this->_headerText = $this->_helper()->__('Tags');
        parent::__construct();

        $this->removeButton('add');
    }

}