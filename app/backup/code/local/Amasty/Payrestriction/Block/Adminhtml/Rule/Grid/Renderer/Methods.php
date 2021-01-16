<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */ 
class Amasty_Payrestriction_Block_Adminhtml_Rule_Grid_Renderer_Methods extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        /* @var $hlp Amasty_Shiprestriction_Helper_Data */
        $hlp = Mage::helper('ampayrestriction'); 
        
        $v = $row->getData('methods');
        if (!$v) {
            return $hlp->__('Allows All');
        }
        $v = explode(',', $v);
        
        $html = '';
        foreach($hlp->getAllMethods() as $row)
        {
            if (in_array($row['value'], $v)){
                $html .= $row['label'] . "<br />";
            }
        }
        return $html;
    }
}