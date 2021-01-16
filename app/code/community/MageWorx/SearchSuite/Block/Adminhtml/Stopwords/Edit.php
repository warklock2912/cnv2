<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Stopwords_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_stopwords';
        $this->_blockGroup = 'mageworx_searchsuite';
        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('mageworx_searchsuite')->__('Save Stopword'));
        $this->_updateButton('delete', 'label', Mage::helper('mageworx_searchsuite')->__('Delete Stopword'));
    }

    public function getHeaderText() {
        if (Mage::registry('current_stopword')->getId()) {
            return Mage::helper('mageworx_searchsuite')->__("Edit Stopword '%s'", $this->htmlEscape(Mage::registry('current_stopword')->getWord()));
        } else {
            return Mage::helper('mageworx_searchsuite')->__('New Stopword');
        }
    }

}
