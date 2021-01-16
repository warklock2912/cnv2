<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Synonyms_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function _construct() {
        $this->setId('synonymsGrid');
        parent::_construct();
    }

    protected function _prepareColumns() {
        parent::_prepareColumns();

        $this->addColumn('query_text', array(
            'header' => Mage::helper('catalogsearch')->__('Query Text'),
            'sortable' => true,
            'index' => 'query_text',
            'width' => '200px'
        ));

        $this->addColumn('query_text', array(
            'header' => Mage::helper('catalogsearch')->__('Query Text'),
            'sortable' => true,
            'index' => 'query_text',
            'width' => '200px'
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('catalog')->__('Store'),
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'sortable' => false,
                'width' => '200px'
            ));
        }
        $this->addColumn('synonyms', array(
            'header' => Mage::helper('mageworx_searchsuite')->__('Synonyms'),
            'index' => 'synonyms',
        ));

        return $this;
    }

    protected function _prepareCollection() {
    	$collection = Mage::getResourceModel('mageworx_searchsuite/synonym_collection')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/mageworx_searchsuite_synonyms/edit', array(
                    'id' => $row->getId()
        ));
    }
    
    protected function _prepareMassaction() {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('synonym_ids');
        $this->getMassactionBlock()->addItem('delete_synonym', array(
            'label' => Mage::helper('adminhtml')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('mageworx_searchsuite')->__('Are you sure?')
        ));
    }

}
