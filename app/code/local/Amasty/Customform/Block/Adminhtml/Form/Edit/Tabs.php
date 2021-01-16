<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Form_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form'); // this should be same as the form id
        $this->setTitle(Mage::helper('amcustomform')->__('Form Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'     => Mage::helper('amcustomform')->__('General'),
            'title'     => Mage::helper('amcustomform')->__('General'),
            'content'   => $this->getLayout()->createBlock('amcustomform/adminhtml_form_edit_tab_general')->toHtml(),
        ));

        $this->addTab('titles', array(
            'label'     => Mage::helper('amcustomform')->__('Titles'),
            'title'     => Mage::helper('amcustomform')->__('Titles'),
            'content'   => $this->getLayout()->createBlock('amcustomform/adminhtml_form_edit_tab_titles')->toHtml(),
        ));

        $this->addTab('layout', array(
            'label'     => Mage::helper('amcustomform')->__('Form Layout'),
            'title'     => Mage::helper('amcustomform')->__('Form Layout'),
            'content'   => $this->getLayout()->createBlock('amcustomform/adminhtml_form_edit_tab_layout')->toHtml(),
        ));

        $this->addTab('embedding', array(
            'label'     => Mage::helper('amcustomform')->__('Embedding'),
            'title'     => Mage::helper('amcustomform')->__('Embedding'),
            'content'   => $this->getLayout()->createBlock('amcustomform/adminhtml_form_edit_tab_embedding')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
