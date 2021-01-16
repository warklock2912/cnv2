<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */
class Amasty_Segments_Block_Adminhtml_Segment_Edit_Tab_Customers_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $hlp =  Mage::helper('amsegments'); 
        $id = $row->getCustomerId();
        return $id ? '<a target="_blank" href="' . Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $id)) . '">' . $hlp->__("View") . '</a>' : $hlp->__("Guest");

    }
}