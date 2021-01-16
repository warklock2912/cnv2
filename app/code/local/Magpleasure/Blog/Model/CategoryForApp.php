<?php
class Magpleasure_Blog_Model_CategoryForApp extends Varien_Object {
	const TYPE_CATEGORY = 1;
	const TYPE_BRAND = 2;

	static public function getOptionArray() {
		return array(
			self::TYPE_CATEGORY => Mage::helper('mpblog')->__('Category'),
			self::TYPE_BRAND => Mage::helper('mpblog')->__('Brand'),
		);
	}
}