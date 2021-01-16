<?php

	/*
	* Copyright (c) 2015 www.magebuzz.com
	*/

	class Magebuzz_Bannerads_Helper_Data extends Mage_Core_Helper_Abstract
	{
		const XML_PATH_ENABLE_BANNER_SLIDER = 'bannerads/general/enable';
		const XML_PATH_INCLUDE_JS = 'bannerads/general/include_js';
		const XML_PATH_TITLE_BANNER = 'banners/general/show_title_banners';
		const XML_PATH_DESCRIPTION_BANNER = 'banners/general/show_description_banners';

		public function getPositionOptionsArray()
		{
			return array(
			 array(
			  'label' => $this->__('------- Please choose banner position -------'),
			  'value' => ''
			 ),
			 array(
			  'label' => $this->__('Popular positions'),
			  'value' => array(
			   array(
				'value' => 'cms-page-content-top',
				'label' => $this->__('Homepage-Content-Top')
			   ),
			  )
			 ),
			 array(
			  'label' => $this->__('Custom Position (will be displayed on custom position)'),
			  'value' => array(
			   array(
				'value' => 'custom-position',
				'label' => $this->__('Custom')
			   ),
			  )
			 ),
			 array(
			  'label' => $this->__('General (will be displayed on all pages)'),
			  'value' => array(
			   array(
				'value' => 'sidebar-right-top',
				'label' => $this->__('Sidebar-Top-Right')
			   ),
			   array(
				'value' => 'sidebar-right-bottom',
				'label' => $this->__('Sidebar-Bottom-Right')
			   ),
			   array(
				'value' => 'sidebar-left-top',
				'label' => $this->__('Sidebar-Top-Left')
			   ),
			   array(
				'value' => 'sidebar-left-bottom',
				'label' => $this->__('Sidebar-Bottom-Left')
			   ),
			   array(
				'value' => 'content-top',
				'label' => $this->__('Content-Top')
			   ),
			   array(
				'value' => 'menu-top',
				'label' => $this->__('Menu-Top')
			   ),
			   array(
				'value' => 'menu-bottom',
				'label' => $this->__('Menu-Bottom')
			   ),
			   array(
				'value' => 'page-bottom',
				'label' => $this->__('Page-Bottom')
			   ),
			  )
			 ),
			 array(
			  'label' => $this->__('Catalog and product'),
			  'value' => array(
			   array(
				'value' => 'catalog-sidebar-right-top',
				'label' => $this->__('Catalog-Sidebar-Top-Right')
			   ),
			   array(
				'value' => 'catalog-sidebar-right-bottom',
				'label' => $this->__('Catalog-Sidebar-Bottom-Right')
			   ),
			   array(
				'value' => 'catalog-sidebar-left-top',
				'label' => $this->__('Catalog-Sidebar-Top-Left')
			   ),
			   array(
				'value' => 'catalog-sidebar-left-bottom',
				'label' => $this->__('Catalog-Sidebar-Bottom-Left')
			   ),
			   array(
				'value' => 'catalog-content-top',
				'label' => $this->__('Catalog-Content-Top')
			   ),
			   array(
				'value' => 'catalog-menu-top',
				'label' => $this->__('Catalog-Menu-Top')
			   ),
			   array(
				'value' => 'catalog-menu-bottom',
				'label' => $this->__('Catalog-Menu-Bottom')
			   ),
			   array(
				'value' => 'catalog-page-bottom',
				'label' => $this->__('Catalog-Page-Bottom')
			   ),
			  )
			 ),
			 array(
			  'label' => $this->__('Category only'),
			  'value' => array(
			   array(
				'value' => 'category-sidebar-right-top',
				'label' => $this->__('Category-Sidebar-Top-Right')
			   ),
			   array(
				'value' => 'category-sidebar-right-bottom',
				'label' => $this->__('Category-Sidebar-Bottom-Right')
			   ),
			   array(
				'value' => 'category-sidebar-left-top',
				'label' => $this->__('Category-Sidebar-Top-Left')
			   ),
			   array(
				'value' => 'category-sidebar-left-bottom',
				'label' => $this->__('Category-Sidebar-Bottom-Left')
			   ),
			   array(
				'value' => 'category-content-top',
				'label' => $this->__('Category-Content-Top')
			   ),
			   array(
				'value' => 'category-menu-top',
				'label' => $this->__('Category-Menu-Top')
			   ),
			   array(
				'value' => 'category-menu-bottom',
				'label' => $this->__('Category-Menu-Bottom')
			   ),
			   array(
				'value' => 'category-page-bottom',
				'label' => $this->__('Category-Page-Bottom')
			   ),
			  )
			 ),
			 array(
			  'label' => $this->__('Product only'),
			  'value' => array(
			   array(
				'value' => 'product-sidebar-right-top',
				'label' => $this->__('Product-Sidebar-Top-Right')
			   ),
			   array(
				'value' => 'product-sidebar-right-bottom',
				'label' => $this->__('Product-Sidebar-Bottom-Right')
			   ),
			   array(
				'value' => 'product-sidebar-left-top',
				'label' => $this->__('Product-Sidebar-Top-Left')
			   ),
			   array(
				'value' => 'product-content-top',
				'label' => $this->__('Product-Content-Top')
			   ),
			   array(
				'value' => 'product-menu-top',
				'label' => $this->__('Product-Menu-Top')
			   ),
			   array(
				'value' => 'product-menu-bottom',
				'label' => $this->__('Product-Menu-Bottom')
			   ),
			   array(
				'value' => 'product-page-bottom',
				'label' => $this->__('Product-Page-Bottom')
			   ),
			  )
			 ),
			 array(
			  'label' => $this->__('Cart & Checkout'),
			  'value' => array(
			   array(
				'value' => 'cart-content-top',
				'label' => $this->__('Cart-Content-Top')
			   ),
			   array(
				'value' => 'checkout-content-top',
				'label' => $this->__('Checkout-Content-Top')
			   ),
			  )
			 ),
			);
		}

		public function getBannerImage($link_url)
		{

			if ($link_url != "") {
				$imgPath = Mage::getBaseUrl('media') . "banners/images/" . $link_url;
			} else {
				$imgPath = Mage::getBaseUrl('media') . "banners/images/image-default.png";
			}
			return $imgPath;
		}

		public function isEnableBanners()
		{
			$storeId = Mage::app()->getStore()->getId();
			return Mage::getStoreConfig(self::XML_PATH_ENABLE_BANNER_SLIDER, $storeId);
		}

		public function includeJs()
		{
			$storeId = Mage::app()->getStore()->getId();
			return (int)Mage::getStoreConfig(self::XML_PATH_INCLUDE_JS, $storeId);
		}

		public function getTransitionSpeed()
		{
			$storeId = Mage::app()->getStore()->getId();
			return (int)Mage::getStoreConfig('bannerads/display_setting/transition_speed', $storeId);
		}

		public function showSliderPager()
		{
			$storeId = Mage::app()->getStore()->getId();
			if (Mage::getStoreConfig('bannerads/display_setting/is_show_paging', $storeId)) {
				return TRUE;
			}
			return FALSE;
		}

		public function generateUrl($string)
		{
			$urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($string));
			$urlKey = strtolower($urlKey);
			$urlKey = trim($urlKey, '-');
			return $urlKey;
		}

		public function getReportData()
		{
			$storeId = Mage::app()->getStore()->getId();
			return (int)Mage::getStoreConfig('bannerads/general/enable_report', $storeId);
		}
		public function getIsoDate($str)
		{
			return Mage::app()->getLocale()->date($str)->getIso();
		}

		public function getBaseImage($product){
			$_product = Mage::getModel('catalog/product')->load($product->getId());
			return Mage::helper('catalog/image')->init($_product, 'image');
		}
	}