<?php

/*
* Copyright (c) 2014 www.magebuzz.com
*/

class Magebuzz_Bannerads_Adminhtml_ReportsblockController extends Mage_Adminhtml_Controller_action {
  protected function _initAction() {
    $this->loadLayout()->_setActiveMenu('bannerads/items')->_addBreadcrumb(Mage::helper('adminhtml')->__('Report Banner'), Mage::helper('adminhtml')->__('Report Banner'));
    return $this;
  }

  public function indexAction() {
    $this->_initAction()->renderLayout();
  }

  public function gridAction() {
    $this->loadLayout();
    $this->getResponse()->setBody($this->getLayout()->createBlock('bannerads/adminhtml_reportsblock_grid')->toHtml());
  }

  public function massDeleteAction() {
    $reportIds = $this->getRequest()->getParam('report_ids');
    if (!is_array($reportIds)) {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
    } else {
      try {
        foreach ($reportIds as $id) {
          $report = Mage::getModel('bannerads/reports')->load($id);
          $report->delete();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($reportIds)));
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }
}