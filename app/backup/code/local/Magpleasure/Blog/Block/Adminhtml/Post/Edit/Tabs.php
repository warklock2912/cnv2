<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Post_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        parent::__construct();
        $this->setId('blog_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->_helper()->__('Post Information'));
        $this->setTemplate('mpblog/post/tabs.phtml');
    }

    protected function _beforeToHtml()
    {
        if ($tab = $this->getRequest()->getParam('tab')){
            $this->setActiveTab($tab);
        }


        return parent::_beforeToHtml();
    }


}