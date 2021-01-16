<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Block_Adminhtml_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setDefaultSort('history_id');
      $this->setDefaultDir('DESC');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amfollowup/history')
              ->getCollection()
//              ->addScheduleData()
              ->addRuleData()
              ->addPendingStatusFilter();
      
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

    protected function _prepareColumns()
    {

        $hlp =  Mage::helper('amfollowup'); 
        $this->addColumn('history_id', array(
            'header'    => $hlp->__('ID'),
            'index'     => 'history_id',
            'width'     => 40,
        ));

        $this->addColumn('rulename', array(
            'header'    => $hlp->__('Rule'),
            'index'     => 'rulename',
            'filter_index' => 'rulename',
            'filter_index' => 'rule.name',
        ));
        
        $incrementColumn = array(
            'header'    => $hlp->__('Order ID'),
            'index'     => 'increment_id',
            'width'     => 100,
            'type'      => 'action',
        );
        
        if (!$this->_isExport){
            $incrementColumn['renderer']  = 'amfollowup/adminhtml_queue_grid_renderer_order';
        }
                
        $this->addColumn('increment_id', $incrementColumn);
        
        $this->addColumn('customer_name', array(
            'header'    => $hlp->__('Customer Name'),
            'index'     => 'customer_name',
        ));
        
        $this->addColumn('email', array(
            'header'    => $hlp->__('Customer Email'),
            'index'     => 'email',
        ));
        
        
        $this->addColumn('delayed_start', array(
            'header'    => $hlp->__('Delay'),
            'index'     => 'delayed_start',
            'filter' => FALSE,
            'renderer'  => 'amfollowup/adminhtml_queue_grid_renderer_delay',
        ));
        
        $this->addColumn('coupon_code', array(
            'header'    => $hlp->__('Coupon'),
            'index'     => 'coupon_code',
            
            'filter_index' => 'coupon_code'
        ));

        $this->addColumn('scheduled_at', array(
            'header'    => $hlp->__('Scheduled At'),
            'index'     => 'scheduled_at',
            'type' => 'datetime',
            'width' => '160'
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('amfollowup')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('amfollowup')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));
        
        return parent::_prepareColumns();
    }
        
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('queue_id');
        $this->getMassactionBlock()->setFormFieldName('queue');
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('amfollowup')->__('Cancel'),
             'url'      => $this->getUrl('*/*/massCancel'),
             'confirm'  => Mage::helper('amfollowup')->__('Are you sure?')
        ));
        
        return $this; 
    }
    
    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/edit', array('id' => $item->getId()));
    }
    
}