<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Model_City extends Mage_Core_Model_Abstract {
	protected $_name = null;
	
	public function _construct() {
		parent::_construct();
		$this->_init('customaddress/city');
	}
	
	public function toOptionArray() {
			$options = $this->_toOptionArray('region_id', 'default_name', array('title' => 'default_name'));
			if (count($options) > 0) {
					array_unshift($options, array(
							'title '=> null,
							'value' => "",
							'label' => Mage::helper('directory')->__('-- Please select --')
					));
			}
			return $options;
	}
	
	public function getName() {
		$name = $this->getData('name');
		if (is_null($name)) {
			$name = $this->getData('default_name');
		}
		return $name;
 	}
}