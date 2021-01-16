<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoGoogleSitemap
 */

class Amasty_SeoGoogleSitemap_Adminhtml_Amseogooglesitemap_SitemapController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->_title(Mage::helper('amseogooglesitemap')->__('Manage Sitemap Pages'));

		$this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap'));
		$this->renderLayout();
	}

	public function editAction()
	{
		$id    = $this->getRequest()->getParam('id');
		$model = Mage::getModel('amseogooglesitemap/sitemap')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (! empty($data)) {
				$model->setData($data);
			}

			Mage::register('am_sitemap_profile', $model);

			$this->loadLayout();
			$this->_setActiveMenu('amseogooglesitemap/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'),
				Mage::helper('adminhtml')->__('Item Manager')
			);
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News')
			);

			$this->_addContent($this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit'))
				->_addLeft($this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amseogooglesitemap'
				)->__('Item does not exist')
			);
			$this->_redirect('*/*/');
		}
	}

	public function runAction()
	{
		$id = $this->getRequest()->getParam('id');

		/* @var $model Amasty_SeoGoogleSitemap_Model_Sitemap */
		$model = Mage::getModel('amseogooglesitemap/sitemap')->load($id);
		if (! $model->getId()) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amseogooglesitemap'
				)->__('Item does not exist')
			);
			$this->_redirect('*/*/');
		}

		$model->run();

		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amseogooglesitemap'
			)->__('Sitemap has been saved')
		);
		$this->_redirect('*/*/');
	}

	public function newAction()
	{
		$this->_forward('edit');
	}

	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			$profile = Mage::getModel('amseogooglesitemap/sitemap');
			$profile->setData($data)
				->setId($this->getRequest()->getParam('id'));

			try {
				$profile->save();
				$profileId = $profile->getId();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amseogooglesitemap'
					)->__('Profile was successfully saved')
				);
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $profileId));

					return;
				}
				$this->_redirect('*/*/');

				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amseogooglesitemap'
			)->__('Unable to find item to save')
		);
		$this->_redirect('*/*/');
	}

	public function deleteAction()
	{
		if ($this->getRequest()->getParam('id') > 0) {
			try {
				$model = Mage::getModel('amseogooglesitemap/sitemap');

				$model->setId($this->getRequest()->getParam('id'))
					->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml'
					)->__('Item was successfully deleted')
				);
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function massDeleteAction()
	{
		$sitemapsIds = $this->getRequest()->getParam('sitemaps');
		if (! is_array($sitemapsIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		} else {
			try {
				foreach ($sitemapsIds as $sitemapId) {
					$sitemaps = Mage::getModel('amseogooglesitemap/sitemap')->load($sitemapId);
					$sitemaps->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__(
						'Total of %d record(s) were successfully deleted', count($sitemapsIds)
					)
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massDuplicateAction()
	{
		$sitemapId = $this->getRequest()->getParam('id');

		try {
			$sitemaps = Mage::getModel('amseogooglesitemap/sitemap')->load($sitemapId);
			$data     = $sitemaps->getData();
			unset($data['id']);
			Mage::getModel('amseogooglesitemap/sitemap')->setData($data)->save();

			Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('adminhtml')->__(
					'Sitemap was successfully duplicated'
				)
			);
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}

		$this->_redirect('*/*/index');
	}

	public function exportCsvAction()
	{
		$this->_exportGrid('csv');
	}

	public function exportXmlAction()
	{
		$this->_exportGrid('xml');
	}

	/**
	 * @param $type
	 *
	 * @throws Exception
	 */
	protected function _exportGrid($type)
	{
		$fileName = 'amasty_sitemap_admin_export.' . $type;
		$block    = $this->getLayout()->createBlock('amseogooglesitemap/adminhtml_sitemap_grid');
		switch ($type) {
			case 'xml' :
				$content = $block->getXml();
				break;

			case 'csv' :
				$content = $block->getCsv();
				break;

			default :
				throw new Exception('Please specify export data type');
				break;
		}

		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function renderLayout($output = '')
	{
		$this->_setActiveMenu('cms/amseotoolkit/amseogooglesitemap');
		parent::renderLayout();
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('cms/amseotoolkit/amseogooglesitemap');
	}
}