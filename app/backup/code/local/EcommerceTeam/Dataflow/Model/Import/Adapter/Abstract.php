<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

abstract class EcommerceTeam_Dataflow_Model_Import_Adapter_Abstract
    implements EcommerceTeam_Dataflow_Model_Import_Adapter_Interface
{
    /** @var EcommerceTeam_Dataflow_Model_Import_Adapter_Config */
    protected $_config;

    final public function __construct(EcommerceTeam_Dataflow_Model_Import_Adapter_Config $config)
    {
        $config->setEntityTypeId($this->_getEntityTypeId());
        $this->_config = $config;
        $this->_construct();
    }

    /**
     * @return EcommerceTeam_Dataflow_Model_Import_Adapter_Config
     */
    final public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     * @throws EcommerceTeam_Dataflow_Model_Import_Adapter_Exception
     */
    protected function _throwException($message, $code = 0, Exception $previous = null)
    {
        throw new EcommerceTeam_Dataflow_Model_Import_Adapter_Exception($message, $code, $previous);
    }

    /**
     * @return $this
     */
    abstract protected function _construct();

    /**
     * @return int
     */
    abstract protected function _getEntityTypeId();
}
