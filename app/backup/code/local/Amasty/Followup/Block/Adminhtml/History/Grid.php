<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('historyGrid');
      
      $this->setDefaultSort('history_id');
      $this->setDefaultDir('DESC');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amfollowup/history')
              ->getCollection()
              ->addCouponData()
              ->addRuleData()
              ->addPendingStatusFilter('neq');
      
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

            'filter_index' => 'rule.name'
        ));
        
        $incrementColumn = array(
            'header'    => $hlp->__('Order ID'),
            'index'     => 'increment_id',
            'width'     => 100,
            
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

        $this->addColumn('status', array(
            'header'    => $hlp->__('Sent'),
            'index'     => 'status',
            'renderer'  => 'amfollowup/adminhtml_history_grid_renderer_status',
            'type'      => 'options',
            'width' => 40,
            'align' => 'center',
            'options'   => $hlp->getHistoryStatusSent(),
        ));
        $this->addColumn('reason', array(
            'header'    => $hlp->__('Cancellation Reason'),
            'index'     => 'reason',
            'renderer'  => 'amfollowup/adminhtml_history_grid_renderer_reason',
            'type'      => 'options',
            'width' => 40,
            'align' => 'center',
            'options'   => $hlp->getCancelReasons(),
        ));
        
        $this->addColumn('finished_at', array(
            'header'    => $hlp->__('Sent At'),
            'index'     => 'finished_at',
            'type' => 'datetime',
            'width' => '160'
        ));
                
        $this->addColumn('coupon_code', array(
            'header'    => $hlp->__('Coupon'),
            'index'     => 'coupon_code',
            'width' => '160',
            'filter_index' => 'coupon_code'
        ));
        
//        $this->addColumn('times_used', array(
//            'header'    => $hlp->__('Coupon Used'),
//            'index'     => 'times_used',
//            'filter' => FALSE,
//            'sortable' => FALSE,
//            'width' => 20,
//            'align' => 'center',
//            'renderer'  => 'amfollowup/adminhtml_history_grid_renderer_used',
//        ));
        
        $this->addExportType('*/*/exportCsv', $hlp->__('CSV'));
        $this->addExportType('*/*/exportExcel', $hlp->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
    
}