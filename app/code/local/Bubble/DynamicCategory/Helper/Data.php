<?php
/**
 * @category    Bubble
 * @package     Bubble_DynamicCategory
 * @version     2.4.2
 * @copyright   Copyright (c) 2015 BubbleShop (https://www.bubbleshop.net)
 */
class Bubble_DynamicCategory_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param Varien_Object $category
     * @return array
     */
    public function getDynamicProductIds(Varien_Object $category)
    {
        if (!$category->getId()) {
            return array();
        }

        $ids = array();
        $conds = $category->getDynamicProductsConds();
        if (is_string($conds)) {
            $conds = unserialize($conds);
        }
        if (!empty($conds)) {
            /** @var Mage_Catalog_Helper_Product_Flat $helper */
            $process = Mage::helper('catalog/product_flat')->getProcess();
            $status = $process->getStatus();
            $process->setStatus(Mage_Index_Model_Process::STATUS_RUNNING);

            /** @var $rule Bubble_DynamicCategory_Model_Rule */
            $rule = Mage::getModel('dynamic_category/rule');
            $rule->setWebsiteIds(array_keys(Mage::app()->getWebsites(true)));
            $rule->loadPost(array('conditions' => $conds));

            Varien_Profiler::start('DYNAMIC CATEGORY MATCHING PRODUCTS');
            $productIds = $rule->getMatchingProductIds();
            Varien_Profiler::stop('DYNAMIC CATEGORY MATCHING PRODUCTS');

            foreach ($productIds as $productId => $validationByWebsite) {
                if (false !== array_search(1, $validationByWebsite, true)) {
                    $ids[] = $productId;
                }
            }

            foreach ($conds as $cond) {
                if (isset($cond['type']) && $cond['type'] == 'dynamic_category/rule_condition_product_parent') {
                    $strategy = $cond['value'];
                    $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
                    $parentIds = $adapter->fetchPairs(
                        $adapter->select()
                            ->from(
                                Mage::getResourceSingleton('catalog/product_relation')->getMainTable(),
                                array('child_id', 'parent_id')
                            )
                            ->where('child_id IN (?)', $ids)
                    );
                    $parentIds = array_map('intval', $parentIds);
                    if ($strategy == 'keep_orphans') {
                        $ids = array_unique(array_merge($parentIds, array_keys(array_diff_key(array_flip($ids), $parentIds))));
                    } else {
                        // Remove orphans
                        $ids = $parentIds;
                    }

                    break;
                }
            }

            $process->setStatus($status);
        }

        return $ids;
    }

    /**
     * @param Varien_Object $category
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getDynamicProductCollection(Varien_Object $category)
    {
        $productIds = $this->getDynamicProductIds($category);
        $collection = Mage::getResourceModel('catalog/product_collection');
        if (!empty($productIds)) {
            $collection->addIdFilter($productIds);
        } else {
            $collection->addIdFilter(array(0)); // Workaround for empty collection
        }

        return $collection;
    }

    /**
     * @param Varien_Object $category
     * @param $storeId
     * @return int
     */
    public function getCategoryProductCount(Varien_Object $category, $storeId)
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addIdFilter($category->getId())
            ->setProductStoreId($storeId)
            ->setLoadProductCount(true)
            ->setStoreId($storeId);

        return (int) $collection->getFirstItem()->getProductCount();
    }

    /**
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return Mage::getStoreConfigFlag('dynamic_category/general/enable_reindex_log');
    }
}
