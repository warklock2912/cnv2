<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2016 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.5.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Configurable
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    /** @var  string  */
    protected $_productTable;
    /** @var  string */
    protected $_superAttributeTable;
    /** @var  string */
    protected $_superAttributePricingTable;
    /** @var  string */
    protected $_superAttributeLabelTable;
    /** @var  string */
    protected $_superLinkTable;
    /** @var  string */
    protected $_productRelationTable;

    protected $_assignedProductData;
    protected $_configAttributes;
    protected $_configAttributePrices;

    protected $_availableConfigAttributes;

    protected $_attributeOptions;


    protected function _construct()
    {
        $this->_productTable   = $this->_config->getResource()->getTableName('catalog/product');
        $this->_superAttributeTable    = $this->_config->getResource()->getTableName('catalog/product_super_attribute');
        $this->_superAttributePricingTable = $this->_config->getResource()->getTableName('catalog/product_super_attribute_pricing');
        $this->_superAttributeLabelTable = $this->_config->getResource()->getTableName('catalog/product_super_attribute_label');
        $this->_superLinkTable = $this->_config->getResource()->getTableName('catalog/product_super_link');
        $this->_productRelationTable = $this->_config->getResource()->getTableName('catalog/product_relation');
        $this->_assignedProductData = array();
        $this->_configAttributes = array();
        $this->_configAttributePrices = array();
        $this->_attributeOptions = array();

        $this->_initConfigurableAttributes();

        parent::_construct();
    }

    protected function _initConfigurableAttributes()
    {
        /** @var $attributeCollection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributeCollection->addFieldToFilter('frontend_input', array('in' => array('select')));
        $attributeCollection->addFieldToSelect('*');
        /** @var Mage_Catalog_Model_Product_Type_Configurable $configurableType */
        $configurableType = Mage::getModel('catalog/product_type_configurable');
        $this->_availableConfigAttributes = array();
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
        foreach ($attributeCollection as $attribute) {
            if ($configurableType->canUseAttribute($attribute)) {
                $this->_availableConfigAttributes[$attribute->getAttributeCode()] = $attribute->getId();
            }
        }
    }

    protected function _getAttributeOptions($attributeCode) {
        if (!isset($this->_attributeOptions[$attributeCode])) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attribute = Mage::getResourceModel('catalog/eav_attribute');
            $attribute->loadByCode('catalog_product', $attributeCode);
            /** @var Mage_Eav_Model_Entity_Attribute $attribute */
            $this->_attributeOptions[$attributeCode] =
                $this->_helper->arrayToOptionHash(
                    $attribute->getSource()->getAllOptions(),
                    'label',
                    'value',
                    false
                );
        }

        return $this->_attributeOptions[$attributeCode];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function processData(array &$data)
    {
        if (!empty($data['associated_products'])) {
            $this->_assignedProductData[$data['sku']] = explode(',', $data['associated_products']);
        }
        if (!empty($data['configurable_attributes']) &&  $data['type_id'] == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $this->_configAttributes[$data['sku']] = explode(',', $data['configurable_attributes']);
        }
        if (!empty($data['configurable_pricing'])) {
            $this->_configAttributePrices[$data['sku']] = explode('|', $data['configurable_pricing']);
        }

        return $this;
    }

    /**
     * @param $skuToId
     * @return $this
     * @throws EcommerceTeam_Dataflow_Model_Import_Adapter_Exception
     */
    public function afterProcess($skuToId)
    {
        if (!empty($this->_configAttributes)) {
            $websites = $this->_config->getWebsites();
            foreach ($this->_configAttributes as $sku => $configAttributes) {
                $this->_writeConnection->delete($this->_superAttributeTable,
                                            $this->_writeConnection->quoteInto('product_id = ?', $skuToId[$sku]));
                $supperAttributeIds = array();
                foreach ($configAttributes as $position => $configAttribute) {
                    if (!isset($this->_availableConfigAttributes[$configAttribute])) {
                        throw new EcommerceTeam_Dataflow_Model_Import_Adapter_Exception(sprintf('Attribute %s is not configurable', $configAttribute));
                    }
                    $attributeId = $this->_availableConfigAttributes[$configAttribute];
                    $values = array(
                        'product_id'          => $skuToId[$sku],
                        'attribute_id'        => $attributeId,
                        'position'            => $position,
                    );
                    $this->_writeConnection->insert($this->_superAttributeTable, $values);
                    $supperAttributeIds[$configAttribute] = $this->_writeConnection->lastInsertId();
                }
                $this->_writeConnection->update($this->_productTable, array('has_options' => 1),
                                            $this->_writeConnection->quoteInto('entity_id = ?', $skuToId[$sku]));
                if (!empty($this->_configAttributePrices[$sku])) {
                    foreach ($this->_configAttributePrices[$sku] as $superAttribute) {
                        list($attributeCode, $optionsData) = explode(':', $superAttribute, 2);
                        if (isset($supperAttributeIds[$attributeCode])) {
                            $optionsData = explode(",", $optionsData);
                            foreach ($optionsData as $optionData) {
                                $optionData = substr($optionData, 1, strlen($optionData) - 2);
                                $optionData = explode(';', $optionData);
                                $availableOptions = $this->_getAttributeOptions($attributeCode);
                                if (isset($availableOptions[$optionData[0]])) {
                                    $websiteId = 0;
                                    if (isset($optionData[3]) && isset($websites[$optionData[3]])) {
                                        $websiteId = $websites[$optionData[3]];
                                    }
                                    $values = array(
                                        'product_super_attribute_id'    => $supperAttributeIds[$attributeCode],
                                        'value_index'                   => $availableOptions[$optionData[0]],
                                        'is_percent'                    => $optionData[2] === 'p',
                                        'pricing_value'                 => $optionData[1],
                                        'website_id'                    => $websiteId
                                    );
                                    $this->_writeConnection->insert($this->_superAttributePricingTable, $values);
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($this->_assignedProductData)) {
            foreach ($this->_assignedProductData as $sku => $assignedProducts) {
                $this->_writeConnection->delete($this->_superLinkTable,
                                            $this->_writeConnection->quoteInto('parent_id = ?', $skuToId[$sku]));
                $this->_writeConnection->delete($this->_productRelationTable,
                                            $this->_writeConnection->quoteInto('parent_id = ?', $skuToId[$sku]));
                foreach ($assignedProducts as $assignedProduct) {
                    if (!isset($skuToId[$assignedProduct])) {
                        throw new EcommerceTeam_Dataflow_Model_Import_Adapter_Exception(sprintf('Associated sku "%s" is not found for %s', $assignedProduct, $sku));
                    }
                    $values = array(
                        'product_id'          => $skuToId[$assignedProduct],
                        'parent_id'           => $skuToId[$sku],
                    );
                    $this->_writeConnection->insert($this->_superLinkTable, $values);
                    $values = array(
                        'parent_id'         => $skuToId[$sku],
                        'child_id'          => $skuToId[$assignedProduct],
                    );
                    $this->_writeConnection->insert($this->_productRelationTable, $values);
                }
            }
        }

        return parent::afterProcess($skuToId);
    }
}