<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $html = '';
        $ruffleId = $row->getData('raffle_id');
        $ruffle = Mage::getModel('campaignmanage/campaignonline')->load($ruffleId);

        if ($row->getData('cc_card_token') && $ruffle->getData('is_card_payment')) {
            $orderId = $row->getData('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                if ($order->canInvoice()) {
                    $html = '<a style="padding-right: 3px;" href="' . $this->getUrl('adminhtml/sales_order/view',
                            array('order_id' => $row->getOrderId())) . '">Order detail</a>';
                } else {
                    $html = '<a style="padding-right: 3px;" href="' . $this->getUrl('adminhtml/sales_order/view',
                            array('order_id' => $row->getOrderId())) . '">Order detail</a>';
                }
            } else {
                $html = '<a style="padding-right: 3px;" href="' . $this->getUrl('*/Order/createOrder',
                        array('joiner_id' => $row->getId())) . '"><button type="button" class="scalable" title="Create Order">Create order</button></a>';
            }
        }

        $url = '\'' . $this->getUrl('*/*/unassign',
                array('joiner_id' => $row->getId())) . '\'';
        $unassignScript = 'unassign(' . $url . ')';
        if (!$row->getOrderId()) {
            $html .= '<a style="padding-right: 3px; margin-left: 10px" onclick="' . $unassignScript . '"><button type="button" style="background-color: red; border-color: red;" class="scalable" title="Unassign Winner"><strong>Unassign</strong></button></a>';
        } else {
            $html .= '<a style="padding-right: 3px; margin-left: 10px"><button type="button"  disabled class="scalable" title="Unassign Winner"><strong>Unassign</strong></button></a>';
        }
        return $html;
    }

}
