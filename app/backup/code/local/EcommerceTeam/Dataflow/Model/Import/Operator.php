<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Operator
    extends EcommerceTeam_Dataflow_Model_Import_Operator_Abstract
{
    /** @var EcommerceTeam_Dataflow_Model_Import_Operator_Config */
    protected $_config;
    /** @var EcommerceTeam_Dataflow_Model_Storage_Interface */
    protected $_temporaryStorage;

    /**
     * Setting and validate configuration parameters
     *
     * @param EcommerceTeam_Dataflow_Model_Import_Operator_Config $config
     */
    public function __construct(EcommerceTeam_Dataflow_Model_Import_Operator_Config $config)
    {
        $this->_config = $config;
        $this->_validate();
    }

    /**
     * Validate configuration parameters
     *
     * @throws EcommerceTeam_Dataflow_Exception
     */
    protected function _validate()
    {
        /** @var $helper EcommerceTeam_Dataflow_Helper_Data */
        $helper = Mage::helper('ecommerceteam_dataflow');
        if (!$this->_config->getParser() instanceof EcommerceTeam_Dataflow_Model_Import_Parser_Interface) {
            $this->_throwException($helper->__('Wrong parser model.'));
        }
        if (!$this->_config->getAdapter() instanceof EcommerceTeam_Dataflow_Model_Import_Adapter_Interface) {
            $this->_throwException($helper->__('Wrong adapter model.'));
        }
        if (!$this->_config->getTemporaryStorage() instanceof EcommerceTeam_Dataflow_Model_Storage_Interface) {
            $this->_throwException($helper->__('Wrong storage model.'));
        }
    }

    /**
     * Run import script
     *
     * @return Varien_Object
     * @throws Exception
     */
    public function run()
    {
        $result  = new Varien_Object();
        try {
            // Prepare before import
            $this->_prepare($result);
            // Import data
            $this->_process($result);
        } catch (Exception $e) {
            $this->logException($e);
            throw $e;
        }
        return $result;
    }

    /**
     * Prepare all data before import
     */
    protected function _prepare(Varien_Object &$result)
    {
        $parser     = $this->_config->getParser();
        $adapter    = $this->_config->getAdapter();
        $tmpStorage = $this->_config->getTemporaryStorage();

        // Check is correct data structure
        $parser->validate();
        // Initialize before prepare begin
        $adapter->beforePrepare();
        while (false !== ($data = $parser->getData())) {
            try {
                // Prepare data row before import
                $adapter->prepareData($data);
                $tmpStorage->saveData($data);
            } catch (EcommerceTeam_Dataflow_Exception $e) {
                $this->logException($e);
            }
        }
        // Initialize after prepare complete
        $adapter->afterPrepare();
        // Reset storage to first item
        $tmpStorage->rewind();

        return $result;
    }

    /**
     * Import all data
     */
    protected function _process(Varien_Object &$result)
    {
        /** @var $helper EcommerceTeam_Dataflow_Helper_Data */
        $helper = Mage::helper('ecommerceteam_dataflow');

        $adapter     = $this->_config->getAdapter();
        $tmpStorage  = $this->_config->getTemporaryStorage();

        $rowsProcessed = 0;
        $rowsSkipped   = 0;

        // Initialize before import begin
        $adapter->beforeProcess();
        $session = Mage::getSingleton('adminhtml/session');
        $i = 0;
        while (false !== ($data = $tmpStorage->getData())) { $i++;
            try {
                // Import item data
                if ($adapter->processData($data)) {
                    $rowsProcessed++;
                } else {
                    $session->addError(sprintf("Row #%d - Item was skipped", $i));
                    $rowsSkipped++;
                }
            } catch (EcommerceTeam_Dataflow_Exception $e) {
                $this->logRecordException($i, $e);
                $rowsSkipped++;
            }
        }
        // Initialize after import complete
        $adapter->afterProcess();
        /** @var $session Mage_Adminhtml_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');
        $session->addNotice($helper->__('Records found: %d', $i));
        $session->addNotice($helper->__('Records skipped: %d', $rowsSkipped));
        $session->addSuccess($helper->__('Records processed: %d', $rowsProcessed));
        return $result;
    }

    /**
     * Store exception
     *
     * @param int $recordNum
     * @param Exception $e
     * @return $this
     */
    public function logRecordException($recordNum, Exception $e)
    {
        /** @var $session Mage_Adminhtml_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');
        $session->addError(sprintf("Row #%d - %s", $recordNum, $e->getMessage()));
        return $this;
    }

    /**
     * Store exception
     *
     * @param Exception $e
     * @return $this
     */
    public function logException(Exception $e)
    {
        /** @var $session Mage_Adminhtml_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');
        $session->addError($e->getMessage());
        return $this;
    }
}
