<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Eav
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract
{
    /** @var string */
    protected $_attributeCodes;
    /** @var Mage_Catalog_Model_Resource_Product_Attribute_Collection */
    protected $_attributeCollection;
    /** @var array */
    protected $_attributeOptionCache = array();
    /** @var array  */
    protected $_requiredEavData = array();
    /** @var  Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection */
    protected $_attributeSetCollection;

    protected $_staticAttributes       = array();
    protected $_eavAttributes          = array();
    protected $_customSourceAttributes = array();//array("status", "visibility", "tax_class_id");
    protected $_attributeOptions  = array();

    protected $_stores;

    /**
     * Initialization
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_requiredEavData = array(
            "status"           => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
            "visibility"       => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            "weight"           => 0,
            "price"            => 0,
            "tax_class_id"     => 0,
        );

        /** @var $attributeSetCollection Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection */
        $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection');
        $attributeSetCollection->setEntityTypeFilter($this->_config->getEntityTypeId());
        $this->_attributeSetCollection = $attributeSetCollection;
        $this->initializeAttributes($this->_config->getAttributeCodes());

        return $this;
    }

    /**
     * Prepare data before save
     *
     * @param array $data
     * @return $this
     */
    public function prepareData(array &$data)
    {
        $this->_prepareAttributeOptions($data);

        return $this;
    }

    /**
     * Will be called when all rows prepared
     *
     * @return $this
     */
    public function afterPrepare()
    {
        if ($this->_config->getCanCreateOptions()) {
            $this->_saveAttributeOptions();
        }

        return $this;
    }

    /**
     * Initialization of necessary attributes
     *
     * @param array $attributeCodes
     * @return $this
     */
    public function initializeAttributes(array $attributeCodes)
    {
        // Ignore media attributes
        $attributeCodes = array_diff($attributeCodes,
            array('images', 'exclude_images', 'image', 'small_image', 'thumbnail', 'category_ids'));

        // Merge with required attributes
        $this->_attributeCodes = array_merge(array_keys($this->_requiredEavData), $attributeCodes);

        // Initialize attribute collection
        /** @var $attributeCollection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributeCollection->setItemObjectClass('catalog/resource_eav_attribute');
        $attributeCollection->addFieldToSelect('*');
        $attributeCollection->addFieldToFilter('attribute_code', $this->_attributeCodes);
        $this->_attributeCollection = $attributeCollection;
        $this->_initAttributeCache();

        return $this;
    }

    /**
     * @return $this
     */
    protected function _initAttributeCache()
    {
        $this->_attributeCollection->clear()->load(); // Reload collection
        $this->_staticAttributes = array();
        $this->_eavAttributes    = array();
        Mage::app()->getTranslator()->init('frontend');
        foreach ($this->_attributeCollection as $attribute) {
            /** @var $attribute Mage_Eav_Model_Attribute */
            $attribute->setStoreId($this->_store->getId());
            $attributeCode = $attribute->getAttributeCode();
            if (Mage_Eav_Model_Entity_Attribute_Abstract::TYPE_STATIC == $attribute->getBackendType()) {
                $this->_staticAttributes[$attributeCode] = $attribute->getId();
            } else {
                $this->_eavAttributes[$attributeCode] = $attribute->getId();
            }
            if ($attribute->usesSource()) {
                /** @var Mage_Eav_Model_Entity_Attribute $attribute */
                $this->_attributeOptionCache[$attributeCode] =
                    $this->_helper->arrayToOptionHash(
                        $attribute->getSource()->getAllOptions(),
                        'label',
                        'value',
                        false
                    );
            }
        }

        return $this;
    }

    /**
     * @param $attribute
     * @return mixed
     */
    protected function _getAttributeOptions($attribute)
    {
        if (!isset($this->_attributeOptions[$attribute->getId()])) {
            $this->_attributeOptions[$attribute->getId()] = array();
            $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                    ->setAttributeFilter($attribute->getId())
                    ->load();
            foreach ($collection as $option) {
                $this->_attributeOptions[$attribute->getId()]['order'][$option->getId()] = $option->getSortOrder();
            }
            $stores = $this->_getStores();
            foreach ($stores as $store) {
                $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                    ->setAttributeFilter($attribute->getId())
                    ->setStoreFilter($store->getId(), false)
                    ->load();
                foreach ($valuesCollection as $option) {
                    $this->_attributeOptions[$attribute->getId()]['store'][$option->getId()][$store->getId()] = $option->getValue();
                }
            }
        }

        return $this->_attributeOptions[$attribute->getId()];
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return Mage_Core_Model_Mysql4_Store_Collection
     */
    protected function _getStores()
    {
        $stores = $this->_stores;
        if (is_null($stores)) {
            $this->_stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
        }

        return $this->_stores;
    }

    /**
     * Prepare new options
     *
     * @param array $data
     * @return $this
     */
    protected function _prepareAttributeOptions(array &$data)
    {
        $canUpdateOptions = $this->_config->getCanCreateOptions();
        foreach ($this->_attributeOptionCache as $attributeCode => $options) {
            if (isset($data[$attributeCode]) && $data[$attributeCode]) {
                /** @var $attribute Mage_Eav_Model_Attribute */
                $attribute = $this->_attributeCollection->getItemByColumnValue('attribute_code', $attributeCode);
                if ('multiselect' !== $attribute->getData('frontend_input')) {
                    $labels = (array) $data[$attributeCode];
                } else {
                    $labels = explode($this->_config->getOptionDelimiter(), $data[$attributeCode]);
                }
                foreach ($labels as $key => $label) {
                    $label = trim(preg_replace('/\s+/', ' ', $label));
                    if ($label) {
                        $existsLabel = $this->_findExistsOptionLabel($label, array_keys($options), true);
                        if ($existsLabel) {
                            $label = $existsLabel;
                        } else {
                            $options[$label] = null;
                        }
                        $labels[$key] = $label;
                    } else {
                        unset($labels[$key]);
                    }
                }
                $data[$attributeCode] = implode($this->_config->getOptionDelimiter(), $labels);

                if ($canUpdateOptions) {
                    $this->_attributeOptionCache[$attributeCode] = $options;
                }
            }
        }

        return $this;
    }

    /**
     * Save updated options
     *
     * @return $this
     * @throws Exception
     */
    protected function _saveAttributeOptions()
    {
        $this->_writeConnection->beginTransaction();
        try {
            foreach ($this->_attributeOptionCache as $attributeCode => $options) {
                /** @var $attribute Mage_Eav_Model_Attribute */
                $attribute = $this->_attributeCollection->getItemByColumnValue('attribute_code', $attributeCode);
                if ($attribute->getSource() instanceof Mage_Eav_Model_Entity_Attribute_Source_Table) {
                    $optionsData = $this->_getAttributeOptions($attribute);
                    if (!empty($options)) {
                        $_option = array();
                        $i = 0;
                        foreach ($options as $label => $value) {
                            if ($value) {
                                // Exists option item
                                $_option['value'][$value][0] = $label;
                                $_option['order'][$value] = isset($optionsData['order'][$value]) ? $optionsData['order'][$value] : 0;
                                $stores = $this->_getStores();
                                foreach ($stores as $store) {
                                    if (isset($optionsData['store'][$value][$store->getId()])) {
                                        $_option['value'][$value][$store->getId()] = $optionsData['store'][$value][$store->getId()];
                                    }
                                }
                            } else {
                                if ($label) {
                                    // New option item
                                    $_option['value']['options_'.$i][0] = $label;
                                    $_option['order']['options_'.$i]    = 0;
                                    $i++;
                                }
                            }
                        }
                        $attribute->setData('option', $_option);
                        $attribute->getResource()->save($attribute);
                    }
                }
            }
            $this->_writeConnection->commit();
        } catch (Exception $e) {
            $this->_writeConnection->rollBack();
            throw $e;
        }
        $this->_initAttributeCache();

        return $this;
    }

    /**
     * Setting default data for system attributes if require
     *
     * @param array $data
     * @return $this
     */
    protected function _prepareEavData(array &$data)
    {
        if ($data['_is_new']) {
            if (!isset($data['visibility'])) {
                $data['visibility'] = array_search($this->_requiredEavData['visibility'], $this->_attributeOptionCache['visibility']);
            }
            if (!isset($data['status'])) {
                $data['status'] = array_search($this->_requiredEavData['status'], $this->_attributeOptionCache['status']);//$this->_attributeOptionCache['status'][$this->_requiredEavData['status']];
            }
            if (!isset($data['tax_class_id'])) {
                $data['tax_class_id'] = array_search($this->_requiredEavData['tax_class_id'], $this->_attributeOptionCache['tax_class_id']);//$this->_attributeOptionCache['tax_class_id'][$this->_requiredEavData['tax_class_id']];
            }
        }

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function processData(array &$data)
    {
        // Set default data for system attributes if require
        $this->_prepareEavData($data);

        // Update attributes
        foreach ($this->_eavAttributes as $attributeCode => $attributeId) {
            /** @var $attribute Mage_Eav_Model_Attribute */
            $attribute   = $this->_attributeCollection->getItemById($attributeId);
            $value       = isset($data[$attributeCode]) ? trim($data[$attributeCode]) : null;
            $backendType = $attribute->getBackendType();
            if (('decimal' == $backendType || 'datetime' == $backendType) && '' === $value) {
                $value = null;
            }
            if (!is_null($value)) {
                $table = $attribute->getBackendTable();
                $values = array(
                    'entity_id'      => $data['product_id'],
                    'attribute_id'   => $attributeId,
                    'entity_type_id' => $this->_config->getEntityTypeId(),
                    'store_id'       => $this->_store->getId(),
                    'value'          => $value,
                );
                if (!$data['_is_new'] && self::DELETE_VALUE_FLAG == $value) {
                    $where = array(
                        $this->_writeConnection->quoteInto('entity_id = ?', $values['entity_id']),
                        $this->_writeConnection->quoteInto('attribute_id = ?', $attributeId),
                        $this->_writeConnection->quoteInto('entity_type_id = ?', $this->_config->getEntityTypeId()),
                        $this->_writeConnection->quoteInto('store_id = ?', $values['store_id']),
                    );
                    $this->_writeConnection->delete($table, implode(' AND ', $where));
                } else {
                    if ($attribute->usesSource()) {
                        if ('multiselect' == $attribute->getData('frontend_input')) {
                            $labels     = explode($this->_config->getOptionDelimiter(), $values['value']);
                            $optionsIds = array();
                            foreach ($labels as $label) {
                                if (isset($this->_attributeOptionCache[$attributeCode][$label])) {
                                    $optionsIds[] = $this->_attributeOptionCache[$attributeCode][$label];
                                }
                            }
                            $values['value'] = implode(',', array_unique($optionsIds));
                        } else {
                            if (isset($this->_attributeOptionCache[$attributeCode][$values['value']])) {
                                $values['value'] = $this->_attributeOptionCache[$attributeCode][$values['value']];
                            }
                        }
                    }
                    if (!is_null($values['value'])) {
                        $this->_writeConnection->insertOnDuplicate($table, $values);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param string $label
     * @param array $labels
     * @param bool $returnLabel
     * @return bool
     */
    protected function _findExistsOptionLabel($label, array $labels, $returnLabel = false)
    {
        $label = preg_replace('/\W/', '', trim(strtolower($label)));
        $correctionFactor = $this->_config->getOptionCorrectionFactor();
        /** @var EcommerceTeam_Dataflow_Helper_Data $helper */
        $helper = Mage::helper('ecommerceteam_dataflow');
        foreach ($labels as $id => $_label) {
            $label2 = preg_replace('/\W/', '', strtolower($_label));
            if ($helper->compareWithLevenshtein($label, $label2, $correctionFactor)) {
                if ($returnLabel) {
                    return $_label;
                } else {
                    return $id;
                }
            }
        }

        return false;
    }

}