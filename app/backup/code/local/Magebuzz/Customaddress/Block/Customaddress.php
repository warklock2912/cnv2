<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Block_Customaddress extends Mage_Core_Block_Template {
	public function _prepareLayout() {
		return parent::_prepareLayout();
  }
    
	public function getCustomaddress() { 
		if (!$this->hasData('customaddress')) {
			$this->setData('customaddress', Mage::registry('customaddress'));
		}
		return $this->getData('customaddress');		
	}
}