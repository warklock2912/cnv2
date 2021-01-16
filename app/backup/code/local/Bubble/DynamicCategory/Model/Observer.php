<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Model_Observer
{
    /**
     * @var Bubble_DynamicCategory_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Core_Model_Session
     */
    protected $_session;

    public function __construct()
    {
        $this->_helper = Mage::helper('dynamic_category');
        $this->_session = Mage::getSingleton('core/session');
    }

    public function onBlockHtmlAfter(Varien_Event_Observer $observer)
    {
        $layout = Mage::app()->getLayout();
        if ($observer->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Catalog_Category_Tab_Product
            && $layout->getBlock('head'))
        {
            $layout->getBlock('head')->addJs('mage/adminhtml/rules.js');
            $dynamic = $layout->createBlock('adminhtml/template', 'dynamic_category_wrapper')
                ->setTemplate('bubble/dynamiccategory/wrapper.phtml');
            $dynamic->append($layout->createBlock(
                'dynamic_category/adminhtml_category_dynamic_conditions_import',
                'dynamic_category_conditions_import'
            ));
            $dynamic->append($layout->createBlock(
                'dynamic_category/adminhtml_category_dynamic_conditions',
                'dynamic_category_conditions'
            ));
            $html = $observer->getTransport()->getHtml();
            $observer->getTransport()->setHtml($dynamic->toHtml() . $html);
        }

        return $this;
    }

    public function onCatalogCategoryPrepareSave(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Category $category */
        $category = $observer->getEvent()->getCategory();
        $conds = array();
        $data = $observer->getEvent()->getRequest()->getPost();
        if (isset($data['rule']) && isset($data['rule']['conditions']) && count($data['rule']['conditions']) > 1) {
            $conds = $data['rule']['conditions'];
        }
        $category->setDynamicProductsConds($conds);
        if ($category->getDynamicProductsConds() != $category->getOrigData('dynamic_products_conds')) {
            $category->setDynamicProductsRefresh(1);
            $category->unsPostedProducts();
        }

        return $this;
    }

    public function onPredispatchCategoryEdit()
    {
        /**
         * Workaround for 'name' attribute not visible in the list because of no additional data being retrieved
         * when fetching attributes used for sort by.
         * @see Mage_Catalog_Model_Config::getAttributesUsedForSortBy()
         */
        Mage::getResourceSingleton('catalog/product')->loadAllAttributes();
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function onAdminBlockHtmlBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Category_Tab_Product) {
            $category = $block->getCategory();
            if ($category->getId()) {
                $url = $block->getUrl('*/dynamic_category/forceRefresh', array('_current' => true));
                $button = $block->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label' => Mage::helper('core')->jsQuoteEscape(
                            $this->_helper->__('Refresh Matching Products')
                        ),
                        'onclick' => "$('category_edit_form').action = '{$url}'; categorySubmit();",
                        'class' => 'save',
                    ));
                $block->getChild('reset_filter_button')->setBeforeHtml($button->toHtml());
            }
        }
    }

    /**
     * Reindex all categories according to last updated date and delay configured
     *
     * @param bool $force
     */
    public function reindexAll($force = false)
    {
        if ($force || Mage::getStoreConfigFlag('dynamic_category/general/enable_reindex')) {
            $delay = (int) Mage::getStoreConfig('dynamic_category/general/reindex_delay');
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->addIsActiveFilter()
                ->addAttributeToFilter('dynamic_products_conds', array('notnull' => true))
                ->addAttributeToFilter('dynamic_products_conds', array('neq' => 'a:0:{}'))
                ->addFieldToFilter('updated_at', array(
                    'lteq' => Varien_Date::formatDate(Zend_Date::now()->subHour($delay))
                ));
            foreach ($collection as $category) {
                /** @var Mage_Catalog_Model_Category $category */
                $this->reindexCategory($category);
            }
        }
    }

    /**
     * Reindex a single category
     *
     * @param Mage_Catalog_Model_Category $category
     */
    public function reindexCategory(Mage_Catalog_Model_Category $category)
    {
        try {
            $category->load($category->getId()); // Needed to avoid bug with empty URL key and Include in Menu switched
            Mage::helper('dynamic_category/indexer')->process($category);
        } catch (Mage_Core_Exception $e) {
            Mage::log(sprintf(
                '[Dynamic Category] Ignoring exception for category %d: %s',
                $category->getId(),
                $e->getMessage()
            ));
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
