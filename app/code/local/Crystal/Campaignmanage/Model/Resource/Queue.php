<?php

class Crystal_Campaignmanage_Model_Resource_Queue extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('campaignmanage/queue','id');
	}
}