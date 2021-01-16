<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */
 
class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Grid_Renderer_Run extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{		
		$url = Mage::helper("adminhtml")->getUrl("adminhtml/amseogooglesitemap_sitemap/run", array('id' => $row->getId()));
		return sprintf('<a href="%s">%s</a>', $url, Mage::helper('amseogooglesitemap')->__('Run Now'));
	}
}