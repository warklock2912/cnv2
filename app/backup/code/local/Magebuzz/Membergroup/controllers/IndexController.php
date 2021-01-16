<?php
class Magebuzz_Membergroup_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {	
		$this->loadLayout();  
		$this->_initLayoutMessages('membergroup/session');
		$this->renderLayout();
    }

  protected function _getSession()
  {
    return Mage::getSingleton('customer/session');
  }

  public function updateMemberIdAction(){
    $vipMember = $this->getRequest()->getParam('vip_member_id');
    try{
      if(Mage::getSingleton('customer/session')->isLoggedIn()){
        $customer = $this->_getSession()->getCustomer();
        $customer->setData('vip_member_id', $vipMember);
        $customer->setData('vip_member_status', '1');
        $customer->save();
        $this->_getSession()
          ->addSuccess($this->__('Your Vip member Id updated, We will verify it shortly'));
        $this->_redirect('customer/account/edit');
        return;
      }else{
        Mage::getSingleton('core/session')->addError('Please Login Again');
        $this->_redirect('customer/account/edit');
        return $this;
      }
    }catch (Exception $e){
      Mage::getSingleton(core/session)->addError($e->getMessage());
    }
    $this->_redirect('customer/account/edit');
  }

  public function testEmailAdminAction(){
    $recipientName = 'jackie Nguyen';
    $recipientEmail = 'nguyenduong0508@gmail.com';

    $store = Mage::app()->getStore();
    $translate = Mage::getSingleton('core/translate');
    $translate->setTranslateInline(false);

    Mage::getModel('core/email_template')
      ->setDesignConfig(array(
        'area' => 'frontend',
        'store' => $store->getId()
      ))->sendTransactional(
        Mage::getStoreConfig('membergroup/notifyadmin/emailtemplate', $store),
        Mage::getStoreConfig('trans_email/ident_general', $store),
        $recipientEmail,
        $recipientName,
        array(
          'store' => $store,
          'recipient_name' => $recipientName,
          'recipient_email' => $recipientEmail
        )
      );

    $translate->setTranslateInline(true);

    die('Email has been sent successfully!');
  }
}