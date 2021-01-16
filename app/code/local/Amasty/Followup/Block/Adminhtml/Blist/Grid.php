<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Block_Adminhtml_Blist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('blistGrid');
      $this->setDefaultSort('blacklist_id');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amfollowup/blist')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
   
    $hlp =  Mage::helper('amfollowup'); 
    $this->addColumn('blacklist_id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'blacklist_id',
    ));
	
    $this->addColumn('email', array(
        'header'    => $hlp->__('Email'),
        'index'     => 'email',
    ));
    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('blacklist_id');
    $this->getMassactionBlock()->setFormFieldName('ids');
    
    $actions = array(
        'massDelete'     => 'Delete',
    );
    foreach ($actions as $code => $label){
        $this->getMassactionBlock()->addItem($code, array(
             'label'    => Mage::helper('amfollowup')->__($label),
             'url'      => $this->getUrl('*/*/' . $code),
             'confirm'  => ($code == 'massDelete' ? Mage::helper('amfollowup')->__('Are you sure?') : null),
        ));        
    }
    return $this; 
  }
}