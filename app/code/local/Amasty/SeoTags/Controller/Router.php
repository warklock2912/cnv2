<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoTags
 */

class Amasty_SeoTags_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
	/**
	 * @param Zend_Controller_Request_Http $request
	 *
	 * @return bool
	 */
	public function match(Zend_Controller_Request_Http $request)
	{
		/** @var Amasty_SeoTags_Helper_Data $helper */
		$helper = Mage::helper('amseotags');

		if (! Mage::isInstalled()) {
			Mage::app()->getFrontController()->getResponse()
				->setRedirect(Mage::getUrl('install'))
				->sendResponse();
			exit;
		}

		$identifier = trim($request->getPathInfo(), '/');
		$parts      = explode('/', $identifier);
		if (isset($parts[0]) && $parts[0] == 'tag' && isset($parts[1]) && $helper->isTagRewritingEnabled()) {
			$p  = explode('-', $parts[1]);
			$id = intval(array_pop($p));
			if ($id == 0) {
				return false;
			}

			$request->setRouteName('tag')
				->setModuleName('tag')
				->setControllerName('product')
				->setActionName('list')
				->setParam('tagId', $id)
				->setAlias(
					Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
					'tags'
				);

			return true;
		}

		return false;
	}
}