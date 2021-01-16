<?php

class Crystal_AppUpdate_Model_Status extends Mage_Core_Model_Abstract
{
    public function toOptionArray()
    {
        return array(
            array('value' => false, 'label' => Mage::helper('appupdate')->__('Disable')),
            array('value' => true, 'label' => Mage::helper('appupdate')->__('Enable')),
        );
    }
}