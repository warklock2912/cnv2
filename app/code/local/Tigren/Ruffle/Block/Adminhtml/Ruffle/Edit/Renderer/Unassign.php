<?php

class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Renderer_Unassign extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '';
        $html = '<a class="unassign-button" style="padding-right: 3px;"  href="'.$this->getUrl('*/Ruffle/unassignWinner', array('ruffle_joiner' => $row->getJoinerId(), 'customer_id' => $row->getCustomerId(), 'product_id' => $row->getProductId(), 'option_id' => urlencode($row->getData('product_options')))).'"><button type="button" href="'.$this->getUrl('*/Ruffle/unassignWinner', array('ruffle_joiner' => $row->getJoinerId(), 'customer_id' => $row->getCustomerId(), 'product_id' => $row->getProductId(), 'option_id' => urlencode($row->getData('product_options')))).'" style="background:red" class="unassign-button"  title="Create Order">Unassign</button></a>
';
        return $html;
    }
}