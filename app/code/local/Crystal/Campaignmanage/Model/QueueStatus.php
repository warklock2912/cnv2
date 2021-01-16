<?php


class Crystal_Campaignmanage_Model_QueueStatus extends Varien_Object {
	const STATUS_IN_QUEUE = 1;
	const STATUS_CURRENT = 2;
	const STATUS_DONE = 3;

	static public function getOptionArray() {
		return array(
			self::STATUS_IN_QUEUE => Mage::helper('campaignmanage')->__('In Queue'),
			self::STATUS_CURRENT => Mage::helper('campaignmanage')->__('Current'),
			self::STATUS_DONE => Mage::helper('campaignmanage')->__('Done'),
		);
	}
}