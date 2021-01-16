<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */
class Amasty_Groupcat_Model_ObserverProduct
{

    const FORBIDDEN_ACTION_404      = '1';
    const FORBIDDEN_ACTION_REDIRECT = '2';


    /* add restriction to layer */
    public function handleLayoutRender()
    {
        $collection = Mage::getSingleton('catalog/layer')->getProductCollection();

        if($collection) {
            $this->addRestrictionToProductCollection($collection);
        }
    }


    /*
     *  hide products on category list
     */
    public function hideProducts(Varien_Event_Observer $observer)
    {
        $collection = $observer->getCollection();

        if($collection) {
            $this->addRestrictionToProductCollection($collection);
        }

        return $this;
    }


    /*
     *  hide products on category list
     */
    public function addRestrictionToProductCollection($collection)
    {
        if (!Mage::getStoreConfig('amgroupcat/general/disable') || Mage::registry('amgroupcat_fetching_category')) {
            return false;
        }
        if ($category = Mage::registry('current_category')) {
            $categoryId = Mage::registry('current_category')->getId();
        } else {
            $categoryId = null;
        }

        Mage::register('amgroupcat_fetching_category', true, true);

        $productIds  = array();
        $activeRules = Mage::helper('amgroupcat')->getActiveRules(array('remove_product_links = 1'));

        if ($activeRules) {
            foreach ($activeRules as $rule) {
                // get directly restricted products
                $currentRuleProductIds = Mage::getModel('amgroupcat/product')->getCollection()
                    ->addFieldToSelect('product_id')
                    ->addFieldToFilter('rule_id', $rule['rule_id'])
                    ->getData();

                foreach ($currentRuleProductIds as $productId) {
                    $productIds[] = $productId['product_id'];
                }

                // get all restricted products from restricted categories
                $catIds = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
                if (!empty($catIds)) {
                    foreach ($catIds as $catId) {
                        if ($catId > 0) {
                            $model = Mage::getModel('catalog/category')->load($catId);
                            if ($model && $model->getId()){
                                $products = $model->getProductCollection()
                                    ->addAttributeToSelect('entity_id')// add all attributes - optional
                                    ->addAttributeToFilter('status', 1)
                                    ->getData();
                                foreach ($products as $product) {
                                    $productIds[] = $product['entity_id'];
                                }
                            }
                        }
                    }
                }
            }


            // add products to collection filter
            if (!empty($productIds)) {
                $productIds = array_unique($productIds);

                $collection->addFieldToFilter('entity_id', array(
                        'nin' => $productIds,
                    )
                );
            }

            // delete trigger
            Mage::unregister('amgroupcat_fetching_category');
        }

        return $this;
    }


    /*
    * direct product access by link
    */
    public function checkProductRestrictions(Varien_Event_Observer $observer, $productId = false, $action = true)
    {
        if (!Mage::getStoreConfig('amgroupcat/general/disable')) {
            return false;
        }

        if (!$productId) {
            $action    = $observer->getEvent()->getData('controller_action')->getRequest()->getParams();
            $productId = isset($action['id']) ? $action['id'] : -1;
        }

        $result = false;

        /**
         * check category restrictions
         */
        $catLoad = Mage::getModel('catalog/product');
        $catLoad->setId($productId);
        $categoryIds = $catLoad->getResource()->getCategoryIds($catLoad);
        if (is_array($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $categoryId = is_array($categoryId) ? $categoryId[0] : $categoryId;
                if ($categoryId > 0) {
                    $result = $result || Mage::getModel('amgroupcat/observerCategory')->checkCategoryTreeRestrictions($categoryId, $action);
                }
            }
        }

        /**
         * check product restrictions on forbidden direct links
         */
        $params = array('r.allow_direct_links = 0');
        $activeRules = Mage::helper('amgroupcat')->getActiveRulesForProduct($productId, $params);
        if ($activeRules) {
            $result = true;
        }

        if ($result) {
            $forbidAction = Mage::registry('am_forbidden_action')
                ? Mage::registry('am_forbidden_action')
                : $activeRules[0]['forbidden_action'];
            $cmsPage = Mage::registry('am_rule_cms_page')
                ? Mage::registry('am_rule_cms_page')
                : $activeRules[0]['cms_page'];

            $allCmsPages = Mage::helper('amgroupcat')->getCmsPages();
            $url         = 'no-route';
            if ($forbidAction == self::FORBIDDEN_ACTION_REDIRECT) {
                $ruleCmsPage = $cmsPage;
                if (array_key_exists($ruleCmsPage, $allCmsPages)) {
                    $url = Mage::getResourceModel('cms/page_collection')
                        ->addFieldToFilter('title', $allCmsPages[$ruleCmsPage])
                        ->getFirstItem()
                        ->getData('identifier');
                }
            } elseif (Mage::registry('am_forbidden_action') == self::FORBIDDEN_ACTION_404) {
                $url = '404';
            }

            $url = Mage::getBaseUrl() . $url;
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        }

        return $result;
    }

}
