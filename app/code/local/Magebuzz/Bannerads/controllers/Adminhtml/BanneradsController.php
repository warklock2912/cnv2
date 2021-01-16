<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Adminhtml_BanneradsController extends Mage_Adminhtml_Controller_action {

  protected function _initAction() {
    $this->loadLayout()
        ->_setActiveMenu('bannerads/items')
        ->_addBreadcrumb(Mage::helper('adminhtml')->__('Block Manager'),
            Mage::helper('adminhtml')->__('Block Manager'));

    return $this;
  }

  public function indexAction() {
    $this->_initAction()->renderLayout();
  }

  public function editAction() {
    $id = $this->getRequest()->getParam('id');
    $model = Mage::getModel('bannerads/bannerads')->load($id);
    if ($model->getId() || $id == 0) {
      $data = Mage::getSingleton('adminhtml/session')->getFormData(TRUE);
      if (!empty($data)) {
        $model->setData($data);
      }
      $customerGroupIds = unserialize($model->getCustomerGroupIds());
      $model->setCustomerGroupIds($customerGroupIds);
      Mage::register('bannerads_data', $model);

      $this->loadLayout();
      $this->_setActiveMenu('bannerads/items');

      $this->getLayout()->getBlock('head')->setCanLoadExtJs(TRUE);
      $this->getLayout()->getBlock('head')->setCanLoadTinyMce(TRUE);

      $this->_addContent($this->getLayout()->createBlock('bannerads/adminhtml_bannerads_edit'))->_addLeft($this->getLayout()->createBlock('bannerads/adminhtml_bannerads_edit_tabs'));

      $this->renderLayout();
    } else {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('bannerads')->__('Item does not exist'));
      $this->_redirect('*/*/');
    }
  }

  public function newAction() {
    $this->_forward('edit');
  }

  public function gridAction() {
    $this->getResponse()->setBody($this->getLayout()->createBlock('bannerads/adminhtml_bannerads_grid')->toHtml());
    return;
  }

  public function saveAction() {
    if ($data = $this->getRequest()->getPost()) {
      $customerGroupId = serialize($data['customer_group_ids']);
      $stores = $data['stores'];

      $model = Mage::getModel('bannerads/bannerads');
      if (isset($data['in_banner'])) {
        if ($data['in_banner'] != '') {
          $banners = explode('&', $data['in_banner']);
        } else {
          $banners = array();
        }
      } else {
        $resources = Mage::getResourceModel('bannerads/bannerads')->lookupImagesId($this->getRequest()->getParam('id'));
        $banners = $resources;
      }
      $data['banner_id'] = $banners;
      $model->setData($data)->setId($this->getRequest()->getParam('id'));
      $categories = $this->getRequest()->getPost('category_ids', -1);
      if ($categories != -1) {
        $categories = explode(',', $categories);
        $categories = serialize($categories);
        $model->setCategory($categories);

      }
      try {
        $model->setCustomerGroupIds($customerGroupId);
        $model->setStores($stores);
        $model->save();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('bannerads')->__('Block was successfully saved'));
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
        $model = Mage::getModel('bannerads/bannerads');

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
    $banneradsIds = $this->getRequest()->getParam('bannerads');
    if (!is_array($banneradsIds)) {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
    } else {
      try {
        foreach ($banneradsIds as $banneradsId) {
          $bannerads = Mage::getModel('bannerads/bannerads')->load($banneradsId);
          $bannerads->delete();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($banneradsIds)));
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }

  public function massStatusAction() {
    $banneradsIds = $this->getRequest()->getParam('bannerads');
    if (!is_array($banneradsIds)) {
      Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
    } else {
      try {
        foreach ($banneradsIds as $banneradsId) {
          $bannerads = Mage::getSingleton('bannerads/bannerads')->load($banneradsId)->setStatus($this->getRequest()->getParam('status'))->setIsMassupdate(TRUE)->save();
        }
        $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($banneradsIds)));
      } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }



  public function imagelistAction() {
    $this->loadLayout();
    $this->getLayout()->getBlock('bannerads.edit.tab.images')->setImages($this->getRequest()->getPost('oblock', null));
    $this->renderLayout();
  }

  public function imagelistGridAction() {
    $this->loadLayout();
    $this->getLayout()->getBlock('bannerads.edit.tab.images')->setImages($this->getRequest()->getPost('oblock', null));
    $this->renderLayout();
  }

  protected function _isAllowed()	{
    return Mage::getSingleton('admin/session')->isAllowed('bannerads/items');
  }

  public function categoriesAction(){

    $this->_initBlock();
    $this->loadLayout();
    $this->renderLayout();
  }
  public function categoriesJsonAction()
  {
    $this->_initBlock();

    $this->getResponse()->setBody(
        $this->getLayout()->createBlock('bannerads/adminhtml_bannerads_edit_tab_categories')
            ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
    );
  }

  protected function _initBlock(){
    $blockId  = (int) $this->getRequest()->getParam('id');
    $block    = Mage::getModel('bannerads/bannerads');

    if ($blockId) {
      $block->load($blockId);
    }
    Mage::register('current_block', $block);
    return $block;
  }



}