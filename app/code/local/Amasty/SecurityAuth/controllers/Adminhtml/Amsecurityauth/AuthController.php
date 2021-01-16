<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


class Amasty_SecurityAuth_Adminhtml_Amsecurityauth_AuthController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

    public function ajaxVerifyCodeAction()
    {
        $request = Mage::app()->getRequest();
        $userId = $request->getPost('user_id');
        $userAuth = Mage::getModel('amsecurityauth/auth')->load($userId);

        $secret = $request->getPost('secret');
        $code = $request->getPost('code', null);

        $valid = $userAuth->verifyCode($secret, $code, Mage::getStoreConfig('amsecurityauth/general/discrepancy'));

        $hlp = Mage::helper('amsecurityauth');
        if ($valid) {
            $message = $hlp->__('Valid!');
            $color = '008800';
            $additional = '';
        } else {
            $message = $hlp->__('Invalid!');
            $color = 'aa1717';
            $additional = $hlp->__('<br>Please check the <a href="https://amasty.com/docs/doku.php?id=magento_1:two-step_authentication&utm_source=extension&utm_medium=link&utm_campaign=2factor-m1-troubleshooting#troubleshooting" target="_blank">user guide</a> to solve the issue.');
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(array('result' => $valid, 'message' => $message, 'color' => $color, 'additional' => $additional)));
    }

    protected function _isAllowed()
    {
        return Mage::getStoreConfig('amsecurityauth/general/active');
    }
}
