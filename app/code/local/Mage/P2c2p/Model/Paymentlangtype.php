<?php

class Mage_P2c2p_Model_Paymentlangtype extends Mage_Payment_Model_Method_Abstract
{
	public function toOptionArray()
	{
		return array(
			array('value' => 'en', 'label' => 'English'),
			array('value' => 'ja', 'label' => 'Japanese'),
			array('value' => 'th', 'label' => 'Thailand'),
			array('value' => 'id', 'label' => 'Bahasa Indonesia'),
			array('value' => 'my', 'label' => 'Burmese'),
			array('value' => 'zh', 'label' => 'Simplified Chinese'),
			);
	}
}
