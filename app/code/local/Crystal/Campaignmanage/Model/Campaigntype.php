<?php


class Crystal_Campaignmanage_Model_Campaigntype extends Varien_Object {
	const TYPE_STORE_QUEUE = 1;
	const TYPE_STORE_SHUFFLE = 2;
	const TYPE_STORE_RAFFLE = 3;

	static public function getOptionArray() {
		return array(
			self::TYPE_STORE_QUEUE => Mage::helper('campaignmanage')->__('Store Queue'),
			self::TYPE_STORE_SHUFFLE => Mage::helper('campaignmanage')->__('Store Shuffle'),
			self::TYPE_STORE_RAFFLE => Mage::helper('campaignmanage')->__('Store Raffle'),
		);
	}
}