<?php

class Crystal_Campaignmanage_Block_Adminhtml_Cropanddrop_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('cropanddropGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(TRUE);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('campaignmanage/cropanddrop')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('campaignmanage')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('campaignmanage')->__('Title'),
            'align' => 'left',
            'index' => 'title',
        ));
        $this->addColumn('content', array(
            'header' => Mage::helper('campaignmanage')->__('Message'),
            'align' => 'left',
            'index' => 'content',
        ));

        $this->addColumn('product_id', array(
            'header' => Mage::helper('campaignmanage')->__('Product Id'),
            'align' => 'left',
            'index' => 'product_id'
        ));
        $this->addColumn('size', array(
            'header' => Mage::helper('campaignmanage')->__('Size'),
            'align' => 'left',
            'index' => 'size'
        ));
        $this->addColumn('created_at', array(
            'header' => Mage::helper('campaignmanage')->__('Created At'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'created_at'
        ));



        $this->addColumn('action', array(
            'header' => Mage::helper('campaignmanage')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array('caption' => Mage::helper('campaignmanage')->__('Edit'), 'url' => array('base' => '*/*/edit'), 'field' => 'id')
            ),
            'filter' => FALSE,
            'sortable' => FALSE,
            'index' => 'stores',
            'is_system' => TRUE,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('campaignmanage')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('campaignmanage')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('campaign_id');
        $this->getMassactionBlock()->setFormFieldName('campaignmanage');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('adminhtml')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('adminhtml')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}