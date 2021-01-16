<?php

class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $html = '';
        $ruffleId = $row->getData('ruffle_id');
        if(!$row->getData('customer_card_token')){
            return $html;
        }
        $ruffle = Mage::getModel('ruffle/ruffle')->load($ruffleId);
        if(!$ruffle->getData('use_creditcard')){
            return $html;
        }
        $orderId  = $row->getData('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        if($order->getId()){
            if($order->canInvoice()){
                $html = '<a style="padding-right: 3px;" href="javascript:void(0)" disabled><button type="button" class="scalable disabled" title="Create Order">Create order</button></a>';
//                $html .= '<a href="'.$this->getUrl('*/OrderRuffle/capture', array('order_id' => $row->getData('order_id'), 'ruffle_joiner' => $row->getJoinerId())).'"><button type="button" class="scalable" title="Capture">Capture</button></a>';
            }else{
                $html = '<a style="padding-right: 3px;"  href="javascript:void(0)" disabled><button type="button" class="scalable disabled" title="Create Order">Create order</button></a>';
//                $html .= '<a href="javascript:void(0)" disabled><button type="button" class="scalable disabled" title="Capture">Capture</button></a>';
            }
        }else{
            $html = '<a style="padding-right: 3px;" href="'.$this->getUrl('*/OrderRuffle/createOrder', array('ruffle_joiner' => $row->getJoinerId(), 'customer_id' => $row->getCustomerId(), 'product_id' => $row->getProductId(), 'option_id' => urlencode($row->getData('product_options')))).'"><button type="button" class="scalable" title="Create Order">Create order</button></a>';
//            $html .= '<a href="javascript:void(0)" disabled><button type="button" class="scalable disabled" title="Capture">Capture</button></a>';
        }

        return $html;
    }

}
