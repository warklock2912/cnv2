<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Reports_Bestsellers extends Amasty_Reports_Model_Reports_Filters_Select
{
    protected $_allowedFilters = array(
        'DateFrom',
        'DateTo',
        'BestsellersCount'
    );

    protected function _getSelectedFields($filters)
    {
        return array('COUNT(*) as count',
                     'SUM(`total_item_count`) as total_item_count ',
                     'SUM(`base_grand_total`) as base_grand_total',
                     'SUM(`base_tax_amount`) as base_tax_amount',
                     'SUM(`base_shipping_amount`) as base_shipping_amount',
                     'SUM(`total_qty_ordered`) as total_qty_ordered'
        );
    }

    public function getReport($filters)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_write');
        $select = $readConnection->select();
        $helper = Mage::getResourceHelper('core');
        $select->group(array(
            'source_table.store_id',
            'order_item.product_id'
        ));
        $columns = array(
            'store_id'               => 'source_table.store_id',
            'product_id'             => 'order_item.product_id',
            'product_name'           => new Zend_Db_Expr(
                sprintf('MIN(%s)',
                    $readConnection->getIfNullSql('product_name.value','product_default_name.value')
                )
            ),
            'product_price'          => new Zend_Db_Expr(
                sprintf('%s * %s',
                    $helper->prepareColumn(
                        sprintf('MIN(%s)',
                            $readConnection->getIfNullSql(
                                $readConnection->getIfNullSql('product_price.value','product_default_price.value'),0)
                        ),
                        $select->getPart(Zend_Db_Select::GROUP)
                    ),
                    $helper->prepareColumn(
                        sprintf('MIN(%s)',
                            $readConnection->getIfNullSql('source_table.base_to_global_rate', '0')
                        ),
                        $select->getPart(Zend_Db_Select::GROUP)
                    )
                )
            ),
            'qty_ordered' => new Zend_Db_Expr('SUM(order_item.qty_ordered)')
        );
        $select
            ->from(
                array(
                    'source_table' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
                $columns)
            ->joinInner(
                array(
                    'order_item' => Mage::getSingleton('core/resource')->getTableName('sales/order_item')),
                'order_item.order_id = source_table.entity_id',
                array()
            );

        /** @var Mage_Catalog_Model_Resource_Product $product */
        $product  = Mage::getResourceSingleton('catalog/product');
        $productTypes = explode(',',$filters['ProductTypes'][0]);
        $this->_addProductTypes($readConnection, $productTypes, $product, $select);
        if (!isset($filters['StoreSelect'])) {
            $filters['StoreSelect'] = array(0);
        }
        $storeIds = implode(',', array_filter($filters['StoreSelect']));
        $this->_addAttributeJoin($readConnection,'price',$product,$select);
        $this->_addAttributeJoin($readConnection,'name',$product,$select);
        $select->order('SUM(order_item.qty_ordered) DESC');
        $this->_addStoreSelect('source_table',$select, $readConnection, $storeIds);
        $filters = $this->_prepareFields($filters);
        $this->_applyFilters('source_table', $select, $readConnection, $filters);
        $results = $readConnection->fetchAll( $select );
        return $results;
    }

    protected function _prepareFields($filters)
    {
        $filters = parent::_prepareFields($filters);
        if (empty($filters['BestsellersCount'])) $filters['BestsellersCount'] = 20;
        return $filters;
    }

    protected function _addProductTypes($readConnection, $productTypes, $product, $select)
    {
        $joinExpr = array(
            'product.entity_id = order_item.product_id',
            $readConnection->quoteInto('product.entity_type_id = ?', $product->getTypeId()),
        );
        $productTypes = array_filter($productTypes);
        if (count($productTypes)>0){
            $joinExpr[] = $readConnection->quoteInto('product.type_id IN(?)', $productTypes);
        }

        $joinExpr = implode(' AND ', $joinExpr);
        $select->joinInner(
            array(
                'product' => Mage::getSingleton('core/resource')->getTableName('catalog/product')),
            $joinExpr,
            array()
        );
    }

    protected function _addBestsellersCount($tableName, $select, $readConnection, $filter)
    {
        $select->limit((int)$filter);
    }

    protected function _addAttributeJoin($readConnection, $attrCode, $product, $select)
    {
        $attr = $product->getAttribute($attrCode);
        $joinExprProductAttr    = array(
            'product_'.$attrCode.'.entity_id = product.entity_id',
            'product_'.$attrCode.'.store_id = source_table.store_id',
            $readConnection->quoteInto('product_'.$attrCode.'.entity_type_id = ?', $product->getTypeId()),
            $readConnection->quoteInto('product_'.$attrCode.'.attribute_id = ?', $attr->getAttributeId())
        );
        $joinExprProductAttr    = implode(' AND ', $joinExprProductAttr);
        $joinExprProductDefAttr = array(
            'product_default_'.$attrCode.'.entity_id = product.entity_id',
            $readConnection->quoteInto('product_default_'.$attrCode.'.entity_type_id = ?', $product->getTypeId()),
            $readConnection->quoteInto('product_default_'.$attrCode.'.attribute_id = ?', $attr->getAttributeId())
        );

        $joinExprProductDefAttr = implode(' AND ', $joinExprProductDefAttr);
        $select->joinLeft(
            array('product_'.$attrCode => $attr->getBackend()->getTable()),
            $joinExprProductAttr,
            array()
        )->joinLeft(
                array('product_default_'.$attrCode => $attr->getBackend()->getTable()),
                $joinExprProductDefAttr,
                array()
            );
    }

    public function getReportFields()
    {
        return array('ReportName','DateFrom', 'DateTo', 'BestsellersCount', 'ProductTypes','OrderStatus','StoreSelect');
    }
}