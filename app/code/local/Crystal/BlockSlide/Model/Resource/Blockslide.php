<?php

class Crystal_BlockSlide_Model_Resource_Blockslide extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('blockslide/blockslide','id');
	}
}