<?php

class Tigren_Kpayment_Qrcode_RedirectController extends Mage_Core_Controller_Front_Action
{
    public function indexAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }

//    public function resultAction()
//    {
//        try {
//            $data = $this->getRequest()->getParams();
//            /** @var Tigren_Kpayment_Model_Charge $charge **/
//            $charge = Mage::getModel('kpayment/charge');
//            $createCharge = $charge->createKpaymentCharge($data);
//            return $this->getResponse()->setBody(json_encode($createCharge));
//        } catch (Exception $e) {
//            Mage::getSingleton('core/session')->addError($e->getMessage());
//            return $this->_redirect('checkout/cart');
//        }
//    }
}