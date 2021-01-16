<?php

/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */
/**
 * Search Suite extension
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @author     MageWorx Dev Team
 */
require_once 'Mage/CatalogSearch/controllers/AjaxController.php';

class MageWorx_SearchSuiteAutocomplete_AjaxController extends Mage_CatalogSearch_AjaxController {

    public function indexAction() {
        $autocomplete = Mage::getSingleton('core/layout')->createBlock('mageworx_searchsuiteautocomplete/autocomplete')->setNameInLayout('autocomplete');
        $content = '';
        $callback = array();
        $helper = Mage::helper('mageworx_searchsuiteautocomplete');

        $queryText = $this->getRequest()->getParam('q', false);
        if ($queryText && !empty($queryText)) {
            $queryModel = Mage::helper('catalogsearch')->getQuery();
            $queryModel->setStoreId(Mage::app()->getStore()->getId());
            $queryModel->prepare();
            $queryModel->setPopularity($queryModel->getPopularity() + 1);
            $queryModel->save();
            $fields = $helper->getPopupFields();

            $hasData = false;

            if (in_array('suggest', $fields)) {
                $suggestData = Mage::getResourceModel('catalogsearch/query_collection')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setQueryFilter($queryText);
                $suggestData->getSelect()->limit($helper->getSuggestResultsNumber());
                if ($suggestData->count() > 0) {
                    $callback[] = 'jQuery(".search-suggest").searchSuiteAutocompleteSuggest()';
                }
                if ($suggestData->count() > 0) {
                    $autocomplete->setSuggestData($suggestData);
                    $hasData = true;
                }
            }

            if (in_array('product', $fields)) {
                $layer = $this->_getLayer();
                $this->_addFacetCondition($layer);

                $attr = array();
                $fields = $helper->getProductResultFields();
                if (in_array('description', $fields)) {
                    $attr[] = 'description';
                }
                if (in_array('short_description', $fields)) {
                    $attr[] = 'short_description';
                }
                if (in_array('product_image', $fields)) {
                    $attr[] = 'image';
                }
                $collection = $layer->getProductCollection();
                $collection->addAttributeToSelect($attr);
                $collection->setOrder('relevance', 'desc');
                $collection->getSelect()->limit($helper->getProductResultsNumber());
                $products = $collection->load();
                if ($products->count() > 0) {
                    $autocomplete->setProducts($products);
                    $hasData = true;
                }
            }

            if ($helper->isCmspageSearchEnabled()) {
                $pages = $helper->getCmspageSearchResults();
                if ($pages->count() > 0) {
                    $autocomplete->setCmsPages($pages);
                    $hasData = true;
                }
            }

            if ($helper->isCategorySearchEnabled()) {
                $categories = $helper->getCategorySearchResults();
                if ($categories->count() > 0) {
                    $autocomplete->setCategories($categories);
                    $hasData = true;
                }
            }

            if ($hasData) {
                $content = $autocomplete->toHtml();
            }
        }

        $callbackFunction = '';
        if (count($callback) > 0) {
            $callbackFunction = implode(';', $callback);
        }

        $result = array('content' => $content, 'callback' => $callbackFunction);
//        echo json_encode($result);
      $this->getResponse()->setBody(json_encode($result));
    }

    protected function _getLayer() {
        $searchParameter = Mage::helper('mageworx_searchsuite')->getSearchParameter();
        $categoryParameter = Mage::helper('mageworx_searchsuite')->getSearchCategory();
        $layer = null;
        if (!$searchParameter && !$categoryParameter) {
            $layer = Mage::getSingleton('catalogsearch/layer');

            if (Mage::getConfig()->getModuleConfig('Enterprise_Search')->is('active', true)) {
                $helper = Mage::helper('enterprise_search');
                if ($helper->isThirdPartSearchEngine() && $helper->isActiveEngine()) {
                    $layer = Mage::getSingleton('enterprise_search/search_layer');
                }
            }
        } else {
            $layer = Mage::getModel('mageworx_searchsuite/layer');
        }

        return $layer;
    }

    protected function _addFacetCondition($layer) {
        if (!Mage::getConfig()->getModuleConfig('Enterprise_Search')->is('active', true)) {
            return $this;
        }
        $category = $layer->getCurrentCategory();
        $childrenCategories = $category->getChildrenCategories();

        $useFlat = (bool) Mage::getStoreConfig('catalog/frontend/flat_catalog_category');
        $categories = ($useFlat) ? array_keys($childrenCategories) : array_keys($childrenCategories->toArray());

        $layer->getProductCollection()->setFacetCondition('category_ids', $categories);

        return $this;
    }

}
