<?php

class Crystal_ConfirmOrder_Model_Resource_Confirm extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('confirmorder/confirm','id');
	}
}