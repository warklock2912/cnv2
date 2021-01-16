<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */

class Amasty_SeoGoogleSitemap_Block_Adminhtml_Sitemap extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
    	$this->_controller = 'adminhtml_sitemap';
    	$this->_blockGroup = 'amseogooglesitemap';
    	$this->_headerText = Mage::helper('amseogooglesitemap')->__('Manage Sitemaps');
    	$this->_addButtonLabel = Mage::helper('amseogooglesitemap')->__('Add New Sitemap');
    	parent::__construct();
	}
}