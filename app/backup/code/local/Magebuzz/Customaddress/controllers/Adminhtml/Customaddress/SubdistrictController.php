<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Adminhtml_Customaddress_SubdistrictController extends Mage_Adminhtml_Controller_Action {
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('customaddress/subdistrict')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id = $this->getRequest()->getParam('id');
		$model = Mage::getModel('customaddress/subdistrict')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('subdistrict_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('customaddress/subdistrict');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('customaddress/adminhtml_subdistrict_edit'))
				->_addLeft($this->getLayout()->createBlock('customaddress/adminhtml_subdistrict_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customaddress')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		$request = $this->getRequest();
		if ($this->getRequest()->getPost()) {
			$id = $request->getParam('id');
			$code = $request->getParam('code');
			$name = $request->getParam('default_name');
			$zipcode = $request->getParam('zipcode');
			$cityId = $request->getParam('city_id');
			if (!$name || !$code) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please fill the required fields'));
				$this->_redirect('*/*/');
				return;
			}
			$subdistricts = Mage::getModel('customaddress/subdistrict')->getCollection()
				->addFieldToFilter('code', $code)
				->addFieldToFilter('city_id', $cityId)
				->getAllIds();
			if (count($subdistricts) > 0 && !in_array($id, $subdistricts)) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('City/Subdistrict combination must be unique'));
				$this->_redirect('*/*/edit', array('id' => $id));
				return;
			}

			try {
				$city = Mage::getModel('customaddress/subdistrict');
				$city->setSubdistrictId($id)
					->setCode($code)
					->setCityId($cityId)
					->setDefaultName($name)
					->setZipcode($zipcode)
					->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Subdistrict was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setSubdistrictData(false);
				$this->_redirect('*/*/');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setSubdistrictData($this->getRequest()->getPost());
				$this->_redirect('*/*/edit', array('region_id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		$this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('customaddress/subdistrict');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function massDeleteAction() {
		$subdistrictIds = $this->getRequest()->getParam('subdistrict');
		if(!is_array($subdistrictIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		} else {
			try {
				foreach ($subdistrictIds as $subdistrictId) {
					$subdistrict = Mage::getModel('customaddress/subdistrict')->load($subdistrictId);
					$subdistrict->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__(
						'Total of %d record(s) were successfully deleted', count($subdistrictIds)
					)
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}
	
	protected function _isAllowed()	{
		return Mage::getSingleton('admin/session')->isAllowed('customaddress/subdistrict');
	}
}