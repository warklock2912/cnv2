<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */
class Amasty_Groupcat_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PREFIX_CSS_SELECTOR = 'selectors/';
    const XML_PATH_MODULE_PREFIX = 'amgroupcat/';
    /**
     * Return css selector by name in config
     * @param string $name
     * @return mixed
     */
    public function getCssSelector($name)
    {
        $xmlPath = self::XML_PATH_PREFIX_CSS_SELECTOR . $name;
        return $this->getStoreConfig($xmlPath);
    }

    /**
     * Return module config
     * @param string $path without module prefix
     * @return mixed
     */
    public function getStoreConfig($path)
    {
        $xmlPath = self::XML_PATH_MODULE_PREFIX . $path;
        return Mage::getStoreConfig($xmlPath);
    }

    /*
     * get list of customer groups for adminhtml settings
     */
    public function getCustomerGroups()
    {
        $customerGroup = array();

        $customer_group = new Mage_Customer_Model_Group();
        $allGroups      = $customer_group->getCollection()->toOptionHash();
        foreach ($allGroups as $key => $allGroup) {
            $customerGroup[$key] = array('value' => $key, 'label' => $allGroup);
        }

        return $customerGroup;
    }


    /*
     * get list of CMS pages for adminhtml settings
     */
    public function getCmsPages()
    {
        $cmsPages = array();

        $pageCollection = Mage::getModel('cms/page')->getCollection()->addFieldToFilter('is_active', '1')->getData();

        foreach ($pageCollection as $page) {
            $cmsPages[$page['page_id']] = $page['title'];
        }

        asort($cmsPages);

        return $cmsPages;
    }


    /*
     * get list of CMS blocks for adminhtml settings
     */
    public function getCmsBlocks()
    {
        $cmsBlocks     = array();
        $cmsBlocks[-1] = '- Do not replace -';

        $pageCollection = Mage::getModel('cms/block')->getCollection()->addFieldToFilter('is_active', '1')->getData();

        foreach ($pageCollection as $page) {
            $cmsBlocks[$page['block_id']] = $page['title'];
        }

        asort($cmsBlocks);

        return $cmsBlocks;
    }


    /*
     *  get active rules for current customer
     */
    public function getActiveRules($params = false)
    {
        // only for logged in customers segments are available
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $segments = $this->getSegmentsForCustomer();
        } else {
            $segments = array();
        }

        // get customer group
        $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        // get rules
        $activeRules = Mage::getModel('amgroupcat/rules')->getActiveRules($groupId, $segments, $params);

        return $activeRules;
    }


    /*
     *  get active rules for current product
     */

    public function getSegmentsForCustomer()
    {
        $result = array();

        // customer email
        $sessionEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        $quoteEmail   = Mage::getSingleton('checkout/session')->getQuote()->getCustomer()->getEmail();
        $email        = $sessionEmail ? $sessionEmail : $quoteEmail;

        if (Mage::getConfig()->getNode('modules/Amasty_Segments/active') && !empty($email)) {
            $select   = Mage::getModel("amsegments/index")
                            ->getCollection()
                            ->addFieldToFilter('customer.customer_email', array('eq' => $email))
                            ->addFieldToFilter('main_table.parent', array('eq' => ""))
                            ->addFieldToFilter('main_table.result', array('eq' => 1))
                            ->getSelect()
                            ->joinLeft(
                                array('customer' => Mage::getSingleton('core/resource')->getTableName('amsegments/customer')),
                                'customer.entity_id = main_table.customer_id',
                                array("customer.customer_email")
                            )
                            ->group("segment_id");
            $segments = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($select);
            if ($segments) {
                foreach ($segments as $segment) {
                    $result[] = $segment['segment_id'];
                }
            }
        }

        return $result;
    }

    /*
     *  get active rules for hiding for current product
     */

    public function getActiveRulesForProduct($productId, $params = false)
    {
        $session     = Mage::getSingleton('customer/session');
        $groupId     = $session->getCustomerGroupId();
        $activeRules = Mage::getModel('amgroupcat/rules')->getActiveRulesForProduct($productId, $groupId, $params);

        return $activeRules;
    }

    /**
     *  get active rules for hiding price for current product
     *
     * @param            $productId
     * @param bool       $params
     *
     * @return mixed
     */
    public function getActiveRulesForProductPrice($product, $params = false)
    {
        $session     = Mage::getSingleton('customer/session');
        $groupId     = $session->getCustomerGroupId();
        $activeRules = Mage::getModel('amgroupcat/rules')->getActiveRulesForProductPrice(
            $product->getId(),
            $groupId,
            $params
        );

        /*
         * apply category rules
         */
        $productCategories = $product->getCategoryIds();
        $activeCategoryRules = $this->getActiveRules(array('hide_price = 1'));
        $currentCategory = Mage::registry('current_category')? Mage::registry('current_category')->getId(): 0;
        if (is_array($activeCategoryRules) && count($activeCategoryRules) > 0) {
            foreach ($activeCategoryRules as $rule) {
                $ruleCategories = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
                if($currentCategory && in_array($currentCategory, $productCategories) && in_array($currentCategory, $ruleCategories) ){
                    array_push($activeRules, $rule);
                }
            }
        }

        return $activeRules;
    }

    /**
     * @return array
     */
    public function getSegmentsValuesForForm()
    {
        $options = array();

        $segments = Mage::getModel('amsegments/segment')->getCollection()->filterByStatus()->getData();
        if ($segments) {
            foreach ($segments as $segment) {
                $options[] = array(
                    'label' => $segment['name'],
                    'value' => $segment['segment_id']
                );
            }
        }

        return $options;
    }

    public function getRestrictedCategories($data)
    {
        $categoryIds = trim($data['categories'], ',');
        $categoryIds = explode(',', $categoryIds);
        if(array_key_exists('category_restricted_type', $data) && $data['category_restricted_type'] == 1) {
            $category = Mage::getModel('catalog/category');
            $catTree = $category->getTreeModel()->load();
            $catIds = $catTree->getCollection()->getAllIds();
            $categoryIds = array_diff($catIds, $categoryIds);
        }

        return $categoryIds;
    }
}
