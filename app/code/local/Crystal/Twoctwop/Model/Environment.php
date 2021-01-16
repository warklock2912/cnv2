<?php

    class Crystal_Twoctwop_Model_Environment extends Mage_Core_Model_Abstract
    {
        public function toOptionArray()
        {
            return array(
                array('value' => 'test', 'label' => Mage::helper('aboutusmobile')->__('Test')),
                array('value' => 'live', 'label' => Mage::helper('aboutusmobile')->__('Live')),
            );
        }
    }
