<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_asyncindex
 * @version   1.1.13
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


class Mirasvit_AsyncIndex_Model_Validator_ProductFlat extends Mirasvit_AsyncIndex_Model_Validator_Abstract
{
    /**
     * Валидируем индекс продуктов, путем сравнения updated_at
     * в таблице catalog_product_entity и catalog_product_flat_x
     * 
     * Если даты отличаються, добавляет элемент в очередь
     * 
     * Максимальное возможное кол-во добавленных элементов в
     * очередь указываеться в Batch Size
     * 
     * ! Работает только если включен Flat Catalog для продуктов
     * 
     * @return object
     */
    public function validate()
    {
        // если отключен флет каталог
        if (!Mage::getStoreConfigFlag(Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT)) {
            return $this;
        }

        $resource     = Mage::getSingleton('core/resource');
        $adapter      = $resource->getConnection('core_write');
        $status       = $this->_getAttribute('status');
        $stores       = Mage::app()->getStores();
        $invalidCount = 0;

        foreach ($stores as $store) {
            $storeId   = $store->getId();
            $websiteId = (int)Mage::app()->getStore($storeId)->getWebsite()->getId();
            $flatTable = sprintf('%s_%s', $resource->getTableName('catalog/product_flat'), $storeId);
            $bind      = array(
                'website_id'     => $websiteId,
                'store_id'       => $storeId,
                'entity_type_id' => (int)$status->getEntityTypeId(),
                'attribute_id'   => (int)$status->getId()
            );

            $fieldExpr = $this->_getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
            $select = $adapter->select()
                ->from(array('e' => $resource->getTableName('catalog/product')),
                    array('entity_id', 'updated_at', 'type_id'))
                ->join(
                    array('wp' => $resource->getTableName('catalog/product_website')),
                    'e.entity_id = wp.product_id AND wp.website_id = :website_id',
                    array())
                ->joinLeft(
                    array('t1' => $status->getBackend()->getTable()),
                    'e.entity_id = t1.entity_id',
                    array())
                ->joinLeft(
                    array('t2' => $status->getBackend()->getTable()),
                    't2.entity_id = t1.entity_id'
                        .' AND t1.entity_type_id = t2.entity_type_id'
                        .' AND t1.attribute_id = t2.attribute_id'
                        .' AND t2.store_id = :store_id',
                    array())
                ->joinLeft(
                    array('flat' => $flatTable),
                    'e.entity_id = flat.entity_id',
                    array('updated_at'))
                ->where('flat.updated_at <> e.updated_at OR flat.updated_at IS NULL')
                ->where('t1.entity_type_id = :entity_type_id')
                ->where('t1.attribute_id = :attribute_id')
                ->where('t1.store_id = ?', Mage_Core_Model_App::ADMIN_STORE_ID)
                ->where("{$fieldExpr} = ?", Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->limit(intval(Mage::getStoreConfig('asyncindex/general/queue_batch_size')));

            $countSelect = clone $select;
            $countSelect->reset(Zend_Db_Select::ORDER);
            $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->columns('COUNT(*)');
            $invalidCount = max($invalidCount, intval($adapter->fetchOne($countSelect, $bind)));
            
            $result = $adapter->fetchAll($select, $bind);
            foreach ($result as $row) {
                $entityId = $row['entity_id'];

                $product = Mage::getModel('catalog/product');
                $product->setForceReindexRequired(1)
                        ->setIsChangedCategories(1)
                        ->setTypeId($row['type_id'])
                        ->setId($entityId);

                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                Mage::getSingleton('index/indexer')->logEvent(
                    $stockItem, Mage_CatalogInventory_Model_Stock_Item::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
                );

                Mage::getSingleton('index/indexer')->logEvent(
                    $product, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
                );
            }

            break;
        }

        Mage::helper('asyncindex')->setVariable('invalid_product_count', $invalidCount);

        return $this;
    }
}