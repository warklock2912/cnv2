<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Stopwords_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function _construct() {
        $this->setId('stopwordsGrid');
        parent::_construct();
    }

    protected function _prepareColumns() {
        parent::_prepareColumns();

        $this->addColumn('id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'width' => '50px',
            'index' => 'id',
        ));

        $this->addColumn('stopword', array(
            'header' => Mage::helper('mageworx_searchsuite')->__('Stopword'),
            'sortable' => true,
            'index' => 'word',
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

        return $this;
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('mageworx_searchsuite/stopword_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/mageworx_searchsuite_stopwords/edit', array(
                    'id' => $row->getId()
        ));
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('stopword_ids');
        $this->getMassactionBlock()->addItem('delete_stopword', array(
            'label' => Mage::helper('adminhtml')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('mageworx_searchsuite')->__('Are you sure?')
        ));
    }

}
