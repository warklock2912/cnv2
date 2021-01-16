<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */
 
class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap_Grid_Renderer_File extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		if (file_exists(Mage::getBaseDir() . DS . $row->getFolderName())) {
			$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $row->getFolderName();	
			return sprintf('<a href="%s" target="_blank">%s</a>', $url, $url);
		} 
		return Mage::helper('amseogooglesitemap')->__('-');
	}
}