<?php

	/*
	* Copyright (c) 2015 www.magebuzz.com
	*/

	class Magebuzz_Bannerads_Block_Blockdata extends Mage_Core_Block_Template
	{
		public function _prepareLayout()
		{
			return parent::_prepareLayout();
		}

		public function getBannerads()
		{
			$blockData = $this->getBanneradsData();
			$blockId = $blockData->getBlockId();
			$imageModel = Mage::getModel('bannerads/images');
			$blockImage = Mage::getResourceModel('bannerads/bannerads')->lookupImagesId($blockId);
			$images = $imageModel->getCollection()
			 ->addFieldToFilter('banner_id', array('in' => $blockImage))
			 ->addFieldtoFilter('start_time',
			  array(
			   array('to' => Mage::getModel('core/date')->gmtDate()),
			   array('start_time', 'null' => '')
			  )
			 )
			 ->addFieldtoFilter('end_time',
			  array(
			   array('gteq' => Mage::getModel('core/date')->gmtDate()),
			   array('end_time', 'null' => '')
			  )
			 )
			 ->addFieldToFilter('status', 1)->setOrder('sort_order', "ASC");
			if ($blockData->getDisplayType() == 2) {
				$images->getSelect()->order('rand()');
				$images->setPageSize(1);
			}
			$blockData->setImages($images);
			return $blockData;
		}

	}
