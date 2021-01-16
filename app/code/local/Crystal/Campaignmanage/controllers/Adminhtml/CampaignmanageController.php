<?php

class Crystal_Campaignmanage_Adminhtml_CampaignmanageController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('campaignmanage/manage');
    }
	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('campaignmanage/campaignmanage')
			->_addBreadcrumb(
				Mage::helper('adminhtml')->__('Item Manager'),
				Mage::helper('adminhtml')->__('Item Manager')
			);
		return $this;
	}

	public function indexAction()
	{
		$this->_initAction()->renderLayout();
	}
    public function exportCsvAction() {
        $fileName = 'campaignmanage.csv';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_campaignmanage_grid')->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'campaignmanage.xml';
        $content = $this->getLayout()->createBlock('campaignmanage/adminhtml_campaignmanage_grid')->getXml();

        $this->_sendUploadResponse($fileName, $content);
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
}
