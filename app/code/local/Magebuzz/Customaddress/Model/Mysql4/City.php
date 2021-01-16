<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Model_Mysql4_City extends Mage_Core_Model_Mysql4_Abstract {
	protected $_cityNameTable;
	
	public function _construct() {
		$this->_init('customaddress/city', 'city_id');
		$this->_cityNameTable = $this->getTable('customaddress/region_city_name');
	}
	
	protected function _getLoadSelect($field, $value, $object) {
		$select  = parent::_getLoadSelect($field, $value, $object);
		$adapter = $this->_getReadAdapter();

		$locale       = Mage::app()->getLocale()->getLocaleCode();
		$systemLocale = Mage::app()->getDistroLocaleCode();

		$cityField = $adapter->quoteIdentifier($this->getMainTable() . '.' . $this->getIdFieldName());

		$condition = $adapter->quoteInto('lrn.locale = ?', $locale);
		$select->joinLeft(
			array('lrn' => $this->_cityNameTable),
			"{$cityField} = lrn.city_id AND {$condition}",
			array());

		if ($locale != $systemLocale) {
			$nameExpr  = $adapter->getCheckSql('lrn.city_id is null', 'srn.name', 'lrn.name');
			$condition = $adapter->quoteInto('srn.locale = ?', $systemLocale);
			$select->joinLeft(
				array('srn' => $this->_cityNameTable),
				"{$cityField} = srn.city_id AND {$condition}",
				array('name' => $nameExpr));
		} else {
			$select->columns(array('name'), 'lrn');
		}

		return $select;
	}
}