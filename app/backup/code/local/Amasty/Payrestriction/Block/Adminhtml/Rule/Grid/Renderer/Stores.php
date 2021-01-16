<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */ 
class Amasty_Payrestriction_Block_Adminhtml_Rule_Grid_Renderer_Stores extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        /* @var $hlp Amasty_Payrestriction_Helper_Data */
        $hlp = Mage::helper('ampayrestriction');
        
        $stores = $row->getData('stores');
        if (!$stores) {
            return $hlp->__('Restricts in All');
        }
        
        $html = '';
        $data = Mage::getSingleton('adminhtml/system_store')->getStoresStructure(false, explode(',', $stores));
        foreach ($data as $website) {
            $html .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $html .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $html .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }
        return $html;
    }
}