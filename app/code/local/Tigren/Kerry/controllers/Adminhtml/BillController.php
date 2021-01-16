<?php

/**
 * Class Tigren_Kerry_Adminhtml_RequestController
 */
class Tigren_Kerry_Adminhtml_BillController extends Mage_Adminhtml_Controller_Action
{


    /**
     * @throws Mage_Core_Exception
     * @throws Zend_Barcode_Exception
     * @throws Zend_Pdf_Exception
     */
    public function printAction()
    {
        /** @var Tigren_Kerry_Helper_Data $kerryHelper **/
        $kerryHelper = Mage::helper('kerry');
        /** @var Mage_Sales_Model_Order_Shipment $shipment **/
        $shipment = Mage::getModel('sales/order_shipment')->load($this->getRequest()->getParam('shipment_id'));
        $pdf = $kerryHelper->getAwbPdf($shipment);
        $this->_prepareDownloadResponse('Kerry_' . time() . '.pdf', $pdf->render());
    }

    /**
     * @return bool
     */
    protected function _isAllowed(){
        return true;
    }
}