<?php

    class Crystal_AboutusMobile_Model_Environment extends Mage_Core_Model_Abstract
    {
        public function toOptionArray()
        {
            return array(
                array('value' => 'dev', 'label' => Mage::helper('aboutusmobile')->__('Development')),
                array('value' => 'live', 'label' => Mage::helper('aboutusmobile')->__('Live')),
            );
        }
    }