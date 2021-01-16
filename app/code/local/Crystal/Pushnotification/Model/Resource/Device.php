<?php
class Crystal_Pushnotification_Model_Resource_Device extends  Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('pushnotification/device','id');
	}
}