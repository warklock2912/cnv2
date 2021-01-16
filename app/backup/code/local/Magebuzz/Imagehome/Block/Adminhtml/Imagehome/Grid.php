<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_Block_Adminhtml_Imagehome_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('imagehomeGrid');
        $this->setDefaultSort('imagehome_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('imagehome/imagehome')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('imagehome_id', array(
            'header' => Mage::helper('imagehome')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'imagehome_id',
        ));

        $this->addColumn('imagehome_grid', array(
            'header' => Mage::helper('imagehome')->__('Grid'),
            'align' => 'left',
            'index' => 'imagehome_grid',
        ));



        $this->addColumn('action', array(
            'header' => Mage::helper('imagehome')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('imagehome')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('imagehome')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('imagehome')->__('XML'));

        return parent::_prepareColumns();
    }

  

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
