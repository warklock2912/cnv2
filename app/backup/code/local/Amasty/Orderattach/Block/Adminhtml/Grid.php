<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattach
 */
class Amasty_Orderattach_Block_Adminhtml_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amorderattachGrid');
        $this->setDefaultSort('code');
        $this->setDefaultDir('ASC');
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amorderattach/field')->getCollection();
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        
        $this->addColumn('code', array(
            'header'    => Mage::helper('amorderattach')->__('Field Alias'),
            'index'     => 'code',
        ));
        
        $this->addColumn('label', array(
            'header'    => Mage::helper('amorderattach')->__('Field Label'),
            'index'     => 'label',
        ));
        
        $this->addColumn('type', array(
            'header'    => Mage::helper('amorderattach')->__('Field Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => Mage::helper('amorderattach')->getTypes(),
        ));
        
        $this->addColumn('customer_visibility', array(
            'header'    => Mage::helper('amorderattach')->__('Display To Customer'),
            'sortable'  => true,
            'index'     => 'customer_visibility',
            'type'      => 'options',
            'options'   => Mage::helper('amorderattach')->getCustomerVisibility(true),
        ));
        
        $this->addColumn('status_backend', array(
            'header'    => Mage::helper('amorderattach')->__('Backend Status'),
            'sortable'  => false,
            'index'     => 'status_backend',
            'renderer'  => 'amorderattach/adminhtml_grid_renderer_StatusBackend',
            'align' => 'center',
            'width' => '120px',
        ));
        
        $this->addColumn('status_frontend', array(
            'header'    => Mage::helper('amorderattach')->__('Frontend Status'),
            'sortable'  => false,
            'index'     => 'status_frontend',
            'renderer'  => 'amorderattach/adminhtml_grid_renderer_StatusFrontend',
            'align' => 'center',
            'width' => '120px',
        ));
        
        $this->addColumn('show_on_grid', array(
            'header'    => Mage::helper('amorderattach')->__('Show On Grid'),
            'sortable'  => true,
            'index'     => 'show_on_grid',
            'type'      => 'options',
            'options' => array(
                '1' => Mage::helper('amorderattach')->__('Yes'),
                '0' => Mage::helper('amorderattach')->__('No'),
            ),
            'align' => 'center',
            'width' => '120px',
        ));
        
        $this->addColumn('is_enabled', array(
            'header'    => Mage::helper('amorderattach')->__('Enabled'),
            'sortable'  => true,
            'index'     => 'is_enabled',
            'type'      => 'options',
            'options' => array(
                '1' => Mage::helper('amorderattach')->__('Yes'),
                '0' => Mage::helper('amorderattach')->__('No'),
            ),
            'align' => 'center',
            'width' => '120px',
        )); 
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('field_id' => $row->getId()));
    }
}