<?php

class Crystal_AboutusMobile_Model_Broadcastenvironment extends Mage_Core_Model_Abstract
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'messageBroadcast_develop', 'label' => Mage::helper('aboutusmobile')->__('Development')),
            array('value' => 'messageBroadcast_staging', 'label' => Mage::helper('aboutusmobile')->__('Staging')),
            array('value' => 'messageBroadcast', 'label' => Mage::helper('aboutusmobile')->__('Production')),
        );
    }
}
