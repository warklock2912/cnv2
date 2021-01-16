<?php
/*
* Copyright (c) 2013 www.magebuzz.com 
*/
class Magebuzz_Imagehome_Block_Imagehome extends Mage_Core_Block_Template {
	public function _prepareLayout() {
		return parent::_prepareLayout();
  }
    
	public function getImagehome() { 
		if (!$this->hasData('imagehome')) {
			$this->setData('imagehome', Mage::registry('imagehome'));
		}
		return $this->getData('imagehome');		
	}
}