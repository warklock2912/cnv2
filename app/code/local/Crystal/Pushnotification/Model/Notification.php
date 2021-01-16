<?php

class Crystal_Pushnotification_Model_Notification extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		$this->_init('pushnotification/notification');
	}

	public function getNotificationImage()
	{
		$imageUrl = '';
		$img = $this->getImage();
			return $img;
	}

}

