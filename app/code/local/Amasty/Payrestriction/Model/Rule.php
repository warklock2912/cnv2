<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */ 
class Amasty_Payrestriction_Model_Rule extends Mage_Rule_Model_Rule
{
    const ALL_ORDERS = 0;
    const BACKORDERS_ONLY = 1;
    const NON_BACKORDERS = 2;

    public function _construct()
    {
        parent::_construct();
        $this->_init('ampayrestriction/rule');
    }
    
    public function getConditionsInstance()
    {
        return Mage::getModel('ampayrestriction/rule_condition_combine');
    }
    
    public function restrict($method)
    {
        return (false !== strpos($this->getMethods(), ',' . $method->getCode() . ','));
    }
    
    public function massChangeStatus($ids, $status)
    {
        return $this->getResource()->massChangeStatus($ids, $status);
    }
    
    protected function _afterSave()
    {
        //Saving attributes used in rule
        $ruleProductAttributes = array_merge(
            $this->_getUsedAttributes($this->getConditionsSerialized()),
            $this->_getUsedAttributes($this->getActionsSerialized())
        );
        if (count($ruleProductAttributes)) {
            $this->getResource()->saveAttributes($this->getId(), $ruleProductAttributes);
        } 
        
        return parent::_afterSave(); 
    } 
    
    /**
     * Return all product attributes used on serialized action or condition
     *
     * @param string $serializedString
     * @return array
     */
    protected function _getUsedAttributes($serializedString)
    {
        $result = array();
        
        $pattern = '~s:32:"salesrule/rule_condition_product";s:9:"attribute";s:\d+:"(.*?)"~s';
        $matches = array();
        if (preg_match_all($pattern, $serializedString, $matches)){
            foreach ($matches[1] as $attributeCode) {
                $result[] = $attributeCode;
            }
        }
        
        return $result;
    }


    protected function _setWebsiteIds(){
        $websites = array();

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $websites[$website->getId()] = $website->getId();
                }
            }
        }

        $this->setOrigData('website_ids', $websites);
    }

    protected function _beforeSave(){
        $this->_setWebsiteIds();
        return parent::_beforeSave();
    }

    protected function _beforeDelete(){
        $this->_setWebsiteIds();
        return parent::_beforeDelete();
    }
}