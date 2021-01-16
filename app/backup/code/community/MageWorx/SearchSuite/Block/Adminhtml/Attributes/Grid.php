<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Attributes_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function _construct() {
        $this->setId('searchAttributes');
        parent::_construct();
    }

    protected function _prepareColumns() {
        parent::_prepareColumns();

        $this->addColumn('attribute_code', array(
            'header' => Mage::helper('eav')->__('Attribute Code'),
            'sortable' => true,
            'index' => 'attribute_code'
        ));

        $this->addColumn('frontend_label', array(
            'header' => Mage::helper('eav')->__('Attribute Label'),
            'sortable' => true,
            'index' => 'frontend_label'
        ));

        $priority = Mage::getModel('mageworx_searchsuite/search_priority')->toArray();
        foreach ($priority as $key => $item) {
            $this->addColumn('quick_search_priority_' . $key, array(
                'header' => $item . ' ' . Mage::helper('mageworx_searchsuite')->__('Priority'),
                'sortable' => true,
                'index' => 'quick_search_priority',
                'type' => 'options',
                'renderer' => 'mageworx_searchsuite/adminhtml_attributes_grid_renderer_priority',
                'options' => $priority,
                'filter' => false,
                'width' => '50px',
                'align' => 'center',
            ));
        }

        $this->addColumn('quick_search_priority_0', array(
            'header' => Mage::helper('mageworx_searchsuite')->__('Do not use'),
            'sortable' => true,
            'index' => 'is_searchable',
            'type' => 'options',
            'renderer' => 'mageworx_searchsuite/adminhtml_attributes_grid_renderer_priority',
            'options' => $priority,
            'filter' => false,
            'width' => '20px',
            'align' => 'center',
        ));

        $this->addColumn('is_attributes_search', array(
            'header' => Mage::helper('mageworx_searchsuite')->__('Search by Attributes'),
            'sortable' => true,
            'index' => 'is_attributes_search',
            'type' => 'checkbox',
            'renderer' => 'mageworx_searchsuite/adminhtml_attributes_grid_renderer_search',
            'align' => 'center',
            'width' => '20px',
            'filter' => false,
        ));

        return $this;
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addVisibleFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

}
