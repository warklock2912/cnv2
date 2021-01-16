<?php

abstract class EcommerceTeam_Dataflow_Model_Import_Parser_Abstract
    implements EcommerceTeam_Dataflow_Model_Import_Parser_Interface
{
    /**
     * @param string $message
     * @param int $code
     * @throws EcommerceTeam_Dataflow_Model_Import_Parser_Exception
     */
    protected function _throwException($message, $code = 0)
    {
        throw new EcommerceTeam_Dataflow_Model_Import_Parser_Exception($message, $code);
    }
}