<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Observer extends MageWorx_SearchSuite_Model_Observer_Abstract {

    public function cmsPageSaveAfter($observer) {
        if (Mage::helper('mageworx_searchsuite')->isCmspageIndexEnabled()) {
            $event = $observer->getEvent();
            if ($event->getObject() instanceof Mage_Cms_Model_Page) {
                $entity = $event->getObject();
                Mage::getSingleton('index/indexer')->processEntityAction(
                        $entity, 'cms_page_search', Mage_Index_Model_Event::TYPE_SAVE
                );
            }
        }
        return $this;
    }

    public function cmsPageDeleteBefore($observer) {
        if (Mage::helper('mageworx_searchsuite')->isCmspageIndexEnabled()) {
            $event = $observer->getEvent();
            if ($event->getObject() instanceof Mage_Cms_Model_Page) {
                $entity = $event->getObject();
                Mage::getSingleton('index/indexer')->processEntityAction(
                        $entity, 'cms_page_search', Mage_Index_Model_Event::TYPE_DELETE
                );
            }
        }
        return $this;
    }

    public function catalogCategorySaveAfter($observer) {
        if (Mage::helper('mageworx_searchsuite')->isCategoryIndexEnabled()) {
            $event = $observer->getEvent();
            if ($event->getDataObject() instanceof Mage_Catalog_Model_Category) {
                $entity = $event->getDataObject();
                Mage::getSingleton('index/indexer')->processEntityAction(
                        $entity, 'category_search', Mage_Index_Model_Event::TYPE_SAVE
                );
            }
        }
        return $this;
    }

    public function catalogCategoryDeleteBefore($observer) {
        if (Mage::helper('mageworx_searchsuite')->isCategoryIndexEnabled()) {
            $event = $observer->getEvent();
            if ($event->getDataObject() instanceof Mage_Catalog_Model_Category) {
                $entity = $event->getDataObject();
                Mage::getSingleton('index/indexer')->processEntityAction(
                        $entity, 'category_search', Mage_Index_Model_Event::TYPE_DELETE
                );
            }
        }
        return $this;
    }

    public function catalogControllerProductInit($observer) {
        if (strpos(Mage::helper('mageworx_searchsuite')->getHttpRefferer(), 'catalogsearch/result') > 0) {
            $product = $observer->getProduct();
            $sdata = Mage::helper('mageworx_searchsuite')->getSearchTransition();
            $queryId = Mage::helper('mageworx_searchsuite')->setSearchTransition($product->getId());
            if ($queryId && (!isset($sdata[$product->getId()]) || $sdata[$product->getId()] !== $queryId)) {
                $data = array('query_id' => $queryId, 'product_id' => $product->getId());
                $tracking = Mage::getSingleton('mageworx_searchsuite/tracking_conversion');
                $tracking->setData($data)->save();
            }
            if (Mage::helper('mageworx_searchsuite')->isHighlightingEnabled()) {
                Mage::helper('catalog/output')->addHandler('productAttribute', Mage::getModel('mageworx_searchsuite/highlight'));
            }
        }
        return $this;
    }

    /**
     * Save search query id in info_buyRequest for quote
     * @param type $observer
     * @return MageWorx_SearchSuite_Model_Observer
     */
    public function checkoutCartProductAddAfter($observer) {
        $sdata = Mage::helper('mageworx_searchsuite')->getSearchTransition();
        if (isset($sdata['query'])) {
            $quote = $observer->getQuoteItem();
            $product = $observer->getProduct();
            $value = null;
            if (strpos(Mage::helper('mageworx_searchsuite')->getHttpRefferer(), 'catalogsearch/result') > 0) {
                $value = $sdata['query'];
            } else if (isset($sdata[$product->getId()])) {
                $value = $sdata[$product->getId()];
            }
            if (!is_null($value)) {
                $info = $quote->getProduct()->getCustomOption('info_buyRequest')->getValue();
                if ($info) {
                    $info = unserialize($info);
                } else {
                    $info = array();
                }
                $info['search_query'] = $value;
                $info = serialize($info);
                $quote->getProduct()->getCustomOption('info_buyRequest')->setValue($info);
            }
        }
        return $this;
    }

    /**
     * Save tracking info after order placing 
     * @param type $observer
     * @return MageWorx_SearchSuite_Model_Observer
     */
    public function salesOrderPlaceAfter($observer) {
        $order = $observer->getOrder();
        $tracking = Mage::getSingleton('mageworx_searchsuite/tracking_purchase');
        foreach ($order->getAllItems() as $item) {
            if($item->getProduct()->getCustomOption('info_buyRequest')){
                $info = $item->getProduct()->getCustomOption('info_buyRequest')->getValue();
                if ($info) {
                    $info = unserialize($info);
                    if (isset($info['search_query'])) {
                        $data = array(
                            'order_id' => $item->getOrderId(),
                            'query_id' => $info['search_query'],
                            'product_id' => $item->getProduct()->getId(),
                            'price' => $item->getProduct()->getPrice()
                        );
                        $tracking->setData($data);
                        $tracking->save();
                    }
                }
            }

        }
        return $this;
    }

    /**
     * Save search query in session 
     * @param type $observer
     * @return MageWorx_SearchSuite_Model_Observer
     */
    public function searchsuitePrepareResultAfter($observer) {
        $query = $observer->getEvent()->getQuery();
        Mage::helper('mageworx_searchsuite')->setSearchQuery($query->getQueryId(), $query->getQueryText());
        if (Mage::getConfig()->getModuleConfig('MageWorx_GeoIP')->is('active', true)) {
            $loc = Mage::getSingleton('mageworx_geoip/geoip')->getCurrentLocation();
            if ($loc->getCode()) {
                $tracking = Mage::getSingleton('mageworx_searchsuite/tracking_region');
                $data = array('query_id' => $query->getId(), 'country' => $loc->getCode());
                try {
                    $tracking->setData($data)->save();
                } catch (Exception $e) {
                    
                }
            }
        }
        return $this;
    }

    public function htmlBefore($observer) {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Catalog_Search_Grid) {
            $this->_removeSynonymForColumnFromGrid($block);
//            $this->_prepareSynonymColumnToGrid($block);
//            $this->_registerSynonymsCollection($block->getCollection());
        } else if ($block instanceof Mage_Adminhtml_Block_Catalog_Search_Edit_Form) {
            $this->_removeSynonymForColumnFromForm($block);
            $this->_prepareStaticBlockColumnToForm($block);
            $this->_prepareTrackingColumnsToForm($block);
        } else if ($block instanceof MageWorx_SearchSuite_Block_Adminhtml_Synonyms_Edit_Form) {
            $this->_prepareSynonymColumnToForm($block);
        }
        return $this;
    }

    public function coreBlockAbstractPrepareLayoutBefore($observer) {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Report_Search_Grid) {
            $this->_prepareTrackingColumnsToGrid($block);
        }
        return $this;
    }

    public function collectionLoadBefore($observer) {
        $controllerName = Mage::app()->getRequest()->getControllerName();
        $collection = $observer->getEvent()->getCollection();
        if ('report' == $controllerName &&
                ($collection instanceof Mage_CatalogSearch_Model_Resource_Query_Collection || $collection instanceof Mage_CatalogSearch_Model_Mysql4_Query_Collection)) {
            $this->_prepareTrackingsToCollection($collection);
        }
        return $this;
    }

    public function catalogProductCollectionLoadAfter($observer) {
        $collection = $observer->getEvent()->getCollection();
        if (($collection instanceof Mage_CatalogSearch_Model_Resource_Fulltext_Collection || $collection instanceof Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection) && Mage::helper('mageworx_searchsuite')->isRedirectToProduct() && Mage::app()->getRequest()->getControllerName() == 'result') {
            if (!$collection->getSelect()->getPart(Zend_Db_Select::LIMIT_OFFSET) && $collection->getSelect()->getPart(Zend_Db_Select::LIMIT_COUNT) > 1) {
                if ($collection->count() === 1) {
                    $product = $collection->getFirstItem();
                    Mage::app()->getResponse()->setRedirect($product->getProductUrl());
                }
            }
        }
        return $this;
    }

    public function catalogProductCollectionLoadBefore($observer) {
        $collection = $observer->getEvent()->getCollection();
        $parameter = Mage::helper('mageworx_searchsuite')->getSearchParameter();

        if (!$parameter && ($collection instanceof Mage_CatalogSearch_Model_Resource_Fulltext_Collection || $collection instanceof Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection || $collection instanceof MageWorx_SearchSuite_Model_Mysql4_CatalogSearch_Fulltext_Collection)) {
            $this->_changeProductRelevance($collection);
        }
        return $this;
    }

    public function controllerActionPredispatch($observer) {
        Mage::helper('mageworx_searchsuite')->addFooterJs('mageworx/searchsuite/searchsuite.js');
    }

}
