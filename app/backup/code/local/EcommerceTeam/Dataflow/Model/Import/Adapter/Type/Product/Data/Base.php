<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Base
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    protected $_entityTypeId;
    /** @var  array  */
    protected $_defaultStaticData = array();
    /** @var  Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection */
    protected $_attributeSetCollection;
    /** @var  array */
    protected $_attributeSetNameToId;
    /** @var  string */
    protected $_productTable;
    /** @var  string */
    protected $_websiteProductTable;

    /**
     * Initialize base data and config
     */
    protected function _construct()
    {
        $this->_entityTypeId         = $this->_getEntityResource()->getTypeId();
        $this->_productTable         = $this->_resource->getTableName('catalog/product');
        $this->_websiteProductTable  = $this->_resource->getTableName('catalog/product_website');

        /** @var $attributeSetCollection Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection */
        $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection');
        $attributeSetCollection->setEntityTypeFilter($this->_entityTypeId);
        $this->_attributeSetCollection = $attributeSetCollection;
        foreach ($this->_attributeSetCollection as $attributeSet) {
            /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
            $this->_attributeSetNameToId[$attributeSet->getAttributeSetName()] = $attributeSet->getId();
        }

        $this->_defaultStaticData = array(
            "sku"              => '',
            "entity_type_id"   => $this->_entityTypeId,
            "attribute_set_id" => $this->_getDefaultAttributeSet()->getId(),
            "type_id"          => Mage_Catalog_Model_Product_Type::DEFAULT_TYPE,
            "created_at"       => now(),
            "updated_at"       => now(),
        );
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product
     */
    protected function _getEntityResource()
    {
        /** @var $product Mage_Catalog_Model_Resource_Product */
        return Mage::getResourceSingleton('catalog/product');
    }

    /**
     * @return Varien_Object
     */
    protected function _getDefaultAttributeSet()
    {
        $defaultAttributeSetId = $this->_getEntityResource()->getEntityType()->getDefaultAttributeSetId();
        return $this->_attributeSetCollection->getItemById($defaultAttributeSetId);
    }

    /**
     * Save base product information
     *
     * @param array $data
     * @return int
     * @throws EcommerceTeam_Dataflow_Exception
     */
    public function processData(array &$data)
    {
        if (isset($data['attribute_set'])) {
            if (isset($this->_attributeSetNameToId[$data['attribute_set']])) {
                $data['attribute_set_id'] = $this->_attributeSetNameToId[$data['attribute_set']];
                unset($data['attribute_set']);
            } else {
                $this->_throwException($this->_helper->__('Unknown attribute set.'));
            }
        }
        $data['_is_new'] = false;
        if (isset($data['product_id'])) {
            $productId = $data['product_id'];
            $baseData  = $this->_helper->arrayIntersectKeys($data, $this->_defaultStaticData);
            $baseData['updated_at'] = now();
            $this->_writeConnection->update($this->_productTable, $baseData, "entity_id = {$productId}");
        } else {
            $baseData           = array_merge($this->_defaultStaticData, $data);
            $baseData           = $this->_helper->arrayIntersectKeys($baseData, $this->_defaultStaticData);
            $this->_writeConnection->insert($this->_productTable, $baseData);
            $productId          = $this->_writeConnection->lastInsertId();
            $data['product_id'] = $productId;
            $data['_is_new']    = true;
        }

        if (isset($data['website_id'])) {
            $websiteIds         = explode(',', $data['website_id']);
            $websites           = $this->_config->getWebsites();
            $data['website_id'] = array();
            foreach ($websiteIds as $websiteId) {
                if (in_array($websiteId, $websites)) {
                    $data['website_id'][] = $websiteId;
                }
            }
        }

        if (isset($data['websites'])) {
            $websiteCodes = explode(',', $data['websites']);
            $websites     = $this->_config->getWebsites();
            foreach ($websiteCodes as $websiteCode) {
                if (isset($websites[$websiteCode])) {
                    $data['website_id'][] = $websites[$websiteCode];
                }
            }
        }

        if (!isset($data['website_id']) || empty($data['website_id'])) {
            $data['website_id'][] = $this->_website->getId();
        }

        foreach ($data['website_id'] as $id) {
            $this->_writeConnection->insertOnDuplicate($this->_websiteProductTable,
                array('product_id' => $productId, 'website_id' => $id)
            );
        }

        return $this;
    }
}