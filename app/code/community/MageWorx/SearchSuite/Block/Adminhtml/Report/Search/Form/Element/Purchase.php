<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Report_Search_Form_Element_Purchase extends Varien_Data_Form_Element_Abstract {

    /**
     * Retrieve search query model instance
     * @return Mage_CatalogSearch_Model_Query
     */
    public function getSearchQuery() {
        return Mage::registry('current_catalog_search');
    }

    /**
     * Get tracking collection for curent query
     * @return MageWorx_SearchSuite_Model_Mysql4_Tracking_Purchase_Collection
     */
    public function getCollection() {
        $collection = Mage::getModel('mageworx_searchsuite/tracking_purchase')->getCollection()
                ->setQueryFilter($this->getSearchQuery())
                ->addOrderToSelect();
        return $collection;
    }

    public function getElementHtml() {
        $collection = $this->getCollection();
        $html = array();
        if ($collection->count() > 0) {
            foreach ($collection as $item) {
                if ($item->getIncrementId()) {
                    $url = Mage::helper('adminhtml')->getUrl("*/sales_order/view", array('order_id' => $item->getOrderId()));
                    $html[] = '<a href="' . $url . '">' . $item->getIncrementId() . '</a>';
                }
            }
        } else {
            $html[] = '<span>' . Mage::helper('mageworx_searchsuite')->__('No orders yet') . '</span>';
        }

        return implode('<br/>' . PHP_EOL, $html);
    }

}
