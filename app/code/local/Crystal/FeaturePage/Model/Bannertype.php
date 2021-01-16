<?php

    class Crystal_FeaturePage_Model_Bannertype extends Mage_Core_Model_Abstract
    {
        public function toOptionArray()
        {
            return array(
                array('value' => 'small', 'label' => Mage::helper('aboutusmobile')->__('Small')),
                array('value' => 'medium', 'label' => Mage::helper('aboutusmobile')->__('Medium')),
                array('value' => 'large', 'label' => Mage::helper('aboutusmobile')->__('Large')),
            );
        }
    }
