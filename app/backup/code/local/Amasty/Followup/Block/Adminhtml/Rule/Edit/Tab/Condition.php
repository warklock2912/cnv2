<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Block_Adminhtml_Rule_Edit_Tab_Condition extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amfollowup/condition.phtml');
    }
    
    function getEventTypes(){
        return Mage::helper("amfollowup")->getEventTypes();
    }
    
    function getOrderStatuses(){
        return Mage::helper("amfollowup")->getOrderStatuses();
    }
    
    function getCustomerGroups(){
        $ret = array();
        $custGroups = Mage::helper("amfollowup")->getCustomerGroups();
        
        foreach($custGroups as $group){
            $ret[$group['value']] = $group['label'];
        }
        return $ret;
        
    }
}