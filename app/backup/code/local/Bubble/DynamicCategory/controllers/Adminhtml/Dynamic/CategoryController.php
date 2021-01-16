<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
require_once 'Mage/Adminhtml/controllers/Catalog/CategoryController.php';

class Bubble_DynamicCategory_Adminhtml_Dynamic_CategoryController extends Mage_Adminhtml_Catalog_CategoryController
{
    /**
     * Generate grid for current category
     */
    public function gridAction()
    {
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
    public function countAction()
    {
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
    public function importCondsAction()
    {
        if ($categoryId = $this->getRequest()->getParam('category_id')) {
            $this->getRequest()->setParam('id', $categoryId);
        }

        if (!$category = $this->_initCategory()) {
            $response = Mage::helper('dynamic_category')->__('Specified category is not valid.');
        } else {
            $conds = $this->getLayout()->createBlock(
                'dynamic_category/adminhtml_category_dynamic_conditions',
                'category.dynamic.conditions'
            );
            $response = $conds->toHtml();
        }

        $this->getResponse()->setBody($response);
    }

    /**
     * Will refresh category matching products
     */
    public function forceRefreshAction()
    {
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
}