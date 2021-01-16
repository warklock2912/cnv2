<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoSingleUrl
 */


if (Mage::helper('core')->isModuleEnabled('Amasty_Mostviewed'))
{
    class Mage_Catalog_Block_Product_List_Related_Abstract extends Amasty_Mostviewed_Block_Catalog_Product_List_Related {}
}
else
{
    class Mage_Catalog_Block_Product_List_Related_Abstract extends Mage_Catalog_Block_Product_List_Related {}
}


class Amasty_SeoSingleUrl_Block_Catalog_Product_List_Related extends Mage_Catalog_Block_Product_List_Related_Abstract
{
	/**
	 * @return $this
	 */
	protected function _prepareData()
	{
		parent::_prepareData();
		$this->_itemCollection->addUrlRewrite();

		return $this;
	}
}
