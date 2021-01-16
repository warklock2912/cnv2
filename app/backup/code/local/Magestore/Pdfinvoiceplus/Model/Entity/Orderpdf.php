<?php

class Magestore_Pdfinvoiceplus_Model_Entity_Orderpdf extends Magestore_Pdfinvoiceplus_Model_Entity_Ordergenerator {

    public $orderId;
    public $templateId;
    public $order = null;

    public function getTheOrder() {
        if (is_null($this->order)) {
            $order = Mage::getModel('sales/order')->load($this->orderId);
            return $order;
        }
        return $this->order;
    }

    public function setTheOrder($order) {
        $this->order = $order;
    }

    public function getThePdf($orderId, $templateId = NULL) {
        $this->templateId = $templateId;
        $this->orderId = $orderId;
        $this->setVars(Mage::helper('pdfinvoiceplus')->processAllVars($this->collectVars()));
        /* Change by Zeus 04/12 */
        $html = NULL;
        return $this->getPdf($html);
        /* end change */
    }

    /* Packing Slip */
    // MS-1006
    public function getThePackingSlipPdf($orderId, $templateId = NULL, $order) {
        $this->templateId = $templateId;
        $this->orderId = $orderId;

        $this->setVars(Mage::helper('pdfinvoiceplus')->processAllVars($this->collectVars()));

        $shipping = $order->getShippingAddress();

        // page
        $html = '<div style="width:100%; height:100%: font-size:10px; text-align:left; line-height:1.4em; padding-left:10px; padding-right:10px;">';

        // head
        $html .= '
            <div style="clear:both; width:100%;">
                <table style="width:100%; font-size:10px; line-height:1.6em;">
                    <tr>
                        <td colspan="2">Order # '. $order->getIncrementId() .'</td>
                    </tr>
                    <tr>
                        <td style="width:50%;">'. $shipping->getName() .'</td>
                        <td style="width:50%;">T:'. $shipping->getTelephone() .'</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            '. $shipping->getStreetFull() .' <br>
                            '. $shipping->getSubdistrict() .', '. $shipping->getCity() .', '. $shipping->getRegion() .', '. $shipping->getPostcode() .'
                        </td>
                    </tr>
                </table>
            </div>
        ';


        // items
        $html .= '
            <div style="clear:both; width:100%; margin-top:30px;">
                <table style="width:100%; font-size:10px; line-height:1.6em;">
        ';
        foreach($order->getAllVisibleItems() as $_item){

            $html .= '
                <tr>
                    <td style="width:82%; vertical-align:top; padding-right:2px;">'. $_item->getName() .'</td>';
            $html .= '
                    <td style="width:18%; text-align:right; vertical-align:top;">Qty: '. (int)$_item->getQtyInvoiced() .'</td>
                </tr>';

            $html .='
                <tr>';
                if ($_item->getProductOptions()):
                    $option = $_item->getProductOptions();
                    $html .= '<td style="width:100%; vertical-align:top; padding-right:2px;">'.$option['attributes_info'][0]['label'] .' : '.$option['attributes_info'][0]['value'];
                endif;    
            $html .='
                </tr>
            ';

        }
        $html .= '
                </table>
            </div>
        ';

        // footer
        $price = $order->getGrandTotal() - $order->getShippingAmount();
        $html .= '
            <div style="clear:both; width:100%;">
                <table style="width:100%; font-size:10px; line-height:1.6em;">
                    <tr>
                        <td style="width:30%; text-align:right;">'. number_format($order->getGrandTotal(), 2, '.', '') .'</td>
                        <td style="width:30%; text-align:right;">'. number_format($order->getShippingAmount(), 2, '.', '') .'</td>
                        <td style="width:40%; text-align:right;">'. number_format($price, 2, '.', '') .'</td>
                    </tr>
                </table>
            </div>
        ';

        // page
        $html .= '</div>';


        return $this->getPackingSlipPdf($html);
    }


    public function collectVars() {
        $vars = Mage::getModel('pdfinvoiceplus/entity_additional_info')
            ->setSource($this->getTheOrder())
            ->setOrder($this->getTheOrder())
            ->getTheInfoMergedVariables();
        return $vars;
    }

}
