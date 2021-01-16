<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Adminhtml_Bannerads_CategoriesController extends Mage_Adminhtml_Controller_action {
  protected function _initAction() {
    $this->loadLayout()->_setActiveMenu('bannerads/items')->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

    return $this;
  }

  public function indexAction() {
    $this->_initAction()->renderLayout();
  }

  public function editAction() {
    $id = $this->getRequest()->getParam('id');
    $model = Mage::getModel('bannerads/categories')->load($id);

    if ($model->getId() || $id == 0) {
      $data = Mage::getSingleton('adminhtml/session')->getFormData(TRUE);
      if (!empty($data)) {
        $model->setData($data);
      }
      Mage::register('category_data', $model);
      $this->loadLayout();
      $this->_setActiveMenu('bannerads/items');
      $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Categories'), Mage::helper('adminhtml')->__('Manage Categories'));
      $this->_addBreadcrumb(Mage::helper('adminhtml')->__('New Category'), Mage::helper('adminhtml')->__('New Category'));

      $this->getLayout()->getBlock('head')->setCanLoadExtJs(TRUE);

      $this->_addContent($this->getLayout()->createBlock('bannerads/adminhtml_categories_edit'))->_addLeft($this->getLayout()->createBlock('bannerads/adminhtml_categories_edit_tabs'));
      $this->renderLayout();
    } else {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('bannerads')->__('Item does not exist'));
      $this->_redirect('*/*/');
    }
  }

  public function newAction() {
    $this->_forward('edit');
  }

  public function saveAction() {
    if ($data = $this->getRequest()->getPost()) {
      $model = Mage::getModel('bannerads/categories');
      $model->setData($data)->setCreatedTime(now())->setId($this->getRequest()->getParam('id'));
      try {
        $model->save();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('bannerads')->__('Category was successfully saved'));
        Mage::getSingleton('adminhtml/session')->setFormData(FALSE);

        if ($this->getRequest()->getParam('back')) {
          $this->_redirect('*/*/edit', array('id' => $model->getId()));
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
    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('bannerads')->__('Unable to find item to save'));
    $this->_redirect('*/*/');
  }

  public function deleteAction() {
    if ($this->getRequest()->getParam('id') > 0) {
      try {
        $model = Mage::getModel('bannerads/categories');
        $model->setId($this->getRequest()->getParam('id'))->delete();

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
    $categoryIds = $this->getRequest()->getParam('categories');
    if (!is_array($categoryIds)) {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
    } else {
      try {
        foreach ($categoryIds as $categoryId) {
          $event_category = Mage::getModel('bannerads/categories')->load($categoryId);
          $event_category->delete();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($categoryIds)));
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }

  public function massStatusAction() {
    $categoryIds = $this->getRequest()->getParam('categories');
    if (!is_array($categoryIds)) {
      Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
    } else {
      try {
        foreach ($categoryIds as $categoryId) {
          $events = Mage::getSingleton('bannerads/categories')->load($categoryId)->setStatus($this->getRequest()->getParam('status'))->setIsMassupdate(TRUE)->save();
        }
        $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($categoryIds)));
      } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }
	
	protected function _isAllowed()	{
		return Mage::getSingleton('admin/session')->isAllowed('bannerads/images');
	}
}