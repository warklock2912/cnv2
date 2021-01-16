<?php

class Tigren_Member_LoginController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();

    }

    public function loginAction()
    {
        $post = $this->getRequest()->getPost('login');
        $session = Mage::getSingleton('customer/session');
        $configPw = Mage::getStoreConfig('mgfapisetting/adminchangevip/password');
        if($post['password'] && $post['password'] == $configPw)
        {

            $session['pw_member'] = $post['password'];
            $session->setData($session['pw_member']);
            $url = Mage::getUrl('member');
            $url= rtrim($url,'/');
            $redirect = Mage::app()->getFrontController()->getResponse()->setRedirect($url);
            return $redirect;
        }
        elseif($post['password'] != $configPw)
        {
            Mage::getSingleton('customer/session')->addError('Incorrect Password');
            $this->_redirect('member');
            return;
        }
        else{
            return $this->_redirect('member');
        }
    }
}