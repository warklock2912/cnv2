<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product
    extends EcommerceTeam_Dataflow_Model_Import_Adapter_Abstract
{
    /** @var  array */
    protected $_dataProcessors;
    /** @var EcommerceTeam_Dataflow_Helper_Data */
    protected $_helper;
    /** @var EcommerceTeam_Dataflow_Helper_Reflection */
    protected $_reflectionHelper;
    /** @var Varien_Db_Adapter_Interface */
    protected $_resourceConnection;
    /** @var string */
    protected $_productTable;
    /** @var array */
    protected $_skuToId;

    /**
     * @return Mage_Catalog_Model_Resource_Product
     */
    protected function _getEntityTypeId()
    {
        /** @var $resource Mage_Catalog_Model_Resource_Product */
        $resource = Mage::getResourceSingleton('catalog/product');

        return $resource->getTypeId();
    }

    /**
     * Initialization
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_helper             = Mage::helper('ecommerceteam_dataflow');
        $this->_reflectionHelper   = Mage::helper('ecommerceteam_dataflow/reflection');
        $this->_resourceConnection = $this->_config->getResourceConnection();
        $this->_productTable       = $this->_config->getResource()->getTableName('catalog/product');

        $this->_dataProcessors = array(
            0  => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Base($this),
            1  => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Category($this),
            2  => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Eav($this),
            3  => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Inventory($this),
            4  => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Media($this),
            5  => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Link($this),
            7  => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Options($this),
            8  => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Configurable($this),
            9  => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Grouped($this),
            10 => new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Bundle($this)
        );
        if (version_compare(Mage::getVersion(), '1.6', '>=')) {
            $this->_dataProcessors[6] = new EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Price($this);
        }

        return $this;
    }

    /**
     * Calls before prepare begin
     *
     * @return $this
     */
    public function beforePrepare()
    {
        foreach ($this->_dataProcessors as $processor) {
            /** @var $processor EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract */
            $processor->beforePrepare();
        }

        return $this;
    }

    /**
     * Prepare data for row
     *
     * @param array $data
     * @return EcommerceTeam_Dataflow_Model_Import_Adapter_Interface
     */
    public function prepareData(array &$data)
    {
        foreach ($this->_dataProcessors as $processor) {
            /** @var $processor EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract */
            $processor->prepareData($data);
        }

        return $this;
    }

    /**
     * @return EcommerceTeam_Dataflow_Model_Import_Adapter_Interface
     */
    public function afterPrepare()
    {
        foreach ($this->_dataProcessors as $processor) {
            /** @var $processor EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract */
            $processor->afterPrepare();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function beforeProcess()
    {
        foreach ($this->_dataProcessors as $processor) {
            /** @var $processor EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract */
            $processor->beforeProcess();
        }
        $this->_skuToId = $this->_resourceConnection->fetchPairs(
            $this->_resourceConnection->select()->from($this->_productTable, array('sku', 'entity_id'))
        );

        return $this;
    }

    /**
     * @param array $data
     * @return array
     * @throws EcommerceTeam_Dataflow_Exception
     */
    protected function _prepareAndValidateData(array &$data)
    {
        if (!isset($data['sku']) || !$data['sku'] || !trim($data['sku'])) {
            $this->_throwException($this->_helper->__('Product SKU is empty or not found.'));
        }
        $pattern = $this->getConfig()->getSkuPattern();
        if ($pattern && (strpos($pattern, '{sku}') !== false)) {
            $data['sku'] = str_replace('{sku}', trim($data['sku']), $pattern);
        }
        if (isset($data['type'])) {
            $data['type_id'] = $data['type'];
            unset($data['type']);
        }

        if (isset($data['price'])) {
            $data['price'] = str_replace(',', '.', $data['price']);
        }

        return $data;
    }

    /**
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function processData(array &$data)
    {
        $this->_prepareAndValidateData($data);

        if (isset($this->_skuToId[$data['sku']])) {
            if (!$this->_config->getUpdateExisting()) {
                return false;
            }
            $data['product_id']   = $this->_skuToId[$data['sku']];
        } else if (!$this->_config->getCanCreateNewEntity()) {
            return false;
        }

        if ($beforeProcessCallback = $this->getConfig()->getBeforeProcessCallback()) {
            $this->_reflectionHelper->getReflation($beforeProcessCallback)
                ->invokeArgs(null, array('data' => &$data));
        }
        $this->_resourceConnection->beginTransaction();
        try {
            foreach ($this->_dataProcessors as $processor) {
                /** @var $processor EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract */
                $processor->processData($data);
            }
            $this->_skuToId[$data['sku']] = $data['product_id'];
            $this->_resourceConnection->commit();
        } catch (Exception $e) {
            $this->_resourceConnection->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @return $this
     */
    public function afterProcess()
    {
        foreach ($this->_dataProcessors as $processor) {
            /** @var $processor EcommerceTeam_Dataflow_Model_Import_Adapter_Type_Product_Data_Abstract */
            $processor->afterProcess($this->_skuToId);
        }

        return $this;
    }
}
