<?php
class Crystal_Pushnotification_Model_Resource_Notification extends  Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('pushnotification/notification','notification_id');
	}
}