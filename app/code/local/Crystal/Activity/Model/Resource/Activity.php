<?php
class Crystal_Activity_Model_Resource_Activity extends  Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('activity/activity','id');
	}

}