<?php

    class Crystal_FeaturePage_Model_Blocktype extends Mage_Core_Model_Abstract
    {
        public function toOptionArray()
        {
            return array(
                array('value' => 'banner', 'label' => Mage::helper('aboutusmobile')->__('Banner')),
                array('value' => 'upcoming', 'label' => Mage::helper('aboutusmobile')->__('Upcoming')),
                array('value' => 'category', 'label' => Mage::helper('aboutusmobile')->__('Category')),
                array('value' => 'highlight_brand', 'label' => Mage::helper('aboutusmobile')->__('Highlight Brand')),
            );
        }
    }
