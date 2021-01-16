<?php

/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
require_once 'Mage/Adminhtml/controllers/Catalog/CategoryController.php';

class Bubble_DynamicCategory_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Catalog_CategoryController {

  /**
   * Generate grid for current category
   */
  public function gridAction() {
    if (!$category = $this->_initCategory(true)) {
      return;
    }

    // Allow another (AJAX) requests to be made if this one is too long
    session_write_close();

    try {
      $productIds = Mage::helper('dynamic_category/indexer')->process($category);

      // Used to generate grid later
      $this->getRequest()->setPost('selected_products', $productIds);
      $category->unsetData('products_position');

      $storeId = $this->getRequest()->getParam('store', 0);

      Mage::getSingleton('admin/session')->setLastViewedStore($storeId);

      $this->getRequest()->setControllerName('catalog_category');

      $this->loadLayout();

      $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
      if ($block) {
        $block->setStoreId($storeId);
      }

      $this->getResponse()->setBody(
              $this->getLayout()->createBlock('adminhtml/catalog_category_edit', 'category.edit')->getFormHtml()
      );
    } catch (Exception $e) {
      $this->getResponse()->setHttpResponseCode(403)->setBody($e->getMessage());
    }
  }

  /**
   * Return category products count
   */
  public function countAction() {
    if (!$category = $this->_initCategory(true)) {
      return;
    }

    $storeId = $this->getRequest()->getParam('store', 0);
    $count = Mage::helper('dynamic_category')->getCategoryProductCount($category, $storeId);

    $this->getResponse()->setBody($count);
  }

  /**
   * Import category conditions
   */
  public function importCondsAction() {
    if ($categoryId = $this->getRequest()->getParam('category_id')) {
      $this->getRequest()->setParam('id', $categoryId);
    }

    if (!$category = $this->_initCategory()) {
      $response = Mage::helper('dynamic_category')->__('Specified category is not valid.');
    } else {
      $conds = $this->getLayout()->createBlock(
              'dynamic_category/adminhtml_category_dynamic_conditions', 'category.dynamic.conditions'
      );
      $response = $conds->toHtml();
    }

    $this->getResponse()->setBody($response);
  }

  /**
   * Will refresh category matching products
   */
  public function forceRefreshAction() {
    if (!$category = $this->_initCategory(true)) {
      return;
    }

    $category->setDynamicProductsRefresh(1)
            ->save();

    $this->getRequest()->setControllerName('catalog_category');

    Mage::unregister('category');
    Mage::unregister('current_category');

    parent::saveAction();
  }

  /**
   * Category save
   */
  public function saveAction() {
    if (!$category = $this->_initCategory()) {
      return;
    }
    $storeId = $this->getRequest()->getParam('store');
    $refreshTree = 'false';

    if ($data = $this->getRequest()->getPost()) {

      if ($data['general']['counting_downs']=='' && $data['general']['counting_down_category'] == '1') {

        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('countingdown')->__('If category is upcoming,start time should not be empty.'));
        $url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $category->getId()));
        $refreshTree = 'true';
        $this->getResponse()->setBody(
                '<script type="text/javascript">parent.updateContent("' . $url . '", {}, ' . $refreshTree . ');</script>'
        );
        return;
      }
      $category->addData($data['general']);

      if (!$category->getId()) {
        $parentId = $this->getRequest()->getParam('parent');
        if (!$parentId) {
          if ($storeId) {
            $parentId = Mage::app()->getStore($storeId)->getRootCategoryId();
          } else {
            $parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
          }
        }
        $parentCategory = Mage::getModel('catalog/category')->load($parentId);
        $category->setPath($parentCategory->getPath());
      }

      /**
       * Check "Use Default Value" checkboxes values
       */
      if ($useDefaults = $this->getRequest()->getPost('use_default')) {
        foreach ($useDefaults as $attributeCode) {
          $category->setData($attributeCode, false);
        }
      }

      /**
       * Process "Use Config Settings" checkboxes
       */
      if ($useConfig = $this->getRequest()->getPost('use_config')) {
        foreach ($useConfig as $attributeCode) {
          $category->setData($attributeCode, null);
        }
      }

      /**
       * Create Permanent Redirect for old URL key
       */
      if ($category->getId() && isset($data['general']['url_key_create_redirect'])) {
        // && $category->getOrigData('url_key') != $category->getData('url_key')
        $category->setData('save_rewrites_history', (bool) $data['general']['url_key_create_redirect']);
      }

      $category->setAttributeSetId($category->getDefaultAttributeSetId());

      if (isset($data['category_products']) &&
              !$category->getProductsReadonly()
      ) {
        $products = Mage::helper('core/string')->parseQueryStr($data['category_products']);
        $category->setPostedProducts($products);
      }

      Mage::dispatchEvent('catalog_category_prepare_save', array(
          'category' => $category,
          'request' => $this->getRequest()
      ));

      /**
       * Proceed with $_POST['use_config']
       * set into category model for proccessing through validation
       */
      $category->setData("use_post_data_config", $this->getRequest()->getPost('use_config'));

      try {
        $validate = $category->validate();
        if ($validate !== true) {
          foreach ($validate as $code => $error) {
            if ($error === true) {
              Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $category->getResource()->getAttribute($code)->getFrontend()->getLabel()));
            } else {
              Mage::throwException($error);
            }
          }
        }

        /**
         * Unset $_POST['use_config'] before save
         */
        $category->unsetData('use_post_data_config');
        $category->save();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The category has been saved.'));
        $refreshTree = 'true';
//        Zend_debug::dump($category->getCountingDowns());die();
      } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage())
                ->setCategoryData($data);
        $refreshTree = 'false';
      }
    }
    $url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $category->getId()));
    $this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, ' . $refreshTree . ');</script>'
    );
  }

}
