<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */ 
class Amasty_Segments_Block_Adminhtml_Segment_Edit_Tab_Customers extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
         $this->setId('gridCustomers');
         $this->setUseAjax(true);
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customerGrid', array('_current'=>true));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('amsegments/index_collection')
                ->addResultSegmentData($this->getModel()->getId());
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('customer_email', array(
            'header'    => Mage::helper('amsegments')->__('Email'),
            'index'     => 'customer_email',
            'sortable'  => true
        ));
        
        $this->addColumn('customer_firstname', array(
            'header'    => Mage::helper('amsegments')->__('First Name'),
            'index'     => 'customer_firstname',
            'sortable'  => true
        ));
        
        $this->addColumn('customer_lastname', array(
            'header'    => Mage::helper('amsegments')->__('Last Name'),
            'index'     => 'customer_lastname',
            'sortable'  => true
        ));

        if (!$this->_isExport){
            $this->addColumn('link', array(
                'header'    => '',
                'index'     =>'link',
                'sortable'  =>false,
                'filter'    => false,
                'renderer'  => 'amsegments/adminhtml_segment_edit_tab_customers_renderer_link',
                'align'     => 'center',
            ));
        }
        
        $this->addExportType('*/*/exportCsv', Mage::helper('amsegments')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('amsegments')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
}