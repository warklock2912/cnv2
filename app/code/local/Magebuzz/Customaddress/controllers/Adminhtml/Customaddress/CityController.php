<?php
/*
* Copyright (c) 2015 www.magebuzz.com 
*/
class Magebuzz_Customaddress_Adminhtml_Customaddress_CityController extends Mage_Adminhtml_Controller_Action {
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('customaddress/city')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id = $this->getRequest()->getParam('id');
		$model = Mage::getModel('customaddress/city')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('city_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('customaddress/city');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('customaddress/adminhtml_city_edit'))
				->_addLeft($this->getLayout()->createBlock('customaddress/adminhtml_city_edit_tabs'));

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
			$regionId = $request->getParam('region_id');
			if (!$name || !$code) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please fill the required fields'));
				$this->_redirect('*/*/');
				return;
			}
			$cities = Mage::getModel('customaddress/city')->getCollection()
				->addFieldToFilter('code', $code)
				->addFieldToFilter('region_id', $regionId)
				->getAllIds();
			if (count($cities) > 0 && !in_array($id, $cities)) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('City/Region combination must be unique'));
				$this->_redirect('*/*/edit', array('id' => $id));
				return;
			}

			try {
				$city = Mage::getModel('customaddress/city');
				$city->setCityId($id)
					->setCode($code)
					->setRegionId($regionId)
					->setDefaultName($name)
					->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('City was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setCityData(false);
				$this->_redirect('*/*/');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setStateData($this->getRequest()->getPost());
				$this->_redirect('*/*/edit', array('region_id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		$this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('customaddress/city');
				 
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
		$cityIds = $this->getRequest()->getParam('city');
		if(!is_array($cityIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		} else {
			try {
				foreach ($cityIds as $cityId) {
					$city = Mage::getModel('customaddress/city')->load($cityId);
					$city->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__(
						'Total of %d record(s) were successfully deleted', count($cityIds)
					)
				);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}		
	
	protected function _isAllowed()	{
		return Mage::getSingleton('admin/session')->isAllowed('customaddress/city');
	}
}