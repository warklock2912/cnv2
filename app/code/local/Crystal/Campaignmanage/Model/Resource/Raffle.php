<?php

class Crystal_Campaignmanage_Model_Resource_Raffle extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('campaignmanage/raffle','id');
	}
}