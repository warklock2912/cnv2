<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
abstract class MageWorx_SearchSuite_Model_Observer_Abstract {

    /**
     * Prepare synonyms column to search terms grid
     * @param type $block
     */
    protected function _prepareSynonymColumnToGrid($block) {
        $block->addColumnAfter('synonym_for', array(
            'header' => Mage::helper('mageworx_searchsuite')->__('Synonyms'),
            'align' => 'left',
            'index' => 'synonyms',
            'width' => '200px',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'mageworx_searchsuite/adminhtml_catalog_search_grid_renderer_synonyms'
                ), 'popularity');
        $block->sortColumnsByOrder();
    }

    /**
     * Register synonyms collection for renderer
     * @param type $collection
     */
    protected function _registerSynonymsCollection($collection) {
        $query_text = array();
        foreach ($collection as $item) {
            $query_text[] = $item->getQueryText();
        }
        $model = Mage::getSingleton('catalogsearch/query');
        $synonyms = $model->getResource()->getSynonymsForQueries($query_text);
        Mage::register('synonyms_collection', $synonyms);
    }

    protected function _prepareSynonymColumnToForm($block) {
        $form = $block->getForm();
        if ($form) {
            $fieldset = $form->getElements()->searchById('base_fieldset');
            $fieldset->addField('synonyms', 'textarea', array(
                'name' => 'synonyms',
                'label' => Mage::helper('mageworx_searchsuite')->__('Synonyms'),
                'title' => Mage::helper('mageworx_searchsuite')->__('Synonyms'),
                'note' => Mage::helper('mageworx_searchsuite')->__('Multiple synonyms should be separated by comma.'),
            ));

            $model = Mage::registry('current_catalog_search');
            $data = $model->getData();
            if (!isset($data['synonyms'])) {
                $synonyms = Mage::getResourceModel('mageworx_searchsuite/synonym_collection');
                $synonyms->addFilter('query_id', $model->getId());
                $list = array();
                foreach ($synonyms as $synonym) {
                    $list[] = $synonym->getSynonym();
                }
                $data['synonyms'] = implode(',', $list);
            }
            $form->addValues($data);
        }
    }

    protected function _removeSynonymForColumnFromForm($block) {
        $form = $block->getForm();
        if ($form) {
            $fieldset = $form->getElements()->searchById('base_fieldset');
            $fieldset->removeField('synonym_for');
        }
    }

    protected function _removeSynonymForColumnFromGrid($block) {
        if ($block && method_exists($block, 'removeColumn')) {
            $block->removeColumn('synonym_for');
        }
    }

    protected function _prepareStaticBlockColumnToForm($block) {
        $form = $block->getForm();
        if ($form) {
            $values = array(array('value' => '', 'label' => ''));
            $collection = Mage::getResourceModel('cms/block_collection');
            foreach ($collection as $item) {
                $values[] = array('value' => $item->getIdentifier(), 'label' => $item->getTitle());
            }
            $model = Mage::registry('current_catalog_search');
            $data = $model->getData();
            $fieldset = $form->getElements()->searchById('base_fieldset');
            $fieldset->addField('static_block', 'select', array(
                'name' => 'static_block',
                'label' => Mage::helper('mageworx_searchsuite')->__('Static Block'),
                'title' => Mage::helper('mageworx_searchsuite')->__('Static Block'),
                'values' => $values,
            ));
            $form->addValues($data);
        }
    }

    /**
     * Prepare tracing columns to search report form
     * @param type $block
     */
    protected function _prepareTrackingColumnsToForm($block) {
        $form = $block->getForm();
        if ($form) {
            $fieldset = $form->getElements()->searchById('base_fieldset');
            $fieldset->addType('purchase', 'MageWorx_SearchSuite_Block_Adminhtml_Report_Search_Form_Element_Purchase');
            $fieldset->addType('region', 'MageWorx_SearchSuite_Block_Adminhtml_Report_Search_Form_Element_Region');
            $fieldset->addField('purchase', 'purchase', array(
                'name' => 'orders',
                'label' => Mage::helper('mageworx_searchsuite')->__('Orders'),
                'title' => Mage::helper('mageworx_searchsuite')->__('Orders'),
            ));
            $fieldset->addField('region', 'region', array(
                'name' => 'region',
                'label' => Mage::helper('mageworx_searchsuite')->__('Countries'),
                'title' => Mage::helper('mageworx_searchsuite')->__('Countries'),
            ));
        }
    }

