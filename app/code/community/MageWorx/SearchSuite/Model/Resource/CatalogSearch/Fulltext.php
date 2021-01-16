<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_CatalogSearch_Fulltext extends Mage_CatalogSearch_Model_Mysql4_Fulltext {

    protected function _construct() {
        parent::_construct();
        $this->_engine = Mage::helper('mageworx_searchsuite')->getEngine();
    }

    protected function _rebuildStoreIndex($storeId, $productIds = null) {
        $this->cleanIndex($storeId, $productIds);

        $staticFields = array();
        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }
        $dynamicFields = array(
            'int' => array_keys($this->_getSearchableAttributes('int')),
            'varchar' => array_keys($this->_getSearchableAttributes('varchar')),
            'text' => array_keys($this->_getSearchableAttributes('text')),
            'decimal' => array_keys($this->_getSearchableAttributes('decimal')),
            'datetime' => array_keys($this->_getSearchableAttributes('datetime')),
        );
        $visibility = $this->_getSearchableAttribute('visibility');
        $status = $this->_getSearchableAttribute('status');
        $statusVals = Mage::getSingleton('catalog/product_status')->getVisibleStatusIds();
        $allowedVisibilityValues = $this->_engine->getAllowedVisibility_compatible();
//var_dump($statusVals,$allowedVisibilityValues);die(); 
        $lastProductId = 0;
		$websiteId = Mage::app()->getStore($storeId)->getWebsiteId();    
		
        while (true) {
            $products = $this->_getSearchableProducts($storeId, $staticFields, $productIds, $lastProductId);
            if (!$products) {
                break;
            }

            $productAttributes = array();
            $productRelations = array();
            foreach ($products as $productData) {
                $lastProductId = $productData['entity_id'];
                $productAttributes[$productData['entity_id']] = $productData['entity_id'];
                if(method_exists($this, '_getProductChildrenIds')){
                	$productChildren = $this->_getProductChildrenIds($productData['entity_id'], $productData['type_id'], $websiteId);                	
                }else{
                	$productChildren = $this->_getProductChildIds($productData['entity_id'], $productData['type_id']);
                }
                
                $productRelations[$productData['entity_id']] = $productChildren;
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        $productAttributes[$productChildId] = $productChildId;
                    }
                }
            }
            $productIndexes = array();
            $productAttributes = $this->_getProductAttributes($storeId, $productAttributes, $dynamicFields);

            foreach ($products as $productData) {
                if (!isset($productAttributes[$productData['entity_id']])) {
                    continue;
                }

                $productAttr = $productAttributes[$productData['entity_id']];
                // ------------- start changes
                if (!isset($productAttr[$visibility->getId()]) || !in_array($productAttr[$visibility->getId()]['value'], $allowedVisibilityValues)
                ) {
                    continue;
                }
                if (!isset($productAttr[$status->getId()]) || !in_array($productAttr[$status->getId()]['value'], $statusVals)) {
                    continue;
                }
                // ------------- end changes

                $productIndex = array(
                    $productData['entity_id'] => $productAttr
                );

                if ($productChildren = $productRelations[$productData['entity_id']]) {
                    foreach ($productChildren as $productChildId) {
                        if (isset($productAttributes[$productChildId])) {
                            $productIndex[$productChildId] = $productAttributes[$productChildId];
                        }
                    }
                }

                $index = $this->_prepareProductIndex($productIndex, $productData, $storeId);
                $productIndexes[$productData['entity_id']] = $index;
            }
            $this->_saveProductIndexes($storeId, $productIndexes);
        }
        $this->resetSearchResults();
        return $this;
    }

    protected function _getProductAttributes($storeId, array $productIds, array $attributeTypes) {
        $result = array();
        $selects = array();
        $adapter = $this->_getWriteAdapter();
        $ifStoreValue = $this->getCheckSql_compatible('t_store.value_id > 0', 't_store.value', 't_default.value');
        // ------------- start changes
        $attributeTable = $this->getTable('catalog/eav_attribute');
        // ------------- end changes   

        foreach ($attributeTypes as $backendType => $attributeIds) {
            if ($attributeIds) {
                $tableName = $this->getTable_compatible(array('catalog/product', $backendType)); // $this->getTable(array) in magento 1.7

                $selects[] = $adapter->select()
                        ->from(
                                array('t_default' => $tableName), array('entity_id', 'attribute_id'))
                        ->joinLeft(
                                array('t_store' => $tableName), $adapter->quoteInto(
                                        't_default.entity_id=t_store.entity_id' .
                                        ' AND t_default.attribute_id=t_store.attribute_id' .
                                        ' AND t_store.store_id=?', $storeId), array('value' => $this->_unifyField_compatible($ifStoreValue, $backendType))) // problem in magento 1.5
                        // ------------- start changes
                        ->joinLeft(
                                array('t_attribute' => $attributeTable), 't_attribute.attribute_id = t_default.attribute_id', array('quick_search_priority'))
                        // ------------- end changes
                        ->where('t_default.store_id=?', 0)
                        ->where('t_default.attribute_id IN (?)', $attributeIds)
                        ->where('t_default.entity_id IN (?)', $productIds);
            }
        }

        if ($selects) {
            $select = $adapter->select()->union($selects, Zend_Db_Select::SQL_UNION_ALL);
            $query = $adapter->query($select);

            while ($row = $query->fetch()) {
                // ------------- start changes
                $result[$row['entity_id']][$row['attribute_id']] = array('value' => strip_tags($row['value']), 'priority' => $row['quick_search_priority']);
                // ------------- end changes
            }
        }
        return $result;
    }

    protected function _prepareProductIndex($indexData, $productData, $storeId) {
        $index = array();

        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (isset($productData[$attributeCode])) {
                $value = $this->_getAttributeValue($attribute->getId(), $productData[$attributeCode], $storeId);
                if ($value) {
                    //For grouped products
                    if (isset($index[$attributeCode])) {
                        if (!is_array($index[$attributeCode])) {
                            $index[$attributeCode] = array($index[$attributeCode]);
                        }
                        $index[$attribute->getData('quick_search_priority')][$attributeCode][] = $value;
                    }
                    //For other types of products
                    else {
                        $index[$attribute->getData('quick_search_priority')][$attributeCode] = $value;
                    }
                }
            }
        }

        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {

                //// ------------- start changes
                $value = $this->_getAttributeValue($attributeId, $attributeValue['value'], $storeId);
                if (!is_null($value) && $value !== false) {
                    $attributeCode = $this->_getSearchableAttribute($attributeId)->getAttributeCode();
                    if (isset($index[$attributeValue['priority']][$attributeCode])) {
                        $index[$attributeValue['priority']][$attributeCode][$entityId] = $value;
                    } else {
                        $index[$attributeValue['priority']][$attributeCode] = array($entityId => $value);
                    }
                }
                // ------------- end changes
            }
        }
        if (!$this->_engine->allowAdvancedIndex()) {
            $product = $this->_getProductEmulator()
                    ->setId($productData['entity_id'])
                    ->setTypeId($productData['type_id'])
                    ->setStoreId($storeId);
            $typeInstance = $this->_getProductTypeInstance($productData['type_id']);
            if ($data = $typeInstance->getSearchableData($product)) {
                $index['options'] = $data;
            }
        }

        // ------------- start changes
        /* if (isset($productData['in_stock'])) {
          $index['in_stock'] = $productData['in_stock'];
          } */

        return $this->prepareIndexData($index, $this->_separator);
        // ------------- end changes
    }

    // prepare index for insert in database, group by priority at 5 levels
    public function prepareIndexData($index, $separator = ' ') {
        $priorityIndex = array(array(), array(), array(), array(), array(), array());
        foreach ($index as $key => $value) {
            if (is_integer($key)) {
                foreach ($value as $value2) {
                    if (!is_array($value2)) {
                        $priorityIndex[$key][] = $value2;
                    } else {
                        $priorityIndex[$key] = array_merge($priorityIndex[$key], $value2);
                    }
                }
            } else {
                if (!is_array($value)) {
                    $priorityIndex[3][] = $value;
                } else {
                    $priorityIndex[3] = array_merge($priorityIndex[3], $value);
                }
            }
        }
        foreach ($priorityIndex as $key => $value) {
            $priorityIndex[$key] = join($separator, $priorityIndex[$key]);
        }
        return $priorityIndex;
    }

    public function prepareResult($object, $queryText, $query) {
        Mage::dispatchEvent('mageworx_searchsuite_prepare_result_before', array('query' => $query));
        if (!$query->getIsProcessed()) {
            $this->_engine->prepareResultForEngine($this, $queryText, $query);
            //$query->setIsProcessed(1);
        }
        Mage::dispatchEvent('mageworx_searchsuite_prepare_result_after', array('query' => $query));
        return $this;
    }

    public function rebuildIndex($storeId = null, $productIds = null) {
        $engines = array();
        if (is_null($storeId)) {
            $storeIds = array_keys(Mage::app()->getStores());
            foreach ($storeIds as $storeId) {
                $this->_rebuildStoreIndex($storeId, $productIds);
                $ename = Mage::helper('mageworx_searchsuite')->getEngineName($storeId);
                if (empty($engines[$ename])) {
                    $engines[$ename] = Mage::helper('mageworx_searchsuite')->getEngine($storeId);
                }
            }
        } else {
            $this->_rebuildStoreIndex($storeId, $productIds);
            $engines[Mage::helper('mageworx_searchsuite')->getEngineName($storeId)] = Mage::helper('mageworx_searchsuite')->getEngine($storeId);
        }
        foreach ($engines as $name => $engine) {
            if ($name != 'xsearch' && $engine) {
                $engine->rebuildIndexForEngine($this, $storeId, $productIds);
            }
        }

        return $this;
    }

    // for magento < 1.7.0.0 from Abstract resource helper class
    public function getCheckSql_compatible($expression, $true, $false) {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new Zend_Db_Expr($expression);
    }

    // for magento < 1.7.0.0 from Abstract resource helper class
    public function getTable_compatible($entityName) {
        if (is_array($entityName)) {
            list($entityName, $entitySuffix) = $entityName;
        } else {
            $entitySuffix = null;
        }
        $entityName = parent::getTable($entityName);
        if (!is_null($entitySuffix)) {
            return $entityName . '_' . $entitySuffix;
        } else {
            return $entityName;
        }
    }

    // for magento < 1.7.0.0 from Abstract resource helper class
    protected function _unifyField_compatible($field, $backendType = 'varchar') {
        if ($backendType == 'datetime') {
            $expr = $this->_getReadAdapter()->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
        } else {
            $expr = $field;
        }
        return $expr;
    }

    // for magento < 1.7.0.0 from Abstract resource helper class
    public function getCILike_compatible($field, $value, $options = array()) {
        $quotedField = $this->_getReadAdapter()->quoteIdentifier($field);
        return new Zend_Db_Expr($quotedField . ' LIKE ' . $this->addLikeEscape_compatible($value, $options));
    }

    // for magento < 1.7.0.0 from Abstract resource helper class
    public function addLikeEscape_compatible($value, $options = array()) {
        $value = Mage::helper('mageworx_searchsuite')->escapeLikeValue($value, $options);
        return new Zend_Db_Expr($this->_getReadAdapter()->quote($value));
    }

    // for magento < 1.7.0.0 from lib Mysql PDO DB adapter
    public function insertFromSelect_compatible(Varien_Db_Select $select, $table) {
        //INSERT INTO `catalogsearch_result`  ON DUPLICATE KEY UPDATE `relevance` = VALUES(`relevance`)
        $query = 'INSERT INTO `' . $table . '` ' . $select->__toString() . ' ON DUPLICATE KEY UPDATE `relevance` = VALUES(`relevance`)';
        return $query;
    }

}
