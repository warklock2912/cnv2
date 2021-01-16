<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */


class Amasty_SeoGoogleSitemap_Model_Observer
{
	public function generate()
	{
		$profiles = Mage::getModel('amseogooglesitemap/sitemap')->getResourceCollection();
		foreach ($profiles as $profile)
		{
			$profile->generateXml();
		}
	}
}
