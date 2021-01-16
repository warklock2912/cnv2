<?php

/*
* Copyright (c) 2014 www.magebuzz.com
*/

class Magebuzz_Confirmpayment_Adminhtml_ConfirmpaymentController extends Mage_Adminhtml_Controller_action {
  protected function _initAction() {
    $this->loadLayout()->_setActiveMenu('confirmpayment/items')->_addBreadcrumb(Mage::helper('adminhtml')->__('Submited Data Manager'), Mage::helper('adminhtml')->__('Submited Data Manager'));

    return $this;
  }

  public function indexAction() {
    $this->_initAction()->renderLayout();
  }
  public function viewAction()
    {
        $this->_initAction();
        
        $submit = Mage::getModel('confirmpayment/cpform');

        $id  = $this->getRequest()->getParam('id');
        $submit->load($id);

        if (!$submit->getId()) {
            $this->_getSession()->addError($this->__('This submit is no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        Mage::register('confirmpayment_current_submit', $submit);

        $this->renderLayout();
    }


  public function deleteAction() {
    if ($this->getRequest()->getParam('id') > 0) {
      try {
        $model = Mage::getModel('confirmpayment/cpform');
        $model->setId($this->getRequest()->getParam('id'))->delete();

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Data was successfully deleted'));
        $this->_redirect('*/*/');
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
      }
    }
    $this->_redirect('*/*/');
  }

  public function massDeleteAction() {
    $formIds = $this->getRequest()->getParam('cpform');
    if (!is_array($formIds)) {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
    } else {
      try {
        foreach ($formIds as $formId) {
          $formData = Mage::getModel('confirmpayment/cpform')->load($formId);
          $formData->delete();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($formIds)));
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }

  public function massStatusAction() {
    $formIds = $this->getRequest()->getParam('cpform');
    if (!is_array($formIds)) {
      Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
    } else {
      try {
        foreach ($formIds as $formId) {
          $formData = Mage::getSingleton('confirmpayment/cpform')->load($formId)->setStatus($this->getRequest()->getParam('status'))->setIsMassupdate(TRUE)->save();
        }
        $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($formIds)));
      } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }
  
  public function changeStatusAction() {
    if ($data = $this->getRequest()->getPost()) {
      $model = Mage::getModel('confirmpayment/cpform');
      $id = $this->getRequest()->getParam('id');
      $model->setId($id)->setStatus($data['cp-stt']);
      try {
        $model->save();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('confirmpayment')->__('Change status successfully'));
        $this->_redirect('*/*/');
        return;
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        $this->_redirect('*/*/');
        return;
      }
    }    
    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('confirmpayment')->__('Unable to change status'));
    $this->_redirect('*/*/');
  }
  
  public function gridAction()
    {
        $this->loadLayout();
        $body = $this->getLayout()->createBlock('confirmpayment/adminhtml_confirmpayment_grid')->toHtml();
        $this->getResponse()->setBody($body);
    }
}