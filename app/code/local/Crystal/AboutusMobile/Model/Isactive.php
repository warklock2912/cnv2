<?php

class Crystal_AboutusMobile_Model_Isactive extends Mage_Core_Model_Abstract
{
	public function toOptionArray()
	{
		return array(
			array('value'=>false, 'label'=>Mage::helper('aboutusmobile')->__('No')),
			array('value'=>true, 'label'=>Mage::helper('aboutusmobile')->__('Yes')),
		);
	}
}