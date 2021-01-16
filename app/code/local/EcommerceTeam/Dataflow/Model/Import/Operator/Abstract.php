<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

abstract class EcommerceTeam_Dataflow_Model_Import_Operator_Abstract
{
    /**
     * @param string $message
     * @param int $code
     * @param Exception $previous
     * @throws EcommerceTeam_Dataflow_Exception
     */
    protected function _throwException($message, $code = 0, Exception $previous = null)
    {
        throw new EcommerceTeam_Dataflow_Exception($message, $code, $previous);
    }
}