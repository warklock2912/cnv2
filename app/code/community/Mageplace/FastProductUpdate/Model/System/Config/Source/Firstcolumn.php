<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Mageplace_FastProductUpdate
 */

class Mageplace_FastProductUpdate_Model_System_Config_Source_Firstcolumn
{
	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => Mageplace_FastProductUpdate_Helper_Const::COLUMN_SKU, 'label' => Mage::helper('mpfastproductupdate')->__('Product SKU')),
			array('value' => Mageplace_FastProductUpdate_Helper_Const::COLUMN_ID, 'label' => Mage::helper('mpfastproductupdate')->__('Product ID')),
		);
	}
}