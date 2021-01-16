<?php

/*
* Copyright (c) 2014 www.magebuzz.com
*/

class Magebuzz_Bannerads_Adminhtml_ImagesController extends Mage_Adminhtml_Controller_action {
  protected function _initAction() {
    $this->loadLayout()->_setActiveMenu('bannerads/items')->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

    return $this;
  }

  public function indexAction() {
    $this->_initAction()->renderLayout();
  }

  public function editAction() {
    $id = $this->getRequest()->getParam('id');
    $model = Mage::getModel('bannerads/images')->load($id);

    if ($model->getId() || $id == 0) {
      $data = Mage::getSingleton('adminhtml/session')->getFormData(TRUE);
      if (!empty($data)) {
        $model->setData($data);
      }
      Mage::register('images_data', $model);
      $this->loadLayout();
      $this->_setActiveMenu('bannerads/items');
      $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Categories'), Mage::helper('adminhtml')->__('Manage Banner Images'));
      $this->_addBreadcrumb(Mage::helper('adminhtml')->__('New Category'), Mage::helper('adminhtml')->__('New Banner'));

      $this->getLayout()->getBlock('head')->setCanLoadExtJs(TRUE);

      $this->_addContent($this->getLayout()->createBlock('bannerads/adminhtml_images_edit'))->_addLeft($this->getLayout()->createBlock('bannerads/adminhtml_images_edit_tabs'));
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
      if (isset($_FILES['banner_image']['name']) && $_FILES['banner_image']['name'] != '') {
				$info = pathinfo($_FILES['banner_image']['name']);
				$newFileName = Mage::helper('bannerads')->generateUrl($info['filename']) . '.' . $info['extension'];


        $path = Mage::getBaseDir('media') . DS . "bannerads" . DS . "images" . DS;
        $uploader = new Varien_File_Uploader('banner_image');
        $uploader->setAllowedExtensions(array('jpg', 'JPG', 'jpeg', 'gif', 'GIF', 'png', 'PNG'));
        $uploader->setAllowRenameFiles(FALSE);
        $uploader->setFilesDispersion(FALSE);

        $uploader->save($path, $newFileName);
        $data['banner_image'] = $newFileName;
        $imgPath = Mage::getBaseUrl('media') . "banners/images/" . $newFileName;
      }

      $model = Mage::getModel('bannerads/images');
      $model->setData($data);
      if ($this->getRequest()->getParam('id')) {
        $model->setUpdateTime(now())->setId($this->getRequest()->getParam('id'));
      } else {
        $model->setCreatedTime(now());
      }
      try {
        $model->save();
        $bannerId = $model->getBannerId();
        if (isset($data['block_ids'])) {
          $blockIds = explode('&', $data['block_ids']);
          Mage::getModel('bannerads/bannerblock')->saveBlock($blockIds, $bannerId);
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('bannerads')->__('Images was successfully saved'));
        Mage::getSingleton('adminhtml/session')->setFormData(FALSE);

        if ($this->getRequest()->getParam('back')) {
          if (isset($data['from_block_id']) && $data['from_block_id']) {
            $this->_redirect('*/*/edit', array('id' => $model->getId(), 'from_block_id' => $data['from_block_id']));
            return;
          }
          $this->_redirect('*/*/edit', array('id' => $model->getId()));
          return;
        }

        if (isset($data['from_block_id']) && $data['from_block_id']) {
          $this->_redirect('*/adminhtml_bannerads/edit', array('id' => $data['from_block_id']));
          return;
        }

        $this->_redirect('*/*/');
        return;
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        Mage::getSingleton('adminhtml/session')->setFormData($data);
        if (isset($data['from_block_id']) && $data['from_block_id']) {
          $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'from_block_id' => $data['from_block_id']));
          return;
        }
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        return;
      }
    }
    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('bannerads')->__('Unable to find item to save'));

    if (isset($data['from_block_id']) && $data['from_block_id']) {
      $this->_redirect('*/adminhtml_bannerads/edit', array('id' => $data['from_block_id']));
      return;
    }

    $this->_redirect('*/*/');
  }

  public function deleteAction() {
    if ($this->getRequest()->getParam('id') > 0) {
      try {
        $model = Mage::getModel('bannerads/images');
        $model->setId($this->getRequest()->getParam('id'))->delete();
        $blockModel = Mage::getModel('bannerads/bannercategory')->saveCategory($categoryIds = array(), $this->getRequest()->getParam('id'));
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
    $categoryIds = $this->getRequest()->getParam('images');
    if (!is_array($categoryIds)) {
      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
    } else {
      try {
        foreach ($categoryIds as $categoryId) {
          $event_category = Mage::getModel('bannerads/images')->load($categoryId);
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
    $categoryIds = $this->getRequest()->getParam('images');
    if (!is_array($categoryIds)) {
      Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
    } else {
      try {
        foreach ($categoryIds as $categoryId) {
          $events = Mage::getSingleton('bannerads/images')->load($categoryId)->setStatus($this->getRequest()->getParam('status'))->setIsMassupdate(TRUE)->save();
        }
        $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully updated', count($categoryIds)));
      } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage());
      }
    }
    $this->_redirect('*/*/index');
  }

  public function categorylistAction() {
    $this->loadLayout();
    $this->getLayout()->getBlock('images.edit.tab.categories')->setCategories($this->getRequest()->getPost('ocategory', null));
    $this->renderLayout();
  }

  public function categorylistGridAction() {
    $this->loadLayout();
    $this->getLayout()->getBlock('images.edit.tab.categories')->setCategories($this->getRequest()->getPost('ocategory', null));
    $this->renderLayout();
  }

  public function block_tabAction() {
    $this->loadLayout();
    $this->getLayout()->getBlock('images_edit_tab_block')->setBlocks($this->getRequest()->getPost('blocks', null));
    $this->renderLayout();
  }

  public function block_gridAction() {
    $this->loadLayout();
    $this->getLayout()->getBlock('images_edit_tab_block')->setBlocks($this->getRequest()->getPost('blocks', null));
    $this->renderLayout();
  }

}