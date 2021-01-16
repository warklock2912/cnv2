<?php
class Omise_Gatewat_Model_Resource_Token_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{

	protected function _construce(){
		$this->_init('omise_gateway/token');
	}
}