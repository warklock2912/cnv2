<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Adminhtml_Bannerads_ReportsblockController extends Mage_Adminhtml_Controller_action {
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

   public function exportCsvAction() {
    $fileName = 'bannerads.csv';
    $content = $this->getLayout()->createBlock('bannerads/adminhtml_reportsblock_grid')->getCsv();

    $this->_sendUploadResponse($fileName, $content);
  }

  public function exportXmlAction() {
    $fileName = 'bannerads.xml';
    $content = $this->getLayout()->createBlock('bannerads/adminhtml_reportsblock_grid')->getXml();

    $this->_sendUploadResponse($fileName, $content);
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
  protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
    $response = $this->getResponse();
    $response->setHeader('HTTP/1.1 200 OK', '');
    $response->setHeader('Pragma', 'public', TRUE);
    $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', TRUE);
    $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
    $response->setHeader('Last-Modified', date('r'));
    $response->setHeader('Accept-Ranges', 'bytes');
    $response->setHeader('Content-Length', strlen($content));
    $response->setHeader('Content-type', $contentType);
    $response->setBody($content);
    $response->sendResponse();
    die;
  }
	
	protected function _isAllowed()	{
		return Mage::getSingleton('admin/session')->isAllowed('bannerads/report');
	}
}