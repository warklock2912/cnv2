<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoTags
 */

class Amasty_SeoTags_Block_Adminhtml_Config_Form extends Mage_Adminhtml_Block_System_Config_Form
{
	/**
	 * @return array
	 */
	protected function _getAdditionalElementTypes()
	{
		$defaultElementTypes = parent::_getAdditionalElementTypes();
		$newElementTypes = array(
			'amseotag_export' => Mage::getConfig()->getBlockClassName('amseotags/adminhtml_config_form_field_export'),
			'amseotag_import' => Mage::getConfig()->getBlockClassName('amseotags/adminhtml_config_form_field_import')
		);

		return $defaultElementTypes + $newElementTypes;
	}
}
