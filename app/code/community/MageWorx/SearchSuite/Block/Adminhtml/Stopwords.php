<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Stopwords extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_blockGroup = 'mageworx_searchsuite';
        $this->_controller = 'adminhtml_stopwords';
        $this->_headerText = Mage::helper('mageworx_searchsuite')->__('Manage Stopwords');
        $this->addButton('stopword_import', array(
            'label' => Mage::helper('mageworx_searchsuite')->__('Import Stopwords'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/import') . '\')'
        ));
        parent::__construct();
    }

}
