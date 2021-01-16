<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

interface EcommerceTeam_Dataflow_Model_Import_Adapter_Interface
{
    public function beforePrepare();
    public function prepareData(array &$data);
    public function afterPrepare();
    public function beforeProcess();
    public function processData(array &$data);
    public function afterProcess();
}
