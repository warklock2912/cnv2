<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ruleTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amfollowup')->__('Rule Configuration'));
    }

    protected function _beforeToHtml()
    {
        $tabs = array(
            'general' => 'General',
        );
        
        if ($this->getModel()->getId()) {
            
            $tabs = array(
                'general' => 'General',
                'stores' => 'Stores & Customer Groups',
                'sender' => 'Sender Details',
                'analytics' => 'Google Analytics',
                'schedule' => 'Schedule'
            );
            
            if (Mage::getConfig()->getNode('modules/Amasty_Segments/active') == 'true')
            {
                $tabs['segments'] = 'Segments';
            }
            
            $recipient = Mage::getStoreConfig("amfollowup/test/recipient");
            
            $recipientValidated = !empty($recipient) && Zend_Validate::is($recipient, 'EmailAddress');
                
            if ($this->getModel()->isOrderRelated()){
                $tabs['order_condition'] = 'Condition';
                
                if ($recipientValidated) {
                    $tabs['test_order'] = 'Test';
                }
            } else {
                if ($recipientValidated) {
                    $tabs['test_customer'] = 'Test';
                }
            }
            
            if (!$recipientValidated){
                $tabs['test_settings'] = 'Test';
            }
        }
        
        foreach ($tabs as $code => $label){
            $label = Mage::helper('amfollowup')->__($label);
            
            $block = $this->getLayout()->createBlock('amfollowup/adminhtml_rule_edit_tab_' . $code);
            $block->setModel($this->getModel());
            
            $content = $block
                ->setTitle($label)
                ->toHtml();
            
            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content,
//                'active' => $code == 'schedule' ? true : false
            ));
        
        }
        
        return parent::_beforeToHtml();
    }
}