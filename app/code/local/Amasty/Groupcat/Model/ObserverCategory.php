<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */
class Amasty_Groupcat_Model_ObserverCategory
{

    const FORBIDDEN_ACTION_404      = '1';
    const FORBIDDEN_ACTION_REDIRECT = '2';


    /*
     * hide category links from top menu block
     */
    public function topMenuCategoryLinksHide(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('amgroupcat/general/disable')) {
            return false;
        }

        $categoryIds = array();
        $activeRules = Mage::helper('amgroupcat')->getActiveRules(array('remove_category_links = 1'));/* active rules which have "remove_category_links" flag */
        if (!empty($activeRules)) {
            foreach ($activeRules as $rule) {
                $ids = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
                if($ids) {
                    $categoryIds = array_merge($ids, $categoryIds);
                }
            }

            $categoryIds = array_unique($categoryIds);
            $menu        = $observer->getEvent()->getMenu();

            $this->hideMenuItems($menu, $categoryIds);
        }

        return $this;
    }

    private function hideMenuItems($menu, $categoryIds)
    {
        $menuCollection = $menu->getChildren();
        foreach ($menuCollection as $menuItem) {
            if (in_array(substr($menuItem->getId(), 14), $categoryIds)) {
                $menuCollection->delete($menuItem);
            } elseif ($menuItem->hasChildren()) {
                $this->hideMenuItems($menuItem, $categoryIds);
            }
        }
    }


    /*
     * get ALL restrictions for current category, including all parent categories restrictions
     */
    public function checkCategoryRestrictions(Varien_Event_Observer $observer, $categoryId = false, $action = true)
    {
        if (!Mage::getStoreConfig('amgroupcat/general/disable')) {
            return false;
        }

        if (!$categoryId) {
            $action     = $observer->getEvent()->getData('controller_action')->getRequest()->getParams();
            $categoryId = isset($action['id']) ? $action['id'] : -1;
        }

        /* recursive walk and check restrictions */
        if ((int)$categoryId > 0) {
            $result = $this->checkCategoryTreeRestrictions($categoryId, $action);
        } else {
            $result = false;
        }

        if ($result) {
            $allCmsPages = Mage::helper('amgroupcat')->getCmsPages();
            $url         = 'no-route';
            if (Mage::registry('am_forbidden_action') == self::FORBIDDEN_ACTION_REDIRECT) {
                $ruleCmsPage = Mage::registry('am_rule_cms_page');
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


    /*
     * get restriction rules by recursive walk
     * from child to top parent (root) item
     */
    public function checkCategoryTreeRestrictions($categoryId, $action = true)
    {
        $result = false;
        /*
         * get restriction rules for category
         * and check current category access restriction
         * @TODO: recursive `LIKE %x%` selects - need to think about other ways to implement feature
         */
        $activeRules = Mage::helper('amgroupcat')->getActiveRules(array("allow_direct_links != 1"));
        if (!empty($activeRules)) {
            $result = $this->checkForbidRestrictions($activeRules, $action, $categoryId);
        }

        /*
         * recursively check all parent categories for any restrictions
         */
        /*
        $currentCategory = Mage::getModel('catalog/category')->load($categoryId);
        $path            = $currentCategory->getPath();  //path: 1/2/10/12
        $ids             = explode('/', $path);
        if (isset($ids[1])) {
            $topId = $ids[1];
            if ($categoryId != $topId) {
                $categoryId = Mage::getModel('catalog/category')->load($categoryId)->getParentCategory()->getId();
                // check if in ANY rule category is blocked
                $result = $result || $this->checkCategoryTreeRestrictions($categoryId, $action);
            }
        }*/

        return $result;
    }

    public function checkForbidRestrictions($rules, $action = true, $categoryId)
    {
        $result = false;

        if (!$action) {
            return $result;
        }

        if (!is_array($rules) && count($rules) < 1) {
            return $result;
        }

        foreach ($rules as $rule) {
            if (!empty($rule)) {
                $cIds = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
                if (!in_array($categoryId, $cIds)) {
                    continue;
                }
                if (!$rule['forbidden_action'] || $rule['allow_direct_links']) {
                    continue;
                }

                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                } else {
                    $groupId = 0;
                }

                $pos = strpos($rule['cust_groups'], ',' . $groupId . ',');

                if ($pos !== false) {
                    Mage::register('am_forbidden_action', $rule['forbidden_action'], true);
                    Mage::register('am_rule_cms_page', $rule['cms_page'], true);
                    $result = true;
                }
            }
        }

        return $result;
    }


    /*
     * hide categories from any navigation blocks
     */
    public function hideCategoriesFromNavigation(Varien_Event_Observer $observer)
    {
        if (!Mage::app()->getStore()->isAdmin()) {
            $categoryIds = array();
            $collection  = $observer->getEvent()->getCategoryCollection();
            $activeRules = Mage::helper('amgroupcat')->getActiveRules(array('remove_category_links = 1'));

            if (!empty($activeRules)) {
                foreach ($activeRules as $rule) {
                    $currentRuleCategoryIds = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
                    $categoryIds            = array_merge($categoryIds, $currentRuleCategoryIds);
                }
                $categoryIds = array_unique($categoryIds);
            }

            if (!empty($categoryIds)) {
                $collection->addFieldToFilter('entity_id', array('nin' => $categoryIds));
            }

            return false;
        }

        return false;
    }

    /*
     * hide categories from any navigation blocks
     */
    public function catalogCategoryFlatLoadnodesBefore(Varien_Event_Observer $observer)
    {
        if (!Mage::app()->getStore()->isAdmin()) {
            $categoryIds = array();
            $select  = $observer->getSelect();
            $activeRules = Mage::helper('amgroupcat')->getActiveRules(array('remove_category_links = 1'));

            if (!empty($activeRules)) {
                foreach ($activeRules as $rule) {
                    $currentRuleCategoryIds = Mage::helper('amgroupcat')->getRestrictedCategories($rule);
                    $categoryIds            = array_merge($categoryIds, $currentRuleCategoryIds);
                }
                $categoryIds = array_unique($categoryIds);
            }

            if (!empty($categoryIds)) {
                $select->where('entity_id NOT IN(?)', $categoryIds);
            }

            return false;
        }

        return false;
    }

}
