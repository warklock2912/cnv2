<?php
class Magebuzz_Customaddress_Model_Resource_Region extends Mage_Core_Model_Resource_Db_Abstract {
  protected function _construct() {
		$this->_init('customaddress/region', 'region_id');
	}
}