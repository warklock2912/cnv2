<?php
class Crystal_Pushnotification_Model_Type extends Varien_Object {
	const TYPE_BLOG = 1;
	const TYPE_RAFFLE = 2;
	const TYPE_MESSAGE = 3;

	static public function getOptionArray() {
		return array(
			self::TYPE_BLOG => Mage::helper('pushnotification')->__('Blog'),
			self::TYPE_RAFFLE => Mage::helper('pushnotification')->__('Raffle'),
			self::TYPE_MESSAGE => Mage::helper('pushnotification')->__('Message'),
		);
	}
}