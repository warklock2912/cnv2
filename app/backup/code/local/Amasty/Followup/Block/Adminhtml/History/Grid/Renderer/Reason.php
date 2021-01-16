<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Block_Adminhtml_History_Grid_Renderer_Reason extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $hlp =  Mage::helper('amfollowup'); 
        
        $types = $hlp->getCancelReasons();
        
        return isset($types[$row->getReason()]) ? $types[$row->getReason()] : '';
    }
}