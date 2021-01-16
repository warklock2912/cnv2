<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Attributes extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_blockGroup = 'mageworx_searchsuite';
        $this->_controller = 'adminhtml_attributes';
        $this->_headerText = Mage::helper('mageworx_searchsuite')->__('Manage Attributes');
        parent::__construct();
        $this->_removeButton('add');
        $this->addButton('save', array(
            'label' => Mage::helper('mageworx_searchsuite')->__('Save attributes'),
            'onclick' => 'var saf = $(\'searchAttributes_form\');saf.action = \'' . $this->getUrl('*/*/save') . '\';saf.submit();',
            'class' => 'save',
        ));
    }

}
