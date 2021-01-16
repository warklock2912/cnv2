<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Model_Mysql4_Subdistrict extends Mage_Core_Model_Mysql4_Abstract {
	protected $_subdistrictNameTable;
	
	public function _construct() {
		$this->_init('customaddress/subdistrict', 'subdistrict_id');
		$this->_subdistrictNameTable = $this->getTable('customaddress/city_subdistrict_name');
	}
	
	protected function _getLoadSelect($field, $value, $object) {
		$select  = parent::_getLoadSelect($field, $value, $object);
		$adapter = $this->_getReadAdapter();

		$locale       = Mage::app()->getLocale()->getLocaleCode();
		$systemLocale = Mage::app()->getDistroLocaleCode();

		$subdistrictField = $adapter->quoteIdentifier($this->getMainTable() . '.' . $this->getIdFieldName());

		$condition = $adapter->quoteInto('lrn.locale = ?', $locale);
		$select->joinLeft(
			array('lrn' => $this->_subdistrictNameTable),
			"{$subdistrictField} = lrn.subdistrict_id AND {$condition}",
			array());

		if ($locale != $systemLocale) {
			$nameExpr  = $adapter->getCheckSql('lrn.subdistrict_id is null', 'srn.name', 'lrn.name');
			$condition = $adapter->quoteInto('srn.locale = ?', $systemLocale);
			$select->joinLeft(
				array('srn' => $this->_subdistrictNameTable),
				"{$subdistrictField} = srn.subdistrict_id AND {$condition}",
				array('name' => $nameExpr));
		} else {
			$select->columns(array('name'), 'lrn');
		}

		return $select;
	}
}