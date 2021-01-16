<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Synonyms extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_blockGroup = 'mageworx_searchsuite';
        $this->_controller = 'adminhtml_synonyms';
        $this->_headerText = Mage::helper('mageworx_searchsuite')->__('Manage Synonyms');
        parent::__construct();
    }

}
