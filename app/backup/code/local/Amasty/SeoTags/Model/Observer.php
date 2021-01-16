<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoTags
 */

class Amasty_SeoTags_Model_Observer
{
	public function initMetaHelper()
	{
		if ($tag = Mage::registry('current_tag') && Mage::helper('amseotoolkit')->isSeoMetaExists()) {
			Mage::helper('ammeta')->addEntityToCollection($tag);
		}
	}

	/**
	 * @param Varien_Event_Observer $observer
	 */
	public function initControllerRouters(Varien_Event_Observer $observer)
	{
		$request = $observer->getFront()->getRequest();
		$helper  = Mage::helper('amseotags');

		$identifier = trim($request->getPathInfo(), '/');
		$parts      = explode('/', $identifier);
		$tagId      = array_pop($parts);

		if ((int) $tagId > 0 && array('tag', 'product', 'list', 'tagId') == $parts && $helper->isTagRewritingEnabled()
		) {
			$default = new Mage_Core_Controller_Varien_Router_Default();
			$observer->getFront()->addRouter('default', $default);

			$tag = Mage::getModel('tag/tag')
				->load($tagId);

			if ($tag->getId() && $url = $tag->getTaggedProductsUrl()) {
				Mage::app()->getFrontController()->getResponse()
					->setRedirect($url, 301)
					->sendResponse();

				exit;
			}
		}
	}
}
