<?php

class Tigren_Member_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     */
    public function indexAction()
    {
        $session = Mage::getSingleton('customer/session');
        if(!$session['pw_member']){
            $this->_redirect('*/login');
        }
        $session->logout();
        $this->loadLayout();
        $this->renderLayout();
    }
}