<?php

class Crystal_FeaturePage_Model_System_Config_Source_Banner_Option extends Varien_Object
{
    
    public function toOptionArray()
    {
        $collection = Mage::getModel('bannerads/bannerads')->getCollection();

        $options = array();

        foreach($collection as $item){
            $options[$item->getData('block_id')] = $item->getData('block_title');
        }

        return $options;
    }
}