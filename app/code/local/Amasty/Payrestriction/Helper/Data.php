<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */ 
class Amasty_Payrestriction_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getAllGroups()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('salesrule')->__('NOT LOGGED IN')));
        } 
        
        return $customerGroups;
    }
    
    public function getAllMethods()
    {
        $hash = array();
        foreach (Mage::getStoreConfig('payment') as $code=>$config){
            if (!empty($config['title'])){
                $label = '';
                if (!empty($config['group'])){
                    $label = ucfirst($config['group']) . ' - ';
                }
                $label .= $config['title'];
                if (!empty($config['allowspecific']) && !empty($config['specificcountry'])){
                    $label .= ' in ' . $config['specificcountry'];    
                }
                $hash[$code] = $label;
                
            }
        }
        asort($hash);
        
        $methods = array();
        foreach ($hash as $code => $label){
            $methods[] = array('value' => $code, 'label' => $label);    
        }
        
        return $methods;      
    }
    
    public function getStatuses()
    {
        return array(
                '1' => Mage::helper('salesrule')->__('Active'),
                '0' => Mage::helper('salesrule')->__('Inactive'),
            );       
    }
    
    public function getAllDays()
    {
        return array(
            array('value'=>'7', 'label' => $this->__('Sunday')),
            array('value'=>'1', 'label' => $this->__('Monday')),
            array('value'=>'2', 'label' => $this->__('Tuesday')),
            array('value'=>'3', 'label' => $this->__('Wednesday')),
            array('value'=>'4', 'label' => $this->__('Thursday')),
            array('value'=>'5', 'label' => $this->__('Friday')),
            array('value'=>'6', 'label' => $this->__('Saturday')),
        );             
    }

    public function getAllTimes()
    {
        $timeArray = array();
        $timeArray[0] = 'Please select...';

        for($i = 0 ; $i < 24 ; $i++){
            for($j = 0; $j < 60 ; $j=$j+15){
                $timeStamp = $i.':'.$j;
                $timeFormat = date ('H:i',strtotime($timeStamp));
                $timeArray[$i * 100 + $j + 1] = $timeFormat;
            }
        }
        return $timeArray;
    }

    public function getAllRules()
    {
        $rules =  array(
            array('value'=>'0', 'label' => $this->__('')));

        $rulesCollection = Mage::getResourceModel('salesrule/rule_collection')->load();

        foreach ($rulesCollection as $rule){
            $rules[] = array('value'=>$rule->getRuleId(), 'label' => $rule->getName());
        }

        return $rules;
    }
}