<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Tracking_Purchase_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('mageworx_searchsuite/tracking_purchase');
    }

    public function setOrderFilter($order) {
        $orderId = 0;
        if (is_object($order)) {
            $orderId = $order->getId();
        } else {
            $orderId = $order;
        }

        $this->getSelect()->where('order_id = ?', $orderId);
        return $this;
    }

    public function setQueryFilter($query) {
        $queryId = 0;
        if (is_object($query)) {
            $queryId = $query->getId();
        } else {
            $queryId = $query;
        }

        $this->getSelect()->where('query_id = ?', $queryId);
        return $this;
    }

    public function addQueryToSelect() {
        $this->getSelect()
                ->joinLeft(array('search_query' => $this->getTable('catalogsearch/search_query')), 'main_table.query_id = search_query.query_id', 'query_text');
        return $this;
    }

    public function addOrderToSelect() {
        $this->getSelect()
                ->joinLeft(array('orders' => $this->getTable('sales/order_grid')), 'main_table.order_id = orders.entity_id', 'increment_id');
        return $this;
    }

}
