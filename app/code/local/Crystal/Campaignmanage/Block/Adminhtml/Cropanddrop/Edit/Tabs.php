<?php

class Crystal_Campaignmanage_Block_Adminhtml_Cropanddrop_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('campaignmanage')->__('Crop and Drop Infomation'));
    }

    public function _beforeToHtml()
    {
        $this->addTab('form_session', array(
            'label' => Mage::helper('campaignmanage')->__('Crop and Drop Detail'),
            'title' => Mage::helper('campaignmanage')->__('Crop and Drop Detail'),
            'content' => $this->getLayout()->createBlock('campaignmanage/adminhtml_cropanddrop_edit_tab_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}