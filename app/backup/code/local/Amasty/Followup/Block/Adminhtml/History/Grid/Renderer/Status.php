<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Block_Adminhtml_History_Grid_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $hlp =  Mage::helper('amfollowup'); 
        
        $types = $hlp->getHistoryStatusSent();
        
        return isset($types[$row->getStatus()]) ? $types[$row->getStatus()] : 'undefined';
    }
}