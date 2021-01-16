<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Bannerads extends Mage_Core_Block_Template {
  public function _prepareLayout() {
    return parent::_prepareLayout();
  }

  protected function _toHtml() {
    if (!Mage::getStoreConfig('bannerads/general/enable')) {
      return '';
    }

    $collection = null;
    $banners = array();
    $todayStartOfDayDate = Mage::app()->getLocale()->date()->setTime('00:00:00')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

    $todayEndOfDayDate = Mage::app()->getLocale()->date()->setTime('23:59:59')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

    $customerSession = Mage::getModel('customer/session')->getCustomerGroupId();
    $storeResourceModel = Mage::getResourceModel('bannerads/bannerads');
    $storeId = Mage::app()->getStore()->getStoreId();
    $collection = Mage::getModel('bannerads/bannerads')->getCollection()
     ->addFieldToFilter('status', 1)
     ->addFieldToFilter('block_position', array('in' => array($this->getBlockPosition(),$this->getCateBlockPosition()),))
     ->addFieldToFilter('from_date', array('or' => array(0 => array('date' => TRUE, 'to' => $todayEndOfDayDate), 1 => array('is' => new Zend_Db_Expr('null')))), 'left')
     ->addFieldToFilter('to_date', array('or' => array(0 => array('date' => TRUE, 'from' => $todayStartOfDayDate), 1 => array('is' => new Zend_Db_Expr('null')))), 'left')
     ->setOrder('sort_order', "ASC");
    $tableStore = Mage::getSingleton('core/resource')->getTableName('bannerads_blocks_store');
    $collection->getSelect()->join(array('t2' => $tableStore), 'main_table.block_id = t2.block_id', 't2.store_id');
    $collection->addFieldToFilter('store_id', array('in' => array(0, $storeId)))->getSelect()->group('t2.block_id');

		$currentCategoryId = 0;
		if (Mage::registry('current_category')) {
			$currentCategoryId = Mage::registry('current_category')->getEntityId();
		}
		foreach ($collection as $item) {
			$canShowBlock = true;
			if ($currentCategoryId) {
				$filterCategoryIds = unserialize($item->getCategory());
					if (!in_array($currentCategoryId, $filterCategoryIds)) {
						$canShowBlock = false;
					}
			}

			if ($canShowBlock) {

				$customerGroup = unserialize($item->getCustomerGroupIds());
				$stores = $storeResourceModel->lookupStoreIds($item->getBlockId());
				if (in_array($customerSession, $customerGroup)) {
					$block = $this->getLayout()->createBlock('bannerads/blockdata')->setTemplate('bannerads/bannerads.phtml')->setBanneradsData($item);
					$banners[] = $block->renderView();
				}
			}

    }
    $htmlBanner = implode('', $banners);
    $html = $htmlBanner;
    return $html;
  }
}
