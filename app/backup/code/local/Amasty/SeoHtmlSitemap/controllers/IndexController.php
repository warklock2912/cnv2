<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoHtmlSitemap
 */
class Amasty_SeoHtmlSitemap_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->loadLayout();

		/** @var $helper Amasty_SeoHtmlSitemap_Helper_Data */
		$helper = Mage::helper('amseohtmlsitemap');

		//Set template
		$this->getLayout()->getBlock('root')->setTemplate($helper->getLayoutTemplate());

		//Set page title
		$pageTitle = trim((string) Mage::getStoreConfig($helper::CONFIG_PAGE_TITLE_PATH));
		if (! empty($pageTitle)) {
			$this->getLayout()->getBlock('head')->setTitle($pageTitle);
		}

		//set meta description
		$metaDescription = trim((string) Mage::getStoreConfig($helper::CONFIG_META_DESCRIPTION_PATH));
		if (! empty($metaDescription)) {
			$this->getLayout()->getBlock('head')->setDescription($metaDescription);
		}

		$this->renderLayout();
	}
}