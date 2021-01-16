<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_Block_Adminhtml_Imagehome_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('imagehome_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('imagehome')->__('Item Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('imagehome')->__('Item Information'),
            'title' => Mage::helper('imagehome')->__('Item Information'),
            'content' => $this->getLayout()->createBlock('imagehome/adminhtml_imagehome_edit_tab_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
