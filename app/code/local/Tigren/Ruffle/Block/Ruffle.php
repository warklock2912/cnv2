<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Block_Ruffle extends Mage_Core_Block_Template {
	protected $_ruffleItems;
	public function _prepareLayout() {
		return parent::_prepareLayout();
		// $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');			
  //   	$pager->setAvailableLimit($this->getPagingValues());
		// $pager->setCollection($this->getCollection());
  //   	$this->setChild('pager', $pager);
    	return $this;
  	}

  	public function __construct() {
    	parent::__construct();
    	$ruffleProductIds = $this->_getRuffleItems();
    	$collection =  Mage::getModel('catalog/product')->getCollection()
    		->addFieldToFilter('entity_id', array('in' => $ruffleProductIds))
    		->addAttributeToSelect('*')
    		->addWebsiteFilter();
      Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

      // $collection->getSelect()
    	// 	->join(array('ruffle_item' => Mage::getSingleton('core/resource')->getTableName('ruffle_product')), 
    	// 		'main_table.entity_id=ruffle_item.product_id', 
    	// 		array('product_id')
    	// 	)
    	$this->setCollection($collection);
  	}
    
	protected function _getRuffleItems() {
		if (empty($this->_ruffleItems)) {
			$currentTimestamp = Mage::getModel('core/date')->timestamp(time());
			$todayDate = date('Y-m-d', $currentTimestamp);
      $customer = Mage::getSingleton('customer/session')->getCustomer();
      $groupId = $customer->getCustomerGroupId();

			$collection = Mage::getModel('ruffle/product')->getCollection();

			$collection->getSelect()
				->join(array('ruffle' => Mage::getSingleton('core/resource')->getTableName('ruffle')), 'main_table.ruffle_id=ruffle.ruffle_id', array('ruffle_id'))
				->joinLeft(array('cp' => Mage::getSingleton('core/resource')->getTableName('catalog/product')), 'main_table.product_id=cp.entity_id', array('type_id'))
				->where('ruffle.is_active=?', 1)
      ;
      if($groupId == Tigren_Ruffle_Model_Ruffle::RUFFLE_VIP_GROUP_ID){
        $collection->getSelect()->where('( IF(`type_id` = \'simple\',vip_qty,99999)  > 0)');
      }else{
        $collection->getSelect()->where('( IF(`type_id` = \'simple\',general_qty,99999)  > 0)');
      }
			$collection->addFieldToFilter('ruffle.start_date', array('to' => $todayDate))
				->addFieldToFilter('ruffle.end_date', array('from' => $todayDate));
			$this->_ruffleItems = $collection->getColumnValues('product_id');
		}

		return $this->_ruffleItems;
	}
}