<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Block_Adminhtml_Queue_Grid_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getOrderId()));
                
        return "<a href='" . $url . "'>" . $row->getIncrementId() . "</a>";
    }
}