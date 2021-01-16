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


class Mirasvit_AsyncIndex_Model_Validator_CategoryFlat extends Mirasvit_AsyncIndex_Model_Validator_Abstract
{
    /**
     * Валидируем индекс категорий, путем сравнения updated_at
     * в таблице catalog_category_entity и catalog_category_flat_x
     * 
     * Если даты отличаються, добавляет элемент в очередь
     * Максимальное возможное кол-во добавленных элементов
     * в очередь указываеться в Batch Size
     * 
     * ! Работает только если включен Flat Catalog для категорий
     * 
     * @return object
     */
    public function validate()
    {
        // если отключен флет каталог
        if (!Mage::getStoreConfigFlag(Mage_Catalog_Helper_Category_Flat::XML_PATH_IS_ENABLED_FLAT_CATALOG_CATEGORY)) {
            return $this;
        }

        $resource     = Mage::getSingleton('core/resource');
        $adapter      = $resource->getConnection('core_write');
        $rootId       = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        $stores       = Mage::app()->getStores();
        $invalidCount = 0;
        $bind         = array();

        foreach ($stores as $store) {
            $storeId   = $store->getId();
            $suffix    = sprintf('store_%d', $storeId);
            $flatTable = sprintf('%s_%s', $resource->getTableName('catalog/category_flat'), $suffix);

            $select = $adapter->select()
                ->from(array('e' => $resource->getTableName('catalog/category')), array('entity_id'))
                ->joinLeft(
                    array('flat' => $flatTable),
                    'e.entity_id = flat.entity_id',
                    array())
                ->where('flat.updated_at <> e.updated_at OR flat.updated_at IS NULL')
                ->where('e.path = "'.(string)$rootId.'" OR e.path = "'."{$rootId}/{$store->getRootCategoryId()}"
                        .'" OR e.path LIKE "'."{$rootId}/{$store->getRootCategoryId()}/%".'"');


            $countSelect = clone $select;
            $countSelect->reset(Zend_Db_Select::ORDER);
            $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->columns('COUNT(*)');
            $invalidCount = max($invalidCount, intval($adapter->fetchOne($countSelect, $bind)));

            $result = $adapter->fetchAll($select, array());
            foreach ($result as $row) {
                $entityId = $row['entity_id'];

                if ($entityId == 1) {
                    continue;
                }

                $category = Mage::getModel('catalog/category')->load($entityId);

                Mage::getSingleton('index/indexer')->logEvent(
                    $category, Mage_Catalog_Model_Category::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
                );
            }

            break;
        }

        if ($invalidCount > 0) {
            $invalidCount--;
        }

        Mage::helper('asyncindex')->setVariable('invalid_category_count', $invalidCount);

        return $this;
    }
}