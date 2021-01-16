<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */ 
class Amasty_Followup_Block_Adminhtml_Rule_Edit_Tab_Schedule extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_salerRuleCollection;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amfollowup/schedule.phtml');
    }
    
    
    function getNumberOptions($number){
        $ret = array('<option value="">-</option>');
        for($index = 1; $index <= $number; $index++){
            $ret[] = '<option value="' . $index . '" >' . $index . '</option>';
        }
        return implode('', $ret);
    }
    
    function getScheduleCollection(){
        $scheduleCollection = Mage::getModel('amfollowup/schedule')->getCollection();
        $scheduleCollection->addFilter('rule_id', $this->getModel()->getId());

        return $scheduleCollection;
    }
    
    function getEmailTemplatesOptions(){
        return Mage::helper('amfollowup')->getEmailTemplatesOptions($this->getModel()->getStartEventType());
    }
    
    function getCouponTypesOptions(){
        $ret = array();
        $types = Mage::helper('amfollowup')->getCouponTypes();
        
        foreach($types as $val => $lb){
            $ret[] = array(
                'value' => $val,
                'label' => $lb
            );
        }
        
        return $ret;
    }
    
    function getDefaultTemplateId(){
        return null;
        $template = Mage::getModel('adminhtml/email_template')->load(Amasty_Followup_Model_Schedule::DEFAULT_TEMPLATE_CODE, 'template_code');
        return $template->getId() ? $template->getId() : NULL;

    }

    function getRules(){
        if (!$this->_salerRuleCollection)
            $this->_salerRuleCollection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFilter('use_auto_generation', 1);

        return $this->_salerRuleCollection;
    }

    function getRulesCount(){
        return $this->getRules()->getSize();
    }
    
    function useAutoGenerationAvailable()
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('salesrule/rule');
        $table = $readConnection->describeTable($tableName);
        return isset($table['use_auto_generation']);
    }
    
    
}