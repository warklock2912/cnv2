<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_Block_Adminhtml_Imagehome extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_imagehome';
        $this->_blockGroup = 'imagehome';
        $this->_headerText = Mage::helper('imagehome')->__('Item Manager');


        parent::__construct();
        $Imagehomes = Mage::getModel('imagehome/imagehome')->getCollection();
        if ($Imagehomes->getSize()) {
            $this->_removeButton('add');
        }
    }

}
