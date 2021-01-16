<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Block_Adminhtml_Rule_Edit_Tab_Test_Renderer_Test extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $hlp =  Mage::helper('amfollowup'); 
        $recipient = Mage::getStoreConfig("amfollowup/test/recipient");
        
        $id = $row->getId();
        return '<button type="button" class="scalable task" onclick="runRuleTesting(this, ' . $id . ')"><span><span><span>' . $hlp->__('Send') .'</span></span></span></button><br/><small><i>to '.$recipient.'</i></small>';

    }
}