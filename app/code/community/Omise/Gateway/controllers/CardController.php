<?php
class Omise_Gateway_CardController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function listAction(){
        $this->loadLayout();
        $this->_title(Mage::helper('omise_gateway')->__('My Saved Cards'));
        $this->renderLayout();
    }
}
