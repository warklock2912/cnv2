<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */
class Amasty_Payrestriction_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ruleTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ampayrestriction')->__('Rule Configuration'));
    }

    protected function _beforeToHtml()
    {
        $tabs = array(
            'general'    => 'Restrictions',
            'stores'     => 'Stores & Customer Groups',
            'daystime'   => 'Days & Time',
            'conditions' => 'Conditions',
            'apply'      => 'Coupons',
        );
        
        foreach ($tabs as $code => $label){
            $label = Mage::helper('ampayrestriction')->__($label);
            $content = $this->getLayout()->createBlock('ampayrestriction/adminhtml_rule_edit_tab_' . $code)
                ->setTitle($label)
                ->toHtml();
                
            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content,
            ));
        }
        
        return parent::_beforeToHtml();
    }
}