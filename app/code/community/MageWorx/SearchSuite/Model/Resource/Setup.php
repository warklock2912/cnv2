<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup {

    public function rebuildIndex() {
        // for can reindex
        Mage::app()->reinitStores();
        Mage::app()->getStore(null)->resetConfig();
        // reindex
        Mage::getModel('catalogsearch/fulltext')->rebuildIndex();
    }

    public function updateAttributes() {
        $priority = array(
            'name' => 1,
            'sku' => 3,
            'manufacturer' => 3,
            'short_description' => 2,
            'description' => 4,
        );
        $search = array(
            'name',
            'sku',
            'description',
            'manufacturer'
        );
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $productAttributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
        foreach ($productAttributeCollection as $attribute) {
            $update = array();
            if (isset($priority[$attribute->getAttributeCode()])) {
                $update['quick_search_priority'] = $priority[$attribute->getAttributeCode()];
            }
            if (in_array($attribute->getAttributeCode(), $search)) {
                $update['is_attributes_search'] = '1';
            }
            if (count($update)) {
                $connection->update($tablePrefix . 'catalog_eav_attribute', $update, 'attribute_id = ' . $attribute->getId());
            }
        }
    }

}
