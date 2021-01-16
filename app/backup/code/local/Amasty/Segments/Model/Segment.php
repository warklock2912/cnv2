<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */ 
class Amasty_Segments_Model_Segment extends Mage_Rule_Model_Rule
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('amsegments/segment');
    }
    
    public function getActionsInstance()
    {
        return Mage::getModel('rule/action_collection');
    }
    
    public function getConditionsInstance()
    {
        return Mage::getModel('amsegments/segment_condition_combine');
    }
    
    function process(){
        $this->getResource()->clearIndex($this);
        $this->getConditions()->process($this->getWebsiteIds());
        $this->setGeneratedAt(date('Y-m-d H:i:s'));
        $this->save();
    }

    function getWebsiteIds()
    {
        $ids = $this->getData('website_ids');

        if ($ids == '0'){
            $ids = array_keys(Mage::app()->getWebsites());
        }
        else if (!is_array($ids)){
            $ids = explode(",", $ids);
        }

        return $ids;
    }
}
?>