    /**
     * Prepare tracking columns to report search grid
     * @param type $block
     */
    protected function _prepareTrackingColumnsToGrid($block) {
        $block->addColumnAfter('purchase', array(
                    'header' => Mage::helper('mageworx_searchsuite')->__('# Purchases'),
                    'align' => 'right',
                    'index' => 'purchase',
                    'type' => 'number',
                    'width' => '100px',
                        ), 'popularity')
                ->addColumnAfter('revenue', array(
                    'header' => Mage::helper('mageworx_searchsuite')->__('Revenue'),
                    'align' => 'right',
                    'index' => 'real_revenue',
                    'type' => 'number',
                    'width' => '100px',
                    'renderer' => 'mageworx_searchsuite/adminhtml_report_search_grid_renderer_revenue',
                        ), 'purchase')
                ->addColumnAfter('order_hits', array(
                    'header' => Mage::helper('mageworx_searchsuite')->__('Orders/Hits, %'),
                    'align' => 'right',
                    'index' => 'orders_hits',
                    'type' => 'number',
                    'width' => '100px',
                    'renderer' => 'mageworx_searchsuite/adminhtml_report_search_grid_renderer_ordersHits',
                        ), 'revenue')
                ->addColumnAfter('view_hits', array(
                    'header' => Mage::helper('mageworx_searchsuite')->__('Views/Hits, %'),
                    'align' => 'right',
                    'index' => 'views_hits',
                    'type' => 'number',
                    'width' => '100px',
                    'renderer' => 'mageworx_searchsuite/adminhtml_report_search_grid_renderer_viewsHits',
                        ), 'order_hits')
                ->addColumnAfter('country', array(
                    'header' => Mage::helper('mageworx_searchsuite')->__('# Country'),
                    'align' => 'right',
                    'index' => 'country',
                    'type' => 'number',
                    'width' => '100px',
                        ), 'view_hits');
    }

    protected function _shellRequest($collection) {
        $where = $collection->getSelect()->getPart(Zend_Db_Select::SQL_WHERE);
        if (is_array($where) && count($where)) {
            //$collection->getSelect()->reset('where')->setPart(Zend_Db_Select::SQL_HAVING,$where); //a good solution, but not for Magento :/
            $select = $collection->getSelect()->reset('where')->assemble();
            $collection->getSelect()->reset()->from(array('main_table' => new Zend_Db_Expr('(' . $select . ')')), '*')->setPart(Zend_Db_Select::SQL_WHERE, $where);
        }
    }

    /**
     * Prepare tracking columns to search query collection
     * @param type $collection
     */
    protected function _prepareTrackingsToCollection($collection) {
        $collection->getSelect()
                ->joinLeft(array('p_tracking' => $collection->getTable('mageworx_searchsuite/purchase_tracking')), 'main_table.query_id = p_tracking.query_id', array('purchase' => new Zend_Db_Expr('COUNT(p_tracking.order_id)'),
                    'revenue' => new Zend_Db_Expr('SUM(p_tracking.price)'),
                    'orders_hits' => new Zend_Db_Expr('100*COUNT(p_tracking.order_id)/main_table.popularity')))
                ->joinLeft(array('orders' => $collection->getTable('sales/order')), 'orders.entity_id = p_tracking.order_id', array('status', 'real_revenue' => new Zend_Db_Expr("SUM(if(`orders`.`status`='complete',p_tracking.price,0))")))
                ->joinLeft(array('c_tracking' => $collection->getTable('mageworx_searchsuite/conversion_tracking')), 'main_table.query_id = c_tracking.query_id', array('views_hits' => new Zend_Db_Expr('100*COUNT(c_tracking.product_id)/main_table.popularity')))
                ->joinLeft(array('r_tracking' => $collection->getTable('mageworx_searchsuite/region_tracking')), 'main_table.query_id = r_tracking.query_id', array('country' => new Zend_Db_Expr('COUNT(r_tracking.country)')))
                ->group('main_table.query_id');

        $this->_shellRequest($collection);
    }

    protected function _changeProductRelevance($collection) {
        $order = $collection->getSelect()->getPart(Zend_Db_Select::ORDER);
        foreach ($order as $key => $item) {
            if ($item[0] == 'relevance') {
                $order[$key][0] = 'corrected_relevance';
                $collection->getSelect()
                        ->joinLeft(array('attributes' => $collection->getTable('eav/attribute')), "attributes.attribute_code='product_search_priority'", array())
                        ->joinLeft(array('decimal_attributes' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_decimal')), 'decimal_attributes.attribute_id=attributes.attribute_id AND e.entity_id=decimal_attributes.entity_id', array(
                            'product_search_priority' => 'decimal_attributes.value',
                            'corrected_relevance' => new Zend_Db_Expr('(IF(decimal_attributes.value <= 0 OR decimal_attributes.value IS NULL, 1,decimal_attributes.value)*relevance)')))
                        ->setPart(Zend_Db_Select::ORDER, $order);
                break;
            }
        }
    }

}
