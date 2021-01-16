<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Model_Storage_File
    implements EcommerceTeam_Dataflow_Model_Storage_Interface
{
    /** @var resource */
    protected $_resource;

    /**
     * Class constructor
     */
    public function __construct($tmpFilePath)
    {
        $this->_resource = fopen($tmpFilePath, 'w+');
    }

    /**
     * @param array $data
     * @return $this
     */
    public function saveData(array &$data)
    {
        fwrite($this->_resource, json_encode($data) . PHP_EOL);
        return $this;
    }

    /**
     * @return bool|array
     */
    public function getData()
    {
        if ($string = fgets($this->_resource)) {
            return json_decode($string, true);
        }
        return false;
    }

    /**
     * @return $this
     */
    public function rewind()
    {
        rewind($this->_resource);
        return $this;
    }
}
