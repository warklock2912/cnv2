<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
  {
      parent::__construct();
      $this->setId('ruleGrid');
      $this->setDefaultSort('rule_id');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amfollowup/rule')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
   
    $hlp =  Mage::helper('amfollowup'); 
    
    $this->addColumn('rule_id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'rule_id',
    ));

    $this->addColumn('is_active', array(
        'header'    => Mage::helper('salesrule')->__('Status'),
        'align'     => 'left',
        'width'     => '80px',
        'index'     => 'is_active',
        'type'      => 'options',
        'options'   => $hlp->getRuleStatuses()
    ));    

    $this->addColumn('name', array(
        'header'    => $hlp->__('Name'),
        'index'     => 'name',
    ));  

    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
}