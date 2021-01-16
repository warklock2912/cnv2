<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Block_Adminhtml_Field_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form'); // this should be same as the form id
        $this->setTitle(Mage::helper('amcustomform')->__('Field Information'));
    }

    protected function _beforeToHtml()
    {
        /** @var Amasty_Customform_Helper_Data $helper */
        $helper = Mage::helper('amcustomform');

        $this->addTab('general', array(
            'label'     => $helper->__('General'),
            'title'     => $helper->__('General'),
            'content'   => $this->getLayout()->createBlock('amcustomform/adminhtml_field_edit_tab_main')->toHtml(),
        ));

        $this->addTab('options', array(
            'label'     => $helper->__('Labels / Options'),
            'title'     => $helper->__('Labels / Options'),
            'content'   => $this->getLayout()->createBlock('amcustomform/adminhtml_field_edit_tab_options')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
