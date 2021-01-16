<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Model_Mysql4_City_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	protected $_regionCityNameTable;
	
	public function _construct() {
		parent::_construct();
		$this->_init('customaddress/city');
		$this->_regionCityNameTable = $this->getTable('customaddress/region_city_name');
	}
	
	public function toOptionArray() {		
		$options = $this->_toOptionArray('city_id', 'default_name', array('title' => 'default_name'));
		if (count($options) > 0) {
			array_unshift($options, array(
				'title '=> null,
				'value' => "",
				'label' => Mage::helper('customaddress')->__('-- Please select --')
			));
		}
		return $options;
	}

  protected function _initSelect() {
    parent::_initSelect();
    $locale = Mage::app()->getLocale()->getLocaleCode();

    $this->addBindParam(':city_locale', $locale);
    $this->getSelect()->joinLeft(
      array('cname' => $this->_regionCityNameTable),
      'main_table.city_id = cname.city_id',
      array('name'))
      ->where('cname.locale = ?', $locale);

    return $this;
  }
}