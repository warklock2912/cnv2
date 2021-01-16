<?php

class Crystal_NewsNotification_Model_Resource_Newsnotification extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('newsnotification/newsnotification','id');
	}
}