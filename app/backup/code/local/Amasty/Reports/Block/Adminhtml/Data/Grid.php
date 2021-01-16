<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Block_Adminhtml_Data_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('dataGrid');
        $this->setDefaultSort('id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amreports/data')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('amreports');
        $this->addColumn('id', array(
            'header'    => $hlp->__('ID'),
            'width'     => 20,
            'type'      => 'text',
            'index'     => 'id'
        ));
        $this->addColumn('name', array(
            'header'    => $hlp->__('Name'),
            'type'      => 'text',
            'index'     => 'name'
        ));
        $this->addColumn('update_date', array(
            'header'    => $hlp->__('Update Date'),
            'type'      => 'datetime',
            'index'     => 'update_date'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $actions = array(
            'massDelete'     => 'Delete',
        );
        foreach ($actions as $code => $label) {
            $this->getMassactionBlock()->addItem($code, array(
                'label'    => Mage::helper('amreports')->__($label),
                'url'      => $this->getUrl('*/*/' . $code),
                'confirm'  => ($code == 'massDelete' ? Mage::helper('amreports')->__('Are you sure?') : null),
            ));
        }
        return $this;
    }
}