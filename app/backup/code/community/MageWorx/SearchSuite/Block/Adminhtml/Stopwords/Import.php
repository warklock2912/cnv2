<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Stopwords_Import extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_stopwords';
        $this->_blockGroup = 'mageworx_searchsuite';
        $this->_mode = 'import';
        parent::__construct();
        $this->_removeButton('reset');
        $this->_updateButton('save', 'label', Mage::helper('mageworx_searchsuite')->__('Import Stopwords'));
    }

    public function getHeaderText() {
        return Mage::helper('mageworx_searchsuite')->__('Import Stopwords');
    }

}
