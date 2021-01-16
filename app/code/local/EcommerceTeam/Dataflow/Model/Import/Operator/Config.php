<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Import_Operator_Config
{
    /** @var EcommerceTeam_Dataflow_Model_Import_Parser_Interface */
    protected $_parser;
    /** @var EcommerceTeam_Dataflow_Model_Import_Adapter_Interface */
    protected $_adapter;
    /** @var EcommerceTeam_Dataflow_Model_Storage_Interface */
    protected $_tmpStorage;

    /**
     * @param EcommerceTeam_Dataflow_Model_Import_Parser_Interface $parser
     * @param EcommerceTeam_Dataflow_Model_Import_Adapter_Interface $adapter
     * @param EcommerceTeam_Dataflow_Model_Storage_Interface $tmpStorage
     */
    public function __construct(
        EcommerceTeam_Dataflow_Model_Import_Parser_Interface $parser,
        EcommerceTeam_Dataflow_Model_Import_Adapter_Interface $adapter,
        EcommerceTeam_Dataflow_Model_Storage_Interface $tmpStorage
    )
    {
        $this->_parser     = $parser;
        $this->_adapter    = $adapter;
        $this->_tmpStorage = $tmpStorage;
    }

    /**
     * @return EcommerceTeam_Dataflow_Model_Import_Parser_Interface
     */
    public function getParser()
    {
        return $this->_parser;
    }

    /**
     * @return EcommerceTeam_Dataflow_Model_Import_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * @return EcommerceTeam_Dataflow_Model_Storage_Interface
     */
    public function getTemporaryStorage()
    {
        return $this->_tmpStorage;
    }
}
