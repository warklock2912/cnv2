<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Synonyms_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_synonyms';
        $this->_blockGroup = 'mageworx_searchsuite';
        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('mageworx_searchsuite')->__('Save Synonyms'));
        $this->_updateButton('delete', 'label', Mage::helper('mageworx_searchsuite')->__('Delete Synonyms'));
    }

    public function getHeaderText() {
        if (Mage::registry('current_catalog_search')->getId()) {
            return Mage::helper('mageworx_searchsuite')->__("Edit Synonyms For '%s'", $this->htmlEscape(Mage::registry('current_catalog_search')->getQueryText()));
        } else {
            return Mage::helper('mageworx_searchsuite')->__('New Synonyms');
        }
    }

}
