<?php

class Mage_P2c2predirect_Model_Paymentmodetype extends Mage_Payment_Model_Method_Abstract
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => 'Test Mode'),
            array('value' => 0, 'label' => 'Live Mode'),
        );
    }
}
