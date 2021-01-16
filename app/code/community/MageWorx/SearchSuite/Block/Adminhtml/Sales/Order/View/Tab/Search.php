<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Block_Adminhtml_Sales_Order_View_Tab_Search extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function __construct() {
        parent::__construct();
        $this->setId('search_queries');
        $this->setUseAjax(true);
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder() {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource() {
        return $this->getOrder();
    }

    protected function _prepareColumns() {
        $this->addColumn('product_id', array(
            'header' => Mage::helper('mageworx_searchsuite')->__('Product'),
            'index' => 'product_id',
            'renderer' => 'mageworx_searchsuite/adminhtml_sales_order_view_tab_renderer_product',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('query_text', array(
            'header' => Mage::helper('mageworx_searchsuite')->__('Query Text'),
            'index' => 'query_text',
            'filter' => false,
            'sortable' => false,
        ));


        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('mageworx_searchsuite/tracking_purchase')->getCollection()
                ->setOrderFilter($this->getOrder())
                ->addQueryToSelect();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel() {
        return Mage::helper('mageworx_searchsuite')->__('Search Queries');
    }

    public function getTabTitle() {
        return Mage::helper('mageworx_searchsuite')->__('Search Queries');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

}
