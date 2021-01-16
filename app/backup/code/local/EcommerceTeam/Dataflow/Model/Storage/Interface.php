<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

interface EcommerceTeam_Dataflow_Model_Storage_Interface
{
    public function saveData(array &$data);
    public function getData();
    public function rewind();
}
