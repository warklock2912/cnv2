<?php

class Crystal_BlockSlide_Model_Resource_Blockslide_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('blockslide/blockslide');
	}
}